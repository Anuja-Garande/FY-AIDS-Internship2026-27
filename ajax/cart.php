<?php
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

$action = $_POST['action'] ?? '';
$db = Database::getInstance();

function getCartSessionCount() {
    $cart = $_SESSION['cart'] ?? [];
    return array_sum($cart);
}

function getCartSessionTotal() {
    if (empty($_SESSION['cart'])) return 0;
    $db = Database::getInstance();
    $ids = array_keys($_SESSION['cart']);
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $rows = $db->fetchAll("SELECT id, COALESCE(discount_price, price) AS sale_price FROM products WHERE id IN ($placeholders) AND status = 1", $ids);
    $total = 0;
    foreach ($rows as $row) {
        $qty = $_SESSION['cart'][$row['id']] ?? 0;
        $total += $row['sale_price'] * $qty;
    }
    return $total;
}

function getCartSessionItems() {
    if (empty($_SESSION['cart'])) return [];
    $db = Database::getInstance();
    $ids = array_keys($_SESSION['cart']);
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $rows = $db->fetchAll(
        "SELECT p.*, COALESCE(p.discount_price, p.price) AS sale_price, p.quantity AS stock,
                c.name AS category_name, c.slug AS category_slug
         FROM products p
         LEFT JOIN categories c ON p.category_id = c.id
         WHERE p.id IN ($placeholders) AND p.status = 1",
        $ids
    );
    $items = [];
    foreach ($rows as $row) {
        $row['quantity'] = $_SESSION['cart'][$row['id']] ?? 1;
        $row['subtotal'] = $row['sale_price'] * $row['quantity'];
        $items[] = $row;
    }
    return $items;
}

