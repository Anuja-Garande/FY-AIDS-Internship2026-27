<?php
// C:\xampp\htdocs\NewProject\register.php
// User Registration Page

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
$name = $email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize input
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Server-side validation
    if (empty($name)) {
        $errors[] = "Name is required.";
    }
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "A valid email is required.";
    }
    if (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters long.";
    }
    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match.";
    }

    if (empty($errors)) {
        try {
            // Check if email already exists
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->rowCount() > 0) {
                $errors[] = "An account with this email address already exists.";
            } else {
                // Hash the password securely using bcrypt
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                // Insert new user (default role is 'user', status 'Active')
                $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role, status) VALUES (?, ?, ?, 'user', 'Active')");
                $stmt->execute([$name, $email, $hashed_password]);

                $_SESSION['success'] = "Registration successful! You can now log in.";
                header("Location: login.php");
                exit;
            }
        } catch (\PDOException $e) {
            $errors[] = "Error registering account. Please try again. " . $e->getMessage();
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
                    <h2 class="fw-bold"><i class="bi-person-plus text-warning me-2"></i>Sign Up</h2>
                    <p class="text-muted">Create a free TravelPortal account</p>
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

                <form action="register.php" method="POST" id="registerForm" class="needs-validation" novalidate>
                    <!-- Full Name -->
                    <div class="mb-3">
                        <label for="name" class="form-label font-weight-bold">Full Name</label>
                        <input type="text" name="name" id="name" class="form-control auth-input" placeholder="e.g. John Doe" value="<?php echo htmlspecialchars($name); ?>" required>
                        <div class="invalid-feedback">Please enter your name.</div>
                    </div>

                    <!-- Email Address -->
                    <div class="mb-3">
                        <label for="email" class="form-label font-weight-bold">Email Address</label>
                        <input type="email" name="email" id="email" class="form-control auth-input" placeholder="e.g. johndoe@gmail.com" value="<?php echo htmlspecialchars($email); ?>" required>
                        <div class="invalid-feedback">Please enter a valid email address.</div>
                    </div>

                    <!-- Password -->
                    <div class="mb-3">
                        <label for="password" class="form-label font-weight-bold">Password</label>
                        <input type="password" name="password" id="password" class="form-control auth-input" placeholder="Min. 6 characters" required minlength="6">
                        <div class="invalid-feedback">Password must be at least 6 characters long.</div>
                    </div>

                    <!-- Confirm Password -->
                    <div class="mb-3">
                        <label for="confirm_password" class="form-label font-weight-bold">Confirm Password</label>
                        <input type="password" name="confirm_password" id="confirm_password" class="form-control auth-input" placeholder="Repeat password" required>
                        <div class="invalid-feedback">Passwords must match.</div>
                    </div>

                    <!-- Register Button -->
                    <button type="submit" class="btn btn-warning w-100 py-3 rounded-pill text-dark fw-bold mb-3 mt-2 shadow-sm">
                        Create Account
                    </button>

                    <div class="text-center mt-3">
                        <span class="text-muted">Already have an account? </span>
                        <a href="login.php" class="fw-bold">Log In</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
