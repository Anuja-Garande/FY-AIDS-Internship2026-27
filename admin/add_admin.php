<?php
session_start();
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

if (!isAdminLoggedIn()) {
    redirect('login.php');
}

$db = Database::getInstance();

if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $errors[] = 'Invalid request.';
    } else {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        $phone = trim($_POST['phone'] ?? '');
        $role = $_POST['role'] ?? 'admin';
        $status = intval($_POST['status'] ?? 1);

        if (empty($name)) $errors[] = 'Name is required.';
        if (empty($email)) $errors[] = 'Email is required.';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Invalid email format.';
        if (empty($password)) $errors[] = 'Password is required.';
        if (strlen($password) < 6) $errors[] = 'Password must be at least 6 characters.';
        if ($password !== $confirm_password) $errors[] = 'Passwords do not match.';
        if (!in_array($role, ['super_admin', 'admin', 'manager'])) $errors[] = 'Invalid role.';

        if (empty($errors)) {
            $existing = $db->query("SELECT id FROM admins WHERE email = ?", [$email])->fetch();
            if ($existing) {
                $errors[] = 'An admin with this email already exists.';
            }
        }

        if (empty($errors)) {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            $db->insert(
                "INSERT INTO admins (name, email, password, phone, role, status, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())",
                [$name, $email, $hashed_password, $phone, $role, $status]
            );

            unset($_SESSION['csrf_token']);
            flash('success', 'Admin "' . $name . '" created successfully!');
            redirect('admins.php');
        }
    }
}

$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
$pageTitle = 'Add Admin';
include __DIR__ . '/includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="fw-bold mb-0">Add New Admin</h5>
    <a href="<?= BASE_URL ?>/admin/admins.php" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Back</a>
</div>

<?php if (!empty($errors)): ?>
<div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errors as $e): ?><li><?= sanitize($e) ?></li><?php endforeach; ?></ul></div>
<?php endif; ?>

<div class="row">
    <div class="col-lg-8">
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?= sanitize($_SESSION['csrf_token']) ?>">
            <div class="card mb-4">
                <div class="card-header"><h6 class="fw-bold mb-0">Admin Details</h6></div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Full Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" required value="<?= sanitize($_POST['name'] ?? '') ?>" placeholder="Enter full name">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Email Address <span class="text-danger">*</span></label>
                        <input type="email" name="email" class="form-control" required value="<?= sanitize($_POST['email'] ?? '') ?>" placeholder="Enter email address">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Phone</label>
                        <input type="text" name="phone" class="form-control" value="<?= sanitize($_POST['phone'] ?? '') ?>" placeholder="Enter phone number">
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Password <span class="text-danger">*</span></label>
                            <input type="password" name="password" class="form-control" required minlength="6" placeholder="Min 6 characters">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Confirm Password <span class="text-danger">*</span></label>
                            <input type="password" name="confirm_password" class="form-control" required minlength="6" placeholder="Re-enter password">
                        </div>
                    </div>
                </div>
            </div>
            <div class="card mb-4">
                <div class="card-header"><h6 class="fw-bold mb-0">Role & Status</h6></div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Role <span class="text-danger">*</span></label>
                            <select name="role" class="form-select">
                                <option value="admin" <?= ($_POST['role'] ?? '') === 'admin' ? 'selected' : '' ?>>Admin</option>
                                <option value="manager" <?= ($_POST['role'] ?? '') === 'manager' ? 'selected' : '' ?>>Manager</option>
                                <option value="super_admin" <?= ($_POST['role'] ?? '') === 'super_admin' ? 'selected' : '' ?>>Super Admin</option>
                            </select>
                            <div class="form-text">Super Admin has full access. Admin and Manager have limited access.</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Status</label>
                            <select name="status" class="form-select">
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            <button type="submit" class="btn btn-primary px-4"><i class="fas fa-save me-1"></i>Create Admin</button>
        </form>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
