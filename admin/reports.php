<?php
session_start();
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

if (!isAdminLoggedIn()) {
    redirect('login.php');
}

$db = Database::getInstance();

$date_from = $_GET['date_from'] ?? date('Y-m-01');
$date_to = $_GET['date_to'] ?? date('Y-m-d');
$period = $_GET['period'] ?? 'daily';

$date_from .= ' 00:00:00';
$date_to .= ' 23:59:59';

$totalSales = $db->query("SELECT COALESCE(SUM(total_amount),0) as total FROM orders WHERE payment_status='paid' AND created_at BETWEEN ? AND ?", [$date_from, $date_to])->fetch()['total'];
$totalOrders = $db->query("SELECT COUNT(*) as count FROM orders WHERE created_at BETWEEN ? AND ?", [$date_from, $date_to])->fetch()['count'];
$completedOrders = $db->query("SELECT COUNT(*) as count FROM orders WHERE payment_status='paid' AND created_at BETWEEN ? AND ?", [$date_from, $date_to])->fetch()['count'];
$avgOrderValue = $completedOrders > 0 ? $totalSales / $completedOrders : 0;

$salesData = $db->query("SELECT DATE(created_at) as date, COALESCE(SUM(total_amount),0) as revenue, COUNT(*) as orders FROM orders WHERE payment_status='paid' AND created_at BETWEEN ? AND ? GROUP BY DATE(created_at) ORDER BY date ASC", [$date_from, $date_to])->fetchAll();

$bestSellingProducts = $db->query("SELECT p.id, p.name, p.price, SUM(oi.quantity) as total_sold, SUM(oi.quantity * oi.product_price) as total_revenue FROM order_items oi JOIN products p ON oi.product_id=p.id JOIN orders o ON oi.order_id=o.id WHERE o.payment_status='paid' AND o.created_at BETWEEN ? AND ? GROUP BY oi.product_id ORDER BY total_sold DESC LIMIT 10", [$date_from, $date_to])->fetchAll();

$topCustomers = $db->query("SELECT u.id, u.name, u.email, COUNT(o.id) as order_count, COALESCE(SUM(o.total_amount),0) as total_spent FROM orders o JOIN users u ON o.user_id=u.id WHERE o.payment_status='paid' AND o.created_at BETWEEN ? AND ? GROUP BY o.user_id ORDER BY total_spent DESC LIMIT 10", [$date_from, $date_to])->fetchAll();

