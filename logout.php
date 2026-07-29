<?php
// C:\xampp\htdocs\NewProject\logout.php
// Log out user and destroy user-specific session data

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Unset user session variables
unset($_SESSION['user_id']);
unset($_SESSION['user_name']);
unset($_SESSION['user_email']);
unset($_SESSION['user_role']);

// Set message and redirect
session_start();
$_SESSION['success'] = "You have logged out successfully.";
header("Location: index.php");
exit;
?>
