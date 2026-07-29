<?php
session_start();

// Remove all session variables
session_unset();

// Destroy the session
session_destroy();

// Prevent browser from caching pages
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

// Redirect to login page
header("Location: login.php");
exit();
?>