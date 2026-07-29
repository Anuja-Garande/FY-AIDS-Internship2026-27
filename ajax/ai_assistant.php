<?php
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

$action = $_POST['action'] ?? '';
$db = Database::getInstance();

function parseUserMessage($message) {
    $lower = strtolower(trim($message));

    $price_patterns = [
        '/under\s+₹?\s*(\d[\d,]*)/i',
        '/below\s+₹?\s*(\d[\d,]*)/i',
        '/less\s+than\s+₹?\s*(\d[\d,]*)/i',
        '/budget\s+(?:of\s+)?₹?\s*(\d[\d,]*)/i',
        '/around\s+₹?\s*(\d[\d,]*)/i',
        '/₹?\s*(\d[\d,]*)\s+(?:or\s+)?less/i',
    ];
    $max_price = null;
    foreach ($price_patterns as $pat) {
        if (preg_match($pat, $lower, $m)) {
            $max_price = (int)str_replace(',', '', $m[1]);
            break;
        }
    }

    $category_map = [
        'phone'     => ['phone', 'mobile', 'smartphone', 'android', 'iphone', 'galaxy', 'oneplus'],
        'laptop'    => ['laptop', 'notebook', 'computer', 'macbook', 'thinkpad', 'pavilion'],
        'headphone' => ['headphone', 'earphone', 'earbuds', 'headset', 'airpods', 'buds'],
        'camera'    => ['camera', 'dslr', 'gopro', 'action cam'],
        'shoe'      => ['shoe', 'sneaker', 'boot', 'jordan', 'ultraboost', 'campus'],
        'tv'        => ['tv', 'television', 'monitor', 'display', 'oled', 'bravia'],
        'speaker'   => ['speaker', 'soundbar', 'audio'],
        'gaming'    => ['gaming', 'game', 'gamer', 'victus'],
        'book'      => ['book', 'novel', 'textbook'],
        'fashion'   => ['shirt', 'tshirt', 't-shirt', 'dress', 'legging', 'clothing'],
        'sports'    => ['sports', 'football', 'cricket', 'training', 'yoga'],
        'beauty'    => ['beauty', 'cosmetic', 'makeup'],
        'watch'     => ['watch', 'smartwatch'],
    ];

    $matched_categories = [];
    foreach ($category_map as $cat => $terms) {
        foreach ($terms as $term) {
            if (str_contains($lower, $term)) {
                $matched_categories[] = $cat;
                break;
            }
        }
    }

    $use_cases = [
        'gaming'       => ['gaming', 'game', 'gamer', 'play', 'esport'],
        'student'      => ['student', 'study', 'college', 'university', 'school', 'homework'],
        'professional' => ['office', 'professional', 'work', 'business', 'meeting'],
        'music'        => ['music', 'song', 'audio', 'listen', 'singing'],
        'photography'  => ['photo', 'photography', 'camera', 'shoot', 'vlog'],
        'fitness'      => ['fitness', 'gym', 'exercise', 'sport', 'running', 'workout'],
        'travel'       => ['travel', 'trip', 'journey', 'vacation', 'pack'],
        'gift'         => ['gift', 'present', 'surprise', 'birthday', 'anniversary'],
    ];
    $matched_use = null;
    foreach ($use_cases as $use => $terms) {
        foreach ($terms as $term) {
            if (str_contains($lower, $term)) {
                $matched_use = $use;
                break 2;
            }
        }
    }

    $is_compare = (str_contains($lower, 'compare') || str_contains($lower, ' vs ') || str_contains($lower, 'difference'));

    $stop_words = ['best', 'top', 'buy', 'please', 'suggest', 'recommend', 'show', 'me', 'the',
                    'a', 'an', 'and', 'or', 'for', 'under', 'above', 'good', 'nice', 'great',
                    'which', 'what', 'some', 'any', 'need', 'want', 'looking', 'find', 'get',
                    'cheap', 'affordable', 'within', 'between', 'that', 'this', 'with'];
    $words = preg_split('/\s+/', preg_replace('/[^a-z0-9\s]/', ' ', $lower));
    $keywords = array_values(array_diff($words, $stop_words));

    return [
        'categories' => array_unique($matched_categories),
        'use_case'   => $matched_use,
        'max_price'  => $max_price,
        'is_compare' => $is_compare,
        'keywords'   => $keywords,
    ];
}

