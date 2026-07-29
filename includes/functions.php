<?php
/**
 * Shared helper functions used across the site.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/** Escape output safely */
function h($str) {
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}

/** Redirect helper */
function redirect($path) {
    header("Location: $path");
    exit;
}

/** Is a normal user logged in? */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/** Is an admin logged in? */
function isAdminLoggedIn() {
    return isset($_SESSION['admin_id']);
}

/** Require a logged-in user, else redirect to login */
function requireLogin() {
    if (!isLoggedIn()) {
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
        redirect('/tourism-portal/login.php');
    }
}

/** Require an admin session, else redirect to admin login */
function requireAdmin() {
    if (!isAdminLoggedIn()) {
        redirect('/tourism-portal/admin/login.php');
    }
}

/** Generate a unique booking reference like TP-8F3K2A */
function generateBookingReference() {
    return 'TP-' . strtoupper(substr(bin2hex(random_bytes(4)), 0, 6));
}

/** Average rating for an item (destination/hotel/restaurant) */
function getAverageRating($pdo, $itemType, $itemId) {
    $stmt = $pdo->prepare("SELECT ROUND(AVG(rating),1) AS avg_rating, COUNT(*) AS total
                           FROM reviews WHERE item_type = ? AND item_id = ? AND status = 'Approved'");
    $stmt->execute([$itemType, $itemId]);
    return $stmt->fetch();
}

/** Star rating markup */
function renderStars($rating) {
    $rating = round($rating * 2) / 2; // nearest 0.5
    $full = floor($rating);
    $half = ($rating - $full) >= 0.5 ? 1 : 0;
    $empty = 5 - $full - $half;
    $html = str_repeat('<i class="bi bi-star-fill"></i>', $full);
    if ($half) $html .= '<i class="bi bi-star-half"></i>';
    $html .= str_repeat('<i class="bi bi-star"></i>', $empty);
    return $html;
}

/** Flash messages */
function setFlash($type, $message) {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}
function getFlash() {
    if (!empty($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/* =====================================================
   TRENDING NOW / RECENTLY VIEWED
   ===================================================== */

/** Call on destination-details.php to count a view + track in session */
function trackDestinationView($pdo, $destinationId) {
    // Increment global view counter (used for "Trending Now")
    $stmt = $pdo->prepare("UPDATE destinations SET views = views + 1 WHERE destination_id = ?");
    $stmt->execute([$destinationId]);

    // Track in session for "Recently Viewed" (max 8, most recent first, no duplicates)
    if (!isset($_SESSION['recently_viewed'])) $_SESSION['recently_viewed'] = [];
    $_SESSION['recently_viewed'] = array_diff($_SESSION['recently_viewed'], [$destinationId]);
    array_unshift($_SESSION['recently_viewed'], $destinationId);
    $_SESSION['recently_viewed'] = array_slice($_SESSION['recently_viewed'], 0, 8);
}

/** Top trending destinations by view count */
function getTrendingDestinations($pdo, $limit = 5) {
    $stmt = $pdo->prepare("SELECT d.*, s.name AS state_name FROM destinations d
                            JOIN states s ON s.state_id = d.state_id
                            ORDER BY d.views DESC, d.popularity DESC LIMIT ?");
    $stmt->bindValue(1, $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

/** Destinations the current visitor recently viewed, excluding one (e.g. current page) */
function getRecentlyViewed($pdo, $excludeId = null, $limit = 4) {
    if (empty($_SESSION['recently_viewed'])) return [];
    $ids = $_SESSION['recently_viewed'];
    if ($excludeId) $ids = array_diff($ids, [$excludeId]);
    $ids = array_slice($ids, 0, $limit);
    if (empty($ids)) return [];

    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $stmt = $pdo->prepare("SELECT d.*, s.name AS state_name FROM destinations d
                            JOIN states s ON s.state_id = d.state_id
                            WHERE d.destination_id IN ($placeholders)");
    $stmt->execute($ids);
    $rows = $stmt->fetchAll();

    // Re-order to match session order (most recently viewed first)
    $rowsById = [];
    foreach ($rows as $r) $rowsById[$r['destination_id']] = $r;
    $ordered = [];
    foreach ($ids as $id) if (isset($rowsById[$id])) $ordered[] = $rowsById[$id];
    return $ordered;
}

/* =====================================================
   LOGIN RATE LIMITING (brute-force protection)
   ===================================================== */

const MAX_LOGIN_ATTEMPTS = 5;
const LOCKOUT_MINUTES = 15;

/** Build a unique identifier per user+IP so one person's lockout doesn't block others */
function loginIdentifier($usernameOrEmail) {
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    return strtolower(trim($usernameOrEmail)) . '|' . $ip;
}

/** Returns remaining lockout minutes (0 if not locked) */
function isLoginLocked($pdo, $usernameOrEmail) {
    $id = loginIdentifier($usernameOrEmail);
    $stmt = $pdo->prepare("SELECT locked_until FROM login_attempts WHERE identifier = ?");
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    if ($row && $row['locked_until'] && strtotime($row['locked_until']) > time()) {
        return ceil((strtotime($row['locked_until']) - time()) / 60);
    }
    return 0;
}

/** Record a failed login attempt; locks the identifier after MAX_LOGIN_ATTEMPTS */
function registerFailedLogin($pdo, $usernameOrEmail) {
    $id = loginIdentifier($usernameOrEmail);
    $stmt = $pdo->prepare("SELECT * FROM login_attempts WHERE identifier = ?");
    $stmt->execute([$id]);
    $row = $stmt->fetch();

    if ($row) {
        $attempts = $row['attempts'] + 1;
        $lockedUntil = null;
        if ($attempts >= MAX_LOGIN_ATTEMPTS) {
            $lockedUntil = date('Y-m-d H:i:s', strtotime('+' . LOCKOUT_MINUTES . ' minutes'));
            $attempts = 0; // reset counter once locked
        }
        $upd = $pdo->prepare("UPDATE login_attempts SET attempts = ?, last_attempt = NOW(), locked_until = ? WHERE identifier = ?");
        $upd->execute([$attempts, $lockedUntil, $id]);
    } else {
        $ins = $pdo->prepare("INSERT INTO login_attempts (identifier, attempts) VALUES (?, 1)");
        $ins->execute([$id]);
    }
}

/** Clear failed attempts after a successful login */
function clearLoginAttempts($pdo, $usernameOrEmail) {
    $id = loginIdentifier($usernameOrEmail);
    $del = $pdo->prepare("DELETE FROM login_attempts WHERE identifier = ?");
    $del->execute([$id]);
}
