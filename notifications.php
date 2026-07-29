<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: authentication/login.php"); exit(); }
require_once("config/db.php");
$user_id = $_SESSION['user_id'];

// Mark one as read
if (isset($_GET['read'])) {
    $stmt = $conn->prepare("UPDATE notifications SET status='Read' WHERE id=? AND user_id=?");
    $stmt->bind_param("ii", $_GET['read'], $user_id);
    $stmt->execute();
    header("Location: notifications.php");
    exit();
}

// Mark all as read
if (isset($_GET['read_all'])) {
    $stmt = $conn->prepare("UPDATE notifications SET status='Read' WHERE user_id=?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    header("Location: notifications.php");
    exit();
}

// Delete one
if (isset($_GET['delete'])) {
    $stmt = $conn->prepare("DELETE FROM notifications WHERE id=? AND user_id=?");
    $stmt->bind_param("ii", $_GET['delete'], $user_id);
    $stmt->execute();
    header("Location: notifications.php");
    exit();
}

$stmt = $conn->prepare("SELECT * FROM notifications WHERE user_id=? ORDER BY created_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$notifications = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Notifications | NeoFinance</title>
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
            <div class="page-eyebrow">Ledger · Alerts</div>
            <h2 class="page-title"><span class="icon-badge"><i class="fa-solid fa-bell"></i></span> Notifications</h2>
        </div>
        <a href="?read_all=1" class="btn btn-outline-info btn-sm">Mark all as read</a>
    </div>

    <div class="transaction-card">
        <div class="list-group">
        <?php if ($notifications->num_rows === 0): ?>
            <div class="list-group-item bg-dark text-light text-center">No notifications yet.</div>
        <?php else: while ($n = $notifications->fetch_assoc()): ?>
            <div class="list-group-item bg-dark text-light d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <span class="status-pill <?php echo strtolower($n['status']); ?> me-2"><?php echo $n['status']; ?></span>
                    <?php echo htmlspecialchars($n['message']); ?>
                    <div class="small text-muted"><?php echo date("d M Y, H:i", strtotime($n['created_at'])); ?></div>
                </div>
                <div>
                    <?php if ($n['status']==='Unread'): ?>
                        <a href="?read=<?php echo $n['id']; ?>" class="btn btn-sm btn-outline-success me-1">Mark read</a>
                    <?php endif; ?>
                    <a href="?delete=<?php echo $n['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this notification?')">Delete</a>
                </div>
            </div>
        <?php endwhile; endif; ?>
        </div>
    </div>
</div>
</body>
</html>
