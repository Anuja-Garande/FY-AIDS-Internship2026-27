<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

$action = $_POST['action'] ?? '';
$db = Database::getInstance();

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Please login first.', 'require_login' => true]);
    exit;
}

$user_id = $_SESSION['user_id'];
$user = getUser();

switch ($action) {

    case 'update_profile':
        $name = trim($_POST['name'] ?? $user['name']);
        $phone = trim($_POST['phone'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $city = trim($_POST['city'] ?? '');
        $state = trim($_POST['state'] ?? '');
        $pincode = trim($_POST['pincode'] ?? '');

        $errors = [];
        if (empty($name)) $errors[] = 'Name is required.';
        if (!empty($phone) && !preg_match('/^[6-9]\d{9}$/', $phone)) $errors[] = 'Invalid phone number.';

        if (!empty($errors)) {
            echo json_encode(['success' => false, 'message' => implode(' ', $errors)]);
            exit;
        }

        $db->update(
            "UPDATE users SET name = ?, phone = ?, address = ?, city = ?, state = ?, pincode = ? WHERE id = ?",
            [$name, $phone, $address, $city, $state, $pincode, $user_id]
        );

        $_SESSION['user'] = null;

        echo json_encode([
            'success' => true,
            'message' => 'Profile updated successfully.'
        ]);
        break;

    case 'change_password':
        $current_password = $_POST['current_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';

        if (empty($current_password) || empty($new_password)) {
            echo json_encode(['success' => false, 'message' => 'Please fill all password fields.']);
            exit;
        }

        if (strlen($new_password) < 6) {
            echo json_encode(['success' => false, 'message' => 'New password must be at least 6 characters.']);
            exit;
        }

        $auth = new Auth();
        $result = $auth->changePassword($user_id, $current_password, $new_password);

        if ($result['success']) {
            echo json_encode(['success' => true, 'message' => 'Password changed successfully.']);
        } else {
            echo json_encode(['success' => false, 'message' => $result['message'] ?? 'Failed to change password.']);
        }
        break;

    case 'mark_read':
        $notification_id = (int)($_POST['notification_id'] ?? 0);
        if ($notification_id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid notification.']);
            exit;
        }

        $db->update(
            "UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?",
            [$notification_id, $user_id]
        );

        echo json_encode(['success' => true, 'message' => 'Notification marked as read.']);
        break;

    case 'mark_all_read':
        $db->update(
            "UPDATE notifications SET is_read = 1 WHERE user_id = ? AND is_read = 0",
            [$user_id]
        );

        echo json_encode(['success' => true, 'message' => 'All notifications marked as read.']);
        break;

    case 'delete_address':
        $address_id = (int)($_POST['address_id'] ?? 0);
        if ($address_id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid address.']);
            exit;
        }

        $addr = $db->fetch("SELECT id, is_default FROM addresses WHERE id = ? AND user_id = ?", [$address_id, $user_id]);
        if (!$addr) {
            echo json_encode(['success' => false, 'message' => 'Address not found.']);
            exit;
        }

        $db->delete("DELETE FROM addresses WHERE id = ? AND user_id = ?", [$address_id, $user_id]);

        if ($addr['is_default']) {
            $next = $db->fetch("SELECT id FROM addresses WHERE user_id = ? ORDER BY created_at DESC LIMIT 1", [$user_id]);
            if ($next) {
                $db->update("UPDATE addresses SET is_default = 1 WHERE id = ?", [$next['id']]);
            }
        }

        echo json_encode(['success' => true, 'message' => 'Address deleted successfully.']);
        break;

    case 'set_default_address':
        $address_id = (int)($_POST['address_id'] ?? 0);
        if ($address_id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid address.']);
            exit;
        }

        $addr = $db->fetch("SELECT id FROM addresses WHERE id = ? AND user_id = ?", [$address_id, $user_id]);
        if (!$addr) {
            echo json_encode(['success' => false, 'message' => 'Address not found.']);
            exit;
        }

        $db->update("UPDATE addresses SET is_default = 0 WHERE user_id = ?", [$user_id]);
        $db->update("UPDATE addresses SET is_default = 1 WHERE id = ? AND user_id = ?", [$address_id, $user_id]);

        echo json_encode(['success' => true, 'message' => 'Default address updated.']);
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action.']);
        break;
}
