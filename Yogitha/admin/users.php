<?php
// C:\xampp\htdocs\NewProject\admin\users.php
// Manage Registered Users

require_once '../config/db_connect.php';
require_once 'includes/admin_header.php';

// 1. Process Actions (Toggle Status or Delete User)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = isset($_POST['action']) ? trim($_POST['action']) : '';

    if ($action === 'toggle_status') {
        $id = intval($_POST['id']);
        $current_status = trim($_POST['status']);
        $new_status = ($current_status === 'Active') ? 'Disabled' : 'Active';

        if ($id > 0) {
            try {
                // Do not allow disabling own logged-in admin account if matched
                $stmt = $pdo->prepare("SELECT email FROM users WHERE id = ?");
                $stmt->execute([$id]);
                $uEmail = $stmt->fetchColumn();

                if ($uEmail === $_SESSION['user_email']) {
                    $_SESSION['admin_error'] = "You cannot disable your own active user account.";
                } else {
                    $upd = $pdo->prepare("UPDATE users SET status = ? WHERE id = ?");
                    $upd->execute([$new_status, $id]);
                    $_SESSION['admin_success'] = "User status updated to '$new_status'.";
                }
            } catch (\PDOException $e) {
                $_SESSION['admin_error'] = "Failed to update user status: " . $e->getMessage();
            }
        }
        header("Location: users.php");
        exit;
    }

    if ($action === 'delete') {
        $id = intval($_POST['id']);
        if ($id > 0) {
            try {
                $stmt = $pdo->prepare("SELECT email FROM users WHERE id = ?");
                $stmt->execute([$id]);
                $uEmail = $stmt->fetchColumn();

                if ($uEmail === $_SESSION['user_email']) {
                    $_SESSION['admin_error'] = "You cannot delete your own logged-in account.";
                } else {
                    $del = $pdo->prepare("DELETE FROM users WHERE id = ?");
                    $del->execute([$id]);
                    $_SESSION['admin_success'] = "User account deleted successfully.";
                }
            } catch (\PDOException $e) {
                $_SESSION['admin_error'] = "Failed to delete user account: " . $e->getMessage();
            }
        }
        header("Location: users.php");
        exit;
    }
}

// 2. Fetch all registered users
try {
    $users = $pdo->query("SELECT * FROM users ORDER BY created_at DESC")->fetchAll();
} catch (\PDOException $e) {
    die("Database fetch error: " . $e->getMessage());
}
?>

<div class="card border-0 shadow-sm rounded-3 bg-white p-4">
    <h5 class="fw-bold mb-4 border-bottom pb-2">
        <i class="bi-people-fill text-primary me-2"></i>Manage Registered Users
    </h5>

    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead>
                <tr>
                    <th style="width: 80px;">ID</th>
                    <th>User Name</th>
                    <th>Email Address</th>
                    <th>User Role</th>
                    <th>Status Badge</th>
                    <th>Joined Date</th>
                    <th class="text-end" style="width: 250px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($users)): ?>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?php echo $user['id']; ?></td>
                            <td><strong><?php echo htmlspecialchars($user['name']); ?></strong></td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td>
                                <span class="badge <?php echo $user['role'] === 'admin' ? 'bg-danger' : 'bg-secondary'; ?>">
                                    <?php echo ucfirst($user['role']); ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge <?php echo $user['status'] === 'Active' ? 'bg-success' : 'bg-warning text-dark'; ?>">
                                    <?php echo $user['status']; ?>
                                </span>
                            </td>
                            <td class="small text-muted"><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                            <td class="text-end">
                                <div class="d-flex justify-content-end gap-2">
                                    <!-- Toggle status button -->
                                    <form action="users.php" method="POST" class="d-inline">
                                        <input type="hidden" name="action" value="toggle_status">
                                        <input type="hidden" name="id" value="<?php echo $user['id']; ?>">
                                        <input type="hidden" name="status" value="<?php echo $user['status']; ?>">
                                        
                                        <?php if ($user['status'] === 'Active'): ?>
                                            <button type="submit" class="btn btn-sm btn-outline-warning" title="Disable User Account">
                                                <i class="bi-person-x-fill me-1"></i>Disable
                                            </button>
                                        <?php else: ?>
                                            <button type="submit" class="btn btn-sm btn-outline-success" title="Activate User Account">
                                                <i class="bi-person-check-fill me-1"></i>Enable
                                            </button>
                                        <?php endif; ?>
                                    </form>

                                    <!-- Delete account button -->
                                    <form action="users.php" method="POST" onsubmit="return confirm('Are you sure you want to delete this user? All their reviews, wishlist, and itineraries will be permanently deleted.');" class="d-inline">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?php echo $user['id']; ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete User Profile">
                                            <i class="bi-trash-fill me-1"></i>Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">No users found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once 'includes/admin_footer.php'; ?>
