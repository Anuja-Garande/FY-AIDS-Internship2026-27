<?php
// C:\xampp\htdocs\NewProject\config\db_connect.php
// Database connection configuration using PDO for security and flexibility

$host = 'localhost';
$db   = 'tourist_portal';
$user = 'root';
$pass = ''; // Default XAMPP MySQL password is empty
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => true,
    PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    
    // Check if core tables exist. If database is empty, auto-import schema
    $stmt = $pdo->query("SHOW TABLES LIKE 'users'");
    $tables = $stmt->fetchAll();
    $stmt->closeCursor();
    
    if (count($tables) === 0) {
        auto_initialize_database($pdo);
    }
} catch (\PDOException $e) {
    // If database does not exist (Error 1049 Unknown database), create it and auto-import schema
    if ($e->getCode() == 1049 || strpos($e->getMessage(), '1049') !== false || strpos($e->getMessage(), 'Unknown database') !== false) {
        try {
            $serverDsn = "mysql:host=$host;charset=$charset";
            $serverPdo = new PDO($serverDsn, $user, $pass, $options);
            $serverPdo->exec("CREATE DATABASE IF NOT EXISTS `$db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            
            $pdo = new PDO($dsn, $user, $pass, $options);
            auto_initialize_database($pdo);
        } catch (\PDOException $e2) {
            die("Database auto-creation failed. Please ensure MySQL is running in XAMPP. Error: " . $e2->getMessage());
        }
    } else {
        die("Database connection failed. Please ensure MySQL is running in XAMPP. Error: " . $e->getMessage());
    }
}


/**
 * Auto-imports database.sql when running on a new computer
 */
function auto_initialize_database($pdo) {
    $sqlFiles = [
        __DIR__ . '/../database.sql'
    ];

    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0;");

    foreach ($sqlFiles as $sqlFile) {
        if (!file_exists($sqlFile)) continue;
        $sql = file_get_contents($sqlFile);
        if (empty(trim($sql))) continue;

        $lines = explode("\n", $sql);
        $query = '';
        
        foreach ($lines as $line) {
            $trimmed = trim($line);
            if (empty($trimmed) || strpos($trimmed, '--') === 0 || strpos($trimmed, '#') === 0) {
                continue;
            }
            $query .= $line . "\n";
            if (substr($trimmed, -1) === ';') {
                try {
                    $pdo->exec($query);
                } catch (\PDOException $ex) {
                    // Ignore minor non-fatal SQL notices during auto-import
                }
                $query = '';
            }
        }

        if (!empty(trim($query))) {
            try {
                $pdo->exec($query);
            } catch (\PDOException $ex) {}
        }
    }

    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");
}


function get_image_url($image) {
    $isAdmin = (strpos($_SERVER['PHP_SELF'] ?? '', '/admin/') !== false);
    $prefix = $isAdmin ? '../' : '';

    if (empty($image)) {
        return $prefix . 'assets/images/default_destination.jpg';
    }
    $image = trim($image);
    if (strpos($image, 'http://') === 0 || strpos($image, 'https://') === 0) {
        return $image;
    }
    if (strpos($image, 'assets/') === 0 || strpos($image, 'uploads/') === 0) {
        return $prefix . $image;
    }
    return $prefix . 'uploads/' . $image;
}

/**
 * Helper function to return a 100% unique, gender-matched portrait photo for every guide
 */
function get_guide_photo($photo, $id = 0, $name = '') {
    if (!empty($photo) && strpos($photo, 'default') === false && strpos($photo, 'placeholder') === false && strpos($photo, 'pravatar') === false) {
        return get_image_url($photo);
    }

    $femaleNames = ['maria', 'ananya', 'kavya', 'priya', 'meera', 'amara', 'elena', 'sunita', 'pooja', 'neha', 'chloe', 'sarah', 'jessica', 'emily', 'sophia', 'aisha', 'fatima', 'rachel', 'laura', 'anna', 'julia'];
    $first = strtolower(explode(' ', trim($name))[0] ?? '');
    
    $isFemale = in_array($first, $femaleNames) || (preg_match('/(a|i|iya|ee|ya)$/i', $first) && !in_array($first, ['tashi', 'kenji', 'zaid', 'pierre', 'jean', 'rohan', 'lucas', 'siddharth', 'aarav', 'rajesh', 'vikram', 'sunil', 'tariq']));

    $num = ($id % 90) + 1;
    if ($isFemale) {
        return "https://randomuser.me/api/portraits/women/{$num}.jpg";
    } else {
        return "https://randomuser.me/api/portraits/men/{$num}.jpg";
    }
}


/**
 * Helper function to create an in-app notification for a user
 */
function create_notification($pdo, $user_id, $message, $type = 'info') {
    if (empty($user_id) || empty($message)) return false;
    try {
        $stmt = $pdo->prepare("INSERT INTO notifications (user_id, message, type, is_read) VALUES (?, ?, ?, 0)");
        return $stmt->execute([$user_id, $message, $type]);
    } catch (\PDOException $e) {
        // Table might not exist yet before migration
        return false;
    }
}

/**
 * Helper function to get unread notifications count for a user
 */
function get_unread_notifications_count($pdo, $user_id) {
    if (empty($user_id)) return 0;
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
        $stmt->execute([$user_id]);
        return intval($stmt->fetchColumn());
    } catch (\PDOException $e) {
        return 0;
    }
}

/**
 * Helper function to fetch recent notifications for a user
 */
function get_user_notifications($pdo, $user_id, $limit = 10) {
    if (empty($user_id)) return [];
    try {
        $stmt = $pdo->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT " . intval($limit));
        $stmt->execute([$user_id]);
        return $stmt->fetchAll();
    } catch (\PDOException $e) {
        return [];
    }
}
