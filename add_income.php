<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: authentication/login.php"); exit(); }
require_once("config/db.php");
$user_id = $_SESSION['user_id'];
$msg = "";

if ($_SERVER['REQUEST_METHOD']==='POST') {
    $category = $_POST['category'];
    $amount   = $_POST['amount'];
    $date     = $_POST['income_date'];
    $note     = trim($_POST['note']);

    $stmt = $conn->prepare("INSERT INTO income (user_id, category, amount, income_date, note) VALUES (?,?,?,?,?)");
    $stmt->bind_param("isdss", $user_id, $category, $amount, $date, $note);
    $stmt->execute();

    header("Location: dashboard.php");
    exit();
}

$cats = $conn->query("SELECT category_name FROM categories WHERE type='Income'");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Add Income | NeoFinance</title>
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
        <h2 class="page-title"><span class="icon-badge"><i class="fa-solid fa-wallet"></i></span> Add Income</h2>
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
            <div class="mb-3"><label>Date</label><input type="date" name="income_date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required></div>
            <div class="mb-3"><label>Note</label><textarea name="note" class="form-control" rows="3"></textarea></div>
            <button class="btn btn-success fw-bold w-100" type="submit">Save Income</button>
        </form>
    </div>
</div>
</body>
</html>
