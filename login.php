<?php
require_once 'config.php';

$error = '';
$success = '';

if (isset($_GET['msg']) && $_GET['msg'] === 'registered') {
    $success = 'Account created successfully! Please sign in with your credentials.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password.';
    } else {
        try {
            // Fetch user from DB
            $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$username, $username]);
            $user = $stmt->fetch();

            if ($user && verifyUserPassword($password, $user['password'])) {
                // Set Session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['name'] = $user['name'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];

                // Redirect according to role
                if ($user['role'] === 'admin') {
                    header("Location: admin_dashboard.php");
                } elseif ($user['role'] === 'doctor') {
                    header("Location: doctor_dashboard.php");
                } else {
                    header("Location: patient_dashboard.php");
                }
                exit;
            } else {
                $error = 'Invalid username or password. Please try again.';
            }
        } catch (PDOException $e) {
            $error = 'Database error: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In | ApexCare Hospital System</title>
    
    <!-- FontAwesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body style="display: flex; align-items: center; justify-content: center; min-height: 100vh; padding: 2rem 1rem;">

    <!-- Liquid Background Blobs -->
    <div class="liquid-bg-container">
        <div class="blob blob-1"></div>
        <div class="blob blob-2"></div>
    </div>

    <div style="width: 100%; max-width: 460px;">

        <!-- Brand Backlink -->
        <div style="text-align: center; margin-bottom: 2rem;">
            <a href="index.php" class="logo-brand" style="justify-content: center;">
                <div class="logo-icon">
                    <i class="fa-solid fa-notes-medical"></i>
                </div>
                <span>Apex<span class="text-gradient">Care</span></span>
            </a>
            <p style="color: var(--text-muted); font-size: 0.9rem; margin-top: 0.5rem;">Access your medical portal & dashboard</p>
        </div>

        <!-- Glass Card Login Form -->
        <div class="glass-panel" style="padding: 2.5rem;">
            
            <h2 style="font-size: 1.6rem; text-align: center; margin-bottom: 1.5rem;">Portal Sign In</h2>

            <?php if ($error): ?>
                <div class="alert-glass alert-error">
                    <i class="fa-solid fa-circle-exclamation"></i>
                    <span><?= htmlspecialchars($error) ?></span>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert-glass alert-success">
                    <i class="fa-solid fa-circle-check"></i>
                    <span><?= htmlspecialchars($success) ?></span>
                </div>
            <?php endif; ?>

            <form action="login.php" method="POST">
                <div class="form-group">
                    <label class="form-label" for="username"><i class="fa-solid fa-user" style="color: var(--primary-cyan);"></i> Username or Email</label>
                    <input type="text" id="username" name="username" class="form-control" placeholder="e.g. yash" required>
                </div>

                <div class="form-group" style="margin-bottom: 1.5rem;">
                    <label class="form-label" for="password"><i class="fa-solid fa-key" style="color: var(--primary-cyan);"></i> Password</label>
                    <input type="password" id="password" name="password" class="form-control" placeholder="••••••••" required>
                    <div style="text-align: right; margin-top: 0.5rem;">
                        <a href="forgot_password.php" style="color: var(--primary-cyan); font-size: 0.85rem; text-decoration: none;">Forgot Password?</a>
                    </div>
                </div>

                <button type="submit" class="btn-glass" style="width: 100%; padding: 1rem;">
                    Sign In to Portal <i class="fa-solid fa-right-to-bracket"></i>
                </button>
            </form>

            <div style="text-align: center; margin-top: 1.5rem; font-size: 0.9rem; color: var(--text-muted);">
                Don't have a patient account? <a href="register.php" style="color: var(--primary-cyan); font-weight: 600; text-decoration: none;">Register Here</a>
            </div>

        </div>
    </div>

</body>
</html>