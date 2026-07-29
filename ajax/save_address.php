<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
    exit;
}

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Please login first.']);
    exit;
}

if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
    echo json_encode(['success' => false, 'message' => 'Invalid security token.']);
    exit;
}

$db = Database::getInstance();
$user = getUser();

$name = trim($_POST['name'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$address_line1 = trim($_POST['address_line1'] ?? '');
$address_line2 = trim($_POST['address_line2'] ?? '');
$city = trim($_POST['city'] ?? '');
$state = trim($_POST['state'] ?? '');
$pincode = trim($_POST['pincode'] ?? '');
$is_default = isset($_POST['is_default']) ? 1 : 0;

if (empty($name) || empty($phone) || empty($address_line1) || empty($city) || empty($state) || empty($pincode)) {
    echo json_encode(['success' => false, 'message' => 'Please fill all required fields.']);
    exit;
}

$count = $db->fetch("SELECT COUNT(*) as cnt FROM addresses WHERE user_id = ?", [$user['id']]);
if ($count && $count['cnt'] >= 5) {
    echo json_encode(['success' => false, 'message' => 'Maximum 5 addresses allowed.']);
    exit;
}

if ($is_default) {
    $db->update("UPDATE addresses SET is_default = 0 WHERE user_id = ?", [$user['id']]);
}

$addr_count = $db->fetch("SELECT COUNT(*) as cnt FROM addresses WHERE user_id = ?", [$user['id']]);
if (empty($addr_count['cnt']) && !$is_default) {
    $is_default = 1;
}

$id = $db->insert(
    "INSERT INTO addresses (user_id, name, phone, address_line1, address_line2, city, state, pincode, is_default, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())",
    [$user['id'], $name, $phone, $address_line1, $address_line2, $city, $state, $pincode, $is_default]
);

if ($id) {
    echo json_encode(['success' => true, 'message' => 'Address saved successfully!']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to save address.']);
}
