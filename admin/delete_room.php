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

if (!isset($_GET['id'])) {
    die("Room ID Missing!");
}

$id = $_GET['id'];

// First delete all bookings of this room
mysqli_query($conn, "DELETE FROM bookings WHERE room_id='$id'");

// Then delete the room
$delete = mysqli_query($conn, "DELETE FROM rooms WHERE id='$id'");

if($delete){

    echo "<script>
    alert('Room Deleted Successfully');
    window.location='rooms.php';
    </script>";

}else{

    echo "<script>
    alert('Unable to delete room.');
    window.location='rooms.php';
    </script>";

}
?>