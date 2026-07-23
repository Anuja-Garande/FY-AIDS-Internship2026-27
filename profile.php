<?php

session_start();
include("database/connection.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch student details
$sql = "SELECT * FROM students WHERE user_id='$user_id'";
$result = mysqli_query($conn, $sql);

if(mysqli_num_rows($result)==0){
    die("Student profile not found.");
}

$row = mysqli_fetch_assoc($result);


// ================= UPDATE PROFILE =================

if(isset($_POST['update']))
{

    $full_name = mysqli_real_escape_string($conn,$_POST['full_name']);
    $email     = mysqli_real_escape_string($conn,$_POST['email']);
    $mobile    = mysqli_real_escape_string($conn,$_POST['mobile']);
    $gender    = mysqli_real_escape_string($conn,$_POST['gender']);
    $dob       = $_POST['dob'];
    $address   = mysqli_real_escape_string($conn,$_POST['address']);
    $city      = mysqli_real_escape_string($conn,$_POST['city']);
    $state     = mysqli_real_escape_string($conn,$_POST['state']);
    $pincode   = mysqli_real_escape_string($conn,$_POST['pincode']);

    $update="UPDATE students SET

    full_name='$full_name',
    email='$email',
    mobile='$mobile',
    gender='$gender',
    dob='$dob',
    address='$address',
    city='$city',
    state='$state',
    pincode='$pincode'

    WHERE user_id='$user_id'";

    if(mysqli_query($conn,$update))
    {

        echo "<script>alert('Profile Updated Successfully');</script>";
        echo "<script>window.location='profile.php';</script>";
        exit();

    }
    else
    {

        echo "<script>alert('Update Failed');</script>";

    }

}


// ================= DELETE PROFILE =================

if(isset($_POST['delete']))
{

    $delete="DELETE FROM students WHERE user_id='$user_id'";

    if(mysqli_query($conn,$delete))
    {

        session_destroy();

        echo "<script>alert('Profile Deleted Successfully');</script>";
        echo "<script>window.location='login.php';</script>";
        exit();

    }

    else
    {

        echo "<script>alert('Delete Failed');</script>";

    }

}

?>
<!DOCTYPE html>

<html>

<head>

<meta charset="UTF-8">

<title>Student Profile</title>

<meta name="viewport" content="width=device-width,initial-scale=1">

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">

<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<style>

*{
margin:0;
padding:0;
box-sizing:border-box;
font-family:Poppins,sans-serif;
}

body{

background:#eef2f7;

display:flex;

}

/* ================= SIDEBAR ================= */

.sidebar{

width:240px;

height:100vh;

background:#2563eb;

position:fixed;

left:0;

top:0;

padding:25px;

}

.sidebar h2{

color:#fff;

margin-bottom:40px;

}

.sidebar a{

display:block;

padding:14px;

margin-bottom:10px;

text-decoration:none;

color:#fff;

border-radius:8px;

transition:.3s;

}

.sidebar a:hover{

background:rgba(255,255,255,.15);

}

/* ================= MAIN ================= */

.main{

margin-left:240px;

width:calc(100% - 240px);

padding:35px;

}

.header{

background:white;

padding:25px;

border-radius:15px;

box-shadow:0 5px 15px rgba(0,0,0,.08);

margin-bottom:30px;

}

.header h1{

color:#2563eb;

}

.card{

background:white;

padding:30px;

border-radius:15px;

box-shadow:0 5px 15px rgba(0,0,0,.08);

}
.main h2{
color:#2563eb;
margin-bottom:25px;
}

.profile-photo{

text-align:center;
margin-bottom:30px;

}

.profile-photo img{

width:130px;
height:130px;
border-radius:50%;
border:4px solid #2563eb;
object-fit:cover;

}

table{

width:100%;

}

table td{

padding:12px;

vertical-align:top;

}

table td:first-child{

font-weight:600;
width:180px;

}

input,
textarea,
select{

width:100%;
padding:11px;
border:1px solid #ccc;
border-radius:8px;
font-size:15px;

}

textarea{

height:90px;
resize:none;

}

.btn{

padding:12px 25px;
border:none;
border-radius:8px;
cursor:pointer;
font-size:15px;
margin:10px;

}

.update{

background:#2563eb;
color:white;

}

.delete{

background:#dc3545;
color:white;

}

.back{

background:#6c757d;
color:white;
text-decoration:none;
display:inline-block;

}

.btn:hover{

opacity:.9;

}

@media(max-width:768px){

.sidebar{

width:100%;
height:auto;
position:relative;

}

.main{

margin-left:0;
width:100%;

}

body{

display:block;

}

table,
tr,
td{

display:block;
width:100%;

}

}
</style>

</head>

<body>

<div class="sidebar">

<h2>Student Portal</h2>

<a href="dashboard.php">
<i class="fa-solid fa-house"></i>
 Dashboard
</a>

<a href="profile.php">
<i class="fa-solid fa-user"></i>
 Student Profile
</a>

<a href="project.php">
<i class="fa-solid fa-folder"></i>
 My Project
</a>

<a href="task.php">
<i class="fa-solid fa-list-check"></i>
 Task List
</a>

<a href="team.php">
<i class="fa-solid fa-users"></i>
 Team Members
</a>

<a href="submit.php">
<i class="fa-solid fa-upload"></i>
 Submit Work
</a>

<a href="logout.php">
<i class="fa-solid fa-right-from-bracket"></i>
 Logout
</a>

</div>

<div class="main">

<div class="header">

<h1>

Welcome,

<?php echo $row['full_name']; ?>

👋

</h1>

<p>

Student Profile Management

</p>

</div>

<div class="card">

<h2>

Student Profile

</h2>

<form method="post">

<div class="profile-photo">

<img src="uploads/<?php echo $row['profile_photo']; ?>">

</div>

<table>

<tr>

<td>Full Name</td>

<td>

<input
type="text"
name="full_name"
value="<?php echo $row['full_name']; ?>">

</td>

</tr>

<tr>

<td>Roll Number</td>

<td>

<input
type="text"
value="<?php echo $row['roll_no']; ?>"
readonly>

</td>

</tr>

<tr>

<td>Email</td>

<td>

<input
type="email"
name="email"
value="<?php echo $row['email']; ?>">

</td>

</tr>

<tr>

<td>Mobile</td>

<td>

<input
type="text"
name="mobile"
value="<?php echo $row['mobile']; ?>">

</td>

</tr>

<tr>

<td>Gender</td>

<td>

<select name="gender">

<option value="Male" <?php if($row['gender']=="Male") echo "selected"; ?>>Male</option>

<option value="Female" <?php if($row['gender']=="Female") echo "selected"; ?>>Female</option>

<option value="Other" <?php if($row['gender']=="Other") echo "selected"; ?>>Other</option>

</select>

</td>

</tr>

<tr>

<td>Date of Birth</td>

<td>

<input
type="date"
name="dob"
value="<?php echo $row['dob']; ?>">

</td>

</tr>

<tr>

<td>Department</td>

<td>

<input
type="text"
value="<?php echo $row['department']; ?>"
readonly>

</td>

</tr>

<tr>

<td>Year</td>

<td>

<input
type="text"
value="<?php echo $row['year']; ?>"
readonly>

</td>

</tr>

<tr>

<td>Division</td>

<td>

<input
type="text"
value="<?php echo $row['division']; ?>"
readonly>

</td>

</tr>

<tr>

<td>Address</td>

<td>

<textarea
name="address"><?php echo $row['address']; ?></textarea>

</td>

</tr>

<tr>

<td>City</td>

<td>

<input
type="text"
name="city"
value="<?php echo $row['city']; ?>">

</td>

</tr>

<tr>

<td>State</td>

<td>

<input
type="text"
name="state"
value="<?php echo $row['state']; ?>">

</td>

</tr>

<tr>

<td>Pincode</td>

<td>

<input
type="text"
name="pincode"
value="<?php echo $row['pincode']; ?>">

</td>

</tr>
<tr>

<td colspan="2" align="center">

<button
type="submit"
name="update"
class="btn update">

<i class="fa-solid fa-floppy-disk"></i>

 Update Profile

</button>

<button
type="submit"
name="delete"
class="btn delete"
onclick="return confirm('Are you sure you want to delete your profile?');">

<i class="fa-solid fa-trash"></i>

 Delete Profile

</button>

<a href="dashboard.php" class="btn back">

<i class="fa-solid fa-arrow-left"></i>

 Dashboard

</a>

</td>

</tr>

</table>

</form>

</div>

</div>

</body>

</html>