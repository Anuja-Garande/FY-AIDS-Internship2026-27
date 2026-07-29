<?php
session_start();
require_once("../database/db.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

if ($_SESSION['role'] != "admin") {
    die("Access Denied!");
}

$admin_name = $_SESSION['user_name'];

$search = "";

$from = isset($_GET['from']) ? $_GET['from'] : "";
$to = isset($_GET['to']) ? $_GET['to'] : "";

if(isset($_GET['search'])){
    $search = mysqli_real_escape_string($conn, $_GET['search']);
}

$sql = "SELECT
            bookings.id,
            users.full_name,
            rooms.room_name,
            bookings.total_amount,
            bookings.booking_status
        FROM bookings
        INNER JOIN users ON bookings.user_id = users.id
        INNER JOIN rooms ON bookings.room_id = rooms.id";

$where = array();

if($search != ""){
    $where[] = "(users.full_name LIKE '%$search%'
                OR rooms.room_name LIKE '%$search%'
                OR bookings.booking_status LIKE '%$search%')";
}

if($from != ""){
    $where[] = "DATE(bookings.created_at) >= '$from'";
}

if($to != ""){
    $where[] = "DATE(bookings.created_at) <= '$to'";
}

if(count($where) > 0){
    $sql .= " WHERE " . implode(" AND ", $where);
}



$limit = 10;

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

if($page < 1){
    $page = 1;
}

$offset = ($page - 1) * $limit;

$sql .= " ORDER BY bookings.id DESC LIMIT $offset, $limit";

$result = mysqli_query($conn, $sql);

$count_sql = "SELECT COUNT(*) AS total
              FROM bookings
              INNER JOIN users ON bookings.user_id = users.id
              INNER JOIN rooms ON bookings.room_id = rooms.id";

              if(count($where) > 0){
    $count_sql .= " WHERE " . implode(" AND ", $where);
}

if($search != ""){
    $count_sql .= " WHERE users.full_name LIKE '%$search%'
                    OR rooms.room_name LIKE '%$search%'
                    OR bookings.booking_status LIKE '%$search%'";
}

$count_result = mysqli_query($conn, $count_sql);

$total_records = mysqli_fetch_assoc($count_result)['total'];

$total_pages = ceil($total_records / $limit);

$total = mysqli_query($conn, "SELECT SUM(total_amount) AS total FROM bookings WHERE booking_status='Confirmed'");
$total_payment = mysqli_fetch_assoc($total);

$paid = mysqli_query($conn, "SELECT COUNT(*) AS total FROM bookings WHERE booking_status='Confirmed'");
$paid_count = mysqli_fetch_assoc($paid)['total'];

$pending = mysqli_query($conn, "SELECT COUNT(*) AS total FROM bookings WHERE booking_status!='Confirmed'");
$pending_count = mysqli_fetch_assoc($pending)['total'];

$total_bookings = mysqli_query($conn, "SELECT COUNT(*) AS total FROM bookings");
$total_booking_count = mysqli_fetch_assoc($total_bookings)['total'];
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Payments</title>

<link rel="stylesheet" href="../assets/css/admin.css">

<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

</head>

<body>

<div class="admin-container">

<?php $current_page = "payments"; ?>
<?php include("includes/sidebar.php"); ?>

<div class="main-content">

<div class="top-navbar">

<form method="GET" class="search-box">

<i class="fas fa-search"></i>

<input
type="text"
name="search"
placeholder="Search Payment..."
value="<?php echo htmlspecialchars($search); ?>">

<input
type="date"
name="from"
value="<?php echo isset($_GET['from']) ? $_GET['from'] : ''; ?>">

<input
type="date"
name="to"
value="<?php echo isset($_GET['to']) ? $_GET['to'] : ''; ?>">

<button type="submit" class="btn-edit">
Search
</button>

</form>

<div class="top-right">

<i class="fas fa-bell notification"></i>

<div class="admin-profile">
<i class="fas fa-user-circle"></i>
<span><?php echo htmlspecialchars($admin_name); ?></span>
</div>

</div>

</div>

<h1>Payments</h1>

<div class="dashboard-cards">

<div class="card revenue-card">
<h3>Total Revenue</h3>
<p>₹<?php echo number_format($total_payment['total'] ?? 0); ?></p>
</div>

<div class="card">
<h3>Paid Payments</h3>
<p><?php echo $paid_count; ?></p>
</div>

<div class="card">
<h3>Pending Payments</h3>
<p><?php echo $pending_count; ?></p>
</div>

<div class="card">
<h3>Total Bookings</h3>
<p><?php echo $total_booking_count; ?></p>
</div>

</div>

<div class="recent-bookings">

<table>

<thead>

<tr>

<th>ID</th>
<th>User</th>
<th>Room</th>
<th>Amount</th>
<th>Payment ID</th>
<th>Status</th>
<th>Method</th>

</tr>

</thead>

<tbody>

<?php

if(mysqli_num_rows($result)>0){

while($row=mysqli_fetch_assoc($result)){

?>

<tr>

<td><?php echo $row['id']; ?></td>

<td><?php echo $row['full_name']; ?></td>

<td><?php echo $row['room_name']; ?></td>

<td>₹<?php echo number_format($row['total_amount']); ?></td>

<td>PAY-<?php echo 10000 + $row['id']; ?></td>



<td>
<?php
if($row['booking_status']=="Confirmed"){
    echo "<span class='status confirmed'>Paid</span>";
}else{
    echo "<span class='status pending'>Pending</span>";
}
?>
</td>

<td>
<?php
if($row['booking_status']=="Confirmed"){

    $methods = ["UPI","Card","Cash"];
    echo $methods[$row['id'] % 3];

}else{

    echo "--";

}
?>
</td>

</td>

</tr>

<?php

}

}else{

?>

<tr>

<td colspan="7" style="text-align:center;">
No Payments Found
</td>

</tr>

<?php

}

?>

</tbody>

</table>
<?php if($total_pages > 1){ ?>

<div style="margin-top:20px; text-align:center;">

<?php if($page > 1){ ?>

<a href="?search=<?php echo urlencode($search); ?>&page=<?php echo $page-1; ?>"
class="btn-edit">
Previous
</a>

<?php } ?>

<?php for($i=1; $i<=$total_pages; $i++){ ?>

<a href="?search=<?php echo urlencode($search); ?>&page=<?php echo $i; ?>"
class="<?php echo ($i==$page) ? 'btn-confirm' : 'btn-edit'; ?>">

<?php echo $i; ?>

</a>

<?php } ?>

<?php if($page < $total_pages){ ?>

<a href="?search=<?php echo urlencode($search); ?>&page=<?php echo $page+1; ?>"
class="btn-edit">
Next
</a>

<?php } ?>

</div>

<?php } ?>

</div>

</div>

</div>

<script src="assets/js/theme.js"></script>

</body>

</html>