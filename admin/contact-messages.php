<?php
session_start();
require_once("../database/db.php");

// Get all messages
$query = "SELECT * FROM contact_messages ORDER BY created_at DESC";
$result = mysqli_query($conn, $query);

// Count messages
$count_query = mysqli_query($conn,"SELECT COUNT(*) AS total FROM contact_messages");
$count = mysqli_fetch_assoc($count_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Contact Messages</title>

<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

<style>

*{
margin:0;
padding:0;
box-sizing:border-box;
font-family:'Poppins',sans-serif;
}

body{
background:#f4f7fb;
padding:30px;
}

.header{
display:flex;
justify-content:space-between;
align-items:center;
margin-bottom:30px;
}

.header h1{
color:#1e3a8a;
font-size:34px;
}

.counter{
background:#2563eb;
color:#fff;
padding:12px 22px;
border-radius:10px;
font-weight:600;
}

.table-box{
background:#fff;
border-radius:18px;
overflow:hidden;
box-shadow:0 10px 30px rgba(0,0,0,.08);
}

table{
width:100%;
border-collapse:collapse;
}

thead{
background:#1e3a8a;
color:#fff;
}

thead th{
padding:18px;
font-size:15px;
}

tbody td{
padding:16px;
border-bottom:1px solid #eee;
font-size:15px;
}

tbody tr:hover{
background:#f8fbff;
}

.view-btn{

background:#16a34a;
color:#fff;
padding:8px 15px;
text-decoration:none;
border-radius:8px;
margin-right:8px;

}

.delete-btn{

background:#dc2626;
color:#fff;
padding:8px 15px;
text-decoration:none;
border-radius:8px;

}

.no-data{

padding:40px;
text-align:center;
font-size:20px;
color:#777;

}

</style>

</head>

<body>

<div class="header">

<h1>
<i class="fa-solid fa-envelope"></i>
Contact Messages
</h1>

<div class="counter">

Total Messages :
<?php echo $count['total']; ?>

</div>

</div>

<div class="table-box">

<table>

<thead>

<tr>

<th>ID</th>
<th>Name</th>
<th>Email</th>
<th>Subject</th>
<th>Date</th>
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

<td><?php echo htmlspecialchars($row['name']); ?></td>

<td><?php echo htmlspecialchars($row['email']); ?></td>

<td><?php echo htmlspecialchars($row['subject']); ?></td>

<td><?php echo date("d M Y",strtotime($row['created_at'])); ?></td>

<td>

<a
href="view-message.php?id=<?php echo $row['id']; ?>"
class="view-btn">

<i class="fa-solid fa-eye"></i>

View

</a>

<a
href="delete-message.php?id=<?php echo $row['id']; ?>"
class="delete-btn"
onclick="return confirm('Delete this message?')">

<i class="fa-solid fa-trash"></i>

Delete

</a>

</td>

</tr>

<?php

}

}else{

?>

<tr>

<td colspan="6" class="no-data">

<i class="fa-solid fa-envelope-open-text"
style="font-size:60px;"></i>

<br><br>

No Messages Found

</td>

</tr>

<?php } ?>

</tbody>

</table>

</div>

</body>

</html>