<?php
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'includes/functions.php';
require_once 'includes/functions_product.php';

$page_title = 'Search Results';
$db = Database::getInstance();

$query = trim($_GET['q'] ?? '');
$ai_search = isset($_GET['ai_search']);
$sort = $_GET['sort'] ?? 'relevance';
$page = max(1, (int)($_GET['page'] ?? 1));

if (!isset($_SESSION['recent_searches'])) $_SESSION['recent_searches'] = [];
if (!empty($query) && !in_array($query, $_SESSION['recent_searches'])) {
    array_unshift($_SESSION['recent_searches'], $query);
    $_SESSION['recent_searches'] = array_slice($_SESSION['recent_searches'], 0, 10);
}

$products = [];
$total_products = 0;
$suggestions = [];

if (!empty($query)) {
    if ($ai_search) {
        $products = aiSearchProducts($query);
        $total_products = count($products);
    } else {
        $products = searchProducts($query);
        $total_products = count($products);

        $like_term = '%' . $query . '%';
        $suggest_rows = $db->fetchAll("SELECT name FROM products WHERE name LIKE :q AND status = 1 LIMIT 5", [':q' => $like_term]);
        $suggestions = array_column($suggest_rows, 'name');
    }

    $words = explode(' ', $query);
    $did_you_mean = '';
    if (count($products) < 3 && count($words) > 1) {
        $alt = $db->fetchAll("SELECT name FROM products WHERE status = 1 ORDER BY sales_count DESC LIMIT 3");
        $did_you_mean = !empty($alt) ? $alt[0]['name'] : '';
    }
}

$sort_map = [
    'price_asc' => fn($a, $b) => ($a['sale_price'] ?? $a['price']) - ($b['sale_price'] ?? $b['price']),
    'price_desc' => fn($a, $b) => ($b['sale_price'] ?? $b['price']) - ($a['sale_price'] ?? $a['price']),
    'newest' => fn($a, $b) => strtotime($b['created_at'] ?? 0) - strtotime($a['created_at'] ?? 0),
    'popular' => fn($a, $b) => ($b['sales_count'] ?? 0) - ($a['sales_count'] ?? 0),
    'rating' => fn($a, $b) => ($b['avg_rating'] ?? 0) - ($a['avg_rating'] ?? 0),
];
if (isset($sort_map[$sort])) {
    usort($products, $sort_map[$sort]);
}

$total_products = count($products);
$per_page = 12;
$total_pages = max(1, ceil($total_products / $per_page));
$page = min($page, $total_pages);
$paginated = array_slice($products, ($page - 1) * $per_page, $per_page);

function highlightText($text, $query) {
    $words = explode(' ', $query);
    foreach ($words as $word) {
        if (strlen($word) > 1) {
            $text = preg_replace('/(' . preg_quote($word, '/') . ')/i', '<mark>$1</mark>', $text);
        }
    }
    return $text;
}
?>
<?php include 'includes/header.php'; ?>

