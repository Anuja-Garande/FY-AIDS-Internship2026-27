<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>My Expense Manager</title>

<!-- Bootstrap 5 -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<!-- Bootstrap Icons -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

<!-- Google Font -->
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<style>

*{
margin:0;
padding:0;
box-sizing:border-box;
font-family:'Poppins',sans-serif;
scroll-behavior:smooth;
}

body{

background:
linear-gradient(rgba(15,23,42,.82),
rgba(37,99,235,.70)),
url("assets/images/bg.png");

background-size:cover;
background-position:center;
background-attachment:fixed;

min-height:100vh;

color:#fff;

}

/*================ NAVBAR ================*/

.navbar{

background:rgba(15,23,42,.55);

backdrop-filter:blur(14px);

padding:15px 0;

position:sticky;

top:0;

z-index:1000;

}

.logo{

font-size:32px;

color:#ffffff;

margin-right:10px;

}

.brand{

font-size:28px;

font-weight:700;

color:#fff;

text-decoration:none;

}

.nav-link{

color:#fff !important;

margin-left:18px;

font-weight:500;

transition:.3s;

}

.nav-link:hover{

color:#ffd166 !important;

}

.btn-login{

background:#fff;

color:#2563eb;

border-radius:30px;

padding:10px 25px;

font-weight:600;

margin-left:15px;

}

.btn-login:hover{

background:#2563eb;

color:#fff;

}

