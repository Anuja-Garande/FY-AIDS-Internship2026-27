<?php
require_once __DIR__ . '/../includes/functions.php';
unset($_SESSION['admin_id'], $_SESSION['admin_username']);
redirect('/tourism-portal/admin/login.php');
