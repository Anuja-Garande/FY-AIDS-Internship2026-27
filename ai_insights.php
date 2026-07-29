<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: authentication/login.php"); exit(); }
require_once("config/db.php");
$user_id = $_SESSION['user_id'];

// Last 6 months expense totals -> used for a simple linear forecast
$labels = []; $data = [];
for ($i = 5; $i >= 0; $i--) {
    $month = date("m", strtotime("-$i month"));
    $year  = date("Y", strtotime("-$i month"));
    $labels[] = date("M Y", strtotime("-$i month"));
    $s = $conn->prepare("SELECT COALESCE(SUM(amount),0) FROM expenses WHERE user_id=? AND MONTH(expense_date)=? AND YEAR(expense_date)=?");
    $s->bind_param("iii", $user_id, $month, $year);
    $s->execute();
    $data[] = (float)$s->get_result()->fetch_row()[0];
}

// Simple moving-average forecast for next month
$nonZero = array_filter($data);
$forecast = count($nonZero) ? array_sum($nonZero) / count($nonZero) : 0;

// Anomaly detection: expenses this month that are 2x+ the user's average expense
$avgStmt = $conn->prepare("SELECT COALESCE(AVG(amount),0) FROM expenses WHERE user_id=?");
$avgStmt->bind_param("i", $user_id);
$avgStmt->execute();
$avgExpense = $avgStmt->get_result()->fetch_row()[0];

