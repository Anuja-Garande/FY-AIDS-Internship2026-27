<?php
session_start();
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

if (!isAdminLoggedIn()) {
    redirect('login.php');
}

$db = Database::getInstance();
$id = intval($_GET['id'] ?? 0);

if (!$id) {
    flash('error', 'Invalid order ID.');
    redirect('orders.php');
}

$order = $db->query("SELECT o.*, u.name as customer_name, u.email as customer_email, u.phone as customer_phone FROM orders o LEFT JOIN users u ON o.user_id=u.id WHERE o.id=?", [$id])->fetch();

if (!$order) {
    flash('error', 'Order not found.');
    redirect('orders.php');
}

$orderItems = $db->query("SELECT oi.*, REPLACE(pi.image, 'products/', '') as product_image FROM order_items oi LEFT JOIN product_images pi ON pi.product_id=oi.product_id AND pi.is_primary=1 WHERE oi.order_id=?", [$id])->fetchAll();

$statusHistory = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        flash('error', 'Invalid request.');
    } else {
        if (isset($_POST['update_order_status'])) {
            $newStatus = $_POST['new_status'] ?? '';
            $validStatuses = ['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled'];
            if (in_array($newStatus, $validStatuses)) {
                $db->update("UPDATE orders SET order_status=? WHERE id=?", [$newStatus, $id]);
                flash('success', 'Order status updated!');
            }
        }
        if (isset($_POST['update_payment_status'])) {
            $newPayStatus = $_POST['new_payment_status'] ?? '';
            $validPay = ['pending', 'paid', 'failed', 'refunded'];
            if (in_array($newPayStatus, $validPay)) {
                $db->update("UPDATE orders SET payment_status=? WHERE id=?", [$newPayStatus, $id]);
                flash('success', 'Payment status updated!');
            }
        }
        if (isset($_POST['update_tracking'])) {
            $tracking = trim($_POST['tracking_number'] ?? '');
            $db->update("UPDATE orders SET tracking_number=? WHERE id=?", [$tracking, $id]);
            flash('success', 'Tracking number updated!');
        }
    }
    unset($_SESSION['csrf_token']);
    redirect('view_order.php?id=' . $id);
}

$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
$pageTitle = 'View Order #' . ($order['order_number'] ?? $order['id']);
include __DIR__ . '/includes/header.php';

