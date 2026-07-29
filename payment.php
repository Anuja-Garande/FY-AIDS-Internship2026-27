<?php

session_start();
require_once("database/db.php");

if(!isset($_SESSION['user_id'])){
    header("Location: auth/login.php");
    exit();
}

if(!isset($_GET['booking_id'])){
    die("Invalid Booking");
}

$booking_id = (int)$_GET['booking_id'];

$query = mysqli_query($conn,"
SELECT
bookings.*,
rooms.room_name,
rooms.room_number,
rooms.image
FROM bookings
INNER JOIN rooms
ON bookings.room_id = rooms.id
WHERE bookings.id='$booking_id'
");

if(mysqli_num_rows($query)==0){
    die("Booking Not Found");
}

$booking = mysqli_fetch_assoc($query);

if(isset($_POST['pay_now'])){

    $method = mysqli_real_escape_string(
        $conn,
        $_POST['payment_method']
    );

    mysqli_query($conn,"
    UPDATE bookings
    SET
    payment_status='Paid',
    payment_method='$method',
    booking_status='Confirmed'
    WHERE id='$booking_id'
    ");

    echo "<script>
    alert('Payment Successful');
    window.location='my-bookings.php';
    </script>";

    exit();

}

?>
<!DOCTYPE html>
<html>

<head>

<meta charset="UTF-8">

<meta
name="viewport"
content="width=device-width,initial-scale=1.0">

<title>Payment</title>

<link
rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

<style>

*{
margin:0;
padding:0;
box-sizing:border-box;
font-family:'Segoe UI',sans-serif;
}

body{

background:#eef4ff;
padding:40px;

}

.payment-box{

max-width:950px;
margin:auto;

background:#fff;

border-radius:20px;

overflow:hidden;

box-shadow:0 20px 50px rgba(0,0,0,.12);

display:grid;

grid-template-columns:1fr 1fr;

}

.left{

padding:35px;

background:#2563eb;

color:#fff;

}

.right{

padding:35px;

}

.room-image{

width:100%;
height:250px;
object-fit:cover;
border-radius:15px;
margin-bottom:20px;

}

.left h2{

margin-bottom:15px;

font-size:28px;

}

.info{

margin:15px 0;

font-size:17px;

}

.info i{

width:28px;

}

.price{

margin-top:25px;

padding:18px;

background:rgba(255,255,255,.15);

border-radius:12px;

font-size:26px;

font-weight:bold;

text-align:center;

}

.right h2{

margin-bottom:25px;

color:#222;

}

.payment-method{

margin-bottom:18px;

padding:15px;

border:2px solid #ddd;

border-radius:12px;

cursor:pointer;

transition:.3s;

display:flex;

align-items:center;

gap:12px;

}

.payment-method:hover{

border-color:#2563eb;

background:#f7fbff;

}

.payment-method input{

transform:scale(1.2);

}

.payment-method i{

font-size:22px;

color:#2563eb;

}

button{

width:100%;

padding:16px;

border:none;

background:#2563eb;

color:#fff;

font-size:18px;

border-radius:10px;

cursor:pointer;

margin-top:25px;

transition:.3s;

}

button:hover{

background:#1d4ed8;

}

</style>

</head>

<body>

<div class="payment-box">

<div class="left">

<img
src="assets/images/<?php echo $booking['image']; ?>"
class="room-image"
alt="Room Image">

<h2><?php echo $booking['room_name']; ?></h2>

<div class="info">
<i class="fa-solid fa-door-open"></i>
Room No :
<?php echo $booking['room_number']; ?>
</div>

<div class="info">
<i class="fa-solid fa-calendar-check"></i>
Check In :
<?php echo $booking['check_in']; ?>
</div>

<div class="info">
<i class="fa-solid fa-calendar-xmark"></i>
Check Out :
<?php echo $booking['check_out']; ?>
</div>

<div class="info">
<i class="fa-solid fa-user-group"></i>
Guests :
<?php echo $booking['guests']; ?>
</div>

<div class="price">
₹ <?php echo number_format($booking['total_amount']); ?>
</div>

</div>

<div class="right">

<h2>Select Payment Method</h2>

<form method="POST">

<label class="payment-method">

<input
type="radio"
name="payment_method"
value="UPI"
checked>

<i class="fa-brands fa-google-pay"></i>

<div>

<b>UPI Payment</b><br>

<small>Google Pay / PhonePe / Paytm</small>

</div>

</label>

<label class="payment-method">

<input
type="radio"
name="payment_method"
value="Credit Card">

<i class="fa-regular fa-credit-card"></i>

<div>

<b>Credit Card</b><br>

<small>Visa / MasterCard</small>

</div>

</label>

<label class="payment-method">

<input
type="radio"
name="payment_method"
value="Debit Card">

<i class="fa-solid fa-credit-card"></i>

<div>

<b>Debit Card</b><br>

<small>All Indian Banks</small>

</div>

</label>

<label class="payment-method">

<input
type="radio"
name="payment_method"
value="Net Banking">

<i class="fa-solid fa-building-columns"></i>

<div>

<b>Net Banking</b><br>

<small>Secure Bank Transfer</small>

</div>

</label>

<button
type="submit"
name="pay_now">

<i class="fa-solid fa-lock"></i>

Pay Now

</button>

</form>

</div>

</div>

<style>

@media(max-width:768px){

.payment-box{

grid-template-columns:1fr;

}

.left,
.right{

padding:25px;

}

.left h2{

font-size:24px;

}

.price{

font-size:22px;

}

button{

font-size:16px;

padding:14px;

}

}

</style>

</body>

</html>