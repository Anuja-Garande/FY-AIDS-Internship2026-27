<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';

if (isAdminLoggedIn()) { redirect('/tourism-portal/admin/dashboard.php'); }

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $lockedMinutes = isLoginLocked($pdo, $username);
    if ($lockedMinutes > 0) {
        $error = "Too many failed attempts. Please try again in $lockedMinutes minute(s).";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM admin WHERE username = ?");
        $stmt->execute([$username]);
        $admin = $stmt->fetch();

        if ($admin && password_verify($password, $admin['password'])) {
            clearLoginAttempts($pdo, $username);
            $_SESSION['admin_id'] = $admin['admin_id'];
            $_SESSION['admin_username'] = $admin['username'];
            redirect('/tourism-portal/admin/dashboard.php');
        } else {
            registerFailedLogin($pdo, $username);
            $error = 'Invalid username or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Login | BharatYatra</title>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:wght@600;700&family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
<link rel="stylesheet" href="/tourism-portal/assets/css/style.css">
</head>
<body>
<section class="auth-wrapper">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-lg-5">
        <div class="auth-card">
          <div class="auth-side" style="padding:40px;">
            <i class="bi bi-shield-lock-fill" style="font-size:2.2rem;color:var(--accent);"></i>
            <h3 class="text-white mt-3 mb-1">Admin Panel</h3>
            <p class="mb-0" style="opacity:.85">BharatYatra Tourism Portal</p>
          </div>
          <div class="auth-form-side" style="padding:40px;">
            <?php if ($error): ?><div class="alert alert-danger"><?= h($error) ?></div><?php endif; ?>
            <form method="post">
              <div class="mb-3">
                <label class="form-label">Username</label>
                <input type="text" name="username" class="form-control" required autofocus>
              </div>
              <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" required>
              </div>
              <button type="submit" class="btn-brand w-100">Login to Admin Panel</button>
              <p class="text-center small text-muted mt-3 mb-0"><a href="/tourism-portal/index.php">&larr; Back to website</a></p>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
</body>
</html>
