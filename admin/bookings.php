<?php
session_start();
require_once("../database/db.php");

if(!isset($_SESSION['user_id'])){
    header("Location: ../auth/login.php");
    exit();
}

if($_SESSION['role']!="admin"){
    die("Access Denied!");
}

$search=isset($_GET['search'])?mysqli_real_escape_string($conn,$_GET['search']):"";
$status=isset($_GET['status'])?mysqli_real_escape_string($conn,$_GET['status']):"";
$from_date=isset($_GET['from_date'])?mysqli_real_escape_string($conn,$_GET['from_date']):"";
$to_date=isset($_GET['to_date'])?mysqli_real_escape_string($conn,$_GET['to_date']):"";

$limit=5;
$page=isset($_GET['page'])?(int)$_GET['page']:1;

if($page<1){
$page=1;
}

$offset=($page-1)*$limit;

$sql="
SELECT
bookings.*,
users.full_name,
rooms.room_name,
rooms.room_number

FROM bookings

INNER JOIN users
ON bookings.user_id=users.id

INNER JOIN rooms
ON bookings.room_id=rooms.id
";

$where=[];

if($search!=""){
$where[]="(
users.full_name LIKE '%$search%'
OR rooms.room_name LIKE '%$search%'
OR bookings.booking_status LIKE '%$search%'
OR bookings.id LIKE '%$search%'
)";
}

if($status!=""){
$where[]="bookings.booking_status='$status'";
}

if($from_date!="" && $to_date!=""){
$where[]="DATE(bookings.check_in)
BETWEEN '$from_date' AND '$to_date'";
}

if(count($where)>0){
$sql.=" WHERE ".implode(" AND ",$where);
}

$sql.=" ORDER BY bookings.id DESC
LIMIT $limit OFFSET $offset";

$result=mysqli_query($conn,$sql);

$count=mysqli_query($conn,"SELECT COUNT(*) total FROM bookings");
$total=mysqli_fetch_assoc($count)['total'];
$totalPages=ceil($total/$limit);
?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
content="width=device-width,initial-scale=1.0">

<title>Manage Bookings</title>

<link rel="stylesheet"
href="../assets/css/admin.css">

<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

<style>

.filter-card{
background:#1f2937;
padding:25px;
border-radius:15px;
margin-bottom:25px;
border:1px solid #334155;
}

.filter-form{
display:grid;
grid-template-columns:2fr 1fr 1fr 1fr auto auto;
gap:15px;
align-items:center;
}

.filter-form input,
.filter-form select{
width:100%;
padding:12px;
border-radius:10px;
border:1px solid #334155;
background:#0f172a;
color:#fff;
box-sizing:border-box;
}

.filter-form button,
.filter-form a{
height:46px;
padding:0 18px;
border-radius:10px;
font-weight:600;
display:flex;
align-items:center;
justify-content:center;
text-decoration:none;
border:none;
cursor:pointer;
}

.search-btn{
background:#22c55e;
color:#fff;
}

.reset-btn{
background:#475569;
color:#fff;
}

.action-box{
display:flex;
justify-content:center;
gap:8px;
}

.action-box a{
width:40px;
height:40px;
display:flex;
justify-content:center;
align-items:center;
border-radius:8px;
text-decoration:none;
}

</style>

</head>

<body>

<div class="admin-container">

<?php $current_page="bookings"; ?>
<?php include("includes/sidebar.php"); ?>

<div class="main-content">

<?php $hideSearch=true; ?>
<?php include("includes/navbar.php"); ?>

<h1>Manage Bookings</h1>

<div class="filter-card">

<form class="filter-form" method="GET">
    <input
type="text"
name="search"
placeholder="Search User, Room or Booking ID..."
value="<?php echo htmlspecialchars($search); ?>">

<select name="status">

<option value="">All Status</option>

<option value="Confirmed"
<?php if($status=="Confirmed") echo "selected"; ?>>
Confirmed
</option>

<option value="Pending"
<?php if($status=="Pending") echo "selected"; ?>>
Pending
</option>

<option value="Cancelled"
<?php if($status=="Cancelled") echo "selected"; ?>>
Cancelled
</option>

