<?php
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'includes/functions.php';
require_once 'includes/functions_product.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

$page_title = 'Order Confirmed';
$db = Database::getInstance();
$user = getUser();
$order_id = (int)($_GET['order'] ?? 0);

$order = null;
if ($order_id) {
    $order = $db->fetch("SELECT * FROM orders WHERE id = :id AND user_id = :uid", [':id' => $order_id, ':uid' => $user['id']]);
}

if (!$order) {
    flash('error', 'Order not found.');
    redirect('orders.php');
}

                    $order_items = $db->fetchAll("SELECT oi.*, p.image_url, REPLACE((SELECT image FROM product_images WHERE product_id = oi.product_id AND is_primary = 1 LIMIT 1), 'products/', '') AS product_image FROM order_items oi LEFT JOIN products p ON oi.product_id = p.id WHERE oi.order_id = :oid", [':oid' => $order['id']]);
$delivery_date = date('M d, Y', strtotime($order['created_at'] . ' + 5 days'));
?>
<?php include 'includes/header.php'; ?>

<style>
.success-check { width: 100px; height: 100px; border-radius: 50%; background: linear-gradient(135deg, #00b09b, #96c93d); display: flex; align-items: center; justify-content: center; margin: 0 auto 24px; animation: scaleIn 0.5s ease; }
.success-check i { font-size: 48px; color: white; animation: checkmark 0.5s ease 0.3s both; }
@keyframes scaleIn { from { transform: scale(0); } to { transform: scale(1); } }
@keyframes checkmark { from { opacity: 0; transform: scale(0) rotate(-45deg); } to { opacity: 1; transform: scale(1) rotate(0deg); } }
.order-card { border-radius: 16px; overflow: hidden; }
</style>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8 text-center">
            <div class="success-check">
                <i class="fas fa-check"></i>
            </div>
            <h2 class="fw-bold text-success mb-2">Order Placed Successfully!</h2>
            <p class="text-muted mb-4">Thank you for your order. We'll send you a confirmation email shortly.</p>

            <div class="card border-0 shadow-sm order-card mb-4">
                <div class="card-body p-4">
                    <div class="row text-start">
                        <div class="col-md-6 mb-3">
                            <small class="text-muted d-block">Order Number</small>
                            <strong class="fs-5 text-primary"><?= htmlspecialchars($order['order_number']) ?></strong>
                        </div>
                        <div class="col-md-6 mb-3">
                            <small class="text-muted d-block">Order Date</small>
                            <strong><?= date('M d, Y \a\t h:i A', strtotime($order['created_at'])) ?></strong>
                        </div>
                        <div class="col-md-6 mb-3">
                            <small class="text-muted d-block">Payment Method</small>
                            <strong class="text-capitalize"><?= htmlspecialchars(str_replace('_', ' ', $order['payment_method'])) ?></strong>
                        </div>
                        <div class="col-md-6 mb-3">
                            <small class="text-muted d-block">Estimated Delivery</small>
                            <strong class="text-success"><?= $delivery_date ?></strong>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h6 class="fw-bold mb-0">Order Summary</h6>
                </div>
                <div class="card-body p-0">
                    <?php foreach ($order_items as $item): ?>
                    <div class="d-flex align-items-center p-3 border-bottom">
                        <img src="<?= htmlspecialchars($item['image_url'] ?: (BASE_URL . 'assets/uploads/products/' . ($item['product_image'] ?? 'default.png'))) ?>" class="rounded me-3" style="width:50px;height:50px;object-fit:cover;" alt="">
                        <div class="flex-grow-1 text-start">
                            <strong class="d-block"><?= htmlspecialchars($item['product_name']) ?></strong>
                            <small class="text-muted">Qty: <?= $item['quantity'] ?> &times; ₹<?= number_format($item['product_price'], 0) ?></small>
                        </div>
                        <strong>₹<?= number_format($item['total'], 0) ?></strong>
                    </div>
                    <?php endforeach; ?>
                    <div class="p-3">
                        <div class="d-flex justify-content-between mb-1"><span>Subtotal</span><span>₹<?= number_format($order['subtotal'], 0) ?></span></div>
                        <?php if ($order['discount_amount'] > 0): ?>
                        <div class="d-flex justify-content-between mb-1 text-success"><span>Discount</span><span>-₹<?= number_format($order['discount_amount'], 0) ?></span></div>
                        <?php endif; ?>
                        <div class="d-flex justify-content-between mb-1"><span>Shipping</span><span><?= $order['shipping_charge'] == 0 ? 'FREE' : '₹' . number_format($order['shipping_charge'], 0) ?></span></div>
                        <div class="d-flex justify-content-between fw-bold fs-5 mt-2 pt-2 border-top"><span>Total</span><span class="text-primary">₹<?= number_format($order['total_amount'], 0) ?></span></div>
                    </div>
                </div>
            </div>

            <div class="d-flex gap-3 justify-content-center flex-wrap">
                <a href="orders.php" class="btn btn-outline-primary btn-lg"><i class="fas fa-box me-2"></i>View My Orders</a>
                <a href="products.php" class="btn btn-primary btn-lg"><i class="fas fa-shopping-bag me-2"></i>Continue Shopping</a>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
