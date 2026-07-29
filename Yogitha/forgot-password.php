<?php
// C:\xampp\htdocs\NewProject\forgot-password.php
// User Forgot Password & Reset Page

require_once 'config/db_connect.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirect logged in user
if (isset($_SESSION['user_id'])) {
    header("Location: profile.php");
    exit;
}

$errors = [];
$success_msg = '';
$step = 1; // 1 = Enter Email, 2 = Reset Password
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // Step 1: Verify Email
    if ($action === 'verify_email') {
        $email = trim($_POST['email'] ?? '');
        if (empty($email)) {
            $errors[] = "Please enter your email address.";
        } else {
            try {
                $stmt = $pdo->prepare("SELECT id, name, email FROM users WHERE email = ?");
                $stmt->execute([$email]);
                $user = $stmt->fetch();

                if ($user) {
                    $step = 2;
                    $_SESSION['reset_user_id'] = $user['id'];
                    $_SESSION['reset_user_email'] = $user['email'];
                } else {
                    $errors[] = "No account found registered with this email address.";
                }
            } catch (\PDOException $e) {
                $errors[] = "Database query error: " . $e->getMessage();
            }
        }
    }

    // Step 2: Reset Password
    if ($action === 'reset_password') {
        $email = $_SESSION['reset_user_email'] ?? '';
        $user_id = $_SESSION['reset_user_id'] ?? 0;
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        if ($user_id <= 0 || empty($email)) {
            $errors[] = "Session expired. Please enter your email again.";
            $step = 1;
        } elseif (empty($new_password) || strlen($new_password) < 6) {
            $errors[] = "New password must be at least 6 characters long.";
            $step = 2;
        } elseif ($new_password !== $confirm_password) {
            $errors[] = "Passwords do not match. Please try again.";
            $step = 2;
        } else {
            try {
                $hashed = password_hash($new_password, PASSWORD_DEFAULT);
                $upd = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                $upd->execute([$hashed, $user_id]);

                unset($_SESSION['reset_user_id']);
                unset($_SESSION['reset_user_email']);

                $_SESSION['success'] = "Password reset successfully! Please log in with your new password.";
                header("Location: login.php");
                exit;
            } catch (\PDOException $e) {
                $errors[] = "Failed to update password: " . $e->getMessage();
                $step = 2;
            }
        }
    }
}

require_once 'includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-5 col-md-8">
            <div class="auth-card shadow-lg rounded-4 p-4 p-md-5">
                <div class="text-center mb-4">
                    <div class="bg-warning-subtle text-warning d-inline-block p-3 rounded-circle mb-3 fs-3">
                        <i class="bi-key-fill"></i>
                    </div>
                    <h2 class="fw-bold">Forgot Password</h2>
                    <p class="text-muted">
                        <?php echo $step === 1 ? 'Enter your registered email address to reset your password' : 'Create a new secure password for your account'; ?>
                    </p>
                </div>

                <!-- Errors Display -->
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <ul class="mb-0 ps-3">
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo htmlspecialchars($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <?php if ($step === 1): ?>
                    <!-- Step 1: Email Form -->
                    <form action="forgot-password.php" method="POST" class="needs-validation" novalidate>
                        <input type="hidden" name="action" value="verify_email">
                        
                        <div class="mb-4">
                            <label for="email" class="form-label font-weight-bold">Registered Email Address</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted"><i class="bi-envelope"></i></span>
                                <input type="email" name="email" id="email" class="form-control auth-input" placeholder="e.g. user@gmail.com" value="<?php echo htmlspecialchars($email); ?>" required>
                            </div>
                            <div class="invalid-feedback">Please enter a valid email address.</div>
                        </div>

                        <button type="submit" class="btn btn-warning w-100 py-3 rounded-pill text-dark fw-bold mb-3 shadow-sm">
                            <i class="bi-arrow-right-circle me-1"></i>Continue
                        </button>
                    </form>
                <?php else: ?>
                    <!-- Step 2: New Password Form -->
                    <form action="forgot-password.php" method="POST" class="needs-validation" novalidate>
                        <input type="hidden" name="action" value="reset_password">

                        <div class="alert alert-info py-2 px-3 small mb-3">
                            <i class="bi-person-check me-1"></i> Account: <strong><?php echo htmlspecialchars($_SESSION['reset_user_email'] ?? ''); ?></strong>
                        </div>
                        
                        <div class="mb-3">
                            <label for="new_password" class="form-label font-weight-bold">New Password</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted"><i class="bi-lock"></i></span>
                                <input type="password" name="new_password" id="new_password" class="form-control auth-input" placeholder="At least 6 characters" minlength="6" required>
                            </div>
                            <div class="invalid-feedback">Password must be at least 6 characters long.</div>
                        </div>

                        <div class="mb-4">
                            <label for="confirm_password" class="form-label font-weight-bold">Confirm New Password</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted"><i class="bi-shield-check"></i></span>
                                <input type="password" name="confirm_password" id="confirm_password" class="form-control auth-input" placeholder="Re-enter new password" minlength="6" required>
                            </div>
                            <div class="invalid-feedback">Please confirm your new password.</div>
                        </div>

                        <button type="submit" class="btn btn-warning w-100 py-3 rounded-pill text-dark fw-bold mb-3 shadow-sm">
                            <i class="bi-check-circle me-1"></i>Reset Password
                        </button>
                    </form>
                <?php endif; ?>

                <div class="text-center mt-3">
                    <a href="login.php" class="text-decoration-none fw-bold small text-muted">
                        <i class="bi-arrow-left me-1"></i>Back to Log In
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
