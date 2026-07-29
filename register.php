<?php
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

if (isLoggedIn()) { redirect('index.php'); }

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid security token.';
    } else {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';
        $terms = isset($_POST['terms']);

        $errors = [];
        if (empty($name)) $errors[] = 'Name is required.';
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email is required.';
        if (empty($phone) || !preg_match('/^[6-9]\d{9}$/', $phone)) $errors[] = 'Valid 10-digit phone number is required.';
        if (strlen($password) < 6) $errors[] = 'Password must be at least 6 characters.';
        if ($password !== $confirm) $errors[] = 'Passwords do not match.';
        if (!$terms) $errors[] = 'You must agree to the Terms & Conditions.';

        $db = Database::getInstance();
        $existing = $db->fetch("SELECT id FROM users WHERE email = ?", [$email]);
        if ($existing) $errors[] = 'An account with this email already exists.';

        if (!empty($errors)) {
            $error = implode('<br>', $errors);
        } else {
            $auth = new Auth();
            $result = $auth->register($name, $email, $phone, $password);
            if ($result['success']) {
                $success = 'Account created successfully! You can now login.';
            } else {
                $error = $result['message'] ?? 'Registration failed. Please try again.';
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
    <title>Register | <?= SITE_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; min-height: 100vh; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center; padding: 20px; }
        .register-card { background: rgba(255,255,255,0.9); backdrop-filter: blur(20px); border-radius: 24px; padding: 40px; max-width: 480px; width: 100%; box-shadow: 0 20px 60px rgba(0,0,0,0.2); border: 1px solid rgba(255,255,255,0.3); }
        .form-control { border-radius: 12px; padding: 12px 16px; border: 1px solid #e0e0e0; }
        .form-control:focus { border-color: #667eea; box-shadow: 0 0 0 3px rgba(102,126,234,0.15); }
        .btn-gradient { background: linear-gradient(135deg, #667eea, #764ba2); border: none; border-radius: 12px; padding: 12px; font-weight: 600; color: white; transition: all 0.3s; }
        .btn-gradient:hover { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(102,126,234,0.4); color: white; }
        .brand-logo { font-size: 2rem; font-weight: 800; background: linear-gradient(135deg, #667eea, #764ba2); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .password-strength { height: 4px; border-radius: 2px; transition: all 0.3s; margin-top: 4px; }
        .strength-weak { background: #dc3545; width: 33%; }
        .strength-medium { background: #ffc107; width: 66%; }
        .strength-strong { background: #198754; width: 100%; }
    </style>
</head>
<body>
    <div class="register-card">
        <div class="text-center mb-4">
            <a href="<?= BASE_URL ?>" class="text-decoration-none brand-logo"><img src="<?= BASE_URL ?>assets/images/shopsphere logo.jpeg" alt="<?= SITE_NAME ?>" style="height:40px;width:auto;"></a>
            <p class="text-muted mt-2">Create your account to start shopping!</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger" role="alert"><?= $error ?></div>
        <?php endif; ?>
        <?php if (!empty($success)): ?>
            <div class="alert alert-success" role="alert"><?= $success ?></div>
        <?php endif; ?>

        <form method="POST" id="registerForm">
            <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">

            <div class="mb-3">
                <label class="form-label fw-semibold">Full Name</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-user"></i></span>
                    <input type="text" class="form-control" name="name" placeholder="Enter your full name" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Email Address</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                    <input type="email" class="form-control" name="email" placeholder="Enter your email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Phone Number</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-phone"></i></span>
                    <input type="tel" class="form-control" name="phone" placeholder="10-digit phone number" value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>" pattern="[6-9]\d{9}" required>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Password</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                    <input type="password" class="form-control" name="password" id="password" placeholder="Min 6 characters" required oninput="checkStrength(this.value)">
                </div>
                <div class="password-strength" id="strengthBar"></div>
                <small class="text-muted" id="strengthText"></small>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Confirm Password</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                    <input type="password" class="form-control" name="confirm_password" placeholder="Re-enter password" required>
                </div>
            </div>

            <div class="form-check mb-4">
                <input type="checkbox" class="form-check-input" id="terms" name="terms" required>
                <label class="form-check-label" for="terms">I agree to the <a href="#" class="text-primary">Terms & Conditions</a> and <a href="#" class="text-primary">Privacy Policy</a></label>
            </div>

            <button type="submit" class="btn btn-gradient w-100 mb-3"><i class="fas fa-user-plus me-2"></i>Create Account</button>
        </form>

        <div class="text-center">
            <span class="text-muted">Already have an account?</span>
            <a href="login.php" class="text-decoration-none fw-semibold"> Login</a>
        </div>
        <div class="text-center mt-3">
            <a href="index.php" class="text-decoration-none text-muted small"><i class="fas fa-arrow-left me-1"></i>Back to Home</a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function checkStrength(pass) {
        const bar = document.getElementById('strengthBar');
        const text = document.getElementById('strengthText');
        bar.className = 'password-strength';
        if (pass.length < 6) { bar.classList.add('strength-weak'); text.textContent = 'Weak'; text.className = 'text-danger small'; }
        else if (pass.length < 8 || !/[A-Z]/.test(pass) || !/[0-9]/.test(pass)) { bar.classList.add('strength-medium'); text.textContent = 'Medium'; text.className = 'text-warning small'; }
        else { bar.classList.add('strength-strong'); text.textContent = 'Strong'; text.className = 'text-success small'; }
        if (!pass) { bar.style.width = '0'; text.textContent = ''; }
    }
    </script>
</body>
</html>
