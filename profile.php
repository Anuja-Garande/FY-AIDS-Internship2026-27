<?php
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

if (!isLoggedIn()) { redirect('login.php'); }

$page_title = 'My Profile';
$db = Database::getInstance();
$user = getUser();

if (!$user) {
    flash('error', 'Please log in with a valid user account.');
    redirect('login.php');
}

$success = '';
$error = '';

$order_stats = $db->fetch("SELECT COUNT(*) as total_orders, COALESCE(SUM(total_amount),0) as total_spent FROM orders WHERE user_id = :uid", [':uid' => $user['id']]);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid security token.';
    } else {
        if (isset($_POST['update_profile'])) {
            $name = trim($_POST['name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $phone = trim($_POST['phone'] ?? '');

            $errors = [];
            if (empty($name)) $errors[] = 'Name is required.';
            if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email is required.';
            if (!empty($phone) && !preg_match('/^[6-9]\d{9}$/', $phone)) $errors[] = 'Invalid phone number.';

            if ($email !== $user['email']) {
                $exists = $db->fetch("SELECT id FROM users WHERE email = :email AND id != :id", [':email' => $email, ':id' => $user['id']]);
                if ($exists) $errors[] = 'Email already in use.';
            }

            if (!empty($errors)) {
                $error = implode(' ', $errors);
            } else {
                $avatar_path = $user['avatar'] ?? '';
                if (!empty($_FILES['avatar']['name'])) {
                    $upload = uploadImage($_FILES['avatar'], USERS_UPLOAD, 'user_' . $user['id']);
                    if ($upload['success']) {
                        $avatar_path = $upload['filename'];
                    } else {
                        $error = $upload['message'];
                    }
                }

                if (empty($error)) {
                    $db->update("UPDATE users SET name = :name, email = :email, phone = :phone, avatar = :avatar WHERE id = :id", [
                        ':name' => $name,
                        ':email' => $email,
                        ':phone' => $phone,
                        ':avatar' => $avatar_path,
                        ':id' => $user['id'],
                    ]);
                    $success = 'Profile updated successfully!';
                    $_SESSION['user'] = null;
                    $user = getUser();
                }
            }
        }

        if (isset($_POST['change_password'])) {
            $current = $_POST['current_password'] ?? '';
            $new_pass = $_POST['new_password'] ?? '';
            $confirm = $_POST['confirm_password'] ?? '';

            if (empty($current) || empty($new_pass)) {
                $error = 'Please fill all password fields.';
            } elseif (strlen($new_pass) < 6) {
                $error = 'New password must be at least 6 characters.';
            } elseif ($new_pass !== $confirm) {
                $error = 'New passwords do not match.';
            } else {
                $auth = new Auth();
                $result = $auth->changePassword($user['id'], $current, $new_pass);
                if ($result['success']) {
                    $success = 'Password changed successfully!';
                } else {
                    $error = $result['message'] ?? 'Failed to change password.';
                }
            }
        }
    }
}
?>
<?php include 'includes/header.php'; ?>

<style>
.profile-header { background: linear-gradient(135deg, #667eea, #764ba2); border-radius: 16px; padding: 40px; color: white; position: relative; overflow: hidden; }
.profile-header::before { content: ''; position: absolute; top: -50%; right: -20%; width: 400px; height: 400px; background: rgba(255,255,255,0.1); border-radius: 50%; }
.profile-avatar { width: 100px; height: 100px; border-radius: 50%; border: 4px solid white; object-fit: cover; }
.stat-card { background: white; border-radius: 12px; padding: 20px; text-align: center; box-shadow: 0 2px 10px rgba(0,0,0,0.06); }
.stat-card .stat-value { font-size: 1.5rem; font-weight: 700; color: #667eea; }
</style>

<div class="container py-4">
    <?php if (!empty($success)): ?>
        <div class="alert alert-success alert-dismissible fade show"><i class="fas fa-check-circle me-2"></i><?= $success ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php endif; ?>
    <?php if (!empty($error)): ?>
        <div class="alert alert-danger alert-dismissible fade show"><i class="fas fa-exclamation-circle me-2"></i><?= $error ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php endif; ?>

    <div class="profile-header mb-4">
        <div class="d-flex align-items-center position-relative" style="z-index:1;">
            <?php if (!empty($user['avatar'])): ?>
                <img src="<?= BASE_URL ?>assets/uploads/users/<?= htmlspecialchars($user['avatar']) ?>" class="profile-avatar me-4" alt="">
            <?php else: ?>
                <div class="profile-avatar me-4 bg-white text-primary d-flex align-items-center justify-content-center" style="font-size:2.5rem;font-weight:700;"><?= strtoupper(substr($user['name'], 0, 1)) ?></div>
            <?php endif; ?>
            <div>
                <h3 class="mb-1 fw-bold"><?= htmlspecialchars($user['name']) ?></h3>
                <p class="mb-0 opacity-75"><?= htmlspecialchars($user['email']) ?></p>
                <small class="opacity-50">Member since <?= date('M Y', strtotime($user['created_at'] ?? 'now')) ?></small>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="stat-card"><div class="stat-value"><?= $order_stats['total_orders'] ?? 0 ?></div><small class="text-muted">Total Orders</small></div>
        </div>
        <div class="col-md-4">
            <div class="stat-card"><div class="stat-value">₹<?= number_format($order_stats['total_spent'] ?? 0, 0) ?></div><small class="text-muted">Total Spent</small></div>
        </div>
        <div class="col-md-4">
            <div class="stat-card">
                <div class="stat-value">
                    <?php
                    $points = $db->fetch("SELECT COALESCE(SUM(total_amount),0) as total FROM orders WHERE user_id = :uid AND order_status = 'delivered'", [':uid' => $user['id']]);
                    echo (int)($points['total'] ?? 0);
                    ?>
                </div>
                <small class="text-muted">Reward Points</small>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-3"><i class="fas fa-user-edit me-2"></i>Edit Profile</h5>
                    <form method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Full Name</label>
                                <input type="text" class="form-control" name="name" value="<?= htmlspecialchars($user['name']) ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Phone</label>
                                <input type="tel" class="form-control" name="phone" value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Profile Picture</label>
                                <input type="file" class="form-control" name="avatar" accept="image/*">
                            </div>
                        </div>
                        <button type="submit" name="update_profile" class="btn btn-primary mt-3"><i class="fas fa-save me-2"></i>Save Changes</button>
                    </form>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-3"><i class="fas fa-lock me-2"></i>Change Password</h5>
                    <form method="POST">
                        <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label">Current Password</label>
                                <input type="password" class="form-control" name="current_password" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">New Password</label>
                                <input type="password" class="form-control" name="new_password" required minlength="6">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Confirm New Password</label>
                                <input type="password" class="form-control" name="confirm_password" required>
                            </div>
                        </div>
                        <button type="submit" name="change_password" class="btn btn-warning mt-3"><i class="fas fa-key me-2"></i>Change Password</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-3"><i class="fas fa-link me-2"></i>Quick Links</h5>
                    <a href="orders.php" class="d-flex align-items-center p-2 rounded text-decoration-none text-dark mb-2 hover-bg"><i class="fas fa-box me-3 text-primary"></i>My Orders</a>
                    <a href="wishlist.php" class="d-flex align-items-center p-2 rounded text-decoration-none text-dark mb-2 hover-bg"><i class="fas fa-heart me-3 text-danger"></i>Wishlist</a>
                    <a href="addresses.php" class="d-flex align-items-center p-2 rounded text-decoration-none text-dark mb-2 hover-bg"><i class="fas fa-map-marker-alt me-3 text-success"></i>My Addresses</a>
                    <a href="notifications.php" class="d-flex align-items-center p-2 rounded text-decoration-none text-dark mb-2 hover-bg"><i class="fas fa-bell me-3 text-warning"></i>Notifications</a>
                    <a href="compare.php" class="d-flex align-items-center p-2 rounded text-decoration-none text-dark mb-2 hover-bg"><i class="fas fa-exchange-alt me-3 text-info"></i>Compare</a>
                </div>
            </div>
        </div>
    </div>
</div>

<style>.hover-bg:hover { background: #f0f4ff; }</style>

<?php include 'includes/footer.php'; ?>