</select>

<input
type="date"
name="from_date"
value="<?php echo $from_date; ?>">

<input
type="date"
name="to_date"
value="<?php echo $to_date; ?>">

<button
type="submit"
class="search-btn">

<i class="fas fa-search"></i>
&nbsp; Search

</button>

<a
href="bookings.php"
class="reset-btn">

Reset

</a>

</form>

</div>

<div class="recent-bookings">

<table>

<thead>

<tr>

<th>Booking ID</th>
<th>User</th>
<th>Room No</th>
<th>Room</th>
<th>Check In</th>
<th>Check Out</th>
<th>Amount</th>
<th>Status</th>
<th>Action</th>

</tr>

</thead>

<tbody>
    <?php

if(mysqli_num_rows($result)>0){

while($row=mysqli_fetch_assoc($result)){

?>

<tr>

<td>
BK-<?php echo str_pad($row['id'], 4, '0', STR_PAD_LEFT); ?>
</td>

<td><?php echo htmlspecialchars($row['full_name']); ?></td>

<td><?php echo htmlspecialchars($row['room_number']); ?></td>

<td><?php echo htmlspecialchars($row['room_name']); ?></td>

<td><?php echo $row['check_in']; ?></td>

<td><?php echo $row['check_out']; ?></td>

<td>₹<?php echo number_format($row['total_amount'],2); ?></td>

<td>

<?php

if($row['booking_status']=="Confirmed"){

echo "<span class='status confirmed'>Confirmed</span>";

}elseif($row['booking_status']=="Pending"){

echo "<span class='status pending'>Pending</span>";

}else{

echo "<span class='status cancelled'>Cancelled</span>";

}

?>

</td>

<td>

<div class="action-box">
    <a
href="view_booking.php?id=<?php echo $row['id']; ?>"
class="btn-edit"
title="View">

<i class="fas fa-eye"></i>

</a>

<a
href="confirm_booking.php?id=<?php echo $row['id']; ?>"
class="btn-confirm"
title="Confirm">

<i class="fas fa-check"></i>

</a>

<a
href="cancel_booking.php?id=<?php echo $row['id']; ?>"
class="btn-cancel"
title="Cancel">

<i class="fas fa-times"></i>

</a>

<a
href="delete_booking.php?id=<?php echo $row['id']; ?>"
class="btn-delete"
title="Delete"
onclick="return confirm('Are you sure you want to delete this booking?');">

<i class="fas fa-trash"></i>

</a>

</div>

</td>

</tr>

<?php

}

}else{

?>

<tr>

<td colspan="9" style="text-align:center;padding:30px;"></td>

No Bookings Found

</td>

</tr>

<?php

}

?>
</tbody>

</table>

<div style="margin-top:25px;text-align:center;">

<?php if($page>1){ ?>

<a
href="?page=<?php echo $page-1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status); ?>&from_date=<?php echo urlencode($from_date); ?>&to_date=<?php echo urlencode($to_date); ?>"
class="btn-primary"
style="text-decoration:none;padding:10px 16px;">

<i class="fas fa-angle-left"></i>
Previous

</a>

<?php } ?>

<?php for($i=1;$i<=$totalPages;$i++){ ?>

<a
href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status); ?>&from_date=<?php echo urlencode($from_date); ?>&to_date=<?php echo urlencode($to_date); ?>"
style="
display:inline-block;
padding:10px 15px;
margin:0 4px;
border-radius:8px;
text-decoration:none;
background:<?php echo ($page==$i)?'#0ea5e9':'#334155'; ?>;
color:#fff;">

<?php echo $i; ?>

</a>

<?php } ?>

<?php if($page<$totalPages){ ?>

<a
href="?page=<?php echo $page+1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status); ?>&from_date=<?php echo urlencode($from_date); ?>&to_date=<?php echo urlencode($to_date); ?>"
class="btn-primary"
style="text-decoration:none;padding:10px 16px;">

Next
<i class="fas fa-angle-right"></i>

</a>

<?php } ?>

</div>

</div>

</div>

</div>
<script src="assets/js/theme.js"></script>

</html>

