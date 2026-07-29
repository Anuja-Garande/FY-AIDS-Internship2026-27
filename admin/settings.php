<?php
session_start();

require_once __DIR__ . '/../includes/db_connect.php';

if (empty($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit;
}

if (!isset($conn) || !($conn instanceof mysqli)) {
    exit('Database connection failed. Please check includes/db_connect.php');
}

function e($value)
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function setFlash($type, $message)
{
    $_SESSION['settings_flash'] = [
        'type' => $type,
        'message' => $message
    ];
}

function getFlash()
{
    $flash = $_SESSION['settings_flash'] ?? null;
    unset($_SESSION['settings_flash']);
    return $flash;
}

function redirectToSettings()
{
    header('Location: settings.php');
    exit;
}

$adminId = (int)$_SESSION['admin_id'];

/* Get currently logged in admin */
$stmt = $conn->prepare(
    "SELECT admin_id, name, email, password, created_at
     FROM admin
     WHERE admin_id = ?"
);

$stmt->bind_param("i", $adminId);
$stmt->execute();
$admin = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$admin) {
    session_destroy();
    header('Location: ../login.php');
    exit;
}

/* Save profile/password */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if ($name === '' || $email === '') {
        setFlash('error', 'Name and email are required.');
        redirectToSettings();
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        setFlash('error', 'Please enter a valid email address.');
        redirectToSettings();
    }

    /* Check whether another admin is already using this email */
    $emailCheck = $conn->prepare(
        "SELECT admin_id FROM admin WHERE email = ? AND admin_id != ?"
    );
    $emailCheck->bind_param("si", $email, $adminId);
    $emailCheck->execute();

    if ($emailCheck->get_result()->num_rows > 0) {
        $emailCheck->close();
        setFlash('error', 'This email address is already in use.');
        redirectToSettings();
    }

    $emailCheck->close();

    /* Profile update only */
    if ($newPassword === '') {
        $update = $conn->prepare(
            "UPDATE admin SET name = ?, email = ? WHERE admin_id = ?"
        );
        $update->bind_param("ssi", $name, $email, $adminId);

        if ($update->execute()) {
            $_SESSION['admin_name'] = $name;
            $_SESSION['name'] = $name;

            setFlash('success', 'Profile updated successfully.');
        } else {
            setFlash('error', 'Unable to update profile.');
        }

        $update->close();
        redirectToSettings();
    }

    /* Password change validation */
    if (strlen($newPassword) < 6) {
        setFlash('error', 'New password must be at least 6 characters long.');
        redirectToSettings();
    }

    if ($newPassword !== $confirmPassword) {
        setFlash('error', 'New password and confirm password do not match.');
        redirectToSettings();
    }

    if ($currentPassword === '') {
        setFlash('error', 'Enter your current password to change it.');
        redirectToSettings();
    }

    /*
     * password_verify supports hashed passwords.
     * hash_equals also allows compatibility if existing database passwords are plain text.
     */
    $currentPasswordCorrect =
        password_verify($currentPassword, $admin['password']) ||
        hash_equals((string)$admin['password'], $currentPassword);

    if (!$currentPasswordCorrect) {
        setFlash('error', 'Current password is incorrect.');
        redirectToSettings();
    }

    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

    $update = $conn->prepare(
        "UPDATE admin
         SET name = ?, email = ?, password = ?
         WHERE admin_id = ?"
    );

    $update->bind_param(
        "sssi",
        $name,
        $email,
        $hashedPassword,
        $adminId
    );

    if ($update->execute()) {
        $_SESSION['admin_name'] = $name;
        $_SESSION['name'] = $name;

        setFlash('success', 'Profile and password updated successfully.');
    } else {
        setFlash('error', 'Unable to update account settings.');
    }

    $update->close();
    redirectToSettings();
}

