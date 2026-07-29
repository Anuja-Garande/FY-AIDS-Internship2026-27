<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: authentication/login.php"); exit(); }
require_once("config/db.php");
$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $conn->prepare("SELECT password FROM users WHERE id=?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();

    if ($row && password_verify($_POST['confirm_password'], $row['password'])) {
        foreach (['income','expenses','savings','budgets','notifications','settings','login_logs'] as $table) {
            $d = $conn->prepare("DELETE FROM $table WHERE user_id=?");
            $d->bind_param("i", $user_id);
            $d->execute();
        }
        $d = $conn->prepare("DELETE FROM users WHERE id=?");
        $d->bind_param("i", $user_id);
        $d->execute();

        session_unset();
        session_destroy();
        header("Location: landing.php?deleted=1");
        exit();
    } else {
        header("Location: security.php?error=wrongpassword");
        exit();
    }
}
?>
