<?php
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
$adminName = $_SESSION['admin_name'] ?? 'Admin';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'Admin Panel' ?> - ShopSphere</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="<?= BASE_URL ?>/admin/assets/css/admin.css" rel="stylesheet">
    <style>
        * { font-family: 'Inter', sans-serif; }
        body { background: #f4f6f9; overflow-x: hidden; }
        .sidebar { position: fixed; top: 0; left: 0; width: 260px; height: 100vh; background: linear-gradient(180deg, #1a1a2e 0%, #16213e 100%); z-index: 1040; transition: all 0.3s; overflow-y: auto; }
        .sidebar.collapsed { margin-left: -260px; }
        .sidebar-brand { padding: 20px; text-align: center; border-bottom: 1px solid rgba(255,255,255,0.1); }
        .sidebar-brand .brand-link { text-decoration: none; color: #fff; display: flex; align-items: center; justify-content: center; gap: 10px; }
        .sidebar-brand .brand-icon { font-size: 28px; color: #e94560; }
        .sidebar-brand .brand-text { font-size: 20px; font-weight: 700; }
        .sidebar-nav { list-style: none; padding: 10px 0; margin: 0; }
        .sidebar-nav .nav-item { margin: 2px 10px; }
        .sidebar-nav .nav-link { display: flex; align-items: center; padding: 10px 15px; color: rgba(255,255,255,0.7); text-decoration: none; border-radius: 8px; transition: all 0.3s; font-size: 14px; gap: 12px; }
        .sidebar-nav .nav-link:hover { background: rgba(233,69,96,0.2); color: #fff; }
        .sidebar-nav .nav-item.active > .nav-link { background: #e94560; color: #fff; }
        .sidebar-nav .nav-link i { width: 20px; text-align: center; font-size: 16px; }
        .sidebar-nav .nav-link .arrow { margin-left: auto; font-size: 12px; transition: transform 0.3s; }
        .sidebar-nav .nav-item.open > .nav-link .arrow { transform: rotate(180deg); }
        .submenu { list-style: none; padding: 0 0 0 47px; }
        .submenu li a { display: block; padding: 8px 15px; color: rgba(255,255,255,0.6); text-decoration: none; border-radius: 6px; font-size: 13px; transition: all 0.3s; margin: 2px 0; }
        .submenu li a:hover, .submenu li a.active { background: rgba(233,69,96,0.15); color: #fff; }
        .main-content { margin-left: 260px; transition: all 0.3s; min-height: 100vh; }
        .topbar { background: #fff; padding: 12px 25px; display: flex; align-items: center; justify-content: space-between; box-shadow: 0 2px 4px rgba(0,0,0,0.05); position: sticky; top: 0; z-index: 1030; }
        .topbar .toggle-btn { background: none; border: none; font-size: 20px; color: #333; cursor: pointer; padding: 5px; }
        .topbar .admin-info { display: flex; align-items: center; gap: 15px; }
        .topbar .admin-info .admin-avatar { width: 36px; height: 36px; border-radius: 50%; background: #e94560; color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 600; font-size: 14px; }
        .topbar .admin-info .dropdown-menu { border: none; box-shadow: 0 5px 25px rgba(0,0,0,0.15); border-radius: 10px; }
        .topbar .admin-info .dropdown-item { padding: 8px 20px; font-size: 14px; }
        .topbar .admin-info .dropdown-item:hover { background: #f8f9fa; }
        .content-wrapper { padding: 25px; }
        .stat-card { background: #fff; border-radius: 12px; padding: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); transition: transform 0.3s; border-left: 4px solid; }
        .stat-card:hover { transform: translateY(-3px); }
        .stat-card .stat-icon { width: 50px; height: 50px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 22px; }
        .stat-card .stat-info h3 { font-size: 24px; font-weight: 700; margin: 0; }
        .stat-card .stat-info p { font-size: 13px; color: #888; margin: 0; }
        .card { border: none; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); }
        .card-header { background: #fff; border-bottom: 1px solid #eee; padding: 15px 20px; border-radius: 12px 12px 0 0 !important; }
        .table { margin-bottom: 0; }
        .table thead th { border-top: none; font-size: 13px; font-weight: 600; color: #666; text-transform: uppercase; letter-spacing: 0.5px; padding: 12px 15px; }
        .table tbody td { padding: 12px 15px; vertical-align: middle; font-size: 14px; }
        .badge { font-weight: 500; padding: 5px 10px; border-radius: 6px; font-size: 12px; }
        .btn-action { padding: 4px 10px; border-radius: 6px; font-size: 12px; }
        @media (max-width: 768px) { .sidebar { margin-left: -260px; } .sidebar.show { margin-left: 0; } .main-content { margin-left: 0; } }
    </style>
</head>
<body>
    <?php include __DIR__ . '/sidebar.php'; ?>
    <div class="main-content" id="mainContent">
        <div class="topbar">
            <div class="d-flex align-items-center gap-3">
                <button class="toggle-btn" onclick="toggleSidebar()"><i class="fas fa-bars"></i></button>
                <h5 class="mb-0 fw-bold" style="font-size:16px;"><?= $pageTitle ?? 'Dashboard' ?></h5>
            </div>
            <div class="admin-info">
                <div class="dropdown">
                    <a href="#" class="d-flex align-items-center text-decoration-none dropdown-toggle" data-bs-toggle="dropdown">
                        <div class="admin-avatar"><?= strtoupper(substr($adminName, 0, 1)) ?></div>
                        <span class="ms-2 d-none d-md-inline fw-semibold" style="font-size:14px;"><?= sanitize($adminName) ?></span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="#"><i class="fas fa-user me-2"></i>Profile</a></li>
                        <li><a class="dropdown-item" href="<?= BASE_URL ?>/admin/settings.php"><i class="fas fa-cog me-2"></i>Settings</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="<?= BASE_URL ?>/admin/logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="content-wrapper">
