<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

date_default_timezone_set('Asia/Kolkata');

define('DB_HOST', 'localhost');
define('DB_NAME', 'ai_shopping');
define('DB_USER', 'root');
define('DB_PASS', '');

define('BASE_URL', 'http://localhost/AI_%20Shopping/');
define('SITE_NAME', 'ShopSphere');
define('ADMIN_EMAIL', 'admin@aishopping.com');

define('UPLOAD_PATH', $_SERVER['DOCUMENT_ROOT'] . '/AI_ Shopping/assets/uploads/');
define('PRODUCTS_UPLOAD', UPLOAD_PATH . 'products/');
define('CATEGORIES_UPLOAD', UPLOAD_PATH . 'categories/');
define('BRANDS_UPLOAD', UPLOAD_PATH . 'brands/');
define('USERS_UPLOAD', UPLOAD_PATH . 'users/');
define('BANNERS_UPLOAD', UPLOAD_PATH . 'banners/');

define('PRODUCTS_PER_PAGE', 12);
define('TAX_RATE', 0.18);
define('SHIPPING_CHARGES', 99);
define('FREE_SHIPPING_MIN', 999);
define('MAX_FILE_SIZE', 5 * 1024 * 1024);
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/webp', 'image/gif']);

define('RAZORPAY_KEY_ID', 'rzp_test_XXXXXXXXXXXX');
define('RAZORPAY_KEY_SECRET', 'XXXXXXXXXXXXXXXXXXXXXXXX');

function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCSRFToken($token) {
    if (empty($_SESSION['csrf_token']) || empty($token)) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}
