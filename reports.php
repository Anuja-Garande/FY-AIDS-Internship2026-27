<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: authentication/login.php"); exit(); }
require_once("config/db.php");
$user_id = $_SESSION['user_id'];

// ===== FILTERS =====
$type      = $_GET['type']   ?? 'All';
$category  = $_GET['category'] ?? 'All';
$fromDate  = $_GET['from']   ?? date('Y-m-01');
$toDate    = $_GET['to']     ?? date('Y-m-d');

$sql = "
    SELECT id, category, amount, 'Income' AS type, income_date AS txn_date, note
    FROM income WHERE user_id=? AND income_date BETWEEN ? AND ?
    UNION ALL
    SELECT id, category, amount, 'Expense' AS type, expense_date AS txn_date, note
    FROM expenses WHERE user_id=? AND expense_date BETWEEN ? AND ?
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ississ", $user_id, $fromDate, $toDate, $user_id, $fromDate, $toDate);
$stmt->execute();
$result = $stmt->get_result();

$rows = [];
$totalIncome = 0; $totalExpense = 0;
while ($r = $result->fetch_assoc()) {
    if ($type !== 'All' && $r['type'] !== $type) continue;
    if ($category !== 'All' && $r['category'] !== $category) continue;
    $rows[] = $r;
    if ($r['type'] === 'Income') $totalIncome += $r['amount']; else $totalExpense += $r['amount'];
}

$catStmt = $conn->prepare("SELECT DISTINCT category_name FROM categories ORDER BY category_name");
$catStmt->execute();
$categories = $catStmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Reports | NeoFinance</title>
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
        <div class="page-eyebrow">Ledger · Statements</div>
        <h2 class="page-title"><span class="icon-badge"><i class="fa-solid fa-file-lines"></i></span> Reports</h2>
    </div>

    <!-- FILTERS -->
    <div class="transaction-card mb-4">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label">From</label>
                <input type="date" name="from" value="<?php echo htmlspecialchars($fromDate); ?>" class="form-control">
            </div>
            <div class="col-md-3">
                <label class="form-label">To</label>
                <input type="date" name="to" value="<?php echo htmlspecialchars($toDate); ?>" class="form-control">
            </div>
            <div class="col-md-2">
                <label class="form-label">Type</label>
                <select name="type" class="form-select">
                    <option <?php echo $type==='All'?'selected':''; ?>>All</option>
                    <option <?php echo $type==='Income'?'selected':''; ?>>Income</option>
                    <option <?php echo $type==='Expense'?'selected':''; ?>>Expense</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Category</label>
                <select name="category" class="form-select">
                    <option>All</option>
                    <?php while ($c = $categories->fetch_assoc()): ?>
                        <option <?php echo $category===$c['category_name']?'selected':''; ?>>
                            <?php echo htmlspecialchars($c['category_name']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="col-md-2">
                <button class="btn btn-info w-100 text-white fw-bold" type="submit">Filter</button>
            </div>
        </form>
    </div>

    <!-- SUMMARY -->
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="dashboard-card income-card">
                <div class="card-header-custom"><i class="fa-solid fa-arrow-trend-up"></i><span>Total Income</span></div>
                <h2>₹<?php echo number_format($totalIncome,2); ?></h2>
            </div>
        </div>
        <div class="col-md-4">
            <div class="dashboard-card expense-card">
                <div class="card-header-custom"><i class="fa-solid fa-arrow-trend-down"></i><span>Total Expense</span></div>
                <h2>₹<?php echo number_format($totalExpense,2); ?></h2>
            </div>
        </div>
        <div class="col-md-4">
            <div class="dashboard-card balance-card">
                <div class="card-header-custom"><i class="fa-solid fa-wallet"></i><span>Net</span></div>
                <h2>₹<?php echo number_format($totalIncome-$totalExpense,2); ?></h2>
            </div>
        </div>
    </div>

    <!-- TABLE -->
    <div class="transaction-card">
        <div class="transaction-header">
            <h3><i class="fa-solid fa-table"></i> Transaction Report</h3>
            <button class="btn btn-sm btn-outline-info" onclick="exportCSV()"><i class="fa-solid fa-download"></i> Export CSV</button>
        </div>
        <div class="table-responsive">
            <table class="table table-dark table-hover" id="reportTable">
                <thead><tr><th>Date</th><th>Category</th><th>Type</th><th>Note</th><th class="text-end">Amount</th></tr></thead>
                <tbody>
                <?php if (count($rows) === 0): ?>
                    <tr><td colspan="5" class="text-center">No records found for the selected filters.</td></tr>
                <?php else: foreach ($rows as $r): ?>
                    <tr>
                        <td><?php echo date("d M Y", strtotime($r['txn_date'])); ?></td>
                        <td><span class="category-badge"><?php echo htmlspecialchars($r['category']); ?></span></td>
                        <td>
                            <?php if ($r['type']==='Income'): ?>
                                <span class="badge bg-success">Income</span>
                            <?php else: ?>
                                <span class="badge bg-danger">Expense</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($r['note']); ?></td>
                        <td class="text-end <?php echo $r['type']==='Income'?'text-success':'text-danger'; ?> fw-bold">
                            <?php echo $r['type']==='Income'?'+':'-'; ?>₹<?php echo number_format($r['amount'],2); ?>
                        </td>
                    </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
function exportCSV(){
    let rows = document.querySelectorAll("#reportTable tr");
    let csv = [];
    rows.forEach(row => {
        let cols = row.querySelectorAll("td, th");
        let r = [];
        cols.forEach(c => r.push('"' + c.innerText.replace(/"/g,'""') + '"'));
        csv.push(r.join(","));
    });
    let blob = new Blob([csv.join("\n")], {type:"text/csv"});
    let link = document.createElement("a");
    link.href = URL.createObjectURL(blob);
    link.download = "neofinance_report.csv";
    link.click();
}
</script>
</body>
</html>
