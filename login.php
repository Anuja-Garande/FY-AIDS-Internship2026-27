<?php
session_start();
include "includes/db_connect.php";

$error = "";

if(isset($_POST['login'])){

    $email = $_POST['email'];
    $password = $_POST['password'];
    $role = $_POST['role'];


   if($role == "admin"){

    $sql = "SELECT * FROM admin WHERE email='$email' AND password='$password'";
    $result = mysqli_query($conn, $sql);

    if(mysqli_num_rows($result) > 0){

        $row = mysqli_fetch_assoc($result);

        $_SESSION['admin_id'] = $row['admin_id'];
        $_SESSION['admin_name'] = $row['name'];
        $_SESSION['email'] = $row['email'];

        header("Location: admin/dashboard.php");
        exit();

    } else {

        $error = "Invalid Admin Login";

    }

}


    elseif($role == "student"){

        $sql = "SELECT * FROM student WHERE email='$email' AND password='$password'";
        $result = mysqli_query($conn,$sql);

        if(mysqli_num_rows($result) > 0){
           $row = mysqli_fetch_assoc($result);

$_SESSION['student_id'] = $row['student_id'];
$_SESSION['name'] = $row['name'];
$_SESSION['email'] = $row['email'];
            header("Location: student/dashboard.php");
            exit();
        }
        else{
            $error = "Invalid Student Login";
        }

    }

    else{

        $error = "Please select user type";

    }

}

?>


<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Login | Digital Library Management System</title>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">

<style>

*{
margin:0;
padding:0;
box-sizing:border-box;
font-family:'Poppins',sans-serif;
}

body{
height:100vh;
display:flex;
justify-content:center;
align-items:center;
background:#eef2ff;
}

.login-box{

width:420px;
background:white;
padding:40px;
border-radius:15px;
box-shadow:0 10px 25px rgba(0,0,0,.15);

}

h1{
text-align:center;
color:#1E3A8A;
margin-bottom:10px;
}

p{
text-align:center;
margin-bottom:30px;
color:#666;
}


input,select{

width:100%;
padding:14px;
margin-bottom:18px;
border:1px solid #ccc;
border-radius:8px;

}


button{

width:100%;
padding:14px;
background:#1E3A8A;
color:white;
border:none;
border-radius:8px;
cursor:pointer;

}


.error{

color:red;
text-align:center;
margin-bottom:15px;

}


</style>

</head>


<body>


<div class="login-box">


<h1>📚 Library Login</h1>

<p>Digital Library Management System</p>


<?php

if($error!=""){
echo "<div class='error'>$error</div>";
}

?>


<form method="POST">


<input type="email" name="email" placeholder="Enter Email Address" required>


<input type="password" name="password" placeholder="Password" required>


<select name="role" required>

<option value="">Select User</option>

<option value="admin">Admin</option>

<option value="student">Student</option>

</select>


<button type="submit" name="login">

<i class="fa-solid fa-right-to-bracket"></i> Login

</button>

<p style="text-align:center; margin-top:20px;">

<a href="index.php" style="text-decoration:none; color:#1E3A8A; font-weight:500;">

← Back to Home

</a>

</p>


</form>


</div>


</body>

</html>