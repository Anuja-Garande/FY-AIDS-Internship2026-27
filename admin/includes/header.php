<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/functions.php';
requireAdmin();
$flash = getFlash();
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= isset($pageTitle) ? h($pageTitle) . ' | Admin' : 'Admin Panel' ?> | BharatYatra</title>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:wght@600;700&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
<link rel="stylesheet" href="/tourism-portal/assets/css/style.css">
</head>
<body class="admin-body">
<div class="d-flex">
  <!-- Sidebar -->
  <div class="admin-sidebar" style="width:260px;">
    <div class="brand"><i class="bi bi-compass-fill"></i> Bharat<span>Yatra</span></div>
    <a href="/tourism-portal/admin/dashboard.php" class="<?= $currentPage=='dashboard.php'?'active':'' ?>"><i class="bi bi-speedometer2"></i> Dashboard</a>
    <a href="/tourism-portal/admin/states.php" class="<?= $currentPage=='states.php'?'active':'' ?>"><i class="bi bi-flag"></i> States</a>
    <a href="/tourism-portal/admin/destinations.php" class="<?= $currentPage=='destinations.php'?'active':'' ?>"><i class="bi bi-geo-alt"></i> Destinations</a>
    <a href="/tourism-portal/admin/hotels.php" class="<?= $currentPage=='hotels.php'?'active':'' ?>"><i class="bi bi-building"></i> Hotels</a>
    <a href="/tourism-portal/admin/restaurants.php" class="<?= $currentPage=='restaurants.php'?'active':'' ?>"><i class="bi bi-cup-hot"></i> Restaurants</a>
    <a href="/tourism-portal/admin/packages.php" class="<?= $currentPage=='packages.php'?'active':'' ?>"><i class="bi bi-suitcase-lg"></i> Tour Packages</a>
    <a href="/tourism-portal/admin/bookings.php" class="<?= $currentPage=='bookings.php'?'active':'' ?>"><i class="bi bi-journal-check"></i> Bookings</a>
    <a href="/tourism-portal/admin/reviews.php" class="<?= $currentPage=='reviews.php'?'active':'' ?>"><i class="bi bi-star"></i> Reviews</a>
    <a href="/tourism-portal/admin/contact-messages.php" class="<?= $currentPage=='contact-messages.php'?'active':'' ?>"><i class="bi bi-envelope"></i> Contact Messages</a>
    <a href="/tourism-portal/admin/users.php" class="<?= $currentPage=='users.php'?'active':'' ?>"><i class="bi bi-people"></i> Users</a>
    <a href="/tourism-portal/admin/profile.php" class="<?= $currentPage=='profile.php'?'active':'' ?>"><i class="bi bi-person-gear"></i> My Profile</a>
    <hr style="border-color:rgba(255,255,255,0.1);margin:16px 24px;">
    <a href="/tourism-portal/index.php" target="_blank"><i class="bi bi-box-arrow-up-right"></i> View Website</a>
    <a href="/tourism-portal/admin/logout.php" class="text-danger"><i class="bi bi-box-arrow-right"></i> Logout</a>
  </div>

  <!-- Main content -->
  <div class="flex-grow-1">
    <div class="admin-topbar">
      <h5 class="mb-0"><?= isset($pageTitle) ? h($pageTitle) : 'Dashboard' ?></h5>
      <div class="d-flex align-items-center gap-2">
        <i class="bi bi-person-circle fs-4" style="color:var(--primary);"></i>
        <span><?= h($_SESSION['admin_username'] ?? 'Admin') ?></span>
      </div>
    </div>
    <div class="p-4">
      <?php if ($flash): ?>
      <div class="alert alert-<?= $flash['type'] ?> alert-dismissible fade show">
        <?= h($flash['message']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
      <?php endif; ?>
