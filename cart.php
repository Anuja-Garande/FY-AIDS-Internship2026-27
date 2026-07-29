<?php
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'includes/functions.php';
require_once 'includes/functions_product.php';

$page_title = 'Shopping Cart';
$db = Database::getInstance();

$cart_items = [];
if (isLoggedIn()) {
    $user = getUser();
    $cart_items = $db->fetchAll(
            "SELECT c.id as cart_id, c.product_id, c.quantity, p.name, p.slug, p.price, p.discount_price, p.quantity as stock, p.image_url,
                REPLACE((SELECT image FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1), 'products/', '') as image
         FROM cart c JOIN products p ON c.product_id = p.id WHERE c.user_id = ? AND p.status = 1",
        [$user['id']]
    );
} elseif (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $pid => $qty) {
        $p = $db->fetch(
            "SELECT p.*, p.id AS product_id, COALESCE(p.discount_price, p.price) AS sale_price, REPLACE((SELECT image FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1), 'products/', '') as image FROM products p WHERE p.id = ? AND p.status = 1",
            [$pid]
        );
        if ($p) {
            $p['cart_id'] = 'session_' . $pid;
            $p['stock'] = $p['quantity'];
            $p['quantity'] = $qty;
            $cart_items[] = $p;
        }
    }
}

$subtotal = 0;
$discount = 0;
foreach ($cart_items as &$item) {
    $price = $item['discount_price'] ?? $item['price'];
    $item['line_total'] = $price * $item['quantity'];
    $subtotal += $item['line_total'];
    if (($item['discount_price'] ?? 0) > 0 && ($item['discount_price'] ?? 0) < ($item['price'] ?? 0)) {
        $discount += ($item['price'] - $item['discount_price']) * $item['quantity'];
    }
}
unset($item);

$shipping = $subtotal >= FREE_SHIPPING_MIN ? 0 : SHIPPING_CHARGES;
$tax = round($subtotal * TAX_RATE);
$total = $subtotal + $shipping + $tax;

$coupon_discount = 0;
$coupon_code = $_SESSION['coupon']['code'] ?? '';
$coupon_msg = '';

if (!empty($_SESSION['coupon']['discount'])) {
    $coupon_discount = $_SESSION['coupon']['discount'];
    $total -= $coupon_discount;
}

if (isset($_POST['apply_coupon'])) {
    $code = trim($_POST['coupon_code'] ?? '');
    if (!empty($code)) {
        $coupon = $db->fetch("SELECT * FROM coupons WHERE code = ? AND status = 1 AND (expiry_date IS NULL OR expiry_date >= CURDATE())", [$code]);
        if ($coupon && $subtotal >= $coupon['min_order']) {
            $disc = $coupon['type'] === 'percentage' ? round($subtotal * $coupon['value'] / 100) : $coupon['value'];
            if ($coupon['max_discount'] > 0) {
                $disc = min($disc, $coupon['max_discount']);
            }
            $disc = min($disc, $subtotal);
            $_SESSION['coupon'] = ['code' => $code, 'discount' => $disc];
            $coupon_discount = $disc;
            $total = $subtotal + $shipping + $tax - $coupon_discount;
            $coupon_msg = 'Coupon applied successfully!';
        } else {
            $coupon_msg = 'Invalid or expired coupon code.';
        }
    }
}

if (isset($_POST['remove_coupon'])) {
    unset($_SESSION['coupon']);
    $coupon_discount = 0;
    $coupon_code = '';
    $total = $subtotal + $shipping + $tax;
}
?>
<?php include 'includes/header.php'; ?>

