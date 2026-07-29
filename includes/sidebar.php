<?php
// Determine the current page so we can highlight the active nav link.
$__currentPage = basename($_SERVER['PHP_SELF']);

function nf_active($page, $current) {
    return $page === $current ? 'active' : '';
}
?>
<button class="sidebar-toggle-btn" id="sidebarToggleBtn" type="button" aria-label="Toggle menu">
    <i class="fa-solid fa-bars"></i>
</button>
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<aside class="sidebar" id="mainSidebar">

    <a href="dashboard.php" class="sidebar-brand">
        <span class="coin">🪙</span> NeoFinance
    </a>

    <div class="sidebar-section-label">Overview</div>
    <ul class="sidebar-nav">
        <li><a href="dashboard.php" class="<?php echo nf_active('dashboard.php', $__currentPage); ?>">
            <i class="fa-solid fa-gauge-high"></i> Dashboard
        </a></li>
        <li><a href="transactions.php" class="<?php echo nf_active('transactions.php', $__currentPage); ?>">
            <i class="fa-solid fa-right-left"></i> Transactions
        </a></li>
        <li><a href="analytics.php" class="<?php echo nf_active('analytics.php', $__currentPage); ?>">
            <i class="fa-solid fa-chart-pie"></i> Analytics
        </a></li>
        <li><a href="ai_insights.php" class="<?php echo nf_active('ai_insights.php', $__currentPage); ?>">
            <i class="fa-solid fa-robot"></i> AI Insights
        </a></li>
    </ul>

    <div class="sidebar-section-label">Ledger</div>
    <ul class="sidebar-nav">
        <li><a href="add_income.php" class="<?php echo nf_active('add_income.php', $__currentPage); ?>">
            <i class="fa-solid fa-wallet"></i> Add Income
        </a></li>
        <li><a href="add_expense.php" class="<?php echo nf_active('add_expense.php', $__currentPage); ?>">
            <i class="fa-solid fa-money-bill-wave"></i> Add Expense
        </a></li>
        <li><a href="budgets.php" class="<?php echo nf_active('budgets.php', $__currentPage); ?>">
            <i class="fa-solid fa-chart-column"></i> Budget
        </a></li>
        <li><a href="savings.php" class="<?php echo nf_active('savings.php', $__currentPage); ?>">
            <i class="fa-solid fa-piggy-bank"></i> Savings Goals
        </a></li>
        <li><a href="reports.php" class="<?php echo nf_active('reports.php', $__currentPage); ?>">
            <i class="fa-solid fa-file-lines"></i> Reports
        </a></li>
    </ul>

    <div class="sidebar-section-label">Account</div>
    <ul class="sidebar-nav">
        <li><a href="notifications.php" class="<?php echo nf_active('notifications.php', $__currentPage); ?>">
            <i class="fa-solid fa-bell"></i> Notifications
        </a></li>
        <li><a href="profile.php" class="<?php echo nf_active('profile.php', $__currentPage); ?>">
            <i class="fa-solid fa-user"></i> Profile
        </a></li>
        <li><a href="security.php" class="<?php echo nf_active('security.php', $__currentPage); ?>">
            <i class="fa-solid fa-shield-halved"></i> Security
        </a></li>
        <li><a href="authentication/logout.php" class="danger-link">
            <i class="fa-solid fa-right-from-bracket"></i> Logout
        </a></li>
    </ul>

    <div class="sidebar-footer">
        <div class="sidebar-serial mono">NF · SERIES 2026 · SECURE</div>
    </div>

</aside>

<script>
(function(){
    var sidebar = document.getElementById('mainSidebar');
    var toggleBtn = document.getElementById('sidebarToggleBtn');
    var overlay = document.getElementById('sidebarOverlay');
    if (!sidebar || !toggleBtn || !overlay) return;

    function closeSidebar(){
        sidebar.classList.remove('open');
        overlay.classList.remove('open');
    }
    toggleBtn.addEventListener('click', function(){
        sidebar.classList.toggle('open');
        overlay.classList.toggle('open');
    });
    overlay.addEventListener('click', closeSidebar);
})();
</script>
