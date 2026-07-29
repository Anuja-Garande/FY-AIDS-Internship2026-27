<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';
$pageTitle = 'Sign Up';

if (isLoggedIn()) { redirect('/tourism-portal/dashboard.php'); }

$errors = [];
$old = ['full_name' => '', 'email' => '', 'contact_number' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old['full_name'] = trim($_POST['full_name'] ?? '');
    $old['email'] = trim($_POST['email'] ?? '');
    $old['contact_number'] = trim($_POST['contact_number'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';

    if ($old['full_name'] === '') $errors[] = 'Full name is required.';
    if (!filter_var($old['email'], FILTER_VALIDATE_EMAIL)) $errors[] = 'Please enter a valid email address.';
    if (strlen($password) < 6) $errors[] = 'Password must be at least 6 characters.';
    if ($password !== $confirm) $errors[] = 'Passwords do not match.';

    if (empty($errors)) {
        $check = $pdo->prepare("SELECT user_id FROM users WHERE email = ?");
        $check->execute([$old['email']]);
        if ($check->fetch()) {
            $errors[] = 'An account with this email already exists. Please login instead.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $ins = $pdo->prepare("INSERT INTO users (full_name, email, password, contact_number) VALUES (?, ?, ?, ?)");
            $ins->execute([$old['full_name'], $old['email'], $hash, $old['contact_number']]);
            setFlash('success', 'Account created successfully! Please log in.');
            redirect('/tourism-portal/login.php');
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
            <h2 class="text-white mt-3">Join BharatYatra</h2>
            <p style="opacity:.85">Create an account to book tour packages, save your wishlist, and share reviews with fellow travellers.</p>
          </div>
          <div class="col-lg-7 auth-form-side">
            <h3 class="mb-4">Create Your Account</h3>

            <?php if ($errors): ?>
              <div class="alert alert-danger">
                <ul class="mb-0">
                  <?php foreach ($errors as $e): ?><li><?= h($e) ?></li><?php endforeach; ?>
                </ul>
              </div>
            <?php endif; ?>

            <form method="post" novalidate>
              <div class="mb-3">
                <label class="form-label">Full Name</label>
                <input type="text" name="full_name" class="form-control" value="<?= h($old['full_name']) ?>" required>
              </div>
              <div class="mb-3">
                <label class="form-label">Email Address</label>
                <input type="email" name="email" class="form-control" value="<?= h($old['email']) ?>" required>
              </div>
              <div class="mb-3">
                <label class="form-label">Contact Number</label>
                <input type="text" name="contact_number" class="form-control" value="<?= h($old['contact_number']) ?>">
              </div>
              <div class="row">
                <div class="col-md-6 mb-3">
                  <label class="form-label">Password</label>
                  <input type="password" name="password" class="form-control" required>
                </div>
                <div class="col-md-6 mb-3">
                  <label class="form-label">Confirm Password</label>
                  <input type="password" name="confirm_password" class="form-control" required>
                </div>
              </div>
              <button type="submit" class="btn-brand w-100 mt-2">Create Account</button>
              <p class="text-center mt-3 mb-0 small">Already have an account? <a href="/tourism-portal/login.php">Login here</a></p>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