<style>
.cart-item { transition: all 0.3s; color: var(--text-primary,#333); }
.cart-item:hover { background: var(--bg-card-hover,#f8f9fa); }
.cart-item img { width: 80px; height: 80px; object-fit: cover; border-radius: 8px; }
.qty-stepper { display: inline-flex; align-items: center; border: 1px solid var(--border-color,#dee2e6); border-radius: 8px; overflow: hidden; }
.qty-stepper button { width: 36px; height: 36px; border: none; background: var(--bg-secondary,#f8f9fa); color: var(--text-primary,#333); cursor: pointer; }
.qty-stepper input { width: 50px; height: 36px; text-align: center; border: none; border-left: 1px solid var(--border-color,#dee2e6); border-right: 1px solid var(--border-color,#dee2e6); font-weight: 600; background: var(--bg-primary,#fff); color: var(--text-primary,#333); }
.cart-summary { background: var(--bg-secondary,#f8f9fa); border-radius: 16px; padding: 24px; position: sticky; top: 80px; color: var(--text-primary,#333); }
.empty-cart { padding: 80px 0; text-align: center; }
.empty-cart h4 { color: var(--text-primary,#333); }
.empty-cart p { color: var(--text-muted,#6c757d); }
</style>

<div class="container py-4">
    <h2 class="fw-bold mb-4"><i class="fas fa-shopping-cart me-2"></i>Shopping Cart</h2>

    <?php if (empty($cart_items)): ?>
    <div class="empty-cart">
        <div class="mb-4"><i class="fas fa-shopping-cart fa-5x text-muted"></i></div>
        <h4 class="fw-bold">Your cart is empty</h4>
        <p class="text-muted mb-4">Looks like you haven't added anything yet.</p>
        <a href="products.php" class="btn btn-primary btn-lg px-5"><i class="fas fa-shopping-bag me-2"></i>Start Shopping</a>
    </div>
    <?php else: ?>
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4">Product</th>
                                    <th>Price</th>
                                    <th>Quantity</th>
                                    <th>Total</th>
                                    <th class="pe-4 text-center">Remove</th>
                                </tr>
                            </thead>
                            <tbody id="cartBody">
                                <?php foreach ($cart_items as $item): ?>
                                <?php $item_price = $item['discount_price'] ?? $item['price']; ?>
                                <tr class="cart-item" data-id="<?= $item['product_id'] ?>">
                                    <td class="ps-4">
                                        <div class="d-flex align-items-center">
                                            <?php $cart_img = !empty($item['image']) ? BASE_URL . 'assets/uploads/products/' . $item['image'] : ($item['image_url'] ?: BASE_URL . 'assets/uploads/products/default.png'); ?>
                                            <img src="<?= htmlspecialchars($cart_img) ?>" alt="<?= sanitize($item['name']) ?>" class="me-3">
                                            <div>
                                                <a href="product.php?slug=<?= htmlspecialchars($item['slug'] ?? $item['product_id']) ?>" class="text-decoration-none fw-semibold text-dark"><?= sanitize($item['name']) ?></a>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="fw-bold"><?= formatPrice($item_price) ?></span>
                                        <?php if ($item['discount_price'] > 0 && $item['discount_price'] < $item['price']): ?>
                                            <del class="text-muted d-block small"><?= formatPrice($item['price']) ?></del>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="qty-stepper">
                                            <button type="button" onclick="updateQty(<?= $item['product_id'] ?>, -1)">-</button>
                                            <input type="number" value="<?= $item['quantity'] ?>" min="1" max="<?= $item['stock'] ?? 99 ?>" id="qty_<?= $item['product_id'] ?>" readonly>
                                            <button type="button" onclick="updateQty(<?= $item['product_id'] ?>, 1)">+</button>
                                        </div>
                                    </td>
                                    <td class="fw-bold"><?= formatPrice($item['line_total']) ?></td>
                                    <td class="pe-4 text-center">
                                        <button class="btn btn-outline-danger btn-sm rounded-circle remove-item" data-id="<?= $item['product_id'] ?>" title="Remove">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="d-flex justify-content-between mt-3">
                <a href="products.php" class="btn btn-outline-primary"><i class="fas fa-arrow-left me-2"></i>Continue Shopping</a>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="cart-summary">
                <h5 class="fw-bold mb-3">Order Summary</h5>
                <div class="d-flex justify-content-between mb-2"><span>Subtotal</span><span class="fw-bold"><?= formatPrice($subtotal) ?></span></div>
                <?php if ($discount > 0): ?>
                    <div class="d-flex justify-content-between mb-2 text-success"><span>Discount</span><span>-<?= formatPrice($discount) ?></span></div>
                <?php endif; ?>
                <div class="d-flex justify-content-between mb-2"><span>Shipping</span><span class="fw-bold <?= $shipping == 0 ? 'text-success' : '' ?>"><?= $shipping == 0 ? 'FREE' : formatPrice($shipping) ?></span></div>
                <div class="d-flex justify-content-between mb-2"><span>Tax (GST <?= (TAX_RATE * 100) ?>%)</span><span><?= formatPrice($tax) ?></span></div>

                <?php if (!empty($coupon_code)): ?>
                <div class="d-flex justify-content-between mb-2 text-success">
                    <span>Coupon (<?= sanitize($coupon_code) ?>)</span>
                    <span>-<?= formatPrice($coupon_discount) ?></span>
                </div>
                <?php endif; ?>

                <hr>
                <div class="d-flex justify-content-between mb-3"><span class="fs-5 fw-bold">Total</span><span class="fs-5 fw-bold text-primary"><?= formatPrice($total) ?></span></div>

                <?php if (isLoggedIn()): ?>
                <form method="POST" class="mb-3">
                    <div class="input-group">
                        <input type="text" class="form-control" name="coupon_code" placeholder="Coupon code" value="<?= sanitize($coupon_code) ?>">
                        <?php if (!empty($coupon_code)): ?>
                            <button type="submit" name="remove_coupon" class="btn btn-outline-danger">Remove</button>
                        <?php else: ?>
                            <button type="submit" name="apply_coupon" class="btn btn-primary">Apply</button>
                        <?php endif; ?>
                    </div>
                    <?php if (!empty($coupon_msg)): ?>
                        <small class="<?= strpos($coupon_msg, 'success') !== false ? 'text-success' : 'text-danger' ?>"><?= $coupon_msg ?></small>
                    <?php endif; ?>
                </form>
                <?php else: ?>
                <div class="alert alert-info small mb-3">
                    <a href="login.php" class="fw-semibold">Login</a> to apply coupons and save more!
                </div>
                <?php endif; ?>

                <a href="checkout.php" class="btn btn-primary btn-lg w-100"><i class="fas fa-lock me-2"></i>Proceed to Checkout</a>
                <?php if (!isLoggedIn()): ?>
                    <small class="text-muted d-block text-center mt-2"><i class="fas fa-info-circle me-1"></i>Login for order tracking and coupon benefits</small>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<script>
function updateQty(productId, delta) {
    const input = document.getElementById('qty_' + productId);
    let val = parseInt(input.value) + delta;
    val = Math.max(1, Math.min(val, parseInt(input.max) || 99));
    input.value = val;
    fetch('ajax/cart.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'action=update&product_id=' + productId + '&quantity=' + val
    }).then(r => r.json()).then(data => { if (data.success) location.reload(); });
}


</script>

<?php include 'includes/footer.php'; ?>
