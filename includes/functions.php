<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

function redirect($url) {
    echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><meta http-equiv="refresh" content="0;url=' . htmlspecialchars($url) . '"><title>Redirecting...</title></head><body><script>window.location.href=' . json_encode($url) . ';</script></body></html>';
    exit;
}

function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function isAdminLoggedIn() {
    return isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id']);
}

function getUser() {
    if (!isLoggedIn()) return false;
    $db = Database::getInstance();
    return $db->fetch("SELECT * FROM users WHERE id = ?", [$_SESSION['user_id']]);
}

function getAdmin() {
    if (!isAdminLoggedIn()) return false;
    $db = Database::getInstance();
    return $db->fetch("SELECT * FROM admins WHERE id = ?", [$_SESSION['admin_id']]);
}

function sanitize($input) {
    if (is_array($input)) {
        return array_map('sanitize', $input);
    }
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

function generateToken() {
    return bin2hex(random_bytes(32));
}

function verifyToken($token) {
    if (empty($_SESSION['csrf_token']) || empty($token)) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

function uploadImage($file, $directory) {
    if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => 'Upload error occurred.'];
    }

    if ($file['size'] > MAX_FILE_SIZE) {
        return ['success' => false, 'message' => 'File size must be less than 5MB.'];
    }

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mime, ALLOWED_IMAGE_TYPES)) {
        return ['success' => false, 'message' => 'Invalid file type. Allowed: JPG, PNG, WEBP, GIF.'];
    }

    $ext = match ($mime) {
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
        'image/gif'  => 'gif',
        default      => 'jpg'
    };

    $filename = uniqid('img_', true) . '.' . $ext;
    $upload_dir = UPLOAD_PATH . $directory . '/';

    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }

    $destination = $upload_dir . $filename;

    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        return ['success' => false, 'message' => 'Failed to move uploaded file.'];
    }

    return [
        'success'  => true,
        'filename' => $filename,
        'path'     => 'assets/uploads/' . $directory . '/' . $filename
    ];
}

function deleteImage($path) {
    if (empty($path)) return false;
    $full_path = $_SERVER['DOCUMENT_ROOT'] . '/AI_ Shopping/' . $path;
    if (file_exists($full_path)) {
        return unlink($full_path);
    }
    return false;
}

function formatPrice($amount) {
    return '₹' . number_format((float)$amount, 2);
}

function timeAgo($datetime) {
    $now = new DateTime();
    $past = new DateTime($datetime);
    $diff = $now->diff($past);

    if ($diff->y > 0) return $diff->y . ' year' . ($diff->y > 1 ? 's' : '') . ' ago';
    if ($diff->m > 0) return $diff->m . ' month' . ($diff->m > 1 ? 's' : '') . ' ago';
    if ($diff->d > 0) return $diff->d . ' day' . ($diff->d > 1 ? 's' : '') . ' ago';
    if ($diff->h > 0) return $diff->h . ' hour' . ($diff->h > 1 ? 's' : '') . ' ago';
    if ($diff->i > 0) return $diff->i . ' minute' . ($diff->i > 1 ? 's' : '') . ' ago';
    return 'Just now';
}

function generateOrderNumber() {
    return 'ORD-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4));
}

function generateSKU($name) {
    $clean = preg_replace('/[^A-Za-z0-9]/', '', strtoupper($name));
    return substr($clean, 0, 6) . '-' . strtoupper(substr(uniqid(), -4));
}

function slugify($text) {
    $text = strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9-]/', '-', $text);
    $text = preg_replace('/-+/', '-', $text);
    return trim($text, '-');
}

function paginate($total, $per_page, $current_page) {
    $total_pages = max(1, ceil($total / $per_page));
    $current_page = max(1, min($current_page, $total_pages));
    $offset = ($current_page - 1) * $per_page;

    return [
        'total'        => $total,
        'per_page'     => $per_page,
        'current_page' => $current_page,
        'total_pages'  => $total_pages,
        'offset'       => $offset,
        'has_prev'     => $current_page > 1,
        'has_next'     => $current_page < $total_pages,
        'prev_page'    => max(1, $current_page - 1),
        'next_page'    => min($total_pages, $current_page + 1),
    ];
}

function getSetting($key) {
    $db = Database::getInstance();
    $row = $db->fetch("SELECT setting_value FROM settings WHERE setting_key = ?", [$key]);
    return $row ? $row['setting_value'] : null;
}

function setNotification($user_id, $title, $message, $type = 'info') {
    $db = Database::getInstance();
    $db->insert(
        "INSERT INTO notifications (user_id, title, message, type, created_at) VALUES (?, ?, ?, ?, NOW())",
        [$user_id, $title, $message, $type]
    );
}

function getProductImages($product_id) {
    $db = Database::getInstance();
    return $db->fetchAll(
        "SELECT * FROM product_images WHERE product_id = ? ORDER BY is_primary DESC, sort_order ASC",
        [$product_id]
    );
}

function getCartCount() {
    if (isLoggedIn()) {
        $db = Database::getInstance();
        $row = $db->fetch("SELECT COALESCE(SUM(quantity), 0) AS total FROM cart WHERE user_id = ?", [$_SESSION['user_id']]);
        return $row ? (int)$row['total'] : 0;
    } elseif (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
        $count = 0;
        foreach ($_SESSION['cart'] as $item) {
            $count += (int)$item;
        }
        return $count;
    }
    return 0;
}

function getCartTotal() {
    if (isLoggedIn()) {
        $db = Database::getInstance();
        $row = $db->fetch(
            "SELECT COALESCE(SUM(COALESCE(p.discount_price, p.price) * c.quantity), 0) AS total FROM cart c JOIN products p ON c.product_id = p.id WHERE c.user_id = ?",
            [$_SESSION['user_id']]
        );
        return $row ? (float)$row['total'] : 0;
    } elseif (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
        $total = 0;
        $db = Database::getInstance();
        foreach ($_SESSION['cart'] as $pid => $qty) {
            $product = $db->fetch("SELECT COALESCE(discount_price, price) AS sale_price FROM products WHERE id = ?", [$pid]);
            if ($product) {
                $total += $product['sale_price'] * (int)$qty;
            }
        }
        return $total;
    }
    return 0;
}

function flash($key, $message) {
    $_SESSION['flash'][$key] = $message;
}

function getFlash($key) {
    if (isset($_SESSION['flash'][$key])) {
        $msg = $_SESSION['flash'][$key];
        unset($_SESSION['flash'][$key]);
        return $msg;
    }
    return null;
}
