<?php
require_once __DIR__ . '/functions.php';
$flash = getFlash();
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= isset($pageTitle) ? h($pageTitle) . ' | Bharat Yatra' : 'Bharat Yatra | Incredible India Tourism Portal' ?></title>
<meta name="description" content="Discover India's most beautiful states and destinations, plan tour packages, book hotels and share your travel stories.">

<!-- Fonts -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,600;9..144,700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<!-- Bootstrap 5 -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<!-- Bootstrap Icons -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
<!-- AOS scroll animation -->
<link href="https://cdn.jsdelivr.net/npm/aos@2.3.1/dist/aos.css" rel="stylesheet">

<!-- Site styles -->
<link rel="stylesheet" href="/tourism-portal/assets/css/style.css">
</head>
<body>

<!-- ===== Page loader ===== -->
<div id="pageLoader">
  <div class="loader-compass"><i class="bi bi-compass"></i></div>
</div>

<!-- ===== Top info bar ===== -->
<div class="top-bar d-none d-md-flex">
  <div class="container d-flex justify-content-between align-items-center">
    <span><i class="bi bi-telephone-fill"></i> +91-90000-00000 &nbsp; <i class="bi bi-envelope-fill"></i> hello@bharatyatra.in</span>
    <span class="top-bar-tagline">"Atithi Devo Bhava" — The Guest is God</span>
  </div>
</div>

<!-- ===== Navbar ===== -->
<nav class="navbar navbar-expand-lg main-navbar sticky-top">
  <div class="container">
    <a class="navbar-brand" href="/tourism-portal/index.php">
      <i class="bi bi-compass-fill brand-icon"></i> Bharat<span>Yatra</span>
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="mainNav">
      <ul class="navbar-nav mx-auto">
        <li class="nav-item"><a class="nav-link <?= $currentPage=='index.php'?'active':'' ?>" href="/tourism-portal/index.php">Home</a></li>
        <li class="nav-item"><a class="nav-link <?= $currentPage=='destinations.php'?'active':'' ?>" href="/tourism-portal/destinations.php">Destinations</a></li>
        <li class="nav-item"><a class="nav-link <?= $currentPage=='hotels.php'?'active':'' ?>" href="/tourism-portal/hotels.php">Hotels</a></li>
        <li class="nav-item"><a class="nav-link <?= $currentPage=='restaurants.php'?'active':'' ?>" href="/tourism-portal/restaurants.php">Restaurants</a></li>
        <li class="nav-item"><a class="nav-link <?= $currentPage=='packages.php'?'active':'' ?>" href="/tourism-portal/packages.php">Packages</a></li>
        <li class="nav-item"><a class="nav-link <?= $currentPage=='about.php'?'active':'' ?>" href="/tourism-portal/about.php">About</a></li>
        <li class="nav-item"><a class="nav-link <?= $currentPage=='contact.php'?'active':'' ?>" href="/tourism-portal/contact.php">Contact</a></li>
      </ul>
      <div class="d-flex align-items-center gap-2 nav-actions">
        <?php if (isLoggedIn()): ?>
          <a href="/tourism-portal/wishlist.php" class="btn btn-icon" title="Wishlist"><i class="bi bi-heart"></i></a>
          <div class="dropdown">
            <a class="btn btn-user dropdown-toggle" href="#" data-bs-toggle="dropdown">
              <i class="bi bi-person-circle"></i> <?= h($_SESSION['user_name'] ?? 'Account') ?>
            </a>
            <ul class="dropdown-menu dropdown-menu-end">
              <li><a class="dropdown-item" href="/tourism-portal/dashboard.php">My Dashboard</a></li>
              <li><a class="dropdown-item" href="/tourism-portal/my-bookings.php">My Bookings</a></li>
              <li><a class="dropdown-item" href="/tourism-portal/wishlist.php">My Wishlist</a></li>
              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item text-danger" href="/tourism-portal/logout.php">Logout</a></li>
            </ul>
          </div>
        <?php else: ?>
          <a href="/tourism-portal/login.php" class="btn btn-outline-nav">Login</a>
          <a href="/tourism-portal/register.php" class="btn btn-solid-nav">Sign Up</a>
        <?php endif; ?>
      </div>
    </div>
  </div>
</nav>

<?php if ($flash): ?>
<div class="container mt-3">
  <div class="alert alert-<?= $flash['type'] ?> alert-dismissible fade show" role="alert">
    <?= h($flash['message']) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
</div>
<?php endif; ?>
