<?php

session_start();

include "../includes/db_connect.php";

/* Change this later after login is connected */
$student_id = 2;

if(isset($_POST['update']))
{

$name = $_POST['name'];
$email = $_POST['email'];
$department = $_POST['department'];
$year = $_POST['year'];
$division = $_POST['division'];
$phone = $_POST['phone'];

mysqli_query($conn,"UPDATE student SET

name='$name',
email='$email',
department='$department',
year='$year',
division='$division',
phone='$phone'

WHERE student_id='$student_id'");

echo "<script>alert('Profile Updated Successfully');</script>";

}

$result=mysqli_query($conn,"SELECT * FROM student WHERE student_id='$student_id'");

$row=mysqli_fetch_assoc($result);

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Edit Profile</title>

<link rel="stylesheet" href="../assets/css/dashboard.css">

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">

<style>

.profile-box{

width:700px;

max-width:95%;

margin:40px auto;

background:#fff;

padding:35px;

border-radius:12px;

box-shadow:0 5px 15px rgba(0,0,0,.1);

}

.profile-box h2{

color:#1E3A8A;

margin-bottom:25px;

text-align:center;

}

.profile-box label{

display:block;

margin-top:15px;

font-weight:600;

}

.profile-box input{

width:100%;

padding:12px;

margin-top:8px;

border:1px solid #ccc;

border-radius:6px;

font-size:15px;

}

.update-btn{

margin-top:25px;

background:#1E3A8A;

color:white;

border:none;

padding:14px;

width:100%;

border-radius:8px;

font-size:16px;

cursor:pointer;

transition:.3s;

}

.update-btn:hover{

background:#17346B;

}

.back-btn{

display:inline-block;

margin-top:20px;

text-decoration:none;

color:#1E3A8A;

font-weight:600;

}

</style>

</head>

<body>

<div class="sidebar">

<div class="logo">

<h2>📚 Library</h2>

<p>Student Panel</p>

</div>

<ul>

<li><a href="dashboard.php"><i class="fa-solid fa-house"></i> Dashboard</a></li>

<li><a href="search_books.php"><i class="fa-solid fa-magnifying-glass"></i> Search Books</a></li>

<li><a href="borrow_books.php"><i class="fa-solid fa-book-open-reader"></i> Borrow Books</a></li>

<li><a href="borrowed_books.php"><i class="fa-solid fa-book"></i> Borrowed Books</a></li>

<li class="active"><a href="profile.php"><i class="fa-solid fa-user"></i> Profile</a></li>

<li><a href="../login.php"><i class="fa-solid fa-right-from-bracket"></i> Logout</a></li>

</ul>

</div>

<div class="main-content">

<header>

<h1>Edit Profile</h1>

<div class="admin-profile">

Student Panel

</div>

</header>

<div class="profile-box">

<h2>Edit Your Profile</h2>

<form method="POST">

<label>Full Name</label>

<input type="text" name="name" value="<?php echo $row['name']; ?>" required>

<label>Email</label>

<input type="email" name="email" value="<?php echo $row['email']; ?>" required>

<label>Department</label>

<input type="text" name="department" value="<?php echo $row['department']; ?>" required>

<label>Year</label>

<input type="text" name="year" value="<?php echo $row['year']; ?>" required>

<label>Division</label>

<input type="text" name="division" value="<?php echo $row['division']; ?>" required>

<label>Phone</label>

<input type="text" name="phone" value="<?php echo $row['phone']; ?>" required>

<button type="submit" name="update" class="update-btn">

<i class="fa-solid fa-floppy-disk"></i> Update Profile

</button>

</form>

<a href="profile.php" class="back-btn">

← Back to Profile

</a>

</div>

</div>

</body>

</html>