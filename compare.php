<?php
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'includes/functions.php';
require_once 'includes/functions_product.php';

$page_title = 'Compare Products';
$db = Database::getInstance();

$compare_ids = $_GET['ids'] ?? '';
$product_ids = array_filter(explode(',', $compare_ids));
$product_ids = array_map('intval', $product_ids);
$product_ids = array_slice($product_ids, 0, 4);
$products = [];
if (!empty($product_ids)) {
    $placeholders = implode(',', array_fill(0, count($product_ids), '?'));
    $products = $db->fetchAll("SELECT p.*, REPLACE((SELECT image FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1), 'products/', '') AS image, c.name as category_name, b.name as brand_name FROM products p LEFT JOIN categories c ON p.category_id = c.id LEFT JOIN brands b ON p.brand_id = b.id WHERE p.id IN ($placeholders)", $product_ids);
}

$all_specs = [];
foreach ($products as $p) {
    $specs = $db->fetchAll("SELECT * FROM product_specifications WHERE product_id = :pid", [':pid' => $p['id']]);
    foreach ($specs as $s) {
        $all_specs[$s['spec_name']] = true;
    }
}
?>
<?php include 'includes/header.php'; ?>

<style>
.compare-table { overflow-x: auto; }
.compare-table table { min-width: 100%; }
.compare-table th, .compare-table td { vertical-align: middle; text-align: center; padding: 16px; min-width: 200px; }
.compare-table th { background: #f8f9fa; font-weight: 600; text-align: left; width: 180px; min-width: 180px; }
.compare-table td img { width: 120px; height: 120px; object-fit: cover; border-radius: 12px; }
.compare-empty { min-height: 400px; display: flex; align-items: center; justify-content: center; }
.add-compare-slot { border: 2px dashed #dee2e6; border-radius: 12px; min-height: 400px; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.3s; }
.add-compare-slot:hover { border-color: #667eea; background: #f0f4ff; }
</style>

<div class="container py-4">
    <h2 class="fw-bold mb-4"><i class="fas fa-exchange-alt me-2"></i>Compare Products</h2>

    <?php if (empty($products)): ?>
    <div class="text-center py-5">
        <i class="fas fa-exchange-alt fa-4x text-muted mb-3"></i>
        <h4>No products to compare</h4>
        <p class="text-muted">Add products from the product page to compare them side by side.</p>
        <a href="products.php" class="btn btn-primary">Browse Products</a>
    </div>
    <?php else: ?>
    <div class="compare-table">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Product</th>
                    <?php foreach ($products as $p): ?>
                    <td class="position-relative">
                        <a href="product.php?slug=<?= htmlspecialchars($p['slug']) ?>" class="text-decoration-none">
                            <img src="<?= htmlspecialchars($p['image_url'] ?: 'https://placehold.co/400x400?text=Product') ?>" alt="<?= htmlspecialchars($p['name']) ?>" class="mb-2">
                            <h6 class="text-dark"><?= htmlspecialchars($p['name']) ?></h6>
                        </a>
                        <a href="product.php?slug=<?= htmlspecialchars($p['slug']) ?>" class="btn btn-primary btn-sm mt-2">View</a>
                    </td>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <th>Price</th>
                    <?php foreach ($products as $p): ?>
                    <td><span class="fw-bold text-primary fs-5">₹<?= number_format($p['sale_price'] ?? $p['price'], 0) ?></span>
                    <?php if (($p['sale_price'] ?? 0) < ($p['price'] ?? 0)): ?>
                        <del class="text-muted d-block small">₹<?= number_format($p['price'], 0) ?></del>
                    <?php endif; ?>
                    </td>
                    <?php endforeach; ?>
                </tr>
                <tr>
                    <th>Rating</th>
                    <?php foreach ($products as $p): ?>
                    <td>
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <i class="fas fa-star <?= $i <= ($p['avg_rating'] ?? 0) ? 'text-warning' : 'text-muted' ?>"></i>
                        <?php endfor; ?>
                        <div class="small text-muted mt-1">(<?= $p['review_count'] ?? 0 ?> reviews)</div>
                    </td>
                    <?php endforeach; ?>
                </tr>
                <tr>
                    <th>Brand</th>
                    <?php foreach ($products as $p): ?>
                    <td><?= htmlspecialchars($p['brand_name'] ?? 'N/A') ?></td>
                    <?php endforeach; ?>
                </tr>
                <tr>
                    <th>Category</th>
                    <?php foreach ($products as $p): ?>
                    <td><?= htmlspecialchars($p['category_name'] ?? 'N/A') ?></td>
                    <?php endforeach; ?>
                </tr>
                <tr>
                    <th>Availability</th>
                    <?php foreach ($products as $p): ?>
                    <td>
                        <?php if ($p['stock'] > 0): ?>
                            <span class="badge bg-success">In Stock (<?= $p['stock'] ?>)</span>
                        <?php else: ?>
                            <span class="badge bg-danger">Out of Stock</span>
                        <?php endif; ?>
                    </td>
                    <?php endforeach; ?>
                </tr>
                <?php foreach (array_keys($all_specs) as $spec_key): ?>
                <tr>
                    <th><?= htmlspecialchars($spec_key) ?></th>
                    <?php foreach ($products as $p): ?>
                    <td>
                        <?php
                        $val = $db->fetch("SELECT spec_value FROM product_specifications WHERE product_id = :pid AND spec_name = :sk", [':pid' => $p['id'], ':sk' => $spec_key]);
                        echo htmlspecialchars($val['spec_value'] ?? '-');
                        ?>
                    </td>
                    <?php endforeach; ?>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="d-flex gap-2 mt-3">
        <button onclick="clearCompare()" class="btn btn-outline-danger"><i class="fas fa-trash me-1"></i>Clear All</button>
        <a href="products.php" class="btn btn-outline-primary"><i class="fas fa-plus me-1"></i>Add More Products</a>
    </div>
    <?php endif; ?>
</div>

<script>
function clearCompare() {
    localStorage.removeItem('compare');
    window.location.href = 'compare.php';
}
</script>

<?php include 'includes/footer.php'; ?>
