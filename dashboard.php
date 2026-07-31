<<<<<<< HEAD
<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: authentication/login.php");
    exit();
}

require_once("config/db.php");

$user_id = $_SESSION['user_id'];

/* ==========================================
   DASHBOARD SUMMARY
========================================== */

// Total Income
$stmt = $conn->prepare("SELECT COALESCE(SUM(amount),0) FROM income WHERE user_id=?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$totalIncome = $stmt->get_result()->fetch_row()[0];

// Total Expense
$stmt = $conn->prepare("SELECT COALESCE(SUM(amount),0) FROM expenses WHERE user_id=?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$totalExpense = $stmt->get_result()->fetch_row()[0];

// Total Savings
$stmt = $conn->prepare("
SELECT COALESCE(SUM(saved_amount),0)
FROM savings
WHERE user_id=?
");

$stmt->bind_param("i", $user_id);
$stmt->execute();
$totalSavings = $stmt->get_result()->fetch_row()[0];
// Current Budget
$stmt = $conn->prepare("SELECT COALESCE(MAX(monthly_budget),0) FROM budgets WHERE user_id=?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$totalBudget = $stmt->get_result()->fetch_row()[0];

$walletBalance = $totalIncome - $totalExpense;
// Today's Income
$stmt = $conn->prepare("
SELECT COALESCE(SUM(amount),0)
FROM income
WHERE user_id=? AND DATE(created_at)=CURDATE()
");
$stmt->bind_param("i",$user_id);
$stmt->execute();
$todayIncome = $stmt->get_result()->fetch_row()[0];

// Today's Expense
$stmt = $conn->prepare("
SELECT COALESCE(SUM(amount),0)
FROM expenses
WHERE user_id=? AND DATE(created_at)=CURDATE()
");
$stmt->bind_param("i",$user_id);
$stmt->execute();
$todayExpense = $stmt->get_result()->fetch_row()[0];

$todayBalance = $todayIncome - $todayExpense;

// Budget Usage
$budgetUsed = ($totalBudget > 0)
    ? ($totalExpense / $totalBudget) * 100
    : 0;

$budgetUsed = min($budgetUsed,100);
/* ==========================================
   MONTHLY STATISTICS
========================================== */

// Monthly Income
$stmt = $conn->prepare("
SELECT COALESCE(SUM(amount),0)
FROM income
WHERE user_id=?
AND MONTH(created_at)=MONTH(CURDATE())
AND YEAR(created_at)=YEAR(CURDATE())
");
$stmt->bind_param("i",$user_id);
$stmt->execute();
$monthlyIncome = $stmt->get_result()->fetch_row()[0];

// Monthly Expense
$stmt = $conn->prepare("
SELECT COALESCE(SUM(amount),0)
FROM expenses
WHERE user_id=?
AND MONTH(created_at)=MONTH(CURDATE())
AND YEAR(created_at)=YEAR(CURDATE())
");
$stmt->bind_param("i",$user_id);
$stmt->execute();
$monthlyExpense = $stmt->get_result()->fetch_row()[0];

// Monthly Savings
$monthlySavings = $monthlyIncome - $monthlyExpense;

// Transactions This Month
$stmt = $conn->prepare("
SELECT COUNT(*)
FROM (
    SELECT id,created_at FROM income WHERE user_id=?
    UNION ALL
    SELECT id,created_at FROM expenses WHERE user_id=?
) t
WHERE MONTH(created_at)=MONTH(CURDATE())
AND YEAR(created_at)=YEAR(CURDATE())
");
$stmt->bind_param("ii",$user_id,$user_id);
$stmt->execute();
$monthlyTransactions = $stmt->get_result()->fetch_row()[0];
/* ==========================================
   RECENT TRANSACTIONS
========================================== */

$sql = "

SELECT amount,category,'Income' AS type,created_at
FROM income
WHERE user_id=?

UNION ALL

SELECT amount,category,'Expense' AS type,created_at
FROM expenses
WHERE user_id=?

ORDER BY created_at DESC

LIMIT 10

";

$stmt = $conn->prepare($sql);

$stmt->bind_param("ii",$user_id,$user_id);

$stmt->execute();

$transactions = $stmt->get_result();

$months = [];
$incomeChart = [];
$expenseChart = [];

for($i=1;$i<=12;$i++){

    $months[] = date("M", mktime(0,0,0,$i,1));

    // Monthly Income
    $stmt = $conn->prepare("
        SELECT COALESCE(SUM(amount),0)
        FROM income
        WHERE user_id=? AND MONTH(created_at)=? AND YEAR(created_at)=YEAR(CURDATE())
    ");
    $stmt->bind_param("ii",$user_id,$i);
    $stmt->execute();
    $incomeChart[] = $stmt->get_result()->fetch_row()[0];

    // Monthly Expense
    $stmt = $conn->prepare("
        SELECT COALESCE(SUM(amount),0)
        FROM expenses
        WHERE user_id=? AND MONTH(created_at)=? AND YEAR(created_at)=YEAR(CURDATE())
    ");
    $stmt->bind_param("ii",$user_id,$i);
    $stmt->execute();
    $expenseChart[] = $stmt->get_result()->fetch_row()[0];
}
/* ==========================================
   EXPENSE CATEGORY CHART
========================================== */

$categoryLabels = [];
$categoryValues = [];

$stmt = $conn->prepare("
SELECT category,
COALESCE(SUM(amount),0) AS total
FROM expenses
WHERE user_id=?
GROUP BY category
");

$stmt->bind_param("i",$user_id);
$stmt->execute();

$result = $stmt->get_result();

while($row = $result->fetch_assoc()){
    $categoryLabels[] = $row['category'];
    $categoryValues[] = $row['total'];
}
/* ==========================================
   TOP 5 EXPENSE CATEGORIES
========================================== */

$stmt = $conn->prepare("
SELECT category,
       SUM(amount) AS total
FROM expenses
WHERE user_id=?
GROUP BY category
ORDER BY total DESC
LIMIT 5
");

$stmt->bind_param("i", $user_id);
$stmt->execute();

$topCategories = $stmt->get_result();
 /* ==========================================
   USER DETAILS
========================================== */

$stmt = $conn->prepare("
SELECT full_name,email
FROM users
WHERE id=?
");

$stmt->bind_param("i",$user_id);
$stmt->execute();

$user = $stmt->get_result()->fetch_assoc();

/* ==========================================
   DYNAMIC NOTIFICATIONS
========================================== */

$notifications = [];

if($walletBalance <= 0){
    $notifications[] = "⚠️ Your wallet balance is zero or negative.";
}

if($budgetUsed >= 90){
    $notifications[] = "📊 You have used more than 90% of your monthly budget.";
}

if($todayExpense > $todayIncome){
    $notifications[] = "💸 Today's expenses are higher than today's income.";
}

if($totalSavings > 0){
    $notifications[] = "🎯 Great job! Keep growing your savings.";
}

if(empty($notifications)){
    $notifications[] = "✅ Everything looks good. Keep tracking your finances!";
}
/* ==========================================
   SMART FINANCIAL INSIGHTS
========================================== */

$insights = [];

if($monthlyExpense > $monthlyIncome){
    $insights[] = "⚠️ You spent more than you earned this month.";
}else{
    $insights[] = "✅ Your income is higher than your expenses.";
}

if($budgetUsed >= 80){
    $insights[] = "📊 Budget usage has crossed 80%.";
}

if($totalSavings > ($monthlyIncome * 0.20)){
    $insights[] = "🎯 Excellent! You've saved more than 20% of your monthly income.";
}

if($monthlyTransactions >= 50){
    $insights[] = "💳 You made a high number of transactions this month.";
}

if(empty($insights)){
    $insights[] = "😊 Your financial health looks stable.";
}
/* ==========================================
   FINANCIAL GOAL PROGRESS
========================================== */

$goalAmount = 100000; // Change this to your desired target

$goalPercentage = 0;

if($goalAmount > 0){
    $goalPercentage = min(
        ($totalSavings / $goalAmount) * 100,
        100
    );
}
/* ==========================================
   RECENT ACTIVITY TIMELINE
========================================== */

$stmt = $conn->prepare("
SELECT created_at,
       category,
       amount,
       'Income' AS type
FROM income
WHERE user_id=?

UNION ALL

SELECT created_at,
       category,
       amount,
       'Expense' AS type
FROM expenses
WHERE user_id=?

ORDER BY created_at DESC
LIMIT 5
");

$stmt->bind_param("ii",$user_id,$user_id);
$stmt->execute();

$activityTimeline = $stmt->get_result();
/* ==========================================
   MONTHLY SPENDING TREND
========================================== */

$expenseTrendLabels = [];
$expenseTrendData = [];

for($i = 5; $i >= 0; $i--){

    $month = date("m", strtotime("-$i month"));
    $year  = date("Y", strtotime("-$i month"));

    $expenseTrendLabels[] = date("M Y", strtotime("-$i month"));

    $stmt = $conn->prepare("
        SELECT COALESCE(SUM(amount),0)
        FROM expenses
        WHERE user_id=?
        AND MONTH(created_at)=?
        AND YEAR(created_at)=?
    ");

    $stmt->bind_param("iii",$user_id,$month,$year);
    $stmt->execute();

    $expenseTrendData[] = $stmt->get_result()->fetch_row()[0];
}
/* ==========================================
   LARGEST EXPENSE THIS MONTH
========================================== */

$stmt = $conn->prepare("
SELECT category,
       amount,
       created_at
FROM expenses
WHERE user_id=?
AND MONTH(created_at)=MONTH(CURDATE())
AND YEAR(created_at)=YEAR(CURDATE())
ORDER BY amount DESC
LIMIT 1
");

$stmt->bind_param("i",$user_id);
$stmt->execute();

$largestExpense = $stmt->get_result()->fetch_assoc();
/* ==========================================
   HIGHEST INCOME SOURCE
========================================== */

$stmt = $conn->prepare("
SELECT category,
       SUM(amount) AS total
FROM income
WHERE user_id=?
GROUP BY category
ORDER BY total DESC
LIMIT 1
");

$stmt->bind_param("i",$user_id);
$stmt->execute();

$highestIncome = $stmt->get_result()->fetch_assoc();
/* ==========================================
   REMAINING BUDGET
========================================== */

$remainingBudget = max(0, $totalBudget - $totalExpense);
/* ==========================================
   TOTAL EXPENSE RECORDS
========================================== */

$stmt = $conn->prepare("
SELECT COUNT(*)
FROM expenses
WHERE user_id=?
");

$stmt->bind_param("i",$user_id);
$stmt->execute();

$totalExpenseRecords = $stmt->get_result()->fetch_row()[0];
/* ==========================================
   TOTAL INCOME RECORDS
========================================== */

$stmt = $conn->prepare("
SELECT COUNT(*)
FROM income
WHERE user_id=?
");

$stmt->bind_param("i",$user_id);
$stmt->execute();

$totalIncomeRecords = $stmt->get_result()->fetch_row()[0];
/* ==========================================
   AVERAGE EXPENSE
========================================== */

$stmt = $conn->prepare("
SELECT COALESCE(AVG(amount),0)
FROM expenses
WHERE user_id=?
");

$stmt->bind_param("i",$user_id);
$stmt->execute();

$averageExpense = $stmt->get_result()->fetch_row()[0];
/* ==========================================
   AVERAGE INCOME
========================================== */

$stmt = $conn->prepare("
SELECT COALESCE(AVG(amount),0)
FROM income
WHERE user_id=?
");

$stmt->bind_param("i",$user_id);
$stmt->execute();

$averageIncome = $stmt->get_result()->fetch_row()[0];
/* ==========================================
   SAVINGS RATE
========================================== */

$savingsRate = 0;

if($totalIncome>0){
    $savingsRate = ($totalSavings/$totalIncome)*100;
}
/* ==========================================
   FINANCIAL HEALTH SCORE
========================================== */

$financialHealth = 100;

if($budgetUsed>100) $financialHealth-=30;
if($monthlyExpense>$monthlyIncome) $financialHealth-=30;
if($walletBalance<0) $financialHealth-=20;
if($totalSavings<=0) $financialHealth-=20;

$financialHealth=max(0,$financialHealth);
?>
<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Dashboard | NeoFinance</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<link rel="stylesheet" href="css/style.css?v=2">
=======

<?php

session_start();

if(!isset($_SESSION['username']))
{
    header("Location: login.php");
    exit();
}

?>


<!DOCTYPE html>
<html lang="en">
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Student Dashboard</title>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
>>>>>>> 3b6323a39d716a2b44dd6448aa482f5c3c4bbcca

<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

<<<<<<< HEAD
=======
<style>

*{
margin:0;
padding:0;
box-sizing:border-box;
font-family:'Poppins',sans-serif;
}

body{
background:#f5f7fa;
}

.container{
display:flex;
min-height:100vh;
}

/* Sidebar */

.sidebar{
width:250px;
background:#0d6efd;
color:white;
padding:20px;
}

.sidebar h2{
text-align:center;
margin-bottom:30px;
}

.sidebar ul{
list-style:none;
}

.sidebar ul li{
margin:18px 0;
}

.sidebar ul li a{
text-decoration:none;
color:white;
display:block;
padding:12px;
border-radius:8px;
transition:.3s;
}

.sidebar ul li a:hover{
background:white;
color:#0d6efd;
}

/* Main Content */

.main{
flex:1;
padding:30px;
}

.header{
background:white;
padding:20px;
border-radius:10px;
box-shadow:0 5px 10px rgba(0,0,0,.1);
margin-bottom:30px;
}

.header h1{
color:#0d6efd;
}

.cards{

display:grid;

grid-template-columns:repeat(auto-fit,minmax(220px,1fr));

gap:20px;

}

.card{

background:white;

padding:30px;

border-radius:12px;

text-align:center;

box-shadow:0 5px 15px rgba(0,0,0,.1);

transition:.3s;

}

.card:hover{

transform:translateY(-8px);

}

.card i{

font-size:45px;

color:#0d6efd;

margin-bottom:15px;

}

.card h3{

margin-bottom:10px;

}

.card a{

display:inline-block;

margin-top:15px;

padding:10px 18px;

background:#0d6efd;

color:white;

text-decoration:none;

border-radius:5px;

}

.logout{

margin-top:30px;

text-align:right;

}

.logout a{

background:red;

padding:12px 20px;

color:white;

text-decoration:none;

border-radius:5px;

}

@media(max-width:768px){

.container{

flex-direction:column;

}

.sidebar{

width:100%;

}

}

</style>

>>>>>>> 3b6323a39d716a2b44dd6448aa482f5c3c4bbcca
</head>

<body>

<<<<<<< HEAD
<?php include("includes/sidebar.php"); ?>

<?php include("includes/topbar.php"); ?>

<div class="dashboard-content">

<div class="container-fluid">

<div class="row g-4">

<!-- Wallet -->

<div class="col-xl-3 col-md-6">

<div class="dashboard-card balance-card">

<div class="card-header-custom">

<i class="fa-solid fa-wallet"></i>

<span>Wallet Balance</span>

</div>

<h2>

₹<?php echo number_format($walletBalance,2); ?>

</h2>

<p>Available Balance</p>
=======
<div class="container">

<div class="sidebar">

<h2>Student Portal</h2>

<ul>

<li><a href="dashboard.php"><i class="fa fa-home"></i> Dashboard</a></li>

<li><a href="profile.php"><i class="fa fa-user"></i> Student Profile</a></li>

<li><a href="project.php"><i class="fa fa-folder"></i> My Project</a></li>

<li><a href="task.php"><i class="fa fa-list-check"></i> Task List</a></li>

<li><a href="team.php"><i class="fa fa-users"></i> Team Members</a></li>

<li><a href="submit.php"><i class="fa fa-upload"></i> Submit Work</a></li>

<li><a href="index.php"><i class="fa fa-sign-out-alt"></i> Logout</a></li>

</ul>

</div>

<div class="main">

<div class="header">

<h1>Welcome, Student 👋</h1>

<p>Student Project Portal Dashboard</p>

</div>

<div class="cards">

<div class="card">
<i class="fa fa-user"></i>
<h3>Student Profile</h3>
<p>View and update your profile.</p>
<a href="profile.php">Open</a>
</div>

<div class="card">
<i class="fa fa-folder-open"></i>
<h3>My Project</h3>
<p>View project details.</p>
<a href="project.php">Open</a>
</div>

<div class="card">
<i class="fa fa-list-check"></i>
<h3>Task List</h3>
<p>Manage assigned tasks.</p>
<a href="task.php">Open</a>
</div>

<div class="card">
<i class="fa fa-users"></i>
<h3>Team Members</h3>
<p>View your team members.</p>
<a href="team.php">Open</a>
</div>

<div class="card">
<i class="fa fa-upload"></i>
<h3>Submit Work</h3>
<p>Upload project files.</p>
<a href="submit.php">Open</a>
</div>



</div>

>>>>>>> 3b6323a39d716a2b44dd6448aa482f5c3c4bbcca

</div>

</div>

<<<<<<< HEAD
<!-- Income -->

<div class="col-xl-3 col-md-6">

<div class="dashboard-card income-card">

<div class="card-header-custom">

<i class="fa-solid fa-arrow-trend-up"></i>

<span>Total Income</span>

</div>

<h2>

₹<?php echo number_format($totalIncome,2); ?>

</h2>

<p>Money Received</p>

</div>

</div>

<!-- Expense -->

<div class="col-xl-3 col-md-6">

<div class="dashboard-card expense-card">

<div class="card-header-custom">

<i class="fa-solid fa-arrow-trend-down"></i>

<span>Total Expense</span>

</div>

<h2>

₹<?php echo number_format($totalExpense,2); ?>

</h2>

<p>Money Spent</p>

</div>

</div>

<!-- Savings -->

<div class="col-xl-3 col-md-6">

<div class="dashboard-card saving-card">

<div class="card-header-custom">

<i class="fa-solid fa-piggy-bank"></i>

<span>Total Savings</span>

</div>

<h2>

₹<?php echo number_format($totalSavings,2); ?>

</h2>

<p>Saved Amount</p>

</div>

</div>

<!-- Budget -->

<div class="col-xl-3 col-md-6">

<div class="dashboard-card budget-card">

<div class="card-header-custom">

<i class="fa-solid fa-chart-column"></i>

<span>Current Budget</span>

</div>

<h2>

₹<?php echo number_format($totalBudget,2); ?>

</h2>

<p>Monthly Budget</p>

</div>

</div>

</div>
<div class="row mt-4">

    <div class="col-lg-12">

        <div class="dashboard-card">

            <div class="d-flex justify-content-between align-items-center">

                <div>

                    <h2>
                        Welcome,
                        <?php echo htmlspecialchars($user['full_name'] ?? 'User'); ?>
                        👋
                    </h2>

                    <p class="text-muted">
                        <?php echo htmlspecialchars($user['email'] ?? ''); ?>
                    </p>

                    <h5>
                        Today is
                        <?php echo date("l, d F Y"); ?>
                        </h5>
                    <h6 id="liveClock"></h6>

                </div>

                <i class="fa-solid fa-user-circle fa-5x text-primary"></i>

            </div>

        </div>

    </div>

</div>
<div class="text-end mb-3">
    <button class="btn btn-outline-light" id="themeToggle">
        🌙 Dark Mode
    </button>
</div>
<!-- PART B STARTS HERE -->
 <!-- ==========================
     RECENT TRANSACTIONS
========================== -->

<div class="row mt-5">

    <div class="col-lg-8">

        <div class="transaction-card">

            <div class="transaction-header">

                <h3>
                    <i class="fa-solid fa-clock-rotate-left"></i>
                    Recent Transactions
                </h3>

                <a href="transactions.php" class="btn btn-info btn-sm">

                    View All

                </a>

            </div>

            <div class="table-responsive">

                <table class="table table-dark table-hover align-middle">

                    <thead>

                    <tr>

                        <th>Date</th>

                        <th>Category</th>

                        <th>Type</th>

                        <th class="text-end">Amount</th>

                    </tr>

                    </thead>

                    <tbody>

                    <?php if($transactions->num_rows>0): ?>

                    <?php while($row=$transactions->fetch_assoc()): ?>

                    <tr>

                        <td>

                            <?php echo date("d M Y",strtotime($row['created_at'])); ?>

                        </td>

                        <td>

                            <span class="category-badge">

                                <?php echo htmlspecialchars($row['category']); ?>

                            </span>

                        </td>

                        <td>

                            <?php if($row['type']=="Income"): ?>

                                <span class="badge bg-success">

                                    Income

                                </span>

                            <?php else: ?>

                                <span class="badge bg-danger">

                                    Expense

                                </span>

                            <?php endif; ?>

                        </td>

                        <td class="text-end">

                            <?php if($row['type']=="Income"): ?>

                                <span class="text-success fw-bold">

                                    +₹<?php echo number_format($row['amount'],2); ?>

                                </span>

                            <?php else: ?>

                                <span class="text-danger fw-bold">

                                    -₹<?php echo number_format($row['amount'],2); ?>

                                </span>

                            <?php endif; ?>

                        </td>

                    </tr>

                    <?php endwhile; ?>

                    <?php else: ?>

                    <tr>

                        <td colspan="4" class="text-center">

                            No Transactions Found

                        </td>

                    </tr>

                    <?php endif; ?>

                    </tbody>

                </table>

            </div>

        </div>

    </div>

    <!-- QUICK ACTIONS -->

    <div class="col-lg-4">

        <div class="transaction-card">

            <h3>

                <i class="fa-solid fa-bolt"></i>

                Quick Actions

            </h3>

            <div class="d-grid gap-3 mt-4">

                <a href="add_income.php" class="btn btn-success">

                    <i class="fa-solid fa-wallet"></i>

                    Add Income

                </a>

                <a href="add_expense.php" class="btn btn-danger">

                    <i class="fa-solid fa-money-bill-wave"></i>

                    Add Expense

                </a>

                <a href="budgets.php" class="btn btn-warning">

                    <i class="fa-solid fa-chart-column"></i>

                    Manage Budget

                </a>

                <a href="reports.php" class="btn btn-primary">

                    <i class="fa-solid fa-file-lines"></i>

                    Reports

                </a>

            </div>

        </div>

    </div>

</div>

<!-- ==========================
     CHARTS
========================== -->

<div class="row mt-5">

    <div class="col-lg-8">

        <div class="chart-card">

            <h4>

                Income vs Expense

            </h4>

            <canvas id="incomeExpenseChart"></canvas>

        </div>

    </div>

    <div class="col-lg-4">

        <div class="chart-card">

            <h4>

                Expense Categories

            </h4>

            <canvas id="expenseCategoryChart"></canvas>

        </div>

    </div>

</div>

</div>

</div>


<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>

const incomeExpenseChart = new Chart(
document.getElementById("incomeExpenseChart"),
{
type:"bar",
data:{
labels: <?php echo json_encode($months); ?>,
datasets:[
{
label:"Income",
data: <?php echo json_encode($incomeChart); ?>,
backgroundColor:"#22C55E"
},
{
label:"Expense",
data: <?php echo json_encode($expenseChart); ?>,
backgroundColor:"#EF4444"
}
]
},
options:{
responsive:true,
plugins:{
legend:{
labels:{
color:"white"
}
}
},
scales:{
x:{
ticks:{color:"white"}
},
y:{
ticks:{color:"white"}
}
}
}
}
);

new Chart(
document.getElementById("expenseCategoryChart"),
{
type:"doughnut",
data:{
labels: <?php echo json_encode($categoryLabels); ?>,
datasets:[{
    data: <?php echo json_encode($categoryValues); ?>,
    backgroundColor:[
        "#3B82F6",
        "#22C55E",
        "#F59E0B",
        "#EF4444",
        "#8B5CF6",
        "#06B6D4",
        "#EC4899",
        "#84CC16"
    ],
    borderColor:"#0F172A",
    borderWidth:2
}]
},
options:{
responsive:true,
plugins:{
legend:{
labels:{
color:"white"
}
}
}
}
}
);

</script>
<script>

document.querySelectorAll(".dashboard-card h2").forEach(function(card){

if (card.innerText.indexOf("₹") === -1) return; // skip non-currency headings (e.g. the welcome message)

let value=parseFloat(
card.innerText.replace(/[₹,]/g,"")
);

let count=0;
let speed=value/60;

let interval=setInterval(function(){
count+=speed;

if(count>=value){
count=value;
clearInterval(interval);
}

card.innerText="₹"+count.toLocaleString(undefined,{
minimumFractionDigits:2,
maximumFractionDigits:2
});
},20);

});

</script>
<script>

function updateClock(){
    const now = new Date();
    const el = document.getElementById("liveClock");
    if (el) el.innerHTML = now.toLocaleTimeString();
}

setInterval(updateClock,1000);
updateClock();

</script>
<script>

const toggle = document.getElementById("themeToggle");

if (toggle) {
    toggle.addEventListener("click",function(){
        document.body.classList.toggle("light-mode");
        if(document.body.classList.contains("light-mode")){
            toggle.innerHTML="☀️ Light Mode";
        }else{
            toggle.innerHTML="🌙 Dark Mode";
        }
    });
}

</script>
</body>
</html>
=======
</body>
</html>
>>>>>>> 3b6323a39d716a2b44dd6448aa482f5c3c4bbcca
