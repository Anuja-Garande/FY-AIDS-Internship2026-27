<?php
session_start();
require_once("../database/db.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

if ($_SESSION['role'] != "admin") {
    die("Access Denied!");
}

if(isset($_GET['id'])){

    $id = intval($_GET['id']);

    $sql = "UPDATE bookings
            SET booking_status='Cancelled'
            WHERE id='$id'";

    mysqli_query($conn, $sql);
}

header("Location: bookings.php");
exit();
?>