$anomalyStmt = $conn->prepare("
    SELECT category, amount, expense_date
    FROM expenses
    WHERE user_id=? AND amount > ? AND MONTH(expense_date)=MONTH(CURDATE()) AND YEAR(expense_date)=YEAR(CURDATE())
    ORDER BY amount DESC LIMIT 5
");
$threshold = $avgExpense * 2;
$anomalyStmt->bind_param("id", $user_id, $threshold);
$anomalyStmt->execute();
$anomalies = $anomalyStmt->get_result();

// Category trending: this month vs last month
$catTrend = [];
$curMonth = date('m'); $curYear = date('Y');
$prevMonth = date('m', strtotime('-1 month')); $prevYear = date('Y', strtotime('-1 month'));

$catList = $conn->prepare("SELECT DISTINCT category FROM expenses WHERE user_id=?");
$catList->bind_param("i", $user_id);
$catList->execute();
$cats = $catList->get_result();

while ($c = $cats->fetch_assoc()) {
    $cat = $c['category'];

    $cur = $conn->prepare("SELECT COALESCE(SUM(amount),0) FROM expenses WHERE user_id=? AND category=? AND MONTH(expense_date)=? AND YEAR(expense_date)=?");
    $cur->bind_param("isii", $user_id, $cat, $curMonth, $curYear);
    $cur->execute();
    $curVal = (float)$cur->get_result()->fetch_row()[0];

    $prev = $conn->prepare("SELECT COALESCE(SUM(amount),0) FROM expenses WHERE user_id=? AND category=? AND MONTH(expense_date)=? AND YEAR(expense_date)=?");
    $prev->bind_param("isii", $user_id, $cat, $prevMonth, $prevYear);
    $prev->execute();
    $prevVal = (float)$prev->get_result()->fetch_row()[0];

    if ($prevVal > 0) {
        $change = (($curVal - $prevVal) / $prevVal) * 100;
        if (abs($change) >= 15) {
            $catTrend[] = ['category' => $cat, 'change' => $change, 'cur' => $curVal, 'prev' => $prevVal];
        }
    }
}

// Savings rate insight
$si = $conn->prepare("SELECT COALESCE(SUM(amount),0) FROM income WHERE user_id=?");
$si->bind_param("i", $user_id); $si->execute();
$totalIncome = $si->get_result()->fetch_row()[0];

$se = $conn->prepare("SELECT COALESCE(SUM(amount),0) FROM expenses WHERE user_id=?");
$se->bind_param("i", $user_id); $se->execute();
$totalExpense = $se->get_result()->fetch_row()[0];

$savingsRate = $totalIncome > 0 ? (($totalIncome - $totalExpense) / $totalIncome) * 100 : 0;

$recommendations = [];
if ($savingsRate < 10) $recommendations[] = "Your savings rate is under 10%. Try the 50/30/20 rule: 50% needs, 30% wants, 20% savings.";
if ($forecast > 0 && end($data) > $forecast * 1.2) $recommendations[] = "Your latest month's spending is well above your 6-month average — review discretionary categories.";
if (count($nonZero) >= 3 && $data[count($data)-1] < $data[count($data)-2]) $recommendations[] = "Nice! Your expenses dropped compared to last month — keep the momentum going.";
if (empty($recommendations)) $recommendations[] = "Your spending pattern looks stable. Keep tracking consistently for sharper insights.";
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>AI Insights | NeoFinance</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="css/style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,400..900&family=Manrope:wght@400;500;600;700;800&family=IBM+Plex+Mono:wght@400;500;600&display=swap" rel="stylesheet">
</head>
<body>
<?php include("includes/sidebar.php"); ?>
<?php include("includes/topbar.php"); ?>

<div class="dashboard-content">
    <div class="page-header">
        <div class="page-eyebrow">Ledger · Intelligence</div>
        <h2 class="page-title"><span class="icon-badge"><i class="fa-solid fa-robot"></i></span> AI Insights</h2>
        <p class="page-subtitle">A rule-based financial intelligence engine that analyzes your own transaction history — no external data is used.</p>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-lg-6">
            <div class="chart-card">
                <h4>Next Month Spending Forecast</h4>
                <h2 class="text-info">₹<?php echo number_format($forecast,2); ?></h2>
                <p class="text-muted">Based on the average of your last 6 months of expenses.</p>
                <canvas id="forecastChart"></canvas>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="dashboard-card h-100">
                <h4>Savings Rate</h4>
                <h2 class="<?php echo $savingsRate>=20?'text-success':($savingsRate>=0?'text-warning':'text-danger'); ?>">
                    <?php echo round($savingsRate,1); ?>%
                </h2>
                <p class="text-muted">Percentage of income you've kept versus spent, all-time.</p>
                <hr>
                <h5><i class="fa-solid fa-lightbulb text-warning"></i> Recommendations</h5>
                <ul class="list-group mt-2">
                    <?php foreach ($recommendations as $r): ?>
                        <li class="list-group-item bg-dark text-light"><?php echo htmlspecialchars($r); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-6">
            <div class="transaction-card">
                <h3><i class="fa-solid fa-triangle-exclamation text-warning"></i> Unusual Expenses This Month</h3>
                <p class="text-muted small">Flagged when a single expense is 2x+ your average.</p>
                <table class="table table-dark table-hover mt-2">
                    <thead><tr><th>Category</th><th>Date</th><th class="text-end">Amount</th></tr></thead>
                    <tbody>
                    <?php if ($anomalies->num_rows === 0): ?>
                        <tr><td colspan="3" class="text-center">No unusual expenses detected. 👍</td></tr>
                    <?php else: while ($a = $anomalies->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($a['category']); ?></td>
                            <td><?php echo date("d M Y", strtotime($a['expense_date'])); ?></td>
                            <td class="text-end text-danger fw-bold">₹<?php echo number_format($a['amount'],2); ?></td>
                        </tr>
                    <?php endwhile; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="transaction-card">
                <h3><i class="fa-solid fa-chart-line text-info"></i> Category Trends (vs Last Month)</h3>
                <table class="table table-dark table-hover mt-2">
                    <thead><tr><th>Category</th><th class="text-end">Change</th></tr></thead>
                    <tbody>
                    <?php if (empty($catTrend)): ?>
                        <tr><td colspan="2" class="text-center">No significant category changes.</td></tr>
                    <?php else: foreach ($catTrend as $t): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($t['category']); ?></td>
                            <td class="text-end <?php echo $t['change']>0?'text-danger':'text-success'; ?> fw-bold">
                                <?php echo ($t['change']>0?'▲ +':'▼ ').round(abs($t['change']),1); ?>%
                            </td>
                        </tr>
                    <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
new Chart(document.getElementById("forecastChart"), {
    type: "line",
    data: {
        labels: [...<?php echo json_encode($labels); ?>, "Forecast"],
        datasets: [{
            label: "Expenses",
            data: [...<?php echo json_encode($data); ?>, <?php echo $forecast; ?>],
            borderColor: "#EAD08E",
            backgroundColor: "rgba(234,208,142,.15)",
            fill: true,
            segment: { borderDash: ctx => ctx.p1DataIndex === <?php echo count($data); ?> ? [6,6] : undefined }
        }]
    },
    options: { responsive:true, plugins:{legend:{labels:{color:"white"}}}, scales:{x:{ticks:{color:"white"}},y:{ticks:{color:"white"}}} }
});
</script>
</body>
</html>
