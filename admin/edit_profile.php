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

if (isset($_POST['update'])) {

    $full_name = mysqli_real_escape_string($conn, $_POST['full_name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    if (!preg_match('/^[0-9]{10}$/', $phone)) {

    echo "<script>
            alert('Phone number must be exactly 10 digits.');
            window.history.back();
          </script>";
    exit();

}
    $image_name = "";

$current = mysqli_query($conn, "SELECT profile_image FROM users WHERE id='$admin_id'");
$currentData = mysqli_fetch_assoc($current);

if($currentData){
    $image_name = $currentData['profile_image'];
}

if(isset($_FILES['profile_image']) && $_FILES['profile_image']['name'] != ""){

    $image_name = time() . "_" . basename($_FILES['profile_image']['name']);

    move_uploaded_file(
        $_FILES['profile_image']['tmp_name'],
        "../uploads/profile/" . $image_name
    );
}

    $sql = "UPDATE users
            SET full_name='$full_name',
                email='$email',
                phone='$phone',
                profile_image='$image_name'
            WHERE id='$admin_id'";

    if (mysqli_query($conn, $sql)) {

        $_SESSION['user_name'] = $full_name;

        header("Location: profile.php?updated=1");
        exit();
    }
}

$query = mysqli_query($conn, "SELECT * FROM users WHERE id='$admin_id'");
$admin = mysqli_fetch_assoc($query);
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Edit Profile</title>

<link rel="stylesheet" href="../assets/css/admin.css">

<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

<style>

.edit-container{
    max-width:700px;
    margin:40px auto;
}

.edit-card{
    background:#1f2937;
    border:1px solid #334155;
    border-radius:18px;
    padding:35px;
}

.edit-card h2{
    color:white;
    text-align:center;
    margin-bottom:30px;
}

.edit-card label{
    color:#38bdf8;
    font-weight:bold;
    display:block;
    margin-bottom:8px;
}

.edit-card input{
    width:100%;
    padding:12px;
    margin-bottom:20px;
    border-radius:8px;
    border:1px solid #475569;
    background:#0f172a;
    color:white;
    font-size:16px;
}

.update-btn{
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

.update-btn:hover{
    background:#16a34a;
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
<i class="fas fa-user-edit"></i>
<input type="text" value="Edit Profile" readonly>
</div>

<div class="top-right">

<i class="fas fa-bell notification"></i>

<div class="admin-profile">
<i class="fas fa-user-circle"></i>
<span><?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
</div>

</div>

</div>

<div class="edit-container">

<div class="edit-card">

<h2>Edit Profile</h2>

<form method="POST" enctype="multipart/form-data">

<label>Full Name</label>
<input type="text" name="full_name"
value="<?php echo htmlspecialchars($admin['full_name']); ?>" required>

<label>Email</label>
<input type="email" name="email"
value="<?php echo htmlspecialchars($admin['email']); ?>" required>

<label>Phone</label>
<input
type="tel"
name="phone"
value="<?php echo htmlspecialchars($admin['phone']); ?>"
maxlength="10"
pattern="[0-9]{10}"
inputmode="numeric"
oninput="this.value=this.value.replace(/[^0-9]/g,'').slice(0,10)"
required>

<label>Profile Image</label>

<input type="file" name="profile_image" accept="image/*">

<button type="submit" name="update" class="update-btn">
<i class="fas fa-save"></i> Update Profile
</button>

</form>
</form>

</div>

</div>

</div>

</div>

</body>

</html>