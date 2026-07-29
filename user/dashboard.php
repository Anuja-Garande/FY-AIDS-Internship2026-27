<?php
session_start();
require_once("../database/db.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];
$user_query = mysqli_query($conn, "SELECT profile_image,email FROM users WHERE id='$user_id'");
$user_data = mysqli_fetch_assoc($user_query);

$profile_image = $user_data['profile_image'];
// Total Bookings
$total_query = "SELECT COUNT(*) AS total FROM bookings WHERE user_id = '$user_id'";
$total_result = mysqli_query($conn, $total_query);
$total_booking = mysqli_fetch_assoc($total_result);
// Pending Bookings
$pending_query = "SELECT COUNT(*) AS pending 
                  FROM bookings 
                  WHERE user_id = '$user_id' 
                  AND booking_status = 'Pending'";

$pending_result = mysqli_query($conn, $pending_query);
$pending_booking = mysqli_fetch_assoc($pending_result);
// Confirmed Bookings
$confirmed_query = "SELECT COUNT(*) AS confirmed
                    FROM bookings
                    WHERE user_id = '$user_id'
                    AND booking_status = 'Confirmed'";

$confirmed_result = mysqli_query($conn, $confirmed_query);
$confirmed_booking = mysqli_fetch_assoc($confirmed_result);

// Cancelled Bookings
$cancelled_query = "SELECT COUNT(*) AS cancelled
                    FROM bookings
                    WHERE user_id = '$user_id'
                    AND booking_status = 'Cancelled'";

$cancelled_result = mysqli_query($conn, $cancelled_query);
$cancelled_booking = mysqli_fetch_assoc($cancelled_result);
// Recent Bookings
$recent_query = "
SELECT
    bookings.*,
    rooms.room_name
FROM bookings
INNER JOIN rooms
ON bookings.room_id = rooms.id
WHERE bookings.user_id = '$user_id'
ORDER BY bookings.created_at DESC
LIMIT 5
";

$recent_result = mysqli_query($conn, $recent_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard</title>

    <link rel="stylesheet" href="../assets/css/user.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>
<body>

<!-- ================= NAVBAR ================= -->

<nav class="navbar">

    <div class="logo">
        🏨 Hotel Booking
    </div>

    <ul class="menu">

        <li><a href="../index.php">Home</a></li>

        <li><a href="../rooms.php">Rooms</a></li>

        <li><a href="../my-bookings.php">My Bookings</a></li>

        <li><a href="../contact.php">Contact</a></li>

    </ul>

    <div class="user-dropdown">
<button class="user-btn">

<?php if(!empty($profile_image) && file_exists("../uploads/profile/".$profile_image)){ ?>

    <img src="../uploads/profile/<?php echo htmlspecialchars($profile_image); ?>" class="nav-profile-img">

<?php } else { ?>

    <i class="fa-solid fa-user"></i>

<?php } ?>

    <span><?php echo htmlspecialchars($user_name); ?></span>

    <i class="fa-solid fa-chevron-down"></i>

</button>

    <div class="dropdown-menu">

    <div class="dropdown-header">

        <?php if(!empty($profile_image) && file_exists("../uploads/profile/".$profile_image)){ ?>

            <img src="../uploads/profile/<?php echo htmlspecialchars($profile_image); ?>" class="dropdown-profile-img">

        <?php } else { ?>

            <i class="fa-solid fa-circle-user dropdown-icon"></i>

        <?php } ?>

        <h4><?php echo htmlspecialchars($user_name); ?></h4>

<small><?php echo htmlspecialchars($user_data['email']); ?></small>

<span class="online-badge">
    <i class="fa-solid fa-circle"></i> Online
</span>

    </div>

    <hr>

    <a href="profile.php">
        <i class="fa-solid fa-user"></i>
        My Profile
    </a>

    <a href="../my-bookings.php">
        <i class="fa-solid fa-calendar-check"></i>
        My Bookings
    </a>

    <a href="invoice.php">
        <i class="fa-solid fa-file-invoice"></i>
        Invoices
    </a>

    <a href="change-password.php">
        <i class="fa-solid fa-key"></i>
        Change Password
    </a>

    <a href="../auth/logout.php">
        <i class="fa-solid fa-right-from-bracket"></i>
        Logout
    </a>

</div>

</div>

</nav>


<!-- ================= CONTAINER ================= -->

<div class="container">


<!-- ================= HERO ================= -->

<section class="hero">

<div class="hero-left">

<h1>
Welcome Back,
<?php echo htmlspecialchars($user_name); ?> 👋
</h1>

<p>

Book luxury rooms,
manage your bookings,
and enjoy a premium hotel experience.

</p>

<div class="hero-buttons">

<a href="../rooms.php" class="btn-primary">

<i class="fa-solid fa-bed"></i>

Browse Rooms

</a>

<a href="../my-bookings.php" class="btn-secondary">

<i class="fa-solid fa-calendar-check"></i>

My Bookings

</a>

</div>

</div>


<div class="hero-right">

    <img src="../assets/images/banner/hotel-banner.png" alt="Luxury Hotel">

</div>

</section>


<!-- ================= STATS ================= -->

<section class="stats">


<div class="card">

<i class="fa-solid fa-calendar-check"></i>

<h2>

<?php echo $total_booking['total']; ?>

</h2>

<p>Total Bookings</p>

</div>


<div class="card">

<i class="fa-solid fa-circle-check"></i>

<h2>

<?php echo $confirmed_booking['confirmed']; ?>

</h2>

<p>Confirmed</p>

</div>


<div class="card">

<i class="fa-solid fa-hourglass-half"></i>

<h2>

<?php echo $pending_booking['pending']; ?>

</h2>

<p>Pending</p>

</div>


<div class="card">

<i class="fa-solid fa-circle-xmark"></i>

<h2>

<?php echo $cancelled_booking['cancelled']; ?>

</h2>

<p>Cancelled</p>

</div>

</section>

<!-- ================= QUICK ACTIONS ================= -->

<section class="quick-actions">

    <h2>Quick Actions</h2>

    <div class="action-grid">

        <a href="../rooms.php" class="action-card">

            <i class="fa-solid fa-bed"></i>

            <h3>Browse Rooms</h3>

            <p>Explore all luxury rooms.</p>

        </a>

        <a href="../my-bookings.php" class="action-card">

            <i class="fa-solid fa-calendar-check"></i>

            <h3>My Bookings</h3>

            <p>View your booking history.</p>

        </a>

        <a href="profile.php" class="action-card">

            <i class="fa-solid fa-user"></i>

            <h3>My Profile</h3>

            <p>Update your profile details.</p>

        </a>

        <a href="../my-bookings.php" class="action-card">

            <i class="fa-solid fa-file-invoice"></i>

            <h3>Booking History</h3>

            <p>View bookings and download invoices.</p>

        </a>

    </div>

</section>
<!-- ================= RECENT BOOKINGS ================= -->

<section class="recent-bookings">

    <div class="section-header">

    <h2>Recent Bookings</h2>

    <a href="../my-bookings.php" class="view-all-btn">
        View All
        <i class="fa-solid fa-arrow-right"></i>
    </a>

</div>

    <table>

        <thead>

            <tr>

                <th>Room</th>

                <th>Check In</th>

                <th>Check Out</th>

                <th>Status</th>

                <th>Amount</th>

            </tr>

        </thead>

        <tbody>

        <?php

        if(mysqli_num_rows($recent_result)>0)
        {

            while($row=mysqli_fetch_assoc($recent_result))
            {

        ?>

        <tr>

            <td><?php echo htmlspecialchars($row['room_name']); ?></td>

            <td><?php echo date("d M Y", strtotime($row['check_in'])); ?></td>

            <td><?php echo date("d M Y", strtotime($row['check_out'])); ?></td>

            <td>

                <span class="status <?php echo strtolower($row['booking_status']); ?>">

                    <?php echo $row['booking_status']; ?>

                </span>

            </td>

            <td>

                ₹<?php echo number_format($row['total_amount'],2); ?>

            </td>

        </tr>

        <?php

            }

        }

        else

        {

            echo "<tr><td colspan='5'>No Bookings Found</td></tr>";

        }

        ?>

        </tbody>

    </table>

</section>


</div>
<script>
const userBtn = document.querySelector(".user-btn");
const dropdown = document.querySelector(".dropdown-menu");

userBtn.addEventListener("click", function(e){

    e.stopPropagation();

    dropdown.classList.toggle("show");

});

document.addEventListener("click", function(){

    dropdown.classList.remove("show");

});
</script>

</body>
</html>