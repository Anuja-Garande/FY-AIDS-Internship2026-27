<?php
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'includes/functions.php';
require_once 'includes/functions_product.php';

$db = Database::getInstance();

$product = null;
if (isset($_GET['id'])) {
    $product = getProductById((int)$_GET['id']);
} elseif (isset($_GET['slug'])) {
    $product = getProductBySlug($_GET['slug']);
} else {
    redirect('products.php');
}

if (!$product) {
    $page_title = 'Product Not Found';
    include 'includes/header.php';
    echo '<div class="container py-5 text-center"><h2>Product Not Found</h2><p class="text-muted">The product you are looking for does not exist.</p><a href="products.php" class="btn btn-primary">Browse Products</a></div>';
    include 'includes/footer.php';
    exit;
}

$page_title = $product['name'];
$product_id = $product['id'];

// Resolve primary product image URL
$primary_image = !empty($product['image_url']) ? $product['image_url'] : 'https://placehold.co/600x600?text=Product+Image';

// Fetch extra gallery images
$db_images = $db->fetchAll("SELECT * FROM product_images WHERE product_id = :pid ORDER BY sort_order ASC", [':pid' => $product_id]);

$gallery_images = [];
if (!empty($db_images)) {
    foreach ($db_images as $img) {
        $clean_path = preg_replace('#^products/#', '', $img['image'] ?? '');
        // Determine full URL whether absolute or stored in uploads
        if (stristr($clean_path, 'http://') || stristr($clean_path, 'https://')) {
            $gallery_images[] = $clean_path;
        } else {
            $gallery_images[] = BASE_URL . 'assets/uploads/products/' . ltrim($clean_path, '/');
        }
    }
} else {
    // Fallback to main product image if no extra gallery images exist
    $gallery_images[] = $primary_image;
}

$specifications = $db->fetchAll("SELECT * FROM product_specifications WHERE product_id = :pid ORDER BY sort_order ASC", [':pid' => $product_id]);
$reviews = $db->fetchAll("SELECT r.*, u.name as user_name, u.avatar FROM reviews r LEFT JOIN users u ON r.user_id = u.id WHERE r.product_id = :pid ORDER BY r.created_at DESC", [':pid' => $product_id]);
$related = getRelatedProducts($product_id, $product['category_id'] ?? 0);

$avg_rating = 0;
$total_reviews = count($reviews);
if ($total_reviews > 0) {
    $sum = array_sum(array_column($reviews, 'rating'));
    $avg_rating = round($sum / $total_reviews, 1);
}
$rating_counts = array_fill(1, 5, 0);
foreach ($reviews as $r) { $rating_counts[$r['rating']]++; }

$is_wishlisted = false;
$is_logged_in = isLoggedIn();
if ($is_logged_in) {
    $user = getUser();
    $wl = $db->fetch("SELECT id FROM wishlist WHERE user_id = :uid AND product_id = :pid", [':uid' => $user['id'], ':pid' => $product_id]);
    $is_wishlisted = !empty($wl);
}

$active_tab = $_GET['tab'] ?? 'description';
?>
<?php include 'includes/header.php'; ?>

