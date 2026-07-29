<?php
/**
 * ONE-TIME SETUP SCRIPT
 * ---------------------------------------------------------
 * Run this once in your browser after importing the database:
 *   http://localhost/tourism-portal/reset_admin_password.php
 *
 * It sets a proper bcrypt password hash for the default admin
 * account so you can log in with:
 *   Username: admin
 *   Password: admin123
 *
 * IMPORTANT: Delete this file after running it once, so no one
 * else can reset the admin password.
 */

require_once __DIR__ . '/config/db.php';

$newPassword = 'admin123';
$hash = password_hash($newPassword, PASSWORD_DEFAULT);

$stmt = $pdo->prepare("UPDATE admin SET password = ? WHERE username = 'admin'");
$stmt->execute([$hash]);

echo "<h3 style='font-family:sans-serif'>Admin password has been reset successfully.</h3>";
echo "<p style='font-family:sans-serif'>Username: <b>admin</b><br>Password: <b>admin123</b></p>";
echo "<p style='font-family:sans-serif;color:red'>For security, please delete this file (reset_admin_password.php) now.</p>";
echo "<p><a href='/tourism-portal/admin/login.php'>Go to Admin Login</a></p>";
