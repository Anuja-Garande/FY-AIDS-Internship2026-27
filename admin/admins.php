<?php
session_start();
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

if (!isAdminLoggedIn()) {
    redirect('login.php');
}

$db = Database::getInstance();

if (isset($_POST['delete_admin'])) {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        flash('error', 'Invalid request.');
        redirect('admins.php');
    }
    $id = intval($_POST['id'] ?? 0);
    if ($id === intval($_SESSION['admin_id'])) {
        flash('error', 'You cannot delete your own account.');
        redirect('admins.php');
    }
    $db->query("DELETE FROM admins WHERE id=?", [$id]);
    unset($_SESSION['csrf_token']);
    flash('success', 'Admin deleted.');
    redirect('admins.php');
}

$search = $_GET['search'] ?? '';
$page = max(1, intval($_GET['page'] ?? 1));
$per_page = 15;
$offset = ($page - 1) * $per_page;

$where = "1=1";
$params = [];

if (!empty($search)) {
    $where .= " AND (name LIKE :search OR email LIKE :search2 OR phone LIKE :search3)";
    $params[':search'] = "%$search%";
    $params[':search2'] = "%$search%";
    $params[':search3'] = "%$search%";
}

$total = $db->query("SELECT COUNT(*) as count FROM admins WHERE $where", $params)->fetch()['count'];
$total_pages = max(1, ceil($total / $per_page));

$admins = $db->query("SELECT * FROM admins WHERE $where ORDER BY id DESC LIMIT $per_page OFFSET $offset", $params)->fetchAll();

$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
$pageTitle = 'Admins';
include __DIR__ . '/includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="fw-bold mb-0">Admins (<?= number_format($total) ?>)</h5>
    <a href="<?= BASE_URL ?>/admin/add_admin.php" class="btn btn-primary"><i class="fas fa-plus me-1"></i>Add Admin</a>
</div>

<?php $flash = getFlash('success'); if ($flash): ?>
<div class="alert alert-success alert-dismissible fade show"><i class="fas fa-check-circle me-2"></i><?= sanitize($flash) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>
<?php $flash = getFlash('error'); if ($flash): ?>
<div class="alert alert-danger alert-dismissible fade show"><i class="fas fa-exclamation-circle me-2"></i><?= sanitize($flash) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>

<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-6"><input type="text" name="search" class="form-control" placeholder="Search by name, email, phone..." value="<?= sanitize($search) ?>"></div>
            <div class="col-md-2"><button type="submit" class="btn btn-primary w-100"><i class="fas fa-search"></i></button></div>
            <div class="col-md-2"><a href="<?= BASE_URL ?>/admin/admins.php" class="btn btn-outline-secondary w-100">Clear</a></div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr><th>ID</th><th>Avatar</th><th>Name</th><th>Email</th><th>Phone</th><th>Role</th><th>Status</th><th>Joined</th><th>Actions</th></tr>
                </thead>
                <tbody>
                    <?php if (empty($admins)): ?>
                    <tr><td colspan="9" class="text-center text-muted py-4">No admins found</td></tr>
                    <?php endif; ?>
                    <?php foreach ($admins as $admin): ?>
                    <tr>
                        <td class="fw-semibold">#<?= $admin['id'] ?></td>
                        <td>
                            <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:40px;height:40px;background:linear-gradient(135deg,#e94560,#0f3460);color:#fff;font-weight:600;font-size:14px;">
                                <?= strtoupper(substr($admin['name'], 0, 1)) ?>
                            </div>
                        </td>
                        <td class="fw-semibold"><?= sanitize($admin['name']) ?></td>
                        <td><?= sanitize($admin['email']) ?></td>
                        <td><?= sanitize($admin['phone'] ?? 'N/A') ?></td>
                        <td><span class="badge bg-<?= $admin['role'] === 'super_admin' ? 'danger' : ($admin['role'] === 'admin' ? 'primary' : 'info') ?>"><?= ucfirst(str_replace('_', ' ', $admin['role'])) ?></span></td>
                        <td>
                            <button class="btn btn-sm btn-<?= ($admin['status'] ?? 1) ? 'success' : 'danger' ?> btn-action" onclick="toggleStatus('admins',<?= $admin['id'] ?>,'status',this)">
                                <?= ($admin['status'] ?? 1) ? 'Active' : 'Inactive' ?>
                            </button>
                        </td>
                        <td><?= date('M d, Y', strtotime($admin['created_at'])) ?></td>
                        <td>
                            <?php if ($admin['id'] != $_SESSION['admin_id']): ?>
                            <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this admin?')">
                                <input type="hidden" name="delete_admin" value="1">
                                <input type="hidden" name="csrf_token" value="<?= sanitize($_SESSION['csrf_token']) ?>">
                                <input type="hidden" name="id" value="<?= $admin['id'] ?>">
                                <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                            </form>
                            <?php else: ?>
                            <span class="text-muted" style="font-size:12px;">You</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php if ($total_pages > 1): ?>
        <div class="d-flex justify-content-center p-3">
            <nav><ul class="pagination pagination-sm mb-0">
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <li class="page-item <?= $i === $page ? 'active' : '' ?>"><a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>"><?= $i ?></a></li>
                <?php endfor; ?>
            </ul></nav>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php
$extraScripts = '<script>
function deleteAdmin(id) {
    Swal.fire({
        title: "Are you sure?",
        text: "This action cannot be undone!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#e94560",
        cancelButtonColor: "#6c757d",
        confirmButtonText: "Yes, delete it!"
    }).then((result) => {
        if (result.isConfirmed) {
            fetch("' . BASE_URL . '/admin/ajax/delete_admin.php", {
                method: "POST",
                headers: {"Content-Type": "application/x-www-form-urlencoded"},
                body: "id=" + id
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({icon:"success",title:"Deleted",text:data.message,timer:1500,showConfirmButton:false}).then(() => location.reload());
                } else {
                    Swal.fire({icon:"error",title:"Error",text:data.message});
                }
            })
            .catch(() => Swal.fire({icon:"error",title:"Error",text:"Something went wrong"}));
        }
    });
}
</script>';
include __DIR__ . '/includes/footer.php';
?>
