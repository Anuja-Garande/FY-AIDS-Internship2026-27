<?php
session_start();
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

if (!isAdminLoggedIn()) {
    redirect('login.php');
}

$db = Database::getInstance();

$search = $_GET['search'] ?? '';
$status_filter = $_GET['status'] ?? '';
$page = max(1, intval($_GET['page'] ?? 1));
$per_page = 15;
$offset = ($page - 1) * $per_page;

$where = "1=1";
$params = [];

if (!empty($search)) {
    $where .= " AND (o.order_number LIKE :search OR u.name LIKE :search2 OR u.email LIKE :search3)";
    $params[':search'] = "%$search%";
    $params[':search2'] = "%$search%";
    $params[':search3'] = "%$search%";
}
if ($status_filter !== '') {
    $where .= " AND o.order_status = :status";
    $params[':status'] = $status_filter;
}

$total = $db->query("SELECT COUNT(*) as count FROM orders o LEFT JOIN users u ON o.user_id=u.id WHERE $where", $params)->fetch()['count'];
$total_pages = max(1, ceil($total / $per_page));

$orders = $db->query("SELECT o.*, u.name as customer_name, u.email as customer_email,
    (SELECT COUNT(*) FROM order_items WHERE order_id=o.id) as item_count
    FROM orders o LEFT JOIN users u ON o.user_id=u.id WHERE $where ORDER BY o.id DESC LIMIT $per_page OFFSET $offset", $params)->fetchAll();

$pageTitle = 'Orders';
include __DIR__ . '/includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="fw-bold mb-0">Orders (<?= number_format($total) ?>)</h5>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-4"><input type="text" name="search" class="form-control" placeholder="Search by order #, name, email..." value="<?= sanitize($search) ?>"></div>
            <div class="col-md-2">
                <select name="status" class="form-select">
                    <option value="">All Status</option>
                    <option value="pending" <?= $status_filter === 'pending' ? 'selected' : '' ?>>Pending</option>
                    <option value="processing" <?= $status_filter === 'processing' ? 'selected' : '' ?>>Processing</option>
                    <option value="shipped" <?= $status_filter === 'shipped' ? 'selected' : '' ?>>Shipped</option>
                    <option value="delivered" <?= $status_filter === 'delivered' ? 'selected' : '' ?>>Delivered</option>
                    <option value="cancelled" <?= $status_filter === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                </select>
            </div>
            <div class="col-md-2"><button type="submit" class="btn btn-primary w-100"><i class="fas fa-search me-1"></i>Filter</button></div>
            <div class="col-md-2"><a href="<?= BASE_URL ?>/admin/orders.php" class="btn btn-outline-secondary w-100">Clear</a></div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr><th>Order #</th><th>Customer</th><th>Items</th><th>Total</th><th>Payment</th><th>Order Status</th><th>Date</th><th>Actions</th></tr>
                </thead>
                <tbody>
                    <?php if (empty($orders)): ?>
                    <tr><td colspan="8" class="text-center text-muted py-4">No orders found</td></tr>
                    <?php endif; ?>
                    <?php foreach ($orders as $order): ?>
                    <?php
                        $paymentStatusClass = match($order['payment_status'] ?? '') {
                            'completed' => 'success', 'pending' => 'warning', 'failed' => 'danger', 'refunded' => 'info', default => 'secondary'
                        };
                        $orderStatusClass = match($order['order_status'] ?? '') {
                            'delivered' => 'success', 'processing' => 'primary', 'shipped' => 'info', 'pending' => 'warning', 'cancelled' => 'danger', default => 'secondary'
                        };
                    ?>
                    <tr>
                        <td><a href="<?= BASE_URL ?>/admin/view_order.php?id=<?= $order['id'] ?>" class="fw-semibold text-primary">#<?= sanitize($order['order_number'] ?? $order['id']) ?></a></td>
                        <td>
                            <div class="fw-semibold"><?= sanitize($order['customer_name'] ?? 'Guest') ?></div>
                            <small class="text-muted"><?= sanitize($order['customer_email'] ?? '') ?></small>
                        </td>
                        <td><span class="badge bg-light text-dark"><?= $order['item_count'] ?> items</span></td>
                        <td class="fw-bold"><?= formatPrice($order['total_amount']) ?></td>
                        <td>
                            <div class="mb-1"><small class="text-muted"><?= sanitize($order['payment_method'] ?? 'N/A') ?></small></div>
                            <span class="badge bg-<?= $paymentStatusClass ?>"><?= sanitize($order['payment_status'] ?? 'N/A') ?></span>
                        </td>
                        <td><span class="badge bg-<?= $orderStatusClass ?>"><?= sanitize($order['order_status'] ?? 'N/A') ?></span></td>
                        <td><?= date('M d, Y', strtotime($order['created_at'])) ?></td>
                        <td><a href="<?= BASE_URL ?>/admin/view_order.php?id=<?= $order['id'] ?>" class="btn btn-sm btn-outline-primary"><i class="fas fa-eye"></i></a></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="d-flex justify-content-center p-3">
            <nav><ul class="pagination pagination-sm mb-0">
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <li class="page-item <?= $i === $page ? 'active' : '' ?>"><a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&status=<?= urlencode($status_filter) ?>"><?= $i ?></a></li>
                <?php endfor; ?>
            </ul></nav>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
