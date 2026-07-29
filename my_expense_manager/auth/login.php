<?php
session_start();

include("../database/db.php");

$message = "";

if(isset($_POST['login']))
{
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];

    $query = mysqli_query($conn,"SELECT * FROM users WHERE email='$email'");

    if(mysqli_num_rows($query)>0)
    {
        $user=mysqli_fetch_assoc($query);

        if(password_verify($password,$user['password']))
        {
            $_SESSION['user_id']=$user['id'];
            $_SESSION['user_name']=$user['name'];

            header("Location: ../index.php");
            exit();
        }
        else
        {
            $message="Incorrect Password!";
        }
    }
    else
    {
        $message="Email not found!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Login | My Expense Manager</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<link rel="stylesheet" href="../assets/css/style.css">

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<style>

.login-card{

    background:rgba(255,255,255,.92);

    backdrop-filter:blur(15px);

    border-radius:20px;

    overflow:hidden;

    box-shadow:0 20px 50px rgba(0,0,0,.20);

}

.card-header{

    background:linear-gradient(135deg,#2563eb,#4f46e5)!important;

    padding:20px;

    border:none;

}

.form-control{

    height:52px;

    border-radius:12px;

}

.btn{

    height:52px;

    border-radius:12px;

}

</style>

</head>

<body class="login-page">

<div class="container">

<div class="row justify-content-center align-items-center" style="min-height:100vh;">

<div class="col-lg-5 col-md-7">

<div class="card login-card">

<div class="card-header text-center text-white">

<h2 class="mb-0">Login</h2>

</div>

<div class="card-body p-4">

<?php if($message!=""){ ?>

<div class="alert alert-danger">

<?php echo $message; ?>

</div>

<?php } ?>

<form method="POST">

<div class="mb-3">

<label class="form-label">Email</label>

<input
type="email"
name="email"
class="form-control"
placeholder="Enter your email"
required>

</div>

<div class="mb-3">

<label class="form-label">Password</label>

<input
type="password"
name="password"
class="form-control"
placeholder="Enter your password"
required>

</div>

<button
type="submit"
name="login"
class="btn btn-primary w-100">

Login

</button>

</form>

<hr>

<p class="text-center mb-0">

Don't have an account?

<a href="register.php">Register Here</a>

</p>

</div>

</div>

</div>

</div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>