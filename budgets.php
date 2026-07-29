<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: authentication/login.php"); exit(); }
require_once("config/db.php");
$user_id = $_SESSION['user_id'];
$msg = "";

$currentMonth = date("F");
$currentYear  = date("Y");

// SET / UPDATE BUDGET FOR CURRENT MONTH
if ($_SERVER['REQUEST_METHOD']==='POST') {
    $amount = $_POST['monthly_budget'];

    $check = $conn->prepare("SELECT id FROM budgets WHERE user_id=? AND month=? AND year=?");
    $check->bind_param("isi", $user_id, $currentMonth, $currentYear);
    $check->execute();
    $existing = $check->get_result()->fetch_assoc();

    if ($existing) {
        $stmt = $conn->prepare("UPDATE budgets SET monthly_budget=? WHERE id=?");
        $stmt->bind_param("di", $amount, $existing['id']);
    } else {
        $stmt = $conn->prepare("INSERT INTO budgets (user_id, monthly_budget, month, year) VALUES (?,?,?,?)");
        $stmt->bind_param("idsi", $user_id, $amount, $currentMonth, $currentYear);
    }
    $stmt->execute();
    $msg = "Budget updated for $currentMonth $currentYear.";
}

// Current month budget & spend
$b = $conn->prepare("SELECT monthly_budget FROM budgets WHERE user_id=? AND month=? AND year=?");
$b->bind_param("isi", $user_id, $currentMonth, $currentYear);
$b->execute();
$currentBudget = $b->get_result()->fetch_assoc()['monthly_budget'] ?? 0;

$e = $conn->prepare("SELECT COALESCE(SUM(amount),0) FROM expenses WHERE user_id=? AND MONTH(expense_date)=MONTH(CURDATE()) AND YEAR(expense_date)=YEAR(CURDATE())");
$e->bind_param("i", $user_id);
$e->execute();
$currentSpend = $e->get_result()->fetch_row()[0];

$pct = $currentBudget > 0 ? min(100, ($currentSpend/$currentBudget)*100) : 0;

// Budget history
$h = $conn->prepare("SELECT * FROM budgets WHERE user_id=? ORDER BY year DESC, FIELD(month,'January','February','March','April','May','June','July','August','September','October','November','December') DESC");
$h->bind_param("i", $user_id);
$h->execute();
$history = $h->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Budget | NeoFinance</title>
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
        <div class="page-eyebrow">Ledger · Planning</div>
        <h2 class="page-title"><span class="icon-badge"><i class="fa-solid fa-chart-column"></i></span> Budget</h2>
    </div>

    <?php if ($msg): ?><div class="alert alert-success"><?php echo $msg; ?></div><?php endif; ?>

    <div class="row g-4">
        <div class="col-lg-5">
            <div class="form-card">
                <h4 class="mb-3">Set Budget - <?php echo $currentMonth." ".$currentYear; ?></h4>
                <form method="POST">
                    <div class="mb-3">
                        <label>Monthly Budget (₹)</label>
                        <input type="number" step="0.01" name="monthly_budget" class="form-control" value="<?php echo $currentBudget; ?>" required>
                    </div>
                    <button type="submit" class="btn btn-info text-white fw-bold w-100">Save Budget</button>
                </form>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="dashboard-card h-100">
                <h4>This Month's Usage</h4>
                <div class="progress my-3" style="height:22px;">
                    <div class="progress-bar <?php echo $pct>=90?'bg-danger':($pct>=70?'bg-warning':'bg-success'); ?>" style="width:<?php echo $pct; ?>%">
                        <?php echo round($pct); ?>%
                    </div>
                </div>
                <div class="d-flex justify-content-between">
                    <div><p class="text-muted mb-0">Spent</p><h4 class="text-danger">₹<?php echo number_format($currentSpend,2); ?></h4></div>
                    <div><p class="text-muted mb-0">Budget</p><h4 class="text-info">₹<?php echo number_format($currentBudget,2); ?></h4></div>
                    <div><p class="text-muted mb-0">Remaining</p><h4 class="text-success">₹<?php echo number_format(max(0,$currentBudget-$currentSpend),2); ?></h4></div>
                </div>
                <?php if ($pct >= 90): ?>
                    <div class="alert alert-danger mt-3 mb-0">⚠️ You're close to (or over) your monthly budget limit!</div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="transaction-card mt-4">
        <h3><i class="fa-solid fa-clock-rotate-left"></i> Budget History</h3>
        <div class="table-responsive mt-3">
            <table class="table table-dark table-hover">
                <thead><tr><th>Month</th><th>Year</th><th class="text-end">Budget</th></tr></thead>
                <tbody>
                <?php if ($history->num_rows === 0): ?>
                    <tr><td colspan="3" class="text-center">No budget history yet.</td></tr>
                <?php else: while ($row = $history->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['month']; ?></td>
                        <td><?php echo $row['year']; ?></td>
                        <td class="text-end">₹<?php echo number_format($row['monthly_budget'],2); ?></td>
                    </tr>
                <?php endwhile; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</body>
</html>
