<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: authentication/login.php"); exit(); }
require_once("config/db.php");
$user_id = $_SESSION['user_id'];
$msg = ""; $error = "";

// CHANGE PASSWORD
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['change_password'])) {
    $current = $_POST['current_password'];
    $new     = $_POST['new_password'];
    $confirm = $_POST['confirm_password'];

    $stmt = $conn->prepare("SELECT password FROM users WHERE id=?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();

    if (!password_verify($current, $row['password'])) {
        $error = "Current password is incorrect.";
    } elseif ($new !== $confirm) {
        $error = "New passwords do not match.";
    } elseif (strlen($new) < 6) {
        $error = "New password must be at least 6 characters.";
    } else {
        $hashed = password_hash($new, PASSWORD_DEFAULT);
        $u = $conn->prepare("UPDATE users SET password=? WHERE id=?");
        $u->bind_param("si", $hashed, $user_id);
        $u->execute();
        $msg = "Password updated successfully.";
    }
}

// TOGGLE 2FA (stored as email_notification-style flag in settings.two_factor)
if (isset($_GET['toggle_2fa'])) {
    $stmt = $conn->prepare("UPDATE settings SET two_factor = 1 - two_factor WHERE user_id=?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    header("Location: security.php");
    exit();
}

$s = $conn->prepare("SELECT two_factor FROM settings WHERE user_id=?");
$s->bind_param("i", $user_id);
$s->execute();
$settingsRow = $s->get_result()->fetch_assoc();
$twoFactorOn = $settingsRow['two_factor'] ?? 0;

// Recent login activity
$l = $conn->prepare("SELECT * FROM login_logs WHERE user_id=? ORDER BY login_time DESC LIMIT 10");
$l->bind_param("i", $user_id);
$l->execute();
$logs = $l->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Security | NeoFinance</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="css/style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,400..900&family=Manrope:wght@400;500;600;700;800&family=IBM+Plex+Mono:wght@400;500;600&display=swap" rel="stylesheet">
</head>
<body>
<?php include("includes/sidebar.php"); ?>
<?php include("includes/topbar.php"); ?>

<div class="dashboard-content">
    <div class="page-header">
        <div class="page-eyebrow">Ledger · Protection</div>
        <h2 class="page-title"><span class="icon-badge"><i class="fa-solid fa-shield-halved"></i></span> Security</h2>
    </div>

    <?php if ($msg): ?><div class="alert alert-success"><?php echo $msg; ?></div><?php endif; ?>
    <?php if ($error): ?><div class="alert alert-danger"><?php echo $error; ?></div><?php endif; ?>

    <div class="row g-4">
        <div class="col-lg-6">
            <div class="form-card" style="max-width:100%;">
                <h4 class="mb-3">Change Password</h4>
                <form method="POST">
                    <div class="mb-3"><label>Current Password</label><input type="password" name="current_password" class="form-control" required></div>
                    <div class="mb-3"><label>New Password</label><input type="password" name="new_password" class="form-control" required minlength="6"></div>
                    <div class="mb-3"><label>Confirm New Password</label><input type="password" name="confirm_password" class="form-control" required minlength="6"></div>
                    <button type="submit" name="change_password" class="btn btn-info text-white fw-bold w-100">Update Password</button>
                </form>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="dashboard-card h-100">
                <h4>Two-Factor Authentication</h4>
                <p class="text-muted">Add an extra layer of security to your account.</p>
                <div class="d-flex align-items-center gap-3">
                    <span class="badge <?php echo $twoFactorOn ? 'bg-success':'bg-secondary'; ?>">
                        <?php echo $twoFactorOn ? 'Enabled' : 'Disabled'; ?>
                    </span>
                    <a href="?toggle_2fa=1" class="btn btn-sm btn-outline-info">
                        <?php echo $twoFactorOn ? 'Disable' : 'Enable'; ?>
                    </a>
                </div>

                <hr class="my-4">

                <h4 class="text-danger">Danger Zone</h4>
                <p class="text-muted">Deleting your account permanently removes all your financial data.</p>
                <button class="btn btn-outline-danger btn-sm" data-bs-toggle="modal" data-bs-target="#deleteModal">Delete My Account</button>
            </div>
        </div>
    </div>

    <div class="transaction-card mt-4">
        <h3><i class="fa-solid fa-clock-rotate-left"></i> Recent Login Activity</h3>
        <div class="table-responsive mt-3">
            <table class="table table-dark table-hover">
                <thead><tr><th>Date & Time</th><th>IP Address</th><th>Device / Browser</th></tr></thead>
                <tbody>
                <?php if ($logs->num_rows === 0): ?>
                    <tr><td colspan="3" class="text-center">No login activity recorded yet.</td></tr>
                <?php else: while ($log = $logs->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo date("d M Y, H:i", strtotime($log['login_time'])); ?></td>
                        <td><?php echo htmlspecialchars($log['ip_address']); ?></td>
                        <td class="text-truncate" style="max-width:320px;"><?php echo htmlspecialchars($log['user_agent']); ?></td>
                    </tr>
                <?php endwhile; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Delete Account Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="delete_account.php" class="modal-content bg-dark text-light">
            <div class="modal-header"><h5 class="modal-title text-danger">Confirm Account Deletion</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <p>This action cannot be undone. Enter your password to confirm.</p>
                <input type="password" name="confirm_password" class="form-control" placeholder="Password" required>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-danger">Delete Account Permanently</button>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
