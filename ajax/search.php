<?php
error_reporting(0);
header('Content-Type: application/json');

$pdo = null;
try {
    $pdo = new PDO("mysql:host=localhost;dbname=ai_shopping;charset=utf8mb4", 'root', '', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'DB error']);
    exit;
}

$action = $_GET['action'] ?? $_POST['action'] ?? '';

if ($action === 'suggest') {
    $q = trim($_GET['q'] ?? '');
    if (mb_strlen($q) < 2) {
        echo json_encode(['success' => true, 'suggestions' => []]);
        exit;
    }

    $like = '%' . $q . '%';
    $stmt = $pdo->prepare(
        "SELECT p.id, p.name, p.slug, p.price, COALESCE(p.discount_price, p.price) AS sale_price,
                REPLACE((SELECT image FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1), 'products/', '') AS image
         FROM products p
         WHERE p.status = 1 AND p.quantity > 0
           AND (p.name LIKE ? OR p.description LIKE ?)
         ORDER BY p.reviews_count DESC, p.rating DESC
         LIMIT 10"
    );
    $stmt->execute([$like, $like]);
    $products = $stmt->fetchAll();

    $results = [];
    foreach ($products as $p) {
        $results[] = [
            'id'    => (int)$p['id'],
            'name'  => $p['name'],
            'price' => (float)$p['sale_price'],
            'image' => $p['image'] ?? 'default.png',
            'slug'  => $p['slug'],
            'url'   => 'product.php?slug=' . $p['slug']
        ];
    }

    echo json_encode(['success' => true, 'suggestions' => $results]);
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid action.']);
}
