<?php
session_start();
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

if (!isAdminLoggedIn()) {
    redirect('login.php');
}

$_SESSION = [];
session_destroy();
unset($_COOKIE['admin_id'], $_COOKIE['admin_token']);
setcookie('admin_id', '', time() - 3600, '/');
setcookie('admin_token', '', time() - 3600, '/');

redirect('../');
exit;
