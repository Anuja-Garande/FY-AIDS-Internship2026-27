<?php
require_once __DIR__ . '/includes/functions.php';
session_unset();
session_destroy();
session_start();
setFlash('success', 'You have been logged out successfully.');
redirect('/tourism-portal/index.php');
