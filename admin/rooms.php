<?php
session_start();

require_once("../database/db.php");

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

// Check if user is admin
if ($_SESSION['role'] != 'admin') {
    echo "Access Denied!";
    exit();
}

$admin_name = $_SESSION['user_name'];

$search = "";

$check_in = "";
$check_out = "";

if(isset($_GET['check_in'])){
    $check_in = $_GET['check_in'];
}

if(isset($_GET['check_out'])){
    $check_out = $_GET['check_out'];
}

if(isset($_GET['search'])){
    $search = mysqli_real_escape_string($conn, $_GET['search']);
}
$type = "";

if(isset($_GET['type'])){
    $type = mysqli_real_escape_string($conn, $_GET['type']);
}

$room_query = "
SELECT
    rooms.*,

    (
        SELECT booking_status
        FROM bookings
        WHERE bookings.room_id = rooms.id
        AND booking_status='Confirmed'
        AND (
    (
        '$check_in' <> ''
        AND '$check_out' <> ''
        AND '$check_in' <= check_out
        AND '$check_out' >= check_in
    )
    OR
    (
        '$check_in' = ''
        AND CURDATE() BETWEEN check_in AND check_out
    )
)
        LIMIT 1
    ) AS room_status,

    (
        SELECT check_out
        FROM bookings
        WHERE bookings.room_id = rooms.id
        AND booking_status='Confirmed'
        AND (
    (
        '$check_in' <> ''
        AND '$check_out' <> ''
        AND '$check_in' <= check_out
        AND '$check_out' >= check_in
    )
    OR
    (
        '$check_in' = ''
        AND CURDATE() BETWEEN check_in AND check_out
    )
)
        LIMIT 1
    ) AS booked_till

FROM rooms
";

$where = [];

if($search != ""){
    $where[] = "(room_name LIKE '%$search%'
                 OR room_type LIKE '%$search%')";
}

if($type != ""){
    $where[] = "room_type = '$type'";
}

if(!empty($where)){
    $room_query .= " WHERE " . implode(" AND ", $where);
}

$room_query .= " ORDER BY id DESC";

$limit = 5;

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

if($page < 1){
    $page = 1;
}

$offset = ($page - 1) * $limit;

/* Total Records */

$count_query = "SELECT COUNT(*) AS total FROM rooms";

if(!empty($where)){
    $count_query .= " WHERE " . implode(" AND ", $where);
}

$count_result = mysqli_query($conn, $count_query);

$total_rows = mysqli_fetch_assoc($count_result)['total'];

$total_pages = ceil($total_rows / $limit);

/* Final Query */

$room_query .= " LIMIT $limit OFFSET $offset";

$room_result = mysqli_query($conn, $room_query);



$total_rooms = mysqli_fetch_assoc(mysqli_query($conn,
"SELECT COUNT(*) AS total FROM rooms"))['total'];

$deluxe_rooms = mysqli_fetch_assoc(mysqli_query($conn,
"SELECT COUNT(*) AS total FROM rooms WHERE room_type='Deluxe'"))['total'];

$total_capacity = mysqli_fetch_assoc(mysqli_query($conn,
"SELECT SUM(capacity) AS total FROM rooms"))['total'];

// Currently Booked Rooms

