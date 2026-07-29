<?php
session_start();
require_once("database/db.php");

$user_name = "";
$profile_image = "";
$email = "";

if(isset($_SESSION['user_id']))
{
    $user_id = $_SESSION['user_id'];

    $user_query = mysqli_query($conn,"SELECT profile_image,email FROM users WHERE id='$user_id'");

    if(mysqli_num_rows($user_query)>0)
    {
        $user_data = mysqli_fetch_assoc($user_query);

        $profile_image = $user_data['profile_image'];
        $email = $user_data['email'];
    }

    $user_name = $_SESSION['user_name'];
}

$query = "SELECT * FROM rooms";
$result = mysqli_query($conn,$query);
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Luxury Rooms</title>

<link rel="stylesheet" href="assets/css/rooms.css">

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

<li><a href="rooms.php" class="active">Rooms</a></li>

<li><a href="contact.php">Contact</a></li>

<?php if(isset($_SESSION['user_id'])){ ?>

<li><a href="my-bookings.php">My Bookings</a></li>

<?php } ?>

</ul>

<?php if(isset($_SESSION['user_id'])){ ?>

<div class="user-dropdown">

<button class="user-btn">

<?php

if(!empty($profile_image) &&
file_exists("uploads/profile/".$profile_image))
{

?>

<img src="uploads/profile/<?php echo htmlspecialchars($profile_image); ?>"
class="nav-profile-img">

<?php

}

else

{

?>

<i class="fa-solid fa-user"></i>

<?php

}

?>

<span><?php echo htmlspecialchars($user_name); ?></span>

<i class="fa-solid fa-chevron-down"></i>

</button>

<div class="dropdown-menu">

<div class="dropdown-header">

<?php

if(!empty($profile_image) &&
file_exists("uploads/profile/".$profile_image))
{

?>

<img src="uploads/profile/<?php echo htmlspecialchars($profile_image); ?>"
class="dropdown-profile-img">

<?php

}

else

{

?>

<i class="fa-solid fa-circle-user dropdown-icon"></i>

<?php

}

?>

<h4><?php echo htmlspecialchars($user_name); ?></h4>

<small><?php echo htmlspecialchars($email); ?></small>

<span class="online-badge">

<i class="fa-solid fa-circle"></i>

Online

</span>

</div>

<hr>

<a href="user/profile.php">

<i class="fa-solid fa-user"></i>

My Profile

</a>

<a href="my-bookings.php">

<i class="fa-solid fa-calendar-check"></i>

My Bookings

</a>

<a href="auth/logout.php">

<i class="fa-solid fa-right-from-bracket"></i>

Logout

</a>

</div>

</div>

<?php } else { ?>

<div class="nav-buttons">

<a href="auth/login.php" class="login-btn">

Login

</a>

<a href="auth/register.php" class="register-btn">

Register

</a>

</div>

<?php } ?>

</nav>

<!-- ================= HERO ================= -->

<section class="rooms-hero">

<div class="hero-content">

<span class="hero-tag">
⭐ Luxury Collection
</span>

<h1>Luxury Rooms & Suites</h1>

<p>

Choose from our collection of premium rooms designed
for comfort, elegance and unforgettable memories.

</p>

<a href="#rooms" class="hero-btn">

<i class="fa-solid fa-bed"></i>

Explore Rooms

</a>

</div>

</section>

<!-- ================= ROOMS ================= -->

<section class="rooms-section" id="rooms">

<div class="section-title">

<h4>Luxury Collection</h4>

<h2>Our Premium Rooms</h2>

<p>

Book your dream stay with modern amenities,
beautiful interiors and affordable pricing.

</p>

</div>

<section class="stats">

    <div class="stat-box">
        <i class="fa-solid fa-hotel"></i>
        <h2>50+</h2>
        <p>Luxury Rooms</p>
    </div>

    <div class="stat-box">
        <i class="fa-solid fa-users"></i>
        <h2>10K+</h2>
        <p>Happy Guests</p>
    </div>

    <div class="stat-box">
        <i class="fa-solid fa-star"></i>
        <h2>4.9</h2>
        <p>Guest Rating</p>
    </div>

    <div class="stat-box">
        <i class="fa-solid fa-headset"></i>
        <h2>24/7</h2>
        <p>Support</p>
    </div>

</section>

