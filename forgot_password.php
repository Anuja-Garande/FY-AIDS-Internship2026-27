<?php
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

if (isLoggedIn()) { redirect('index.php'); }

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $message = 'Invalid security token.';
    } else {
        $email = trim($_POST['email'] ?? '');
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $message = 'Please enter a valid email address.';
        } else {
            $db = Database::getInstance();
            $user = $db->fetch("SELECT id, email FROM users WHERE email = :email", [':email' => $email]);
            if ($user) {
                $token = bin2hex(random_bytes(32));
                $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));
                $db->update("UPDATE users SET reset_token = :token, reset_expiry = :expiry WHERE id = :id", [
                    ':token' => $token,
                    ':expiry' => $expiry,
                    ':id' => $user['id'],
                ]);
                $message = 'success';
            } else {
                $message = 'success';
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
    <title>Forgot Password | <?= SITE_NAME ?></title>
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
            <h5 class="fw-bold">Reset Link Sent!</h5>
            <p class="text-muted">If an account exists with that email, we've sent a password reset link. Please check your inbox.</p>
            <a href="login.php" class="btn btn-gradient w-100 mt-3">Back to Login</a>
        </div>
        <?php else: ?>
        <div class="text-center mb-4">
            <div class="mb-3">
                <i class="fas fa-key fa-3x text-primary opacity-50"></i>
            </div>
            <h5 class="fw-bold">Forgot Password?</h5>
            <p class="text-muted small">Enter your email and we'll send you a reset link.</p>
        </div>

        <?php if (!empty($message) && $message !== 'success'): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
            <div class="mb-4">
                <label class="form-label fw-semibold">Email Address</label>
                <input type="email" class="form-control" name="email" placeholder="Enter your registered email" required>
            </div>
            <button type="submit" class="btn btn-gradient w-100"><i class="fas fa-paper-plane me-2"></i>Send Reset Link</button>
        </form>

        <div class="text-center mt-3">
            <a href="login.php" class="text-decoration-none text-muted small"><i class="fas fa-arrow-left me-1"></i>Back to Login</a>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>
