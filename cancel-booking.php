<?php

session_start();
require_once("database/db.php");

if(!isset($_SESSION['user_id']))
{
    header("Location: auth/login.php");
    exit();
}

if(isset($_GET['id']))
{
    $booking_id = $_GET['id'];
    $user_id = $_SESSION['user_id'];

    $query = "UPDATE bookings
              SET booking_status='Cancelled'
              WHERE id='$booking_id'
              AND user_id='$user_id'";

    mysqli_query($conn, $query);

    header("Location: my-bookings.php");
    exit();
}

?>