$paymentStatusClass = match($order['payment_status'] ?? '') {
    'completed' => 'success', 'pending' => 'warning', 'failed' => 'danger', 'refunded' => 'info', default => 'secondary'
};
$orderStatusClass = match($order['order_status'] ?? '') {
    'delivered' => 'success', 'processing' => 'primary', 'shipped' => 'info', 'pending' => 'warning', 'cancelled' => 'danger', default => 'secondary'
};
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h5 class="fw-bold mb-0">Order #<?= sanitize($order['order_number'] ?? $order['id']) ?></h5>
        <small class="text-muted">Placed on <?= date('F d, Y h:i A', strtotime($order['created_at'])) ?></small>
    </div>
    <div class="d-flex gap-2">
        <button onclick="window.print()" class="btn btn-outline-primary"><i class="fas fa-print me-1"></i>Print Invoice</button>
        <a href="<?= BASE_URL ?>/admin/orders.php" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Back</a>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card mb-4">
            <div class="card-header"><h6 class="fw-bold mb-0">Order Items</h6></div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead><tr><th>Product</th><th>Price</th><th>Qty</th><th>Subtotal</th></tr></thead>
                        <tbody>
                            <?php foreach ($orderItems as $item): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <?php if (!empty($item['product_image'])): ?>
                                        <img src="<?= BASE_URL ?>assets/uploads/products/<?= sanitize($item['product_image']) ?>" width="50" height="50" style="object-fit:cover;border-radius:8px;" alt="">
                                        <?php else: ?>
                                        <div class="bg-light d-flex align-items-center justify-content-center" style="width:50px;height:50px;border-radius:8px;"><i class="fas fa-image text-muted"></i></div>
                                        <?php endif; ?>
                                        <div>
                                            <div class="fw-semibold"><?= sanitize($item['product_name'] ?? 'Product Removed') ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td><?= formatPrice($item['product_price']) ?></td>
                                <td><?= $item['quantity'] ?></td>
                                <td class="fw-bold"><?= formatPrice($item['product_price'] * $item['quantity']) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr><td colspan="3" class="text-end fw-semibold">Subtotal</td><td class="fw-bold"><?= formatPrice($order['subtotal'] ?? 0) ?></td></tr>
                            <tr><td colspan="3" class="text-end fw-semibold">Shipping</td><td class="fw-bold"><?= formatPrice($order['shipping_charge'] ?? 0) ?></td></tr>
                            
                            <?php if (!empty($order['discount_amount'])): ?>
                            <tr><td colspan="3" class="text-end fw-semibold text-success">Discount</td><td class="fw-bold text-success">-<?= formatPrice($order['discount_amount']) ?></td></tr>
                            <?php endif; ?>
                            <tr class="table-active"><td colspan="3" class="text-end fw-bold">Total</td><td class="fw-bold fs-5"><?= formatPrice($order['total_amount']) ?></td></tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <?php if (!empty($statusHistory)): ?>
        <div class="card mb-4">
            <div class="card-header"><h6 class="fw-bold mb-0">Order Timeline</h6></div>
            <div class="card-body">
                <?php foreach ($statusHistory as $idx => $hist): ?>
                <div class="d-flex mb-3 <?= $idx === count($statusHistory) - 1 ? '' : 'border-start border-2 ms-2 ps-3' ?>">
                    <div class="me-3">
                        <span class="badge bg-<?= match($hist['status']) { 'delivered'=>'success','processing'=>'primary','shipped'=>'info','pending'=>'warning','cancelled'=>'danger',default=>'secondary' } ?>"><?= ucfirst($hist['status']) ?></span>
                    </div>
                    <div>
                        <div class="fw-semibold" style="font-size:13px;"><?= date('M d, Y h:i A', strtotime($hist['created_at'])) ?></div>
                        <?php if (!empty($hist['note'])): ?><small class="text-muted"><?= sanitize($hist['note']) ?></small><?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <div class="col-lg-4">
        <div class="card mb-4">
            <div class="card-header"><h6 class="fw-bold mb-0">Customer Info</h6></div>
            <div class="card-body">
                <p class="mb-1"><strong>Name:</strong> <?= sanitize($order['customer_name'] ?? 'Guest') ?></p>
                <p class="mb-1"><strong>Email:</strong> <?= sanitize($order['customer_email'] ?? 'N/A') ?></p>
                <p class="mb-1"><strong>Phone:</strong> <?= sanitize($order['customer_phone'] ?? 'N/A') ?></p>
                <hr>
                <h6 class="fw-bold" style="font-size:14px;">Shipping Address</h6>
                <p class="mb-0 text-muted" style="font-size:13px;"><?= nl2br(sanitize($order['shipping_address'] ?? 'N/A')) ?></p>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header"><h6 class="fw-bold mb-0">Update Order Status</h6></div>
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?= sanitize($_SESSION['csrf_token']) ?>">
                    <input type="hidden" name="update_order_status" value="1">
                    <div class="mb-3">
                        <select name="new_status" class="form-select">
                            <?php foreach (['pending','processing','shipped','delivered','cancelled'] as $s): ?>
                            <option value="<?= $s ?>" <?= ($order['order_status'] ?? '') === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3"><textarea name="status_note" class="form-control" rows="2" placeholder="Status note (optional)"></textarea></div>
                    <button type="submit" class="btn btn-primary w-100"><i class="fas fa-save me-1"></i>Update Status</button>
                </form>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header"><h6 class="fw-bold mb-0">Update Payment Status</h6></div>
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?= sanitize($_SESSION['csrf_token']) ?>">
                    <input type="hidden" name="update_payment_status" value="1">
                    <div class="mb-3">
                        <select name="new_payment_status" class="form-select">
                            <?php foreach (['pending','paid','failed','refunded'] as $ps): ?>
                            <option value="<?= $ps ?>" <?= ($order['payment_status'] ?? '') === $ps ? 'selected' : '' ?>><?= ucfirst($ps) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-warning w-100"><i class="fas fa-save me-1"></i>Update Payment</button>
                </form>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header"><h6 class="fw-bold mb-0">Tracking Number</h6></div>
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?= sanitize($_SESSION['csrf_token']) ?>">
                    <input type="hidden" name="update_tracking" value="1">
                    <div class="mb-3"><input type="text" name="tracking_number" class="form-control" placeholder="Enter tracking number" value="<?= sanitize($order['tracking_number'] ?? '') ?>"></div>
                    <button type="submit" class="btn btn-info w-100"><i class="fas fa-truck me-1"></i>Update Tracking</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