<style>
.search-suggestions { position: absolute; top: 100%; left: 0; right: 0; background: white; border-radius: 0 0 16px 16px; box-shadow: 0 8px 30px rgba(0,0,0,0.12); border: 1px solid rgba(0,0,0,0.08); z-index: 100; display: none; max-height: 380px; overflow-y: auto; padding: 8px; margin-top: -1px; }
.search-suggestions.show { display: block; }
.search-suggestions .suggestion-item { padding: 10px 16px; cursor: pointer; border-bottom: 1px solid #f0f0f0; }
.search-suggestions .suggestion-item:hover { background: #f8f9fa; }
.search-box { position: relative; }
.product-card { transition: all 0.3s; }
.product-card:hover { transform: translateY(-6px); box-shadow: 0 10px 30px rgba(0,0,0,0.12); }
.product-card .card-img-top { height: 200px; object-fit: cover; }
</style>

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <form method="GET" action="search.php" class="mb-4">
                <div class="search-box">
                    <div class="input-group input-group-lg shadow-sm">
                        <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-primary"></i></span>
                        <input type="search" name="q" class="form-control border-start-0" placeholder="Search products..." value="<?= htmlspecialchars($query) ?>" id="searchInput" autocomplete="off">
                        <button type="submit" class="btn btn-primary px-4">Search</button>
                    </div>
                    <div class="search-suggestions" id="searchSuggestions"></div>
                </div>
                <div class="form-check mt-2">
                    <input class="form-check-input" type="checkbox" name="ai_search" value="1" id="aiSearch" <?= $ai_search ? 'checked' : '' ?>>
                    <label class="form-check-label" for="aiSearch"><i class="fas fa-robot me-1 text-primary"></i>Enable AI Smart Search</label>
                </div>
            </form>

            <?php if (!empty($query)): ?>
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="fw-bold">Results for "<span class="text-primary"><?= htmlspecialchars($query) ?></span>" (<?= $total_products ?> found)</h5>
                <select class="form-select form-select-sm" style="width:auto;" onchange="var p=new URLSearchParams(window.location.search);p.set('sort',this.value);p.delete('page');window.location.href='search.php?'+p.toString();">
                    <option value="relevance" <?= $sort === 'relevance' ? 'selected' : '' ?>>Relevance</option>
                    <option value="price_asc" <?= $sort === 'price_asc' ? 'selected' : '' ?>>Price: Low to High</option>
                    <option value="price_desc" <?= $sort === 'price_desc' ? 'selected' : '' ?>>Price: High to Low</option>
                    <option value="newest" <?= $sort === 'newest' ? 'selected' : '' ?>>Newest</option>
                    <option value="popular" <?= $sort === 'popular' ? 'selected' : '' ?>>Most Popular</option>
                    <option value="rating" <?= $sort === 'rating' ? 'selected' : '' ?>>Top Rated</option>
                </select>
            </div>

            <?php if (!empty($suggestions) && $total_products < 5): ?>
            <div class="alert alert-info">
                <i class="fas fa-lightbulb me-2"></i>Did you mean:
                <?php foreach ($suggestions as $s): ?>
                    <a href="search.php?q=<?= urlencode($s) ?>" class="badge bg-primary text-decoration-none me-1"><?= htmlspecialchars($s) ?></a>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <?php if (!empty($did_you_mean)): ?>
            <div class="alert alert-warning">
                <i class="fas fa-spell-check me-2"></i>Did you mean: <a href="search.php?q=<?= urlencode($did_you_mean) ?>" class="fw-bold"><?= htmlspecialchars($did_you_mean) ?></a>?
            </div>
            <?php endif; ?>

            <?php if (empty($products)): ?>
            <div class="text-center py-5">
                <i class="fas fa-search fa-4x text-muted mb-3"></i>
                <h4>No products found</h4>
                <p class="text-muted">Try different keywords or browse our categories.</p>
                <a href="products.php" class="btn btn-primary">Browse All Products</a>
            </div>
            <?php else: ?>
            <div class="row g-4">
                <?php foreach ($paginated as $product): ?>
                <div class="col-sm-6 col-md-4">
                    <div class="card product-card h-100 shadow-sm">
                        <a href="product.php?slug=<?= htmlspecialchars($product['slug'] ?? '') ?>">
                            <img src="<?= htmlspecialchars($product['image_url'] ?: 'https://placehold.co/400x400?text=Product') ?>" class="card-img-top" alt="<?= htmlspecialchars($product['name']) ?>">
                        </a>
                        <div class="card-body">
                            <h6 class="text-truncate"><a href="product.php?slug=<?= htmlspecialchars($product['slug'] ?? '') ?>" class="text-decoration-none text-dark"><?= highlightText(htmlspecialchars($product['name']), $query) ?></a></h6>
                            <div class="d-flex align-items-center mb-2">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <i class="fas fa-star <?= $i <= ($product['avg_rating'] ?? 0) ? 'text-warning' : 'text-muted' ?>" style="font-size:0.8rem;"></i>
                                <?php endfor; ?>
                                <small class="text-muted ms-1">(<?= $product['review_count'] ?? 0 ?>)</small>
                            </div>
                            <span class="fw-bold text-primary fs-5">₹<?= number_format($product['sale_price'] ?? $product['price'], 0) ?></span>
                            <?php if (($product['sale_price'] ?? 0) < ($product['price'] ?? 0)): ?>
                                <del class="text-muted ms-1 small">₹<?= number_format($product['price'], 0) ?></del>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <?php if ($total_pages > 1): ?>
            <nav class="mt-4">
                <ul class="pagination justify-content-center">
                    <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                        <a class="page-link" href="search.php?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>">&laquo;</a>
                    </li>
                    <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                    <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                        <a class="page-link" href="search.php?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>"><?= $i ?></a>
                    </li>
                    <?php endfor; ?>
                    <li class="page-item <?= $page >= $total_pages ? 'disabled' : '' ?>">
                        <a class="page-link" href="search.php?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>">&raquo;</a>
                    </li>
                </ul>
            </nav>
            <?php endif; ?>
            <?php endif; ?>
            <?php else: ?>
            <?php if (!empty($_SESSION['recent_searches'])): ?>
            <div class="mt-4">
                <h6 class="fw-bold"><i class="fas fa-history me-2"></i>Recent Searches</h6>
                <div class="d-flex flex-wrap gap-2">
                    <?php foreach ($_SESSION['recent_searches'] as $rs): ?>
                        <a href="search.php?q=<?= urlencode($rs) ?>" class="badge bg-light text-dark text-decoration-none p-2"><?= htmlspecialchars($rs) ?></a>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
            <div class="text-center py-5">
                <i class="fas fa-robot fa-4x text-primary mb-3 opacity-50"></i>
                <h4>What are you looking for?</h4>
                <p class="text-muted">Use AI Smart Search to find exactly what you need!</p>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
const searchInput = document.getElementById('searchInput');
const suggestionsBox = document.getElementById('searchSuggestions');
let debounceTimer;

searchInput?.addEventListener('input', function() {
    clearTimeout(debounceTimer);
    const q = this.value.trim();
    if (q.length < 2) { suggestionsBox.classList.remove('show'); return; }
    suggestionsBox.innerHTML = '<div style="text-align:center;padding:12px;color:var(--text-muted,#999);font-size:0.85rem;"><i class="fas fa-spinner fa-spin"></i></div>';
    suggestionsBox.classList.add('show');
    debounceTimer = setTimeout(() => {
        fetch('ajax/search.php?action=suggest&q=' + encodeURIComponent(q))
            .then(r => r.json())
            .then(data => {
                if (data.success && data.suggestions && data.suggestions.length > 0) {
                    suggestionsBox.innerHTML = data.suggestions.map(p => {
                        var img = p.image && p.image !== 'default.png' ? 'assets/uploads/products/' + p.image : '';
                        var imgHtml = img ? '<img src="' + img + '" style="width:40px;height:40px;object-fit:cover;border-radius:8px;flex-shrink:0;" alt="">' : '<div style="width:40px;height:40px;border-radius:8px;background:rgba(244,114,182,0.1);display:flex;align-items:center;justify-content:center;flex-shrink:0;">🔍</div>';
                        return '<a href="' + p.url + '" style="display:flex;align-items:center;gap:10px;padding:8px 12px;border-radius:8px;text-decoration:none;color:var(--text-primary,#333);" onmouseover="this.style.background=\'rgba(244,114,182,0.08)\'" onmouseout="this.style.background=\'transparent\'">' + imgHtml + '<div style="flex:1;min-width:0;"><div style="font-size:0.84rem;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;color:var(--text-primary,#333);">' + p.name + '</div><div style="font-size:0.78rem;font-weight:700;color:#F472B6;">₹' + Number(p.price).toLocaleString('en-IN') + '</div></div></a>';
                    }).join('');
                    suggestionsBox.classList.add('show');
                } else {
                    suggestionsBox.innerHTML = '<div style="text-align:center;padding:14px;color:var(--text-muted,#999);font-size:0.85rem;">No products found</div>';
                }
            })
            .catch(function() {
                suggestionsBox.innerHTML = '<div style="text-align:center;padding:14px;color:var(--text-muted,#999);font-size:0.85rem;">Search failed</div>';
            });
    }, 300);
});

document.addEventListener('click', function(e) {
    if (!e.target.closest('.search-box')) suggestionsBox.classList.remove('show');
});
</script>

<?php include 'includes/footer.php'; ?>
