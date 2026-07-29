<?php
$pageTitle = 'My Profile';
require __DIR__ . '/includes/header.php';

$adminId = $_SESSION['admin_id'];
$stmt = $pdo->prepare("SELECT * FROM admin WHERE admin_id = ?");
$stmt->execute([$adminId]);
$admin = $stmt->fetch();

$errors = [];

// Update profile info (username / email)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);

    if ($username === '') $errors[] = 'Username cannot be empty.';

    if (empty($errors)) {
        $check = $pdo->prepare("SELECT admin_id FROM admin WHERE username = ? AND admin_id != ?");
        $check->execute([$username, $adminId]);
        if ($check->fetch()) {
            $errors[] = 'That username is already taken.';
        } else {
            $upd = $pdo->prepare("UPDATE admin SET username = ?, email = ? WHERE admin_id = ?");
            $upd->execute([$username, $email, $adminId]);
            $_SESSION['admin_username'] = $username;
            setFlash('success', 'Profile updated successfully.');
            redirect('/tourism-portal/admin/profile.php');
        }
    }
}

// Change password
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $current = $_POST['current_password'] ?? '';
    $new = $_POST['new_password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if (!password_verify($current, $admin['password'])) {
        $errors[] = 'Current password is incorrect.';
    } elseif (strlen($new) < 6) {
        $errors[] = 'New password must be at least 6 characters.';
    } elseif ($new !== $confirm) {
        $errors[] = 'New passwords do not match.';
    } else {
        $hash = password_hash($new, PASSWORD_DEFAULT);
        $upd = $pdo->prepare("UPDATE admin SET password = ? WHERE admin_id = ?");
        $upd->execute([$hash, $adminId]);
        setFlash('success', 'Password changed successfully.');
        redirect('/tourism-portal/admin/profile.php');
    }
}
?>

<div class="row g-4">
  <div class="col-lg-6">
    <div class="admin-card">
      <h6 class="mb-3"><i class="bi bi-person-circle"></i> Profile Information</h6>
      <?php if ($errors): ?>
        <div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errors as $e): ?><li><?= h($e) ?></li><?php endforeach; ?></ul></div>
      <?php endif; ?>
      <form method="post">
        <div class="mb-3">
          <label class="form-label">Username</label>
          <input type="text" name="username" class="form-control" value="<?= h($admin['username']) ?>" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Email Address</label>
          <input type="email" name="email" class="form-control" value="<?= h($admin['email']) ?>">
        </div>
        <button type="submit" name="update_profile" class="btn-teal">Update Profile</button>
      </form>
    </div>
  </div>

  <div class="col-lg-6">
    <div class="admin-card">
      <h6 class="mb-3"><i class="bi bi-shield-lock"></i> Change Password</h6>
      <form method="post">
        <div class="mb-3">
          <label class="form-label">Current Password</label>
          <input type="password" name="current_password" class="form-control" required>
        </div>
        <div class="mb-3">
          <label class="form-label">New Password</label>
          <input type="password" name="new_password" class="form-control" required minlength="6">
        </div>
        <div class="mb-3">
          <label class="form-label">Confirm New Password</label>
          <input type="password" name="confirm_password" class="form-control" required minlength="6">
        </div>
        <button type="submit" name="change_password" class="btn-brand">Change Password</button>
      </form>
    </div>
  </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
