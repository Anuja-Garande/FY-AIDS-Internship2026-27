<?php
session_start();
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

if (!isAdminLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$id = intval($_POST['id'] ?? 0);
if (!$id) {
    echo json_encode(['success' => false, 'message' => 'Invalid product ID']);
    exit;
}

$db = Database::getInstance();
$product = $db->query("SELECT * FROM products WHERE id=?", [$id])->fetch();

if (!$product) {
    echo json_encode(['success' => false, 'message' => 'Product not found']);
    exit;
}

$uploadDir = __DIR__ . '/../assets/uploads/products/';
$images = $db->query("SELECT image FROM product_images WHERE product_id=?", [$id])->fetchAll();
foreach ($images as $img) {
    $path = $uploadDir . $img['image'];
    if (file_exists($path)) unlink($path);
}

$db->query("DELETE FROM product_images WHERE product_id=?", [$id]);
$db->query("DELETE FROM product_specifications WHERE product_id=?", [$id]);
$db->query("DELETE FROM cart WHERE product_id=?", [$id]);
$db->query("DELETE FROM wishlist_items WHERE product_id=?", [$id]);
$db->query("DELETE FROM order_items WHERE product_id=?", [$id]);
$db->query("DELETE FROM reviews WHERE product_id=?", [$id]);
$db->query("DELETE FROM products WHERE id=?", [$id]);

echo json_encode(['success' => true, 'message' => 'Product deleted successfully']);
