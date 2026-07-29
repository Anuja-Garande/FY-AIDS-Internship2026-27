<?php
// C:\xampp\htdocs\NewProject\admin\includes\admin_header.php
// Admin Panel Header layout and Security Guard

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
ob_start();

$current_admin_page = basename($_SERVER['PHP_SELF']);

// Security Session Guard: Redirect to login if not logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    if ($current_admin_page !== 'login.php') {
        header("Location: login.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TravelPortal Admin Panel</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Custom Style Sheet -->
    <link href="../assets/css/style.css?v=<?php echo time(); ?>" rel="stylesheet">
</head>
<body class="bg-light">

<?php if ($current_admin_page !== 'login.php'): ?>
<div class="container-fluid">
    <div class="row">
        <!-- Admin Sidebar Nav (col-md-3 col-lg-2) -->
        <div class="col-md-3 col-lg-2 admin-sidebar px-0 position-fixed start-0 top-0 bottom-0 d-flex flex-column justify-content-between py-4">
            <div>
                <!-- Brand Title -->
                <div class="text-center mb-4 px-3">
                    <a href="index.php" class="text-white text-decoration-none fw-bold fs-4">
                        <i class="bi-shield-lock-fill text-warning me-2"></i>Admin<span>Portal</span>
                    </a>
                </div>
                
                <!-- Navigation Items -->
                <nav class="nav flex-column px-3 overflow-auto" style="max-height: calc(100vh - 180px);">
                    <a class="nav-link <?php echo $current_admin_page === 'index.php' ? 'active' : ''; ?>" href="index.php">
                        <i class="bi-speedometer2"></i> Dashboard
                    </a>
                    <a class="nav-link <?php echo $current_admin_page === 'bookings.php' ? 'active' : ''; ?>" href="bookings.php">
                        <i class="bi-ticket-perforated-fill text-warning"></i> Bookings
                    </a>
                    <a class="nav-link <?php echo $current_admin_page === 'destinations.php' ? 'active' : ''; ?>" href="destinations.php">
                        <i class="bi-geo-alt-fill"></i> Destinations
                    </a>
                    <a class="nav-link <?php echo $current_admin_page === 'packages.php' ? 'active' : ''; ?>" href="packages.php">
                        <i class="bi-box-seam-fill"></i> Tour Packages
                    </a>
                    <a class="nav-link <?php echo in_array($current_admin_page, ['offers.php', 'coupons.php']) ? 'active' : ''; ?>" href="offers.php">
                        <i class="bi-percent"></i> Offers & Coupons
                    </a>
                    <a class="nav-link <?php echo $current_admin_page === 'hotels.php' ? 'active' : ''; ?>" href="hotels.php">
                        <i class="bi-building"></i> Hotels
                    </a>
                    <a class="nav-link <?php echo $current_admin_page === 'restaurants.php' ? 'active' : ''; ?>" href="restaurants.php">
                        <i class="bi-cup-hot-fill"></i> Restaurants
                    </a>
                    <a class="nav-link <?php echo $current_admin_page === 'nearby_places.php' ? 'active' : ''; ?>" href="nearby_places.php">
                        <i class="bi-pin-map-fill"></i> Nearby Places
                    </a>
                    <a class="nav-link <?php echo $current_admin_page === 'guides.php' ? 'active' : ''; ?>" href="guides.php">
                        <i class="bi-person-badge-fill"></i> Tour Guides
                    </a>
                    <a class="nav-link <?php echo $current_admin_page === 'categories.php' ? 'active' : ''; ?>" href="categories.php">
                        <i class="bi-grid-fill"></i> Categories
                    </a>
                    <a class="nav-link <?php echo $current_admin_page === 'faqs.php' ? 'active' : ''; ?>" href="faqs.php">
                        <i class="bi-question-circle-fill"></i> FAQs
                    </a>
                    <a class="nav-link <?php echo $current_admin_page === 'users.php' ? 'active' : ''; ?>" href="users.php">
                        <i class="bi-people-fill"></i> Users List
                    </a>
                    <a class="nav-link <?php echo $current_admin_page === 'reviews.php' ? 'active' : ''; ?>" href="reviews.php">
                        <i class="bi-chat-left-text-fill"></i> Reviews
                    </a>
                    <a class="nav-link <?php echo $current_admin_page === 'messages.php' ? 'active' : ''; ?>" href="messages.php">
                        <i class="bi-envelope-paper-fill"></i> Messages
                    </a>
                </nav>
            </div>
            
            <!-- Sidebar Footer Controls -->
            <div class="px-3">
                <a href="../index.php" class="btn btn-outline-light btn-sm w-100 mb-2 rounded-pill" target="_blank">
                    <i class="bi-eye me-1"></i> View Website
                </a>
                <a href="logout.php" class="btn btn-danger btn-sm w-100 rounded-pill">
                    <i class="bi-box-arrow-right me-1"></i> Log Out
                </a>
            </div>
        </div>

        <!-- Main Body Wrapper (Offset by Sidebar width) -->
        <div class="col-md-9 offset-md-3 col-lg-10 offset-lg-2 px-0">
            <!-- Top Navbar Bar -->
            <header class="admin-header-bar d-flex justify-content-between align-items-center mb-4">
                <div class="d-flex align-items-center">
                    <h5 class="fw-bold mb-0 text-dark">
                        <?php 
                        // Show Title depending on page
                        switch ($current_admin_page) {
                            case 'index.php': echo 'Dashboard Statistics'; break;
                            case 'bookings.php': echo 'Manage Bookings'; break;
                            case 'destinations.php': echo 'Manage Destinations'; break;
                            case 'packages.php': echo 'Manage Tour Packages'; break;
                            case 'offers.php': echo 'Manage Offers & Coupons'; break;
                            case 'hotels.php': echo 'Manage Hotels'; break;
                            case 'restaurants.php': echo 'Manage Restaurants'; break;
                            case 'nearby_places.php': echo 'Manage Nearby Attractions'; break;
                            case 'guides.php': echo 'Manage Tour Guides'; break;
                            case 'categories.php': echo 'Manage Categories'; break;
                            case 'faqs.php': echo 'Manage FAQs'; break;
                            case 'users.php': echo 'Manage Users'; break;
                            case 'reviews.php': echo 'Moderate Reviews'; break;
                            case 'messages.php': echo 'Contact Messages'; break;
                            default: echo 'Admin Dashboard';
                        }
                        ?>
                    </h5>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="text-muted small">Logged in as:</span>
                    <strong class="text-primary"><i class="bi-person-badge-fill me-1"></i><?php echo htmlspecialchars($_SESSION['admin_username'] ?? 'Administrator'); ?></strong>
                </div>
            </header>
            
            <!-- Layout Alert Messages -->
            <div class="container-fluid px-4">
                <?php if (isset($_SESSION['admin_success'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="bi-check-circle-fill me-2"></i><?php echo $_SESSION['admin_success']; unset($_SESSION['admin_success']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <?php if (isset($_SESSION['admin_error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi-exclamation-triangle-fill me-2"></i><?php echo $_SESSION['admin_error']; unset($_SESSION['admin_error']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Page Content Injection Area -->
            <main class="container-fluid px-4 pb-5">
<?php endif; ?>
