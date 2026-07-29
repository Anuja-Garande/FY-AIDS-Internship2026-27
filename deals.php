<?php
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'includes/functions.php';
require_once 'includes/functions_product.php';

$page_title = 'Deals & Offers';
$db = Database::getInstance();

$flash_sale_products = getFlashSaleProducts();
$deals_products = $db->fetchAll("SELECT p.*, COALESCE(p.discount_price, p.price) as sale_price, p.quantity as stock, p.rating as avg_rating, REPLACE((SELECT image FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1), 'products/', '') as image, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.status = 1 AND p.quantity > 0 AND COALESCE(p.discount_price, p.price) < p.price ORDER BY (p.price - COALESCE(p.discount_price, p.price)) DESC LIMIT 16");

$coupons = $db->fetchAll("SELECT * FROM coupons WHERE status = 1 AND (expiry_date IS NULL OR expiry_date >= CURDATE()) ORDER BY created_at DESC");
?>
<?php include 'includes/header.php'; ?>

<style>
.deal-hero { background: linear-gradient(135deg, #ff416c, #ff4b2b); color: white; border-radius: 20px; padding: 60px 40px; text-align: center; position: relative; overflow: hidden; }
.deal-hero::before { content: ''; position: absolute; top: -50%; right: -20%; width: 500px; height: 500px; background: rgba(255,255,255,0.1); border-radius: 50%; }
.deal-hero::after { content: ''; position: absolute; bottom: -30%; left: -10%; width: 300px; height: 300px; background: rgba(255,255,255,0.05); border-radius: 50%; }
.deal-card { border: none; border-radius: 16px; overflow: hidden; transition: all 0.3s; }
.deal-card:hover { transform: translateY(-6px); box-shadow: 0 12px 40px rgba(0,0,0,0.12); }
.deal-card .card-img-top { height: 220px; object-fit: cover; }
.deal-discount { position: absolute; top: 12px; left: 12px; z-index: 2; }
.countdown-box { display: inline-flex; flex-direction: column; align-items: center; justify-content: center; background: #1a1a2e; color: white; border-radius: 10px; padding: 10px 14px; min-width: 55px; }
.coupon-card { background: linear-gradient(135deg, #667eea, #764ba2); color: white; border-radius: 12px; padding: 20px; position: relative; overflow: hidden; }
.coupon-card::before { content: ''; position: absolute; top: 0; right: 0; width: 100px; height: 100px; background: rgba(255,255,255,0.1); border-radius: 50%; }
.coupon-card .code { background: rgba(255,255,255,0.2); padding: 6px 16px; border-radius: 8px; font-family: monospace; font-weight: 700; letter-spacing: 2px; }
.copy-coupon { cursor: pointer; transition: all 0.2s; }
.copy-coupon:hover { background: rgba(255,255,255,0.3) !important; }
</style>

<div class="container py-4">
    <div class="deal-hero mb-5 position-relative">
        <h1 class="fw-bold mb-3" style="position:relative;z-index:1;"><i class="fas fa-fire me-2"></i>Hot Deals & Offers</h1>
        <p class="mb-4 opacity-90" style="position:relative;z-index:1;">Grab the best deals before they're gone!</p>
        <div class="d-flex justify-content-center gap-3" style="position:relative;z-index:1;">
            <div class="countdown-box"><span id="cd-h" class="fw-bold fs-4">00</span><small style="font-size:0.7rem;">HRS</small></div>
            <div class="countdown-box"><span id="cd-m" class="fw-bold fs-4">00</span><small style="font-size:0.7rem;">MIN</small></div>
            <div class="countdown-box"><span id="cd-s" class="fw-bold fs-4">00</span><small style="font-size:0.7rem;">SEC</small></div>
        </div>
    </div>

    <?php if (!empty($flash_sale_products)): ?>
    <section class="mb-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="fw-bold"><i class="fas fa-bolt text-warning me-2"></i>Flash Sale</h3>
        </div>
        <div class="row g-4">
            <?php foreach ($flash_sale_products as $product): ?>
            <div class="col-sm-6 col-md-4 col-lg-3">
                <div class="card deal-card h-100 position-relative shadow-sm">
                    <div class="deal-discount"><span class="badge bg-danger fs-6">-<?= $product['discount'] ?? 0 ?>%</span></div>
                    <a href="product.php?slug=<?= htmlspecialchars($product['slug']) ?>">
                        <img src="<?= htmlspecialchars($product['image_url'] ?: 'https://placehold.co/400x400?text=Product') ?>" class="card-img-top" alt="<?= htmlspecialchars($product['name']) ?>">
                    </a>
                    <div class="card-body">
                        <h6 class="text-truncate"><a href="product.php?slug=<?= htmlspecialchars($product['slug']) ?>" class="text-decoration-none text-dark"><?= htmlspecialchars($product['name']) ?></a></h6>
                        <div class="d-flex align-items-center mb-2">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <i class="fas fa-star <?= $i <= ($product['avg_rating'] ?? 0) ? 'text-warning' : 'text-muted' ?>" style="font-size:0.8rem;"></i>
                            <?php endfor; ?>
                        </div>
                        <div>
                            <span class="fw-bold text-danger fs-5">₹<?= number_format($product['sale_price'] ?? $product['price'], 0) ?></span>
                            <del class="text-muted ms-1 small">₹<?= number_format($product['price'], 0) ?></del>
                        </div>
                    </div>
                    <div class="card-footer bg-transparent border-0 pb-3">
                        <a href="product.php?slug=<?= htmlspecialchars($product['slug']) ?>" class="btn btn-primary btn-sm w-100">View Deal</a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>

    <?php if (!empty($coupons)): ?>
    <section class="mb-5">
        <h3 class="fw-bold mb-4"><i class="fas fa-ticket-alt me-2 text-success"></i>Available Coupons</h3>
        <div class="row g-4">
            <?php foreach ($coupons as $coupon): ?>
            <div class="col-md-6 col-lg-4">
                <div class="coupon-card h-100">
                    <h5 class="fw-bold mb-1"><?= $coupon['type'] === 'percent' ? $coupon['value'] . '% OFF' : '₹' . number_format($coupon['value']) . ' OFF' ?></h5>
                    <p class="small opacity-75 mb-3"><?= htmlspecialchars($coupon['description'] ?? 'Use this coupon to save on your order!') ?></p>
                    <div class="d-flex align-items-center justify-content-between">
                        <span class="code"><?= htmlspecialchars($coupon['code']) ?></span>
                        <button class="btn btn-sm copy-coupon" style="background:rgba(255,255,255,0.2);color:white;border:none;" onclick="copyCoupon('<?= htmlspecialchars($coupon['code']) ?>')"><i class="fas fa-copy me-1"></i>Copy</button>
                    </div>
                    <?php if (!empty($coupon['min_order'])): ?>
                        <small class="d-block mt-2 opacity-60">Min. order: ₹<?= number_format($coupon['min_order']) ?></small>
                    <?php endif; ?>
                    <?php if (!empty($coupon['expiry_date'])): ?>
                        <small class="d-block opacity-60">Expires: <?= date('M d, Y', strtotime($coupon['expiry_date'])) ?></small>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>

    <?php if (!empty($deals_products)): ?>
    <section class="mb-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="fw-bold"><i class="fas fa-percentage me-2 text-primary"></i>Best Deals</h3>
            <a href="products.php" class="btn btn-outline-primary btn-sm">View All <i class="fas fa-arrow-right ms-1"></i></a>
        </div>
        <div class="row g-4">
            <?php foreach ($deals_products as $product): ?>
            <?php
                $discount_pct = ($product['price'] > 0 && $product['sale_price'] < $product['price']) ? round((1 - $product['sale_price'] / $product['price']) * 100) : 0;
            ?>
            <div class="col-sm-6 col-md-4 col-lg-3">
                <div class="card deal-card h-100 position-relative shadow-sm">
                    <?php if ($discount_pct > 0): ?>
                        <div class="deal-discount"><span class="badge bg-danger">-<?= $discount_pct ?>%</span></div>
                    <?php endif; ?>
                    <a href="product.php?slug=<?= htmlspecialchars($product['slug']) ?>">
                        <img src="<?= htmlspecialchars($product['image_url'] ?: 'https://placehold.co/400x400?text=Product') ?>" class="card-img-top" alt="<?= htmlspecialchars($product['name']) ?>">
                    </a>
                    <div class="card-body">
                        <small class="text-muted"><?= htmlspecialchars($product['category_name'] ?? '') ?></small>
                        <h6 class="text-truncate mt-1"><a href="product.php?slug=<?= htmlspecialchars($product['slug']) ?>" class="text-decoration-none text-dark"><?= htmlspecialchars($product['name']) ?></a></h6>
                        <div class="d-flex align-items-center mb-2">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <i class="fas fa-star <?= $i <= ($product['avg_rating'] ?? 0) ? 'text-warning' : 'text-muted' ?>" style="font-size:0.8rem;"></i>
                            <?php endfor; ?>
                            <small class="text-muted ms-1">(<?= $product['review_count'] ?? 0 ?>)</small>
                        </div>
                        <div>
                            <span class="fw-bold text-primary fs-5">₹<?= number_format($product['sale_price'] ?? $product['price'], 0) ?></span>
                            <?php if ($discount_pct > 0): ?>
                                <del class="text-muted ms-1 small">₹<?= number_format($product['price'], 0) ?></del>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="card-footer bg-transparent border-0 pb-3">
                        <div class="d-flex gap-2">
                            <a href="product.php?slug=<?= htmlspecialchars($product['slug']) ?>" class="btn btn-primary btn-sm flex-grow-1">View Deal</a>
                            <button class="btn btn-outline-danger btn-sm add-wishlist" data-id="<?= $product['id'] ?>" title="Wishlist"><i class="far fa-heart"></i></button>
                            <button class="btn btn-outline-info btn-sm add-compare" data-id="<?= $product['id'] ?>" title="Compare"><i class="fas fa-exchange-alt"></i></button>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>
</div>

<script>
const endDate = new Date();
endDate.setHours(23, 59, 59, 999);
function updateCountdown() {
    const now = new Date();
    const diff = endDate - now;
    if (diff <= 0) { endDate.setDate(endDate.getDate() + 1); return updateCountdown(); }
    document.getElementById('cd-h').textContent = String(Math.floor(diff / 3600000)).padStart(2, '0');
    document.getElementById('cd-m').textContent = String(Math.floor((diff % 3600000) / 60000)).padStart(2, '0');
    document.getElementById('cd-s').textContent = String(Math.floor((diff % 60000) / 1000)).padStart(2, '0');
}
updateCountdown();
setInterval(updateCountdown, 1000);

function copyCoupon(code) {
    navigator.clipboard.writeText(code);
    alert('Coupon code "' + code + '" copied!');
}

document.querySelectorAll('.add-wishlist').forEach(btn => {
    btn.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        const id = this.dataset.id;
        const btn = this;
        fetch('ajax/wishlist.php', { method: 'POST', headers: {'Content-Type': 'application/x-www-form-urlencoded'}, body: 'action=toggle&product_id=' + id })
        .then(r => r.json()).then(data => {
            if (data.success) {
                if (data.status === 'added') {
                    btn.innerHTML = '<i class="fas fa-heart text-danger"></i>';
                    btn.classList.add('active');
                } else {
                    btn.innerHTML = '<i class="far fa-heart"></i>';
                    btn.classList.remove('active');
                }
                if (typeof Swal !== 'undefined') {
                    Swal.fire({icon:'success',title:data.message,timer:1500,showConfirmButton:false});
                }
            } else if (data.require_login) {
                window.location.href = 'login.php';
            }
        }).catch(() => {});
    });
});

document.querySelectorAll('.add-compare').forEach(btn => {
    btn.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        const id = this.dataset.id;
        fetch('ajax/compare.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'action=add&product_id=' + id
        }).then(r => r.json()).then(data => {
            if (data.success) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({icon:'success',title:data.message,timer:1500,showConfirmButton:false});
                }
            } else {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({icon:'warning',title:'Compare',text:data.message});
                }
            }
        }).catch(() => {});
    });
});
</script>

<?php include 'includes/footer.php'; ?>
