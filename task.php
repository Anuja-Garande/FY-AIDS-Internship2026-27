<?php

session_start();
include("database/connection.php");

if(!isset($_SESSION['user_id']))
{
    header("Location:login.php");
    exit();
}

$user_id=$_SESSION['user_id'];

// Get Project ID of logged in student
$stmt = mysqli_prepare($conn, "SELECT project_id FROM students WHERE user_id = ?");
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$student=mysqli_fetch_assoc($res);
$project_id=$student['project_id'];
mysqli_stmt_close($stmt);


//================ ADD TASK =================//

if(isset($_POST['add']))
{

$title=$_POST['title'];
$description=$_POST['description'];
$priority=$_POST['priority'];
$due_date=$_POST['due_date'];
$status=$_POST['status'];
    
$stmt = mysqli_prepare($conn, "INSERT INTO tasks(project_id, title, description, priority, due_date, status) VALUES (?, ?, ?, ?, ?, ?)");
mysqli_stmt_bind_param($stmt, "isssss", $project_id, $title, $description, $priority, $due_date, $status);
mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);

header("Location: task.php?status=added");
exit();

}


//================ DELETE TASK =================//

if(isset($_GET['delete']))
{

$id=$_GET['delete'];

$stmt = mysqli_prepare($conn, "DELETE FROM tasks WHERE task_id = ? AND project_id = ?");
mysqli_stmt_bind_param($stmt, "ii", $id, $project_id);
mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);

header("Location: task.php?status=deleted");
exit();

}


$stmt = mysqli_prepare($conn, "SELECT * FROM tasks WHERE project_id = ? ORDER BY task_id DESC");
if ($stmt === false) {
    die("Prepare failed: " . htmlspecialchars(mysqli_error($conn)));
}
mysqli_stmt_bind_param($stmt, "i", $project_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

?>
<!DOCTYPE html>

<html>

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width,initial-scale=1">

<title>Task List</title>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">

<link rel="stylesheet"

href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<style>

*{

margin:0;
padding:0;
box-sizing:border-box;
font-family:Poppins,sans-serif;

}

body{

background:#eef2f7;
display:flex;

}

.sidebar{

width:240px;
background:#2563eb;
height:100vh;
position:fixed;
padding:25px;

}

.sidebar h2{

color:white;
margin-bottom:35px;

}

.sidebar a{

display:block;
padding:14px;
color:white;
text-decoration:none;
margin-bottom:10px;
border-radius:8px;

}

.sidebar a:hover{

background:rgba(255,255,255,.2);

}

.main{

margin-left:240px;
padding:35px;
width:calc(100% - 240px);

}

.header{

background:white;
padding:25px;
border-radius:15px;
box-shadow:0 5px 15px rgba(0,0,0,.1);
margin-bottom:30px;

}

.card{

background:white;
padding:25px;
border-radius:15px;
box-shadow:0 5px 15px rgba(0,0,0,.1);

}
h2{
color:#2563eb;
margin-bottom:20px;
}

input,
textarea,
select{

width:100%;
padding:10px;
border:1px solid #ccc;
border-radius:8px;
margin-top:5px;

}

textarea{

height:80px;
resize:none;

}

table{

width:100%;
border-collapse:collapse;
margin-top:30px;

}

table th{

background:#2563eb;
color:white;
padding:12px;

}

table td{

padding:12px;
border-bottom:1px solid #ddd;
text-align:center;

}

.btn{

padding:10px 20px;
border:none;
border-radius:6px;
cursor:pointer;
font-size:15px;
margin-top:15px;

}

.add{

background:#2563eb;
color:white;

}

.edit{

background:#ffc107;
color:black;
text-decoration:none;
padding:8px 15px;
border-radius:5px;

}

.delete{

background:#dc3545;
color:white;
text-decoration:none;
padding:8px 15px;
border-radius:5px;

}

.back{

background:#6c757d;
color:white;
text-decoration:none;
padding:10px 18px;
border-radius:6px;

}

@media(max-width:768px){

body{

display:block;

}

.sidebar{

position:relative;
width:100%;
height:auto;

}

.main{

margin-left:0;
width:100%;

}

table{

display:block;
overflow:auto;

}

}

</style>

</head>

<body>

<div class="sidebar">

<h2>Student Portal</h2>

<a href="dashboard.php"><i class="fa fa-home"></i> Dashboard</a>

<a href="profile.php"><i class="fa fa-user"></i> Student Profile</a>

<a href="project.php"><i class="fa fa-folder"></i> My Project</a>

<a href="task.php"><i class="fa fa-list"></i> Task List</a>

<a href="team.php"><i class="fa fa-users"></i> Team Members</a>

<a href="submit.php"><i class="fa fa-upload"></i> Submit Work</a>

<a href="logout.php"><i class="fa fa-sign-out-alt"></i> Logout</a>

</div>

<div class="main">

<div class="header">

<h1>Task Management</h1>

<p>Create and Manage Your Daily Tasks</p>

</div>

<div class="card">

<?php if(isset($_GET['status']) && $_GET['status'] == 'added'): ?>
    <div style="padding: 15px; background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; border-radius: 5px; margin-bottom: 20px;">
        Task Added Successfully!
    </div>
<?php elseif(isset($_GET['status']) && $_GET['status'] == 'deleted'): ?>
    <div style="padding: 15px; background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; border-radius: 5px; margin-bottom: 20px;">
        Task Deleted Successfully!
    </div>
<?php endif; ?>

<h2>Add New Task</h2>

<form method="post">

<label>Task Title</label>

<input
type="text"
name="title"
required>

<label>Description</label>

<textarea
name="description"
required></textarea>

<label>Priority</label>

<select name="priority">

<option>High</option>

<option>Medium</option>

<option>Low</option>

</select>

<label>Due Date</label>

<input
type="date"
name="due_date"
required>

<label>Status</label>

<select name="status">

<option>Pending</option>

<option>In Progress</option>

<option>Completed</option>

</select>

<button
type="submit"
name="add"
class="btn add">

<i class="fa fa-plus"></i>

 Add Task

</button>

<a
href="dashboard.php"
class="back">

<i class="fa fa-arrow-left"></i>

 Dashboard

</a>

</form>

<h2 style="margin-top:40px;">

My Tasks

</h2>

<table>

<tr>

<th>Task ID</th>

<th>Title</th>

<th>Priority</th>

<th>Due Date</th>

<th>Status</th>

<th>Action</th>

</tr>

<?php

while($row=mysqli_fetch_assoc($result))
{

?>

<tr>

<td><?php echo $row['task_id']; ?></td>

<td><?php echo $row['title']; ?></td>

<td><?php echo $row['priority']; ?></td>

<td><?php echo $row['due_date']; ?></td>

<td><?php echo $row['status']; ?></td>

<td>
    <a
href="edit_task.php?id=<?php echo $row['task_id']; ?>"
class="edit">

<i class="fa fa-edit"></i>

 Edit

</a>

<a
href="task.php?delete=<?php echo $row['task_id']; ?>"
class="delete"
onclick="return confirm('Are you sure you want to delete this task?');">

<i class="fa fa-trash"></i>

 Delete

</a>

</td>

</tr>

<?php
}
?>

</table>

</div>

</div>

</body>

</html>