$pageTitle = 'Reports';
include __DIR__ . '/includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="fw-bold mb-0">Sales Reports</h5>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-3"><label class="form-label fw-semibold" style="font-size:13px;">Date From</label><input type="date" name="date_from" class="form-control" value="<?= sanitize($_GET['date_from'] ?? date('Y-m-01')) ?>"></div>
            <div class="col-md-3"><label class="form-label fw-semibold" style="font-size:13px;">Date To</label><input type="date" name="date_to" class="form-control" value="<?= sanitize($_GET['date_to'] ?? date('Y-m-d')) ?>"></div>
            <div class="col-md-2"><label class="form-label fw-semibold" style="font-size:13px;">Period</label><select name="period" class="form-select"><option value="daily" <?= $period === 'daily' ? 'selected' : '' ?>>Daily</option><option value="weekly" <?= $period === 'weekly' ? 'selected' : '' ?>>Weekly</option><option value="monthly" <?= $period === 'monthly' ? 'selected' : '' ?>>Monthly</option></select></div>
            <div class="col-md-2"><button type="submit" class="btn btn-primary w-100"><i class="fas fa-filter me-1"></i>Apply</button></div>
            <div class="col-md-2"><a href="<?= BASE_URL ?>/admin/reports.php" class="btn btn-outline-secondary w-100">Reset</a></div>
        </form>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="stat-card" style="border-color:#1cc88a;">
            <div class="d-flex align-items-center justify-content-between">
                <div class="stat-info"><h3><?= formatPrice($totalSales) ?></h3><p>Total Revenue</p></div>
                <div class="stat-icon" style="background:rgba(28,200,138,0.1);color:#1cc88a;"><i class="fas fa-indian-rupee-sign"></i></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card" style="border-color:#4e73df;">
            <div class="d-flex align-items-center justify-content-between">
                <div class="stat-info"><h3><?= number_format($totalOrders) ?></h3><p>Total Orders</p></div>
                <div class="stat-icon" style="background:rgba(78,115,223,0.1);color:#4e73df;"><i class="fas fa-shopping-cart"></i></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card" style="border-color:#f6c23e;">
            <div class="d-flex align-items-center justify-content-between">
                <div class="stat-info"><h3><?= number_format($completedOrders) ?></h3><p>Completed</p></div>
                <div class="stat-icon" style="background:rgba(246,194,62,0.1);color:#f6c23e;"><i class="fas fa-check-circle"></i></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card" style="border-color:#36b9cc;">
            <div class="d-flex align-items-center justify-content-between">
                <div class="stat-info"><h3><?= formatPrice($avgOrderValue) ?></h3><p>Avg. Order Value</p></div>
                <div class="stat-icon" style="background:rgba(54,185,204,0.1);color:#36b9cc;"><i class="fas fa-chart-line"></i></div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0">Revenue Trend</h6>
                <button class="btn btn-sm btn-outline-primary" onclick="window.print()"><i class="fas fa-print me-1"></i>Print</button>
            </div>
            <div class="card-body"><canvas id="revenueChart" height="120"></canvas></div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-header"><h6 class="fw-bold mb-0">Top Customers</h6></div>
            <div class="card-body p-0">
                <table class="table table-sm table-hover mb-0">
                    <thead><tr><th>Customer</th><th>Orders</th><th>Spent</th></tr></thead>
                    <tbody>
                        <?php foreach ($topCustomers as $cust): ?>
                        <tr>
                            <td>
                                <div class="fw-semibold" style="font-size:13px;"><?= sanitize($cust['name']) ?></div>
                                <small class="text-muted"><?= sanitize($cust['email']) ?></small>
                            </td>
                            <td><span class="badge bg-info"><?= $cust['order_count'] ?></span></td>
                            <td class="fw-bold" style="font-size:13px;"><?= formatPrice($cust['total_spent']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($topCustomers)): ?>
                        <tr><td colspan="3" class="text-center text-muted py-3">No data</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header"><h6 class="fw-bold mb-0">Best Selling Products</h6></div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead><tr><th>#</th><th>Product</th><th>Price</th><th>Sold</th><th>Revenue</th></tr></thead>
                <tbody>
                    <?php if (empty($bestSellingProducts)): ?>
                    <tr><td colspan="5" class="text-center text-muted py-4">No data available for this period</td></tr>
                    <?php endif; ?>
                    <?php foreach ($bestSellingProducts as $idx => $prod): ?>
                    <tr>
                        <td class="fw-bold"><?= $idx + 1 ?></td>
                        <td class="fw-semibold"><?= sanitize($prod['name']) ?></td>
                        <td><?= formatPrice($prod['price']) ?></td>
                        <td><span class="badge bg-success"><?= $prod['total_sold'] ?> sold</span></td>
                        <td class="fw-bold"><?= formatPrice($prod['total_revenue']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
$chartLabels = array_column($salesData, 'date');
$chartRevenue = array_column($salesData, 'revenue');
$chartOrders = array_column($salesData, 'orders');

$extraScripts = '<script>
new Chart(document.getElementById("revenueChart"), {
    type: "line",
    data: {
        labels: ' . json_encode($chartLabels) . ',
        datasets: [
            { label: "Revenue", data: ' . json_encode($chartRevenue) . ', borderColor: "#e94560", backgroundColor: "rgba(233,69,96,0.1)", fill: true, tension: 0.4, yAxisID: "y" },
            { label: "Orders", data: ' . json_encode($chartOrders) . ', borderColor: "#4e73df", backgroundColor: "rgba(78,115,223,0.1)", fill: false, tension: 0.4, yAxisID: "y1" }
        ]
    },
    options: {
        responsive: true,
        interaction: { mode: "index", intersect: false },
        plugins: { legend: { position: "top" } },
        scales: {
            y: { type: "linear", display: true, position: "left", beginAtZero: true },
            y1: { type: "linear", display: true, position: "right", beginAtZero: true, grid: { drawOnChartArea: false } }
        }
    }
});
</script>';
include __DIR__ . '/includes/footer.php';
?>
