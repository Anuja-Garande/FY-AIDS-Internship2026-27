<?php
// Best-effort fetch of the logged-in user's display info + unread notification count.
// Falls back gracefully if $conn / session data isn't available for any reason.
$__fullName = $_SESSION['full_name'] ?? 'Account';
$__initial  = strtoupper(substr($__fullName, 0, 1) ?: 'N');
$__unread   = 0;

if (isset($conn, $_SESSION['user_id'])) {
    if ($stmt = $conn->prepare("SELECT COUNT(*) FROM notifications WHERE user_id=? AND status='Unread'")) {
        $stmt->bind_param("i", $_SESSION['user_id']);
        $stmt->execute();
        $__unread = (int) $stmt->get_result()->fetch_row()[0];
    }
}
?>
<header class="topbar">

    <div class="topbar-title">
        <span class="eyebrow-sm mono">NeoFinance · Ledger</span>
        <span class="live-clock mono" id="liveClock"></span>
    </div>

    <div class="topbar-actions">

        <a href="notifications.php" class="topbar-icon-btn" title="Notifications">
            <i class="fa-solid fa-bell"></i>
            <?php if ($__unread > 0): ?>
                <span class="ping"><?php echo $__unread > 9 ? '9+' : $__unread; ?></span>
            <?php endif; ?>
        </a>

        <div class="dropdown">
            <a href="#" class="topbar-user dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false" style="text-decoration:none;">
                <span class="avatar-fallback"><?php echo htmlspecialchars($__initial); ?></span>
                <span>
                    <span class="name-text"><?php echo htmlspecialchars($__fullName); ?></span>
                    <span class="role-tag">My Account</span>
                </span>
            </a>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item" href="profile.php"><i class="fa-solid fa-user"></i> Profile</a></li>
                <li><a class="dropdown-item" href="security.php"><i class="fa-solid fa-shield-halved"></i> Security</a></li>
                <li><a class="dropdown-item" href="notifications.php"><i class="fa-solid fa-bell"></i> Notifications</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item text-danger" href="authentication/logout.php"><i class="fa-solid fa-right-from-bracket"></i> Logout</a></li>
            </ul>
        </div>

    </div>

</header>

<script>
(function(){
    function updateClock(){
        var el = document.getElementById('liveClock');
        if (el) el.textContent = new Date().toLocaleTimeString();
    }
    setInterval(updateClock, 1000);
    updateClock();
})();
</script>
