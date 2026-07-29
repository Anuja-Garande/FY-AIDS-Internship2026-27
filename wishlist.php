<?php
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'includes/functions.php';
require_once 'includes/functions_product.php';

if (!isLoggedIn()) { redirect('login.php'); }

$page_title = 'My Wishlist';
$db = Database::getInstance();
$user = getUser();

if (isset($_POST['remove_wishlist'])) {
    if (verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $pid = (int)$_POST['product_id'];
        $db->delete("DELETE FROM wishlist WHERE user_id = :uid AND product_id = :pid", [':uid' => $user['id'], ':pid' => $pid]);
        flash('success', 'Removed from wishlist.');
        redirect('wishlist.php');
    }
}

if (isset($_POST['add_to_cart_wishlist'])) {
    if (verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $pid = (int)$_POST['product_id'];
        $existing = $db->fetch("SELECT id, quantity FROM cart WHERE user_id = :uid AND product_id = :pid", [':uid' => $user['id'], ':pid' => $pid]);
        if ($existing) {
            $db->update("UPDATE cart SET quantity = quantity + 1 WHERE id = :id", [':id' => $existing['id']]);
        } else {
            $db->insert("INSERT INTO cart (user_id, product_id, quantity, created_at) VALUES (:uid, :pid, 1, NOW())", [':uid' => $user['id'], ':pid' => $pid]);
        }
        $db->delete("DELETE FROM wishlist WHERE user_id = :uid AND product_id = :pid", [':uid' => $user['id'], ':pid' => $pid]);
        flash('success', 'Added to cart and removed from wishlist!');
        redirect('wishlist.php');
    }
}

    $wishlist_items = $db->fetchAll("SELECT w.*, p.name, p.slug, p.price, COALESCE(p.discount_price, p.price) as sale_price, p.image_url, REPLACE((SELECT image FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1), 'products/', '') as image, p.quantity as stock, p.rating as avg_rating, c.name as category_name FROM wishlist w JOIN products p ON w.product_id = p.id LEFT JOIN categories c ON p.category_id = c.id WHERE w.user_id = :uid ORDER BY w.created_at DESC", [':uid' => $user['id']]);
?>
<?php include 'includes/header.php'; ?>

<style>
.wishlist-card { transition: all 0.3s; border: none; overflow: hidden; }
.wishlist-card:hover { transform: translateY(-4px); box-shadow: 0 8px 30px rgba(0,0,0,0.1); }
.wishlist-card .card-img-top { height: 200px; object-fit: cover; }
.wishlist-card .remove-btn { position: absolute; top: 10px; right: 10px; z-index: 2; width: 32px; height: 32px; border-radius: 50%; background: white; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 8px rgba(0,0,0,0.1); cursor: pointer; border: none; }
</style>

<div class="container py-4">
    <h2 class="fw-bold mb-4"><i class="fas fa-heart me-2"></i>My Wishlist <small class="text-muted fs-6">(<?= count($wishlist_items) ?> items)</small></h2>

    <?php if (empty($wishlist_items)): ?>
    <div class="text-center py-5">
        <i class="fas fa-heart-broken fa-4x text-muted mb-3"></i>
        <h4>Your wishlist is empty</h4>
        <p class="text-muted">Save items you love to buy them later.</p>
        <a href="products.php" class="btn btn-primary">Browse Products</a>
    </div>
    <?php else: ?>
    <div class="row g-4">
        <?php foreach ($wishlist_items as $item): ?>
        <div class="col-sm-6 col-md-4 col-lg-3">
            <div class="card wishlist-card h-100 shadow-sm position-relative">
                <form method="POST" class="position-absolute" style="z-index:3;top:10px;right:10px;">
                    <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                    <input type="hidden" name="product_id" value="<?= $item['product_id'] ?>">
                    <button type="submit" name="remove_wishlist" class="remove-btn" title="Remove"><i class="fas fa-times text-danger"></i></button>
                </form>
                <a href="product.php?slug=<?= htmlspecialchars($item['slug']) ?>">
                    <?php $wish_img = !empty($item['image']) ? BASE_URL . 'assets/uploads/products/' . $item['image'] : ($item['image_url'] ?: BASE_URL . 'assets/uploads/products/default.png'); ?>
                    <img src="<?= htmlspecialchars($wish_img) ?>" class="card-img-top" alt="<?= htmlspecialchars($item['name']) ?>">
                </a>
                <div class="card-body">
                    <small class="text-muted"><?= htmlspecialchars($item['category_name'] ?? '') ?></small>
                    <h6 class="text-truncate mt-1"><a href="product.php?slug=<?= htmlspecialchars($item['slug']) ?>" class="text-decoration-none text-dark"><?= htmlspecialchars($item['name']) ?></a></h6>
                    <div class="d-flex align-items-center mb-2">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <i class="fas fa-star <?= $i <= ($item['avg_rating'] ?? 0) ? 'text-warning' : 'text-muted' ?>" style="font-size:0.8rem;"></i>
                        <?php endfor; ?>
                    </div>
                    <div class="mb-2">
                        <span class="fw-bold text-primary fs-5">₹<?= number_format($item['sale_price'] ?? $item['price'], 0) ?></span>
                        <?php if (($item['sale_price'] ?? 0) < ($item['price'] ?? 0)): ?>
                            <del class="text-muted ms-1 small">₹<?= number_format($item['price'], 0) ?></del>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="card-footer bg-transparent border-0 pb-3">
                    <?php if ($item['stock'] > 0): ?>
                    <form method="POST">
                        <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                        <input type="hidden" name="product_id" value="<?= $item['product_id'] ?>">
                        <button type="submit" name="add_to_cart_wishlist" class="btn btn-primary btn-sm w-100"><i class="fas fa-cart-plus me-1"></i>Add to Cart</button>
                    </form>
                    <?php else: ?>
                    <button class="btn btn-secondary btn-sm w-100" disabled>Out of Stock</button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
