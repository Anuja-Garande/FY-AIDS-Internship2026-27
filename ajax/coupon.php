<?php
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

$action = $_POST['action'] ?? '';
$db = Database::getInstance();

switch ($action) {

    case 'apply':
        $code = trim($_POST['code'] ?? '');
        $subtotal = (float)($_POST['subtotal'] ?? 0);

        if (empty($code)) {
            echo json_encode(['success' => false, 'message' => 'Please enter a coupon code.']);
            exit;
        }

        $coupon = $db->fetch(
            "SELECT * FROM coupons WHERE code = ? AND status = 1",
            [strtoupper($code)]
        );

        if (!$coupon) {
            echo json_encode(['success' => false, 'valid' => false, 'message' => 'Invalid coupon code.']);
            exit;
        }

        if (!empty($coupon['expiry_date']) && strtotime($coupon['expiry_date']) < time()) {
            echo json_encode(['success' => false, 'valid' => false, 'message' => 'This coupon has expired.']);
            exit;
        }

        if ($coupon['usage_limit'] > 0 && $coupon['used_count'] >= $coupon['usage_limit']) {
            echo json_encode(['success' => false, 'valid' => false, 'message' => 'This coupon has reached its usage limit.']);
            exit;
        }

        if ($coupon['min_order'] > 0 && $subtotal < $coupon['min_order']) {
            echo json_encode([
                'success' => false,
                'valid' => false,
                'message' => 'Minimum order of ' . formatPrice($coupon['min_order']) . ' required.'
            ]);
            exit;
        }

        if (isLoggedIn()) {
            $used = $db->fetch(
                "SELECT id FROM coupon_usage WHERE user_id = ? AND coupon_id = ?",
                [$_SESSION['user_id'], $coupon['id']]
            );
            if ($used) {
                echo json_encode(['success' => false, 'valid' => false, 'message' => 'You have already used this coupon.']);
                exit;
            }
        }

        if ($coupon['type'] === 'percentage') {
            $discount = round($subtotal * $coupon['value'] / 100);
        } else {
            $discount = (float)$coupon['value'];
        }

        if (!empty($coupon['max_discount']) && $discount > $coupon['max_discount']) {
            $discount = (float)$coupon['max_discount'];
        }

        $discount = min($discount, $subtotal);
        $new_total = $subtotal - $discount;

        $_SESSION['coupon'] = [
            'code'     => $coupon['code'],
            'discount' => $discount,
            'coupon_id' => $coupon['id']
        ];

        echo json_encode([
            'success'         => true,
            'valid'           => true,
            'message'         => 'Coupon applied successfully!',
            'discount_amount' => $discount,
            'discount_formatted' => formatPrice($discount),
            'new_total'       => $new_total,
            'new_total_formatted' => formatPrice($new_total),
            'coupon_code'     => $coupon['code'],
            'coupon_type'     => $coupon['type'],
            'coupon_value'    => (float)$coupon['value']
        ]);
        break;

    case 'remove':
        unset($_SESSION['coupon']);
        echo json_encode([
            'success' => true,
            'message' => 'Coupon removed successfully.'
        ]);
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action.']);
        break;
}
