<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: authentication/login.php"); exit(); }
require_once("config/db.php");
$user_id = $_SESSION['user_id'];

$sql = "
    SELECT id, category, amount, 'Income' AS type, income_date AS txn_date, note
    FROM income WHERE user_id=?
    UNION ALL
    SELECT id, category, amount, 'Expense' AS type, expense_date AS txn_date, note
    FROM expenses WHERE user_id=?
    ORDER BY txn_date DESC
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $user_id, $user_id);
$stmt->execute();
$transactions = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Transactions | NeoFinance</title>
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
    <div class="d-flex justify-content-between align-items-end mb-4 flex-wrap gap-2">
        <div class="page-header mb-0">
            <div class="page-eyebrow">Ledger · Full History</div>
            <h2 class="page-title"><span class="icon-badge"><i class="fa-solid fa-right-left"></i></span> All Transactions</h2>
        </div>
        <div>
            <a href="add_income.php" class="btn btn-success btn-sm me-2"><i class="fa-solid fa-plus"></i> Income</a>
            <a href="add_expense.php" class="btn btn-danger btn-sm"><i class="fa-solid fa-plus"></i> Expense</a>
        </div>
    </div>
    <div class="transaction-card">
        <div class="table-responsive">
            <table class="table table-dark table-hover align-middle">
                <thead><tr><th>Date</th><th>Category</th><th>Type</th><th>Note</th><th class="text-end">Amount</th></tr></thead>
                <tbody>
                <?php if ($transactions->num_rows===0): ?>
                    <tr><td colspan="5" class="text-center">No transactions yet.</td></tr>
                <?php else: while ($r=$transactions->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo date("d M Y", strtotime($r['txn_date'])); ?></td>
                        <td><span class="category-badge"><?php echo htmlspecialchars($r['category']); ?></span></td>
                        <td><?php if($r['type']==='Income'): ?><span class="badge bg-success">Income</span><?php else: ?><span class="badge bg-danger">Expense</span><?php endif; ?></td>
                        <td><?php echo htmlspecialchars($r['note']); ?></td>
                        <td class="text-end <?php echo $r['type']==='Income'?'text-success':'text-danger'; ?> fw-bold">
                            <?php echo $r['type']==='Income'?'+':'-'; ?>₹<?php echo number_format($r['amount'],2); ?>
                        </td>
                    </tr>
                <?php endwhile; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</body>
</html>
