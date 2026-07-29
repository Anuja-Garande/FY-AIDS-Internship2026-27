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

    case 'submit':
        if (!isLoggedIn()) {
            echo json_encode(['success' => false, 'message' => 'Please login to submit a review.', 'require_login' => true]);
            exit;
        }

        $product_id = (int)($_POST['product_id'] ?? 0);
        $rating = (int)($_POST['rating'] ?? 0);
        $title = trim($_POST['title'] ?? '');
        $comment = trim($_POST['comment'] ?? '');

        if ($product_id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid product.']);
            exit;
        }

        if ($rating < 1 || $rating > 5) {
            echo json_encode(['success' => false, 'message' => 'Rating must be between 1 and 5.']);
            exit;
        }

        if (empty($comment)) {
            echo json_encode(['success' => false, 'message' => 'Please write a review comment.']);
            exit;
        }

        $product = $db->fetch("SELECT id, name FROM products WHERE id = ? AND status = 1", [$product_id]);
        if (!$product) {
            echo json_encode(['success' => false, 'message' => 'Product not found.']);
            exit;
        }

        $user_id = $_SESSION['user_id'];
        $existing = $db->fetch(
            "SELECT id FROM reviews WHERE user_id = ? AND product_id = ?",
            [$user_id, $product_id]
        );
        if ($existing) {
            echo json_encode(['success' => false, 'message' => 'You have already reviewed this product. You can edit your existing review.']);
            exit;
        }

        $review_id = $db->insert(
            "INSERT INTO reviews (user_id, product_id, rating, title, comment, is_approved, created_at) VALUES (?, ?, ?, ?, ?, 0, NOW())",
            [$user_id, $product_id, $rating, $title ?: null, $comment]
        );

        if ($review_id) {
            $avg = $db->fetch(
                "SELECT ROUND(AVG(rating), 2) AS avg_rating, COUNT(*) AS total_reviews FROM reviews WHERE product_id = ? AND is_approved = 1",
                [$product_id]
            );
            $db->update(
                "UPDATE products SET rating = ?, reviews_count = ? WHERE id = ?",
                [$avg['avg_rating'] ?? 0, $avg['total_reviews'] ?? 0, $product_id]
            );

            setNotification($user_id, 'Review Submitted', 'Your review for "' . $product['name'] . '" has been submitted and is pending approval.', 'success');

            echo json_encode([
                'success' => true,
                'message' => 'Review submitted successfully! It will be visible after approval.',
                'review_id' => $review_id
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to submit review. Please try again.']);
        }
        break;

    case 'get_reviews':
        $product_id = (int)($_POST['product_id'] ?? 0);
        $page = max(1, (int)($_POST['page'] ?? 1));
        $per_page = 5;

        if ($product_id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid product.']);
            exit;
        }

        $total = $db->fetch(
            "SELECT COUNT(*) AS cnt FROM reviews WHERE product_id = ? AND is_approved = 1",
            [$product_id]
        );
        $total_reviews = $total ? (int)$total['cnt'] : 0;
        $total_pages = max(1, ceil($total_reviews / $per_page));
        $page = min($page, $total_pages);
        $offset = ($page - 1) * $per_page;

        $reviews = $db->fetchAll(
            "SELECT r.*, u.name AS user_name, u.avatar AS user_avatar
             FROM reviews r
             LEFT JOIN users u ON r.user_id = u.id
             WHERE r.product_id = ? AND r.is_approved = 1
             ORDER BY r.created_at DESC
             LIMIT ? OFFSET ?",
            [$product_id, $per_page, $offset]
        );

        $rating_dist = [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0];
        $dist_rows = $db->fetchAll(
            "SELECT rating, COUNT(*) AS cnt FROM reviews WHERE product_id = ? AND is_approved = 1 GROUP BY rating",
            [$product_id]
        );
        foreach ($dist_rows as $dr) {
            $rating_dist[(int)$dr['rating']] = (int)$dr['cnt'];
        }

        $avg_row = $db->fetch(
            "SELECT ROUND(AVG(rating), 1) AS avg_rating FROM reviews WHERE product_id = ? AND is_approved = 1",
            [$product_id]
        );

        $results = [];
        foreach ($reviews as $r) {
            $results[] = [
                'id'         => (int)$r['id'],
                'user_name'  => $r['user_name'] ?? 'Anonymous',
                'user_avatar' => $r['user_avatar'] ?? null,
                'rating'     => (int)$r['rating'],
                'title'      => $r['title'] ?? '',
                'comment'    => $r['comment'] ?? '',
                'admin_reply' => $r['admin_reply'] ?? null,
                'created_at' => $r['created_at'],
                'time_ago'   => timeAgo($r['created_at'])
            ];
        }

        echo json_encode([
            'success'       => true,
            'reviews'       => $results,
            'total_reviews' => $total_reviews,
            'avg_rating'    => (float)($avg_row['avg_rating'] ?? 0),
            'rating_distribution' => $rating_dist,
            'pagination'    => [
                'current_page' => $page,
                'total_pages'  => $total_pages,
                'has_prev'     => $page > 1,
                'has_next'     => $page < $total_pages
            ]
        ]);
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action.']);
        break;
}