$flash = getFlash();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings | Digital Library</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f4f7fb;
            color: #1e293b;
        }

        .topbar {
            background: #12355b;
            color: white;
            padding: 15px 5%;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .topbar a {
            color: white;
            text-decoration: none;
        }

        .brand {
            font-size: 20px;
            font-weight: bold;
        }

        .layout {
            display: flex;
            min-height: calc(100vh - 56px);
        }

        .sidebar {
            width: 245px;
            background: white;
            padding: 20px 12px;
            box-shadow: 1px 0 8px rgba(0, 0, 0, 0.08);
        }

        .sidebar a {
            display: block;
            padding: 12px 14px;
            margin: 4px 0;
            border-radius: 7px;
            color: #475569;
            text-decoration: none;
        }

        .sidebar a:hover,
        .sidebar a.active {
            background: #e5f0fb;
            color: #0f4c81;
            font-weight: bold;
        }

        .sidebar i {
            width: 22px;
        }

        .main-content {
            flex: 1;
            padding: 30px;
            max-width: 1200px;
        }

        .card {
            background: white;
            border-radius: 12px;
            padding: 24px;
            margin-top: 20px;
            box-shadow: 0 2px 12px rgba(30, 41, 59, 0.1);
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 16px;
        }

        .full-width {
            grid-column: 1 / -1;
        }

        label {
            display: block;
            margin-bottom: 6px;
            font-weight: bold;
        }

        input {
            width: 100%;
            padding: 11px;
            border: 1px solid #cbd5e1;
            border-radius: 7px;
            font-size: 14px;
        }

        input:focus {
            outline: none;
            border-color: #1464a0;
            box-shadow: 0 0 0 3px rgba(20, 100, 160, 0.12);
        }

        .btn {
            display: inline-block;
            border: none;
            border-radius: 7px;
            padding: 11px 15px;
            cursor: pointer;
            color: white;
            background: #1464a0;
            font-size: 14px;
        }

        .message {
            padding: 13px 15px;
            border-radius: 7px;
            margin: 18px 0;
        }

        .success {
            background: #dcfce7;
            color: #166534;
        }

        .error {
            background: #fee2e2;
            color: #991b1b;
        }

        .help-text {
            font-size: 13px;
            color: #64748b;
            margin-top: 6px;
        }

        .account-info {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
        }

        .info-box {
            border-radius: 8px;
            background: #f8fafc;
            padding: 15px;
        }

        .info-box span {
            display: block;
            color: #64748b;
            font-size: 13px;
            margin-bottom: 6px;
        }

        .password-heading {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e2e8f0;
        }

        @media (max-width: 900px) {
            .sidebar {
                display: none;
            }

            .main-content {
                padding: 18px;
            }

            .account-info {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 600px) {
            .topbar {
                padding: 14px;
            }

            .brand {
                font-size: 16px;
            }

            .form-grid {
                grid-template-columns: 1fr;
            }

            .full-width {
                grid-column: auto;
            }
        }
    </style>
</head>

<body>

<header class="topbar">
    <a class="brand" href="dashboard.php">
        <i class="fa-solid fa-book-open"></i> Digital Library
    </a>

    <a href="../login.php">
        <i class="fa-solid fa-right-from-bracket"></i> Logout
    </a>
</header>

<div class="layout">

    <aside class="sidebar">
        <a href="dashboard.php"><i class="fa-solid fa-gauge"></i> Dashboard</a>
        <a class="active" href="settings.php"><i class="fa-solid fa-gear"></i> Settings</a>
    </aside>

    <main class="main-content">

        <h1>Account Settings</h1>
        <p>Update your admin profile and password.</p>

        <?php if ($flash): ?>
            <div class="message <?= e($flash['type']) ?>">
                <?= e($flash['message']) ?>
            </div>
        <?php endif; ?>

        <section class="card">
            <h2>Admin Account</h2>

            <div class="account-info">
                <div class="info-box">
                    <span>Admin ID</span>
                    <strong>#<?= (int)$admin['admin_id'] ?></strong>
                </div>

                <div class="info-box">
                    <span>Registered Email</span>
                    <strong><?= e($admin['email']) ?></strong>
                </div>

                <div class="info-box">
                    <span>Account Created</span>
                    <strong><?= e($admin['created_at'] ?? '-') ?></strong>
                </div>
            </div>
        </section>

        <section class="card">
            <h2>Update Profile</h2>

            <form method="post" id="settingsForm">
                <div class="form-grid">
                    <div>
                        <label>Full Name *</label>
                        <input
                            type="text"
                            name="name"
                            required
                            maxlength="150"
                            value="<?= e($admin['name']) ?>"
                        >
                    </div>

                    <div>
                        <label>Email Address *</label>
                        <input
                            type="email"
                            name="email"
                            required
                            maxlength="150"
                            value="<?= e($admin['email']) ?>"
                        >
                    </div>
                </div>

                <h2 class="password-heading">Change Password</h2>
                <p class="help-text">
                    Leave all password fields empty if you only want to update your profile.
                </p>

                <div class="form-grid">
                    <div class="full-width">
                        <label>Current Password</label>
                        <input
                            type="password"
                            name="current_password"
                            autocomplete="current-password"
                        >
                    </div>

                    <div>
                        <label>New Password</label>
                        <input
                            type="password"
                            name="new_password"
                            minlength="6"
                            autocomplete="new-password"
                        >
                        <div class="help-text">Minimum 6 characters.</div>
                    </div>

                    <div>
                        <label>Confirm New Password</label>
                        <input
                            type="password"
                            name="confirm_password"
                            minlength="6"
                            autocomplete="new-password"
                        >
                    </div>
                </div>

                <p style="margin-top: 22px;">
                    <button type="submit" class="btn">
                        <i class="fa-solid fa-floppy-disk"></i> Save Settings
                    </button>
                </p>
            </form>
        </section>

    </main>
</div>

<script>
document.getElementById('settingsForm').addEventListener('submit', function (event) {
    const currentPassword = this.current_password.value;
    const newPassword = this.new_password.value;
    const confirmPassword = this.confirm_password.value;

    if (newPassword !== '' || confirmPassword !== '' || currentPassword !== '') {
        if (currentPassword === '') {
            event.preventDefault();
            alert('Enter your current password to change the password.');
            return;
        }

        if (newPassword.length < 6) {
            event.preventDefault();
            alert('New password must contain at least 6 characters.');
            return;
        }

        if (newPassword !== confirmPassword) {
            event.preventDefault();
            alert('New password and confirm password do not match.');
        }
    }
});
</script>

</body>
</html>