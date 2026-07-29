<?php

session_start();
require_once("../database/db.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

if (isset($_POST['change_password'])) {

    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Get Current Password From Database
    $query = "SELECT password FROM users WHERE id='$user_id'";
    $result = mysqli_query($conn, $query);
    $user = mysqli_fetch_assoc($result);

    // Check Current Password
    if (!password_verify($current_password, $user['password'])) {

        echo "<script>alert('Current Password is Incorrect!');</script>";

    }
    // Check New Password & Confirm Password
    elseif ($new_password != $confirm_password) {

        echo "<script>alert('New Password and Confirm Password do not match!');</script>";

    }
    else {

        $new_hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

        $update = "UPDATE users
                   SET password='$new_hashed_password'
                   WHERE id='$user_id'";

        if (mysqli_query($conn, $update)) {

            echo "<script>
                    alert('Password Changed Successfully!');
                    window.location='profile.php';
                  </script>";

        } else {

            echo "<script>alert('Something went wrong!');</script>";

        }

    }

}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password</title>
</head>

<body>

<h2>Change Password</h2>

<form action="" method="POST">

    <label>Current Password</label><br>
    <input type="password" name="current_password" required>
    <br><br>

    <label>New Password</label><br>
    <input type="password" name="new_password" required>
    <br><br>

    <label>Confirm New Password</label><br>
    <input type="password" name="confirm_password" required>
    <br><br>

    <button type="submit" name="change_password">
        Change Password
    </button>

</form>

</body>
</html>