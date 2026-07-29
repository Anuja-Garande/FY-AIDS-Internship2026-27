<?php
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'includes/functions.php';
require_once 'includes/functions_product.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

$page_title = 'My Orders';
$db = Database::getInstance();
$user = getUser();

$status_filter = $_GET['status'] ?? '';
$page = max(1, (int)($_GET['page'] ?? 1));

$where = ["o.user_id = :uid"];
$params = [':uid' => $user['id']];

if ($status_filter) {
    $where[] = "o.order_status = :status";
    $params[':status'] = $status_filter;
}

$where_sql = implode(' AND ', $where);

$count = $db->fetch("SELECT COUNT(*) as cnt FROM orders o WHERE $where_sql", $params)['cnt'] ?? 0;
$per_page = 10;
$total_pages = max(1, ceil($count / $per_page));
$page = min($page, $total_pages);
$offset = ($page - 1) * $per_page;

$params[':limit'] = $per_page;
$params[':offset'] = $offset;
$orders = $db->fetchAll("SELECT o.*, (SELECT COUNT(*) FROM order_items WHERE order_id = o.id) as item_count FROM orders o WHERE $where_sql ORDER BY o.created_at DESC LIMIT :limit OFFSET :offset", $params);

$status_badges = [
    'pending' => 'warning',
    'confirmed' => 'info',
    'processing' => 'primary',
    'shipped' => 'info',
    'delivered' => 'success',
    'cancelled' => 'danger',
    'returned' => 'secondary',
];
?>
<?php include 'includes/header.php'; ?>

<style>
.order-card { border-radius: 12px; transition: all 0.3s; border-left: 4px solid transparent; color: var(--text-primary,#333); }
.order-card:hover { box-shadow: 0 4px 20px rgba(0,0,0,0.08); }
.order-card.status-pending { border-left-color: #ffc107; }
.order-card.status-confirmed { border-left-color: #17a2b8; }
.order-card.status-processing { border-left-color: #667eea; }
.order-card.status-shipped { border-left-color: #0dcaf0; }
.order-card.status-delivered { border-left-color: #198754; }
.order-card.status-cancelled { border-left-color: #dc3545; }
.filter-btn { border-radius: 20px; padding: 6px 16px; font-size: 0.85rem; }
.filter-btn.active { background: #667eea; color: white; border-color: #667eea; }
</style>

<div class="container py-4">
    <h2 class="fw-bold mb-4"><i class="fas fa-box me-2"></i>My Orders</h2>

    <div class="d-flex flex-wrap gap-2 mb-4">
        <a href="orders.php" class="btn btn-sm filter-btn <?= !$status_filter ? 'active' : '' ?>">All</a>
        <?php foreach ($status_badges as $status => $color): ?>
            <a href="orders.php?status=<?= $status ?>" class="btn btn-sm filter-btn <?= $status_filter === $status ? 'active' : '' ?>"><?= ucfirst($status) ?></a>
        <?php endforeach; ?>
    </div>

    <?php if (empty($orders)): ?>
    <div class="text-center py-5">
        <i class="fas fa-box-open fa-4x text-muted mb-3"></i>
        <h4>No orders found</h4>
        <p class="text-muted">You haven't placed any orders yet.</p>
        <a href="products.php" class="btn btn-primary">Start Shopping</a>
    </div>
    <?php else: ?>
    <?php foreach ($orders as $order): ?>
    <div class="card border-0 shadow-sm mb-3 order-card status-<?= $order['order_status'] ?>">
        <div class="card-body p-4">
            <div class="row align-items-center">
                <div class="col-md-3">
                    <small class="text-muted d-block">Order Number</small>
                    <strong class="text-primary"><?= htmlspecialchars($order['order_number']) ?></strong>
                </div>
                <div class="col-md-2">
                    <small class="text-muted d-block">Date</small>
                    <span><?= date('M d, Y', strtotime($order['created_at'])) ?></span>
                </div>
                <div class="col-md-2">
                    <small class="text-muted d-block">Items</small>
                    <span><?= $order['item_count'] ?> item<?= $order['item_count'] != 1 ? 's' : '' ?></span>
                </div>
                <div class="col-md-2">
                    <small class="text-muted d-block">Total</small>
                    <strong>₹<?= number_format($order['total_amount'], 0) ?></strong>
                </div>
                <div class="col-md-1">
                    <span class="badge bg-<?= $status_badges[$order['order_status']] ?? 'secondary' ?>"><?= ucfirst($order['order_status']) ?></span>
                </div>
                <div class="col-md-2 text-end">
                    <a href="order_detail.php?id=<?= $order['id'] ?>" class="btn btn-sm btn-outline-primary"><i class="fas fa-eye me-1"></i>View</a>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>

    <?php if ($total_pages > 1): ?>
    <nav class="mt-4">
        <ul class="pagination justify-content-center">
            <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                <a class="page-link" href="orders.php?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>">&laquo;</a>
            </li>
            <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
            <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                <a class="page-link" href="orders.php?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>"><?= $i ?></a>
            </li>
            <?php endfor; ?>
            <li class="page-item <?= $page >= $total_pages ? 'disabled' : '' ?>">
                <a class="page-link" href="orders.php?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>">&raquo;</a>
            </li>
        </ul>
    </nav>
    <?php endif; ?>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
