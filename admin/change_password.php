<?php
session_start();
require_once("../database/db.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

if ($_SESSION['role'] != "admin") {
    die("Access Denied!");
}

$admin_id = $_SESSION['user_id'];
$message = "";
$message_type = "";

if (isset($_POST['change_password'])) {

    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    $query = mysqli_query($conn, "SELECT password FROM users WHERE id='$admin_id'");
    $user = mysqli_fetch_assoc($query);

    if (!password_verify($current_password, $user['password'])) {

        $message = "Current password is incorrect!";
        $message_type = "error";

    } elseif ($new_password != $confirm_password) {

        $message = "New password and Confirm password do not match!";
        $message_type = "error";

    } else {

        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

        mysqli_query($conn, "UPDATE users SET password='$hashed_password' WHERE id='$admin_id'");

        $message = "Password changed successfully!";
        $message_type = "success";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Change Password</title>

<link rel="stylesheet" href="../assets/css/admin.css">

<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

<style>

.password-container{
    max-width:700px;
    margin:40px auto;
}

.password-card{
    background:#1f2937;
    border:1px solid #334155;
    border-radius:18px;
    padding:35px;
}

.password-card h2{
    color:white;
    text-align:center;
    margin-bottom:30px;
}

.password-card label{
    display:block;
    color:#38bdf8;
    font-weight:bold;
    margin-bottom:8px;
}

.password-card input{
    width:100%;
    padding:12px;
    margin-bottom:20px;
    border-radius:8px;
    border:1px solid #475569;
    background:#0f172a;
    color:white;
    font-size:16px;
}

.change-btn{
    width:100%;
    padding:14px;
    background:#22c55e;
    color:white;
    border:none;
    border-radius:8px;
    font-size:17px;
    font-weight:bold;
    cursor:pointer;
}

.change-btn:hover{
    background:#16a34a;
}

.success{
    background:#14532d;
    color:#bbf7d0;
    padding:12px;
    border-radius:8px;
    margin-bottom:20px;
}

.error{
    background:#7f1d1d;
    color:#fecaca;
    padding:12px;
    border-radius:8px;
    margin-bottom:20px;
}

</style>

</head>

<body>

<div class="admin-container">

<div class="sidebar">

<h2>🏨 Hotel Admin</h2>

<a href="dashboard.php"><i class="fas fa-chart-line"></i> Dashboard</a>
<a href="rooms.php"><i class="fas fa-bed"></i> Manage Rooms</a>
<a href="users.php"><i class="fas fa-users"></i> Manage Users</a>
<a href="bookings.php"><i class="fas fa-calendar-check"></i> Manage Bookings</a>
<a href="payments.php"><i class="fas fa-money-bill-wave"></i> Payments</a>
<a href="reports.php"><i class="fas fa-chart-pie"></i> Reports</a>
<a href="profile.php"><i class="fas fa-user-circle"></i> Profile</a>
<a href="../auth/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>

</div>

<div class="main-content">

<div class="top-navbar">

<div class="search-box">
<i class="fas fa-key"></i>
<input type="text" value="Change Password" readonly>
</div>

<div class="top-right">

<i class="fas fa-bell notification"></i>

<div class="admin-profile">
<i class="fas fa-user-circle"></i>
<span><?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
</div>

</div>

</div>

<div class="password-container">

<div class="password-card">

<h2>Change Password</h2>

<?php if($message!=""){ ?>

<div class="<?php echo $message_type; ?>">
<?php echo $message; ?>
</div>

<?php } ?>

<form method="POST">

<label>Current Password</label>
<input type="password" name="current_password" required>

<label>New Password</label>
<input type="password" name="new_password" required>

<label>Confirm Password</label>
<input type="password" name="confirm_password" required>

<button type="submit" name="change_password" class="change-btn">
<i class="fas fa-save"></i> Change Password
</button>

</form>

</div>

</div>

</div>

</div>

</body>

</html>