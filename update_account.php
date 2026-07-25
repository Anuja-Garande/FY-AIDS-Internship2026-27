<?php

session_start();

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$conn = mysqli_connect(
    "localhost",
    "root",
    "",
    "inventory_management"
);

if (!$conn) {
    die("Database Connection Failed");
}

if ($_SERVER["REQUEST_METHOD"] != "POST") {
    header("Location: settings.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$username = trim($_POST['username']);
$email = trim($_POST['email']);

if (empty($username) || empty($email)) {
    die("All fields are required.");
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    die("Invalid email address.");
}

/* Check if username already exists */

$stmt = mysqli_prepare(
    $conn,
    "SELECT id FROM users WHERE username = ? AND id != ?"
);

mysqli_stmt_bind_param(
    $stmt,
    "si",
    $username,
    $user_id
);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) > 0) {
    die("Username already exists.");
}

mysqli_stmt_close($stmt);

/* Check if email already exists */

$stmt = mysqli_prepare(
    $conn,
    "SELECT id FROM users WHERE email = ? AND id != ?"
);

mysqli_stmt_bind_param(
    $stmt,
    "si",
    $email,
    $user_id
);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) > 0) {
    die("Email already exists.");
}

mysqli_stmt_close($stmt);

/* Update account */

$stmt = mysqli_prepare(
    $conn,
    "UPDATE users
     SET username = ?, email = ?
     WHERE id = ?"
);

mysqli_stmt_bind_param(
    $stmt,
    "ssi",
    $username,
    $email,
    $user_id
);

if (mysqli_stmt_execute($stmt)) {

    $_SESSION['username'] = $username;

    header("Location: settings.php?updated=1");
    exit();

} else {

    echo "Failed to update account.";

}

mysqli_stmt_close($stmt);

mysqli_close($conn);

?>