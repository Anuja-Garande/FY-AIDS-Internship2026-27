<?php
/**
 * Database connection (PDO)
 * ---------------------------------------------------------
 * Update the values below to match your XAMPP / MySQL setup.
 * Default XAMPP settings: host=localhost, user=root, password="" (empty)
 */

define('DB_HOST', 'localhost');
define('DB_NAME', 'tourism_db');
define('DB_USER', 'root');
define('DB_PASS', '');

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
} catch (PDOException $e) {
    die('Database connection failed. Please check config/db.php and make sure the "tourism_db" '
        . 'database has been imported in phpMyAdmin. Error: ' . $e->getMessage());
}
