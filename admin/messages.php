<?php
// C:\xampp\htdocs\NewProject\admin\messages.php
// Manage Contact Messages

require_once '../config/db_connect.php';
require_once 'includes/admin_header.php';

// 1. Process Deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = isset($_POST['action']) ? trim($_POST['action']) : '';
    
    if ($action === 'delete') {
        $id = intval($_POST['id']);
        if ($id > 0) {
            try {
                $stmt = $pdo->prepare("DELETE FROM contact_messages WHERE id = ?");
                $stmt->execute([$id]);
                $_SESSION['admin_success'] = "Message deleted successfully.";
            } catch (\PDOException $e) {
                $_SESSION['admin_error'] = "Database error: " . $e->getMessage();
            }
        }
        header("Location: messages.php");
        exit;
    }
}

// 2. Fetch all messages
try {
    $messages = $pdo->query("SELECT * FROM contact_messages ORDER BY created_at DESC")->fetchAll();
} catch (\PDOException $e) {
    die("Database fetch error: " . $e->getMessage());
}
?>

<div class="card border-0 shadow-sm rounded-3 bg-white p-4">
    <h5 class="fw-bold mb-4 border-bottom pb-2">
        <i class="bi-envelope-paper-fill text-primary me-2"></i>Contact Messages Inbox
    </h5>

    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead>
                <tr>
                    <th style="width: 80px;">ID</th>
                    <th>Name</th>
                    <th>Email Address</th>
                    <th>Message Details</th>
                    <th>Submitted On</th>
                    <th class="text-end" style="width: 120px;">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($messages)): ?>
                    <?php foreach ($messages as $msg): ?>
                        <tr>
                            <td><?php echo $msg['id']; ?></td>
                            <td><strong><?php echo htmlspecialchars($msg['name']); ?></strong></td>
                            <td>
                                <a href="mailto:<?php echo htmlspecialchars($msg['email']); ?>" class="text-decoration-none">
                                    <?php echo htmlspecialchars($msg['email']); ?>
                                </a>
                            </td>
                            <td>
                                <div class="text-muted small" style="max-width: 450px; white-space: normal; word-break: break-word;">
                                    <?php echo htmlspecialchars($msg['message']); ?>
                                </div>
                            </td>
                            <td class="small text-muted"><?php echo date('M d, Y H:i', strtotime($msg['created_at'])); ?></td>
                            <td class="text-end">
                                <form action="messages.php" method="POST" onsubmit="return confirm('Are you sure you want to delete this contact message?');" class="d-inline">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?php echo $msg['id']; ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                        <i class="bi-trash me-1"></i>Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">No messages in inbox.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once 'includes/admin_footer.php'; ?>
