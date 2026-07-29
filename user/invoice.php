<?php

session_start();
require_once("../database/db.php");

if(!isset($_SESSION['user_id'])){
    header("Location: ../auth/login.php");
    exit();
}

if(!isset($_GET['booking_id'])){
    die("Invalid Booking");
}

$booking_id = (int)$_GET['booking_id'];

$query = mysqli_query($conn, "

SELECT

bookings.*,

users.full_name,

users.email,

rooms.room_name,

rooms.room_number

FROM bookings

INNER JOIN users
ON bookings.user_id = users.id

INNER JOIN rooms
ON bookings.room_id = rooms.id

WHERE bookings.id='$booking_id'

LIMIT 1

");

if(mysqli_num_rows($query)==0){

die("Invoice Not Found");

}

$row = mysqli_fetch_assoc($query);

?>
<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta
name="viewport"
content="width=device-width, initial-scale=1.0">

<title>Invoice</title>

<link
rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

<style>

*{

margin:0;
padding:0;
box-sizing:border-box;
font-family:Segoe UI,sans-serif;

}

body{

background:#f4f7fb;
padding:40px;

}

.invoice{

max-width:900px;
margin:auto;

background:#fff;

border-radius:15px;

padding:40px;

box-shadow:0 15px 40px rgba(0,0,0,.12);

}

.header{

display:flex;

justify-content:space-between;

align-items:center;

margin-bottom:35px;

padding-bottom:20px;

border-bottom:2px dashed #ddd;

}

.logo{

font-size:32px;

font-weight:bold;

color:#1e40af;

}

.invoice-title{

text-align:right;

}

.invoice-title h2{

color:#111;

margin-bottom:8px;

}

.invoice-title p{

color:#666;
font-size:15px;

}

.info-grid{

display:grid;

grid-template-columns:1fr 1fr;

gap:30px;

margin-bottom:35px;

}

.card{

background:#f8fafc;

padding:20px;

border-radius:12px;

border:1px solid #e5e7eb;

}

.card h3{

margin-bottom:15px;

color:#1e40af;

font-size:20px;

}

.card p{

margin:10px 0;

font-size:16px;

color:#333;

line-height:1.6;

}

.amount-box{

margin-top:25px;

padding:20px;

background:#1e40af;

color:#fff;

border-radius:12px;

font-size:28px;

font-weight:bold;

text-align:center;

}

.buttons{

margin-top:35px;

display:flex;

gap:15px;

justify-content:center;

}

.btn{

padding:14px 28px;

border-radius:10px;

text-decoration:none;

font-weight:600;

color:#fff;

transition:.3s;

}

.print{

background:#16a34a;

}

.back{

background:#2563eb;

}

.btn:hover{

opacity:.9;

}

</style>

</head>

<body>

<div class="invoice">

<div class="header">

<div class="logo">

🏨 Hotel Booking

</div>

<div class="invoice-title">

<h2>INVOICE</h2>

<p>

Invoice No :
<strong>

INV-<?php echo str_pad($row['id'],5,"0",STR_PAD_LEFT); ?>

</strong>

</p>

</div>

</div>

<div class="info-grid">

<div class="card">

<h3>Customer Details</h3>

<p>

<strong>Name :</strong>

<?php echo htmlspecialchars($row['full_name']); ?>

</p>

<p>

<strong>Email :</strong>

<?php echo htmlspecialchars($row['email']); ?>

</p>

<p>

<strong>Booking Date :</strong>

<?php echo date("d M Y",strtotime($row['created_at'])); ?>

</p>

</div>

<div class="card">

<h3>Booking Details</h3>

<p>

<strong>Room :</strong>

<?php echo htmlspecialchars($row['room_name']); ?>

</p>

<p>

<strong>Room Number :</strong>

<?php echo htmlspecialchars($row['room_number']); ?>

</p>

<p>

<strong>Check In :</strong>

<?php echo date("d M Y",strtotime($row['check_in'])); ?>

</p>

<p>

<strong>Check Out :</strong>

<?php echo date("d M Y",strtotime($row['check_out'])); ?>

</p>

<p>

<strong>Guests :</strong>

<?php echo $row['guests']; ?>

</p>

<p>

<strong>Payment Method :</strong>

<?php echo htmlspecialchars($row['payment_method']); ?>

</p>

<p>

<strong>Payment Status :</strong>

<span style="color:green;font-weight:bold;">

<?php echo $row['payment_status']; ?>

</span>

</p>

<p>

<strong>Booking Status :</strong>

<span style="color:#2563eb;font-weight:bold;">

<?php echo $row['booking_status']; ?>

</span>

</p>

</div>

</div>

<div class="amount-box">

Total Amount :

₹ <?php echo number_format($row['total_amount']); ?>

</div>

<div class="buttons">

<a
href="#"
onclick="window.print();return false;"
class="btn print">

<i class="fa-solid fa-print"></i>

Print Invoice

</a>

<a
href="../my-bookings.php"
class="btn back">

<i class="fa-solid fa-arrow-left"></i>

Back

</a>

</div>

<p style="margin-top:35px;
text-align:center;
font-size:16px;
color:#666;
line-height:28px;">

Thank You For Staying With Us ❤️

<br>

We Hope To Welcome You Again Soon.

</p>

<hr style="margin:35px 0;">

<p style="text-align:center;
color:#999;
font-size:14px;">

© <?php echo date("Y"); ?>

Hotel Booking System

All Rights Reserved.

</p>

</div>

<style>

@media(max-width:768px){

body{

padding:15px;

}

.invoice{

padding:20px;

}

.header{

flex-direction:column;

text-align:center;

gap:20px;

}

.invoice-title{

text-align:center;

}

.info-grid{

grid-template-columns:1fr;

}

.amount-box{

font-size:22px;

}

.buttons{

flex-direction:column;

}

.btn{

width:100%;

text-align:center;

}

}

@media print{

.buttons{

display:none;

}

body{

background:#fff;

padding:0;

}

.invoice{

box-shadow:none;

border-radius:0;

max-width:100%;

}

}

</style>

</body>

</html>