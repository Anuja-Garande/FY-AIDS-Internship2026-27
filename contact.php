<?php
session_start();
require_once("database/db.php");

if(isset($_POST['send_message'])){

    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $subject = mysqli_real_escape_string($conn, $_POST['subject']);
    $message = mysqli_real_escape_string($conn, $_POST['message']);

    $query = "INSERT INTO contact_messages
    (name, email, subject, message)
    VALUES
    ('$name', '$email', '$subject', '$message')";

    if(mysqli_query($conn, $query)){

        echo "<script>
        alert('Message Sent Successfully!');
        window.location='contact.php';
        </script>";

        exit();

    }else{

        echo "<script>
        alert('Failed to send message!');
        </script>";

    }

}
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Contact Us</title>

<link rel="stylesheet" href="assets/css/contact.css">

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

<li><a href="contact.php" class="active">Contact</a></li>

</ul>

<?php if(isset($_SESSION['user_id'])){ ?>

<a href="user/dashboard.php" class="dashboard-btn">

<i class="fa-solid fa-user"></i>

Dashboard

</a>

<?php } else { ?>

<a href="auth/login.php" class="dashboard-btn">

<i class="fa-solid fa-right-to-bracket"></i>

Login

</a>

<?php } ?>

</nav>

<!-- ================= HERO ================= -->

<section class="contact-hero">

<h1>Contact Us</h1>

<p>

We'd love to hear from you.
Feel free to contact our hotel anytime.

</p>

</section>
<!-- ================= CONTACT SECTION ================= -->

<section class="contact-section">

<div class="contact-container">

<div class="contact-info">

<h2>Get In Touch</h2>

<p>
Have questions about bookings or rooms?
Our team is always ready to help you.
</p>

<div class="info-card">

<i class="fa-solid fa-location-dot"></i>

<div>

<h3>Address</h3>

<p>Pune, Maharashtra, India</p>

</div>

</div>

<div class="info-card">

<i class="fa-solid fa-phone"></i>

<div>

<h3>Phone</h3>

<p>+91 98765 43210</p>

</div>

</div>

<div class="info-card">

<i class="fa-solid fa-envelope"></i>

<div>

<h3>Email</h3>

<p>support@hotelbooking.com</p>

</div>

</div>

</div>

<div class="contact-form">

<h2>Send Message</h2>

<form method="POST" action="">

<input
type="text"
name="name"
placeholder="Your Name"
required>

<input
type="email"
name="email"
placeholder="Email Address"
required>

<input
type="text"
name="subject"
placeholder="Subject"
required>

<textarea
name="message"
placeholder="Write your message..."
rows="6"
required></textarea>

<button type="submit" name="send_message">

<i class="fa-solid fa-paper-plane"></i>

Send Message

</button>

</form>

</div>

</div>

</section>
<!-- ================= MAP ================= -->

<section class="map-section">

<iframe
src="https://www.google.com/maps?q=Pune,Maharashtra&output=embed"
loading="lazy">
</iframe>

</section>

<!-- ================= FOOTER ================= -->

<footer>

<p>

© <?php echo date("Y"); ?> Hotel Booking System |
Made with ❤️ in Pune

</p>

</footer>

</body>

</html>