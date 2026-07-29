<?php
session_start();
require_once("../database/db.php");

if(isset($_POST['register']))
{
    $full_name = mysqli_real_escape_string($conn, $_POST['full_name']);
    $email     = mysqli_real_escape_string($conn, $_POST['email']);
    $phone     = mysqli_real_escape_string($conn, $_POST['phone']);
    if (!preg_match('/^[0-9]{10}$/', $phone))
{
    echo "<script>alert('Phone number must be exactly 10 digits.');</script>";
    exit();
}
    $password  = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $check = mysqli_query($conn,"SELECT * FROM users WHERE email='$email'");

    if(mysqli_num_rows($check)>0)
    {
        echo "<script>alert('Email Already Registered!');</script>";
    }
    else
    {
        $query="INSERT INTO users(full_name,email,phone,password)
        VALUES('$full_name','$email','$phone','$password')";

        if(mysqli_query($conn,$query))
{
    $user_id = mysqli_insert_id($conn);

    $_SESSION['user_id'] = $user_id;
    $_SESSION['user_name'] = $full_name;
    $_SESSION['user_email'] = $email;

    echo "<script>
    alert('Registration Successful. Welcome!');
    window.location='../user/dashboard.php';
    </script>";
    exit;
}
        else
        {
            echo "<script>alert('Registration Failed');</script>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Create Account</title>

<link rel="preconnect" href="https://fonts.googleapis.com">

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

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

background:
linear-gradient(rgba(7,15,30,.72),rgba(7,15,30,.72)),
url('../assets/images/login-bg.jpeg');

background-size:cover;
background-position:center;

}

.login-card{

width:460px;

padding:32px;

background:rgba(255,255,255,.08);

backdrop-filter:blur(20px);

border:1px solid rgba(255,255,255,.18);

border-radius:22px;

box-shadow:0 20px 50px rgba(0,0,0,.45);

animation:floatCard 4s ease-in-out infinite;

}

.logo{

width:90px;
height:90px;

margin:auto;
margin-bottom:20px;

border-radius:50%;

background:linear-gradient(135deg,#38bdf8,#0ea5e9);

display:flex;
justify-content:center;
align-items:center;

font-size:34px;

color:#fff;

box-shadow:0 10px 25px rgba(14,165,233,.45);

}

.login-card h2{

text-align:center;

color:#fff;

font-size:30px;

margin-bottom:8px;

}

.login-card p{

text-align:center;

color:#cbd5e1;

margin-bottom:30px;

font-size:14px;

}

.input-group{

position:relative;

margin-bottom:20px;

}

.input-group i{

position:absolute;

left:16px;

top:17px;

color:#94a3b8;

font-size:16px;

}

.input-group input{

width:100%;

padding:15px 15px 15px 48px;

border-radius:12px;

border:1px solid rgba(255,255,255,.15);

background:rgba(255,255,255,.08);

color:#fff;

font-size:15px;

outline:none;

transition:.3s;

}

.input-group input::placeholder{

color:#cbd5e1;

}

.input-group input:focus{

border-color:#ffffff;

box-shadow:0 0 15px rgba(56,189,248,.35);

}

.login-btn{

width:100%;

padding:15px;

border:none;

border-radius:12px;

background:linear-gradient(135deg,#0ea5e9,#2563eb);

color:#fff;

font-size:16px;

font-weight:600;

cursor:pointer;

transition:.3s;

margin-top:10px;

}

.login-btn:hover{

transform:translateY(-3px) scale(1.02);

background:linear-gradient(135deg,#38bdf8,#2563eb);

box-shadow:0 10px 25px rgba(37,99,235,.45);

}

.bottom-text{

text-align:center;

margin-top:20px;

color:#e2e8f0;

font-size:14px;

}

.bottom-text a{

color:#38bdf8;

text-decoration:none;

font-weight:600;

}

.bottom-text a:hover{

text-decoration:underline;

}

@keyframes floatCard{

0%{

transform:translateY(0px);

}

50%{

transform:translateY(-10px);

}

100%{

transform:translateY(0px);

}

}

.login-card:hover{

box-shadow:0 30px 70px rgba(0,0,0,.55);

transition:.4s;

}

.input-group input:hover{

border-color:#60a5fa;

}

</style>

</head>

<body>

<div class="login-card">

<div class="logo">

<i class="fa-solid fa-hotel"></i>

</div>

<h2>Create Account</h2>

<p style="font-size:18px;font-weight:600;color:#38bdf8;margin-top:8px;">

Hotel Room Booking System

</p>

<p>

Create your account to start booking rooms.

</p>

<form method="POST">

<div class="input-group">

<i class="fa-solid fa-user"></i>

<input
type="text"
name="full_name"
placeholder="Enter Full Name"
required>

</div>

<div class="input-group">

<i class="fa-solid fa-envelope"></i>

<input
type="email"
name="email"
placeholder="Enter Email"
required>

</div>

<div class="input-group">

<i class="fa-solid fa-phone"></i>

<input
type="tel"
name="phone"
placeholder="Enter Phone Number"
maxlength="10"
pattern="[0-9]{10}"
inputmode="numeric"
oninput="this.value=this.value.replace(/[^0-9]/g,'').slice(0,10)"
required>

</div>

<div class="input-group">

<i class="fa-solid fa-lock"></i>

<input
type="password"
id="password"
name="password"
placeholder="Enter Password"
required>

<i
class="fa-solid fa-eye"
id="togglePassword"
style="right:18px;left:auto;cursor:pointer;color:#cbd5e1;">
</i>

</div>

<button
type="submit"
name="register"
class="login-btn">

<i class="fa-solid fa-user-plus"></i> Register



</button>

</form>

<div class="bottom-text">

Already have an account?

<a href="login.php">

Login

</a>

</div>

</div>

<script>

const password=document.getElementById("password");

const toggle=document.getElementById("togglePassword");

toggle.addEventListener("click",function(){

if(password.type==="password"){

password.type="text";

this.classList.remove("fa-eye");

this.classList.add("fa-eye-slash");

}else{

password.type="password";

this.classList.remove("fa-eye-slash");

this.classList.add("fa-eye");

}

});

</script>

</body>

</html>