function searchProductsForAI($parsed, $db) {
    $sql = "SELECT p.id, p.name, p.slug, p.short_description, p.description,
                   p.price, COALESCE(p.discount_price, p.price) AS sale_price,
                   REPLACE((SELECT image FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1), 'products/', '') AS image, p.quantity AS stock, p.rating, p.reviews_count,
                   c.name AS category_name, c.slug AS category_slug,
                   b.name AS brand_name
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            LEFT JOIN brands b ON p.brand_id = b.id
            WHERE p.status = 1 AND p.quantity > 0";
    $params = [];
    $conditions = [];

    if (!empty($parsed['categories'])) {
        $cat_or = [];
        foreach ($parsed['categories'] as $cat) {
            $cat_or[] = "c.name LIKE ?";
            $params[] = '%' . $cat . '%';
            $cat_or[] = "p.name LIKE ?";
            $params[] = '%' . $cat . '%';
            $cat_or[] = "c.slug LIKE ?";
            $params[] = '%' . $cat . '%';
        }
        $conditions[] = "(" . implode(' OR ', $cat_or) . ")";
    }

    if ($parsed['max_price'] !== null) {
        $conditions[] = "COALESCE(p.discount_price, p.price) <= ?";
        $params[] = $parsed['max_price'];
    }

    if (!empty($parsed['keywords'])) {
        $kw_or = [];
        foreach ($parsed['keywords'] as $kw) {
            $kw_or[] = "p.name LIKE ?";
            $params[] = '%' . $kw . '%';
        }
        if (count($kw_or) > 0) {
            $conditions[] = "(" . implode(' OR ', $kw_or) . ")";
        }
    }

    if (!empty($conditions)) {
        $sql .= " AND " . implode(' AND ', $conditions);
    }

    $sql .= " ORDER BY p.rating DESC, p.reviews_count DESC LIMIT 6";
    return $db->fetchAll($sql, $params);
}

function formatProductCards($products, $db) {
    if (empty($products)) return '';

    $html = '<div style="display:flex;flex-direction:column;gap:10px;margin-top:10px;">';
    foreach ($products as $p) {
        $img = !empty($p['image']) ? $p['image'] : 'default.png';
        $url = BASE_URL . 'product.php?slug=' . htmlspecialchars($p['slug']);
        $rating = (float)($p['rating'] ?? 0);
        $stars = '';
        for ($i = 1; $i <= 5; $i++) {
            $stars .= $i <= $rating ? '★' : '☆';
        }
        $specs = $db->fetchAll("SELECT spec_name, spec_value FROM product_specifications WHERE product_id = ? ORDER BY sort_order LIMIT 3", [$p['id']]);
        $spec_text = '';
        foreach ($specs as $s) {
            $spec_text .= '<span style="color:var(--text-muted,#666);font-size:0.8rem;">' . htmlspecialchars($s['spec_name']) . ': ' . htmlspecialchars($s['spec_value']) . '</span><br>';
        }

        $html .= '<div style="display:flex;gap:12px;background:var(--bg-card,white);border:1px solid var(--border-color,#e9ecef);border-radius:12px;padding:12px;">';
        $html .= '<img src="' . BASE_URL . 'assets/uploads/products/' . htmlspecialchars($img) . '" style="width:70px;height:70px;object-fit:cover;border-radius:8px;" alt="">';
        $html .= '<div style="flex:1;">';
        $html .= '<a href="' . $url . '" style="font-weight:600;color:var(--text-primary,#333);text-decoration:none;font-size:0.95rem;">' . htmlspecialchars($p['name']) . '</a><br>';
        $html .= '<span style="color:#f59e0b;font-size:0.85rem;">' . $stars . '</span> <span style="color:var(--text-muted,#999);font-size:0.8rem;">(' . ($p['reviews_count'] ?? 0) . ')</span><br>';
        $html .= '<span style="font-weight:700;color:#667eea;font-size:1.05rem;">₹' . number_format($p['sale_price'], 0) . '</span>';
        if ($p['discount_price'] && $p['price'] > $p['sale_price']) {
            $html .= ' <del style="color:var(--text-muted,#999);font-size:0.8rem;">₹' . number_format($p['price'], 0) . '</del>';
            $html .= ' <span style="color:#22c55e;font-size:0.8rem;font-weight:600;">' . $p['discount_percent'] ?? round((1 - $p['sale_price'] / $p['price']) * 100) . '% off</span>';
        }
        $html .= '<br>' . $spec_text;
        $html .= '<a href="' . $url . '" style="display:inline-block;margin-top:4px;padding:4px 14px;background:#667eea;color:white;border-radius:6px;text-decoration:none;font-size:0.8rem;">View Product</a>';
        $html .= '</div></div>';
    }
    $html .= '</div>';
    return $html;
}

function generateFollowUps($parsed) {
    $suggestions = [];
    if (!empty($parsed['categories'])) {
        $cat = $parsed['categories'][0];
        $suggestions[] = "Show me best $cat brands";
        if ($parsed['max_price']) {
            $suggestions[] = "Show cheaper $cat options";
        }
    }
    if ($parsed['use_case'] && $parsed['use_case'] !== 'gift') {
        $suggestions[] = "Best {$parsed['use_case']} products";
    }
    $suggestions[] = "What's on sale today?";
    $suggestions[] = "Show trending products";

    return array_slice($suggestions, 0, 3);
}

