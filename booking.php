<?php

session_start();
require_once("database/db.php");

/* ================= ROOM ID CHECK ================= */

if (!isset($_GET['room_id']) || !is_numeric($_GET['room_id'])) {
    die("Invalid Room ID");
}

$room_id = (int) $_GET['room_id'];

/* ================= GET ROOM ================= */

$query = "SELECT * FROM rooms WHERE id = '$room_id'";
$result = mysqli_query($conn, $query);

if (!$result || mysqli_num_rows($result) == 0) {
    die("Room Not Found");
}

$room = mysqli_fetch_assoc($result);

/* ================= SAME ROOM TYPE ================= */

$room_type = mysqli_real_escape_string($conn, $room['room_type']);

$same_rooms = mysqli_query($conn, "
SELECT *
FROM rooms
WHERE room_type='$room_type'
AND id != '$room_id'
ORDER BY room_number ASC
LIMIT 4
");

/* ================= BOOK ROOM ================= */

if (isset($_POST['book_room'])) {

    /* LOGIN CHECK */

    if (!isset($_SESSION['user_id'])) {

        echo "<script>
            alert('Please login first to book a room.');
            window.location='auth/login.php';
        </script>";

        exit();
    }

    $user_id = (int) $_SESSION['user_id'];

    $check_in = $_POST['checkin'];
    $check_out = $_POST['checkout'];
    $guests = (int) $_POST['guests'];

    /* ================= VALIDATION ================= */

    if (empty($check_in) || empty($check_out) || $guests < 1) {

        echo "<script>
            alert('Please fill all booking details.');
        </script>";

    }

    elseif ($check_in < date('Y-m-d')) {

    echo "<script>
        alert('You cannot book for a past date.');
    </script>";

}

    elseif ($check_out <= $check_in) {

        echo "<script>
            alert('Check-out date must be after check-in date.');
        </script>";

    }

    elseif ($guests > $room['capacity']) {

        echo "<script>
            alert('Maximum capacity for this room is ".$room['capacity']." guests.');
        </script>";

    }

    else {

        /* ================= CALCULATE NIGHTS ================= */

        $start_date = new DateTime($check_in);
        $end_date = new DateTime($check_out);

        $difference = $start_date->diff($end_date);

        $total_nights = $difference->days;

        $total_amount = $room['price'] * $total_nights;

        $check_booking = mysqli_query($conn, "
SELECT *
FROM bookings
WHERE room_id = '$room_id'
AND booking_status='Confirmed'
AND '$check_in' < check_out
AND '$check_out' > check_in
");

if(mysqli_num_rows($check_booking) > 0){

   $recommended_rooms = mysqli_query($conn,"
SELECT *
FROM rooms
WHERE id != '$room_id'
AND id NOT IN(

SELECT room_id
FROM bookings
WHERE booking_status='Confirmed'
AND '$check_in' < check_out
AND '$check_out' > check_in

)

ORDER BY best_seller DESC, price ASC
LIMIT 3
");

    $booking_error = true;

}
else{

        /* ================= INSERT BOOKING ================= */

        $insert_query = "INSERT INTO bookings
        (
            user_id,
            room_id,
            check_in,
            check_out,
            guests,
            total_amount
        )
        VALUES
        (
            '$user_id',
            '$room_id',
            '$check_in',
            '$check_out',
            '$guests',
            '$total_amount'
        )";

       if (mysqli_query($conn, $insert_query)) {

    $booking_id = mysqli_insert_id($conn);

    echo "<script>
        alert('Booking Created Successfully!');
        window.location='payment.php?booking_id=".$booking_id."';
    </script>";

    exit();

} else {

    echo "<script>
        alert('Booking Failed. Please try again.');
    </script>";

}
    }
}
}

?>
<!DOCTYPE html>
<html>
<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Book Room</title>

<link rel="stylesheet" href="assets/css/booking.css">

<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

</head>
<body>
    <!-- ================= NAVBAR ================= -->

<nav class="navbar">

<div class="logo">
🏨 Hotel Booking
</div>

<ul class="menu">
<li><a href="index.php">Home</a></li>
<li><a href="rooms.php">Rooms</a></li>
<li><a href="my-bookings.php">My Bookings</a></li>
<li><a href="contact.php">Contact</a></li>
</ul>

<div class="nav-buttons">
<a href="rooms.php" class="back-btn">
<i class="fa-solid fa-arrow-left"></i>
Back
</a>
</div>

</nav>

<section class="booking-section">

<div class="booking-container">

<!-- LEFT SIDE : ROOM DETAILS -->

<div class="room-details-card">

    <div class="room-image-box">

        <?php if($room['best_seller'] == 1){ ?>
            <span class="best-seller">
                <i class="fa-solid fa-fire"></i> Best Seller
            </span>
        <?php } ?>

        <img src="assets/images/<?php echo htmlspecialchars($room['image']); ?>"
             alt="<?php echo htmlspecialchars($room['room_name']); ?>">

    </div>

    <div class="room-details-content">

        <div class="room-title-row">

            <div>
               <span class="small-title">YOUR SELECTED ROOM</span>

<h4 style="color:#2563eb; margin:8px 0;">
    Room No: <?php echo htmlspecialchars($room['room_number']); ?>
</h4>

<h1>
    <?php echo htmlspecialchars($room['room_name']); ?>
</h1>
            </div>

            <div class="rating">
                <i class="fa-solid fa-star"></i>
                4.9
            </div>

        </div>

        <p class="room-description">
            <?php echo htmlspecialchars($room['description']); ?>
        </p>

        <div class="room-features">

            <div>
                <i class="fa-solid fa-hotel"></i>
                <span>
                    <small>Room Type</small>
                    <?php echo htmlspecialchars($room['room_type']); ?>
                </span>
            </div>

            <div>
                <i class="fa-solid fa-users"></i>
                <span>
                    <small>Capacity</small>
                    <?php echo $room['capacity']; ?> Guests
                </span>
            </div>

            <div>
                <i class="fa-solid fa-wifi"></i>
                <span>
                    <small>Internet</small>
                    Free WiFi
                </span>
            </div>

            <div>
                <i class="fa-solid fa-mug-hot"></i>
                <span>
                    <small>Included</small>
                    Breakfast
                </span>
            </div>

        </div>

    </div>

</div>


<!-- RIGHT SIDE : BOOKING CARD -->

<div class="booking-card">

    <div class="price-box">

        <span>Starting from</span>

        <h2>
            ₹<?php echo number_format($room['price']); ?>
            <small>/ night</small>
        </h2>

    </div>

    <h3>Reserve Your Stay</h3>

    <p class="booking-subtitle">
        Enter your stay details to confirm your reservation.
    </p>

    <form action="" method="POST">

        <div class="form-group">

            <label>
                <i class="fa-regular fa-calendar"></i>
                Check In
            </label>

            <input
type="date"
name="checkin"
min="<?php echo date('Y-m-d'); ?>"
required>

        </div>

        <div class="form-group">

            <label>
                <i class="fa-regular fa-calendar-check"></i>
                Check Out
            </label>

            <input
type="date"
name="checkout"
min="<?php echo date('Y-m-d'); ?>"
required>

        </div>

        <div class="form-group">

            <label>
                <i class="fa-solid fa-users"></i>
                Guests
            </label>

            <input
                type="number"
                name="guests"
                min="1"
                max="<?php echo $room['capacity']; ?>"
                value="1"
                required
            >

        </div>

        <button type="submit" name="book_room" class="confirm-btn">

            <i class="fa-solid fa-calendar-check"></i>

            Confirm Booking

        </button>

    </form>

    <div class="booking-benefits">

        <p>
            <i class="fa-solid fa-shield-halved"></i>
            Secure Booking
        </p>

        <p>
            <i class="fa-solid fa-circle-check"></i>
            Instant Confirmation
        </p>

    </div>

</div>
</div>
<?php if(isset($booking_error)){ ?>

<style>

.booking-error{

max-width:1200px;
margin:40px auto;
padding:35px;
background:#fff;
border-radius:20px;
box-shadow:0 15px 40px rgba(0,0,0,.12);

}

.booking-error h2{

color:#dc2626;
font-size:32px;
margin-bottom:10px;

}

.booking-error p{

color:#555;
font-size:17px;
margin-bottom:30px;

}

.recommended-grid{

display:grid;
grid-template-columns:repeat(auto-fit,minmax(280px,1fr));
gap:25px;

}

.recommended-card{

background:#fff;
border-radius:20px;
overflow:hidden;
box-shadow:0 15px 35px rgba(0,0,0,.10);
transition:.35s;
border:1px solid #e5e7eb;

}

.recommended-card:hover{

transform:translateY(-10px) scale(1.02);
box-shadow:0 25px 50px rgba(37,99,235,.25);

}

.recommended-card img{

width:100%;
height:220px;
object-fit:cover;

}

.recommended-content{

padding:20px;

}

.recommended-content h3{

margin-bottom:10px;

}

.recommended-content p{

margin-bottom:15px;
color:#666;

}

.price{

font-size:34px;

font-weight:700;

color:#0f766e;

margin:15px 0;

}

.price span{

font-size:16px;

font-weight:500;

color:#666;

}

.starting{

font-size:13px;

color:#888;

margin-bottom:5px;

text-transform:uppercase;

letter-spacing:1px;

}

.book-btn{

display:block;
width:100%;
text-align:center;
padding:14px;
background:linear-gradient(135deg,#2563eb,#1d4ed8);
color:#fff;
text-decoration:none;
border-radius:12px;
font-weight:600;
transition:.3s;

}

.book-btn:hover{

background:linear-gradient(135deg,#1d4ed8,#1e40af);
transform:translateY(-2px);

}

.booking-error-section{

background:#ffffff;

border-radius:22px;

padding:35px;

margin-top:50px;

box-shadow:0 20px 50px rgba(0,0,0,.08);

border-left:7px solid #ef4444;

animation:fadeUp .6s ease;

}

.error-top{

display:flex;

align-items:center;

gap:20px;

margin-bottom:25px;

}

.error-icon{

width:80px;

height:80px;

border-radius:50%;

background:#fff5f5;

display:flex;

justify-content:center;

align-items:center;

font-size:42px;

}

.error-top h2{

font-size:40px;

margin-bottom:10px;

color:#e11d48;

font-weight:700;

}

.error-top p{

font-size:18px;

color:#555;

margin:5px 0;

}

.sub-text{

color:#16a34a;

font-weight:600;

}

.recommend-title{

font-size:28px;

font-weight:700;

margin:35px 0 20px;

color:#1e3a8a;

}

@keyframes fadeUp{

from{

opacity:0;

transform:translateY(40px);

}

to{

opacity:1;

transform:translateY(0);

}

}

.available-badge{

display:inline-flex;

align-items:center;

gap:8px;

padding:8px 18px;

background:#16a34a;

color:#fff;

border-radius:50px;

font-weight:600;

}

.dot{

width:10px;

height:10px;

background:#9fff9f;

border-radius:50%;

animation:pulse 1.5s infinite;

}

@keyframes pulse{

0%{

transform:scale(1);

}

50%{

transform:scale(1.5);

}

100%{

transform:scale(1);

}

}

</style>

<div class="booking-error-section" id="booking-error">

<div class="error-top">

    <div class="error-icon">
        ⚠️
    </div>

    <div>

        <h2>Room Unavailable</h2>

        <p>
            This room is already booked for
            <strong>
                <?= date('d M Y', strtotime($check_in)); ?>
                →
                <?= date('d M Y', strtotime($check_out)); ?>
            </strong>
        </p>

        <p class="sub-text">
            Don't worry! We found similar rooms available for your stay.
        </p>

    </div>

</div>

<h3 class="recommend-title">
    ⭐ Recommended Rooms For You
</h3>



<div class="recommended-grid">

<?php while($rec=mysqli_fetch_assoc($recommended_rooms)){ ?>

<div class="recommended-card">

<img src="assets/images/<?php echo htmlspecialchars($rec['image']); ?>">

<div class="recommended-content">

<h3>

<?php echo htmlspecialchars($rec['room_name']); ?>

</h3>

<span style="
display:inline-block;
background:#16a34a;
color:#fff;
padding:5px 12px;
border-radius:20px;
font-size:13px;
margin:10px 0;
">

<span class="available-badge">

<span class="dot"></span>

Available

</span>

</span>

<p>

<?php echo htmlspecialchars($rec['room_type']); ?>

</p>

<p>

⭐ 4.9 Rating

&nbsp;&nbsp;|&nbsp;&nbsp;

👥 Capacity :
<?php echo $rec['capacity']; ?>

</p>

<p class="starting">
Starting From
</p>

<div class="price">
₹<?php echo number_format($rec['price']); ?>
<span>/ Night</span>
</div>

<p style="color:#16a34a;font-weight:600;margin-bottom:15px;">

✔ Free Cancellation

</p>

<a
href="booking.php?room_id=<?php echo $rec['id']; ?>"
class="book-btn">

Book Now

</a>

</div>

</div>

<?php } ?>

</div>

</div>

<?php } ?>

<hr style="margin:60px 0;">

<div class="same-room-section">

<h2 style="margin-bottom:25px;color:#1e3a8a;">
More <?php echo htmlspecialchars($room['room_type']); ?> Rooms
</h2>

<div class="same-room-grid">

<?php while($same=mysqli_fetch_assoc($same_rooms)){ ?>

<div class="same-room-card">

<img src="assets/images/<?php echo htmlspecialchars($same['image']); ?>">

<div class="same-room-content">

<h3 style="
font-size:24px;
font-weight:700;
color:#1e3a8a;
margin-bottom:12px;
line-height:1.3;
text-transform:uppercase;
">

<?php echo htmlspecialchars($same['room_name']); ?>

</h3>

<div style="
display:inline-block;
background:#2563eb;
color:#fff;
padding:8px 18px;
border-radius:30px;
font-size:15px;
font-weight:600;
margin:8px 0 18px 0;
">

Room <?php echo htmlspecialchars($same['room_number']); ?>

</div>

<p style="font-size:18px;margin-bottom:10px;">

👥
<strong>
<?php echo $same['capacity']; ?>
Guests
</strong>

</p>

<p class="starting">
Starting From
</p>


<div class="price" style="
font-size:38px;
font-weight:700;
color:#0f766e;
margin:10px 0;
">

₹<?php echo number_format($same['price']); ?>

<span>/ Night</span>

</div>

<a
href="booking.php?room_id=<?php echo $same['id']; ?>"
class="book-btn">

View Details →

</a>

</div>

</div>

<?php } ?>

</div>

<div style="text-align:center;margin-top:35px;">

<a href="rooms.php?type=<?php echo urlencode($room['room_type']); ?>"
style="
display:inline-block;
padding:14px 35px;
background:#1e3a8a;
color:#fff;
text-decoration:none;
border-radius:10px;
font-weight:600;
">

View All <?php echo htmlspecialchars($room['room_type']); ?> Rooms →

</a>

</div>

</div>

</section>

<?php if(isset($booking_error) && $booking_error){ ?>

<script>
window.addEventListener("load", function () {

    const errorSection = document.getElementById("booking-error");

    if(errorSection){

        errorSection.scrollIntoView({
            behavior: "smooth",
            block: "start"
        });

    }

});
</script>

<?php } ?>

<script>

const checkIn = document.querySelector('input[name="checkin"]');
const checkOut = document.querySelector('input[name="checkout"]');

checkIn.addEventListener("change", function () {

    checkOut.min = this.value;

    if (checkOut.value < this.value) {
        checkOut.value = "";
    }

});

</script>
</body>
</html>