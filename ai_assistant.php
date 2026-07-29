<?php
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'includes/functions.php';
require_once 'includes/functions_product.php';

$page_title = 'ShopSphere Assistant';
$db = Database::getInstance();

if (isset($_POST['send_message'])) {
    $user_message = trim($_POST['message'] ?? '');
    if (!empty($user_message)) {
        if (!isset($_SESSION['ai_chat'])) $_SESSION['ai_chat'] = [];
        $_SESSION['ai_chat'][] = ['role' => 'user', 'message' => $user_message];

        $ai_response = generateAIResponse($user_message, $db);
        $_SESSION['ai_chat'][] = ['role' => 'ai', 'message' => $ai_response['text'], 'products' => $ai_response['products'] ?? []];
    }
    header('Content-Type: application/json');
    echo json_encode(['success' => true]);
    exit;
}

if (isset($_GET['clear_chat'])) {
    unset($_SESSION['ai_chat']);
    header('Location: ai_assistant.php');
    exit;
}

if (isset($_POST['quick_question'])) {
    $q = trim($_POST['question'] ?? '');
    if (!empty($q)) {
        if (!isset($_SESSION['ai_chat'])) $_SESSION['ai_chat'] = [];
        $_SESSION['ai_chat'][] = ['role' => 'user', 'message' => $q];
        $ai_response = generateAIResponse($q, $db);
        $_SESSION['ai_chat'][] = ['role' => 'ai', 'message' => $ai_response['text'], 'products' => $ai_response['products'] ?? []];
    }
    redirect('ai_assistant.php');
}

function generateAIResponse($query, $db) {
    $query_lower = strtolower($query);
    $text = '';
    $products = [];

    $price_match = [];
    preg_match_all('/(\d[\d,]*)/', $query, $price_match);
    $prices = array_map(function($p) { return (int)str_replace(',', '', $p); }, $price_match[0] ?? []);

    $keywords = preg_split('/\s+/', $query_lower);
    $stop_words = ['best', 'top', 'buy', 'please', 'suggest', 'recommend', 'show', 'me', 'the', 'a', 'an', 'and', 'or', 'for', 'under', 'above', 'between', 'cheap', 'good', 'nice', 'great'];
    $search_terms = array_diff($keywords, $stop_words);

    $category_map = [
        'phone' => ['mobile', 'smartphone', 'phone', 'android', 'iphone'],
        'laptop' => ['laptop', 'notebook', 'computer'],
        'headphone' => ['headphone', 'earphone', 'earbuds', 'headset'],
        'camera' => ['camera', 'dslr', 'gopro'],
        'shoe' => ['shoe', 'sneaker', 'boot', 'footwear'],
        'shirt' => ['shirt', 'tshirt', 't-shirt', 'top'],
        'watch' => ['watch', 'smartwatch', 'smart watch'],
        'tv' => ['tv', 'television', 'monitor', 'display'],
        'speaker' => ['speaker', 'soundbar', 'audio'],
        'gaming' => ['gaming', 'game', 'gamer', 'playstation', 'xbox'],
        'bag' => ['bag', 'backpack', 'handbag', 'luggage'],
        'book' => ['book', 'novel', 'textbook'],
        'fashion' => ['fashion', 'dress', 'jeans', 'pant', 'trouser'],
        'beauty' => ['beauty', 'cosmetic', 'makeup', 'skincare'],
        'home' => ['home', 'furniture', 'decor', 'kitchen'],
    ];

    $matched_category = null;
    foreach ($category_map as $cat => $terms) {
        foreach ($terms as $term) {
            if (in_array($term, $search_terms) || str_contains($query_lower, $term)) {
                $matched_category = $cat;
                break;
            }
        }
        if ($matched_category) break;
    }

    $use_case_map = [
        'student' => ['student', 'study', 'college', 'university', 'school'],
        'professional' => ['office', 'professional', 'work', 'business'],
        'gaming' => ['gaming', 'game', 'gamer'],
        'fitness' => ['fitness', 'gym', 'exercise', 'sport', 'running'],
        'travel' => ['travel', 'trip', 'journey', 'vacation'],
    ];
    $matched_use = null;
    foreach ($use_case_map as $use => $terms) {
        foreach ($terms as $term) {
            if (in_array($term, $search_terms) || str_contains($query_lower, $term)) {
                $matched_use = $use;
                break;
            }
        }
        if ($matched_use) break;
    }

    $sql = "SELECT p.*, REPLACE((SELECT image FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1), 'products/', '') AS image, c.name as category_name, c.slug as category_slug FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.status = 1 AND p.quantity > 0";
    $params = [];
    $conditions = [];

    if ($matched_category) {
        $conditions[] = "(c.name LIKE :cat OR c.slug LIKE :cat OR p.name LIKE :cat_name)";
        $params[':cat'] = '%' . $matched_category . '%';
        $params[':cat_name'] = '%' . $matched_category . '%';
    }

    if (!empty($search_terms)) {
        $or_parts = [];
        foreach ($search_terms as $i => $term) {
            $key = ":kw_$i";
            $or_parts[] = "p.name LIKE $key";
            $params[$key] = '%' . $term . '%';
        }
        if (!empty($or_parts)) {
            $conditions[] = "(" . implode(' OR ', $or_parts) . ")";
        }
    }

    if (count($prices) >= 2) {
        $conditions[] = "COALESCE(p.discount_price, p.price) BETWEEN :price_min AND :price_max";
        $params[':price_min'] = min($prices);
        $params[':price_max'] = max($prices);
    } elseif (count($prices) == 1) {
        $conditions[] = "COALESCE(p.discount_price, p.price) <= :price_max";
        $params[':price_max'] = $prices[0];
    }

    if (!empty($conditions)) {
        $sql .= " AND " . implode(' AND ', $conditions);
    }

    $sql .= " ORDER BY p.rating DESC, p.reviews_count DESC LIMIT 6";
    $products = $db->fetchAll($sql, $params);

    if (!empty($products)) {
        $cat_name = $products[0]['category_name'] ?? 'products';
        $text = "Here are the best " . htmlspecialchars($cat_name) . " I found for you based on your query:";
        if (!empty($prices)) {
            $text .= " All within your budget of ₹" . number_format(max($prices)) . ".";
        }
        $text .= " These products are highly rated and currently in stock. Would you like more details on any of these?";
    } else {
        $text = "I couldn't find exact matches for your query, but let me suggest some popular products that might interest you! Try refining your search with specific categories, brands, or price ranges.";
        $products = $db->fetchAll("SELECT p.*, REPLACE((SELECT image FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1), 'products/', '') AS image, c.name as category_name, c.slug as category_slug FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.status = 1 AND p.quantity > 0 ORDER BY p.reviews_count DESC LIMIT 4");
    }

    return ['text' => $text, 'products' => $products];
}

