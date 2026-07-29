<?php
/*
  Database connection for NeoFinance
  Update these 4 values to match your local MySQL (XAMPP/WAMP) setup.
*/
$host    = "localhost";
$dbuser  = "root";
$dbpass  = "";
$dbname  = "expense_tracker";

$conn = new mysqli($host, $dbuser, $dbpass, $dbname);

if ($conn->connect_error) {
    die("Connection Failed: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");
?>
