<?php
session_start();
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

if (!isAdminLoggedIn()) {
    redirect('login.php');
}

$db = Database::getInstance();

if (isset($_POST['delete_user'])) {
    $id = intval($_POST['id'] ?? 0);
    $db->query("DELETE FROM cart WHERE user_id=?", [$id]);
    $db->query("DELETE FROM wishlist_items WHERE user_id=?", [$id]);
    $db->query("DELETE FROM reviews WHERE user_id=?", [$id]);
    $db->query("DELETE FROM users WHERE id=?", [$id]);
    flash('success', 'User deleted.');
    redirect('users.php');
}

$search = $_GET['search'] ?? '';
$page = max(1, intval($_GET['page'] ?? 1));
$per_page = 15;
$offset = ($page - 1) * $per_page;

$where = "1=1";
$params = [];

if (!empty($search)) {
    $where .= " AND (u.name LIKE :search OR u.email LIKE :search2 OR u.phone LIKE :search3)";
    $params[':search'] = "%$search%";
    $params[':search2'] = "%$search%";
    $params[':search3'] = "%$search%";
}

$total = $db->query("SELECT COUNT(*) as count FROM users u WHERE $where", $params)->fetch()['count'];
$total_pages = max(1, ceil($total / $per_page));

$users = $db->query("SELECT u.* FROM users u WHERE $where ORDER BY u.id DESC LIMIT $per_page OFFSET $offset", $params)->fetchAll();

$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
$pageTitle = 'Users';
include __DIR__ . '/includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="fw-bold mb-0">Users (<?= number_format($total) ?>)</h5>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-6"><input type="text" name="search" class="form-control" placeholder="Search by name, email, phone..." value="<?= sanitize($search) ?>"></div>
            <div class="col-md-2"><button type="submit" class="btn btn-primary w-100"><i class="fas fa-search"></i></button></div>
            <div class="col-md-2"><a href="<?= BASE_URL ?>/admin/users.php" class="btn btn-outline-secondary w-100">Clear</a></div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr><th>ID</th><th>Avatar</th><th>Name</th><th>Email</th><th>Phone</th><th>Status</th><th>Joined</th><th>Actions</th></tr>
                </thead>
                <tbody>
                    <?php if (empty($users)): ?>
                    <tr><td colspan="8" class="text-center text-muted py-4">No users found</td></tr>
                    <?php endif; ?>
                    <?php foreach ($users as $user): ?>
                    <tr>
                        <td class="fw-semibold">#<?= $user['id'] ?></td>
                        <td>
                            <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:40px;height:40px;background:linear-gradient(135deg,#e94560,#0f3460);color:#fff;font-weight:600;font-size:14px;">
                                <?= strtoupper(substr($user['name'], 0, 1)) ?>
                            </div>
                        </td>
                        <td class="fw-semibold"><?= sanitize($user['name']) ?></td>
                        <td><?= sanitize($user['email']) ?></td>
                        <td><?= sanitize($user['phone'] ?? 'N/A') ?></td>
                        <td>
                            <button class="btn btn-sm btn-<?= ($user['status'] ?? 1) ? 'success' : 'danger' ?> btn-action" onclick="toggleStatus('users',<?= $user['id'] ?>,'status',this)">
                                <?= ($user['status'] ?? 1) ? 'Active' : 'Blocked' ?>
                            </button>
                        </td>
                        <td><?= date('M d, Y', strtotime($user['created_at'])) ?></td>
                        <td>
                            <button class="btn btn-sm btn-outline-info" onclick="viewUser(<?= $user['id'] ?>,'<?= sanitize(addslashes($user['name'])) ?>','<?= sanitize(addslashes($user['email'])) ?>','<?= sanitize(addslashes($user['phone'] ?? 'N/A')) ?>','<?= sanitize(addslashes($user['address'] ?? 'N/A')) ?>','<?= date('M d, Y', strtotime($user['created_at'])) ?>')"><i class="fas fa-eye"></i></button>
                            <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this user?')">
                                <input type="hidden" name="delete_user" value="1"><input type="hidden" name="id" value="<?= $user['id'] ?>">
                                <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="d-flex justify-content-center p-3">
            <nav><ul class="pagination pagination-sm mb-0">
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <li class="page-item <?= $i === $page ? 'active' : '' ?>"><a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>"><?= $i ?></a></li>
                <?php endfor; ?>
            </ul></nav>
        </div>
    </div>
</div>

<div class="modal fade" id="viewUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title fw-bold">User Details</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <table class="table table-borderless">
                    <tr><th style="width:120px;">ID</th><td id="modalUserId"></td></tr>
                    <tr><th>Name</th><td id="modalUserName"></td></tr>
                    <tr><th>Email</th><td id="modalUserEmail"></td></tr>
                    <tr><th>Phone</th><td id="modalUserPhone"></td></tr>
                    <tr><th>Address</th><td id="modalUserAddress"></td></tr>
                    <tr><th>Joined</th><td id="modalUserJoined"></td></tr>
                </table>
            </div>
        </div>
    </div>
</div>

<?php
$extraScripts = '<script>
function viewUser(id, name, email, phone, address, joined) {
    document.getElementById("modalUserId").textContent = "#" + id;
    document.getElementById("modalUserName").textContent = name;
    document.getElementById("modalUserEmail").textContent = email;
    document.getElementById("modalUserPhone").textContent = phone;
    document.getElementById("modalUserAddress").textContent = address;
    document.getElementById("modalUserJoined").textContent = joined;
    new bootstrap.Modal(document.getElementById("viewUserModal")).show();
}
</script>';
include __DIR__ . '/includes/footer.php';
?>
