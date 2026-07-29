<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>My Expense Manager</title>

<!-- Bootstrap CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<!-- Bootstrap Icons -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

<!-- Google Fonts -->
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<!-- Custom CSS -->
<link rel="stylesheet" href="assets/css/style.css">

</head>

<body>

<!-- Sidebar -->
<?php include("includes/sidebar.php"); ?>

<!-- ================= NAVBAR ================= -->

<nav class="navbar navbar-expand-lg custom-navbar shadow">

<div class="container-fluid">

    <!-- Logo -->

    <a class="navbar-brand d-flex align-items-center" href="dashboard.php">

        <div class="logo-box">
            <i class="bi bi-wallet2"></i>
        </div>

        <div class="ms-2">

            <h5 class="brand-title mb-0">
                My Expense Manager
            </h5>

            <small class="brand-subtitle">
                Track Your Money Smartly
            </small>

        </div>

    </a>

    <!-- Mobile Toggle -->

    <button class="navbar-toggler border-0 text-white"
            type="button"
            data-bs-toggle="collapse"
            data-bs-target="#navbarNav">

        <i class="bi bi-list fs-2"></i>

    </button>

    <div class="collapse navbar-collapse" id="navbarNav">

        <!-- Search -->

        <form class="search-box mx-auto">

            <div class="input-group">

                <span class="input-group-text">

                    <i class="bi bi-search"></i>

                </span>

                <input
                    type="search"
                    class="form-control"
                    placeholder="Search transactions...">

            </div>

        </form>

        <!-- Right Menu -->

        <ul class="navbar-nav align-items-center ms-auto">

            <!-- Notification -->

            <li class="nav-item me-3">

                <a class="nav-link position-relative" href="#">

                    <i class="bi bi-bell-fill fs-5"></i>

                    <span class="notification-badge">3</span>

                </a>

            </li>

            <!-- Profile -->

            <li class="nav-item dropdown">

                <a class="nav-link dropdown-toggle d-flex align-items-center"
                   href="#"
                   data-bs-toggle="dropdown">

                    <img
    src="https://ui-avatars.com/api/?name=<?php echo urlencode($_SESSION['user_name']); ?>&background=0D6EFD&color=fff"
    class="profile-img"
    alt="Profile">

                    <span class="ms-2 fw-semibold">
                        <?php echo $_SESSION['user_name']; ?>
                    </span>

                </a>

                <ul class="dropdown-menu dropdown-menu-end shadow">

                    <li>
                        <a class="dropdown-item" href="profile.php">
                            <i class="bi bi-person-circle"></i>
                            My Profile
                        </a>
                    </li>

                    <li>
                        <a class="dropdown-item" href="settings.php">
                            <i class="bi bi-gear"></i>
                            Settings
                        </a>
                    </li>

                    <li><hr class="dropdown-divider"></li>

                    <li>

                        <a class="dropdown-item text-danger"
                           href="auth/logout.php">

                            <i class="bi bi-box-arrow-right"></i>

                            Logout

                        </a>

                    </li>

                </ul>

            </li>

        </ul>

    </div>

</div>

</nav>
<!-- ================= MAIN CONTENT ================= -->

<main class="main-content">

    <div class="container-fluid">