<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$currentFolder = basename(dirname($_SERVER['SCRIPT_NAME']));

if (
    $currentFolder === 'admin' ||
    $currentFolder === 'student' ||
    $currentFolder === 'librarian'
) {
    $rootPath = '../';
} else {
    $rootPath = '';
}

$isLoggedIn = false;
$dashboardLink = '';
$userName = '';

if (!empty($_SESSION['admin_id'])) {
    $isLoggedIn = true;
    $dashboardLink = $rootPath . 'admin/dashboard.php';
    $userName = $_SESSION['admin_name'] ?? $_SESSION['name'] ?? 'Admin';
} elseif (!empty($_SESSION['student_id'])) {
    $isLoggedIn = true;
    $dashboardLink = $rootPath . 'student/dashboard.php';
    $userName = $_SESSION['student_name'] ?? $_SESSION['name'] ?? 'Student';
} elseif (!empty($_SESSION['librarian_id'])) {
    $isLoggedIn = true;
    $dashboardLink = $rootPath . 'librarian/dashboard.php';
    $userName = $_SESSION['librarian_name'] ?? $_SESSION['name'] ?? 'Librarian';
}
?>

<nav>
    <div class="logo">
        <img src="<?= $rootPath ?>assets/images/logo.png" alt="Digital Library Logo">

        <div>
            <h2>Digital Library</h2>
            <p>Zeal Education Society</p>
        </div>
    </div>

    <ul>
        <li>
            <a href="<?= $rootPath ?>index.php">
                <i class="fa-solid fa-house"></i> Home
            </a>
        </li>

        <li>
            <a href="<?= $rootPath ?>about.php">
                <i class="fa-solid fa-circle-info"></i> About
            </a>
        </li>

        <li>
            <a href="<?= $rootPath ?>books.php">
                <i class="fa-solid fa-book"></i> Books
            </a>
        </li>

        <li>
            <a href="<?= $rootPath ?>contact.php">
                <i class="fa-solid fa-envelope"></i> Contact
            </a>
        </li>

        <?php if ($isLoggedIn): ?>
            <li>
                <a class="login-btn" href="<?= $dashboardLink ?>">
                    <i class="fa-solid fa-gauge"></i>
                    <?= htmlspecialchars($userName, ENT_QUOTES, 'UTF-8') ?>
                </a>
            </li>

            <li>
                <a href="<?= $rootPath ?>login.php">
                    <i class="fa-solid fa-right-from-bracket"></i> Logout
                </a>
            </li>
        <?php else: ?>
            <li>
                <a class="login-btn" href="<?= $rootPath ?>login.php">
                    <i class="fa-solid fa-right-to-bracket"></i> Login
                </a>
            </li>
        <?php endif; ?>
    </ul>
</nav>