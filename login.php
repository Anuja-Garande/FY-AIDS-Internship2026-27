<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';
$pageTitle = 'Login';

if (isLoggedIn()) { redirect('/tourism-portal/dashboard.php'); }

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $lockedMinutes = isLoginLocked($pdo, $email);
    if ($lockedMinutes > 0) {
        $error = "Too many failed attempts. Please try again in $lockedMinutes minute(s).";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            clearLoginAttempts($pdo, $email);
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['user_name'] = $user['full_name'];
            $redirect = $_SESSION['redirect_after_login'] ?? '/tourism-portal/dashboard.php';
            unset($_SESSION['redirect_after_login']);
            redirect($redirect);
        } else {
            registerFailedLogin($pdo, $email);
            $error = 'Invalid email or password. Please try again.';
        }
    }
}

require __DIR__ . '/includes/header.php';
?>

<section class="auth-wrapper">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-lg-9">
        <div class="auth-card row g-0" data-aos="fade-up">
          <div class="col-lg-5 auth-side">
            <i class="bi bi-compass-fill" style="font-size:2.4rem;color:var(--accent);"></i>
            <h2 class="text-white mt-3">Welcome Back</h2>
            <p style="opacity:.85">Log in to manage your bookings, wishlist, and continue exploring Incredible India.</p>
          </div>
          <div class="col-lg-7 auth-form-side">
            <h3 class="mb-4">Login to Your Account</h3>

            <?php if ($error): ?><div class="alert alert-danger"><?= h($error) ?></div><?php endif; ?>

            <form method="post" novalidate>
              <div class="mb-3">
                <label class="form-label">Email Address</label>
                <input type="email" name="email" class="form-control" required autofocus>
              </div>
              <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" required>
              </div>
              <button type="submit" class="btn-brand w-100 mt-2">Login</button>
              <p class="text-center mt-3 mb-0 small">Don't have an account? <a href="/tourism-portal/register.php">Sign up here</a></p>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
