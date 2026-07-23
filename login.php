<?php

session_start();

include("database/connection.php");

$message = "";

if(isset($_POST['login']))
{

    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if($username=="" || $password=="")
    {
        $message="Please enter Username and Password";
    }
    else
    {

        $sql="SELECT * FROM users WHERE username='$username' AND password='$password'";

        $result=mysqli_query($conn,$sql);

        if(mysqli_num_rows($result)>0)
        {

            $row=mysqli_fetch_assoc($result);

            $_SESSION['user_id']=$row['user_id'];
            $_SESSION['username']=$row['username'];

            header("Location: dashboard.php");
            exit();

        }
        else
        {
            $message="Invalid Username or Password";
        }

    }

}

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Student Login</title>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">

<style>

*{
margin:0;
padding:0;
box-sizing:border-box;
font-family:'Poppins',sans-serif;
}

body{

background:#f4f7fc;

display:flex;

justify-content:center;

align-items:center;

height:100vh;

}

.login-box{

width:380px;

background:#fff;

padding:35px;

border-radius:15px;

box-shadow:0 8px 20px rgba(0,0,0,.15);

}

.logo{

text-align:center;

font-size:55px;

margin-bottom:10px;

}

h2{

text-align:center;

color:#0d6efd;

margin-bottom:5px;

}

.subtitle{

text-align:center;

color:#777;

margin-bottom:25px;

font-size:14px;

}

input{

width:100%;

padding:12px;

margin-bottom:18px;

border:1px solid #ccc;

border-radius:8px;

font-size:15px;

outline:none;

}

input:focus{

border-color:#0d6efd;

}

button{

width:100%;

padding:12px;

background:#0d6efd;

color:white;

border:none;

border-radius:8px;

font-size:16px;

cursor:pointer;

transition:.3s;

}

button:hover{

background:#084298;

}

.error{

margin-top:15px;

text-align:center;

color:red;

font-size:14px;

}

.forgot{

display:block;

margin-top:18px;

text-align:center;

text-decoration:none;

color:#0d6efd;

font-size:14px;

}

.forgot:hover{

text-decoration:underline;

}

@media(max-width:500px){

.login-box{

width:90%;

padding:25px;

}

}

</style>

</head>

<body>

<div class="login-box">

<div class="logo">🎓</div>

<h2>Student Project Portal</h2>

<p class="subtitle">

Artificial Intelligence & Data Science

</p>

<form method="post">

<input
type="text"
name="username"
placeholder="Enter Username"
required>

<input
type="password"
name="password"
placeholder="Enter Password"
required>

<button
type="submit"
name="login">

Login

</button>

<?php

if($message!="")
{

echo "<div class='error'>$message</div>";

}

?>

<a href="#" class="forgot">

Forgot Password?

</a>

</form>

</div>

</body>

</html>