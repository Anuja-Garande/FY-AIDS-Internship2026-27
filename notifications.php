<?php
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'includes/functions.php';

if (!isLoggedIn()) { redirect('login.php'); }

$page_title = 'Notifications';
$db = Database::getInstance();
$user = getUser();

if (isset($_GET['read'])) {
    $nid = (int)$_GET['read'];
    $db->update("UPDATE notifications SET is_read = 1 WHERE id = :id AND user_id = :uid", [':id' => $nid, ':uid' => $user['id']]);
    redirect('notifications.php');
}

if (isset($_POST['mark_all_read'])) {
    $db->update("UPDATE notifications SET is_read = 1 WHERE user_id = :uid AND is_read = 0", [':uid' => $user['id']]);
    flash('success', 'All notifications marked as read.');
    redirect('notifications.php');
}

if (isset($_POST['delete_notification'])) {
    if (verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $nid = (int)$_POST['notification_id'];
        $db->delete("DELETE FROM notifications WHERE id = :id AND user_id = :uid", [':id' => $nid, ':uid' => $user['id']]);
        redirect('notifications.php');
    }
}

$notifications = $db->fetchAll("SELECT * FROM notifications WHERE user_id = :uid ORDER BY created_at DESC LIMIT 50", [':uid' => $user['id']]);
$unread_count = $db->fetch("SELECT COUNT(*) as cnt FROM notifications WHERE user_id = :uid AND is_read = 0", [':uid' => $user['id']])['cnt'] ?? 0;

$type_icons = [
    'order' => ['icon' => 'fas fa-box', 'color' => 'primary'],
    'promo' => ['icon' => 'fas fa-tags', 'color' => 'success'],
    'system' => ['icon' => 'fas fa-cog', 'color' => 'secondary'],
    'alert' => ['icon' => 'fas fa-exclamation-triangle', 'color' => 'warning'],
    'info' => ['icon' => 'fas fa-info-circle', 'color' => 'info'],
];
?>
<?php include 'includes/header.php'; ?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold"><i class="fas fa-bell me-2"></i>Notifications <small class="text-muted fs-6">(<?= $unread_count ?> unread)</small></h2>
        <?php if ($unread_count > 0): ?>
        <form method="POST"><input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>"><button type="submit" name="mark_all_read" class="btn btn-outline-primary btn-sm"><i class="fas fa-check-double me-1"></i>Mark All Read</button></form>
        <?php endif; ?>
    </div>

    <?php if (empty($notifications)): ?>
    <div class="text-center py-5">
        <i class="fas fa-bell-slash fa-4x text-muted mb-3"></i>
        <h4>No notifications</h4>
        <p class="text-muted">You're all caught up!</p>
    </div>
    <?php else: ?>
    <div class="list-group">
        <?php foreach ($notifications as $notif): ?>
        <?php
            $type_info = $type_icons[$notif['type'] ?? 'info'] ?? $type_icons['info'];
            $link = $notif['link'] ?? '#';
        ?>
        <div class="list-group-item list-group-item-action d-flex align-items-start gap-3 <?= !$notif['is_read'] ? 'bg-light' : '' ?>" style="border-radius:0;cursor:pointer;" onclick="window.location.href='notifications.php?read=<?= $notif['id'] ?>'">
            <div class="rounded-circle bg-<?= $type_info['color'] ?> text-white d-flex align-items-center justify-content-center flex-shrink-0" style="width:40px;height:40px;">
                <i class="<?= $type_info['icon'] ?>" style="font-size:0.9rem;"></i>
            </div>
            <div class="flex-grow-1">
                <div class="d-flex justify-content-between">
                    <h6 class="mb-1 <?= !$notif['is_read'] ? 'fw-bold' : '' ?>"><?= htmlspecialchars($notif['title'] ?? 'Notification') ?></h6>
                    <small class="text-muted"><?= timeAgo($notif['created_at']) ?></small>
                </div>
                <p class="mb-1 text-muted small"><?= htmlspecialchars($notif['message'] ?? '') ?></p>
            </div>
            <?php if (!$notif['is_read']): ?>
                <span class="badge bg-primary rounded-circle p-1" style="width:10px;height:10px;"></span>
            <?php endif; ?>
            <form method="POST" onclick="event.stopPropagation();" class="flex-shrink-0">
                <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                <input type="hidden" name="notification_id" value="<?= $notif['id'] ?>">
                <button type="submit" name="delete_notification" class="btn btn-sm btn-outline-danger" title="Delete"><i class="fas fa-times"></i></button>
            </form>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