<div class="room-container">


<?php

while($row = mysqli_fetch_assoc($result))
{

$imagePath = "assets/images/".$row['image'];

?>





<div class="room-card">



<?php if($row['best_seller'] == 1){ ?>

<div class="room-badge">
    <i class="fa-solid fa-fire"></i>
    Best Seller
</div>



<?php } ?>

<div class="rating">
    ⭐ 4.9
</div>

<?php

if(!empty($row['image']) &&
file_exists($imagePath))
{

?>

<div class="room-image">

<img src="<?php echo $imagePath; ?>">

</div>

<?php

}

else

{

?>

<div class="room-image">

<img src="assets/images/rooms/new-room.jpg">

</div>

<?php

}

?>
<div class="room-content">

<span class="room-number">
Room No: <?php echo htmlspecialchars($row['room_number']); ?>
</span>

<h3><?php echo htmlspecialchars($row['room_name']); ?></h3>

<div class="room-info">

<?php if(isset($_SESSION['user_id'])) { ?>

<span>

<i class="fa-solid fa-indian-rupee-sign"></i>

₹<?php echo number_format($row['price']); ?>

</span>

<?php } ?>

<span>

<i class="fa-solid fa-hotel"></i>

<?php echo htmlspecialchars($row['room_type']); ?>

</span>

</div>

<div class="capacity">

<i class="fa-solid fa-users"></i>

Capacity :

<strong>

<?php echo $row['capacity']; ?>

Persons

</strong>

</div>

<p class="description">

<?php echo htmlspecialchars($row['description']); ?>

</p>

<div class="room-footer">

<?php if(isset($_SESSION['user_id'])) { ?>

<a href="booking.php?room_id=<?php echo $row['id']; ?>" class="book-btn">

<i class="fa-solid fa-calendar-check"></i>

Book Now

</a>

<?php } else { ?>

<a href="auth/login.php" class="book-btn login-required">

<i class="fa-solid fa-lock"></i>

Login to View Price & Book

</a>

<?php } ?>

</div>

</div>

</div>

<?php

}

?>

</div>

</section>

<!-- ================= FEATURES ================= -->

<section class="features">

<div class="feature-box">

<i class="fa-solid fa-wifi"></i>

<h3>Free WiFi</h3>

<p>High speed internet available in every room.</p>

</div>

<div class="feature-box">

<i class="fa-solid fa-mug-hot"></i>

<h3>Breakfast</h3>

<p>Complimentary breakfast with every booking.</p>

</div>

<div class="feature-box">

<i class="fa-solid fa-square-parking"></i>

<h3>Parking</h3>

<p>Secure parking facility for all guests.</p>

</div>

<div class="feature-box">

<i class="fa-solid fa-person-swimming"></i>

<h3>Swimming Pool</h3>

<p>Relax and enjoy our luxury swimming pool.</p>

</div>

</section>

<!-- ================= FOOTER ================= -->

<footer class="footer">

<div class="footer-container">

<div class="footer-box">

<h2>🏨 Hotel Booking</h2>

<p>

Luxury rooms with premium comfort and
world class hospitality.

</p>

</div>

<div class="footer-box">

<h3>Quick Links</h3>

<ul>

<li><a href="index.php">Home</a></li>

<li><a href="rooms.php">Rooms</a></li>

<li><a href="contact.php">Contact</a></li>

<li><a href="my-bookings.php">My Bookings</a></li>

</ul>

</div>

<div class="footer-box">

<h3>Contact</h3>

<p>

<i class="fa-solid fa-phone"></i>

+91 9876543210

</p>

<p>

<i class="fa-solid fa-envelope"></i>

hotel@gmail.com

</p>

<p>

<i class="fa-solid fa-location-dot"></i>

Pune, Maharashtra

</p>

</div>

</div>

<div class="footer-bottom">

<p>

© <?php echo date("Y"); ?>

Hotel Booking System | All Rights Reserved.

</p>

</div>

</footer>

<script>

const userBtn=document.querySelector(".user-btn");

const dropdown=document.querySelector(".dropdown-menu");

if(userBtn){

userBtn.addEventListener("click",function(e){

e.stopPropagation();

dropdown.classList.toggle("show");

});

document.addEventListener("click",function(){

dropdown.classList.remove("show");

});

}

</script>

</body>

</html>