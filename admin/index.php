<?php
session_start();
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

if (!isAdminLoggedIn()) {
    redirect('login.php');
}

$db = Database::getInstance();

$totalUsers = ($row = $db->fetch("SELECT COUNT(*) as count FROM users")) ? $row['count'] : 0;
$totalProducts = ($row = $db->fetch("SELECT COUNT(*) as count FROM products")) ? $row['count'] : 0;
$totalOrders = ($row = $db->fetch("SELECT COUNT(*) as count FROM orders")) ? $row['count'] : 0;
$totalRevenue = ($row = $db->fetch("SELECT COALESCE(SUM(total_amount),0) as total FROM orders WHERE payment_status='paid'")) ? $row['total'] : 0;
$pendingOrders = ($row = $db->fetch("SELECT COUNT(*) as count FROM orders WHERE order_status='pending'")) ? $row['count'] : 0;
$deliveredOrders = ($row = $db->fetch("SELECT COUNT(*) as count FROM orders WHERE order_status='delivered'")) ? $row['count'] : 0;

$monthlyRevenue = $db->fetchAll("SELECT DATE_FORMAT(created_at,'%Y-%m') as month, COALESCE(SUM(total_amount),0) as revenue FROM orders WHERE payment_status='paid' AND created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH) GROUP BY DATE_FORMAT(created_at,'%Y-%m') ORDER BY month ASC");

$ordersByStatus = $db->fetchAll("SELECT order_status, COUNT(*) as count FROM orders GROUP BY order_status");

$topProducts = $db->fetchAll("SELECT p.name, COALESCE(SUM(oi.quantity),0) as total_sold FROM order_items oi JOIN products p ON oi.product_id=p.id GROUP BY oi.product_id ORDER BY total_sold DESC LIMIT 5");

$latestOrders = $db->fetchAll("SELECT o.*, u.name as customer_name FROM orders o LEFT JOIN users u ON o.user_id=u.id ORDER BY o.created_at DESC LIMIT 10");

$recentUsers = $db->fetchAll("SELECT * FROM users ORDER BY created_at DESC LIMIT 5");

$pageTitle = 'Dashboard';
$monthLabels = array_column($monthlyRevenue, 'month');
$monthRevenue = array_column($monthlyRevenue, 'revenue');
$statusLabels = array_column($ordersByStatus, 'order_status');
$statusCounts = array_column($ordersByStatus, 'count');
$topProdNames = array_column($topProducts, 'name');
$topProdSales = array_column($topProducts, 'total_sold');

include __DIR__ . '/includes/header.php';
?>

