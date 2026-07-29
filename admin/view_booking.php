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
    die("Invalid Booking ID");
}

$id = (int)$_GET['id'];

$sql = "SELECT
            bookings.*,
            users.full_name,
            users.email,
            rooms.room_name,
            rooms.room_type
        FROM bookings
        INNER JOIN users ON bookings.user_id = users.id
        INNER JOIN rooms ON bookings.room_id = rooms.id
        WHERE bookings.id='$id'";

$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) == 0) {
    die("Booking Not Found");
}

$row = mysqli_fetch_assoc($result);

$checkin = new DateTime($row['check_in']);
$checkout = new DateTime($row['check_out']);
$nights = $checkin->diff($checkout)->days;
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Booking Details</title>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">

<style>

*{
margin:0;
padding:0;
box-sizing:border-box;
font-family:'Segoe UI',sans-serif;
}

body{
background:#0f172a;
color:#fff;
padding:30px;
}

.container{
max-width:1350px;
margin:auto;
}

.header{
background:linear-gradient(135deg,#2563eb,#06b6d4);
padding:30px;
border-radius:18px;
display:flex;
justify-content:space-between;
align-items:center;
box-shadow:0 10px 30px rgba(0,0,0,.35);
margin-bottom:25px;
}

.header h1{
font-size:34px;
}

.header p{
margin-top:8px;
opacity:.9;
}

.logo{
width:80px;
height:80px;
border-radius:50%;
background:rgba(255,255,255,.15);
display:flex;
justify-content:center;
align-items:center;
font-size:34px;
}

.summary{
display:grid;
grid-template-columns:repeat(4,1fr);
gap:20px;
margin-bottom:25px;
}

.card{
background:#1e293b;
padding:22px;
border-radius:15px;
border:1px solid #334155;
transition:.3s;
}

.card:hover{
transform:translateY(-5px);
}

.card i{
font-size:28px;
color:#38bdf8;
margin-bottom:12px;
}

.card h4{
font-size:13px;
color:#94a3b8;
text-transform:uppercase;
}

.card h2{
margin-top:8px;
font-size:24px;
}

.details{
background:#1e293b;
padding:30px;
border-radius:18px;
border:1px solid #334155;
}

.details h2{
margin-bottom:20px;
font-size:28px;
}

table{
width:100%;
border-collapse:collapse;
}

table td{
padding:18px;
border-bottom:1px solid #334155;
font-size:17px;
}

table td:first-child{
width:35%;
font-weight:bold;
color:#cbd5e1;
}

.amount{
color:#22c55e;
font-size:22px;
font-weight:bold;
}

.status{
padding:8px 18px;
border-radius:25px;
font-weight:bold;
display:inline-block;
color:#fff;
}

.confirmed{
background:#16a34a;
}

.pending{
background:#f59e0b;
}

.cancelled{
background:#dc2626;
}
.buttons{
margin-top:30px;
display:flex;
justify-content:center;
gap:15px;
flex-wrap:wrap;
}

.btn{
padding:12px 24px;
border-radius:8px;
text-decoration:none;
font-weight:bold;
color:#fff;
transition:.3s;
}

.btn-back{
background:#2563eb;
}

.btn-print{
background:#16a34a;
}

.btn:hover{
transform:translateY(-3px);
opacity:.9;
}

@media(max-width:992px){

.summary{
grid-template-columns:repeat(2,1fr);
}

}

@media(max-width:768px){

body{
padding:15px;
}

.header{
flex-direction:column;
text-align:center;
gap:20px;
}

.summary{
grid-template-columns:1fr;
}

table,
tbody,
tr,
td{
display:block;
width:100%;
}

table td:first-child{
background:#273449;
font-weight:bold;
}

}

</style>

</head>

<body>

<div class="container">

<div class="header">

<div>

<h1>
<i class="fas fa-file-invoice"></i>
Booking Details
</h1>

<p>
Premium Hotel Booking Management
</p>

</div>

<div class="logo">
<i class="fas fa-hotel"></i>
</div>

</div>

<div class="summary">

<div class="card">
<i class="fas fa-user"></i>
<h4>Customer</h4>
<h2><?php echo htmlspecialchars($row['full_name']); ?></h2>
</div>

<div class="card">
<i class="fas fa-bed"></i>
<h4>Room</h4>
<h2><?php echo htmlspecialchars($row['room_name']); ?></h2>
</div>

<div class="card">
<i class="fas fa-calendar-days"></i>
<h4>Stay</h4>
<h2><?php echo $nights; ?> Nights</h2>
</div>

<div class="card">
<i class="fas fa-indian-rupee-sign"></i>
<h4>Amount</h4>
<h2 class="amount">
₹<?php echo number_format($row['total_amount']); ?>
</h2>
</div>

</div>

<div class="details">

<h2>
<i class="fas fa-circle-info"></i>
Booking Information
</h2>

<table>

<tr>
<td>Booking ID</td>
<td>#<?php echo $row['id']; ?></td>
</tr>

<tr>
<td>Customer Name</td>
<td><?php echo htmlspecialchars($row['full_name']); ?></td>
</tr>

<tr>
<td>Email Address</td>
<td><?php echo htmlspecialchars($row['email']); ?></td>
</tr>

<tr>
<td>Room Name</td>
<td><?php echo htmlspecialchars($row['room_name']); ?></td>
</tr>

<tr>
<td>Room Type</td>
<td><?php echo htmlspecialchars($row['room_type']); ?></td>
</tr>

<tr>
<td>Guests</td>
<td><?php echo $row['guests']; ?></td>
</tr>

<tr>
<td>Check In</td>
<td><?php echo date("d M Y",strtotime($row['check_in'])); ?></td>
</tr>

<tr>
<td>Check Out</td>
<td><?php echo date("d M Y",strtotime($row['check_out'])); ?></td>
</tr>

<tr>
<td>Total Nights</td>
<td><?php echo $nights; ?> Night<?php echo ($nights>1)?'s':''; ?></td>
</tr>

<tr>
<td>Total Amount</td>
<td class="amount">
₹<?php echo number_format($row['total_amount']); ?>
</td>
</tr>

<tr>

<td>Booking Status</td>

<td>

<?php

$status = strtolower($row['booking_status']);

if($status=="confirmed"){

echo '<span class="status confirmed">
<i class="fas fa-circle-check"></i>
Confirmed
</span>';

}elseif($status=="pending"){

echo '<span class="status pending">
<i class="fas fa-clock"></i>
Pending
</span>';

}else{

echo '<span class="status cancelled">
<i class="fas fa-circle-xmark"></i>
Cancelled
</span>';

}

?>

</td>

</tr>

</table>

<div class="buttons">

<a href="bookings.php" class="btn btn-back">
<i class="fas fa-arrow-left"></i>
&nbsp; Back to Bookings
</a>

<a href="#" onclick="window.print();" class="btn btn-print">
<i class="fas fa-print"></i>
&nbsp; Print Booking
</a>

</div>

</div>
</div>

</body>

</html>