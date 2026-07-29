<?php
session_start();
require_once("../database/db.php");

if(isset($_SESSION['user_id'])){

    if($_SESSION['role']=="admin"){
        header("Location: ../admin/dashboard.php");
    }else{
        header("Location: ../user/dashboard.php");
    }
    exit();
}

$error="";

if(isset($_POST['login'])){



    $email=mysqli_real_escape_string($conn,$_POST['email']);
    $password=$_POST['password'];

    $query=mysqli_query($conn,"
    SELECT * FROM users
    WHERE email='$email'
    ");

    if(mysqli_num_rows($query)>0){

        $user=mysqli_fetch_assoc($query);

       if(password_verify($password,$user['password'])){

    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_name'] = $user['full_name'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['role'] = $user['role'];

    if($user['role']=="admin"){
        header("Location: ../admin/dashboard.php");
    }else{
        header("Location: ../user/dashboard.php");
    }

    exit();

}else{

    $error="Invalid Password.";

}

    }else{

        $error="Account Not Found.";

    }

}
?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
content="width=device-width, initial-scale=1.0">

<title>Hotel Login</title>

<link rel="preconnect"
href="https://fonts.googleapis.com">

<link
href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
rel="stylesheet">

<link
rel="stylesheet"
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

width:430px;
padding:40px;

background:rgba(255,255,255,.08);

backdrop-filter:blur(20px);

border:1px solid rgba(255,255,255,.18);

border-radius:22px;

box-shadow:
0 20px 50px rgba(0,0,0,.45);
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

border-color:#38bdf8;

box-shadow:
0 0 15px rgba(56,189,248,.35);

}

.error{

background:#ef4444;

padding:12px;

border-radius:10px;

text-align:center;

color:#fff;

margin-bottom:18px;

font-size:14px;

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

transform:translateY(-2px);

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

box-shadow:
0 30px 70px rgba(0,0,0,.55);

transition:.4s;

}

.login-btn{

letter-spacing:.5px;

}

.login-btn:hover{

transform:translateY(-3px) scale(1.02);

background:linear-gradient(135deg,#38bdf8,#2563eb);

}

.input-group input{

transition:.35s;

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

   <h2>Welcome Back</h2>

<p style="font-size:18px;font-weight:600;color:#38bdf8;margin-top:8px;">
Hotel Room Booking System
</p>

<p>
Login to continue your hotel booking journey.
</p>
    <?php if($error!=""){ ?>

        <div class="error">
            <?php echo $error; ?>
        </div>

    <?php } ?>

    <form method="POST">

        <div class="input-group">

            <i class="fa-solid fa-envelope"></i>

            <input
            type="email"
            name="email"
            placeholder="Enter Email"
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
style="
right:18px;
left:auto;
cursor:pointer;
color:#cbd5e1;
">
</i>

        </div>

        <button
type="submit"
name="login"
class="login-btn"
id="loginBtn">

<i class="fa-solid fa-right-to-bracket"></i>

<span id="btnText">
Login
</span>

</button>

    </form>

    <div class="bottom-text">

        Don't have an account?

        <a href="register.php">
            Register
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