$chat_messages = $_SESSION['ai_chat'] ?? [];
$quick_questions = [
    'Best phone under ₹20000',
    'Suggest laptops for students',
    'Best gaming products',
    'Gift suggestions under ₹1000',
    'Compare products',
    'Best headphones',
];
?>
<?php include 'includes/header.php'; ?>

<style>
.chat-container { max-height: 500px; overflow-y: auto; scroll-behavior: smooth; }
.chat-container::-webkit-scrollbar { width: 6px; }
.chat-container::-webkit-scrollbar-thumb { background: #ccc; border-radius: 3px; }
.chat-bubble { max-width: 80%; padding: 14px 18px; border-radius: 18px; margin-bottom: 12px; animation: fadeInUp 0.3s ease; }
.chat-bubble.user { background: linear-gradient(135deg, #667eea, #764ba2); color: white; margin-left: auto; border-bottom-right-radius: 4px; }
.chat-bubble.ai { background: var(--bg-card,#f0f2f5); color: var(--text-primary,#333); border-bottom-left-radius: 4px; }
@keyframes fadeInUp { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
.typing-indicator { display: flex; gap: 4px; padding: 14px 18px; }
.typing-indicator span { width: 8px; height: 8px; background: #999; border-radius: 50%; animation: typing 1.4s infinite; }
.typing-indicator span:nth-child(2) { animation-delay: 0.2s; }
.typing-indicator span:nth-child(3) { animation-delay: 0.4s; }
@keyframes typing { 0%, 60%, 100% { transform: translateY(0); } 30% { transform: translateY(-8px); } }
.quick-question { background: var(--bg-card,white); border: 1px solid var(--border-color,#dee2e6); border-radius: 20px; padding: 8px 16px; cursor: pointer; transition: all 0.3s; font-size: 0.85rem; white-space: nowrap; color: var(--text-primary,#333); }
.quick-question:hover { background: #667eea; color: white; border-color: #667eea; transform: translateY(-2px); }
.product-card-mini { display: flex; gap: 12px; background: var(--bg-card,white); border-radius: 12px; padding: 12px; margin-top: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); transition: transform 0.2s; }
.product-card-mini:hover { transform: translateY(-2px); }
.product-card-mini img { width: 60px; height: 60px; object-fit: cover; border-radius: 8px; }
.chat-input-area { background: var(--bg-card,white); border-top: 1px solid var(--border-color,#eee); padding: 16px; border-radius: 0 0 16px 16px; }
</style>

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow" style="border-radius:16px;">
                <div class="card-header bg-primary text-white p-3" style="border-radius:16px 16px 0 0;">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center">
                            <div class="rounded-circle bg-white text-primary d-flex align-items-center justify-content-center me-3" style="width:40px;height:40px;">
                                <i class="fas fa-robot"></i>
                            </div>
                            <div>
                                <h6 class="mb-0 fw-bold">ShopSphere Assistant</h6>
                                <small class="opacity-75">Always here to help you find the best products</small>
                            </div>
                        </div>
                        <?php if (!empty($chat_messages)): ?>
                        <a href="ai_assistant.php?clear_chat=1" class="btn btn-sm btn-outline-light"><i class="fas fa-trash me-1"></i>Clear</a>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="chat-container p-3" id="chatContainer" style="min-height:400px;background:var(--bg-secondary,#fafafa);">
                    <?php if (empty($chat_messages)): ?>
                    <div class="text-center py-5">
                        <div class="mb-3">
                            <div class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center" style="width:80px;height:80px;font-size:2rem;">
                                <i class="fas fa-robot"></i>
                            </div>
                        </div>
                        <h5 class="fw-bold">Hello! I'm your ShopSphere Assistant</h5>
                        <p class="text-muted mb-4">Ask me anything about products, or try one of these quick questions:</p>
                        <div class="d-flex flex-wrap justify-content-center gap-2" id="quickQuestions">
                            <?php foreach ($quick_questions as $qq): ?>
                            <form method="POST" class="d-inline">
                                <input type="hidden" name="question" value="<?= htmlspecialchars($qq) ?>">
                                <button type="submit" name="quick_question" class="quick-question"><?= htmlspecialchars($qq) ?></button>
                            </form>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php else: ?>
                    <?php foreach ($chat_messages as $msg): ?>
                    <div class="chat-bubble <?= $msg['role'] ?>">
                        <div><?= nl2br(htmlspecialchars($msg['message'])) ?></div>
                        <?php if (!empty($msg['products'])): ?>
                            <?php foreach ($msg['products'] as $prod): ?>
                            <div class="product-card-mini">
                                <a href="product.php?slug=<?= htmlspecialchars($prod['slug'] ?? '') ?>">
                                    <?php $ai_img = !empty($prod['image']) ? BASE_URL . 'assets/uploads/products/' . $prod['image'] : ($prod['image_url'] ?: BASE_URL . 'assets/uploads/products/default.png'); ?>
                                    <img src="<?= htmlspecialchars($ai_img) ?>" alt="">
                                </a>
                                <div style="flex:1;">
                                    <a href="product.php?slug=<?= htmlspecialchars($prod['slug'] ?? '') ?>" class="text-decoration-none text-dark fw-semibold" style="font-size:0.9rem;"><?= htmlspecialchars($prod['name']) ?></a>
                                    <div class="d-flex align-items-center gap-2 mt-1">
                                        <span class="fw-bold text-primary">₹<?= number_format($prod['discount_price'] ?? $prod['price'], 0) ?></span>
                                        <?php if (($prod['discount_price'] ?? 0) > 0 && ($prod['discount_price'] ?? 0) < ($prod['price'] ?? 0)): ?>
                                            <del class="text-muted small">₹<?= number_format($prod['price'], 0) ?></del>
                                        <?php endif; ?>
                                    </div>
                                    <div class="d-flex align-items-center mt-1">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <i class="fas fa-star <?= $i <= ($prod['rating'] ?? 0) ? 'text-warning' : 'text-muted' ?>" style="font-size:0.7rem;"></i>
                                        <?php endfor; ?>
                                        <small class="text-muted ms-1">(<?= $prod['reviews_count'] ?? 0 ?>)</small>
                                    </div>
                                    <a href="product.php?slug=<?= htmlspecialchars($prod['slug'] ?? '') ?>" class="btn btn-primary btn-sm mt-1" style="font-size:0.75rem;padding:2px 10px;">View Product</a>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                    <div id="typingIndicator" class="chat-bubble ai" style="display:none;">
                        <div class="typing-indicator"><span></span><span></span><span></span></div>
                    </div>
                    <?php endif; ?>
                </div>

                <div class="chat-input-area">
                    <form id="chatForm" class="d-flex gap-2">
                        <input type="text" class="form-control" id="chatInput" placeholder="Ask about any product..." autocomplete="off" required>
                        <button type="submit" class="btn btn-primary px-4"><i class="fas fa-paper-plane"></i></button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const chatContainer = document.getElementById('chatContainer');
const chatForm = document.getElementById('chatForm');
const chatInput = document.getElementById('chatInput');
const typingIndicator = document.getElementById('typingIndicator');

chatContainer.scrollTop = chatContainer.scrollHeight;

chatForm.addEventListener('submit', function(e) {
    e.preventDefault();
    const msg = chatInput.value.trim();
    if (!msg) return;

    const bubble = document.createElement('div');
    bubble.className = 'chat-bubble user';
    bubble.innerHTML = '<div>' + escapeHtml(msg) + '</div>';
    chatContainer.appendChild(bubble);
    chatInput.value = '';
    chatContainer.scrollTop = chatContainer.scrollHeight;

    if (typingIndicator) {
        typingIndicator.style.display = 'block';
        chatContainer.scrollTop = chatContainer.scrollHeight;
    }

    fetch('ai_assistant.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'send_message=1&message=' + encodeURIComponent(msg)
    }).then(r => r.json()).then(data => {
        setTimeout(() => { location.reload(); }, 800);
    }).catch(() => {
        if (typingIndicator) typingIndicator.style.display = 'none';
    });
});

function escapeHtml(text) {
    const div = document.createElement('div');
    div.appendChild(document.createTextNode(text));
    return div.innerHTML;
}
</script>

<?php include 'includes/footer.php'; ?>