<style>
.gallery-main { border-radius: 16px; overflow: hidden; position: relative; background: var(--bg-secondary, #f8f9fa); }
.gallery-main img { width: 100%; height: 450px; object-fit: contain; transition: transform 0.3s ease; cursor: zoom-in; }
.gallery-main:hover img { transform: scale(1.05); }
.gallery-thumb { cursor: pointer; border: 2px solid transparent; border-radius: 8px; overflow: hidden; transition: all 0.2s ease; width: 70px; height: 70px; flex-shrink: 0; background: var(--bg-secondary, #f8f9fa); }
.gallery-thumb.active, .gallery-thumb:hover { border-color: #D4A017; transform: translateY(-2px); }
.gallery-thumb img { width: 100%; height: 100%; object-fit: cover; }
.product-badge { position: absolute; top: 15px; left: 15px; z-index: 2; }
.qty-stepper { display: inline-flex; align-items: center; border: 1px solid var(--border-color,#dee2e6); border-radius: 8px; overflow: hidden; }
.qty-stepper button { width: 40px; height: 40px; border: none; background: var(--bg-secondary,#f8f9fa); color: var(--text-primary,#333); cursor: pointer; font-size: 1.1rem; }
.qty-stepper input { width: 60px; height: 40px; text-align: center; border: none; border-left: 1px solid var(--border-color,#dee2e6); border-right: 1px solid var(--border-color,#dee2e6); font-weight: 600; background: var(--bg-primary,#fff); color: var(--text-primary,#333); }
.review-item { border-bottom: 1px solid var(--border-color,#eee); padding: 16px 0; color: var(--text-primary,#333); }
.review-item:last-child { border-bottom: none; }
.rating-bar { display: flex; align-items: center; gap: 8px; color: var(--text-primary,#333); }
.rating-bar .bar { flex: 1; height: 8px; background: var(--border-color,#eee); border-radius: 4px; overflow: hidden; }
.rating-bar .bar-fill { height: 100%; background: #ffc107; border-radius: 4px; }
.spec-table td { padding: 10px 16px; border-bottom: 1px solid var(--border-color,#f0f0f0); color: var(--text-primary,#333); }
.spec-table td:first-child { font-weight: 600; width: 35%; background: var(--bg-secondary,#f8f9fa); color: var(--text-primary,#333); }
.share-btn { width: 40px; height: 40px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; color: white; text-decoration: none; transition: transform 0.2s; border: none; }
.share-btn:hover { transform: scale(1.1); color: white; }

/* 3D Add to Cart Animation */
.prod-add-btn { position: relative; overflow: hidden; perspective: 600px; transition: all 0.4s cubic-bezier(0.34,1.56,0.64,1); }
.prod-add-btn::after {
  content: ''; position: absolute; inset: 0;
  background: linear-gradient(105deg, transparent 40%, rgba(255,255,255,0.15) 50%, transparent 60%);
  transform: translateX(-100%); transition: none;
}
.prod-add-btn:hover::after { transform: translateX(100%); transition: transform 0.6s ease; }
.prod-add-btn:active { transform: scale(0.95) rotateX(10deg); }

.prod-add-btn.btn-spin {
  animation: prodBtnSpin3D 0.8s cubic-bezier(0.4,0,0.2,1);
  pointer-events: none;
}
@keyframes prodBtnSpin3D {
  0%   { transform: perspective(600px) rotateY(0deg) scale(1); }
  30%  { transform: perspective(600px) rotateY(120deg) scale(0.85); }
  60%  { transform: perspective(600px) rotateY(240deg) scale(0.9); }
  100% { transform: perspective(600px) rotateY(360deg) scale(1); }
}

.prod-add-btn.btn-success-state {
  background: linear-gradient(135deg, #00c853, #00e676) !important;
  border-color: #00c853 !important;
  transform: perspective(600px) scale(1);
  animation: prodBtnPop3D 0.5s cubic-bezier(0.34,1.56,0.64,1);
  pointer-events: none;
}
@keyframes prodBtnPop3D {
  0%   { transform: perspective(600px) scale(0.8) rotateX(20deg); }
  50%  { transform: perspective(600px) scale(1.15) rotateX(-5deg); }
  100% { transform: perspective(600px) scale(1) rotateX(0); }
}

.prod-add-btn .btn-ripple-effect {
  position: absolute; border-radius: 50%; pointer-events: none;
  width: 30px; height: 30px; transform: translate(-50%,-50%) scale(0);
  background: rgba(255,255,255,0.4);
  animation: prodRippleOut 0.6s ease-out forwards;
}
@keyframes prodRippleOut {
  0% { transform: translate(-50%,-50%) scale(0); opacity: 1; }
  100% { transform: translate(-50%,-50%) scale(6); opacity: 0; }
}

.gallery-main img.prod-fly-out {
  animation: prodImgSpin3D 0.8s cubic-bezier(0.34,1.56,0.64,1);
}
@keyframes prodImgSpin3D {
  0%   { transform: perspective(800px) rotateY(0) scale(1); filter: brightness(1); }
  40%  { transform: perspective(800px) rotateY(180deg) scale(0.85); filter: brightness(1.3); }
  70%  { transform: perspective(800px) rotateY(300deg) scale(0.9); filter: brightness(1.1); }
  100% { transform: perspective(800px) rotateY(360deg) scale(1); filter: brightness(1); }
}

.prod-fly-clone {
  position: fixed; z-index: 99999; pointer-events: none;
  width: 80px; height: 80px; border-radius: 16px; overflow: hidden;
  box-shadow: 0 10px 40px rgba(102,126,234,0.5), 0 0 30px rgba(102,126,234,0.3);
  border: 3px solid rgba(255,255,255,0.9);
  transition: all 0.8s cubic-bezier(0.23,1,0.32,1);
  opacity: 1;
}
.prod-fly-clone img { width: 100%; height: 100%; object-fit: cover; }
.prod-fly-clone.fly-end {
  opacity: 0.2;
  transform: scale(0.15) rotate(360deg) translateY(-50px);
}

@keyframes prodCartBounce {
  0%,100% { transform: scale(1); }
  20% { transform: scale(1.4) rotate(-12deg); }
  40% { transform: scale(0.85) rotate(8deg); }
  60% { transform: scale(1.2) rotate(-5deg); }
  80% { transform: scale(0.95) rotate(2deg); }
}
.nav-link.prod-cart-bounce i.fa-shopping-cart {
  animation: prodCartBounce 0.7s cubic-bezier(0.34,1.56,0.64,1);
}

.price-pop-effect {
  animation: pricePop 0.5s cubic-bezier(0.34,1.56,0.64,1);
}
@keyframes pricePop {
  0% { transform: scale(1); }
  40% { transform: scale(1.15); color: #00c853 !important; }
  100% { transform: scale(1); }
}

.star-rating { display: flex; flex-direction: row-reverse; justify-content: flex-end; gap: 4px; }
.star-rating input { display: none; }
.star-rating label { color: #ddd; cursor: pointer; font-size: 1.3rem; transition: color 0.2s; }
.star-rating input:checked ~ label, .star-rating label:hover, .star-rating label:hover ~ label { color: #ffc107; }

/* Gallery Zoom */
.gallery-main { position: relative; overflow: hidden; border-radius: 16px; cursor: zoom-in; }
.gallery-main img { width: 100%; height: 450px; object-fit: contain; transition: transform 0.3s ease; transform-origin: var(--zoom-x, center) var(--zoom-y, center); }
.gallery-main:hover img { transform: scale(1.8); }
.gallery-main .zoom-hint {
  position: absolute; bottom: 10px; right: 10px; background: rgba(0,0,0,0.55); color: #fff;
  font-size: 0.7rem; padding: 4px 10px; border-radius: 20px; pointer-events: none;
  transition: opacity 0.3s; display: flex; align-items: center; gap: 5px;
}
.gallery-main:hover .zoom-hint { opacity: 0; }

/* Gallery Arrows */
.gallery-wrap { position: relative; }
.gallery-arrow {
  position: absolute; top: 50%; transform: translateY(-50%); z-index: 5;
  width: 38px; height: 38px; border-radius: 50%; border: none;
  background: rgba(255,255,255,0.92); color: #333; font-size: 1rem;
  display: flex; align-items: center; justify-content: center;
  box-shadow: 0 2px 8px rgba(0,0,0,0.15); cursor: pointer; transition: all 0.2s;
}
.gallery-arrow:hover { background: #fff; box-shadow: 0 4px 14px rgba(0,0,0,0.2); transform: translateY(-50%) scale(1.1); }
.gallery-arrow.prev { left: 10px; }
.gallery-arrow.next { right: 10px; }

/* Thumbnail strip */
#thumbStrip { overflow-x: auto; scrollbar-width: thin; padding-bottom: 4px; }
#thumbStrip::-webkit-scrollbar { height: 4px; }
#thumbStrip::-webkit-scrollbar-thumb { background: #ccc; border-radius: 4px; }

/* Lightbox */
.lb-overlay {
  display: none; position: fixed; inset: 0; z-index: 99999;
  background: rgba(0,0,0,0.92); backdrop-filter: blur(4px);
  align-items: center; justify-content: center; flex-direction: column;
  animation: lbFadeIn 0.25s ease;
}
.lb-overlay.active { display: flex; }
@keyframes lbFadeIn { from { opacity: 0; } to { opacity: 1; } }

.lb-close {
  position: absolute; top: 16px; right: 20px; z-index: 10;
  width: 44px; height: 44px; border-radius: 50%; border: none;
  background: rgba(255,255,255,0.12); color: #fff; font-size: 1.4rem;
  cursor: pointer; display: flex; align-items: center; justify-content: center;
  transition: background 0.2s;
}
.lb-close:hover { background: rgba(255,255,255,0.25); }

.lb-counter {
  position: absolute; top: 22px; left: 20px; z-index: 10;
  color: rgba(255,255,255,0.7); font-size: 0.85rem;
}

.lb-img-wrap {
  max-width: 85vw; max-height: 78vh; display: flex; align-items: center; justify-content: center;
}
.lb-img-wrap img {
  max-width: 100%; max-height: 78vh; object-fit: contain; border-radius: 8px;
  box-shadow: 0 8px 40px rgba(0,0,0,0.5);
  animation: lbImgIn 0.3s cubic-bezier(0.34,1.56,0.64,1);
}
@keyframes lbImgIn { from { transform: scale(0.85); opacity: 0; } to { transform: scale(1); opacity: 1; } }

.lb-arrow {
  position: absolute; top: 50%; transform: translateY(-50%); z-index: 10;
  width: 50px; height: 50px; border-radius: 50%; border: none;
  background: rgba(255,255,255,0.1); color: #fff; font-size: 1.3rem;
  cursor: pointer; display: flex; align-items: center; justify-content: center;
  transition: all 0.2s;
}
.lb-arrow:hover { background: rgba(255,255,255,0.25); transform: translateY(-50%) scale(1.1); }
.lb-arrow.lb-prev { left: 20px; }
.lb-arrow.lb-next { right: 20px; }

.lb-thumbs {
  display: flex; gap: 8px; margin-top: 16px; overflow-x: auto; max-width: 85vw;
  padding: 6px; scrollbar-width: thin;
}
.lb-thumbs::-webkit-scrollbar { height: 3px; }
.lb-thumbs::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.3); border-radius: 3px; }
.lb-thumb {
  width: 56px; height: 56px; border-radius: 6px; overflow: hidden; cursor: pointer;
  border: 2px solid transparent; opacity: 0.5; transition: all 0.2s; flex-shrink: 0;
}
.lb-thumb.active { border-color: #ffc107; opacity: 1; }
.lb-thumb:hover { opacity: 0.85; }
.lb-thumb img { width: 100%; height: 100%; object-fit: cover; }

@media (max-width: 768px) {
  .gallery-main img { height: 300px; }
  .gallery-main:hover img { transform: scale(1.5); }
  .gallery-arrow { width: 32px; height: 32px; font-size: 0.85rem; }
  .lb-arrow { width: 40px; height: 40px; font-size: 1.1rem; }
  .lb-thumb { width: 44px; height: 44px; }
}
</style>

<div class="container py-4">
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>" class="text-decoration-none">Home</a></li>
            <?php if (!empty($product['category_name'])): ?>
                <li class="breadcrumb-item"><a href="products.php?category=<?= htmlspecialchars($product['category_slug'] ?? '') ?>" class="text-decoration-none"><?= htmlspecialchars($product['category_name']) ?></a></li>
            <?php endif; ?>
            <li class="breadcrumb-item active" aria-current="page"><?= htmlspecialchars($product['name']) ?></li>
        </ol>
    </nav>

    <div class="row g-5">
        <div class="col-lg-6">
            <div class="gallery-wrap">
                <div class="gallery-main mb-3 position-relative" id="galleryMain">
                    <?php if (($product['sale_price'] ?? 0) < ($product['price'] ?? 0)): ?>
                        <span class="product-badge badge bg-danger fs-6">-<?= round((1 - $product['sale_price'] / $product['price']) * 100) ?>% OFF</span>
                    <?php endif; ?>
                    <img id="mainImage" src="<?= htmlspecialchars($gallery_images[0] ?? $primary_image) ?>" alt="<?= htmlspecialchars($product['name']) ?>">
                    <div class="zoom-hint"><i class="fas fa-search-plus"></i> Hover to zoom</div>
                    <?php if (count($gallery_images) > 1): ?>
                    <button class="gallery-arrow prev" onclick="galleryNav(-1)"><i class="fas fa-chevron-left"></i></button>
                    <button class="gallery-arrow next" onclick="galleryNav(1)"><i class="fas fa-chevron-right"></i></button>
                    <?php endif; ?>
                </div>
                <div class="d-flex gap-2 flex-wrap" id="thumbStrip">
                    <?php foreach ($gallery_images as $idx => $img_url): ?>
                    <div class="gallery-thumb <?= $idx === 0 ? 'active' : '' ?>" data-image="<?= htmlspecialchars($img_url) ?>" data-index="<?= $idx ?>">
                        <img src="<?= htmlspecialchars($img_url) ?>" alt="Thumbnail <?= $idx + 1 ?>">
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <h2 class="fw-bold mb-2"><?= htmlspecialchars($product['name']) ?></h2>
            <div class="d-flex align-items-center gap-3 mb-3">
                <div>
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                        <i class="fas fa-star <?= $i <= round($avg_rating) ? 'text-warning' : 'text-muted' ?>"></i>
                    <?php endfor; ?>
                    <span class="ms-1"><?= $avg_rating ?></span>
                </div>
                <span class="text-muted">(<?= $total_reviews ?> reviews)</span>
                <?php if ($product['stock'] > 0): ?>
                    <span class="badge bg-success">In Stock</span>
                <?php else: ?>
                    <span class="badge bg-danger">Out of Stock</span>
                <?php endif; ?>
            </div>

            <div class="mb-3">
                <span class="fs-3 fw-bold text-primary">₹<?= number_format($product['sale_price'] ?? $product['price'], 0) ?></span>
                <?php if (($product['sale_price'] ?? 0) < ($product['price'] ?? 0)): ?>
                    <del class="text-muted ms-2 fs-5">₹<?= number_format($product['price'], 0) ?></del>
                    <span class="badge bg-success ms-2 fs-6">Save ₹<?= number_format($product['price'] - $product['sale_price'], 0) ?></span>
                <?php endif; ?>
            </div>

            <?php if (!empty($product['short_description'])): ?>
                <p class="text-muted mb-4"><?= nl2br(htmlspecialchars($product['short_description'])) ?></p>
            <?php endif; ?>

            <div class="mb-4">
                <label class="form-label fw-semibold">Quantity</label>
                <div class="qty-stepper">
                    <button type="button" onclick="changeQty(-1)">-</button>
                    <input type="number" id="qty" value="1" min="1" max="<?= $product['stock'] ?>">
                    <button type="button" onclick="changeQty(1)">+</button>
                </div>
            </div>

            <div class="d-flex gap-3 mb-4 flex-wrap">
                <button class="btn btn-primary btn-lg px-5 flex-grow-1 add-to-cart-btn" data-id="<?= $product_id ?>" <?= $product['stock'] <= 0 ? 'disabled' : '' ?>>
                    <i class="fas fa-shopping-cart me-2"></i>Add to Cart
                </button>
                <button class="btn btn-outline-danger btn-lg wishlist-btn <?= $is_wishlisted ? 'active' : '' ?>" data-id="<?= $product_id ?>">
                    <i class="fas fa-heart <?= $is_wishlisted ? 'text-danger' : '' ?>"></i>
                </button>
                <button class="btn btn-outline-secondary btn-lg compare-btn" data-id="<?= $product_id ?>" data-name="<?= htmlspecialchars($product['name']) ?>" data-price="<?= $product['sale_price'] ?? $product['price'] ?>" data-image="<?= htmlspecialchars($primary_image) ?>">
                    <i class="fas fa-exchange-alt"></i>
                </button>
            </div>

            <div class="d-flex gap-3 mb-4">
                <div class="d-flex align-items-center">
                    <i class="fas fa-truck text-success me-2"></i>
                    <small>Free shipping over ₹<?= number_format(defined('FREE_SHIPPING_MIN') ? FREE_SHIPPING_MIN : 500) ?></small>
                </div>
                <div class="d-flex align-items-center">
                    <i class="fas fa-undo text-primary me-2"></i>
                    <small>7 Day Returns</small>
                </div>
            </div>

            <div class="mb-4">
                <small class="text-muted">Share:</small>
                <div class="d-flex gap-2 ms-2 d-inline-flex">
                    <a href="https://wa.me/?text=<?= urlencode($product['name'] . ' - ' . BASE_URL . 'product.php?id=' . $product_id) ?>" target="_blank" class="share-btn" style="background:#25d366;"><i class="fab fa-whatsapp"></i></a>
                    <a href="https://twitter.com/intent/tweet?text=<?= urlencode($product['name']) ?>&url=<?= urlencode(BASE_URL . 'product.php?id=' . $product_id) ?>" target="_blank" class="share-btn" style="background:#1da1f2;"><i class="fab fa-twitter"></i></a>
                    <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode(BASE_URL . 'product.php?id=' . $product_id) ?>" target="_blank" class="share-btn" style="background:#4267b2;"><i class="fab fa-facebook-f"></i></a>
                    <button class="share-btn" style="background:#666;" onclick="copyLink()"><i class="fas fa-link"></i></button>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-5">
        <ul class="nav nav-tabs" id="productTabs">
            <li class="nav-item"><a class="nav-link <?= $active_tab === 'description' ? 'active' : '' ?>" data-bs-toggle="tab" href="#tab-description">Description</a></li>
            <li class="nav-item"><a class="nav-link <?= $active_tab === 'specs' ? 'active' : '' ?>" data-bs-toggle="tab" href="#tab-specs">Specifications</a></li>
            <li class="nav-item"><a class="nav-link <?= $active_tab === 'reviews' ? 'active' : '' ?>" data-bs-toggle="tab" href="#tab-reviews">Reviews (<?= $total_reviews ?>)</a></li>
        </ul>
        <div class="tab-content p-4 border border-top-0 rounded-bottom" style="border-color: var(--border-color,#dee2e6) !important; background: var(--bg-primary,#fff); color: var(--text-primary,#333);">
            <div class="tab-pane fade <?= $active_tab === 'description' ? 'show active' : '' ?>" id="tab-description">
                <div class="product-description"><?= nl2br(htmlspecialchars($product['description'] ?? 'No description available.')) ?></div>
            </div>
            <div class="tab-pane fade <?= $active_tab === 'specs' ? 'show active' : '' ?>" id="tab-specs">
                <?php if (!empty($specifications)): ?>
                <table class="table spec-table">
                    <?php foreach ($specifications as $spec): ?>
                    <tr><td><?= htmlspecialchars($spec['spec_name']) ?></td><td><?= htmlspecialchars($spec['spec_value']) ?></td></tr>
                    <?php endforeach; ?>
                </table>
                <?php else: ?>
                <p class="text-muted">No specifications available.</p>
                <?php endif; ?>
            </div>
            <div class="tab-pane fade <?= $active_tab === 'reviews' ? 'show active' : '' ?>" id="tab-reviews">
                <div class="row g-4">
                    <div class="col-md-4">
                        <div class="text-center p-3">
                            <h3 class="fw-bold"><?= $avg_rating ?></h3>
                            <div class="mb-2">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <i class="fas fa-star <?= $i <= round($avg_rating) ? 'text-warning' : 'text-muted' ?>"></i>
                                <?php endfor; ?>
                            </div>
                            <small class="text-muted"><?= $total_reviews ?> reviews</small>
                        </div>
                        <?php for ($r = 5; $r >= 1; $r--): ?>
                        <div class="rating-bar mb-1">
                            <small class="me-1" style="width:20px;"><?= $r ?>★</small>
                            <div class="bar"><div class="bar-fill" style="width:<?= $total_reviews > 0 ? ($rating_counts[$r] / $total_reviews * 100) : 0 ?>%"></div></div>
                            <small class="text-muted" style="width:30px;text-align:right;"><?= $rating_counts[$r] ?></small>
                        </div>
                        <?php endfor; ?>
                    </div>
                    <div class="col-md-8">
                        <?php if ($is_logged_in): ?>
                        <div class="card mb-4">
                            <div class="card-body">
                                <h6 class="fw-bold mb-3">Write a Review</h6>
                                <form method="POST" action="ajax/review.php">
                                    <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                                    <input type="hidden" name="product_id" value="<?= $product_id ?>">
                                    <div class="mb-2">
                                        <label class="form-label">Rating</label>
                                        <div class="star-rating">
                                            <?php for ($i = 5; $i >= 1; $i--): ?>
                                                <input type="radio" name="rating" value="<?= $i ?>" id="star<?= $i ?>" <?= $i == 5 ? 'checked' : '' ?>><label for="star<?= $i ?>"><i class="fas fa-star"></i></label>
                                            <?php endfor; ?>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <textarea class="form-control" name="comment" rows="3" placeholder="Share your experience..." required></textarea>
                                    </div>
                                    <button type="submit" class="btn btn-primary btn-sm">Submit Review</button>
                                </form>
                            </div>
                        </div>
                        <?php else: ?>
                        <p class="text-muted mb-3"><a href="login.php" class="text-decoration-none fw-semibold">Login</a> to write a review.</p>
                        <?php endif; ?>

                        <?php foreach ($reviews as $review): ?>
                        <div class="review-item">
                            <div class="d-flex align-items-start">
                                <?php if (!empty($review['avatar'])): ?>
                                    <img src="<?= BASE_URL ?>assets/uploads/users/<?= htmlspecialchars($review['avatar']) ?>" class="rounded-circle me-3" style="width:40px;height:40px;object-fit:cover;" alt="">
                                <?php else: ?>
                                    <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-3" style="width:40px;height:40px;font-size:0.9rem;font-weight:600;flex-shrink:0;"><?= strtoupper(substr($review['user_name'] ?? 'U', 0, 1)) ?></div>
                                <?php endif; ?>
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <strong><?= htmlspecialchars($review['user_name'] ?? 'Customer') ?></strong>
                                            <div class="mb-1">
                                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                                    <i class="fas fa-star <?= $i <= $review['rating'] ? 'text-warning' : 'text-muted' ?>" style="font-size:0.8rem;"></i>
                                                <?php endfor; ?>
                                            </div>
                                        </div>
                                        <small class="text-muted"><?= timeAgo($review['created_at']) ?></small>
                                    </div>
                                    <p class="mb-0 text-muted"><?= htmlspecialchars($review['comment']) ?></p>
                                    <?php if (!empty($review['admin_reply'])): ?>
                                    <div class="bg-light p-3 rounded mt-2" style="background: var(--bg-secondary,#f8f9fa) !important;">
                                        <small class="fw-semibold text-primary"><i class="fas fa-reply me-1"></i>Admin Reply:</small>
                                        <p class="mb-0 small"><?= htmlspecialchars($review['admin_reply']) ?></p>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        <?php if (empty($reviews)): ?>
                        <p class="text-muted text-center py-4">No reviews yet. Be the first to review!</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php if (!empty($related)): ?>
    <div class="mt-5">
        <h3 class="fw-bold mb-4">Related Products</h3>
        <div class="row g-4">
            <?php foreach ($related as $rp): ?>
            <?php $rp_img = !empty($rp['image_url']) ? $rp['image_url'] : 'https://placehold.co/400x400?text=Product'; ?>
            <div class="col-sm-6 col-md-4 col-lg-3">
                <div class="card product-card h-100 shadow-sm">
                    <a href="product.php?slug=<?= htmlspecialchars($rp['slug']) ?>">
                        <img src="<?= htmlspecialchars($rp_img) ?>" class="card-img-top" alt="<?= htmlspecialchars($rp['name']) ?>" style="height:220px; object-fit:cover;">
                    </a>
                    <div class="card-body">
                        <h6 class="text-truncate"><a href="product.php?slug=<?= htmlspecialchars($rp['slug']) ?>" class="text-decoration-none text-dark"><?= htmlspecialchars($rp['name']) ?></a></h6>
                        <div class="d-flex align-items-center mb-2">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <i class="fas fa-star <?= $i <= ($rp['avg_rating'] ?? 0) ? 'text-warning' : 'text-muted' ?>" style="font-size:0.8rem;"></i>
                            <?php endfor; ?>
                        </div>
                        <span class="fw-bold text-primary">₹<?= number_format($rp['sale_price'] ?? $rp['price'], 0) ?></span>
                        <?php if (($rp['sale_price'] ?? 0) < ($rp['price'] ?? 0)): ?>
                            <del class="text-muted ms-1 small">₹<?= number_format($rp['price'], 0) ?></del>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Lightbox Overlay -->
<div class="lb-overlay" id="lightbox">
    <button class="lb-close" onclick="closeLightbox()"><i class="fas fa-times"></i></button>
    <span class="lb-counter" id="lbCounter">1 / <?= count($gallery_images) ?></span>
    <?php if (count($gallery_images) > 1): ?>
    <button class="lb-arrow lb-prev" onclick="lbNav(-1)"><i class="fas fa-chevron-left"></i></button>
    <button class="lb-arrow lb-next" onclick="lbNav(1)"><i class="fas fa-chevron-right"></i></button>
    <?php endif; ?>
    <div class="lb-img-wrap" id="lbImgWrap">
        <img id="lbImage" src="<?= htmlspecialchars($gallery_images[0] ?? $primary_image) ?>" alt="Lightbox">
    </div>
    <?php if (count($gallery_images) > 1): ?>
    <div class="lb-thumbs" id="lbThumbs">
        <?php foreach ($gallery_images as $idx => $img_url): ?>
        <div class="lb-thumb <?= $idx === 0 ? 'active' : '' ?>" data-index="<?= $idx ?>" onclick="lbGoTo(<?= $idx ?>)">
            <img src="<?= htmlspecialchars($img_url) ?>" alt="Thumb <?= $idx + 1 ?>">
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<script>
var galleryImages = <?= json_encode(array_values($gallery_images), JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
var currentGalleryIndex = 0;

document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.gallery-thumb').forEach(thumb => {
        thumb.addEventListener('click', function() {
            var idx = parseInt(this.dataset.index) || 0;
            setGalleryImage(idx);
        });
    });

    var galleryMain = document.getElementById('galleryMain');
    var mainImg = document.getElementById('mainImage');
    if (galleryMain && mainImg) {
        galleryMain.addEventListener('mousemove', function(e) {
            var rect = galleryMain.getBoundingClientRect();
            var x = ((e.clientX - rect.left) / rect.width * 100);
            var y = ((e.clientY - rect.top) / rect.height * 100);
            mainImg.style.setProperty('--zoom-x', x + '%');
            mainImg.style.setProperty('--zoom-y', y + '%');
        });
        galleryMain.addEventListener('mouseleave', function() {
            mainImg.style.setProperty('--zoom-x', 'center');
            mainImg.style.setProperty('--zoom-y', 'center');
        });
        galleryMain.addEventListener('click', function(e) {
            if (e.target.closest('.gallery-arrow')) return;
            openLightbox(currentGalleryIndex);
        });
    }

    document.addEventListener('keydown', function(e) {
        var lb = document.getElementById('lightbox');
        if (!lb || !lb.classList.contains('active')) return;
        if (e.key === 'Escape') closeLightbox();
        if (e.key === 'ArrowLeft') lbNav(-1);
        if (e.key === 'ArrowRight') lbNav(1);
    });

    /* Product Page - 3D Add to Cart Animation */
    (function() {
        var addBtn = document.querySelector('.add-to-cart-btn');
        if (!addBtn) return;

        addBtn.classList.add('prod-add-btn');
        var mainImg = document.getElementById('mainImage');
        var cartIcon = document.querySelector('a[href*="cart.php"] .fa-shopping-cart');
        var priceEl = document.querySelector('.fs-3.fw-bold');
        var origText = addBtn.innerHTML;

        addBtn.addEventListener('click', function(e) {
            if (addBtn.classList.contains('btn-spin') || addBtn.classList.contains('btn-success-state')) return;

            /* Ripple */
            var ripple = document.createElement('span');
            ripple.className = 'btn-ripple-effect';
            var rect = addBtn.getBoundingClientRect();
            ripple.style.left = (e.clientX - rect.left) + 'px';
            ripple.style.top = (e.clientY - rect.top) + 'px';
            addBtn.appendChild(ripple);
            setTimeout(function() { if (ripple.parentNode) ripple.remove(); }, 700);

            /* 3D Spin the button */
            addBtn.classList.add('btn-spin');
            addBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Adding...';

            /* 3D Spin the product image */
            if (mainImg) {
                mainImg.classList.add('prod-fly-out');
                setTimeout(function() { mainImg.classList.remove('prod-fly-out'); }, 900);
            }

            /* Fly clone to cart */
            if (mainImg && cartIcon) {
                var imgRect = mainImg.getBoundingClientRect();
                var cartRect = cartIcon.getBoundingClientRect();
                var clone = document.createElement('div');
                clone.className = 'prod-fly-clone';
                var cloneImg = document.createElement('img');
                cloneImg.src = mainImg.src;
                clone.appendChild(cloneImg);
                clone.style.left = (imgRect.left + imgRect.width / 2 - 40) + 'px';
                clone.style.top = (imgRect.top + imgRect.height / 2 - 40) + 'px';
                document.body.appendChild(clone);

                requestAnimationFrame(function() {
                    requestAnimationFrame(function() {
                        clone.classList.add('fly-end');
                        clone.style.left = (cartRect.left + cartRect.width / 2 - 15) + 'px';
                        clone.style.top = (cartRect.top + cartRect.height / 2 - 15) + 'px';
                        clone.style.width = '30px';
                        clone.style.height = '30px';
                    });
                });
                setTimeout(function() { if (clone.parentNode) clone.remove(); }, 900);
            }

            /* After spin -> show success */
            setTimeout(function() {
                addBtn.classList.remove('btn-spin');
                addBtn.classList.add('btn-success-state');
                addBtn.innerHTML = '<i class="fas fa-check me-2"></i>Added to Cart!';

                if (cartIcon) {
                    var nav = cartIcon.closest('.nav-link');
                    if (nav) {
                        nav.classList.remove('prod-cart-bounce');
                        void nav.offsetWidth;
                        nav.classList.add('prod-cart-bounce');
                        setTimeout(function() { nav.classList.remove('prod-cart-bounce'); }, 800);
                    }
                }

                document.querySelectorAll('.cart-badge').forEach(function(b) {
                    b.classList.remove('badge-pop');
                    void b.offsetWidth;
                    b.classList.add('badge-pop');
                });

                if (priceEl) {
                    priceEl.classList.remove('price-pop-effect');
                    void priceEl.offsetWidth;
                    priceEl.classList.add('price-pop-effect');
                }
            }, 850);

            /* Reset button */
            setTimeout(function() {
                addBtn.classList.remove('btn-success-state');
                addBtn.innerHTML = origText;
            }, 3000);
        });
    })();

    document.querySelector('.compare-btn')?.addEventListener('click', function() {
        let compare = JSON.parse(localStorage.getItem('compare') || '[]');
        const item = { id: this.dataset.id, name: this.dataset.name, price: this.dataset.price, image: this.dataset.image };
        const exists = compare.findIndex(c => c.id === item.id);
        if (exists >= 0) { 
            compare.splice(exists, 1); 
            if (typeof Toast !== 'undefined') Toast.show('Removed from compare', 'info'); 
        } else {
            if (compare.length >= 4) { 
                if (typeof Toast !== 'undefined') Toast.show('Max 4 products to compare', 'warning'); 
                return; 
            }
            compare.push(item); 
            if (typeof Toast !== 'undefined') Toast.show('Added to compare', 'success');
        }
        localStorage.setItem('compare', JSON.stringify(compare));
    });
});

function changeQty(delta) {
    const input = document.getElementById('qty');
    let val = parseInt(input.value) + delta;
    val = Math.max(1, Math.min(val, parseInt(input.max) || 99));
    input.value = val;
}

function copyLink() {
    navigator.clipboard.writeText(window.location.href);
    if (typeof Toast !== 'undefined') Toast.show('Link copied!', 'success');
}

/* Gallery Navigation */
function setGalleryImage(idx) {
    if (idx < 0 || idx >= galleryImages.length) return;
    currentGalleryIndex = idx;
    var mainImg = document.getElementById('mainImage');
    if (mainImg) mainImg.src = galleryImages[idx];
    document.querySelectorAll('.gallery-thumb').forEach(function(t, i) {
        t.classList.toggle('active', i === idx);
    });
    var strip = document.getElementById('thumbStrip');
    if (strip) {
        var active = strip.children[idx];
        if (active) active.scrollIntoView({ behavior: 'smooth', inline: 'center', block: 'nearest' });
    }
}

function galleryNav(dir) {
    var next = currentGalleryIndex + dir;
    if (next < 0) next = galleryImages.length - 1;
    if (next >= galleryImages.length) next = 0;
    setGalleryImage(next);
}

/* Lightbox */
function openLightbox(idx) {
    currentGalleryIndex = idx || 0;
    var lb = document.getElementById('lightbox');
    if (!lb) return;
    lb.classList.add('active');
    document.body.style.overflow = 'hidden';
    lbSetImage(currentGalleryIndex);
}

function closeLightbox() {
    var lb = document.getElementById('lightbox');
    if (!lb) return;
    lb.classList.remove('active');
    document.body.style.overflow = '';
}

function lbNav(dir) {
    var next = currentGalleryIndex + dir;
    if (next < 0) next = galleryImages.length - 1;
    if (next >= galleryImages.length) next = 0;
    currentGalleryIndex = next;
    lbSetImage(next);
}

function lbGoTo(idx) {
    currentGalleryIndex = idx;
    lbSetImage(idx);
}

function lbSetImage(idx) {
    var lbImg = document.getElementById('lbImage');
    if (lbImg) {
        lbImg.style.animation = 'none';
        void lbImg.offsetWidth;
        lbImg.style.animation = '';
        lbImg.src = galleryImages[idx];
    }
    var counter = document.getElementById('lbCounter');
    if (counter) counter.textContent = (idx + 1) + ' / ' + galleryImages.length;
    document.querySelectorAll('.lb-thumb').forEach(function(t, i) {
        t.classList.toggle('active', i === idx);
    });
}

document.addEventListener('click', function(e) {
    if (e.target.id === 'lightbox' || e.target.classList.contains('lb-img-wrap')) {
        closeLightbox();
    }
});
</script>

<?php include 'includes/footer.php'; ?>