<?php
// C:\xampp\htdocs\NewProject\includes\header.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Helper to check active navigation tab
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <script>
        // Immediately set theme to avoid flashing on reload
        (function () {
            const savedTheme = localStorage.getItem('theme') || 'light';
            document.documentElement.setAttribute('data-bs-theme', savedTheme);
        })();
    </script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Explore global tourist destinations, plan customized itineraries, and read review guides at Tourist Portal.">
    <title>Tourist Guide & Destination Portal</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Custom Style Sheet -->
    <link href="assets/css/style.css?v=<?php echo time(); ?>" rel="stylesheet">
</head>
<body>

<!-- Dynamic Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark navbar-custom sticky-top">
    <div class="container">
        <a class="navbar-brand" href="index.php">
            <i class="bi-airplane-fill me-2 text-warning"></i>Travel<span>Portal</span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#userNavbar" aria-controls="userNavbar" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="userNavbar">
            <ul class="navbar-nav navbar-nav-scrollable me-auto my-2 my-lg-0">
                <li class="nav-item">
                    <a class="nav-link <?php echo $current_page === 'index.php' ? 'active' : ''; ?>" href="index.php">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $current_page === 'destinations.php' || $current_page === 'destination-detail.php' ? 'active' : ''; ?>" href="destinations.php">Destinations</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $current_page === 'guides.php' ? 'active' : ''; ?>" href="guides.php">Guides</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $current_page === 'packages.php' || $current_page === 'package-detail.php' ? 'active' : ''; ?>" href="packages.php">Packages</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $current_page === 'offers.php' ? 'active' : ''; ?>" href="offers.php">Offers <span class="badge bg-danger rounded-pill ms-1 small">Deals</span></a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $current_page === 'hotels.php' || $current_page === 'hotel-detail.php' ? 'active' : ''; ?>" href="hotels.php">Hotels</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $current_page === 'restaurants.php' ? 'active' : ''; ?>" href="restaurants.php">Restaurants</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $current_page === 'faq.php' ? 'active' : ''; ?>" href="faq.php">FAQ</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $current_page === 'contact.php' ? 'active' : ''; ?>" href="contact.php">About Us</a>
                </li>
            </ul>
            <div class="d-flex align-items-center gap-2 flex-shrink-0 ms-auto">
                <!-- Theme Toggle Button -->
                <button id="themeToggle" class="btn btn-outline-light btn-sm rounded-circle px-2 py-1" title="Toggle Dark/Light Mode">
                    <i class="bi-moon-fill" id="themeIcon"></i>
                </button>

                <?php if (isset($_SESSION['user_id'])): 
                    $unreadCount = function_exists('get_unread_notifications_count') && isset($pdo) ? get_unread_notifications_count($pdo, $_SESSION['user_id']) : 0;
                    $headerNotifications = function_exists('get_user_notifications') && isset($pdo) ? get_user_notifications($pdo, $_SESSION['user_id'], 5) : [];
                ?>
                    <!-- Notification Bell Dropdown -->
                    <div class="dropdown me-1">
                        <button class="btn btn-outline-light btn-sm position-relative rounded-circle px-2 py-1" type="button" id="notifDropdown" data-bs-toggle="dropdown" aria-expanded="false" title="Notifications">
                            <i class="bi-bell-fill"></i>
                            <?php if ($unreadCount > 0): ?>
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                    <?php echo $unreadCount > 9 ? '9+' : $unreadCount; ?>
                                </span>
                            <?php endif; ?>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 rounded-3 mt-2" aria-labelledby="notifDropdown" style="width: 320px; max-height: 400px; overflow-y: auto;">
                            <li class="dropdown-header d-flex justify-content-between align-items-center bg-light fw-bold text-dark py-2 px-3">
                                <span><i class="bi-bell me-1 text-primary"></i> Notifications</span>
                                <a href="notifications.php" class="small text-primary text-decoration-none">View All</a>
                            </li>
                            <li><hr class="dropdown-divider m-0"></li>
                            <?php if (!empty($headerNotifications)): ?>
                                <?php foreach ($headerNotifications as $notif): ?>
                                    <li class="px-3 py-2 border-bottom <?php echo $notif['is_read'] ? '' : 'bg-light'; ?>">
                                        <div class="small fw-semibold text-dark mb-1"><?php echo htmlspecialchars($notif['message']); ?></div>
                                        <div class="text-muted" style="font-size: 0.75rem;"><?php echo date('M d, g:i a', strtotime($notif['created_at'])); ?></div>
                                    </li>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <li class="px-3 py-3 text-center text-muted small">No notifications yet</li>
                            <?php endif; ?>
                            <li>
                                <a class="dropdown-item text-center small text-primary fw-bold py-2" href="notifications.php">Open Notification Center <i class="bi-chevron-right small"></i></a>
                            </li>
                        </ul>
                    </div>

                    <span class="text-white-50 small me-2 d-none d-xl-inline">
                        Welcome, <strong class="text-white"><?php echo htmlspecialchars($_SESSION['user_name']); ?></strong>
                    </span>
                    <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                        <a href="admin/index.php" class="btn btn-warning btn-sm rounded-pill px-3 fw-bold text-dark">
                            <i class="bi-shield-lock-fill me-1"></i>Admin
                        </a>
                    <?php endif; ?>
                    <a href="profile.php" class="btn btn-outline-light btn-sm rounded-pill px-3 <?php echo $current_page === 'profile.php' ? 'active' : ''; ?>">
                        <i class="bi-person-circle me-1"></i>Profile
                    </a>
                    <a href="logout.php" class="btn btn-danger btn-sm rounded-pill px-3">
                        <i class="bi-box-arrow-right me-1"></i>Logout
                    </a>
                <?php else: ?>
                    <a href="login.php" class="btn btn-outline-light btn-sm rounded-pill px-3 me-1 fw-bold">Log In</a>
                    <a href="register.php" class="btn btn-auth btn-sm rounded-pill px-3">Sign Up</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>

<!-- Alert Messaging System for Notifications -->
<div class="container mt-3">
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi-check-circle-fill me-2"></i><?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi-exclamation-triangle-fill me-2"></i><?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
</div>