if($check_in != "" && $check_out != ""){

    $booked_rooms = mysqli_fetch_assoc(mysqli_query($conn,"
    SELECT COUNT(DISTINCT room_id) AS total
    FROM bookings
    WHERE booking_status='Confirmed'
    AND '$check_in' <= check_out
    AND '$check_out' >= check_in
    "))['total'];

}else{

    $booked_rooms = mysqli_fetch_assoc(mysqli_query($conn,"
    SELECT COUNT(DISTINCT room_id) AS total
    FROM bookings
    WHERE booking_status='Confirmed'
    AND CURDATE() BETWEEN check_in AND check_out
    "))['total'];

}

// Available Rooms
$available_rooms = $total_rooms - $booked_rooms;

$simple_rooms = mysqli_fetch_assoc(mysqli_query($conn,
"SELECT COUNT(*) AS total FROM rooms WHERE room_type='Simple'"))['total'];

$family_rooms = mysqli_fetch_assoc(mysqli_query($conn,
"SELECT COUNT(*) AS total FROM rooms WHERE room_type='Family'"))['total'];

$suite_rooms = mysqli_fetch_assoc(mysqli_query($conn,
"SELECT COUNT(*) AS total FROM rooms WHERE room_type='Suite'"))['total'];
?>

<!DOCTYPE html>
<html lang="en">

<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Manage Rooms</title>

<link rel="stylesheet" href="../assets/css/admin.css">

<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

</head>

<body>

<?php $current_page = "rooms"; ?>
<?php include("includes/sidebar.php"); ?>

<div class="main-content">

<h2>Manage Rooms</h2>

<p>Welcome, <?php echo htmlspecialchars($admin_name); ?></p>
<div class="dashboard-cards">

    <div class="card">
        <h3><i class="fas fa-hotel"></i> Total Rooms</h3>
        <p><?php echo $total_rooms; ?></p>
    </div>

    <div class="card">
    <h3><i class="fas fa-user-group"></i> Total Capacity</h3>
    <p><?php echo $total_capacity; ?></p>
</div>

    <div class="card">
        <h3><i class="fas fa-bed"></i> Simple Rooms</h3>
        <p><?php echo $simple_rooms; ?></p>
    </div>

    <div class="card">
        <h3><i class="fas fa-crown"></i> Deluxe Rooms</h3>
        <p><?php echo $deluxe_rooms; ?></p>
    </div>

    <div class="card">
        <h3><i class="fas fa-users"></i> Family Rooms</h3>
        <p><?php echo $family_rooms; ?></p>
    </div>

    <div class="card">
        <h3><i class="fas fa-star"></i> Suite Rooms</h3>
        <p><?php echo $suite_rooms; ?></p>
    </div>

  <div class="card">
    <h3><i class="fas fa-circle-check"></i> Available Rooms</h3>
    <p><?php echo $available_rooms; ?></p>
</div>

<div class="card">
    <h3><i class="fas fa-circle-xmark"></i> Booked Rooms</h3>
    <p><?php echo $booked_rooms; ?></p>
</div>

</div>

<div style="display:flex;justify-content:space-between;align-items:center;margin-top:25px;">

    <h2>Rooms List</h2>

    <a href="add_room.php" class="btn-primary">
        <i class="fas fa-plus"></i> Add Room
    </a>

</div>
<form method="GET" class="availability-form">

    <input
        type="date"
        name="check_in"
        value="<?php echo $_GET['check_in'] ?? ''; ?>">

    <input
        type="date"
        name="check_out"
        value="<?php echo $_GET['check_out'] ?? ''; ?>">

    <input
        type="text"
        name="search"
        placeholder="Search Room..."
        value="<?php echo htmlspecialchars($search); ?>">

    <select name="type">

        <option value="">All Types</option>

        <option value="Simple" <?php if($type=="Simple") echo "selected"; ?>>Simple</option>

        <option value="Deluxe" <?php if($type=="Deluxe") echo "selected"; ?>>Deluxe</option>

        <option value="Family" <?php if($type=="Family") echo "selected"; ?>>Family</option>

        <option value="Suite" <?php if($type=="Suite") echo "selected"; ?>>Suite</option>

    </select>

    <button type="submit" class="btn-primary">
        <i class="fas fa-search"></i> Search
    </button>

    <a href="rooms.php" class="btn-delete">
        <i class="fas fa-rotate-left"></i> Reset
    </a>

</form>

<div class="table-container">
<table border="1" cellpadding="10" cellspacing="0">

<tr>
    <th>ID</th>
    <th>Room Name</th>
    <th>Room Type</th>
    <th>Price</th>
    <th>Capacity</th>
    <th>Status</th>
    <th>Booked Till</th>
    <th>Action</th>
</tr>

<?php while($room = mysqli_fetch_assoc($room_result)) { ?>

<tr>
    
<td>#<?php echo $room['id']; ?></td>
    <td><?php echo htmlspecialchars($room['room_name']); ?></td>
    <td><?php echo htmlspecialchars($room['room_type']); ?></td>
    <td>₹<?php echo number_format($room['price']); ?></td>
    <td><?php echo $room['capacity']; ?></td>
    <td>
<?php
if($room['room_status'] == 'Confirmed'){
    echo "<span class='status cancelled'><i class='fas fa-circle-xmark'></i> Booked</span>";
}else{
    echo "<span class='status confirmed'><i class='fas fa-circle-check'></i> Available</span>";
}
?>
</td>

<td>
<?php
if(!empty($room['booked_till'])){
    echo date("d M Y", strtotime($room['booked_till']));
}else{
    echo "-";
}
?>
</td>
   <td>

<a href="edit_room.php?id=<?php echo $room['id']; ?>" class="btn-edit">
    <i class="fas fa-edit"></i> Edit
</a>

<a href="delete_room.php?id=<?php echo $room['id']; ?>"
class="btn-delete"
onclick="return confirm('Are you sure you want to delete this room?');">

<i class="fas fa-trash"></i> Delete

</a>

</td>
</tr>

<?php } ?>

</table>
<?php if($total_pages > 1){ ?>

<div style="margin-top:20px; text-align:center;">

<?php if($page > 1){ ?>

<a href="?search=<?php echo urlencode($search); ?>&type=<?php echo urlencode($type); ?>&page=<?php echo $page-1; ?>"
class="btn-edit">
    Previous
</a>

<?php } ?>

<?php for($i=1; $i<=$total_pages; $i++){ ?>

<a href="?search=<?php echo urlencode($search); ?>&type=<?php echo urlencode($type); ?>&page=<?php echo $i; ?>"
class="<?php echo ($i==$page) ? 'btn-confirm' : 'btn-edit'; ?>"></a>
<?php echo $i; ?>

</a>

<?php } ?>

<?php if($page < $total_pages){ ?>

<a href="?search=<?php echo urlencode($search); ?>&type=<?php echo urlencode($type); ?>&page=<?php echo $page+1; ?>"
class="btn-edit">
    Next
</a>

<?php } ?>

</div>

<?php } ?>
</div>

<script src="assets/js/theme.js"></script>
</body>
</html>