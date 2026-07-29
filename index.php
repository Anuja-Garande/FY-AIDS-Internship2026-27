<?php
session_start();
require_once("database/db.php");
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
content="width=device-width, initial-scale=1.0">

<title>Hotel Room Booking System</title>

<link rel="preconnect"
href="https://fonts.googleapis.com">

<link rel="preconnect"
href="https://fonts.gstatic.com"
crossorigin>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap"
rel="stylesheet">

<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">

<style>

*{

margin:0;
padding:0;
box-sizing:border-box;
font-family:'Poppins',sans-serif;

}

html{

scroll-behavior:smooth;

}

body{

background:#f8fafc;
overflow-x:hidden;

}

header{

position:fixed;

top:0;
left:0;

width:100%;

z-index:999;

padding:18px 7%;

transition:.4s;
transition:.4s ease;

}

.navbar{

display:flex;

justify-content:space-between;

align-items:center;

background:rgba(255,255,255,.10);

backdrop-filter:blur(18px);

padding:18px 35px;

border-radius:18px;

border:1px solid rgba(255,255,255,.18);

}

.logo{

font-size:28px;

font-weight:700;

color:#fff;

}

.logo span{

color:#38bdf8;

}

.nav-links{

display:flex;

gap:35px;

list-style:none;

}

.nav-links a{

color:#fff;

text-decoration:none;

font-weight:500;

transition:.3s;

position:relative;

}

.nav-links a::after{

content:"";

position:absolute;

left:0;
bottom:-6px;

width:0;

height:2px;

background:#38bdf8;

transition:.3s;

}

.nav-links a:hover::after{

width:100%;

}

.nav-buttons{

display:flex;

gap:15px;

align-items:center;

}

.login-btn{

padding:12px 22px;

border-radius:10px;

text-decoration:none;

background:rgba(255,255,255,.12);

border:1px solid rgba(255,255,255,.18);

color:#fff;

transition:.3s;

}

