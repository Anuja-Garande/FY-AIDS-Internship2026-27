<div class="top-navbar">

   <?php if(!isset($hideSearch) || !$hideSearch){ ?>

<div class="search-box">
    <i class="fas fa-search"></i>
    <input type="text" placeholder="Search...">
</div>

<?php } ?>

<?php
$notification_query = mysqli_query($conn,"
SELECT COUNT(*) AS total
FROM bookings
WHERE booking_status='Pending'
");

$notification = mysqli_fetch_assoc($notification_query);
$notification_count = $notification['total'];
?>

    <div class="top-right">

    <?php
$notification_list = mysqli_query($conn,"
SELECT users.full_name, rooms.room_name
FROM bookings
INNER JOIN users ON bookings.user_id = users.id
INNER JOIN rooms ON bookings.room_id = rooms.id
WHERE bookings.booking_status='Pending'
ORDER BY bookings.id DESC
LIMIT 5
");
?>

        <div class="notification-box" id="notificationBox">

    <i class="fas fa-bell notification"></i>

    <?php if($notification_count > 0){ ?>

        <span class="notification-badge">
            <?php echo $notification_count; ?>
        </span>

    <?php } ?>



<div class="notification-dropdown" id="notificationDropdown">

<?php

if(mysqli_num_rows($notification_list)>0){

while($n=mysqli_fetch_assoc($notification_list)){

?>

<p>
<b><?php echo $n['full_name']; ?></b><br>
Booked <?php echo $n['room_name']; ?>
</p>

<?php

}

}else{

?>

<p>No Notifications</p>

<?php } ?>

</div>
</div>



        <div class="admin-profile">

        <?php
        $adminData = mysqli_fetch_assoc(
            mysqli_query(
                $conn,
                "SELECT profile_image FROM users WHERE id='".$_SESSION['user_id']."'"
            )
        );
        ?>

        <?php if(!empty($adminData['profile_image'])){ ?>

            <img src="../uploads/profile/<?php echo htmlspecialchars($adminData['profile_image']); ?>"
                 style="width:42px;height:42px;border-radius:50%;object-fit:cover;">

        <?php } else { ?>

            <i class="fas fa-user-circle"></i>

        <?php } ?>

        <span><?php echo htmlspecialchars($_SESSION['user_name']); ?></span>

        </div>

    </div>

</div> 

