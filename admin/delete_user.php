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
    die("User ID Missing!");
}

$id = $_GET['id'];

/* Admin account delete hou naye */
$check = mysqli_query($conn, "SELECT role FROM users WHERE id='$id'");
$user = mysqli_fetch_assoc($check);

if ($user['role'] == 'admin') {
    echo "<script>
    alert('Admin account cannot be deleted.');
    window.location='users.php';
    </script>";
    exit();
}

// First delete all bookings of this user
mysqli_query($conn, "DELETE FROM bookings WHERE user_id='$id'");

// Then delete the user
$delete = mysqli_query($conn, "DELETE FROM users WHERE id='$id'");

if($delete){

    echo "<script>
    alert('User Deleted Successfully');
    window.location='users.php';
    </script>";

}else{

    echo "<script>
    alert('Unable to delete user.');
    window.location='users.php';
    </script>";

}
?>