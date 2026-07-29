<?php
// C:\xampp\htdocs\NewProject\login.php
// User Login Page

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
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $errors[] = "Email and password are required.";
    } else {
        try {
            // Find user by email
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                // Check if user account is active
                if ($user['status'] === 'Disabled') {
                    $errors[] = "Your account has been disabled by the administrator.";
                } else {
                    // Start User Session
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_name'] = $user['name'];
                    $_SESSION['user_email'] = $user['email'];
                    $_SESSION['user_role'] = $user['role'];
                    $_SESSION['success'] = "Welcome back, " . $user['name'] . "!";
                    
                    // If user is also an admin, we can auto-login to admin session for convenience
                    if ($user['role'] === 'admin') {
                        $_SESSION['admin_logged_in'] = true;
                        $_SESSION['admin_username'] = $user['name'];
                    }

                    header("Location: index.php");
                    exit;
                }
            } else {
                $errors[] = "Invalid email or password.";
            }
        } catch (\PDOException $e) {
            $errors[] = "Login error. Please try again. " . $e->getMessage();
        }
    }
}

require_once 'includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-5 col-md-8">
            <div class="auth-card">
                <div class="text-center mb-4">
                    <h2 class="fw-bold"><i class="bi-box-arrow-in-right text-warning me-2"></i>Log In</h2>
                    <p class="text-muted">Access your wishlist, itinerary and reviews</p>
                </div>

                <!-- Server-side errors display -->
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo htmlspecialchars($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form action="login.php" method="POST" class="needs-validation" novalidate>
                    <!-- Email Address -->
                    <div class="mb-3">
                        <label for="email" class="form-label font-weight-bold">Email Address</label>
                        <input type="email" name="email" id="email" class="form-control auth-input" placeholder="e.g. user@gmail.com" value="<?php echo htmlspecialchars($email); ?>" required>
                        <div class="invalid-feedback">Please enter a valid email address.</div>
                    </div>

                    <!-- Password -->
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label for="password" class="form-label font-weight-bold mb-0">Password</label>
                            <a href="forgot-password.php" class="small text-primary text-decoration-none fw-bold">Forgot Password?</a>
                        </div>
                        <input type="password" name="password" id="password" class="form-control auth-input" placeholder="Enter password" required>
                        <div class="invalid-feedback">Please enter your password.</div>
                    </div>

                    <!-- Login Button -->
                    <button type="submit" class="btn btn-warning w-100 py-3 rounded-pill text-dark fw-bold mb-3 mt-2 shadow-sm">
                        Log In
                    </button>

                    <div class="text-center mt-3">
                        <span class="text-muted">Don't have an account? </span>
                        <a href="register.php" class="fw-bold">Sign Up</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
