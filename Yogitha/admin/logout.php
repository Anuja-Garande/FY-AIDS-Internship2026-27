<?php
// C:\xampp\htdocs\NewProject\admin\logout.php
// Log out admin and destroy admin-specific sessions

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

unset($_SESSION['admin_logged_in']);
unset($_SESSION['admin_username']);

session_start();
$_SESSION['admin_success'] = "Logged out of admin panel successfully.";
header("Location: login.php");
exit;
?>
