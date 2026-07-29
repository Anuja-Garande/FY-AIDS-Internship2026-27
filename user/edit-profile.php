<?php
session_start();
require_once("../database/db.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$query = "SELECT * FROM users WHERE id='$user_id'";
$result = mysqli_query($conn, $query);
$user = mysqli_fetch_assoc($result);

if (isset($_POST['update_profile'])) {

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

    $profile_image = $user['profile_image'];

    if (!empty($_FILES['profile_image']['name'])) {

        $target_dir = "../uploads/profile/";

        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        $file_name = time() . "_" . basename($_FILES['profile_image']['name']);
        $target_file = $target_dir . $file_name;

        move_uploaded_file($_FILES['profile_image']['tmp_name'], $target_file);

        $profile_image = $file_name;
    }

    $update = "UPDATE users SET
        full_name='$full_name',
        email='$email',
        phone='$phone',
        profile_image='$profile_image'
        WHERE id='$user_id'";

    if(mysqli_query($conn,$update)){
        header("Location: profile.php");
        exit();
    }else{
        echo "Update Failed!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Edit Profile</title>

<link rel="stylesheet" href="../assets/css/style.css">

<style>

body{
background:#f4f7fb;
font-family:Arial,Helvetica,sans-serif;
}

.container{
width:600px;
margin:40px auto;
background:#fff;
padding:30px;
border-radius:12px;
box-shadow:0 5px 15px rgba(0,0,0,.1);
}

h2{
text-align:center;
margin-bottom:25px;
}

.profile-img{
width:140px;
height:140px;
border-radius:50%;
object-fit:cover;
display:block;
margin:0 auto 20px;
border:4px solid #0d6efd;
}

label{
font-weight:bold;
display:block;
margin-top:15px;
}

input{
width:100%;
padding:12px;
margin-top:5px;
border:1px solid #ccc;
border-radius:6px;
box-sizing:border-box;
}

button{
margin-top:25px;
background:#0d6efd;
color:white;
padding:12px 20px;
border:none;
border-radius:6px;
cursor:pointer;
font-size:16px;
}

button:hover{
background:#084dbf;
}

.cancel{
background:#dc3545;
text-decoration:none;
color:white;
padding:12px 20px;
border-radius:6px;
margin-left:10px;
}

</style>

</head>

<body>

<div class="container">

<h2>Edit Profile</h2>

<form method="POST" enctype="multipart/form-data">

<?php
if (!empty($user['profile_image']) && file_exists("../uploads/profile/".$user['profile_image'])) {
?>
    <img src="../uploads/profile/<?php echo $user['profile_image']; ?>" class="profile-img">
<?php
} else {
?>
    <img src="../assets/images/default-user.png" class="profile-img">
<?php
}
?>

<label>Profile Image</label>
<input type="file" name="profile_image" accept="image/*">

<label>Full Name</label>
<input
    type="text"
    name="full_name"
    value="<?php echo htmlspecialchars($user['full_name']); ?>"
    required>

<label>Email</label>
<input
    type="email"
    name="email"
    value="<?php echo htmlspecialchars($user['email']); ?>"
    required>

<label>Phone</label>
<input
    type="tel"
    name="phone"
    value="<?php echo htmlspecialchars($user['phone']); ?>"
    maxlength="10"
    pattern="[0-9]{10}"
    inputmode="numeric"
    oninput="this.value=this.value.replace(/[^0-9]/g,'').slice(0,10)"
    required>

<div style="margin-top:25px;">

    <button type="submit" name="update_profile">
        Update Profile
    </button>

    <a href="profile.php" class="cancel">
        Cancel
    </a>

</div>
</form>

</div>

</body>

</html>