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

$admin_name = $_SESSION['user_name'];

if(isset($_POST['save_room']))
{
    $room_name = mysqli_real_escape_string($conn,$_POST['room_name']);
    $room_type = mysqli_real_escape_string($conn,$_POST['room_type']);
    $price = mysqli_real_escape_string($conn,$_POST['price']);
    $capacity = mysqli_real_escape_string($conn,$_POST['capacity']);
    $description = mysqli_real_escape_string($conn,$_POST['description']);
    $best_seller = $_POST['best_seller'];

    $image_name = $_FILES['room_image']['name'];
    $image_tmp  = $_FILES['room_image']['tmp_name'];

    move_uploaded_file($image_tmp,"../assets/images/rooms/".$image_name);

    $query = "INSERT INTO rooms
    (room_name,room_type,price,capacity,description,best_seller,image)
    VALUES
    ('$room_name','$room_type','$price','$capacity','$description','$best_seller','rooms/$image_name')";

    if(mysqli_query($conn,$query)){
        echo "<script>
        alert('Room Added Successfully');
        window.location='rooms.php';
        </script>";
    }else{
        echo "<script>alert('Failed to Add Room');</script>";
    }
}

$adminData = mysqli_fetch_assoc(
mysqli_query($conn,"SELECT profile_image FROM users WHERE id='".$_SESSION['user_id']."'")
);
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Add Room</title>

<link rel="stylesheet" href="../assets/css/admin.css">

<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

<style>

.form-card{
    background:#1e293b;
    border:1px solid #334155;
    border-radius:15px;
    padding:35px;
    margin-top:30px;
}

.form-grid{
    display:grid;
    grid-template-columns:repeat(2,1fr);
    gap:25px;
}

.form-group{
    display:flex;
    flex-direction:column;
}

.form-group label{
    color:#38bdf8;
    margin-bottom:8px;
    font-weight:600;
}

.form-group input,
.form-group textarea,
.form-group select{
    background:#0f172a;
    border:1px solid #334155;
    color:#fff;
    padding:12px;
    border-radius:10px;
    outline:none;
}

.form-group textarea{
    resize:none;
    height:120px;
}

.full-width{
    grid-column:1/3;
}

.save-btn{
    margin-top:30px;
    background:#22c55e;
    color:white;
    border:none;
    padding:14px 25px;
    border-radius:10px;
    font-size:16px;
    cursor:pointer;
}

.save-btn:hover{
    background:#16a34a;
}

</style>

</head>

<body>

<div class="admin-container">

<?php $current_page="rooms"; ?>
<?php include("includes/sidebar.php"); ?>

<div class="main-content">

<?php include("includes/navbar.php"); ?>

<h1>Add New Room</h1>

<div class="form-card">

<form method="POST" enctype="multipart/form-data">

<div class="form-group">

<label>Room Name</label>

<input
type="text"
name="room_name"
required>

</div>

<div class="form-group">

<label>Room Type</label>

<input
type="text"
name="room_type"
required>

</div>

<div class="form-group">

<label>Price (₹)</label>

<input
type="number"
name="price"
required>

</div>

<div class="form-group">

<label>Capacity</label>

<input
type="number"
name="capacity"
required>

</div>

<div class="form-group full-width">

<label>Description</label>

<textarea
name="description"
required></textarea>

</div>

<div class="form-group">

<label>Best Seller</label>

<select name="best_seller">

<option value="0">No</option>

<option value="1">Yes</option>

</select>

</div>

<div class="form-group">

<label>Room Image</label>

<input
type="file"
name="room_image"
accept="image/*"
required>

</div>

</div>

<button
type="submit"
name="save_room"
class="save-btn">

<i class="fas fa-save"></i>

Save Room

</button>

</form>

</div>
</div>

</div>

</body>

</html>