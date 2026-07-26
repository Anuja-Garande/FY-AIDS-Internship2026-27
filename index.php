<?php

$conn=mysqli_connect(
"localhost",
"root",
"",
"inventory_management"
);

if(!$conn){
    die("Database Connection Failed");
}

/* Contact Form */

$success="";

if(isset($_POST['send_message']))
{

    $name=mysqli_real_escape_string($conn,$_POST['name']);
    $email=mysqli_real_escape_string($conn,$_POST['email']);
    $subject=mysqli_real_escape_string($conn,$_POST['subject']);
    $message=mysqli_real_escape_string($conn,$_POST['message']);

    mysqli_query(
    $conn,
    "
    INSERT INTO contact_messages
    (name,email,subject,message)
    VALUES
    ('$name','$email','$subject','$message')
    ");

}


/* Dashboard Counters */

$product_count=mysqli_fetch_assoc(
mysqli_query(
$conn,
"SELECT COUNT(*) total FROM products")
)['total'];

$supplier_count=mysqli_fetch_assoc(
mysqli_query(
$conn,
"SELECT COUNT(*) total FROM suppliers")
)['total'];

$sales_today=mysqli_fetch_assoc(
mysqli_query(
$conn,
"
SELECT COUNT(*) total
FROM sales
WHERE DATE(created_at)=CURDATE()
")
)['total'];


/* Latest Products */

$latest_products=mysqli_query(
$conn,
"
SELECT
product_name,
category,
quantity,
unit,
selling_price
FROM products
ORDER BY created_at DESC
LIMIT 6
"
);

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta
name="viewport"
content="width=device-width, initial-scale=1.0">

<title>InventoryPro</title>

<link
href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
rel="stylesheet">

<link
rel="stylesheet"
href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

<link
rel="stylesheet"
href="assets/css/style.css">

</head>

<body>

<!-- ================= NAVBAR ================= -->

<nav class="navbar navbar-expand-lg fixed-top">

<div class="container">

<a
class="navbar-brand"
href="#">

📦 InventoryPro

</a>

<button
class="navbar-toggler"
type="button"
data-bs-toggle="collapse"
data-bs-target="#navbarNav">

<span class="navbar-toggler-icon"></span>

</button>

<div
class="collapse navbar-collapse"
id="navbarNav">

<ul class="navbar-nav ms-auto align-items-center">

<li class="nav-item">
<a class="nav-link" href="#">Home</a>
</li>

<li class="nav-item">
<a class="nav-link" href="#features">Features</a>
</li>

<li class="nav-item">
<a class="nav-link" href="#about">About</a>
</li>

<li class="nav-item">
<a class="nav-link" href="#contact">Contact</a>
</li>

<li class="nav-item me-3">

<button
id="themeToggle"
class="btn btn-outline-primary">

<i class="bi bi-moon-stars-fill"></i>

</button>

</li>

<li class="nav-item">

<a
href="login.php"
class="btn btn-primary login-btn">

Login

</a>

</li>

</ul>

</div>

</div>

</nav>



<!-- ================= HERO ================= -->

<section class="hero">

<div class="container">

<div class="row align-items-center">

<div class="col-lg-6">

<span class="badge bg-warning text-dark mb-3">

Smart Inventory Solution

</span>

<h1>

Inventory & Stock
Management System

</h1>

<p>

Manage products,
suppliers,
customers,
purchases,
sales and inventory
using one modern dashboard.

</p>

<div class="mt-4">

<a
href="login.php"
class="btn btn-success btn-lg me-3">

Get Started

</a>

<a
href="#features"
class="btn btn-outline-light btn-lg">

Learn More

</a>

</div>

</div>


<div class="col-lg-6 text-center">

<img
src="assets/images/hero.png"
class="img-fluid hero-image">

</div>

</div>


<div class="row mt-5 g-4">

<div class="col-md-4">

<div class="floating-card">

<h2
class="counter"
data-target="<?= $product_count ?>">

0

</h2>

<p>Total Products</p>

</div>

</div>

<div class="col-md-4">

<div class="floating-card">

<h2
class="counter"
data-target="<?= $supplier_count ?>">

0

</h2>

<p>Suppliers</p>

</div>

</div>

<div class="col-md-4">

<div class="floating-card">

<h2
class="counter"
data-target="<?= $sales_today ?>">

0

</h2>

<p>Today's Sales</p>

</div>

</div>

</div>

</div>

</section>

<!-- ================= FEATURES ================= -->

<section id="features" class="features">

<div class="container">

<h2 class="text-center mb-5">

System Features

</h2>

<div class="row g-4">

<div class="col-md-3">
<div class="feature-box">

<i class="bi bi-box-seam"></i>

<h4>Products</h4>

<p>
Manage products with real-time stock tracking.
</p>

</div>
</div>

<div class="col-md-3">
<div class="feature-box">

<i class="bi bi-tags"></i>

<h4>Categories</h4>

<p>
Organize products into categories quickly.
</p>

</div>
</div>

<div class="col-md-3">
<div class="feature-box">

<i class="bi bi-truck"></i>

<h4>Suppliers</h4>

<p>
Maintain supplier records and purchases.
</p>

</div>
</div>

<div class="col-md-3">
<div class="feature-box">

<i class="bi bi-people"></i>

<h4>Customers</h4>

<p>
Store customer information securely.
</p>

</div>
</div>

<div class="col-md-3">
<div class="feature-box">

<i class="bi bi-cart-plus"></i>

<h4>Purchases</h4>

<p>
Track purchases with automatic stock updates.
</p>

</div>
</div>

<div class="col-md-3">
<div class="feature-box">

<i class="bi bi-cash-stack"></i>

<h4>Sales</h4>

<p>
Generate invoices and monitor sales.
</p>

</div>
</div>

<div class="col-md-3">
<div class="feature-box">

<i class="bi bi-bar-chart-line"></i>

<h4>Reports</h4>

<p>
Powerful analytics and business reports.
</p>

</div>
</div>

<div class="col-md-3">
<div class="feature-box">

<i class="bi bi-shield-lock"></i>

<h4>Secure Login</h4>

<p>
Role-based authentication and secure access.
</p>

</div>
</div>

</div>

</div>

</section>



<!-- ================= ABOUT ================= -->

<section id="about" class="py-5">

<div class="container">

<div class="row align-items-center">

<div class="col-lg-6">

<h2 class="mb-4">

About InventoryPro

</h2>

<p class="lead">

InventoryPro is a complete Inventory & Stock
Management System that helps businesses manage
products, suppliers, purchases, customers and sales
efficiently.

</p>

<p>

The system provides real-time stock updates,
sales tracking, secure authentication,
and professional reporting.

</p>

</div>

<div class="col-lg-6">

<div class="row text-center">

<div class="col-6 mb-4">

<h2>

<?= $product_count ?>

</h2>

<p>

Products

</p>

</div>

<div class="col-6 mb-4">

<h2>

<?= $supplier_count ?>

</h2>

<p>

Suppliers

</p>

</div>

<div class="col-6">

<h2>

<?= $sales_today ?>

</h2>

<p>

Today's Sales

</p>

</div>

<div class="col-6">

<h2>

24/7

</h2>

<p>

Availability

</p>

</div>

</div>

</div>

</div>

</div>

</section>



<!-- ================= LATEST PRODUCTS ================= -->

<section class="latest-products py-5">

<div class="container">

<h2 class="text-center mb-5">

Latest Products

</h2>

<div class="row">

<?php while($product=mysqli_fetch_assoc($latest_products)){ ?>

<div class="col-md-4 mb-4">

<div class="feature-box h-100">

<i class="bi bi-box-seam"></i>

<h4>

<?= htmlspecialchars($product['product_name']) ?>

</h4>

<p>

<strong>Category:</strong>

<?= htmlspecialchars($product['category']) ?>

</p>

<p>

<strong>Stock:</strong>

<?= $product['quantity'] ?>

<?= htmlspecialchars($product['unit']) ?>

</p>

<h5 class="text-success">

₹<?= number_format($product['selling_price'],2) ?>

</h5>

</div>

</div>

<?php } ?>

</div>

</div>

</section>

<!-- ================= CONTACT ================= -->

<section id="contact" class="py-5">

<div class="container">

<div class="text-center mb-5">

<h2>Contact Us</h2>

<p class="text-muted">

Have questions? We'd love to hear from you.

</p>

</div>

<div class="row">

<div class="col-lg-5 mb-4">

<div class="feature-box h-100">

<h4 class="mb-4">

Get in Touch

</h4>

<p>

<i class="bi bi-geo-alt-fill text-primary me-2"></i>

Indore, Madhya Pradesh, India

</p>

<p>

<i class="bi bi-envelope-fill text-primary me-2"></i>

inventorypro@gmail.com

</p>

<p>

<i class="bi bi-telephone-fill text-primary me-2"></i>

+91 98765 43210

</p>

<p>

<i class="bi bi-clock-fill text-primary me-2"></i>

Monday - Saturday

<br>

9:00 AM - 6:00 PM

</p>

</div>

</div>



<div class="col-lg-7">

<div class="feature-box">

<form action="contact_process.php" method="POST">

<div class="mb-3">

<input
type="text"
name="name"
class="form-control"
placeholder="Your Name"
required>

</div>

<div class="mb-3">

<input
type="email"
name="email"
class="form-control"
placeholder="Your Email"
required>

</div>

<div class="mb-3">

<input
type="text"
name="subject"
class="form-control"
placeholder="Subject"
required>

</div>

<div class="mb-3">

<textarea
name="message"
class="form-control"
rows="5"
placeholder="Write your message..."
required></textarea>

</div>

<button
type="submit"
name="send_message"
class="btn btn-primary w-100">

<i class="bi bi-send-fill"></i>

Send Message

</button>

</form>

</div>

</div>

</div>

</div>

</section>



<!-- ================= FOOTER ================= -->

<footer>

<div class="container">

<p class="mb-0">

© 2026 InventoryPro | Inventory & Stock Management System

</p>

</div>

</footer>



<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>

document.addEventListener("DOMContentLoaded",function(){


/* ================= COUNTER ================= */

const counters=document.querySelectorAll(".counter");

counters.forEach(counter=>{

const target=parseInt(counter.dataset.target)||0;

let current=0;

const increment=Math.max(1,Math.ceil(target/50));

function update(){

current+=increment;

if(current>=target){

counter.innerText=target;

}else{

counter.innerText=current;

requestAnimationFrame(update);

}

}

update();

});


/* ================= DARK MODE ================= */

const toggle=document.getElementById("themeToggle");

if(toggle){

const savedTheme=localStorage.getItem("theme");

if(savedTheme==="dark"){

document.body.classList.add("dark-mode");

toggle.innerHTML='<i class="bi bi-sun-fill"></i>';

}else{

document.body.classList.remove("dark-mode");

toggle.innerHTML='<i class="bi bi-moon-stars-fill"></i>';

}

toggle.addEventListener("click",function(){

document.body.classList.toggle("dark-mode");

if(document.body.classList.contains("dark-mode")){

localStorage.setItem("theme","dark");

toggle.innerHTML='<i class="bi bi-sun-fill"></i>';

}else{

localStorage.setItem("theme","light");

toggle.innerHTML='<i class="bi bi-moon-stars-fill"></i>';

}

});

}

});

</script>

</body>

</html>