switch ($action) {

    case 'add':
        $product_id = (int)($_POST['product_id'] ?? 0);
        $quantity = max(1, (int)($_POST['quantity'] ?? 1));

        if ($product_id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid product.']);
            exit;
        }

        $product = $db->fetch("SELECT id, COALESCE(discount_price, price) AS sale_price, price, quantity AS stock, name FROM products WHERE id = ? AND status = 1", [$product_id]);
        if (!$product) {
            echo json_encode(['success' => false, 'message' => 'Product not found.']);
            exit;
        }

        if ($product['stock'] < $quantity) {
            echo json_encode(['success' => false, 'message' => 'Insufficient stock. Only ' . $product['stock'] . ' available.']);
            exit;
        }

        if (isLoggedIn()) {
            $user_id = $_SESSION['user_id'];
            $existing = $db->fetch("SELECT id, quantity FROM cart WHERE user_id = ? AND product_id = ?", [$user_id, $product_id]);
            if ($existing) {
                $new_qty = $existing['quantity'] + $quantity;
                if ($new_qty > $product['stock']) {
                    echo json_encode(['success' => false, 'message' => 'Cannot add more. Only ' . $product['stock'] . ' available.']);
                    exit;
                }
                $db->update("UPDATE cart SET quantity = ? WHERE id = ?", [$new_qty, $existing['id']]);
            } else {
                $db->insert("INSERT INTO cart (user_id, product_id, quantity, created_at) VALUES (?, ?, ?, NOW())", [$user_id, $product_id, $quantity]);
            }
            $cart_count = getCartCount();
            $cart_total = getCartTotal();
        } else {
            if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];
            $current = $_SESSION['cart'][$product_id] ?? 0;
            $new_qty = $current + $quantity;
            if ($new_qty > $product['stock']) {
                echo json_encode(['success' => false, 'message' => 'Cannot add more. Only ' . $product['stock'] . ' available.']);
                exit;
            }
            $_SESSION['cart'][$product_id] = $new_qty;
            $cart_count = getCartSessionCount();
            $cart_total = getCartSessionTotal();
        }

        echo json_encode([
            'success' => true,
            'message' => '"' . htmlspecialchars($product['name']) . '" added to cart.',
            'cart_count' => $cart_count,
            'cart_total' => $cart_total,
            'cart_total_formatted' => formatPrice($cart_total)
        ]);
        break;

    case 'update':
        $product_id = (int)($_POST['product_id'] ?? 0);
        $quantity = max(1, (int)($_POST['quantity'] ?? 1));

        if ($product_id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid product.']);
            exit;
        }

        $product = $db->fetch("SELECT id, COALESCE(discount_price, price) AS sale_price, quantity AS stock FROM products WHERE id = ? AND status = 1", [$product_id]);
        if (!$product) {
            echo json_encode(['success' => false, 'message' => 'Product not found.']);
            exit;
        }

        if ($quantity > $product['stock']) {
            echo json_encode(['success' => false, 'message' => 'Only ' . $product['stock'] . ' items available in stock.']);
            exit;
        }

        if (isLoggedIn()) {
            $user_id = $_SESSION['user_id'];
            $db->update("UPDATE cart SET quantity = ? WHERE user_id = ? AND product_id = ?", [$quantity, $user_id, $product_id]);
            $cart_count = getCartCount();
            $cart_total = getCartTotal();
        } else {
            if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];
            $_SESSION['cart'][$product_id] = $quantity;
            $cart_count = getCartSessionCount();
            $cart_total = getCartSessionTotal();
        }

        $subtotal = $product['sale_price'] * $quantity;

        echo json_encode([
            'success' => true,
            'message' => 'Cart updated.',
            'subtotal' => $subtotal,
            'subtotal_formatted' => formatPrice($subtotal),
            'cart_count' => $cart_count,
            'cart_total' => $cart_total,
            'cart_total_formatted' => formatPrice($cart_total)
        ]);
        break;

    case 'remove':
        $product_id = (int)($_POST['product_id'] ?? 0);

        if ($product_id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid product.']);
            exit;
        }

        if (isLoggedIn()) {
            $user_id = $_SESSION['user_id'];
            $db->delete("DELETE FROM cart WHERE user_id = ? AND product_id = ?", [$user_id, $product_id]);
            $cart_count = getCartCount();
            $cart_total = getCartTotal();
        } else {
            if (isset($_SESSION['cart'][$product_id])) {
                unset($_SESSION['cart'][$product_id]);
            }
            $cart_count = getCartSessionCount();
            $cart_total = getCartSessionTotal();
        }

        echo json_encode([
            'success' => true,
            'message' => 'Item removed from cart.',
            'cart_count' => $cart_count,
            'cart_total' => $cart_total,
            'cart_total_formatted' => formatPrice($cart_total)
        ]);
        break;

    case 'get_cart':
        $items = [];
        $subtotal = 0;

        if (isLoggedIn()) {
            $user_id = $_SESSION['user_id'];
            $items = $db->fetchAll(
                "SELECT ci.id AS cart_id, ci.product_id, ci.quantity,
                        p.name, p.slug, p.price, COALESCE(p.discount_price, p.price) AS sale_price,
                        p.quantity AS stock, c.name AS category_name,
                        REPLACE((SELECT image FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1), 'products/', '') as image
                 FROM cart ci
                 JOIN products p ON ci.product_id = p.id
                 LEFT JOIN categories c ON p.category_id = c.id
                 WHERE ci.user_id = ? AND p.status = 1",
                [$user_id]
            );
        } else {
            $items = getCartSessionItems();
        }

        foreach ($items as &$item) {
            $item['subtotal'] = $item['sale_price'] * $item['quantity'];
            $subtotal += $item['subtotal'];
        }
        unset($item);

        $shipping = $subtotal >= FREE_SHIPPING_MIN ? 0 : SHIPPING_CHARGES;
        $tax = round($subtotal * TAX_RATE);
        $coupon_discount = $_SESSION['coupon']['discount'] ?? 0;
        $total = $subtotal + $shipping + $tax - $coupon_discount;
        $coupon_code = $_SESSION['coupon']['code'] ?? '';

        echo json_encode([
            'success' => true,
            'items' => array_values($items),
            'subtotal' => $subtotal,
            'shipping' => $shipping,
            'tax' => $tax,
            'coupon_discount' => $coupon_discount,
            'coupon_code' => $coupon_code,
            'total' => $total,
            'subtotal_formatted' => formatPrice($subtotal),
            'shipping_formatted' => $shipping == 0 ? 'FREE' : formatPrice($shipping),
            'tax_formatted' => formatPrice($tax),
            'total_formatted' => formatPrice($total)
        ]);
        break;

    case 'apply_coupon':
        $code = trim($_POST['code'] ?? '');
        if (empty($code)) {
            echo json_encode(['success' => false, 'message' => 'Please enter a coupon code.']);
            exit;
        }

        if (!isLoggedIn()) {
            echo json_encode(['success' => false, 'message' => 'Please login to apply coupons.']);
            exit;
        }

        $subtotal = 0;
        if (isLoggedIn()) {
            $cart_total = getCartTotal();
            $subtotal = $cart_total;
        } else {
            $subtotal = getCartSessionTotal();
        }

        $coupon = $db->fetch(
            "SELECT * FROM coupons WHERE code = ? AND status = 1 AND (expiry_date IS NULL OR expiry_date >= CURDATE())",
            [$code]
        );

        if (!$coupon) {
            echo json_encode(['success' => false, 'message' => 'Invalid or expired coupon code.']);
            exit;
        }

        if ($coupon['min_order'] > 0 && $subtotal < $coupon['min_order']) {
            echo json_encode(['success' => false, 'message' => 'Minimum order of ' . formatPrice($coupon['min_order']) . ' required for this coupon.']);
            exit;
        }

        if ($coupon['usage_limit'] > 0 && $coupon['used_count'] >= $coupon['usage_limit']) {
            echo json_encode(['success' => false, 'message' => 'This coupon has reached its usage limit.']);
            exit;
        }

        $user_id = $_SESSION['user_id'];

        if ($coupon['type'] === 'percentage') {
            $discount = round($subtotal * $coupon['value'] / 100);
        } else {
            $discount = $coupon['value'];
        }

        if (!empty($coupon['max_discount']) && $discount > $coupon['max_discount']) {
            $discount = $coupon['max_discount'];
        }

        $discount = min($discount, $subtotal);

        $_SESSION['coupon'] = [
            'code' => $coupon['code'],
            'discount' => $discount,
            'coupon_id' => $coupon['id']
        ];

        $new_total = $subtotal - $discount;

        echo json_encode([
            'success' => true,
            'message' => 'Coupon applied successfully!',
            'discount' => $discount,
            'discount_formatted' => formatPrice($discount),
            'new_total' => $new_total,
            'new_total_formatted' => formatPrice($new_total)
        ]);
        break;

    case 'remove_coupon':
        unset($_SESSION['coupon']);

        $subtotal = isLoggedIn() ? getCartTotal() : getCartSessionTotal();
        $shipping = $subtotal >= FREE_SHIPPING_MIN ? 0 : SHIPPING_CHARGES;
        $tax = round($subtotal * TAX_RATE);
        $total = $subtotal + $shipping + $tax;

        echo json_encode([
            'success' => true,
            'message' => 'Coupon removed.',
            'total' => $total,
            'total_formatted' => formatPrice($total)
        ]);
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action.']);
        break;
}
