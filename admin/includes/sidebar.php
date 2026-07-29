<?php
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
?>
<aside id="sidebar" class="sidebar">
    <div class="sidebar-brand">
        <a href="<?= BASE_URL ?>/admin/index.php" class="brand-link" style="display:flex;flex-direction:column;align-items:center;text-decoration:none;">
            <img src="<?= BASE_URL ?>assets/images/shopsphere logo.jpeg" alt="ShopSphere" style="height:28px;width:auto;">
            <small style="font-weight:800;font-size:0.75rem;color:var(--text-heading);margin-top:2px;">ShopSphere</small>
        </a>
    </div>
    <ul class="sidebar-nav" id="sidebarNav">
        <li class="nav-item <?= $currentPage === 'index' ? 'active' : '' ?>">
            <a href="<?= BASE_URL ?>/admin/index.php" class="nav-link">
                <i class="fas fa-tachometer-alt"></i><span>Dashboard</span>
            </a>
        </li>
        <li class="nav-item <?= in_array($currentPage, ['products','add_product','edit_product']) ? 'active open' : '' ?>">
            <a href="#productsSubmenu" class="nav-link has-submenu" data-bs-toggle="collapse" aria-expanded="<?= in_array($currentPage, ['products','add_product','edit_product']) ? 'true' : 'false' ?>">
                <i class="fas fa-box"></i><span>Products</span><i class="fas fa-chevron-down arrow"></i>
            </a>
            <ul class="submenu collapse <?= in_array($currentPage, ['products','add_product','edit_product']) ? 'show' : '' ?>" id="productsSubmenu">
                <li><a href="<?= BASE_URL ?>/admin/products.php" class="<?= $currentPage === 'products' ? 'active' : '' ?>">All Products</a></li>
                <li><a href="<?= BASE_URL ?>/admin/add_product.php" class="<?= $currentPage === 'add_product' ? 'active' : '' ?>">Add Product</a></li>
            </ul>
        </li>
        <li class="nav-item <?= in_array($currentPage, ['categories','add_category','edit_category']) ? 'active' : '' ?>">
            <a href="#categoriesSubmenu" class="nav-link has-submenu" data-bs-toggle="collapse" aria-expanded="<?= in_array($currentPage, ['categories','add_category','edit_category']) ? 'true' : 'false' ?>">
                <i class="fas fa-tags"></i><span>Categories</span><i class="fas fa-chevron-down arrow"></i>
            </a>
            <ul class="submenu collapse <?= in_array($currentPage, ['categories','add_category','edit_category']) ? 'show' : '' ?>" id="categoriesSubmenu">
                <li><a href="<?= BASE_URL ?>/admin/categories.php" class="<?= $currentPage === 'categories' ? 'active' : '' ?>">All Categories</a></li>
                <li><a href="<?= BASE_URL ?>/admin/add_category.php" class="<?= $currentPage === 'add_category' ? 'active' : '' ?>">Add Category</a></li>
            </ul>
        </li>
        <li class="nav-item <?= in_array($currentPage, ['brands','add_brand','edit_brand']) ? 'active' : '' ?>">
            <a href="#brandsSubmenu" class="nav-link has-submenu" data-bs-toggle="collapse" aria-expanded="<?= in_array($currentPage, ['brands','add_brand','edit_brand']) ? 'true' : 'false' ?>">
                <i class="fas fa-trademark"></i><span>Brands</span><i class="fas fa-chevron-down arrow"></i>
            </a>
            <ul class="submenu collapse <?= in_array($currentPage, ['brands','add_brand','edit_brand']) ? 'show' : '' ?>" id="brandsSubmenu">
                <li><a href="<?= BASE_URL ?>/admin/brands.php" class="<?= $currentPage === 'brands' ? 'active' : '' ?>">All Brands</a></li>
                <li><a href="<?= BASE_URL ?>/admin/add_brand.php" class="<?= $currentPage === 'add_brand' ? 'active' : '' ?>">Add Brand</a></li>
            </ul>
        </li>
        <li class="nav-item <?= in_array($currentPage, ['orders','view_order']) ? 'active' : '' ?>">
            <a href="<?= BASE_URL ?>/admin/orders.php" class="nav-link">
                <i class="fas fa-shopping-cart"></i><span>Orders</span>
            </a>
        </li>
        <li class="nav-item <?= in_array($currentPage, ['flight_bookings','view_flight_booking']) ? 'active' : '' ?>">
            <a href="<?= BASE_URL ?>/admin/flight_bookings.php" class="nav-link">
                <i class="fas fa-plane"></i><span>Flight Bookings</span>
            </a>
        </li>
        <li class="nav-item <?= in_array($currentPage, ['coupons','add_coupon','edit_coupon']) ? 'active' : '' ?>">
            <a href="#couponsSubmenu" class="nav-link has-submenu" data-bs-toggle="collapse" aria-expanded="<?= in_array($currentPage, ['coupons','add_coupon','edit_coupon']) ? 'true' : 'false' ?>">
                <i class="fas fa-ticket-alt"></i><span>Coupons</span><i class="fas fa-chevron-down arrow"></i>
            </a>
            <ul class="submenu collapse <?= in_array($currentPage, ['coupons','add_coupon','edit_coupon']) ? 'show' : '' ?>" id="couponsSubmenu">
                <li><a href="<?= BASE_URL ?>/admin/coupons.php" class="<?= $currentPage === 'coupons' ? 'active' : '' ?>">All Coupons</a></li>
                <li><a href="<?= BASE_URL ?>/admin/add_coupon.php" class="<?= $currentPage === 'add_coupon' ? 'active' : '' ?>">Add Coupon</a></li>
            </ul>
        </li>
        <li class="nav-item <?= in_array($currentPage, ['banners','add_banner','edit_banner']) ? 'active' : '' ?>">
            <a href="#bannersSubmenu" class="nav-link has-submenu" data-bs-toggle="collapse" aria-expanded="<?= in_array($currentPage, ['banners','add_banner','edit_banner']) ? 'true' : 'false' ?>">
                <i class="fas fa-image"></i><span>Banners</span><i class="fas fa-chevron-down arrow"></i>
            </a>
            <ul class="submenu collapse <?= in_array($currentPage, ['banners','add_banner','edit_banner']) ? 'show' : '' ?>" id="bannersSubmenu">
                <li><a href="<?= BASE_URL ?>/admin/banners.php" class="<?= $currentPage === 'banners' ? 'active' : '' ?>">All Banners</a></li>
                <li><a href="<?= BASE_URL ?>/admin/add_banner.php" class="<?= $currentPage === 'add_banner' ? 'active' : '' ?>">Add Banner</a></li>
            </ul>
        </li>
        <li class="nav-item <?= $currentPage === 'reviews' ? 'active' : '' ?>">
            <a href="<?= BASE_URL ?>/admin/reviews.php" class="nav-link">
                <i class="fas fa-star"></i><span>Reviews</span>
            </a>
        </li>
        <li class="nav-item <?= in_array($currentPage, ['admins','add_admin']) ? 'active' : '' ?>">
            <a href="#adminsSubmenu" class="nav-link has-submenu" data-bs-toggle="collapse" aria-expanded="<?= in_array($currentPage, ['admins','add_admin']) ? 'true' : 'false' ?>">
                <i class="fas fa-user-shield"></i><span>Admins</span><i class="fas fa-chevron-down arrow"></i>
            </a>
            <ul class="submenu collapse <?= in_array($currentPage, ['admins','add_admin']) ? 'show' : '' ?>" id="adminsSubmenu">
                <li><a href="<?= BASE_URL ?>/admin/admins.php" class="<?= $currentPage === 'admins' ? 'active' : '' ?>">All Admins</a></li>
                <li><a href="<?= BASE_URL ?>/admin/add_admin.php" class="<?= $currentPage === 'add_admin' ? 'active' : '' ?>">Add Admin</a></li>
            </ul>
        </li>
        <li class="nav-item <?= $currentPage === 'users' ? 'active' : '' ?>">
            <a href="<?= BASE_URL ?>/admin/users.php" class="nav-link">
                <i class="fas fa-users"></i><span>Users</span>
            </a>
        </li>
        <li class="nav-item <?= $currentPage === 'notifications' ? 'active' : '' ?>">
            <a href="<?= BASE_URL ?>/admin/notifications.php" class="nav-link">
                <i class="fas fa-bell"></i><span>Notifications</span>
            </a>
        </li>
        <li class="nav-item <?= $currentPage === 'reports' ? 'active' : '' ?>">
            <a href="<?= BASE_URL ?>/admin/reports.php" class="nav-link">
                <i class="fas fa-chart-bar"></i><span>Reports</span>
            </a>
        </li>
        <li class="nav-item <?= $currentPage === 'settings' ? 'active' : '' ?>">
            <a href="<?= BASE_URL ?>/admin/settings.php" class="nav-link">
                <i class="fas fa-cog"></i><span>Settings</span>
            </a>
        </li>
    </ul>
</aside>
