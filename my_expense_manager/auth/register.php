<?php
session_start();
include("../database/db.php");

$message = "";

if(isset($_POST['register']))
{
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Check if email already exists
    $check = mysqli_query($conn, "SELECT * FROM users WHERE email='$email'");

    if(mysqli_num_rows($check) > 0)
    {
        $message = "Email already exists!";
    }
    elseif($password != $confirm_password)
    {
        $message = "Passwords do not match!";
    }
    else
    {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $insert = mysqli_query($conn, "
            INSERT INTO users(name, email, password)
            VALUES('$name', '$email', '$hashed_password')
        ");

        if($insert)
        {
            header("Location: login.php");
            exit();
        }
        else
        {
            $message = "Registration failed!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Register</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

</head>

<body class="bg-light">

<div class="container">

    <div class="row justify-content-center mt-5">

        <div class="col-md-6">

            <div class="card shadow">

                <div class="card-header bg-success text-white text-center">

                    <h3>Create Account</h3>

                </div>

                <div class="card-body">

                    <?php if($message != "") { ?>
                        <div class="alert alert-danger">
                            <?php echo $message; ?>
                        </div>
                    <?php } ?>

                    <form method="POST">

                        <div class="mb-3">
                            <label class="form-label">Full Name</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Confirm Password</label>
                            <input type="password" name="confirm_password" class="form-control" required>
                        </div>

                        <button type="submit" name="register" class="btn btn-success w-100">
                            Register
                        </button>

                    </form>

                    <hr>

                    <p class="text-center">
                        Already have an account?
                        <a href="login.php">Login Here</a>
                    </p>

                </div>

            </div>

        </div>

    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>