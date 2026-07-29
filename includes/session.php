<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/* Check login status */

function isAdminLoggedIn()
{
    return !empty($_SESSION['admin_id']);
}

function isStudentLoggedIn()
{
    return !empty($_SESSION['student_id']);
}

function isLibrarianLoggedIn()
{
    return !empty($_SESSION['librarian_id']);
}

/* Protect role pages */

function requireAdmin($loginPage = '../login.php')
{
    if (!isAdminLoggedIn()) {
        header('Location: ' . $loginPage);
        exit;
    }
}

function requireStudent($loginPage = '../login.php')
{
    if (!isStudentLoggedIn()) {
        header('Location: ' . $loginPage);
        exit;
    }
}

function requireLibrarian($loginPage = '../login.php')
{
    if (!isLibrarianLoggedIn()) {
        header('Location: ' . $loginPage);
        exit;
    }
}

/* Get currently logged-in user details */

function getLoggedInUserName()
{
    return $_SESSION['admin_name']
        ?? $_SESSION['student_name']
        ?? $_SESSION['librarian_name']
        ?? $_SESSION['name']
        ?? 'User';
}

function getLoggedInUserRole()
{
    if (isAdminLoggedIn()) {
        return 'admin';
    }

    if (isStudentLoggedIn()) {
        return 'student';
    }

    if (isLibrarianLoggedIn()) {
        return 'librarian';
    }

    return null;
}

/* Logout */

function logoutUser()
{
    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();

        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params['path'],
            $params['domain'],
            $params['secure'],
            $params['httponly']
        );
    }

    session_destroy();
}
?>