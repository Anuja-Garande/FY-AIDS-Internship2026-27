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

    case 'subscribe':
        $email = trim($_POST['email'] ?? '');

        if (empty($email)) {
            echo json_encode(['success' => false, 'message' => 'Please enter your email address.']);
            exit;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['success' => false, 'message' => 'Please enter a valid email address.']);
            exit;
        }

        $db->query("CREATE TABLE IF NOT EXISTS newsletter_subscribers (
            id INT UNSIGNED NOT NULL AUTO_INCREMENT,
            email VARCHAR(100) NOT NULL,
            user_id INT UNSIGNED DEFAULT NULL,
            status ENUM('active','unsubscribed') NOT NULL DEFAULT 'active',
            subscribed_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            unsubscribed_at TIMESTAMP DEFAULT NULL,
            PRIMARY KEY (id),
            UNIQUE INDEX newsletter_email_unique (email),
            INDEX newsletter_status_index (status)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        $existing = $db->fetch("SELECT id, status FROM newsletter_subscribers WHERE email = ?", [$email]);

        if ($existing) {
            if ($existing['status'] === 'active') {
                echo json_encode(['success' => false, 'message' => 'This email is already subscribed to our newsletter.']);
                exit;
            } else {
                $db->update("UPDATE newsletter_subscribers SET status = 'active', subscribed_at = NOW(), unsubscribed_at = NULL WHERE id = ?", [$existing['id']]);
                echo json_encode(['success' => true, 'message' => 'Welcome back! You have been re-subscribed to our newsletter.']);
                exit;
            }
        }

        $user_id = isLoggedIn() ? $_SESSION['user_id'] : null;

        $db->insert(
            "INSERT INTO newsletter_subscribers (email, user_id, status, subscribed_at) VALUES (?, ?, 'active', NOW())",
            [$email, $user_id]
        );

        echo json_encode([
            'success' => true,
            'message' => 'Successfully subscribed to our newsletter! You\'ll receive updates on the latest deals and products.'
        ]);
        break;

    case 'unsubscribe':
        $email = trim($_POST['email'] ?? '');
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['success' => false, 'message' => 'Please enter a valid email address.']);
            exit;
        }

        $existing = $db->fetch("SELECT id, status FROM newsletter_subscribers WHERE email = ?", [$email]);
        if (!$existing || $existing['status'] === 'unsubscribed') {
            echo json_encode(['success' => false, 'message' => 'This email is not subscribed to our newsletter.']);
            exit;
        }

        $db->update("UPDATE newsletter_subscribers SET status = 'unsubscribed', unsubscribed_at = NOW() WHERE id = ?", [$existing['id']]);

        echo json_encode(['success' => true, 'message' => 'You have been unsubscribed from our newsletter.']);
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action.']);
        break;
}
