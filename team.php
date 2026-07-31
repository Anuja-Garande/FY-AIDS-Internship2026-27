<?php

error_reporting(0); // Optional: Hide notices/warnings on production. Remove for debugging.
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


//================ ADD MEMBER =================//

if(isset($_POST['add']))
{

$name=$_POST['member_name'];

$roll=$_POST['roll_no'];

$email=$_POST['email'];

$mobile=$_POST['mobile'];

$insert_stmt = mysqli_prepare($conn, "INSERT INTO team_members (project_id, member_name, roll_no, email, mobile) VALUES (?, ?, ?, ?, ?)");
mysqli_stmt_bind_param($insert_stmt, "issss", $project_id, $name, $roll, $email, $mobile);
mysqli_stmt_execute($insert_stmt);
mysqli_stmt_close($insert_stmt);

header("Location: team.php?status=added");
exit();

}


//================ DELETE MEMBER =================//

if(isset($_GET['delete']))
{

$id=$_GET['delete'];

    $delete_stmt = mysqli_prepare($conn, "DELETE FROM team_members WHERE id = ?");
    mysqli_stmt_bind_param($delete_stmt, "i", $id);
    mysqli_stmt_execute($delete_stmt);
    mysqli_stmt_close($delete_stmt);
    header("Location: team.php?status=deleted");
exit();

}

$result_stmt = mysqli_prepare($conn, "SELECT * FROM team_members WHERE project_id = ? ORDER BY id");
mysqli_stmt_bind_param($result_stmt, "i", $project_id);
mysqli_stmt_execute($result_stmt);
$result = mysqli_stmt_get_result($result_stmt);
mysqli_stmt_close($result_stmt);

?>
<!DOCTYPE html>
<html>

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width,initial-scale=1">

<title>Team Members</title>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">

<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<style>

*{
margin:0;
padding:0;
box-sizing:border-box;
font-family:'Poppins',sans-serif;
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
left:0;
top:0;
padding:25px;

}

.sidebar h2{

color:#fff;
margin-bottom:35px;

}

.sidebar a{

display:block;
padding:14px;
margin-bottom:10px;
text-decoration:none;
color:white;
border-radius:8px;
transition:.3s;

}

.sidebar a:hover{

background:rgba(255,255,255,.2);

}

.main{

margin-left:240px;
width:calc(100% - 240px);
padding:35px;

}

.header{

background:white;
padding:25px;
border-radius:15px;
box-shadow:0 5px 15px rgba(0,0,0,.08);
margin-bottom:30px;

}

.card{

background:white;
padding:30px;
border-radius:15px;
box-shadow:0 5px 15px rgba(0,0,0,.08);

}

.card h2{

color:#2563eb;
margin-bottom:20px;

}

input{

width:100%;
padding:10px;
border:1px solid #ccc;
border-radius:8px;
margin-bottom:15px;

}

.btn{

padding:10px 22px;
border:none;
border-radius:6px;
cursor:pointer;
font-size:15px;

}

.add{

background:#2563eb;
color:white;

}

.back{

background:#6c757d;
color:white;
text-decoration:none;
padding:10px 20px;
border-radius:6px;
margin-left:10px;

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

.delete{

background:#dc3545;
color:white;
padding:8px 15px;
text-decoration:none;
border-radius:5px;

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

<h1>Team Members</h1>

<p>Manage Your Project Team</p>

</div>

<div class="card">

<?php if(isset($_GET['status']) && $_GET['status'] == 'added'): ?>
    <div style="padding: 15px; background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; border-radius: 5px; margin-bottom: 20px;">
        Member Added Successfully!
    </div>
<?php elseif(isset($_GET['status']) && $_GET['status'] == 'deleted'): ?>
    <div style="padding: 15px; background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; border-radius: 5px; margin-bottom: 20px;">
        Member Deleted Successfully!
    </div>
<?php endif; ?>

<h2>Add Team Member</h2>

<form method="post">

<input
type="text"
name="member_name"
placeholder="Member Name"
required>

<input
type="text"
name="roll_no"
placeholder="Roll Number"
required>

<input
type="email"
name="email"
placeholder="Email Address"
required>

<input
type="text"
name="mobile"
placeholder="Mobile Number"
required>

<button
type="submit"
name="add"
class="btn add">

<i class="fa fa-plus"></i>

 Add Member

</button>

<a
href="dashboard.php"
class="back">

<i class="fa fa-arrow-left"></i>

 Dashboard

</a>

</form>

<h2 style="margin-top:35px;">

Team Members List

</h2>

<table>

<tr>

<th>ID</th>

<th>Name</th>

<th>Roll No</th>

<th>Email</th>

<th>Mobile</th>

<th>Action</th>

</tr>

<?php while($row=mysqli_fetch_assoc($result)){ ?>

<tr>

<td><?php echo $row['id']; ?></td>

<td><?php echo $row['member_name']; ?></td>

<td><?php echo $row['roll_no']; ?></td>

<td><?php echo $row['email']; ?></td>

<td><?php echo $row['mobile']; ?></td>

<td>
        <a
href="team.php?delete=<?php echo $row['id']; ?>"
class="delete"
onclick="return confirm('Are you sure you want to remove this team member?');">

<i class="fa fa-trash"></i>

 Delete

</a>
</td>

</tr>

<?php } ?>

</table>

</div>

</div>

</body>

</html>