<?php

session_start();


if(!isset($_SESSION['username']))
{
    header("Location: login.php");
    exit();
}



$conn=mysqli_connect(
"localhost",
"root",
"",
"inventory_management"
);



if(!$conn)
{
    die("Database Connection Failed");
}



$user_id=$_SESSION['user_id'];



if(isset($_POST['change']))
{


$current_password=$_POST['current_password'];

$new_password=$_POST['new_password'];

$confirm_password=$_POST['confirm_password'];



$result=mysqli_query(
$conn,
"
SELECT password 
FROM users 
WHERE id='$user_id'
"
);



$user=mysqli_fetch_assoc($result);





if(!password_verify($current_password,$user['password']))
{

$error="Current password is incorrect.";

}

else if($new_password != $confirm_password)
{

$error="New password and confirm password do not match.";

}

else
{


$hashed_password=password_hash(
$new_password,
PASSWORD_DEFAULT
);



mysqli_query(
$conn,
"
UPDATE users

SET password='$hashed_password'

WHERE id='$user_id'
"
);



$success="Password changed successfully.";

}



}



?>



<!DOCTYPE html>

<html>

<head>


<title>Change Password</title>



<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">



<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">



<link rel="stylesheet" href="assets/css/admin.css">



</head>



<body>



<?php include("includes/sidebar.php"); ?>




<div id="main-content">





<div class="d-flex justify-content-between align-items-center mb-4">


<h2 class="page-title">


<i class="bi bi-key"></i>


Change Password


</h2>


</div>







<?php if(isset($error)){ ?>


<div class="alert alert-danger">

<i class="bi bi-exclamation-triangle"></i>

<?php echo $error; ?>


</div>


<?php } ?>






<?php if(isset($success)){ ?>


<div class="alert alert-success">

<i class="bi bi-check-circle"></i>

<?php echo $success; ?>


</div>


<?php } ?>








<div class="card shadow border-0">

    <div class="card-header">
        <h5 class="mb-0 fw-semibold">
            <i class="bi bi-shield-lock me-2"></i>
            Update Password
        </h5>
    </div>

    <div class="card-body p-4">

        <form method="POST">

            <div class="mb-3">
                <label class="form-label fw-semibold">
                    Current Password
                </label>

                <input
                    type="password"
                    name="current_password"
                    class="form-control"
                    placeholder="Enter current password"
                    required>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">
                    New Password
                </label>

                <input
                    type="password"
                    name="new_password"
                    class="form-control"
                    placeholder="Enter new password"
                    required>
            </div>

            <div class="mb-4">
                <label class="form-label fw-semibold">
                    Confirm New Password
                </label>

                <input
                    type="password"
                    name="confirm_password"
                    class="form-control"
                    placeholder="Confirm new password"
                    required>
            </div>

            <button
                type="submit"
                name="change"
                class="btn btn-warning">
                <i class="bi bi-key-fill me-1"></i>
                Change Password
            </button>

            <a href="profile.php" class="btn btn-secondary ms-2">
                <i class="bi bi-arrow-left me-1"></i>
                Cancel
            </a>

        </form>

    </div>

</div>




</div>






</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script src="assets/js/darkmode.js"></script>




</body>


</html>