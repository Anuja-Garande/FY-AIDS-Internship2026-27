<?php
session_start();
require_once("../database/db.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

// Optional: Admin Role Check
if ($_SESSION['role'] != "admin") {
    die("Access Denied!");
}

$admin_name = $_SESSION['user_name'];
// Total Users
$user_query = "SELECT COUNT(*) AS total_users FROM users";
$user_result = mysqli_query($conn, $user_query);
$total_users = mysqli_fetch_assoc($user_result);
// Total Rooms
$room_query = "SELECT COUNT(*) AS total_rooms FROM rooms";
$room_result = mysqli_query($conn, $room_query);
$total_rooms = mysqli_fetch_assoc($room_result);
// Total Bookings
$booking_query = "SELECT COUNT(*) AS total_bookings FROM bookings";
$booking_result = mysqli_query($conn, $booking_query);
$total_bookings = mysqli_fetch_assoc($booking_result);
// Total Revenue
$revenue_query = "SELECT SUM(total_amount) AS total_revenue FROM bookings";
$revenue_result = mysqli_query($conn, $revenue_query);
$total_revenue = mysqli_fetch_assoc($revenue_result);

// Booked Rooms (Currently Occupied)
$bookedRoomsQuery = "
SELECT COUNT(DISTINCT room_id) AS booked_rooms
FROM bookings
WHERE booking_status='Confirmed'
AND CURDATE() BETWEEN check_in AND check_out
";

$bookedRoomsResult = mysqli_query($conn, $bookedRoomsQuery);
$bookedRooms = mysqli_fetch_assoc($bookedRoomsResult)['booked_rooms'];

// Available Rooms
$availableRooms = $total_rooms['total_rooms'] - $bookedRooms;

// Confirmed Bookings
$confirmed_query = "SELECT COUNT(*) AS total FROM bookings WHERE booking_status='Confirmed'";
$confirmed_result = mysqli_query($conn, $confirmed_query);
$confirmedBookings = mysqli_fetch_assoc($confirmed_result)['total'];

// Pending Bookings
$pending_query = "SELECT COUNT(*) AS total FROM bookings WHERE booking_status='Pending'";
$pending_result = mysqli_query($conn, $pending_query);
$pendingBookings = mysqli_fetch_assoc($pending_result)['total'];

// Cancelled Bookings
$cancelled_query = "SELECT COUNT(*) AS total FROM bookings WHERE booking_status='Cancelled'";
$cancelled_result = mysqli_query($conn, $cancelled_query);
$cancelledBookings = mysqli_fetch_assoc($cancelled_result)['total'];

// Monthly Revenue
$monthlyRevenueQuery = "
SELECT 
    DATE_FORMAT(created_at, '%b') AS month,
    SUM(total_amount) AS revenue
FROM bookings
WHERE booking_status='Confirmed'
GROUP BY MONTH(created_at)
ORDER BY MONTH(created_at)
";

$monthlyRevenueResult = mysqli_query($conn, $monthlyRevenueQuery);

$months = [];
$revenues = [];

while ($row = mysqli_fetch_assoc($monthlyRevenueResult)) {
    $months[] = $row['month'];
    $revenues[] = $row['revenue'];
}

// Top 5 Most Booked Rooms
$topRoomsQuery = "
SELECT
    rooms.room_name,
    COUNT(bookings.id) AS total_bookings
FROM bookings
INNER JOIN rooms ON bookings.room_id = rooms.id
GROUP BY bookings.room_id
ORDER BY total_bookings DESC
LIMIT 5
";

$topRoomsResult = mysqli_query($conn, $topRoomsQuery);
// Recent 5 Bookings
$recentBookingsQuery = "
SELECT
    bookings.id,
    bookings.booking_status,
    bookings.total_amount,
    bookings.created_at,
    users.full_name AS user_name,
    rooms.room_name
FROM bookings
INNER JOIN users ON bookings.user_id = users.id
INNER JOIN rooms ON bookings.room_id = rooms.id
ORDER BY bookings.created_at DESC
LIMIT 5
";

$recentBookingsResult = mysqli_query($conn, $recentBookingsQuery);

// Latest Contact Messages
$latestMessagesQuery = "
SELECT *
FROM contact_messages
ORDER BY created_at DESC
LIMIT 5
";

$latestMessagesResult = mysqli_query($conn, $latestMessagesQuery);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>

    <link rel="stylesheet" href="../assets/css/admin.css">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>
<body>
    <div class="admin-container">

     <?php $current_page = "dashboard"; ?>
<?php include("includes/sidebar.php"); ?>

    <div class="main-content">
  <?php include("includes/navbar.php"); ?>

<h1 style="font-size:42px;font-weight:700;">
Welcome Back,
<span style="color:#38bdf8;">
<?php echo htmlspecialchars($admin_name); ?>
</span>
👋
</h1>

<p style="color:#94a3b8;font-size:18px;margin-top:-10px;">
Here's what's happening in your hotel today.
</p>
<div style="
height:2px;
background:linear-gradient(to right,#38bdf8,transparent);
margin:20px 0 35px;
"></div>



<div class="dashboard-cards">

    <div class="card users-card">
       <h3><i class="fas fa-users"></i> Total Users</h3>
        <p><?php echo $total_users['total_users']; ?></p>
    </div>

    <div class="card rooms-card">
        <h3><i class="fas fa-bed"></i> Total Rooms</h3>
        <p><?php echo $total_rooms['total_rooms']; ?></p>
    </div>

   <div class="card bookings-card">
        <h3><i class="fas fa-calendar-check"></i> Total Bookings</h3>
        <p><?php echo $total_bookings['total_bookings']; ?></p>
    </div>

    <div class="card revenue-card">
        <h3><i class="fas fa-indian-rupee-sign"></i> Total Revenue</h3>
        <p><?php echo number_format($total_revenue['total_revenue'] ?? 0); ?></p>
    </div>

    

<!-- 👇 He ithe paste kar -->

<div class="card" style="background:#16a34a;">
    <h3><i class="fas fa-circle-check"></i> Available Rooms</h3>
    <p><?php echo $availableRooms; ?></p>
</div>

<div class="card" style="background:#dc2626;">
    <h3><i class="fas fa-bed"></i> Booked Rooms</h3>
    <p><?php echo $bookedRooms; ?></p>
</div>



</div>

<div class="card" style="margin:30px 0; padding:25px;">
    <h2 style="color:#fff; margin-bottom:20px;">Booking Status Overview</h2>

    <canvas id="revenueChart" width="900" height="350"></canvas>

</div>
    <div class="card" style="margin:30px 0; padding:25px;">
    <h2 style="color:#fff; margin-bottom:20px;">Monthly Revenue</h2>

    <canvas id="monthlyRevenueChart" width="900" height="350"></canvas>
</div>
  
<div class="recent-bookings">

<h2 class="section-title" style="margin-top:0; margin-bottom:20px;">
    Top 5 Most Booked Rooms
</h2>

<table>

<tr>
    <th>Rank</th>
    <th>Room Name</th>
    <th>Total Bookings</th>
</tr>

<?php
$rank = 1;
while($room = mysqli_fetch_assoc($topRoomsResult)) {
?>

<tr>

<td>
<?php
if($rank == 1){
    echo "🥇";
}elseif($rank == 2){
    echo "🥈";
}elseif($rank == 3){
    echo "🥉";
}else{
    echo $rank;
}
?>
</td>

<td><?php echo htmlspecialchars($room['room_name']); ?></td>

<td><?php echo $room['total_bookings']; ?></td>

</tr>

<?php
$rank++;
}
?>

</table>

</div>

<div style="display:flex;justify-content:space-between;align-items:center;margin-top:40px;margin-bottom:20px;">

<h2 class="section-title" style="margin:0;">Recent Bookings</h2>

<a href="bookings.php" class="btn-primary">
    View All <i class="fas fa-arrow-right"></i>
</a>

</div>

<div class="recent-bookings">

<table>

<tr>
    <th>ID</th>
    <th>User</th>
    <th>Room</th>
    <th>Status</th>
    <th>Amount</th>
    <th>Date</th>
</tr>

<?php while($booking = mysqli_fetch_assoc($recentBookingsResult)) { ?>

<tr>

    <td>#<?php echo $booking['id']; ?></td>

    <td><?php echo htmlspecialchars($booking['user_name']); ?></td>

    <td><?php echo htmlspecialchars($booking['room_name']); ?></td>

    <td>
        <span class="status <?php echo strtolower($booking['booking_status']); ?>">
            <?php echo htmlspecialchars($booking['booking_status']); ?>
        </span>
    </td>

    <td>₹<?php echo number_format($booking['total_amount']); ?></td>

    <td><?php echo date("d M Y", strtotime($booking['created_at'])); ?></td>

</tr>

<?php } ?>

</table>

</div>

<div class="recent-bookings" style="margin-top:40px;">

<h2 class="section-title" style="
margin-top:0;
margin-bottom:20px;
display:flex;
align-items:center;
gap:10px;
">

<i class="fas fa-envelope"></i>

Latest Contact Messages

</h2>

<table>

<tr>
    <th>Name</th>
    <th>Subject</th>
    <th>Date</th>
    <th>Action</th>
</tr>

<?php while($msg = mysqli_fetch_assoc($latestMessagesResult)){ ?>

<tr>

<td><?php echo htmlspecialchars($msg['name']); ?></td>

<td><?php echo htmlspecialchars($msg['subject']); ?></td>

<td><?php echo date("d M Y", strtotime($msg['created_at'])); ?></td>

<td>

<a href="contact-messages.php" class="btn-primary">

View

</a>

</td>

</tr>

<?php } ?>

</table>

</div>

</div>

</div>


    


<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const ctx = document.getElementById('revenueChart');

new Chart(ctx, {
    type: 'bar',
    data: {
        labels: ['Confirmed', 'Pending', 'Cancelled'],
        datasets: [{
            label: 'Bookings',
            data: [
                <?php echo $confirmedBookings; ?>,
                <?php echo $pendingBookings; ?>,
                <?php echo $cancelledBookings; ?>
            ],
            backgroundColor: [
                '#22c55e',
                '#f59e0b',
                '#ef4444'
            ],
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                labels: {
                    color: '#ffffff'
                }
            }
        },
        scales: {
            x: {
                ticks: {
                    color: '#ffffff'
                }
            },
            y: {
                beginAtZero: true,
                ticks: {
                    color: '#ffffff'
                }
            }
        }
    }
});

const revenueCtx = document.getElementById('monthlyRevenueChart');

new Chart(revenueCtx, {
    type: 'line',
    data: {
        labels: <?php echo json_encode($months); ?>,
        datasets: [{
            label: 'Revenue (₹)',
            data: <?php echo json_encode($revenues); ?>,
            borderColor: '#38bdf8',
            backgroundColor: 'rgba(56,189,248,0.2)',
            fill: true,
            tension: 0.4
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                labels: {
                    color: '#ffffff'
                }
            }
        },
        scales: {
            x: {
                ticks: {
                    color: '#ffffff'
                }
            },
            y: {
                beginAtZero: true,
                ticks: {
                    color: '#ffffff'
                }
            }
        }
    }
});
</script>

<script>
const bell = document.getElementById("notificationBox");
const dropdown = document.getElementById("notificationDropdown");

if(bell && dropdown){

    dropdown.style.display = "none";

    bell.addEventListener("click", function(e){
        e.stopPropagation();

        if(dropdown.style.display === "block"){
            dropdown.style.display = "none";
        }else{
            dropdown.style.display = "block";
        }
    });

    document.addEventListener("click", function(){
        dropdown.style.display = "none";
    });

}
</script>

<script>

const themeToggle = document.getElementById("themeToggle");

if(themeToggle){

    if(localStorage.getItem("theme") === "light"){
        document.body.classList.add("light-mode");
        themeToggle.innerHTML = '<i class="fas fa-sun"></i>';
    }

    themeToggle.addEventListener("click", function(){

        document.body.classList.toggle("light-mode");

        if(document.body.classList.contains("light-mode")){
            localStorage.setItem("theme","light");
            themeToggle.innerHTML = '<i class="fas fa-sun"></i>';
        }else{
            localStorage.setItem("theme","dark");
            themeToggle.innerHTML = '<i class="fas fa-moon"></i>';
        }

    });

}
</script>


</body>
</html>