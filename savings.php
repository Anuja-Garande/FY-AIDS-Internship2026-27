<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: authentication/login.php"); exit(); }
require_once("config/db.php");
$user_id = $_SESSION['user_id'];
$msg = "";

// ADD GOAL
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['add_goal'])) {
    $goal = trim($_POST['goal_name']);
    $target = $_POST['target_amount'];
    $deadline = $_POST['deadline'];
    $stmt = $conn->prepare("INSERT INTO savings (user_id, goal_name, target_amount, saved_amount, deadline) VALUES (?,?,?,0,?)");
    $stmt->bind_param("isds", $user_id, $goal, $target, $deadline);
    $stmt->execute();
    $msg = "Savings goal created!";
}

// ADD FUNDS TO GOAL
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['add_funds'])) {
    $goalId = $_POST['goal_id'];
    $amount = $_POST['amount'];
    $stmt = $conn->prepare("UPDATE savings SET saved_amount = saved_amount + ? WHERE id=? AND user_id=?");
    $stmt->bind_param("dii", $amount, $goalId, $user_id);
    $stmt->execute();
    $msg = "Funds added to goal!";
}

// DELETE GOAL
if (isset($_GET['delete'])) {
    $stmt = $conn->prepare("DELETE FROM savings WHERE id=? AND user_id=?");
    $stmt->bind_param("ii", $_GET['delete'], $user_id);
    $stmt->execute();
    header("Location: savings.php");
    exit();
}

$stmt = $conn->prepare("SELECT * FROM savings WHERE user_id=? ORDER BY deadline ASC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$goals = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Savings | NeoFinance</title>
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
            <div class="page-eyebrow">Ledger · Goals</div>
            <h2 class="page-title"><span class="icon-badge"><i class="fa-solid fa-piggy-bank"></i></span> Savings Goals</h2>
        </div>
        <button class="btn btn-info text-white fw-bold" data-bs-toggle="modal" data-bs-target="#addGoalModal">
            <i class="fa-solid fa-plus"></i> New Goal
        </button>
    </div>

    <?php if ($msg): ?><div class="alert alert-success"><?php echo $msg; ?></div><?php endif; ?>

    <div class="row g-4">
        <?php if ($goals->num_rows === 0): ?>
            <div class="col-12">
                <div class="dashboard-card text-center">
                    <p class="text-muted m-0">No savings goals yet. Create one to get started!</p>
                </div>
            </div>
        <?php endif; ?>

        <?php while ($g = $goals->fetch_assoc()):
            $pct = $g['target_amount'] > 0 ? min(100, ($g['saved_amount']/$g['target_amount'])*100) : 0;
            $daysLeft = (strtotime($g['deadline']) - time()) / 86400;
        ?>
        <div class="col-lg-4 col-md-6">
            <div class="dashboard-card h-100">
                <div class="d-flex justify-content-between align-items-start">
                    <h4><?php echo htmlspecialchars($g['goal_name']); ?></h4>
                    <a href="?delete=<?php echo $g['id']; ?>" class="text-danger" onclick="return confirm('Delete this goal?')">
                        <i class="fa-solid fa-trash"></i>
                    </a>
                </div>
                <p class="text-muted mb-1">
                    ₹<?php echo number_format($g['saved_amount'],2); ?> of ₹<?php echo number_format($g['target_amount'],2); ?>
                </p>
                <div class="progress mb-2" style="height:14px;">
                    <div class="progress-bar bg-success" style="width:<?php echo $pct; ?>%"><?php echo round($pct); ?>%</div>
                </div>
                <p class="small text-muted mb-3">
                    <i class="fa-solid fa-calendar"></i>
                    <?php echo $daysLeft >= 0 ? round($daysLeft)." days left" : "Deadline passed"; ?>
                    (<?php echo date("d M Y", strtotime($g['deadline'])); ?>)
                </p>
                <button class="btn btn-outline-info btn-sm w-100" data-bs-toggle="modal" data-bs-target="#fundModal<?php echo $g['id']; ?>">
                    <i class="fa-solid fa-plus"></i> Add Funds
                </button>
            </div>
        </div>

        <!-- Add Funds Modal -->
        <div class="modal fade" id="fundModal<?php echo $g['id']; ?>" tabindex="-1">
            <div class="modal-dialog">
                <form method="POST" class="modal-content bg-dark text-light">
                    <div class="modal-header"><h5 class="modal-title">Add Funds to <?php echo htmlspecialchars($g['goal_name']); ?></h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
                    <div class="modal-body">
                        <input type="hidden" name="goal_id" value="<?php echo $g['id']; ?>">
                        <label>Amount (₹)</label>
                        <input type="number" step="0.01" name="amount" class="form-control" required>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" name="add_funds" class="btn btn-info text-white">Add</button>
                    </div>
                </form>
            </div>
        </div>
        <?php endwhile; ?>
    </div>
</div>

<!-- New Goal Modal -->
<div class="modal fade" id="addGoalModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" class="modal-content bg-dark text-light">
            <div class="modal-header"><h5 class="modal-title">New Savings Goal</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="mb-3"><label>Goal Name</label><input type="text" name="goal_name" class="form-control" required></div>
                <div class="mb-3"><label>Target Amount (₹)</label><input type="number" step="0.01" name="target_amount" class="form-control" required></div>
                <div class="mb-3"><label>Deadline</label><input type="date" name="deadline" class="form-control" required></div>
            </div>
            <div class="modal-footer">
                <button type="submit" name="add_goal" class="btn btn-info text-white">Create Goal</button>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
