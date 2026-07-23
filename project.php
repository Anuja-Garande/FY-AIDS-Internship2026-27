<?php

session_start();
include("database/connection.php");

if(!isset($_SESSION['user_id']))
{
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Use prepared statements to prevent SQL injection
$stmt = mysqli_prepare($conn, "SELECT p.* FROM students s INNER JOIN projects p ON s.project_id = p.project_id WHERE s.user_id = ?");
if ($stmt === false) {
    die("Prepare failed: " . htmlspecialchars(mysqli_error($conn)));
}
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if(mysqli_num_rows($result)==0)
{
    die("Project Not Assigned.");
}

$row = mysqli_fetch_assoc($result);



//================ UPDATE PROJECT =================//

if(isset($_POST['update']))
{

$project_title=$_POST['project_title'];

$project_domain=$_POST['project_domain'];

$problem_statement=$_POST['problem_statement'];

$project_description=$_POST['project_description'];

$guide_name=$_POST['guide_name'];

$team_name=$_POST['team_name'];

$status=$_POST['status'];

$start_date=$_POST['start_date'];

$end_date=$_POST['end_date'];

$update_sql = "UPDATE projects SET project_title=?, project_domain=?, problem_statement=?, project_description=?, guide_name=?, team_name=?, status=?, start_date=?, end_date=? WHERE project_id=?";
$update_stmt = mysqli_prepare($conn, $update_sql);
mysqli_stmt_bind_param($update_stmt, "sssssssssi", $project_title, $project_domain, $problem_statement, $project_description, $guide_name, $team_name, $status, $start_date, $end_date, $row['project_id']);


if(mysqli_stmt_execute($update_stmt))
{

echo "<script>alert('Project Updated Successfully');</script>";

echo "<script>window.location='project.php';</script>";

exit();

}

else
{

echo "<script>alert('Update Failed');</script>";

}

}



//================ DELETE PROJECT =================//

if(isset($_POST['delete']))
{

    $delete_sql = "UPDATE students SET project_id=NULL WHERE user_id=?";
    $delete_stmt = mysqli_prepare($conn, $delete_sql);
    mysqli_stmt_bind_param($delete_stmt, "i", $user_id);

if(mysqli_stmt_execute($delete_stmt))
{

echo "<script>alert('Project Removed Successfully');</script>";

echo "<script>window.location='dashboard.php';</script>";

exit();

}

else
{

echo "<script>alert('Unable To Remove Project');</script>";

}

}

?>

<!DOCTYPE html>

<html>

<head>

<meta charset="UTF-8">

<meta name="viewport"
content="width=device-width,initial-scale=1">

<title>My Project</title>

<link
href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap"
rel="stylesheet">

<link
rel="stylesheet"
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

left:0;

top:0;

padding:25px;

}

.sidebar h2{

color:white;

margin-bottom:35px;

}

.sidebar a{

display:block;

padding:14px;

margin-bottom:10px;

color:white;

text-decoration:none;

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
h2{
color:#2563eb;
margin-bottom:25px;
}

table{
width:100%;
}

table td{
padding:12px;
vertical-align:top;
}

table td:first-child{
width:180px;
font-weight:600;
}

input,
textarea,
select{

width:100%;
padding:11px;
border:1px solid #ccc;
border-radius:8px;
font-size:15px;

}

textarea{
height:100px;
resize:none;
}

.btn{

padding:12px 22px;
border:none;
border-radius:8px;
cursor:pointer;
font-size:15px;
margin:8px;

}

.update{

background:#2563eb;
color:white;

}

.delete{

background:#dc3545;
color:white;

}

.back{

background:#6c757d;
color:white;
text-decoration:none;
display:inline-block;

}

.btn:hover{

opacity:.9;

}

@media(max-width:768px){

body{

display:block;

}

.sidebar{

width:100%;
height:auto;
position:relative;

}

.main{

margin-left:0;
width:100%;

}

table,
tr,
td{

display:block;
width:100%;

}

}

</style>

</head>

<body>

<div class="sidebar">

<h2>Student Portal</h2>

<a href="dashboard.php">
<i class="fa-solid fa-house"></i> Dashboard
</a>

<a href="profile.php">
<i class="fa-solid fa-user"></i> Student Profile
</a>

<a href="project.php">
<i class="fa-solid fa-folder"></i> My Project
</a>

<a href="task.php">
<i class="fa-solid fa-list-check"></i> Task List
</a>

<a href="team.php">
<i class="fa-solid fa-users"></i> Team Members
</a>

<a href="submit.php">
<i class="fa-solid fa-upload"></i> Submit Work
</a>

<a href="logout.php">
<i class="fa-solid fa-right-from-bracket"></i> Logout
</a>

</div>

<div class="main">

<div class="header">

<h1>

📁 My Project

</h1>

<p>

Welcome,

<b><?php echo $row['team_name']; ?></b>

</p>

</div>

<div class="card">

<h2>

Project Details

</h2>

<form method="post">

<table>

<tr>

<td>Project Title</td>

<td>

<input
type="text"
name="project_title"
value="<?php echo $row['project_title']; ?>">

</td>

</tr>

<tr>

<td>Project Domain</td>

<td>

<input
type="text"
name="project_domain"
value="<?php echo $row['project_domain']; ?>">

</td>

</tr>

<tr>

<td>Guide Name</td>

<td>

<input
type="text"
name="guide_name"
value="<?php echo $row['guide_name']; ?>">

</td>

</tr>

<tr>

<td>Team Name</td>

<td>

<input
type="text"
name="team_name"
value="<?php echo $row['team_name']; ?>">

</td>

</tr>

<tr>

<td>Problem Statement</td>

<td>

<textarea
name="problem_statement"><?php echo $row['problem_statement']; ?></textarea>

</td>

</tr>

<tr>

<td>Description</td>

<td>

<textarea
name="project_description"><?php echo $row['project_description']; ?></textarea>

</td>

</tr>

<tr>

<td>Start Date</td>

<td>

<input
type="date"
name="start_date"
value="<?php echo $row['start_date']; ?>">

</td>

</tr>

<tr>

<td>End Date</td>

<td>

<input
type="date"
name="end_date"
value="<?php echo $row['end_date']; ?>">

</td>

</tr>

<tr>

<td>Status</td>

<td>

<select name="status">

<option value="Pending"
<?php if($row['status']=="Pending") echo "selected"; ?>>
Pending
</option>

<option value="In Progress"
<?php if($row['status']=="In Progress") echo "selected"; ?>>
In Progress
</option>

<option value="Completed"
<?php if($row['status']=="Completed") echo "selected"; ?>>
Completed
</option>

</select>

</td>
</tr>

<tr>
    <td colspan="2" style="text-align:center;">
        <button type="submit" name="update" class="btn update">
            <i class="fa fa-save"></i> Update Project
        </button>
        <button type="submit" name="delete" class="btn delete" onclick="return confirm('Are you sure you want to remove this project from your profile?');">
            <i class="fa fa-trash"></i> Remove Project
        </button>
        <a href="dashboard.php" class="btn back">
            <i class="fa fa-arrow-left"></i> Dashboard
        </a>
    </td>
</tr>

</table>

</form>

</div>

</div>

</body>

</html>