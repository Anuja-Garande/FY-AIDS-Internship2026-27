<?php
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

if (isLoggedIn()) { redirect('index.php'); }

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid security token.';
    } else {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $remember = isset($_POST['remember']);

        if (empty($email) || empty($password)) {
            $error = 'Please fill in all fields.';
        } else {
            $auth = new Auth();
            $result = $auth->login($email, $password);

            if ($result['success']) {
                if ($remember) {
                    $token = bin2hex(random_bytes(32));
                    setcookie('remember_token', $token, time() + (86400 * 30), '/');
                    $db = Database::getInstance();
                    $db->update("UPDATE users SET remember_token = ? WHERE id = ?", [$token, $result['user']['id']]);
                }
                if (isset($result['is_admin'])) {
                    $_SESSION['admin_id'] = $result['user']['id'];
                    $_SESSION['admin_name'] = $result['user']['name'];
                    redirect('admin/index.php');
                }
                $redirect = $_SESSION['redirect_after_login'] ?? 'index.php';
                unset($_SESSION['redirect_after_login']);
                redirect($redirect);
            } else {
                $error = $result['message'] ?? 'Invalid email or password.';
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
    <title>Login | <?= SITE_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; min-height: 100vh; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center; padding: 20px; }
        .login-card { background: rgba(255,255,255,0.9); backdrop-filter: blur(20px); border-radius: 24px; padding: 40px; max-width: 440px; width: 100%; box-shadow: 0 20px 60px rgba(0,0,0,0.2); border: 1px solid rgba(255,255,255,0.3); }
        .login-card h2 { font-weight: 700; }
        .form-control { border-radius: 12px; padding: 12px 16px; border: 1px solid #e0e0e0; }
        .form-control:focus { border-color: #667eea; box-shadow: 0 0 0 3px rgba(102,126,234,0.15); }
        .btn-gradient { background: linear-gradient(135deg, #667eea, #764ba2); border: none; border-radius: 12px; padding: 12px; font-weight: 600; color: white; transition: all 0.3s; }
        .btn-gradient:hover { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(102,126,234,0.4); color: white; }
        .input-group-text { border-radius: 12px 0 0 12px; border: 1px solid #e0e0e0; border-right: none; background: white; }
        .input-group .form-control { border-radius: 0 12px 12px 0; }
        .brand-logo { font-size: 2rem; font-weight: 800; background: linear-gradient(135deg, #667eea, #764ba2); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="text-center mb-4">
            <a href="<?= BASE_URL ?>" class="text-decoration-none brand-logo"><img src="<?= BASE_URL ?>assets/images/shopsphere logo.jpeg" alt="<?= SITE_NAME ?>" style="height:40px;width:auto;"></a>
            <p class="text-muted mt-2">Welcome back! Please login to your account.</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($error) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">

            <div class="mb-3">
                <label class="form-label fw-semibold">Email Address</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                    <input type="email" class="form-control" name="email" placeholder="Enter your email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Password</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                    <input type="password" class="form-control" name="password" placeholder="Enter your password" id="password" required>
                    <button type="button" class="btn btn-outline-secondary" onclick="togglePassword()"><i class="fas fa-eye" id="toggleIcon"></i></button>
                </div>
            </div>

            <div class="d-flex justify-content-between align-items-center mb-4">
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" id="remember" name="remember">
                    <label class="form-check-label" for="remember">Remember me</label>
                </div>
                <a href="forgot_password.php" class="text-decoration-none text-primary small">Forgot Password?</a>
            </div>

            <button type="submit" class="btn btn-gradient w-100 mb-3"><i class="fas fa-sign-in-alt me-2"></i>Login</button>
        </form>

        <div class="text-center">
            <span class="text-muted">Don't have an account?</span>
            <a href="register.php" class="text-decoration-none fw-semibold"> Register Now</a>
        </div>
        <div class="text-center mt-3">
            <a href="index.php" class="text-decoration-none text-muted small"><i class="fas fa-arrow-left me-1"></i>Back to Home</a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function togglePassword() {
        const p = document.getElementById('password');
        const i = document.getElementById('toggleIcon');
        if (p.type === 'password') { p.type = 'text'; i.classList.replace('fa-eye', 'fa-eye-slash'); }
        else { p.type = 'password'; i.classList.replace('fa-eye-slash', 'fa-eye'); }
    }
    </script>
</body>
</html>