.register-btn{

padding:12px 24px;

border-radius:10px;

text-decoration:none;

background:linear-gradient(135deg,#0ea5e9,#2563eb);

color:#fff;

font-weight:600;

transition:.3s;

}

.register-btn:hover,
.login-btn:hover{

transform:translateY(-3px);

}

.hero{

height:100vh;

background:

linear-gradient(rgba(5,15,30,.70),rgba(5,15,30,.70)),

url('assets/images/home-bg.jpeg');

background-size:cover;

background-position:center;

display:flex;

align-items:center;

padding:0 8%;

padding-top:120px;

}

.hero-content{

max-width:700px;

}

.hero-content h4{

color:#38bdf8;

font-size:22px;

letter-spacing:2px;

margin-bottom:15px;

text-transform:uppercase;

}

.hero-content h1{

font-size:68px;

line-height:82px;

color:#fff;

margin-bottom:25px;

font-weight:800;

}

.hero-content p{

font-size:18px;

color:#dbeafe;

line-height:32px;

margin-bottom:35px;

}

.hero-buttons{

display:flex;

gap:18px;

margin-top:20px;

}

.hero-btn{

padding:16px 34px;

border-radius:12px;

text-decoration:none;

font-size:16px;

font-weight:600;

transition:.35s;

}

.primary-btn{

background:linear-gradient(135deg,#0ea5e9,#2563eb);

color:#fff;

}

.secondary-btn{

background:rgba(255,255,255,.12);

border:1px solid rgba(255,255,255,.20);

color:#fff;

}

.hero-btn:hover{

transform:translateY(-4px);

box-shadow:0 12px 30px rgba(14,165,233,.35);

}

.scroll-down{

position:absolute;

bottom:35px;

left:50%;

transform:translateX(-50%);

color:#fff;

font-size:30px;

animation:bounce 2s infinite;

}

@keyframes bounce{

0%,20%,50%,80%,100%{

transform:translate(-50%,0);

}

40%{

transform:translate(-50%,-10px);

}

60%{

transform:translate(-50%,-5px);

}

}
/*==========================
ABOUT SECTION
==========================*/

.about-section{

padding:120px 8%;

background:#ffffff;

}

.section-container{

display:grid;

grid-template-columns:1fr 1fr;

gap:70px;

align-items:center;

}

.about-image{

position:relative;

}

.about-image img{

width:100%;

height:650px;

object-fit:cover;

border-radius:22px;

box-shadow:0 25px 60px rgba(0,0,0,.15);

}

.experience-box{

position:absolute;

bottom:30px;

left:30px;

background:#0f172a;

color:#fff;

padding:25px 35px;

border-radius:18px;

text-align:center;

}

.experience-box h2{

font-size:42px;

margin-bottom:5px;

}

.section-tag{

display:inline-block;

color:#0ea5e9;

font-weight:700;

letter-spacing:2px;

margin-bottom:15px;

}

.about-content h2{

font-size:48px;

line-height:62px;

margin-bottom:20px;

color:#0f172a;

}

.about-content p{

font-size:17px;

line-height:32px;

color:#64748b;

margin-bottom:30px;

}

.about-features{

display:grid;

grid-template-columns:1fr 1fr;

gap:20px;

margin-bottom:35px;

}

.feature-box{

display:flex;

gap:18px;

padding:20px;

border-radius:16px;

background:#f8fafc;

transition:.35s;

}

.feature-box:hover{

transform:translateY(-6px);

box-shadow:0 20px 40px rgba(0,0,0,.08);

}

.feature-box i{

font-size:28px;

color:#0ea5e9;

margin-top:5px;

}

.about-btn{

display:inline-block;

padding:16px 35px;

background:linear-gradient(135deg,#0ea5e9,#2563eb);

color:#fff;

text-decoration:none;

border-radius:12px;

font-weight:600;

transition:.3s;

}

.about-btn:hover{

transform:translateY(-3px);

}

/*==========================
ROOMS
==========================*/

.rooms-section{

padding:120px 8%;

background:#f8fafc;

}

.section-title{

text-align:center;

margin-bottom:60px;

}

.section-title span{

color:#0ea5e9;

font-weight:700;

letter-spacing:2px;

}

.section-title h2{

font-size:48px;

margin:18px 0;

color:#0f172a;

}

.section-title p{

max-width:700px;

margin:auto;

font-size:17px;

color:#64748b;

line-height:30px;

}

.room-grid{

display:grid;

grid-template-columns:repeat(auto-fit,minmax(340px,1fr));

gap:35px;

}

.room-card{

background:#fff;

border-radius:20px;

overflow:hidden;

box-shadow:0 15px 40px rgba(0,0,0,.08);

transition:.35s;

}

.room-card:hover{
transform:translateY(-12px) scale(1.02);
box-shadow:0 25px 60px rgba(0,0,0,.18);
}

.room-image{

position:relative;

overflow:hidden;

}

.room-image img{

width:100%;

height:260px;

object-fit:cover;

transition:.5s;

}

.room-card:hover .room-image img{
transform:scale(1.08);
}

.room-card:hover img{

transform:scale(1.08);

}

.price-tag{

position:absolute;

top:18px;

right:18px;

background:#0ea5e9;

color:#fff;

padding:10px 18px;

border-radius:30px;

font-weight:600;

}

.room-body{

padding:28px;

}

.rating{

color:#f59e0b;

font-weight:600;

margin-bottom:15px;

}

.rating span{

color:#64748b;

margin-left:8px;

}

.room-body h3{

font-size:28px;

margin-bottom:15px;

color:#0f172a;

}

.room-body p{

color:#64748b;

line-height:28px;

margin-bottom:20px;

}

.room-icons{

display:flex;

justify-content:space-between;

margin-bottom:25px;

font-size:14px;

color:#475569;

}

.room-btn{

display:block;

text-align:center;

padding:15px;

background:linear-gradient(135deg,#0ea5e9,#2563eb);

color:#fff;

text-decoration:none;

border-radius:12px;

font-weight:600;

transition:.3s;

}

.room-btn:hover{

transform:translateY(-3px);

}

header.scrolled{

padding:10px 7%;

}

header.scrolled .navbar{

background:#ffffff;

box-shadow:0 10px 35px rgba(0,0,0,.12);

}

header.scrolled .logo{

color:#0f172a;

}

header.scrolled .logo span{

color:#0ea5e9;

}

header.scrolled .nav-links a{

color:#0f172a;

}

header.scrolled .login-btn{

background:#f1f5f9;

border:1px solid #dbeafe;

color:#0f172a;

}

header.scrolled .login-btn:hover{

background:#0ea5e9;

color:#fff;

}
/* ================= WHY CHOOSE US ================= */

.why-section{

padding:90px 7%;

background:#f8fafc;

}

.why-grid{

display:grid;

grid-template-columns:repeat(auto-fit,minmax(240px,1fr));

gap:25px;

margin-top:50px;

}

.why-card{

background:#fff;

padding:35px 25px;

border-radius:20px;

text-align:center;

box-shadow:0 10px 30px rgba(0,0,0,.08);

transition:.35s;

}

.why-card:hover{

transform:translateY(-10px);

box-shadow:0 20px 45px rgba(14,165,233,.18);

}

.why-card i{

font-size:45px;

color:#0ea5e9;

margin-bottom:20px;

}

.why-card h3{

font-size:22px;

margin-bottom:12px;

color:#0f172a;

}

.why-card p{

color:#64748b;

line-height:1.8;

}

/* ================= STATS ================= */

.stats-section{

padding:80px 7%;

background:linear-gradient(135deg,#0f172a,#1e293b);

display:grid;

grid-template-columns:repeat(auto-fit,minmax(180px,1fr));

gap:30px;

text-align:center;

}

.stat-box h2{

font-size:42px;

color:#38bdf8;

margin-bottom:10px;

}

.stat-box p{

color:#fff;

font-size:17px;

letter-spacing:1px;

}
/*================ CONTACT ================*/

.contact-section{

padding:100px 8%;

background:#ffffff;

}

.contact-grid{

display:grid;

grid-template-columns:repeat(auto-fit,minmax(260px,1fr));

gap:30px;

margin-top:50px;

}

.contact-card{

background:#f8fafc;

padding:40px;

text-align:center;

border-radius:18px;

transition:.35s;

box-shadow:0 10px 30px rgba(0,0,0,.06);

}

.contact-card:hover{

transform:translateY(-8px);

box-shadow:0 20px 40px rgba(14,165,233,.18);

}

.contact-card i{

font-size:42px;

color:#0ea5e9;

margin-bottom:20px;

}

.contact-card h3{

margin-bottom:10px;

color:#0f172a;

}

/*================ FOOTER ================*/

.footer{

background:#0f172a;

padding:60px 20px;

text-align:center;

color:#fff;

}

.footer h2{

margin-bottom:15px;

}

.footer p{

color:#cbd5e1;

margin:10px 0;

}

.footer-social{

display:flex;

justify-content:center;

gap:18px;

margin:25px 0;

}

.footer-social a{

width:48px;

height:48px;

display:flex;

align-items:center;

justify-content:center;

border-radius:50%;

background:#1e293b;

color:#fff;

text-decoration:none;

transition:.35s;

}

.footer-social a:hover{

background:#0ea5e9;

transform:translateY(-5px);

}

#topBtn{

position:fixed;

right:25px;

bottom:25px;

width:50px;

height:50px;

border:none;

border-radius:50%;

background:#0ea5e9;

color:#fff;

font-size:20px;

display:none;

align-items:center;

justify-content:center;

cursor:pointer;

box-shadow:0 10px 25px rgba(0,0,0,.25);

transition:.3s;

z-index:999;

}

#topBtn:hover{

background:#2563eb;

transform:translateY(-5px);

}

</style>

</head>

<body>

<header>

<nav class="navbar">

<div class="logo">

🏨 <span>Luxury Hotel</span>

</div>

<ul class="nav-links">

<li><a href="#">Home</a></li>

<li><a href="#about">About</a></li>

<li><a href="#rooms">Rooms</a></li>

<li><a href="#contact">Contact</a></li>

</ul>

<div class="nav-buttons">

<?php if(isset($_SESSION['user_name'])){ ?>

<a href="user/booking-history.php" class="login-btn">

My Bookings

</a>

<a href="auth/logout.php" class="register-btn">

Logout

</a>

<?php }else{ ?>

<a href="auth/login.php" class="login-btn">

Login

</a>

<a href="auth/register.php" class="register-btn">

Register

</a>

<?php } ?>

</div>

</nav>

</header>

<section class="hero">

<div class="hero-content">

<h4>Luxury • Comfort • Premium</h4>

<h1>

Find Your Perfect Stay

</h1>

<p>

Experience world-class hospitality with luxurious rooms,
premium services and unforgettable memories.

</p>

<div class="hero-buttons">

<a href="#rooms" class="hero-btn primary-btn">

Book Now

</a>

<a href="#about" class="hero-btn secondary-btn">

Explore

</a>

</div>

</div>

<div class="scroll-down">

<i class="fa-solid fa-angles-down"></i>

</div>

</section>


<!-- ================= ABOUT SECTION ================= -->

<section id="about" class="about-section">

<div class="section-container">

<div class="about-image">

<img src="assets/images/about/about-image.jpg" alt="Luxury Hotel">

<div class="experience-box">

<h2>10+</h2>

<p>Years Of Luxury Experience</p>

</div>

</div>

<div class="about-content">

<span class="section-tag">

ABOUT OUR HOTEL

</span>

<h2>

Luxury Stay With Premium Hospitality

</h2>

<p>

Experience elegant rooms, premium facilities, delicious food,
and world-class hospitality designed to make every stay unforgettable.

</p>

<div class="about-features">

<div class="feature-box">

<i class="fa-solid fa-wifi"></i>

<div>

<h4>Free WiFi</h4>

<p>Unlimited high-speed internet.</p>

</div>

</div>

<div class="feature-box">

<i class="fa-solid fa-utensils"></i>

<div>

<h4>Restaurant</h4>

<p>Luxury dining experience.</p>

</div>

</div>

<div class="feature-box">

<i class="fa-solid fa-water-ladder"></i>

<div>

<h4>Swimming Pool</h4>

<p>Relax with premium facilities.</p>

</div>

</div>

<div class="feature-box">

<i class="fa-solid fa-car"></i>

<div>

<h4>Free Parking</h4>

<p>Safe parking for all guests.</p>

</div>

</div>

</div>

<a href="#rooms" class="about-btn">

Explore Rooms

</a>

</div>

</div>

</section>

<!-- ================= ROOMS ================= -->

<section id="rooms" class="rooms-section">

<div class="section-title">

<span>OUR ROOMS</span>

<h2>

Luxury Rooms & Suites

</h2>

<p>

Choose your perfect room for a comfortable and unforgettable stay.

</p>

</div>

<div class="room-grid">

<?php

$query=mysqli_query($conn,"SELECT * FROM rooms WHERE status='Available' ORDER BY id DESC");

while($room=mysqli_fetch_assoc($query)){

?>

<div class="room-card">

<div class="room-image">

<img src="assets/images/<?php echo $room['image']; ?>">

<?php if(isset($_SESSION['user_id'])) { ?>

<span class="price-tag">

₹<?php echo $room['price']; ?>/Night

</span>

<?php } ?>

</div>

<div class="room-body">

<div class="rating">

★★★★★

<span>

4.9

</span>

</div>

<h3>

<?php echo $room['room_name']; ?>

</h3>

<p>

<?php echo $room['description']; ?>

</p>

<div class="room-icons">

<span><i class="fa-solid fa-bed"></i> 2 Beds</span>

<span><i class="fa-solid fa-wifi"></i> WiFi</span>

<span><i class="fa-solid fa-tv"></i> TV</span>

</div>


<?php if(isset($_SESSION['user_id'])) { ?>

<a href="booking.php?room_id=<?php echo $room['id']; ?>" class="room-btn">

Book Now

</a>

<?php } else { ?>

<a href="auth/login.php" class="room-btn">

<i class="fa-solid fa-lock"></i>

Login to View Price & Book

</a>

<?php } ?>

</div>

</div>

<?php } ?>

</div>

</section>
<!-- ================= WHY CHOOSE US ================= -->

<section class="why-section">

<div class="section-title">

<span>WHY CHOOSE US</span>

<h2>Experience Luxury Like Never Before</h2>

<p>

We provide premium hospitality with world-class facilities
and unforgettable experiences.

</p>

</div>

<div class="why-grid">

<div class="why-card">

<i class="fa-solid fa-hotel"></i>

<h3>Luxury Rooms</h3>

<p>

Beautiful rooms designed for maximum comfort and relaxation.

</p>

</div>

<div class="why-card">

<i class="fa-solid fa-wifi"></i>

<h3>Free WiFi</h3>

<p>

Unlimited high-speed internet in every room.

</p>

</div>

<div class="why-card">

<i class="fa-solid fa-utensils"></i>

<h3>Fine Dining</h3>

<p>

Delicious meals prepared by our expert chefs.

</p>

</div>

<div class="why-card">

<i class="fa-solid fa-headset"></i>

<h3>24/7 Support</h3>

<p>

Friendly staff available anytime for your comfort.

</p>

</div>

</div>

</section>

<!-- ================= HOTEL STATS ================= -->

<section class="stats-section">

<div class="stat-box">

<h2>500+</h2>

<p>Luxury Rooms</p>

</div>

<div class="stat-box">

<h2>12K+</h2>

<p>Happy Guests</p>

</div>

<div class="stat-box">

<h2>4.9★</h2>

<p>Guest Rating</p>

</div>

<div class="stat-box">

<h2>24/7</h2>

<p>Premium Support</p>

</div>

</section>

<!-- ================= CONTACT ================= -->

<section id="contact" class="contact-section">

<div class="section-title">

<span>CONTACT US</span>

<h2>We're Always Here For You</h2>

<p>
Have any questions? Contact us anytime.
</p>

</div>

<div class="contact-grid">

<div class="contact-card">
<i class="fa-solid fa-location-dot"></i>
<h3>Address</h3>
<p>Pune, Maharashtra, India</p>
</div>

<div class="contact-card">
<i class="fa-solid fa-phone"></i>
<h3>Phone</h3>
<p>+91 9876543210</p>
</div>

<div class="contact-card">
<i class="fa-solid fa-envelope"></i>
<h3>Email</h3>
<p>info@luxuryhotel.com</p>
</div>

</div>

</section>

<!-- ================= FOOTER ================= -->

<footer class="footer">

<h2>🏨 Luxury Hotel</h2>

<p>
Luxury Rooms • Premium Hospitality • Best Experience
</p>

<div class="footer-social">

<a href="#"><i class="fab fa-facebook-f"></i></a>

<a href="#"><i class="fab fa-instagram"></i></a>

<a href="#"><i class="fab fa-x-twitter"></i></a>

<a href="#"><i class="fab fa-linkedin-in"></i></a>

</div>

<p class="copyright">

© <?php echo date("Y"); ?> Luxury Hotel. All Rights Reserved.

</p>

</footer>

<button id="topBtn">

<i class="fa-solid fa-arrow-up"></i>

</button>

<script>

const header=document.querySelector("header");

window.addEventListener("scroll",()=>{

if(window.scrollY>80){

header.classList.add("scrolled");

}else{

header.classList.remove("scrolled");

}

const btn=document.getElementById("topBtn");

if(window.scrollY>400){

btn.style.display="flex";

}else{

btn.style.display="none";

}

});

document.getElementById("topBtn").onclick=function(){

window.scrollTo({

top:0,

behavior:"smooth"

});

};

</script>

</body>
</html>
