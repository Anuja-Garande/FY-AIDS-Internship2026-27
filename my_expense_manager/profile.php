<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: auth/login.php");
    exit();
}

include("database/db.php");

$user_id = $_SESSION['user_id'];
$message = "";

/* ============================
   Get User Details
============================ */

$user = mysqli_query($conn,"
SELECT *
FROM users
WHERE id='$user_id'
LIMIT 1
");

$userData = mysqli_fetch_assoc($user);

/* ============================
   Update Profile
============================ */

if(isset($_POST['update_profile']))
{

    $name = mysqli_real_escape_string($conn,$_POST['name']);
    $email = mysqli_real_escape_string($conn,$_POST['email']);

    $update = mysqli_query($conn,"
    UPDATE users
    SET
    name='$name',
    email='$email'
    WHERE id='$user_id'
    ");

    if($update)
    {
        $message='
        <div class="alert alert-success alert-dismissible fade show">
        Profile Updated Successfully.
        <button class="btn-close" data-bs-dismiss="alert"></button>
        </div>';

        $user = mysqli_query($conn,"
        SELECT *
        FROM users
        WHERE id='$user_id'
        ");

        $userData = mysqli_fetch_assoc($user);
    }
    else
    {
        $message='
        <div class="alert alert-danger alert-dismissible fade show">
        Failed To Update Profile.
        <button class="btn-close" data-bs-dismiss="alert"></button>
        </div>';
    }
}

include("includes/header.php");
include("includes/sidebar.php");
?>

<div class="container-fluid">

<h2 class="fw-bold mb-4">

My Profile

</h2>

<?php echo $message; ?>

<div class="row">

<div class="col-lg-6">

<div class="card shadow">

<div class="card-header bg-primary text-white">

<h4 class="mb-0">

Profile Information

</h4>

</div>

<div class="card-body">

<form method="POST">

<div class="mb-3">

<label class="form-label">

Full Name

</label>

<input
type="text"
name="name"
class="form-control"
value="<?php echo $userData['name']; ?>"
required>

</div>

<div class="mb-3">

<label class="form-label">

Email Address

</label>

<input
type="email"
name="email"
class="form-control"
value="<?php echo $userData['email']; ?>"
required>

</div>

<button
type="submit"
name="update_profile"
class="btn btn-primary">

<i class="bi bi-save"></i>

Update Profile

</button>

</form>

</div>

</div>

</div>
<?php

/* ============================
   Change Password
============================ */

if (isset($_POST['change_password']))
{

    $current_password = $_POST['current_password'];

    $new_password = $_POST['new_password'];

    $confirm_password = $_POST['confirm_password'];

    if (!password_verify($current_password, $userData['password']))
    {

        $message='
        <div class="alert alert-danger alert-dismissible fade show">

        Current Password is Incorrect.

        <button class="btn-close"
        data-bs-dismiss="alert"></button>

        </div>';

    }

    elseif($new_password != $confirm_password)

    {

        $message='
        <div class="alert alert-warning alert-dismissible fade show">

        New Password and Confirm Password do not match.

        <button class="btn-close"
        data-bs-dismiss="alert"></button>

        </div>';

    }

    else

    {

        $hash=password_hash($new_password,PASSWORD_DEFAULT);

        $update=mysqli_query($conn,"
        UPDATE users

        SET password='$hash'

        WHERE id='$user_id'
        ");

        if($update)
        {

            $message='
            <div class="alert alert-success alert-dismissible fade show">

            Password Changed Successfully.

            <button class="btn-close"
            data-bs-dismiss="alert"></button>

            </div>';

        }

        else

        {

            $message='
            <div class="alert alert-danger alert-dismissible fade show">

            Failed To Change Password.

            <button class="btn-close"
            data-bs-dismiss="alert"></button>

            </div>';

        }

    }

}

?>

<div class="col-lg-6">

<div class="card shadow">

<div class="card-header bg-warning">

<h4 class="mb-0">

Change Password

</h4>

</div>

<div class="card-body">

<form method="POST">

<div class="mb-3">

<label class="form-label">

Current Password

</label>

<input
type="password"
name="current_password"
class="form-control"
required>

</div>

<div class="mb-3">

<label class="form-label">

New Password

</label>

<input
type="password"
name="new_password"
class="form-control"
required>

</div>

<div class="mb-3">

<label class="form-label">

Confirm Password

</label>

<input
type="password"
name="confirm_password"
class="form-control"
required>

</div>

<button
type="submit"
name="change_password"
class="btn btn-warning">

<i class="bi bi-key"></i>

Change Password

</button>

</form>

</div>

</div>

</div>

</div>

<br>
<div class="row">

<div class="col-lg-12">

<div class="card shadow">

<div class="card-header bg-info text-white">

<h4 class="mb-0">

Account Summary

</h4>

</div>

<div class="card-body">

<div class="row">

<div class="col-md-3">

<h6 class="text-muted">

User ID

</h6>

<p class="fw-bold">

#<?php echo $userData['id']; ?>

</p>

</div>

<div class="col-md-3">

<h6 class="text-muted">

Full Name

</h6>

<p class="fw-bold">

<?php echo htmlspecialchars($userData['name']); ?>

</p>

</div>

<div class="col-md-3">

<h6 class="text-muted">

Email

</h6>

<p class="fw-bold">

<?php echo htmlspecialchars($userData['email']); ?>

</p>

</div>

<div class="col-md-3">

<h6 class="text-muted">

Registered On

</h6>

<p class="fw-bold">

<?php
if (!empty($userData['created_at'])) {
    echo date("d M Y", strtotime($userData['created_at']));
} else {
    echo "Not Available";
}
?>

</p>

</div>

</div>

<hr>

<div class="alert alert-primary mb-0">

<i class="bi bi-person-check-fill"></i>

Your account information is secure. Keep your password confidential and update it regularly for better security.

</div>

</div>

</div>

</div>

</div>

<?php

include("includes/footer.php");

?>