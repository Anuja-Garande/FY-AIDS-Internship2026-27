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

$search = "";

if(isset($_GET['search'])){
    $search = mysqli_real_escape_string($conn, $_GET['search']);
}

$role = "";

if(isset($_GET['role'])){
    $role = mysqli_real_escape_string($conn, $_GET['role']);
}

$query = "SELECT * FROM users";

$where = [];

if($search != ""){
    $where[] = "(full_name LIKE '%$search%'
                OR email LIKE '%$search%'
                OR phone LIKE '%$search%')";
}

if($role != ""){
    $where[] = "role = '$role'";
}

if(!empty($where)){
    $query .= " WHERE " . implode(" AND ", $where);
}

$query .= " ORDER BY id DESC";

$limit = 5;

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

if($page < 1){
    $page = 1;
}

$offset = ($page - 1) * $limit;

/* Total Records */

$count_query = str_replace("SELECT *", "SELECT COUNT(*) AS total", $query);

$count_result = mysqli_query($conn, $count_query);

$total_rows = mysqli_fetch_assoc($count_result)['total'];

$total_pages = ceil($total_rows / $limit);

/* Final Query */

$query .= " LIMIT $limit OFFSET $offset";

$result = mysqli_query($conn, $query);

$total_users = mysqli_fetch_assoc(mysqli_query($conn,
"SELECT COUNT(*) AS total FROM users"))['total'];

$total_admins = mysqli_fetch_assoc(mysqli_query($conn,
"SELECT COUNT(*) AS total FROM users WHERE role='admin'"))['total'];

$total_normal_users = mysqli_fetch_assoc(mysqli_query($conn,
"SELECT COUNT(*) AS total FROM users WHERE role='user'"))['total'];
?>

<!DOCTYPE html>
<html lang="en">

<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Manage Users</title>

<link rel="stylesheet" href="../assets/css/admin.css">

<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

</head>

<body>

<?php $current_page = "users"; ?>
<?php include("includes/sidebar.php"); ?>

<div class="main-content">

<h2>Manage Users</h2>

<div class="dashboard-cards">

    <div class="card">
        <h3>Total Users</h3>
        <p><?php echo $total_users; ?></p>
    </div>

    <div class="card">
        <h3>Total Admins</h3>
        <p><?php echo $total_admins; ?></p>
    </div>

    <div class="card">
        <h3>Normal Users</h3>
        <p><?php echo $total_normal_users; ?></p>
    </div>

</div>

<form method="GET" style="margin:20px 0; display:flex; gap:10px;">

    <input
        type="text"
        name="search"
        placeholder="Search by Name, Email, Phone or Role..."
        value="<?php echo htmlspecialchars($search); ?>"
        style="width:350px; padding:10px; border-radius:8px; border:1px solid #334155;">

        <select name="role" style="padding:10px; border-radius:8px; border:1px solid #334155;">

    <option value="">All Roles</option>

    <option value="admin" <?php if($role=="admin") echo "selected"; ?>>
        Admin
    </option>

    <option value="user" <?php if($role=="user") echo "selected"; ?>>
        User
    </option>

</select>

    <button type="submit" class="btn-edit">
        <i class="fas fa-search"></i> Search
    </button>

   <a href="users.php" class="btn-delete">
    <i class="fas fa-rotate-left"></i> Reset
</a>

</form>

<div class="table-container">

<table>

<tr>
    <th>#</th>
    <th><i class="fas fa-user"></i> Name</th>
    <th><i class="fas fa-envelope"></i> Email</th>
    <th><i class="fas fa-phone"></i> Phone</th>
    <th><i class="fas fa-user-tag"></i> Role</th>
    <th><i class="fas fa-cogs"></i> Action</th>
</tr>

<?php while($user = mysqli_fetch_assoc($result)){ ?>

<tr>

    <td>#<?php echo $user['id']; ?></td>
    <td><?php echo $user['full_name']; ?></td>
    <td><?php echo $user['email']; ?></td>
    <td><?php echo $user['phone']; ?></td>
    <td><?php echo ucfirst($user['role']); ?></td>

   <td>

<a href="edit_user.php?id=<?php echo $user['id']; ?>" class="btn-edit">
    <i class="fas fa-edit"></i> Edit
</a>

<a href="delete_user.php?id=<?php echo $user['id']; ?>"
class="btn-delete"
onclick="return confirm('Delete this user?')">
    <i class="fas fa-trash"></i> Delete
</a>

</td>

</tr>

<?php } ?>

</table>
<?php if($total_pages > 1){ ?>

<div style="margin-top:20px; text-align:center;">

<?php if($page > 1){ ?>
<a href="?search=<?php echo urlencode($search); ?>&page=<?php echo $page-1; ?>" class="btn-edit">
    Previous
</a>
<?php } ?>

<?php for($i = 1; $i <= $total_pages; $i++){ ?>

<a href="?search=<?php echo urlencode($search); ?>&page=<?php echo $i; ?>"
class="<?php echo ($i == $page) ? 'btn-confirm' : 'btn-edit'; ?>">

<?php echo $i; ?>

</a>

<?php } ?>

<?php if($page < $total_pages){ ?>
<a href="?search=<?php echo urlencode($search); ?>&page=<?php echo $page+1; ?>" class="btn-edit">
    Next
</a>
<?php } ?>

</div>
</div>
<?php } ?>

<script src="assets/js/theme.js"></script>

</body>
</html>