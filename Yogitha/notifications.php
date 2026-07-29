<?php
// C:\xampp\htdocs\NewProject\notifications.php
// User Notification Center Page

require_once 'config/db_connect.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "Please log in to view notifications.";
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Process Actions: Mark All as Read / Delete
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = isset($_POST['action']) ? trim($_POST['action']) : '';

    if ($action === 'mark_all_read') {
        try {
            $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ?");
            $stmt->execute([$user_id]);
            $_SESSION['success'] = "All notifications marked as read.";
        } catch (\PDOException $e) {
            $_SESSION['error'] = "Error updating notifications.";
        }
        header("Location: notifications.php");
        exit;
    }

    if ($action === 'clear_all') {
        try {
            $stmt = $pdo->prepare("DELETE FROM notifications WHERE user_id = ?");
            $stmt->execute([$user_id]);
            $_SESSION['success'] = "Notification inbox cleared.";
        } catch (\PDOException $e) {
            $_SESSION['error'] = "Error clearing notifications.";
        }
        header("Location: notifications.php");
        exit;
    }
}

// Fetch user notifications
try {
    $stmt = $pdo->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC");
    $stmt->execute([$user_id]);
    $notifications = $stmt->fetchAll();
} catch (\PDOException $e) {
    $notifications = [];
}

require_once 'includes/header.php';
?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-extrabold text-dark mb-0"><i class="bi-bell-fill text-primary me-2"></i>Notification Center</h2>

        <?php if (!empty($notifications)): ?>
            <div class="d-flex gap-2">
                <form action="notifications.php" method="POST">
                    <input type="hidden" name="action" value="mark_all_read">
                    <button type="submit" class="btn btn-sm btn-outline-primary rounded-pill"><i class="bi-check-all me-1"></i>Mark All Read</button>
                </form>
                <form action="notifications.php" method="POST" onsubmit="return confirm('Clear all notifications?');">
                    <input type="hidden" name="action" value="clear_all">
                    <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill"><i class="bi-trash me-1"></i>Clear All</button>
                </form>
            </div>
        <?php endif; ?>
    </div>

    <div class="card border-0 shadow-sm rounded-4 bg-white p-4">
        <?php if (!empty($notifications)): ?>
            <div class="d-flex flex-column gap-3">
                <?php foreach ($notifications as $n): ?>
                    <div class="p-3 rounded-3 border <?php echo $n['is_read'] ? 'bg-white' : 'bg-light border-primary-subtle'; ?> d-flex align-items-start gap-3">
                        <div class="fs-4 text-primary mt-1">
                            <?php if ($n['type'] === 'booking'): ?>
                                <i class="bi-calendar-check-fill text-success"></i>
                            <?php elseif ($n['type'] === 'offer'): ?>
                                <i class="bi-tag-fill text-warning"></i>
                            <?php else: ?>
                                <i class="bi-info-circle-fill text-primary"></i>
                            <?php endif; ?>
                        </div>
                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="fw-bold text-dark small"><?php echo ucfirst($n['type']); ?> Update</span>
                                <span class="text-muted small" style="font-size: 0.8rem;"><?php echo date('M d, Y h:i A', strtotime($n['created_at'])); ?></span>
                            </div>
                            <p class="text-dark small mb-0"><?php echo htmlspecialchars($n['message']); ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-5">
                <i class="bi-bell-slash text-muted display-3 mb-3"></i>
                <h4 class="fw-bold text-dark">No Notifications</h4>
                <p class="text-muted">You're all caught up! Booking updates and special offers will appear here.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
