<?php require_once("admin_guard.php"); ?>
<?php
$totalUsers = $conn->query("SELECT COUNT(*) c FROM users")->fetch_assoc()['c'];
$totalIncome = $conn->query("SELECT COALESCE(SUM(amount),0) t FROM income")->fetch_assoc()['t'];
$totalExpense = $conn->query("SELECT COALESCE(SUM(amount),0) t FROM expenses")->fetch_assoc()['t'];
$totalTransactions = $conn->query("SELECT (SELECT COUNT(*) FROM income)+(SELECT COUNT(*) FROM expenses) c")->fetch_assoc()['c'];

$recentUsers = $conn->query("SELECT id, full_name, email, created_at FROM users ORDER BY created_at DESC LIMIT 8");

// Signups per month (current year)
$months=[]; $signups=[];
for($i=1;$i<=12;$i++){
    $months[]=date("M",mktime(0,0,0,$i,1));
    $s=$conn->prepare("SELECT COUNT(*) c FROM users WHERE MONTH(created_at)=? AND YEAR(created_at)=YEAR(CURDATE())");
    $s->bind_param("i",$i); $s->execute();
    $signups[]=$s->get_result()->fetch_assoc()['c'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Admin Panel | NeoFinance</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="../css/style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>
<body>
<div class="sidebar" id="sidebar">
    <div class="sidebar-brand">🛡️ <span>Admin Panel</span></div>
    <ul class="sidebar-menu">
        <li class="active"><a href="index.php"><i class="fa-solid fa-gauge"></i> Overview</a></li>
        <li><a href="users.php"><i class="fa-solid fa-users"></i> Manage Users</a></li>
        <li class="sidebar-divider"></li>
        <li><a href="../dashboard.php"><i class="fa-solid fa-arrow-left"></i> Back to App</a></li>
        <li><a href="../authentication/logout.php"><i class="fa-solid fa-right-from-bracket"></i> Logout</a></li>
    </ul>
</div>
<div class="topbar">
    <button class="sidebar-toggle" id="sidebarToggle"><i class="fa-solid fa-bars"></i></button>
    <div class="topbar-right"><span class="text-muted">Welcome, <?php echo htmlspecialchars($_SESSION['full_name']); ?></span></div>
</div>

<div class="dashboard-content">
    <h2 class="fw-bold mb-4"><i class="fa-solid fa-user-shield text-info"></i> Admin Overview</h2>

    <div class="row g-4">
        <div class="col-xl-3 col-md-6">
            <div class="dashboard-card balance-card"><div class="card-header-custom"><i class="fa-solid fa-users"></i><span>Total Users</span></div><h2><?php echo $totalUsers; ?></h2></div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="dashboard-card income-card"><div class="card-header-custom"><i class="fa-solid fa-arrow-trend-up"></i><span>Total Income (All Users)</span></div><h2>₹<?php echo number_format($totalIncome,2); ?></h2></div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="dashboard-card expense-card"><div class="card-header-custom"><i class="fa-solid fa-arrow-trend-down"></i><span>Total Expense (All Users)</span></div><h2>₹<?php echo number_format($totalExpense,2); ?></h2></div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="dashboard-card budget-card"><div class="card-header-custom"><i class="fa-solid fa-right-left"></i><span>Total Transactions</span></div><h2><?php echo $totalTransactions; ?></h2></div>
        </div>
    </div>

    <div class="row g-4 mt-1">
        <div class="col-lg-7">
            <div class="chart-card">
                <h4>New Signups This Year</h4>
                <canvas id="signupChart"></canvas>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="transaction-card">
                <h3><i class="fa-solid fa-user-plus"></i> Recent Signups</h3>
                <div class="list-group mt-3">
                    <?php while ($u = $recentUsers->fetch_assoc()): ?>
                    <div class="list-group-item bg-dark text-light d-flex justify-content-between">
                        <div><strong><?php echo htmlspecialchars($u['full_name']); ?></strong><br><small class="text-muted"><?php echo htmlspecialchars($u['email']); ?></small></div>
                        <small class="text-muted"><?php echo date("d M Y", strtotime($u['created_at'])); ?></small>
                    </div>
                    <?php endwhile; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.getElementById('sidebarToggle').addEventListener('click', () => document.getElementById('sidebar').classList.toggle('show'));
new Chart(document.getElementById("signupChart"), {
    type:"bar",
    data:{labels: <?php echo json_encode($months); ?>, datasets:[{label:"Signups", data: <?php echo json_encode($signups); ?>, backgroundColor:"#00E5FF"}]},
    options:{responsive:true, plugins:{legend:{labels:{color:"white"}}}, scales:{x:{ticks:{color:"white"}},y:{ticks:{color:"white"}}}}
});
</script>
</body>
</html>
