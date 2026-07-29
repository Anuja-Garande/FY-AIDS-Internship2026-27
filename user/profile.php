<?php

session_start();
require_once("../database/db.php");

if(!isset($_SESSION['user_id']))
{
    header("Location: ../auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$query = "SELECT * FROM users WHERE id='$user_id'";

$result = mysqli_query($conn,$query);

$user = mysqli_fetch_assoc($result);

?>

<!DOCTYPE html>
<html>

<head>
    <link rel="stylesheet" href="../assets/css/user.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <title>My Profile</title>
</head>

<body>

<div class="container">

    <section class="profile-card">

   <div class="profile-image">

<?php if(!empty($user['profile_image']) && file_exists("../uploads/profile/".$user['profile_image'])){ ?>

    <img src="../uploads/profile/<?php echo htmlspecialchars($user['profile_image']); ?>" alt="Profile Image">

<?php } else { ?>

    <i class="fa-solid fa-circle-user"></i>

<?php } ?>

</div>

<div class="profile-details">

            <h2>
                <?php echo htmlspecialchars($user['full_name']); ?>
            </h2>

            <p>
                <i class="fa-solid fa-envelope"></i>
                <?php echo htmlspecialchars($user['email']); ?>
            </p>

            <p>
                <i class="fa-solid fa-phone"></i>
                <?php echo htmlspecialchars($user['phone']); ?>
            </p>

            <div class="profile-buttons">

                <a href="edit-profile.php" class="btn-primary">
                    <i class="fa-solid fa-user-pen"></i>
                    Edit Profile
                </a>

                <a href="change-password.php" class="btn-secondary">
                    <i class="fa-solid fa-key"></i>
                    Change Password
                </a>

            </div>

        </div>

    </section>

</div>

</body>

</html> 