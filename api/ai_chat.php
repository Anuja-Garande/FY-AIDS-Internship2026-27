<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/functions_product.php';

function json_response($data, $code = 200) {
    http_response_code($code);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['success' => false, 'message' => 'Method not allowed. Use POST.'], 405);
}

$input = json_decode(file_get_contents('php://input'), true);
if (!$input || empty($input['message'])) {
    json_response(['success' => false, 'message' => 'Message is required in JSON body.'], 400);
}

$message = trim($input['message']);
if (empty($message)) {
    json_response(['success' => false, 'message' => 'Message cannot be empty.'], 400);
}

$conversation_history = $input['history'] ?? [];

$OPENAI_API_KEY = defined('OPENAI_API_KEY') ? OPENAI_API_KEY : '';

if (!empty($OPENAI_API_KEY)) {
    $system_prompt = "You are an AI Shopping Assistant for " . SITE_NAME . ". You help users find products, compare items, and make purchase decisions. Be friendly, concise, and helpful. When recommending products, include their names, prices, and brief descriptions. Format your responses nicely. If you don't know about a specific product, suggest the user browse the website.";

    $api_messages = [
        ['role' => 'system', 'content' => $system_prompt]
    ];

    foreach (array_slice($conversation_history, -10) as $msg) {
        $api_messages[] = [
            'role' => $msg['role'] ?? 'user',
            'content' => $msg['content'] ?? ''
        ];
    }
    $api_messages[] = ['role' => 'user', 'content' => $message];

    $ch = curl_init('https://api.openai.com/v1/chat/completions');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $OPENAI_API_KEY,
        ],
        CURLOPT_POSTFIELDS => json_encode([
            'model' => 'gpt-3.5-turbo',
            'messages' => $api_messages,
            'max_tokens' => 800,
            'temperature' => 0.7,
        ]),
        CURLOPT_TIMEOUT => 30,
        CURLOPT_SSL_VERIFYPEER => true,
    ]);

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($response && $http_code === 200) {
        $api_data = json_decode($response, true);
        $reply = $api_data['choices'][0]['message']['content'] ?? 'Sorry, I could not process your request.';
        $products = aiSearchProducts($message);
        json_response([
            'success' => true,
            'response' => $reply,
            'products' => array_slice($products, 0, 4),
            'source' => 'openai',
        ]);
    } else {
        error_log("OpenAI API error: HTTP $http_code - $response");
    }
}

$products = aiSearchProducts($message);
$reply = '';
$lower_msg = strtolower($message);

if (preg_match('/\b(hello|hi|hey|good\s*(morning|afternoon|evening))\b/i', $lower_msg)) {
    $reply = "Hello! Welcome to " . SITE_NAME . "! I'm your AI shopping assistant. How can I help you today? You can ask me about products, categories, deals, or recommendations.";
} elseif (preg_match('/\b(thank|thanks)\b/i', $lower_msg)) {
    $reply = "You're welcome! Is there anything else I can help you with?";
} elseif (preg_match('/\b(help|what\s*can\s*you\s*do)\b/i', $lower_msg)) {
    $reply = "I can help you with:\n- Finding specific products\n- Comparing products by features and price\n- Recommending products based on your needs\n- Checking deals and flash sales\n- Navigating categories\n\nJust describe what you're looking for!";
} elseif (preg_match('/\b(deal|offer|discount|sale|cheap)\b/i', $lower_msg)) {
    $reply = "Great, let me find the best deals for you! Here are some products currently on sale:";
} elseif (preg_match('/\b(bestsell|popular|trending|top\s*rated)\b/i', $lower_msg)) {
    $reply = "Here are our most popular products based on sales and ratings:";
} elseif (!empty($products)) {
    $count = count($products);
    $reply = "I found $count product" . ($count !== 1 ? 's' : '') . " that match your request. Here are the top results:";
} else {
    $reply = "I couldn't find specific products matching your query. Could you try describing what you're looking for differently? For example:\n- \"wireless headphones under 2000\"\n- \"best running shoes\"\n- \"phone cases for iPhone\"";
}

json_response([
    'success' => true,
    'response' => $reply,
    'products' => array_slice($products, 0, 4),
    'source' => 'database',
]);
