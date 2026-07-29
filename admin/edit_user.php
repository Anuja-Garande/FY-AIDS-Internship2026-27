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
    die("User ID Missing!");
}

$id = (int)$_GET['id'];

$result = mysqli_query($conn,"SELECT * FROM users WHERE id='$id'");

if(mysqli_num_rows($result)==0){
    die("User Not Found!");
}

$user = mysqli_fetch_assoc($result);

if(isset($_POST['update_user'])){

    $full_name = mysqli_real_escape_string($conn,$_POST['full_name']);
    $email = mysqli_real_escape_string($conn,$_POST['email']);
    $phone = mysqli_real_escape_string($conn,$_POST['phone']);
    if (!preg_match('/^[0-9]{10}$/', $phone)) {

    echo "<script>
            alert('Phone number must be exactly 10 digits.');
            window.history.back();
          </script>";
    exit();

}
    $role = mysqli_real_escape_string($conn,$_POST['role']);

    $update = mysqli_query($conn,"
    UPDATE users SET

    full_name='$full_name',
    email='$email',
    phone='$phone',
    role='$role'

    WHERE id='$id'
    ");

    if($update){

        echo "<script>
        alert('User Updated Successfully');
        window.location='users.php';
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

<title>Edit User</title>

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
.group select{
width:100%;
padding:14px;
background:#0f172a;
border:1px solid #334155;
border-radius:10px;
color:white;
box-sizing:border-box;
}

</style>

</head>

<body>

<div class="admin-container">

<?php $current_page="users"; ?>
<?php include("includes/sidebar.php"); ?>

<div class="main-content">

<?php include("includes/navbar.php"); ?>

<h1>Edit User</h1>

<div class="edit-card">

<form method="POST">
    <div class="group">

<label>Full Name</label>

<input
type="text"
name="full_name"
value="<?php echo htmlspecialchars($user['full_name']); ?>"
required>

</div>

<div class="group">

<label>Email Address</label>

<input
type="email"
name="email"
value="<?php echo htmlspecialchars($user['email']); ?>"
required>

</div>

<div class="group">

<label>Phone Number</label>

<input
type="tel"
name="phone"
value="<?php echo htmlspecialchars($user['phone']); ?>"
maxlength="10"
pattern="[0-9]{10}"
inputmode="numeric"
oninput="this.value=this.value.replace(/[^0-9]/g,'').slice(0,10)"
required>

</div>

<div class="group">

<label>User Role</label>

<select name="role">

<option value="user"
<?php if($user['role']=="user") echo "selected"; ?>>
User
</option>

<option value="admin"
<?php if($user['role']=="admin") echo "selected"; ?>>
Admin
</option>

</select>

</div>
<div class="group">

<button
type="submit"
name="update_user"
class="btn-primary">

<i class="fas fa-save"></i>
&nbsp; Update User

</button>

<a
href="users.php"
class="btn-secondary"
style="
margin-left:10px;
padding:12px 18px;
display:inline-block;
background:#475569;
color:#fff;
border-radius:8px;
text-decoration:none;
font-weight:600;">

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