<?php
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Please login to place an order.', 'redirect' => 'login.php']);
    exit;
}

$action = $_POST['action'] ?? '';
$db = Database::getInstance();
$user = getUser();

switch ($action) {

    case 'place_order':
        $addr_id = (int)($_POST['address_id'] ?? 0);
        $payment_method = $_POST['payment_method'] ?? 'cod';

        if (!$addr_id) {
            echo json_encode(['success' => false, 'message' => 'Please select a delivery address.']);
            exit;
        }

        $address = $db->fetch("SELECT * FROM addresses WHERE id = ? AND user_id = ?", [$addr_id, $user['id']]);
        if (!$address) {
            echo json_encode(['success' => false, 'message' => 'Invalid address.']);
            exit;
        }

        $cart = $db->fetchAll(
            "SELECT c.id, c.product_id, c.quantity, p.name, p.price, p.discount_price, p.quantity as stock
             FROM cart c JOIN products p ON c.product_id = p.id WHERE c.user_id = ? AND p.status = 1",
            [$user['id']]
        );

        if (empty($cart)) {
            echo json_encode(['success' => false, 'message' => 'Your cart is empty.']);
            exit;
        }

        foreach ($cart as $ci) {
            if ($ci['quantity'] > $ci['stock']) {
                echo json_encode(['success' => false, 'message' => $ci['name'] . ' has insufficient stock.']);
                exit;
            }
        }

        $subtotal = 0;
        foreach ($cart as $ci) {
            $price = $ci['discount_price'] ?? $ci['price'];
            $subtotal += $price * $ci['quantity'];
        }

        $shipping = $subtotal >= FREE_SHIPPING_MIN ? 0 : SHIPPING_CHARGES;
        $tax = 0;
        $coupon_discount = $_SESSION['coupon']['discount'] ?? 0;
        $total = $subtotal + $shipping + $tax - $coupon_discount;

        $db->beginTransaction();
        try {
            $order_id = $db->insert(
                "INSERT INTO orders (user_id, order_number, subtotal, discount_amount, coupon_code, shipping_charge, total_amount, payment_method, payment_status, order_status, shipping_name, shipping_email, shipping_phone, shipping_address, shipping_city, shipping_state, shipping_pincode, notes, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending', 'pending', ?, ?, ?, ?, ?, ?, ?, '', NOW())",
                [
                    $user['id'],
                    generateOrderNumber(),
                    $subtotal,
                    $coupon_discount,
                    $_SESSION['coupon']['code'] ?? null,
                    $shipping,
                    $total,
                    $payment_method,
                    $address['name'],
                    $user['email'],
                    $address['phone'],
                    $address['address_line1'] . ($address['address_line2'] ? ', ' . $address['address_line2'] : ''),
                    $address['city'],
                    $address['state'],
                    $address['pincode']
                ]
            );

            foreach ($cart as $ci) {
                $price = $ci['discount_price'] ?? $ci['price'];
                $db->insert(
                    "INSERT INTO order_items (order_id, product_id, product_name, product_price, quantity, total, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())",
                    [$order_id, $ci['product_id'], $ci['name'], $price, $ci['quantity'], $ci['quantity'] * $price]
                );
            }

            $db->delete("DELETE FROM cart WHERE user_id = ?", [$user['id']]);
            unset($_SESSION['coupon']);

            $db->commit();

            setNotification($user['id'], 'Order Placed', 'Your order has been placed successfully!', 'success');

            echo json_encode([
                'success' => true,
                'message' => 'Order placed successfully!',
                'redirect' => 'order_confirmation.php?order=' . $order_id
            ]);
        } catch (Exception $e) {
            $db->rollback();
            error_log("Order failed: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Failed to place order. Please try again.']);
        }
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action.']);
        break;
}
