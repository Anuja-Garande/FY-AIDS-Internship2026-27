<?php
session_start();
require_once("database/db.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$query = "SELECT bookings.*, rooms.room_name, rooms.room_number
FROM bookings
INNER JOIN rooms
ON bookings.room_id = rooms.id
WHERE bookings.user_id='$user_id'
ORDER BY bookings.created_at DESC";

$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>My Bookings</title>

<link rel="stylesheet" href="assets/css/user.css">
<link rel="stylesheet" href="assets/css/my-bookings.css">
<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">



</head>

<body>

<div class="container">

<div class="page-header">

<h1>
<i class="fa-solid fa-calendar-check"></i>
My Bookings
</h1>

<a href="user/dashboard.php" class="back-btn">
<i class="fa-solid fa-arrow-left"></i>
Dashboard
</a>

</div>

<div class="table-box">

<table>

<thead>

<tr>

<th>ID</th>
<th>Room No</th>
<th>Room</th>
<th>Check In</th>
<th>Check Out</th>
<th>Guests</th>
<th>Total Amount</th>
<th>Booked On</th>
<th>Status</th>
<th>Payment</th>
<th>Method</th>
<th>Actions</th>

</tr>

</thead>

<tbody>

<?php

if(mysqli_num_rows($result)>0){

while($row=mysqli_fetch_assoc($result)){

?>

<tr>

<td><?php echo $row['id']; ?></td>

<td><?php echo htmlspecialchars($row['room_number']); ?></td>

<td><?php echo htmlspecialchars($row['room_name']); ?></td>

<td><?php echo date("d M Y", strtotime($row['check_in'])); ?></td>

<td><?php echo date("d M Y", strtotime($row['check_out'])); ?></td>

<td><?php echo $row['guests']; ?></td>

<td>
₹ <?php echo number_format($row['total_amount']); ?>
</td>
<td>
<?php echo date("d M Y", strtotime($row['created_at'])); ?>
</td>

<td>

<span class="status <?php echo strtolower($row['booking_status']); ?>">

<?php echo $row['booking_status']; ?>

</span>

</td>

<td>

<?php

if($row['payment_status']=="Paid"){

?>

<span class="status confirmed">

<i class="fa-solid fa-circle-check"></i>

Paid

</span>

<?php

}else{

?>

<span class="status pending">

<i class="fa-solid fa-clock"></i>

Pending

</span>

<?php

}

?>

</td>

<td>

<?php

echo empty($row['payment_method'])

? "-"

: htmlspecialchars($row['payment_method']);

?>

</td>


<td>

<?php

if($row['payment_status']=="Pending"){

?>

<a href="payment.php?booking_id=<?php echo $row['id']; ?>"
class="action-btn"
style="background:#f59e0b;color:#fff;">

<i class="fa-solid fa-credit-card"></i>

Pay Now

</a>

<br><br>

<a href="cancel-booking.php?id=<?php echo $row['id']; ?>"
class="action-btn cancel-btn"
onclick="return confirm('Are you sure you want to cancel this booking?')">

<i class="fa-solid fa-ban"></i>

Cancel

</a>

<?php

}else{

?>

<a href="user/invoice.php?booking_id=<?php echo $row['id']; ?>"
class="action-btn invoice-btn">

<i class="fa-solid fa-file-invoice"></i>

Invoice

</a>

<?php

}

?>

</td>

</tr>

<?php

}

}else{

?>

<tr>
    <td colspan="10" class="no-booking" style="text-align:center;padding:40px;">

        <i class="fa-solid fa-calendar-xmark"
        style="font-size:50px;color:#999;"></i>

        <br><br>

        <strong>No Bookings Found</strong>

    </td>
</tr>

<?php
}
?>

</tbody>

</table>

</div>

</div>

</body>

</html>
