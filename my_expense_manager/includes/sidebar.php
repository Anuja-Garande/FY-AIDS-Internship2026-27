<!-- Sidebar -->
<?php
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<div class="sidebar">

    <div class="sidebar-header">

        <div class="logo-circle">
            <i class="bi bi-wallet2"></i>
        </div>

        <h5>Expense Tracker</h5>

        <small>Personal Finance</small>

    </div>

    <ul class="sidebar-menu">

<li>
<a href="dashboard.php" <?= ($currentPage == 'dashboard.php') ? 'class="active"' : ''; ?>>
<i class="bi bi-grid-fill"></i> Dashboard
</a>
</li>

<li>
<a href="income.php" <?= ($currentPage == 'income.php') ? 'class="active"' : ''; ?>>
<i class="bi bi-cash-coin"></i> Income
</a>
</li>

<li>
<a href="expenses.php" <?= ($currentPage == 'expenses.php') ? 'class="active"' : ''; ?>>
<i class="bi bi-credit-card-2-front-fill"></i> Expenses
</a>
</li>

<li>
<a href="categories.php" <?= ($currentPage == 'categories.php') ? 'class="active"' : ''; ?>>
<i class="bi bi-tags-fill"></i> Categories
</a>
</li>

<li>
<a href="reports.php" <?= ($currentPage == 'reports.php') ? 'class="active"' : ''; ?>>
<i class="bi bi-bar-chart-fill"></i> Reports
</a>
</li>

<li>
<a href="profile.php" <?= ($currentPage == 'profile.php') ? 'class="active"' : ''; ?>>
<i class="bi bi-person-circle"></i> Profile
</a>
</li>

<li>
<a href="settings.php" <?= ($currentPage == 'settings.php') ? 'class="active"' : ''; ?>>
<i class="bi bi-gear-fill"></i> Settings
</a>
</li>

<li>
<a href="export_excel.php" <?= ($currentPage == 'export_excel.php') ? 'class="active"' : ''; ?>>
<i class="bi bi-file-earmark-excel-fill"></i> Export Excel
</a>
</li>

</ul>

    <div class="sidebar-footer">

        <a href="auth/logout.php">

            <i class="bi bi-box-arrow-right"></i>

            Logout

        </a>

    </div>

</div>

