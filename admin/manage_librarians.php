<?php
session_start();

include "../includes/db_connect.php";

if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login.php");
    exit();
}

$result = mysqli_query($conn, "SELECT * FROM librarian ORDER BY librarian_id ASC");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Librarians</title>

    <link rel="stylesheet" href="../assets/css/dashboard.css">

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">

<style>

body{
    margin:0;
    font-family:Poppins,sans-serif;
    background:#f5f7fb;
}

.main-content{
    margin-left:260px;
    padding:30px;
}

h1{
    color:#1E3A8A;
}

table{
    width:100%;
    border-collapse:collapse;
    margin-top:25px;
    background:white;
    box-shadow:0 5px 15px rgba(0,0,0,.1);
}

table th{
    background:#1E3A8A;
    color:white;
    padding:15px;
}

table td{
    padding:15px;
    text-align:center;
    border-bottom:1px solid #ddd;
}

.edit{
    background:#10B981;
    color:white;
    padding:8px 15px;
    text-decoration:none;
    border-radius:5px;
}

.delete{
    background:#EF4444;
    color:white;
    padding:8px 15px;
    text-decoration:none;
    border-radius:5px;
}

</style>

</head>

<body>

<div class="sidebar">

<div class="logo">
<h2>📚 Library</h2>
<p>Admin Panel</p>
</div>

<ul>

<li>
<a href="dashboard.php">
<i class="fa-solid fa-house"></i> Dashboard
</a>
</li>

<li>
<a href="manage_books.php">
<i class="fa-solid fa-book"></i> Manage Books
</a>
</li>

<li>
<a href="manage_categories.php">
<i class="fa-solid fa-layer-group"></i> Categories
</a>
</li>

<li class="active">
<a href="manage_librarians.php">
<i class="fa-solid fa-user-tie"></i> Librarians
</a>
</li>

<li>
<a href="reports.php">
<i class="fa-solid fa-chart-column"></i> Reports
</a>
</li>

<li>
<a href="settings.php">
<i class="fa-solid fa-gear"></i> Settings
</a>
</li>

<li>
<a href="../login.php">
<i class="fa-solid fa-right-from-bracket"></i> Logout
</a>
</li>

</ul>

</div>

<div class="main-content">

<h1>Manage Librarians</h1>

<table>

<tr>

<th>ID</th>
<th>Name</th>
<th>Email</th>
<th>Phone</th>
<th>Address</th>
<th>Action</th>

</tr>

<?php while($row=mysqli_fetch_assoc($result)){ ?>

<tr>

<td><?php echo $row['librarian_id']; ?></td>

<td><?php echo $row['name']; ?></td>

<td><?php echo $row['email']; ?></td>

<td><?php echo $row['phone']; ?></td>

<td><?php echo $row['address']; ?></td>

<td>

<a class="edit" href="#">Edit</a>

<a class="delete" href="#">Delete</a>

</td>

</tr>

<?php } ?>

</table>

</div>

</body>
</html>