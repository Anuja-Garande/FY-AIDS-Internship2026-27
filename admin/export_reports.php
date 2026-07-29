<?php
session_start();
require_once("../database/db.php");

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != "admin") {
    die("Access Denied!");
}

$from = isset($_GET['from']) ? $_GET['from'] : "";
$to   = isset($_GET['to']) ? $_GET['to'] : "";

header("Content-Type: text/csv");
header("Content-Disposition: attachment; filename=reports.csv");

$output = fopen("php://output", "w");

fputcsv($output, array(
    "Booking ID",
    "User",
    "Room",
    "Check In",
    "Check Out",
    "Amount",
    "Status",
    "Created At"
));

$sql = "SELECT
            bookings.id,
            users.full_name,
            rooms.room_name,
            bookings.check_in,
            bookings.check_out,
            bookings.total_amount,
            bookings.booking_status,
            bookings.created_at
        FROM bookings
        INNER JOIN users ON bookings.user_id = users.id
        INNER JOIN rooms ON bookings.room_id = rooms.id";

if($from != "" && $to != ""){
    $sql .= " WHERE DATE(bookings.created_at) BETWEEN '$from' AND '$to'";
}

$sql .= " ORDER BY bookings.id DESC";

$result = mysqli_query($conn, $sql);

while($row = mysqli_fetch_assoc($result)){
    fputcsv($output, array(
        $row['id'],
        $row['full_name'],
        $row['room_name'],
        $row['check_in'],
        $row['check_out'],
        $row['total_amount'],
        $row['booking_status'],
        $row['created_at']
    ));
}

fclose($output);
exit();
?>