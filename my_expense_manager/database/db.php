<?php
// =======================================
// Database Connection File
// =======================================

$servername = "localhost";
$username   = "root";
$password   = "";
$database   = "my_expense_manager";

// Create Connection
$conn = mysqli_connect($servername, $username, $password, $database);

// Check Connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Set Character Encoding
mysqli_set_charset($conn, "utf8");
?>