.btn-register{

background:linear-gradient(135deg,#2563eb,#4f46e5);

color:#fff;

border-radius:30px;

padding:10px 25px;

margin-left:10px;

}

.btn-register:hover{

transform:translateY(-3px);

color:#fff;

}

/*================ HERO ================*/

.hero{

min-height:92vh;

display:flex;

align-items:center;

}

.hero h1{

font-size:60px;

font-weight:700;

line-height:1.2;

}

.hero p{

font-size:20px;

margin-top:25px;

color:#e2e8f0;

line-height:1.8;

}

.btn-start{

background:#fff;

color:#2563eb;

padding:15px 35px;

border-radius:50px;

text-decoration:none;

font-weight:600;

display:inline-block;

margin-right:15px;

margin-top:35px;

}

.btn-start:hover{

background:#2563eb;

color:#fff;

}

.btn-demo{

border:2px solid #fff;

color:#fff;

padding:15px 35px;

border-radius:50px;

text-decoration:none;

display:inline-block;

margin-top:35px;

}

.btn-demo:hover{

background:#fff;

color:#2563eb;

}

.hero-image{

text-align:center;

}

.hero-image i{

font-size:240px;

animation:float 4s ease-in-out infinite;

}

@keyframes float{

0%{transform:translateY(0);}
50%{transform:translateY(-18px);}
100%{transform:translateY(0);}

}

</style>

</head>

<body>

<!-- ================= NAVBAR ================= -->

<nav class="navbar navbar-expand-lg">

<div class="container">

<a href="#home" class="navbar-brand d-flex align-items-center">

<i class="bi bi-wallet2 logo"></i>

<span class="brand">

My Expense Manager

</span>

</a>

<button
class="navbar-toggler"
type="button"
data-bs-toggle="collapse"
data-bs-target="#menu">

<i class="bi bi-list text-white fs-1"></i>

</button>

<div class="collapse navbar-collapse" id="menu">

<ul class="navbar-nav ms-auto align-items-center">

<li class="nav-item">

<a href="#home" class="nav-link">

Home

</a>

</li>

<li class="nav-item">

<a href="#features" class="nav-link">

Features

</a>

</li>

<li class="nav-item">

<a href="#about" class="nav-link">

About

</a>

</li>

<li class="nav-item">

<a href="#contact" class="nav-link">

Contact

</a>

</li>

<li>

<a href="auth/login.php" class="btn btn-login">

Login

</a>

</li>

<li>

<a href="auth/register.php" class="btn btn-register">

Register

</a>

</li>

</ul>

</div>

</div>

</nav>

<!-- ================= HERO ================= -->

<section id="home" class="hero">

<div class="container">

<div class="row align-items-center">

<div class="col-lg-6">

<h1>

Manage Your Money<br>Like a Pro

</h1>

<p>

Track your income, manage expenses, view beautiful reports and achieve your financial goals with My Expense Manager.

</p>

<a href="auth/login.php" class="btn-start">

Get Started

</a>

<a href="auth/register.php" class="btn-demo">

Create Account

</a>

</div>

<div class="col-lg-6 hero-image">

<i class="bi bi-pie-chart-fill"></i>

</div>

</div>

</div>

</section><!-- ================= FEATURES ================= -->

<section id="features" class="py-5">

<div class="container">

<div class="text-center mb-5">

<h2 class="display-4 fw-bold">

Why Choose My Expense Manager?

</h2>

<p class="text-light">

Simple • Secure • Fast • Beautiful

</p>

</div>

<div class="row g-4">

<div class="col-lg-4 col-md-6">

<div class="feature-card">

<div class="feature-icon bg-primary">

<i class="bi bi-cash-stack"></i>

</div>

<h4>Income Management</h4>

<p>

Add, edit and manage all your income records easily.

</p>

</div>

</div>

<div class="col-lg-4 col-md-6">

<div class="feature-card">

<div class="feature-icon bg-danger">

<i class="bi bi-wallet2"></i>

</div>

<h4>Expense Tracking</h4>

<p>

Keep track of every expense and control your spending.

</p>

</div>

</div>

<div class="col-lg-4 col-md-6">

<div class="feature-card">

<div class="feature-icon bg-success">

<i class="bi bi-bar-chart-fill"></i>

</div>

<h4>Monthly Reports</h4>

<p>

Generate monthly and yearly reports instantly.

</p>

</div>

</div>

<div class="col-lg-4 col-md-6">

<div class="feature-card">

<div class="feature-icon bg-warning">

<i class="bi bi-pie-chart-fill"></i>

</div>

<h4>Interactive Charts</h4>

<p>

Understand your finances with colorful charts.

</p>

</div>

</div>

<div class="col-lg-4 col-md-6">

<div class="feature-card">

<div class="feature-icon bg-info">

<i class="bi bi-file-earmark-excel-fill"></i>

</div>

<h4>Export Excel</h4>

<p>

Download your financial reports in Excel format.

</p>

</div>

</div>

<div class="col-lg-4 col-md-6">

<div class="feature-card">

<div class="feature-icon bg-secondary">

<i class="bi bi-shield-lock-fill"></i>

</div>

<h4>Secure Login</h4>

<p>

Password protected user accounts for complete security.

</p>

</div>

</div>

</div>

</div>

</section>

<!-- ================= STATISTICS ================= -->

<section class="py-5">

<div class="container">

<div class="row text-center g-4">

<div class="col-md-3">

<div class="stats-box">

<h2>100%</h2>

<p>Secure</p>

</div>

</div>

<div class="col-md-3">

<div class="stats-box">

<h2>24×7</h2>

<p>Available</p>

</div>

</div>

<div class="col-md-3">

<div class="stats-box">

<h2>Fast</h2>

<p>Performance</p>

</div>

</div>

<div class="col-md-3">

<div class="stats-box">

<h2>Easy</h2>

<p>User Friendly</p>

</div>

</div>

</div>

</div>

</section>

<!-- ================= ABOUT ================= -->

<section id="about" class="py-5">

<div class="container">

<div class="row align-items-center">

<div class="col-lg-6 text-center mb-4">

<i class="bi bi-graph-up-arrow display-1 text-warning"></i>

</div>

<div class="col-lg-6">

<h2 class="display-5 fw-bold">

About My Expense Manager

</h2>

<p class="lead text-light">

My Expense Manager is a modern personal finance system developed using PHP, MySQL, Bootstrap 5 and Chart.js.

</p>

<p class="text-light">

Manage income, expenses, categories, reports and financial insights from one beautiful dashboard.

</p>

</div>

</div>

</div>

</section>

<!-- ================= TECHNOLOGIES ================= -->

<section class="py-5">

<div class="container">

<div class="text-center mb-5">

<h2 class="display-5 fw-bold">

Technologies Used

</h2>

</div>

<div class="row g-4">

<div class="col-lg-3 col-md-6">

<div class="tech-card">

<i class="bi bi-filetype-php"></i>

<h4>PHP</h4>

</div>

</div>

<div class="col-lg-3 col-md-6">

<div class="tech-card">

<i class="bi bi-database-fill"></i>

<h4>MySQL</h4>

</div>

</div>

<div class="col-lg-3 col-md-6">

<div class="tech-card">

<i class="bi bi-bootstrap-fill"></i>

<h4>Bootstrap 5</h4>

</div>

</div>

<div class="col-lg-3 col-md-6">

<div class="tech-card">

<i class="bi bi-bar-chart-fill"></i>

<h4>Chart.js</h4>

</div>

</div>

</div>

</div>

</section>

<!-- ================= CTA ================= -->

<section class="py-5">

<div class="container">

<div class="cta-box text-center">

<h2 class="fw-bold">

Start Managing Your Money Today

</h2>

<p class="mt-3">

Join thousands of users tracking their finances smarter.

</p>

<div class="mt-4">

<a href="auth/register.php" class="btn btn-light btn-lg px-5 me-3">

Create Account

</a>

<a href="auth/login.php" class="btn btn-outline-light btn-lg px-5">

Login

</a>

</div>

</div>

</div>

</section>

<style>

.feature-card{

background:rgba(255,255,255,.12);

backdrop-filter:blur(15px);

border-radius:20px;

padding:35px;

text-align:center;

height:100%;

transition:.35s;

border:1px solid rgba(255,255,255,.15);

}

.feature-card:hover{

transform:translateY(-10px);

box-shadow:0 20px 45px rgba(0,0,0,.35);

}

.feature-icon{

width:80px;

height:80px;

border-radius:50%;

display:flex;

align-items:center;

justify-content:center;

margin:auto;

font-size:35px;

color:#fff;

margin-bottom:20px;

}

.stats-box{

background:rgba(255,255,255,.12);

padding:35px;

border-radius:20px;

backdrop-filter:blur(15px);

}

.stats-box h2{

font-size:45px;

font-weight:700;

}

.tech-card{

background:rgba(255,255,255,.12);

padding:35px;

border-radius:20px;

text-align:center;

height:100%;

transition:.35s;

}

.tech-card:hover{

transform:translateY(-10px);

}

.tech-card i{

font-size:55px;

color:#ffd166;

margin-bottom:20px;

}

.cta-box{

background:linear-gradient(135deg,#2563eb,#4f46e5);

padding:70px 40px;

border-radius:25px;

box-shadow:0 20px 45px rgba(0,0,0,.35);

}

</style><!-- ================= CONTACT ================= -->

<section id="contact" class="py-5">

<div class="container">

<div class="text-center mb-5">

<h2 class="display-5 fw-bold">

Contact Us

</h2>

<p class="text-light">

Have any questions? We'd love to hear from you.

</p>

</div>

<div class="row g-4">

<div class="col-lg-6">

<div class="contact-card">

<h3 class="mb-4">

Get In Touch

</h3>

<p>

<i class="bi bi-envelope-fill me-2"></i>

support@expensemanager.com

</p>

<p>

<i class="bi bi-telephone-fill me-2"></i>

+91 9876543210

</p>

<p>

<i class="bi bi-geo-alt-fill me-2"></i>

Pune, Maharashtra

</p>

<div class="social-icons mt-4">

<a href="#"><i class="bi bi-facebook"></i></a>

<a href="#"><i class="bi bi-instagram"></i></a>

<a href="#"><i class="bi bi-twitter-x"></i></a>

<a href="#"><i class="bi bi-linkedin"></i></a>

<a href="#"><i class="bi bi-github"></i></a>

</div>

</div>

</div>

<div class="col-lg-6">

<div class="contact-card">

<form>

<div class="mb-3">

<input
type="text"
class="form-control"
placeholder="Your Name">

</div>

<div class="mb-3">

<input
type="email"
class="form-control"
placeholder="Email Address">

</div>

<div class="mb-3">

<textarea
class="form-control"
rows="5"
placeholder="Your Message"></textarea>

</div>

<button
type="submit"
class="btn btn-primary w-100">

Send Message

</button>

</form>

</div>

</div>

</div>

</div>

</section>

<!-- ================= FOOTER ================= -->

<footer class="footer">

<div class="container">

<div class="row">

<div class="col-lg-4">

<h3>

💰 My Expense Manager

</h3>

<p>

Track your income, expenses and achieve your financial goals easily.

</p>

</div>

<div class="col-lg-4">

<h5>

Quick Links

</h5>

<ul class="footer-links">

<li>

<a href="#home">

Home

</a>

</li>

<li>

<a href="#features">

Features

</a>

</li>

<li>

<a href="#about">

About

</a>

</li>

<li>

<a href="#contact">

Contact

</a>

</li>

</ul>

</div>

<div class="col-lg-4">

<h5>

Account

</h5>

<ul class="footer-links">

<li>

<a href="auth/login.php">

Login

</a>

</li>

<li>

<a href="auth/register.php">

Register

</a>

</li>

</ul>

</div>

</div>

<hr class="border-light">

<div class="text-center">

<p class="mb-0">

© <span id="year"></span>

My Expense Manager

|

Designed by

<strong>Kunal Jawade</strong>

</p>

</div>

</div>

</footer>

<style>

/* CONTACT */

.contact-card{

background:rgba(255,255,255,.12);

padding:35px;

border-radius:20px;

backdrop-filter:blur(15px);

height:100%;

border:1px solid rgba(255,255,255,.15);

}

.contact-card p{

font-size:18px;

}

.form-control{

background:rgba(255,255,255,.12);

border:none;

color:#fff;

}

.form-control::placeholder{

color:#ddd;

}

.form-control:focus{

background:rgba(255,255,255,.20);

color:#fff;

box-shadow:none;

}

.social-icons a{

display:inline-flex;

align-items:center;

justify-content:center;

width:45px;

height:45px;

border-radius:50%;

background:#fff;

color:#2563eb;

margin-right:10px;

font-size:20px;

transition:.3s;

text-decoration:none;

}

.social-icons a:hover{

background:#2563eb;

color:#fff;

transform:translateY(-5px);

}

/* FOOTER */

.footer{

background:#0f172a;

padding:60px 0;

margin-top:50px;

}

.footer-links{

list-style:none;

padding:0;

}

.footer-links li{

margin-bottom:10px;

}

.footer-links a{

color:#ddd;

text-decoration:none;

transition:.3s;

}

.footer-links a:hover{

color:#ffffff;

padding-left:8px;

}

</style>

<!-- Bootstrap JS -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>

document.getElementById("year").innerHTML = new Date().getFullYear();

</script>

</body>

</html>