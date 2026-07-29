<?php
session_start();
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

if (!isAdminLoggedIn()) {
    redirect('login.php');
}

$db = Database::getInstance();

if (isset($_POST['send_notification'])) {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        flash('error', 'Invalid request.');
    } else {
        $user_id = !empty($_POST['user_id']) ? intval($_POST['user_id']) : null;
        $title = trim($_POST['title'] ?? '');
        $message = trim($_POST['message'] ?? '');
        $type = $_POST['type'] ?? 'info';

        if (empty($title) || empty($message)) {
            flash('error', 'Title and message are required.');
        } else {
            if ($user_id) {
                $db->insert("INSERT INTO notifications (user_id, title, message, type, is_read, created_at) VALUES (?, ?, ?, ?, 0, NOW())", [
                    $user_id, $title, $message, $type
                ]);
                flash('success', 'Notification sent to user.');
            } else {
                $allUsers = $db->query("SELECT id FROM users")->fetchAll();
                foreach ($allUsers as $u) {
                    $db->insert("INSERT INTO notifications (user_id, title, message, type, is_read, created_at) VALUES (?, ?, ?, ?, 0, NOW())", [
                        $u['id'], $title, $message, $type
                    ]);
                }
                flash('success', 'Notification sent to all users (' . count($allUsers) . ' users).');
            }
        }
    }
    unset($_SESSION['csrf_token']);
    redirect('notifications.php');
}

$users = $db->query("SELECT id, name, email FROM users ORDER BY name ASC")->fetchAll();

$page = max(1, intval($_GET['page'] ?? 1));
$per_page = 15;
$offset = ($page - 1) * $per_page;

$total = $db->query("SELECT COUNT(*) as count FROM notifications")->fetch()['count'];
$total_pages = max(1, ceil($total / $per_page));

$notifications = $db->query("SELECT n.*, u.name as user_name FROM notifications n LEFT JOIN users u ON n.user_id=u.id ORDER BY n.id DESC LIMIT $per_page OFFSET $offset")->fetchAll();

$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
$pageTitle = 'Notifications';
include __DIR__ . '/includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="fw-bold mb-0">Notifications (<?= number_format($total) ?>)</h5>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#sendNotificationModal"><i class="fas fa-paper-plane me-2"></i>Send Notification</button>
</div>

<div class="row g-4">
    <div class="col-lg-4">
        <div class="card mb-4">
            <div class="card-header"><h6 class="fw-bold mb-0">Send New Notification</h6></div>
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?= sanitize($_SESSION['csrf_token']) ?>">
                    <input type="hidden" name="send_notification" value="1">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Send To</label>
                        <select name="user_id" class="form-select">
                            <option value="">All Users</option>
                            <?php foreach ($users as $u): ?>
                            <option value="<?= $u['id'] ?>"><?= sanitize($u['name']) ?> (<?= sanitize($u['email']) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control" required placeholder="Notification title">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Message <span class="text-danger">*</span></label>
                        <textarea name="message" class="form-control" rows="4" required placeholder="Notification message..."></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Type</label>
                        <select name="type" class="form-select">
                            <option value="info">Info</option>
                            <option value="success">Success</option>
                            <option value="warning">Warning</option>
                            <option value="order">Order</option>
                            <option value="promo">Promotional</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary w-100"><i class="fas fa-paper-plane me-1"></i>Send</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card">
            <div class="card-header"><h6 class="fw-bold mb-0">Notification History</h6></div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead><tr><th>ID</th><th>To</th><th>Title</th><th>Message</th><th>Type</th><th>Date</th></tr></thead>
                        <tbody>
                            <?php if (empty($notifications)): ?>
                            <tr><td colspan="6" class="text-center text-muted py-4">No notifications sent yet</td></tr>
                            <?php endif; ?>
                            <?php foreach ($notifications as $notif): ?>
                            <tr>
                                <td class="fw-semibold">#<?= $notif['id'] ?></td>
                                <td><span class="badge bg-info"><?= sanitize($notif['user_name'] ?? 'All Users') ?></span></td>
                                <td class="fw-semibold"><?= sanitize($notif['title']) ?></td>
                                <td style="max-width:250px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= sanitize($notif['message']) ?></td>
                                <td><span class="badge bg-<?= match($notif['type']) { 'success'=>'success','warning'=>'warning','order'=>'primary','promo'=>'danger',default=>'secondary' } ?>"><?= sanitize(ucfirst($notif['type'])) ?></span></td>
                                <td><?= date('M d, Y H:i', strtotime($notif['created_at'])) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="d-flex justify-content-center p-3">
                    <nav><ul class="pagination pagination-sm mb-0">
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <li class="page-item <?= $i === $page ? 'active' : '' ?>"><a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a></li>
                        <?php endfor; ?>
                    </ul></nav>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
