<?php

$host = "localhost";
$user = "root";
$password = "";
$database = "hotel_booking";

$conn = mysqli_connect($host, $user, $password, $database);

if (!$conn) {
    die("Database Connection Failed: " . mysqli_connect_error());
}