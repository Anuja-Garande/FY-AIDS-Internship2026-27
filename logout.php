<?php
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

$auth = new Auth();
$auth->logout();
session_unset();
session_destroy();
setcookie('remember_token', '', time() - 3600, '/');
header('Location: ' . BASE_URL);
exit;
