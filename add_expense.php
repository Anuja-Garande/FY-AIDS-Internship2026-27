<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: authentication/login.php"); exit(); }
require_once("config/db.php");
$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD']==='POST') {
    $category = $_POST['category'];
    $amount   = $_POST['amount'];
    $date     = $_POST['expense_date'];
    $note     = trim($_POST['note']);

    $stmt = $conn->prepare("INSERT INTO expenses (user_id, category, amount, expense_date, note) VALUES (?,?,?,?,?)");
    $stmt->bind_param("isdss", $user_id, $category, $amount, $date, $note);
    $stmt->execute();

    // Auto notification if this pushes budget usage high
    $b = $conn->prepare("SELECT monthly_budget FROM budgets WHERE user_id=? AND month=? AND year=?");
    $m = date('F'); $y = date('Y');
    $b->bind_param("isi", $user_id, $m, $y);
    $b->execute();
    $budget = $b->get_result()->fetch_assoc()['monthly_budget'] ?? 0;

    if ($budget > 0) {
        $e = $conn->prepare("SELECT COALESCE(SUM(amount),0) FROM expenses WHERE user_id=? AND MONTH(expense_date)=MONTH(CURDATE()) AND YEAR(expense_date)=YEAR(CURDATE())");
        $e->bind_param("i", $user_id); $e->execute();
        $spent = $e->get_result()->fetch_row()[0];
        if ($spent >= $budget * 0.9) {
            $note2 = "📊 You've used " . round(($spent/$budget)*100) . "% of your monthly budget.";
            $n = $conn->prepare("INSERT INTO notifications (user_id, message) VALUES (?,?)");
            $n->bind_param("is", $user_id, $note2);
            $n->execute();
        }
    }

    header("Location: dashboard.php");
    exit();
}

$cats = $conn->query("SELECT category_name FROM categories WHERE type='Expense'");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Add Expense | NeoFinance</title>
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
        <div class="page-eyebrow">Ledger · New Entry</div>
        <h2 class="page-title"><span class="icon-badge"><i class="fa-solid fa-money-bill-wave"></i></span> Add Expense</h2>
    </div>
    <div class="form-card">
        <form method="POST">
            <div class="mb-3">
                <label>Category</label>
                <select name="category" class="form-select" required>
                    <?php while($c=$cats->fetch_assoc()): ?>
                        <option><?php echo htmlspecialchars($c['category_name']); ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="mb-3"><label>Amount (₹)</label><input type="number" step="0.01" name="amount" class="form-control" required></div>
            <div class="mb-3"><label>Date</label><input type="date" name="expense_date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required></div>
            <div class="mb-3"><label>Note</label><textarea name="note" class="form-control" rows="3"></textarea></div>
            <button class="btn btn-danger fw-bold w-100" type="submit">Save Expense</button>
        </form>
    </div>
</div>
</body>
</html>
