<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/functions_product.php';

function jsonResponse($data, $code = 200) {
    http_response_code($code);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function getParam($key, $default = null) {
    return isset($_GET[$key]) && $_GET[$key] !== '' ? $_GET[$key] : $default;
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
}

$single_id = getParam('id');
$search_query = getParam('search');
$featured = getParam('featured');
$trending = getParam('trending');
$flash_sale = getParam('flash_sale');

if ($single_id) {
    $product = getProductById((int)$single_id);
    if (!$product) {
        jsonResponse(['success' => false, 'message' => 'Product not found'], 404);
    }
    jsonResponse(['success' => true, 'data' => $product]);
}

if ($search_query) {
    $products = searchProducts(sanitize($search_query));
    jsonResponse([
        'success' => true,
        'data' => $products,
        'pagination' => [
            'total' => count($products),
            'per_page' => count($products),
            'current_page' => 1,
            'total_pages' => 1,
        ]
    ]);
}

if ($featured === '1') {
    $limit = (int)(getParam('limit', 8));
    $products = getFeaturedProducts($limit);
    jsonResponse([
        'success' => true,
        'data' => $products,
        'pagination' => [
            'total' => count($products),
            'per_page' => $limit,
            'current_page' => 1,
            'total_pages' => 1,
        ]
    ]);
}

if ($trending === '1') {
    $limit = (int)(getParam('limit', 8));
    $products = getTrendingProducts($limit);
    jsonResponse([
        'success' => true,
        'data' => $products,
        'pagination' => [
            'total' => count($products),
            'per_page' => $limit,
            'current_page' => 1,
            'total_pages' => 1,
        ]
    ]);
}

if ($flash_sale === '1') {
    $limit = (int)(getParam('limit', 8));
    $products = getFlashSaleProducts($limit);
    jsonResponse([
        'success' => true,
        'data' => $products,
        'pagination' => [
            'total' => count($products),
            'per_page' => $limit,
            'current_page' => 1,
            'total_pages' => 1,
        ]
    ]);
}

$filters = [];

$category = getParam('category');
if ($category) {
    $db = Database::getInstance();
    $cat = $db->fetch("SELECT id FROM categories WHERE slug = ? OR id = ?", [$category, (int)$category]);
    if ($cat) {
        $filters['category_id'] = $cat['id'];
    }
}

$brand = getParam('brand');
if ($brand) {
    $db = Database::getInstance();
    $br = $db->fetch("SELECT id FROM brands WHERE slug = ? OR id = ?", [$brand, (int)$brand]);
    if ($br) {
        $filters['brand_id'] = $br['id'];
    }
}

$min_price = getParam('min_price');
if ($min_price !== null) {
    $filters['min_price'] = (float)$min_price;
}

$max_price = getParam('max_price');
if ($max_price !== null) {
    $filters['max_price'] = (float)$max_price;
}

$sort = getParam('sort');
if ($sort) {
    $filters['sort'] = $sort;
}

$page = (int)(getParam('page', 1));
$limit = (int)(getParam('limit', PRODUCTS_PER_PAGE));
$limit = max(1, min($limit, 50));

$filters['page'] = $page;
$filters['per_page'] = $limit;

$result = getAllProducts($filters);

jsonResponse([
    'success' => true,
    'data' => $result['products'],
    'pagination' => [
        'total' => $result['pagination']['total'],
        'per_page' => $result['pagination']['per_page'],
        'current_page' => $result['pagination']['current_page'],
        'total_pages' => $result['pagination']['total_pages'],
        'has_prev' => $result['pagination']['has_prev'],
        'has_next' => $result['pagination']['has_next'],
    ]
]);
