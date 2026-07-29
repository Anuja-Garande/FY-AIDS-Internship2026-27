<?php

require_once("../database/db.php");

session_start();

if(!isset($_SESSION['user_id']))
{
  header("Location: ../auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$query = "SELECT bookings.*, rooms.room_name
FROM bookings
INNER JOIN rooms
ON bookings.room_id = rooms.id
WHERE bookings.user_id='$user_id'";

$result = mysqli_query($conn, $query);

?>

<!DOCTYPE html>
<html>
<head>
    <title>My Bookings</title>
</head>
<body>

<h2>My Bookings</h2>

<table border="1" cellpadding="10">
    <tr>
        <th>Booking ID</th>
        <th>Room Name</th>
        <th>Check In</th>
        <th>Check Out</th>
        <th>Guests</th>
        <th>Total Amount</th>
        <th>Status</th>
        <th>Action</th>
    </tr>

<?php

while($row = mysqli_fetch_assoc($result))
{
?>

<tr>
    <td><?php echo $row['id']; ?></td>
    <td><?php echo $row['room_name']; ?></td>
    <td><?php echo $row['check_in']; ?></td>
    <td><?php echo $row['check_out']; ?></td>
    <td><?php echo $row['guests']; ?></td>
    <td>₹<?php echo $row['total_amount']; ?></td>
    <td><?php echo $row['booking_status']; ?></td>
    <td>

<?php
if($row['booking_status'] == "Pending")
{
?>
    <a href="cancel-booking.php?id=<?php echo $row['id']; ?>">
        Cancel
    </a>
<?php
}
else
{
    echo $row['booking_status'];
}
?>

<br><br>

<a href="invoice.php?id=<?php echo $row['id']; ?>">
    Invoice
</a>

</td>
</tr>

<?php
}
?>

</table>

</body>
</html>