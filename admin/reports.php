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

$from = isset($_GET['from']) ? $_GET['from'] : "";
$to = isset($_GET['to']) ? $_GET['to'] : "";

$dateFilter = "";

if($from != "" && $to != ""){
    $dateFilter = " WHERE DATE(created_at) BETWEEN '$from' AND '$to'";
}

// Counts
$totalUsers = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM users"))['total'];

$totalRooms = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM rooms"))['total'];

$booking_sql = "SELECT COUNT(*) AS total FROM bookings";

if($from != "" && $to != ""){
    $booking_sql .= " WHERE DATE(created_at) BETWEEN '$from' AND '$to'";
}

$totalBookings = mysqli_fetch_assoc(mysqli_query($conn, $booking_sql))['total'];
$revenue_sql = "SELECT SUM(total_amount) AS total
                FROM bookings
                WHERE booking_status='Confirmed'";

if($from != "" && $to != ""){
    $revenue_sql .= " AND DATE(created_at) BETWEEN '$from' AND '$to'";
}

$totalRevenue = mysqli_fetch_assoc(mysqli_query($conn, $revenue_sql))['total'];


$status_sql = "SELECT
    SUM(CASE WHEN booking_status='Confirmed' THEN 1 ELSE 0 END) AS confirmed,
    SUM(CASE WHEN booking_status='Pending' THEN 1 ELSE 0 END) AS pending,
    SUM(CASE WHEN booking_status='Cancelled' THEN 1 ELSE 0 END) AS cancelled
FROM bookings";

if($from != "" && $to != ""){
    $status_sql .= " WHERE DATE(created_at) BETWEEN '$from' AND '$to'";
}

$status_result = mysqli_fetch_assoc(mysqli_query($conn, $status_sql));

$confirmed = $status_result['confirmed'] ?? 0;
$pending = $status_result['pending'] ?? 0;
$cancelled = $status_result['cancelled'] ?? 0;
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Reports</title>

<link rel="stylesheet" href="../assets/css/admin.css">

<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

</head>

<body>

<div class="admin-container">

<?php $current_page = "reports"; ?>
<?php include("includes/sidebar.php"); ?>

<div class="main-content">

<div class="top-navbar">

<form method="GET" class="search-box">

<input
type="date"
name="from"
value="<?php echo isset($_GET['from']) ? $_GET['from'] : ''; ?>">

<input
type="date"
name="to"
value="<?php echo isset($_GET['to']) ? $_GET['to'] : ''; ?>">

<button type="submit" class="btn-edit">
Filter
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

<h1>Reports</h1>

<div style="margin-bottom:20px;">

<a href="export_reports.php?from=<?php echo urlencode($from); ?>&to=<?php echo urlencode($to); ?>"
class="btn-confirm"
style="display:inline-flex;align-items:center;gap:8px;text-decoration:none;padding:10px 18px;margin-right:10px;">

<i class="fas fa-file-csv"></i>
<span>Export CSV</span>

</a>

<a href="export_report_pdf.php?from=<?php echo urlencode($from); ?>&to=<?php echo urlencode($to); ?>"
class="btn-edit"
style="display:inline-flex;align-items:center;gap:8px;text-decoration:none;padding:10px 18px;">

<i class="fas fa-file-pdf"></i>
<span>Export PDF</span>

</a>

</div>

<div class="dashboard-cards">

<div class="card">
<h3>Total Users</h3>
<p><?php echo $totalUsers; ?></p>
</div>

<div class="card">
<h3>Total Rooms</h3>
<p><?php echo $totalRooms; ?></p>
</div>

<div class="card">
<h3>Total Bookings</h3>
<p><?php echo $totalBookings; ?></p>
</div>

<div class="card revenue-card">
<h3>Total Revenue</h3>
<p>₹<?php echo $totalRevenue ? $totalRevenue : 0; ?></p>
</div>

</div>

<div class="recent-bookings">

<h2 style="margin-bottom:20px;">Booking Status Summary</h2>

<table>

<thead>

<tr>
<th>Confirmed</th>
<th>Pending</th>
<th>Cancelled</th>
</tr>

</thead>

<tbody>

<tr>

<td>
<span class="status confirmed">
<?php echo $confirmed; ?>
</span>
</td>

<td>
<span class="status pending">
<?php echo $pending; ?>
</span>
</td>

<td>
<span class="status cancelled">
<?php echo $cancelled; ?>
</span>
</td>

</tr>

</tbody>

</table>

</div>

</div>

</div>

<script src="assets/js/theme.js"></script>

</body>

</html>