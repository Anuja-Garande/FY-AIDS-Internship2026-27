<?php
// C:\xampp\htdocs\NewProject\admin\login.php
// Admin Login Page

require_once '../config/db_connect.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirect if already logged in
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header("Location: index.php");
    exit;
}

$errors = [];
$username = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        $errors[] = "Username and password are required.";
    } else {
        try {
            $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ?");
            $stmt->execute([$username]);
            $admin = $stmt->fetch();

            if ($admin && password_verify($password, $admin['password'])) {
                // Set Admin Sessions
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_username'] = $admin['username'];
                $_SESSION['admin_success'] = "Welcome to the Admin Dashboard!";
                header("Location: index.php");
                exit;
            } else {
                $errors[] = "Invalid administrator username or password.";
            }
        } catch (\PDOException $e) {
            $errors[] = "Database login error: " . $e->getMessage();
        }
    }
}

require_once 'includes/admin_header.php';
?>

<div class="container" style="min-height: 100vh; display: flex; align-items: center; justify-content: center;">
    <div class="row justify-content-center w-100">
        <div class="col-lg-4 col-md-7 col-sm-10">
            <div class="card border-0 shadow-lg rounded-4 p-4 bg-white">
                <div class="text-center mb-4">
                    <h2 class="fw-bold text-primary"><i class="bi-shield-lock-fill text-warning me-2"></i>Admin Area</h2>
                    <p class="text-muted">Authenticate to access database control panel</p>
                </div>

                <!-- Server-side errors display -->
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger small">
                        <ul class="mb-0">
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo htmlspecialchars($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form action="login.php" method="POST" class="needs-validation" novalidate>
                    <!-- Username -->
                    <div class="mb-3">
                        <label for="username" class="form-label small fw-bold text-muted">Admin Username</label>
                        <input type="text" name="username" id="username" class="form-control auth-input" placeholder="e.g. admin" value="<?php echo htmlspecialchars($username); ?>" required autocomplete="off">
                        <div class="invalid-feedback">Please enter username.</div>
                    </div>

                    <!-- Password -->
                    <div class="mb-3">
                        <label for="password" class="form-label small fw-bold text-muted">Secret Password</label>
                        <input type="password" name="password" id="password" class="form-control auth-input" placeholder="••••••••" required>
                        <div class="invalid-feedback">Please enter password.</div>
                    </div>

                    <!-- Submit -->
                    <button type="submit" class="btn btn-primary w-100 py-3 rounded-pill fw-bold shadow mt-2">
                        System Sign In
                    </button>
                </form>
                
                <div class="text-center mt-4">
                    <a href="../index.php" class="small text-muted"><i class="bi-arrow-left me-1"></i>Back to Main Site</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/admin_footer.php'; ?>