switch ($action) {

    case 'chat':
        $message = trim($_POST['message'] ?? '');
        if (empty($message)) {
            echo json_encode(['success' => false, 'message' => 'Please type a message.']);
            exit;
        }

        if (!isset($_SESSION['ai_chat'])) $_SESSION['ai_chat'] = [];
        $_SESSION['ai_chat'][] = ['role' => 'user', 'message' => $message, 'time' => date('H:i')];

        $parsed = parseUserMessage($message);
        $products = searchProductsForAI($parsed, $db);

        $greeting_patterns = ['/^(hi|hello|hey|namaste|sup|yo)/i', '/^(good\s*(morning|afternoon|evening))/i', '/^(howdy|greetings)/i'];
        $is_greeting = false;
        foreach ($greeting_patterns as $pat) {
            if (preg_match($pat, trim($message))) {
                $is_greeting = true;
                break;
            }
        }

        if ($is_greeting && empty($parsed['categories']) && $parsed['max_price'] === null) {
            $response_text = "Hello! Welcome to ShopSphere! 👋 I can help you find the perfect products. You can ask me things like:\n\n";
            $response_text .= "• \"Best phones under ₹20000\"\n• \"Laptops for students\"\n• \"Gaming headphones\"\n• \"Gift ideas under ₹1000\"\n\nWhat are you looking for today?";
            $products = $db->fetchAll("SELECT p.id, p.name, p.slug, p.short_description, p.description, p.price, COALESCE(p.discount_price, p.price) AS sale_price, REPLACE((SELECT image FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1), 'products/', '') AS image, p.quantity AS stock, p.rating, p.reviews_count, c.name AS category_name, c.slug AS category_slug, b.name AS brand_name FROM products p LEFT JOIN categories c ON p.category_id = c.id LEFT JOIN brands b ON p.brand_id = b.id WHERE p.status = 1 AND p.quantity > 0 ORDER BY p.reviews_count DESC LIMIT 4");
        } elseif (!empty($products)) {
            $cat_label = !empty($parsed['categories']) ? $parsed['categories'][0] . 's' : 'products';
            $response_text = "Great choice! I found " . count($products) . " " . htmlspecialchars($cat_label) . " for you";
            if ($parsed['max_price']) {
                $response_text .= " under ₹" . number_format($parsed['max_price']);
            }
            if ($parsed['use_case']) {
                $response_text .= " perfect for " . $parsed['use_case'];
            }
            $response_text .= ". Here are my top recommendations:";
        } else {
            $response_text = "I couldn't find exact matches for \"" . htmlspecialchars($message) . "\". Here are some popular products you might like! Try asking about specific categories, brands, or price ranges for better results.";
            $products = $db->fetchAll("SELECT p.id, p.name, p.slug, p.short_description, p.description, p.price, COALESCE(p.discount_price, p.price) AS sale_price, REPLACE((SELECT image FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1), 'products/', '') AS image, p.quantity AS stock, p.rating, p.reviews_count, c.name AS category_name, c.slug AS category_slug, b.name AS brand_name FROM products p LEFT JOIN categories c ON p.category_id = c.id LEFT JOIN brands b ON p.brand_id = b.id WHERE p.status = 1 AND p.quantity > 0 ORDER BY p.reviews_count DESC LIMIT 4");
        }

        $product_cards = formatProductCards($products, $db);
        $follow_ups = generateFollowUps($parsed);
        $follow_html = '';
        if (!empty($follow_ups)) {
            $follow_html = '<div style="margin-top:12px;display:flex;flex-wrap:wrap;gap:6px;">';
            foreach ($follow_ups as $fu) {
                $follow_html .= '<span class="quick-follow" style="background:#f0f4ff;color:#667eea;border:1px solid #d4dafc;border-radius:16px;padding:4px 12px;font-size:0.8rem;cursor:pointer;" onclick="sendQuickMessage(\'' . htmlspecialchars(addslashes($fu)) . '\')">' . htmlspecialchars($fu) . '</span>';
            }
            $follow_html .= '</div>';
        }

        $full_response = htmlspecialchars($response_text) . $product_cards . $follow_html;

        $product_data = [];
        foreach ($products as $p) {
            $product_data[] = [
                'id'    => (int)$p['id'],
                'name'  => $p['name'],
                'price' => (float)$p['sale_price'],
                'image' => $p['image'] ?? 'default.png',
                'slug'  => $p['slug'],
                'rating' => (float)($p['rating'] ?? 0),
                'url'   => BASE_URL . 'product.php?slug=' . $p['slug']
            ];
        }

        $_SESSION['ai_chat'][] = [
            'role'     => 'ai',
            'message'  => $response_text,
            'products' => $product_data,
            'time'     => date('H:i')
        ];

        if (count($_SESSION['ai_chat']) > 50) {
            $_SESSION['ai_chat'] = array_slice($_SESSION['ai_chat'], -50);
        }

        echo json_encode([
            'success'    => true,
            'response'   => $full_response,
            'products'   => $product_data,
            'follow_ups' => $follow_ups,
            'suggested_products' => $product_data
        ]);
        break;

    case 'history':
        $messages = $_SESSION['ai_chat'] ?? [];
        echo json_encode([
            'success'  => true,
            'messages' => $messages,
            'count'    => count($messages)
        ]);
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action.']);
        break;
}
