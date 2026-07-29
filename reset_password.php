<?php
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

if (isLoggedIn()) { redirect('index.php'); }

$token = $_GET['token'] ?? '';
$message = '';
$show_form = false;

if (empty($token)) {
    $message = 'Invalid reset link.';
} else {
    $db = Database::getInstance();
    $user = $db->fetch("SELECT id FROM users WHERE reset_token = :token AND reset_expiry > NOW()", [':token' => $token]);

    if (!$user) {
        $message = 'This reset link has expired or is invalid.';
    } else {
        $show_form = true;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $message = 'Invalid security token.';
    } else {
        $password = $_POST['password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

        if (strlen($password) < 6) {
            $message = 'Password must be at least 6 characters.';
        } elseif ($password !== $confirm) {
            $message = 'Passwords do not match.';
        } else {
            $db = Database::getInstance();
            $user = $db->fetch("SELECT id FROM users WHERE reset_token = :token AND reset_expiry > NOW()", [':token' => $token]);
            if ($user) {
                $hashed = password_hash($password, PASSWORD_DEFAULT);
                $db->update("UPDATE users SET password = :pw, reset_token = NULL, reset_expiry = NULL WHERE id = :id", [
                    ':pw' => $hashed,
                    ':id' => $user['id'],
                ]);
                $message = 'success';
            } else {
                $message = 'Reset link has expired.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password | <?= SITE_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; min-height: 100vh; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center; padding: 20px; }
        .reset-card { background: rgba(255,255,255,0.9); backdrop-filter: blur(20px); border-radius: 24px; padding: 40px; max-width: 440px; width: 100%; box-shadow: 0 20px 60px rgba(0,0,0,0.2); }
        .form-control { border-radius: 12px; padding: 12px 16px; border: 1px solid #e0e0e0; }
        .form-control:focus { border-color: #667eea; box-shadow: 0 0 0 3px rgba(102,126,234,0.15); }
        .btn-gradient { background: linear-gradient(135deg, #667eea, #764ba2); border: none; border-radius: 12px; padding: 12px; font-weight: 600; color: white; }
        .btn-gradient:hover { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(102,126,234,0.4); color: white; }
        .brand-logo { font-size: 2rem; font-weight: 800; background: linear-gradient(135deg, #667eea, #764ba2); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
    </style>
</head>
<body>
    <div class="reset-card">
        <div class="text-center mb-4">
            <a href="<?= BASE_URL ?>" class="text-decoration-none brand-logo"><img src="<?= BASE_URL ?>assets/images/shopsphere logo.jpeg" alt="<?= SITE_NAME ?>" style="height:40px;width:auto;"></a>
        </div>

        <?php if ($message === 'success'): ?>
        <div class="text-center">
            <div class="mb-4">
                <div class="rounded-circle bg-success text-white d-inline-flex align-items-center justify-content-center" style="width:80px;height:80px;font-size:2rem;">
                    <i class="fas fa-check"></i>
                </div>
            </div>
            <h5 class="fw-bold">Password Reset Successfully!</h5>
            <p class="text-muted">Your password has been updated. You can now login with your new password.</p>
            <a href="login.php" class="btn btn-gradient w-100 mt-3">Login Now</a>
        </div>
        <?php elseif ($show_form): ?>
        <div class="text-center mb-4">
            <i class="fas fa-lock fa-3x text-primary opacity-50 mb-3"></i>
            <h5 class="fw-bold">Reset Your Password</h5>
            <p class="text-muted small">Enter your new password below.</p>
        </div>

        <?php if (!empty($message) && $message !== 'success'): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
            <div class="mb-3">
                <label class="form-label fw-semibold">New Password</label>
                <input type="password" class="form-control" name="password" placeholder="Min 6 characters" required>
            </div>
            <div class="mb-4">
                <label class="form-label fw-semibold">Confirm Password</label>
                <input type="password" class="form-control" name="confirm_password" placeholder="Re-enter password" required>
            </div>
            <button type="submit" class="btn btn-gradient w-100"><i class="fas fa-key me-2"></i>Reset Password</button>
        </form>
        <?php else: ?>
        <div class="text-center">
            <i class="fas fa-exclamation-triangle fa-3x text-warning mb-3"></i>
            <h5 class="fw-bold"><?= htmlspecialchars($message) ?></h5>
            <a href="forgot_password.php" class="btn btn-gradient w-100 mt-3">Request New Link</a>
        </div>
        <?php endif; ?>

        <div class="text-center mt-3">
            <a href="login.php" class="text-decoration-none text-muted small"><i class="fas fa-arrow-left me-1"></i>Back to Login</a>
        </div>
    </div>
</body>
</html>
