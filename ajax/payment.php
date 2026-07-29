<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/functions_product.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
    exit;
}

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Please login to continue.']);
    exit;
}

$action = $_POST['action'] ?? '';
$db = Database::getInstance();
$user = getUser();

switch ($action) {

    case 'create_order':
        $address_id = (int)($_POST['address_id'] ?? 0);
        if (!$address_id) {
            echo json_encode(['success' => false, 'message' => 'Please select a shipping address.']);
            exit;
        }

        $address = $db->fetch("SELECT * FROM addresses WHERE id = ? AND user_id = ?", [$address_id, $user['id']]);
        if (!$address) {
            echo json_encode(['success' => false, 'message' => 'Invalid address.']);
            exit;
        }

        $cart_items = $db->fetchAll(
            "SELECT c.id, c.product_id, c.quantity, p.name, p.price, p.discount_price, p.quantity as stock
             FROM cart c JOIN products p ON c.product_id = p.id WHERE c.user_id = ? AND p.status = 1",
            [$user['id']]
        );

        if (empty($cart_items)) {
            echo json_encode(['success' => false, 'message' => 'Your cart is empty.']);
            exit;
        }

        foreach ($cart_items as $ci) {
            if ($ci['quantity'] > $ci['stock']) {
                echo json_encode(['success' => false, 'message' => $ci['name'] . ' has insufficient stock.']);
                exit;
            }
        }

        $subtotal = 0;
        foreach ($cart_items as $ci) {
            $price = $ci['discount_price'] ?? $ci['price'];
            $subtotal += $price * $ci['quantity'];
        }

        $shipping = $subtotal >= FREE_SHIPPING_MIN ? 0 : SHIPPING_CHARGES;
        $coupon_discount = $_SESSION['coupon']['discount'] ?? 0;
        $total = $subtotal + $shipping - $coupon_discount;

        $amount_in_paise = round($total * 100);

        $receipt = 'order_' . time() . '_' . $user['id'];

        $ch = curl_init('https://api.razorpay.com/v1/orders');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_USERPWD => RAZORPAY_KEY_ID . ':' . RAZORPAY_KEY_SECRET,
            CURLOPT_POSTFIELDS => json_encode([
                'amount' => $amount_in_paise,
                'currency' => 'INR',
                'receipt' => $receipt,
                'notes' => [
                    'user_id' => $user['id'],
                    'user_name' => $user['name'] ?? $user['email']
                ]
            ]),
            CURLOPT_HTTPHEADER => ['Content-Type: application/json']
        ]);

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($http_code !== 200) {
            error_log('Razorpay order creation failed: ' . $response);
            echo json_encode(['success' => false, 'message' => 'Payment gateway error. Please try again.']);
            exit;
        }

        $order = json_decode($response, true);

        echo json_encode([
            'success' => true,
            'razorpay_order_id' => $order['id'],
            'amount' => $amount_in_paise,
            'currency' => 'INR',
            'key' => RAZORPAY_KEY_ID,
            'name' => SITE_NAME,
            'description' => 'Order from ' . SITE_NAME,
            'prefill' => [
                'name' => $user['name'] ?? '',
                'email' => $user['email'] ?? '',
                'contact' => $address['phone'] ?? ''
            ]
        ]);
        break;

    case 'verify_payment':
        $razorpay_order_id = $_POST['razorpay_order_id'] ?? '';
        $razorpay_payment_id = $_POST['razorpay_payment_id'] ?? '';
        $razorpay_signature = $_POST['razorpay_signature'] ?? '';
        $address_id = (int)($_POST['address_id'] ?? 0);

        if (empty($razorpay_order_id) || empty($razorpay_payment_id) || empty($razorpay_signature)) {
            echo json_encode(['success' => false, 'message' => 'Missing payment details.']);
            exit;
        }

        $expected_signature = hash_hmac('sha256', $razorpay_order_id . '|' . $razorpay_payment_id, RAZORPAY_KEY_SECRET);

        if ($expected_signature !== $razorpay_signature) {
            error_log('Razorpay signature mismatch for order: ' . $razorpay_order_id);
            echo json_encode(['success' => false, 'message' => 'Payment verification failed.']);
            exit;
        }

        $address = $db->fetch("SELECT * FROM addresses WHERE id = ? AND user_id = ?", [$address_id, $user['id']]);
        if (!$address) {
            echo json_encode(['success' => false, 'message' => 'Invalid address.']);
            exit;
        }

        $cart_items = $db->fetchAll(
            "SELECT c.id, c.product_id, c.quantity, p.name, p.price, p.discount_price, p.quantity as stock
             FROM cart c JOIN products p ON c.product_id = p.id WHERE c.user_id = ? AND p.status = 1",
            [$user['id']]
        );

        if (empty($cart_items)) {
            echo json_encode(['success' => false, 'message' => 'Your cart is empty.']);
            exit;
        }

        $subtotal = 0;
        foreach ($cart_items as $ci) {
            $price = $ci['discount_price'] ?? $ci['price'];
            $subtotal += $price * $ci['quantity'];
        }

        $shipping = $subtotal >= FREE_SHIPPING_MIN ? 0 : SHIPPING_CHARGES;
        $coupon_discount = $_SESSION['coupon']['discount'] ?? 0;
        $total = $subtotal + $shipping - $coupon_discount;

        $db->beginTransaction();
        try {
            $order_id = $db->insert(
                "INSERT INTO orders (user_id, order_number, subtotal, discount_amount, coupon_code, shipping_charge, total_amount, payment_method, payment_status, order_status, shipping_name, shipping_email, shipping_phone, shipping_address, shipping_city, shipping_state, shipping_pincode, notes, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, 'razorpay', 'paid', 'confirmed', ?, ?, ?, ?, ?, ?, ?, ?, NOW())",
                [
                    $user['id'],
                    generateOrderNumber(),
                    $subtotal,
                    $coupon_discount,
                    $_SESSION['coupon']['code'] ?? null,
                    $shipping,
                    $total,
                    $address['name'],
                    $user['email'],
                    $address['phone'],
                    $address['address_line1'] . ($address['address_line2'] ? ', ' . $address['address_line2'] : ''),
                    $address['city'],
                    $address['state'],
                    $address['pincode'],
                    ''
                ]
            );

            if (!$order_id) throw new Exception('Failed to create order.');

            foreach ($cart_items as $ci) {
                $price = $ci['discount_price'] ?? $ci['price'];
                $db->insert(
                    "INSERT INTO order_items (order_id, product_id, product_name, product_price, quantity, total, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())",
                    [$order_id, $ci['product_id'], $ci['name'], $price, $ci['quantity'], $ci['quantity'] * $price]
                );
            }

            $db->insert(
                "INSERT INTO payments (order_id, user_id, method, transaction_id, amount, status, gateway_response, created_at) VALUES (?, ?, 'razorpay', ?, ?, 'paid', ?, NOW())",
                [$order_id, $user['id'], $razorpay_payment_id, $total, json_encode(['razorpay_order_id' => $razorpay_order_id, 'razorpay_payment_id' => $razorpay_payment_id])]
            );

            $db->delete("DELETE FROM cart WHERE user_id = ?", [$user['id']]);
            unset($_SESSION['coupon']);

            $db->commit();

            setNotification($user['id'], 'Order Placed', "Your order has been placed successfully! Payment confirmed via Razorpay.", 'success');

            echo json_encode([
                'success' => true,
                'message' => 'Payment successful! Order placed.',
                'redirect' => 'order_confirmation.php?order=' . $order_id
            ]);
        } catch (Exception $e) {
            $db->rollback();
            error_log("Order creation after payment failed: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Order creation failed after payment. Contact support with Payment ID: ' . $razorpay_payment_id]);
        }
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action.']);
        break;
}
