<div class="sidebar">

<h2><i class="fas fa-hotel"></i> Hotel Admin</h2>

<a href="dashboard.php" class="<?= ($current_page=='dashboard') ? 'active' : ''; ?>">
    <i class="fas fa-chart-line"></i>
    Dashboard
</a>

<a href="rooms.php" class="<?= ($current_page=='rooms') ? 'active' : ''; ?>">
    <i class="fas fa-bed"></i>
    Manage Rooms
</a>

<a href="users.php" class="<?= ($current_page=='users') ? 'active' : ''; ?>">
    <i class="fas fa-users"></i>
    Manage Users
</a>

<a href="bookings.php" class="<?= ($current_page=='bookings') ? 'active' : ''; ?>">
    <i class="fas fa-calendar-check"></i>
    Manage Bookings
</a>

<a href="payments.php" class="<?= ($current_page=='payments') ? 'active' : ''; ?>">
    <i class="fas fa-credit-card"></i>
    Payments
</a>

<a href="reports.php" class="<?= ($current_page=='reports') ? 'active' : ''; ?>">
    <i class="fas fa-chart-pie"></i>
    Reports
</a>

<a href="profile.php" class="<?= ($current_page=='profile') ? 'active' : ''; ?>">
    <i class="fas fa-user-circle"></i>
    Profile
</a>

<a href="../auth/logout.php">
    <i class="fas fa-right-from-bracket"></i>
    Logout
</a>

</div> 