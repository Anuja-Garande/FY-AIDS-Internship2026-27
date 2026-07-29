<?php
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

$action = $_POST['action'] ?? '';
$db = Database::getInstance();

function getWishlistCount() {
    if (!isLoggedIn()) return 0;
    $db = Database::getInstance();
    $row = $db->fetch("SELECT COUNT(*) AS cnt FROM wishlist WHERE user_id = ?", [$_SESSION['user_id']]);
    return $row ? (int)$row['cnt'] : 0;
}

switch ($action) {

    case 'toggle':
        if (!isLoggedIn()) {
            echo json_encode(['success' => false, 'message' => 'Please login to use wishlist.', 'require_login' => true]);
            exit;
        }

        $product_id = (int)($_POST['product_id'] ?? 0);
        if ($product_id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid product.']);
            exit;
        }

        $product = $db->fetch("SELECT id, name FROM products WHERE id = ? AND status = 1", [$product_id]);
        if (!$product) {
            echo json_encode(['success' => false, 'message' => 'Product not found.']);
            exit;
        }

        $user_id = $_SESSION['user_id'];
        $existing = $db->fetch("SELECT id FROM wishlist WHERE user_id = ? AND product_id = ?", [$user_id, $product_id]);

        if ($existing) {
            $db->delete("DELETE FROM wishlist WHERE user_id = ? AND product_id = ?", [$user_id, $product_id]);
            $status = 'removed';
            $message = 'Removed from wishlist.';
        } else {
            $db->insert("INSERT INTO wishlist (user_id, product_id, created_at) VALUES (?, ?, NOW())", [$user_id, $product_id]);
            $status = 'added';
            $message = '"' . $product['name'] . '" added to wishlist.';
        }

        $count = getWishlistCount();

        echo json_encode([
            'success' => true,
            'status' => $status,
            'message' => $message,
            'count' => $count
        ]);
        break;

    case 'get_count':
        $count = isLoggedIn() ? getWishlistCount() : 0;
        echo json_encode(['success' => true, 'count' => $count]);
        break;

    case 'check':
        if (!isLoggedIn()) {
            echo json_encode(['success' => true, 'is_wishlisted' => false]);
            exit;
        }

        $product_id = (int)($_POST['product_id'] ?? 0);
        if ($product_id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid product.']);
            exit;
        }

        $exists = $db->fetch("SELECT id FROM wishlist WHERE user_id = ? AND product_id = ?", [$_SESSION['user_id'], $product_id]);

        echo json_encode([
            'success' => true,
            'is_wishlisted' => (bool)$exists
        ]);
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action.']);
        break;
}
