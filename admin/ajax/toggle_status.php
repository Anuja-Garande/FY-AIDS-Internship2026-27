<?php
session_start();
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';

header('Content-Type: application/json');

if (!isAdminLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$table = $_POST['table'] ?? '';
$id = intval($_POST['id'] ?? 0);
$field = $_POST['field'] ?? '';

$allowedTables = ['products', 'categories', 'brands', 'coupons', 'banners', 'users', 'reviews'];
$allowedFields = ['status', 'is_featured', 'is_approved'];

if (!in_array($table, $allowedTables) || !in_array($field, $allowedFields) || $id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
    exit;
}

$db = Database::getInstance();

$current = $db->query("SELECT `$field` FROM `$table` WHERE id=?", [$id])->fetch();
if (!$current) {
    echo json_encode(['success' => false, 'message' => 'Record not found']);
    exit;
}

$newValue = $current[$field] ? 0 : 1;
$db->update("UPDATE `$table` SET `$field`=? WHERE id=?", [$newValue, $id]);

echo json_encode([
    'success' => true,
    'message' => ucfirst(str_replace('_', ' ', $field)) . ' ' . ($newValue ? 'enabled' : 'disabled') . ' successfully',
    'new_value' => $newValue
]);
