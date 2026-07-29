<?php
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

$action = $_POST['action'] ?? '';
$db = Database::getInstance();

if (!isset($_SESSION['compare'])) $_SESSION['compare'] = [];

switch ($action) {

    case 'add':
        $product_id = (int)($_POST['product_id'] ?? 0);
        if ($product_id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid product.']);
            exit;
        }

        if (in_array($product_id, $_SESSION['compare'])) {
            echo json_encode(['success' => false, 'message' => 'Product already in comparison list.']);
            exit;
        }

        if (count($_SESSION['compare']) >= 4) {
            echo json_encode(['success' => false, 'message' => 'You can compare up to 4 products at a time. Remove one first.']);
            exit;
        }

        $product = $db->fetch(
            "SELECT p.id, p.name, p.slug,
                    REPLACE((SELECT image FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1), 'products/', '') AS image,
                    COALESCE(p.discount_price, p.price) AS sale_price, p.price,
                    p.rating, p.reviews_count, p.quantity AS stock,
                    c.name AS category_name, b.name AS brand_name
             FROM products p
             LEFT JOIN categories c ON p.category_id = c.id
             LEFT JOIN brands b ON p.brand_id = b.id
             WHERE p.id = ? AND p.status = 1",
            [$product_id]
        );

        if (!$product) {
            echo json_encode(['success' => false, 'message' => 'Product not found.']);
            exit;
        }

        $_SESSION['compare'][] = $product_id;

        echo json_encode([
            'success'    => true,
            'message'    => '"' . $product['name'] . '" added to comparison.',
            'compare_ids' => $_SESSION['compare'],
            'compare_count' => count($_SESSION['compare'])
        ]);
        break;

    case 'remove':
        $product_id = (int)($_POST['product_id'] ?? 0);
        if ($product_id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid product.']);
            exit;
        }

        $_SESSION['compare'] = array_values(array_filter($_SESSION['compare'], fn($id) => $id !== $product_id));

        echo json_encode([
            'success'      => true,
            'message'      => 'Product removed from comparison.',
            'compare_ids'  => $_SESSION['compare'],
            'compare_count' => count($_SESSION['compare'])
        ]);
        break;

    case 'get':
        $compare_ids = $_SESSION['compare'];
        $products = [];

        if (!empty($compare_ids)) {
            $placeholders = implode(',', array_fill(0, count($compare_ids), '?'));
            $products = $db->fetchAll(
                "SELECT p.id, p.name, p.slug, p.description, p.short_description,
                        p.price, COALESCE(p.discount_price, p.price) AS sale_price,
                        p.discount_percent, REPLACE((SELECT image FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1), 'products/', '') AS image, p.quantity AS stock,
                        p.rating, p.reviews_count, p.sku,
                        c.name AS category_name, b.name AS brand_name
                 FROM products p
                 LEFT JOIN categories c ON p.category_id = c.id
                 LEFT JOIN brands b ON p.brand_id = b.id
                 WHERE p.id IN ($placeholders) AND p.status = 1",
                $compare_ids
            );

            foreach ($products as &$p) {
                $p['specifications'] = $db->fetchAll(
                    "SELECT spec_name AS name, spec_value AS value FROM product_specifications WHERE product_id = ? ORDER BY sort_order ASC",
                    [$p['id']]
                );
            }
            unset($p);
        }

        $all_specs = [];
        foreach ($products as $p) {
            foreach ($p['specifications'] as $spec) {
                $all_specs[$spec['name']] = true;
            }
        }

        echo json_encode([
            'success'       => true,
            'products'      => $products,
            'compare_count' => count($products),
            'all_spec_keys' => array_keys($all_specs),
            'compare_ids'   => $compare_ids
        ]);
        break;

    case 'clear':
        $_SESSION['compare'] = [];
        echo json_encode([
            'success' => true,
            'message' => 'Comparison list cleared.',
            'compare_count' => 0
        ]);
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action.']);
        break;
}
