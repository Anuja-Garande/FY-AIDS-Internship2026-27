<?php
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'includes/functions.php';
require_once 'includes/functions_product.php';

if (!isLoggedIn()) { redirect('login.php'); }

$page_title = 'Order Details';
$db = Database::getInstance();
$user = getUser();
$order_id = (int)($_GET['id'] ?? 0);

$order = $db->fetch("SELECT * FROM orders WHERE id = :id AND user_id = :uid", [':id' => $order_id, ':uid' => $user['id']]);
if (!$order) { flash('error', 'Order not found.'); redirect('orders.php'); }

                    $order_items = $db->fetchAll("SELECT oi.*, p.image_url, REPLACE((SELECT image FROM product_images WHERE product_id = oi.product_id AND is_primary = 1 LIMIT 1), 'products/', '') AS product_image FROM order_items oi LEFT JOIN products p ON oi.product_id = p.id WHERE oi.order_id = :oid", [':oid' => $order['id']]);

$status_timeline = ['pending', 'confirmed', 'processing', 'shipped', 'delivered'];
$current_idx = array_search($order['order_status'], $status_timeline);
if ($current_idx === false) $current_idx = -1;

$delivery_date = date('M d, Y', strtotime($order['created_at'] . ' + 5 days'));
?>
<?php include 'includes/header.php'; ?>

<style>
.timeline { position: relative; padding: 0; list-style: none; }
.timeline::before { content: ''; position: absolute; left: 20px; top: 0; bottom: 0; width: 3px; background: #e9ecef; }
.timeline-item { position: relative; padding-left: 50px; padding-bottom: 30px; }
.timeline-item:last-child { padding-bottom: 0; }
.timeline-dot { position: absolute; left: 10px; top: 2px; width: 24px; height: 24px; border-radius: 50%; background: #e9ecef; display: flex; align-items: center; justify-content: center; z-index: 1; }
.timeline-dot.completed { background: #198754; color: white; }
.timeline-dot.current { background: #667eea; color: white; animation: pulse 2s infinite; }
@keyframes pulse { 0%, 100% { box-shadow: 0 0 0 0 rgba(102,126,234,0.4); } 50% { box-shadow: 0 0 0 8px rgba(102,126,234,0); } }
.order-item { display: flex; align-items: center; gap: 16px; padding: 12px 0; border-bottom: 1px solid #f0f0f0; }
.order-item:last-child { border-bottom: none; }
.order-item img { width: 70px; height: 70px; object-fit: cover; border-radius: 8px; }
</style>

<div class="container py-4">
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="orders.php" class="text-decoration-none">My Orders</a></li>
            <li class="breadcrumb-item active"><?= htmlspecialchars($order['order_number']) ?></li>
        </ol>
    </nav>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1">Order <?= htmlspecialchars($order['order_number']) ?></h4>
            <small class="text-muted">Placed on <?= date('M d, Y \a\t h:i A', strtotime($order['created_at'])) ?></small>
        </div>
        <div class="d-flex gap-2">
            <button onclick="window.print()" class="btn btn-outline-primary btn-sm"><i class="fas fa-print me-1"></i>Print Invoice</button>
            <a href="orders.php" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>Back</a>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h6 class="fw-bold mb-0"><i class="fas fa-shipping-fast me-2"></i>Order Status</h6>
                </div>
                <div class="card-body">
                    <ul class="timeline">
                        <?php foreach ($status_timeline as $idx => $status): ?>
                        <li class="timeline-item">
                            <div class="timeline-dot <?= $idx < $current_idx ? 'completed' : ($idx == $current_idx ? 'current' : '') ?>">
                                <?php if ($idx < $current_idx): ?>
                                    <i class="fas fa-check" style="font-size:0.7rem;"></i>
                                <?php endif; ?>
                            </div>
                            <div>
                                <strong class="<?= $idx <= $current_idx ? '' : 'text-muted' ?>"><?= ucfirst($status) ?></strong>
                                <?php if ($idx == $current_idx): ?>
                                    <span class="badge bg-primary ms-2">Current</span>
                                <?php endif; ?>
                                <?php if ($idx <= $current_idx): ?>
                                    <small class="d-block text-muted">Completed</small>
                                <?php endif; ?>
                            </div>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h6 class="fw-bold mb-0"><i class="fas fa-box me-2"></i>Order Items</h6>
                </div>
                <div class="card-body p-3">
                    <?php foreach ($order_items as $item): ?>
                    <div class="order-item">
                        <img src="<?= htmlspecialchars($item['image_url'] ?: (BASE_URL . 'assets/uploads/products/' . ($item['product_image'] ?? 'default.png'))) ?>" alt="">
                        <div class="flex-grow-1">
                            <a href="product.php?id=<?= $item['product_id'] ?>" class="text-decoration-none fw-semibold text-dark"><?= htmlspecialchars($item['product_name']) ?></a>
                            <small class="d-block text-muted">Qty: <?= $item['quantity'] ?> &times; ₹<?= number_format($item['product_price'], 0) ?></small>
                        </div>
                        <strong>₹<?= number_format($item['total'], 0) ?></strong>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h6 class="fw-bold mb-0"><i class="fas fa-map-marker-alt me-2"></i>Shipping Address</h6>
                </div>
                <div class="card-body">
                    <p class="mb-0">
                        <strong><?= htmlspecialchars($order['shipping_name']) ?></strong><br>
                        <?= htmlspecialchars($order['shipping_address']) ?><br>
                        <?= htmlspecialchars($order['shipping_city'] . ', ' . $order['shipping_state'] . ' - ' . $order['shipping_pincode']) ?><br>
                        <small class="text-muted"><?= htmlspecialchars($order['shipping_email']) ?> | <?= htmlspecialchars($order['shipping_phone']) ?></small>
                    </p>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-4" style="position:sticky;top:80px;">
                <div class="card-body p-4">
                    <h6 class="fw-bold mb-3">Order Summary</h6>
                    <div class="d-flex justify-content-between mb-2"><span>Subtotal</span><span>₹<?= number_format($order['subtotal'], 0) ?></span></div>
                    <?php if ($order['discount_amount'] > 0): ?>
                    <div class="d-flex justify-content-between mb-2 text-success"><span>Discount</span><span>-₹<?= number_format($order['discount_amount'], 0) ?></span></div>
                    <?php endif; ?>
                    <div class="d-flex justify-content-between mb-2"><span>Shipping</span><span><?= $order['shipping_charge'] == 0 ? 'FREE' : '₹' . number_format($order['shipping_charge'], 0) ?></span></div>
                    <div class="d-flex justify-content-between fw-bold fs-5 mt-2 pt-2 border-top"><span>Total</span><span class="text-primary">₹<?= number_format($order['total_amount'], 0) ?></span></div>

                    <hr>

                    <h6 class="fw-bold mb-2">Payment Info</h6>
                    <p class="mb-1"><small class="text-muted">Method:</small> <strong class="text-capitalize"><?= htmlspecialchars(str_replace('_', ' ', $order['payment_method'])) ?></strong></p>
                    <p class="mb-1"><small class="text-muted">Status:</small> <span class="badge bg-<?= $order['payment_status'] === 'paid' ? 'success' : 'warning' ?>"><?= ucfirst($order['payment_status']) ?></span></p>
                    <p class="mb-0"><small class="text-muted">Est. Delivery:</small> <strong class="text-success"><?= $delivery_date ?></strong></p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
