<?php
session_start();
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

if (isAdminLoggedIn()) {
    redirect('index.php');
}

$error = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error = 'Invalid request. Please try again.';
    } else {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $remember = isset($_POST['remember']);

        if (empty($email) || empty($password)) {
            $error = 'Please fill in all fields.';
        } else {
            $db = Database::getInstance();
            $stmt = $db->query("SELECT * FROM admins WHERE email = ? LIMIT 1", [$email]);
            $admin = $stmt ? $stmt->fetch() : false;

            if ($admin && password_verify($password, $admin['password'])) {
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_name'] = $admin['name'];
                $_SESSION['admin_email'] = $admin['email'];

                if ($remember) {
                    setcookie('admin_id', $admin['id'], time() + (86400 * 30), '/');
                    setcookie('admin_token', password_hash($admin['password'], PASSWORD_DEFAULT), time() + (86400 * 30), '/');
                }

                unset($_SESSION['csrf_token']);
                redirect('index.php');
            } else {
                $error = 'Invalid email or password.';
            }
        }
    }
}

$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - ShopSphere</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%); min-height: 100vh; display: flex; align-items: center; justify-content: center; font-family: 'Segoe UI', sans-serif; }
        .login-card { background: #fff; border-radius: 16px; box-shadow: 0 20px 60px rgba(0,0,0,0.3); overflow: hidden; width: 100%; max-width: 420px; }
        .login-header { background: linear-gradient(135deg, #e94560, #0f3460); padding: 30px; text-align: center; color: #fff; }
        .login-header i { font-size: 48px; margin-bottom: 10px; }
        .login-header h2 { margin: 0; font-weight: 700; font-size: 22px; }
        .login-header p { margin: 5px 0 0; opacity: 0.8; font-size: 14px; }
        .login-body { padding: 30px; }
        .form-floating { margin-bottom: 15px; }
        .form-floating .form-control { border-radius: 10px; border: 2px solid #e9ecef; padding: 12px 15px; height: 50px; }
        .form-floating .form-control:focus { border-color: #e94560; box-shadow: 0 0 0 0.2rem rgba(233,69,96,0.15); }
        .btn-login { background: linear-gradient(135deg, #e94560, #c23152); border: none; border-radius: 10px; padding: 12px; font-weight: 600; font-size: 16px; width: 100%; color: #fff; transition: all 0.3s; }
        .btn-login:hover { transform: translateY(-2px); box-shadow: 0 5px 20px rgba(233,69,96,0.4); color: #fff; }
        .form-check-label { font-size: 14px; color: #666; }
        .alert { border-radius: 10px; font-size: 14px; }
        .back-link { text-align: center; margin-top: 20px; }
        .back-link a { color: #666; text-decoration: none; font-size: 14px; }
        .back-link a:hover { color: #e94560; }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="login-header">
            <img src="<?= BASE_URL ?>assets/images/shopsphere logo.jpeg" alt="ShopSphere" style="height:50px;width:auto;margin-bottom:10px;">
            <h2>Admin Panel</h2>
            <p>ShopSphere Management System</p>
        </div>
        <div class="login-body">
            <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i><?= sanitize($error) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            <form method="POST" action="">
                <input type="hidden" name="csrf_token" value="<?= sanitize($_SESSION['csrf_token']) ?>">
                <div class="form-floating">
                    <input type="email" class="form-control" id="email" name="email" placeholder="Email Address" value="<?= sanitize($email) ?>" required autofocus>
                    <label for="email"><i class="fas fa-envelope me-2"></i>Email Address</label>
                </div>
                <div class="form-floating">
                    <input type="password" class="form-control" id="password" name="password" placeholder="Password" required>
                    <label for="password"><i class="fas fa-lock me-2"></i>Password</label>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="remember" name="remember">
                        <label class="form-check-label" for="remember">Remember me</label>
                    </div>
                </div>
                <button type="submit" class="btn btn-login">
                    <i class="fas fa-sign-in-alt me-2"></i>Sign In
                </button>
            </form>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
