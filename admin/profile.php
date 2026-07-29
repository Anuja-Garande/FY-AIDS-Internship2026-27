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

$query = mysqli_query($conn, "SELECT * FROM users WHERE id='$admin_id'");
$admin = mysqli_fetch_assoc($query);
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Admin Profile</title>

<link rel="stylesheet" href="../assets/css/admin.css">

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

<style>

.profile-container{
    max-width:1000px;
    margin:40px auto;
}

.profile-card{
    background:#1f2937;
    border-radius:20px;
    padding:40px;
    border:1px solid #334155;
    box-shadow:0 0 20px rgba(56,189,248,.15);
}

.profile-header{
    text-align:center;
    margin-bottom:35px;
}

.profile-header i{
    font-size:90px;
    color:#38bdf8;
    margin-bottom:15px;
}

.profile-header h2{
    color:white;
    font-size:32px;
}

.profile-table{
    width:100%;
    border-collapse:collapse;
}

.profile-table tr{
    border-bottom:1px solid #334155;
}

.profile-table td{
    padding:18px 10px;
    color:white;
    font-size:18px;
}

.profile-table td:first-child{
    width:220px;
    font-weight:bold;
    color:#38bdf8;
}

.profile-buttons{
    margin-top:35px;
    display:flex;
    justify-content:center;
    gap:20px;
}

.profile-buttons a{
    text-decoration:none;
    padding:12px 22px;
    border-radius:8px;
    font-weight:bold;
    color:white;
}

.btn-edit{
    background:#f59e0b;
}

.btn-password{
    background:#22c55e;
}

.btn-edit:hover{
    background:#d97706;
}

.btn-password:hover{
    background:#16a34a;
}

</style>

</head>

<body>

<div class="admin-container">

<?php $current_page = "profile"; ?>
<?php include("includes/sidebar.php"); ?>

<div class="main-content">

<?php include("includes/navbar.php"); ?>





<div class="profile-container">

<div class="profile-card">

<div class="profile-header">

<?php if(!empty($admin['profile_image'])){ ?>

<img src="../uploads/profile/<?php echo htmlspecialchars($admin['profile_image']); ?>"
style="width:120px;height:120px;border-radius:50%;object-fit:cover;border:4px solid #38bdf8;">

<?php }else{ ?>

<i class="fas fa-user-circle"></i>

<?php } ?>

<h2><?php echo htmlspecialchars($admin['full_name']); ?></h2>

</div>

<table class="profile-table">

<tr>
<td>Full Name</td>
<td><?php echo htmlspecialchars($admin['full_name']); ?></td>
</tr>

<tr>
<td>Email</td>
<td><?php echo htmlspecialchars($admin['email']); ?></td>
</tr>

<tr>
<td>Phone</td>
<td><?php echo htmlspecialchars($admin['phone']); ?></td>
</tr>

<tr>
<td>Role</td>
<td><?php echo htmlspecialchars($admin['role']); ?></td>
</tr>

<tr>
<td>Account Created</td>
<td><?php echo htmlspecialchars($admin['created_at']); ?></td>
</tr>

</table>

<div class="profile-buttons">

<a href="edit_profile.php" class="btn-edit">
<i class="fas fa-edit"></i> Edit Profile
</a>

<a href="change_password.php" class="btn-password">
<i class="fas fa-key"></i> Change Password
</a>

</div>

</div>

</div>

</div>

</div>

<script src="assets/js/theme.js"></script>

</body>

</html>