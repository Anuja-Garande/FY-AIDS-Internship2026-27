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

if (!isset($_GET['id'])) {
    die("Room ID Missing!");
}

$id = (int)$_GET['id'];

$result = mysqli_query($conn,"SELECT * FROM rooms WHERE id='$id'");

if(mysqli_num_rows($result)==0){
    die("Room Not Found!");
}

$room = mysqli_fetch_assoc($result);

if(isset($_POST['update_room'])){

    $room_name = mysqli_real_escape_string($conn,$_POST['room_name']);
    $room_type = mysqli_real_escape_string($conn,$_POST['room_type']);
    $price = mysqli_real_escape_string($conn,$_POST['price']);
    $capacity = mysqli_real_escape_string($conn,$_POST['capacity']);
    $description = mysqli_real_escape_string($conn,$_POST['description']);
    $best_seller = $_POST['best_seller'];

    $image = $room['image'];

    if(!empty($_FILES['room_image']['name'])){

        $filename = time()."_".$_FILES['room_image']['name'];

        move_uploaded_file(
            $_FILES['room_image']['tmp_name'],
            "../assets/images/rooms/".$filename
        );

        $image = "rooms/".$filename;
    }

    $update = mysqli_query($conn,"
    UPDATE rooms SET

    room_name='$room_name',
    room_type='$room_type',
    price='$price',
    capacity='$capacity',
    description='$description',
    best_seller='$best_seller',
    image='$image'

    WHERE id='$id'
    ");

    if($update){

        echo "<script>
        alert('Room Updated Successfully');
        window.location='rooms.php';
        </script>";

        exit();

    }else{

        echo "<script>alert('Update Failed');</script>";

    }

}
?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
content="width=device-width, initial-scale=1.0">

<title>Edit Room</title>

<link rel="stylesheet"
href="../assets/css/admin.css">

<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

<style>

.edit-card{
background:#1e293b;
padding:35px;
border-radius:15px;
border:1px solid #334155;
margin-top:25px;
}

.group{
margin-bottom:22px;
}

.group label{
display:block;
color:#38bdf8;
font-weight:600;
margin-bottom:8px;
}

.group input,
.group textarea,
.group select{
width:100%;
padding:14px;
background:#0f172a;
border:1px solid #334155;
border-radius:10px;
color:white;
box-sizing:border-box;
}

.group textarea{
height:170px;
resize:vertical;
}

.preview{
width:220px;
height:150px;
object-fit:cover;
border-radius:12px;
border:2px solid #38bdf8;
}

</style>

</head>

<body>

<div class="admin-container">

<?php $current_page="rooms"; ?>
<?php include("includes/sidebar.php"); ?>

<div class="main-content">

<?php include("includes/navbar.php"); ?>

<h1>Edit Room</h1>

<div class="edit-card">

<form method="POST" enctype="multipart/form-data">
    <div class="group">

<label>Room Name</label>

<input
type="text"
name="room_name"
value="<?php echo htmlspecialchars($room['room_name']); ?>"
required>

</div>

<div class="group">

<label>Room Type</label>

<input
type="text"
name="room_type"
value="<?php echo htmlspecialchars($room['room_type']); ?>"
required>

</div>

<div class="group">

<label>Price (₹)</label>

<input
type="number"
name="price"
value="<?php echo $room['price']; ?>"
required>

</div>

<div class="group">

<label>Capacity</label>

<input
type="number"
name="capacity"
value="<?php echo $room['capacity']; ?>"
required>

</div>

<div class="group">

<label>Description</label>

<textarea
name="description"
required><?php echo htmlspecialchars($room['description']); ?></textarea>

</div>

<div class="group">

<label>Best Seller</label>

<select name="best_seller">

<option value="0"
<?php if($room['best_seller']==0) echo "selected"; ?>>
No
</option>

<option value="1"
<?php if($room['best_seller']==1) echo "selected"; ?>>
Yes
</option>

</select>

</div>
<div class="group">

<label>Current Image</label>

<br><br>

<img
src="../assets/images/<?php echo $room['image']; ?>"
class="preview">

</div>

<div class="group">

<label>Change Image</label>

<input
type="file"
name="room_image"
accept="image/*">

</div>

<div class="group">

<button
type="submit"
name="update_room"
class="btn-primary">

<i class="fas fa-save"></i>
&nbsp; Update Room

</button>

<a
href="rooms.php"
class="btn-secondary"
style="margin-left:10px;text-decoration:none;padding:12px 18px;display:inline-block;">

<i class="fas fa-arrow-left"></i>
&nbsp; Back

</a>

</div>

</form>

</div>

</div>

</div>

</body>

</html>
</form>