<div class="row g-3 mb-4">
    <div class="col-xl-2 col-md-4 col-sm-6">
        <div class="stat-card" style="border-color:#4e73df;">
            <div class="d-flex align-items-center justify-content-between">
                <div class="stat-info"><h3><?= number_format($totalUsers) ?></h3><p>Total Users</p></div>
                <div class="stat-icon" style="background:rgba(78,115,223,0.1);color:#4e73df;"><i class="fas fa-users"></i></div>
            </div>
        </div>
    </div>
    <div class="col-xl-2 col-md-4 col-sm-6">
        <div class="stat-card" style="border-color:#1cc88a;">
            <div class="d-flex align-items-center justify-content-between">
                <div class="stat-info"><h3><?= number_format($totalProducts) ?></h3><p>Total Products</p></div>
                <div class="stat-icon" style="background:rgba(28,200,138,0.1);color:#1cc88a;"><i class="fas fa-box"></i></div>
            </div>
        </div>
    </div>
    <div class="col-xl-2 col-md-4 col-sm-6">
        <div class="stat-card" style="border-color:#36b9cc;">
            <div class="d-flex align-items-center justify-content-between">
                <div class="stat-info"><h3><?= number_format($totalOrders) ?></h3><p>Total Orders</p></div>
                <div class="stat-icon" style="background:rgba(54,185,204,0.1);color:#36b9cc;"><i class="fas fa-shopping-cart"></i></div>
            </div>
        </div>
    </div>
    <div class="col-xl-2 col-md-4 col-sm-6">
        <div class="stat-card" style="border-color:#f6c23e;">
            <div class="d-flex align-items-center justify-content-between">
                <div class="stat-info"><h3><?= formatPrice($totalRevenue) ?></h3><p>Total Revenue</p></div>
                <div class="stat-icon" style="background:rgba(246,194,62,0.1);color:#f6c23e;"><i class="fas fa-indian-rupee-sign"></i></div>
            </div>
        </div>
    </div>
    <div class="col-xl-2 col-md-4 col-sm-6">
        <div class="stat-card" style="border-color:#e74a3b;">
            <div class="d-flex align-items-center justify-content-between">
                <div class="stat-info"><h3><?= number_format($pendingOrders) ?></h3><p>Pending Orders</p></div>
                <div class="stat-icon" style="background:rgba(231,74,59,0.1);color:#e74a3b;"><i class="fas fa-clock"></i></div>
            </div>
        </div>
    </div>
    <div class="col-xl-2 col-md-4 col-sm-6">
        <div class="stat-card" style="border-color:#1cc88a;">
            <div class="d-flex align-items-center justify-content-between">
                <div class="stat-info"><h3><?= number_format($deliveredOrders) ?></h3><p>Delivered</p></div>
                <div class="stat-icon" style="background:rgba(28,200,138,0.1);color:#1cc88a;"><i class="fas fa-check-circle"></i></div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-xl-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold">Monthly Revenue</h6>
            </div>
            <div class="card-body"><canvas id="revenueChart" height="100"></canvas></div>
        </div>
    </div>
    <div class="col-xl-4">
        <div class="card">
            <div class="card-header"><h6 class="mb-0 fw-bold">Orders by Status</h6></div>
            <div class="card-body"><canvas id="statusChart" height="200"></canvas></div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-xl-6">
        <div class="card">
            <div class="card-header"><h6 class="mb-0 fw-bold">Top 5 Products</h6></div>
            <div class="card-body"><canvas id="topProductsChart" height="150"></canvas></div>
        </div>
    </div>
    <div class="col-xl-6">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold">Recent Users</h6>
                <a href="<?= BASE_URL ?>/admin/users.php" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body p-0">
                <table class="table table-hover">
                    <thead><tr><th>Name</th><th>Email</th><th>Joined</th></tr></thead>
                    <tbody>
                        <?php foreach ($recentUsers as $user): ?>
                        <tr>
                            <td class="fw-semibold"><?= sanitize($user['name']) ?></td>
                            <td><?= sanitize($user['email']) ?></td>
                            <td><?= date('M d, Y', strtotime($user['created_at'])) ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($recentUsers)): ?>
                        <tr><td colspan="3" class="text-center text-muted py-3">No users found</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-bold">Latest Orders</h6>
        <a href="<?= BASE_URL ?>/admin/orders.php" class="btn btn-sm btn-outline-primary">View All</a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr><th>Order #</th><th>Customer</th><th>Total</th><th>Payment</th><th>Status</th><th>Date</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($latestOrders as $order): ?>
                    <tr>
                        <td><a href="<?= BASE_URL ?>/admin/view_order.php?id=<?= $order['id'] ?>" class="fw-semibold text-primary">#<?= sanitize($order['order_number'] ?? $order['id']) ?></a></td>
                        <td><?= sanitize($order['customer_name'] ?? 'Guest') ?></td>
                        <td class="fw-bold"><?= formatPrice($order['total_amount']) ?></td>
                        <td><span class="badge bg-<?= ($order['payment_status'] ?? '') === 'paid' ? 'success' : (($order['payment_status'] ?? '') === 'pending' ? 'warning' : 'secondary') ?>"><?= sanitize($order['payment_status'] ?? 'N/A') ?></span></td>
                        <td><span class="badge bg-<?= ($order['order_status'] ?? '') === 'delivered' ? 'success' : (($order['order_status'] ?? '') === 'pending' ? 'warning' : (($order['order_status'] ?? '') === 'cancelled' ? 'danger' : 'info')) ?>"><?= sanitize($order['order_status'] ?? 'N/A') ?></span></td>
                        <td><?= date('M d, Y', strtotime($order['created_at'])) ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($latestOrders)): ?>
                    <tr><td colspan="6" class="text-center text-muted py-3">No orders found</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <a href="<?= BASE_URL ?>/admin/add_product.php" class="btn btn-primary w-100 py-3"><i class="fas fa-plus me-2"></i>Add Product</a>
    </div>
    <div class="col-md-3">
        <a href="<?= BASE_URL ?>/admin/orders.php" class="btn btn-success w-100 py-3"><i class="fas fa-shopping-cart me-2"></i>Manage Orders</a>
    </div>
    <div class="col-md-3">
        <a href="<?= BASE_URL ?>/admin/coupons.php" class="btn btn-warning w-100 py-3"><i class="fas fa-ticket-alt me-2"></i>Manage Coupons</a>
    </div>
    <div class="col-md-3">
        <a href="<?= BASE_URL ?>/admin/reports.php" class="btn btn-info w-100 py-3"><i class="fas fa-chart-bar me-2"></i>View Reports</a>
    </div>
</div>

<?php
$extraScripts = '<script>
const monthLabels = ' . json_encode($monthLabels) . ';
const monthRevenue = ' . json_encode($monthRevenue) . ';
const statusLabels = ' . json_encode($statusLabels) . ';
const statusCounts = ' . json_encode($statusCounts) . ';
const topProdNames = ' . json_encode($topProdNames) . ';
const topProdSales = ' . json_encode($topProdSales) . ';

new Chart(document.getElementById("revenueChart"), {
    type: "line",
    data: {
        labels: monthLabels,
        datasets: [{
            label: "Revenue",
            data: monthRevenue,
            borderColor: "#e94560",
            backgroundColor: "rgba(233,69,96,0.1)",
            fill: true,
            tension: 0.4,
            pointRadius: 4,
            pointBackgroundColor: "#e94560"
        }]
    },
    options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
});

new Chart(document.getElementById("statusChart"), {
    type: "doughnut",
    data: {
        labels: statusLabels,
        datasets: [{ data: statusCounts, backgroundColor: ["#f6c23e","#1cc88a","#e74a3b","#36b9cc","#4e73df","#858796"] }]
    },
    options: { responsive: true, plugins: { legend: { position: "bottom" } } }
});

new Chart(document.getElementById("topProductsChart"), {
    type: "bar",
    data: {
        labels: topProdNames,
        datasets: [{ label: "Units Sold", data: topProdSales, backgroundColor: "#4e73df" }]
    },
    options: { responsive: true, indexAxis: "y", plugins: { legend: { display: false } } }
});
</script>';

include __DIR__ . '/includes/footer.php';
?>
