<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: authentication/login.php"); exit(); }
require_once("config/db.php");
$user_id = $_SESSION['user_id'];

// Monthly income vs expense (current year)
$months=[]; $incomeChart=[]; $expenseChart=[];
for($i=1;$i<=12;$i++){
    $months[]=date("M",mktime(0,0,0,$i,1));
    $s=$conn->prepare("SELECT COALESCE(SUM(amount),0) FROM income WHERE user_id=? AND MONTH(income_date)=? AND YEAR(income_date)=YEAR(CURDATE())");
    $s->bind_param("ii",$user_id,$i); $s->execute();
    $incomeChart[]=$s->get_result()->fetch_row()[0];

    $s=$conn->prepare("SELECT COALESCE(SUM(amount),0) FROM expenses WHERE user_id=? AND MONTH(expense_date)=? AND YEAR(expense_date)=YEAR(CURDATE())");
    $s->bind_param("ii",$user_id,$i); $s->execute();
    $expenseChart[]=$s->get_result()->fetch_row()[0];
}

// Category-wise expense breakdown
$catLabels=[]; $catValues=[];
$s=$conn->prepare("SELECT category, COALESCE(SUM(amount),0) t FROM expenses WHERE user_id=? GROUP BY category ORDER BY t DESC");
$s->bind_param("i",$user_id); $s->execute(); $res=$s->get_result();
while($row=$res->fetch_assoc()){ $catLabels[]=$row['category']; $catValues[]=$row['t']; }

// Income source breakdown
$incLabels=[]; $incValues=[];
$s=$conn->prepare("SELECT category, COALESCE(SUM(amount),0) t FROM income WHERE user_id=? GROUP BY category ORDER BY t DESC");
$s->bind_param("i",$user_id); $s->execute(); $res=$s->get_result();
while($row=$res->fetch_assoc()){ $incLabels[]=$row['category']; $incValues[]=$row['t']; }

// Weekday spending pattern
$weekdayLabels=['Sun','Mon','Tue','Wed','Thu','Fri','Sat'];
$weekdayValues=array_fill(0,7,0);
$s=$conn->prepare("SELECT DAYOFWEEK(expense_date)-1 AS dow, SUM(amount) t FROM expenses WHERE user_id=? GROUP BY dow");
$s->bind_param("i",$user_id); $s->execute(); $res=$s->get_result();
while($row=$res->fetch_assoc()){ $weekdayValues[(int)$row['dow']] = (float)$row['t']; }

// Averages
$s=$conn->prepare("SELECT COALESCE(AVG(amount),0) FROM expenses WHERE user_id=?");
$s->bind_param("i",$user_id); $s->execute(); $avgExpense=$s->get_result()->fetch_row()[0];

$s=$conn->prepare("SELECT COALESCE(AVG(amount),0) FROM income WHERE user_id=?");
$s->bind_param("i",$user_id); $s->execute(); $avgIncome=$s->get_result()->fetch_row()[0];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Analytics | NeoFinance</title>
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
        <div class="page-eyebrow">Ledger · Data</div>
        <h2 class="page-title"><span class="icon-badge"><i class="fa-solid fa-chart-pie"></i></span> Analytics</h2>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-6">
            <div class="dashboard-card"><h4>Average Expense</h4><h2 class="text-danger">₹<?php echo number_format($avgExpense,2); ?></h2></div>
        </div>
        <div class="col-md-6">
            <div class="dashboard-card"><h4>Average Income</h4><h2 class="text-success">₹<?php echo number_format($avgIncome,2); ?></h2></div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="chart-card">
                <h4>Income vs Expense (This Year)</h4>
                <canvas id="yearChart"></canvas>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="chart-card">
                <h4>Expense by Category</h4>
                <canvas id="expenseCatChart"></canvas>
            </div>
        </div>
    </div>

    <div class="row g-4 mt-1">
        <div class="col-lg-6">
            <div class="chart-card">
                <h4>Income by Source</h4>
                <canvas id="incomeCatChart"></canvas>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="chart-card">
                <h4>Spending by Day of Week</h4>
                <canvas id="weekdayChart"></canvas>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const opts = {responsive:true, plugins:{legend:{labels:{color:"white"}}}, scales:{x:{ticks:{color:"white"}},y:{ticks:{color:"white"}}}};

new Chart(document.getElementById("yearChart"),{
    type:"bar",
    data:{labels: <?php echo json_encode($months); ?>,
    datasets:[
        {label:"Income", data: <?php echo json_encode($incomeChart); ?>, backgroundColor:"#3FCB92"},
        {label:"Expense", data: <?php echo json_encode($expenseChart); ?>, backgroundColor:"#D97A5F"}
    ]},
    options: opts
});

new Chart(document.getElementById("expenseCatChart"),{
    type:"doughnut",
    data:{labels: <?php echo json_encode($catLabels); ?>,
    datasets:[{data: <?php echo json_encode($catValues); ?>, backgroundColor:["#C6A15B","#3FCB92","#D97A5F","#4FB3A9","#A78BC9","#E0B34E","#7C9473","#C97B63"]}]},
    options:{responsive:true, plugins:{legend:{labels:{color:"white"}}}}
});

new Chart(document.getElementById("incomeCatChart"),{
    type:"pie",
    data:{labels: <?php echo json_encode($incLabels); ?>,
    datasets:[{data: <?php echo json_encode($incValues); ?>, backgroundColor:["#3FCB92","#C6A15B","#E0B34E","#A78BC9","#4FB3A9"]}]},
    options:{responsive:true, plugins:{legend:{labels:{color:"white"}}}}
});

new Chart(document.getElementById("weekdayChart"),{
    type:"radar",
    data:{labels: <?php echo json_encode($weekdayLabels); ?>,
    datasets:[{label:"Expense", data: <?php echo json_encode($weekdayValues); ?>, backgroundColor:"rgba(234,208,142,.28)", borderColor:"#EAD08E"}]},
    options:{responsive:true, plugins:{legend:{labels:{color:"white"}}}, scales:{r:{ticks:{color:"white",backdropColor:"transparent"}, grid:{color:"rgba(255,255,255,.1)"}, pointLabels:{color:"white"}}}}
});
</script>
</body>
</html>
