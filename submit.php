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


//================ FILE UPLOAD =================//

if(isset($_POST['upload']))
{
    $title = $_POST['title'];
    $description = $_POST['description'];
    $file_name = $_FILES['file']['name'];
    $file_tmp = $_FILES['file']['tmp_name'];
    $target_dir = "uploads/";
    $target_file = $target_dir . basename($file_name);

    // Ensure the uploads directory exists
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    if (move_uploaded_file($file_tmp, $target_file)) {
        // Use prepared statements to prevent SQL injection
        $stmt = mysqli_prepare($conn, "INSERT INTO submissions (project_id, title, description, file_name, submission_date) VALUES (?, ?, ?, ?, NOW())");
        mysqli_stmt_bind_param($stmt, "isss", $project_id, $title, $description, $file_name);
        
        if (mysqli_stmt_execute($stmt)) {
            header("Location: submit.php?status=success");
        } else {
            header("Location: submit.php?status=error");
        }
    } else {
        header("Location: submit.php?status=upload_failed");
    }
    mysqli_stmt_close($stmt);
    exit();
}


//================ DELETE SUBMISSION =================//

if(isset($_GET['delete']))
{
    $id = $_GET['delete'];
    // Use prepared statements to prevent SQL injection
    $stmt = mysqli_prepare($conn, "DELETE FROM submissions WHERE submission_id = ? AND project_id = ?");
    mysqli_stmt_bind_param($stmt, "ii", $id, $project_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    header("Location: submit.php?status=deleted");
    exit();
}

// Use prepared statements to fetch submissions
$stmt = mysqli_prepare($conn, "SELECT * FROM submissions WHERE project_id = ? ORDER BY submission_id DESC");
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

<meta name="viewport"
content="width=device-width,initial-scale=1">

<title>Submit Work</title>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap"
rel="stylesheet">

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
margin-bottom:10px;
text-decoration:none;
color:white;
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
padding:30px;
border-radius:15px;
box-shadow:0 5px 15px rgba(0,0,0,.1);

}

input,
textarea{

width:100%;
padding:10px;
border:1px solid #ccc;
border-radius:8px;
margin-bottom:15px;

}

input[type=file]{

padding:8px;

}

textarea{

height:90px;
resize:none;

}

.btn{

padding:10px 22px;
border:none;
border-radius:6px;
cursor:pointer;
font-size:15px;

}

.upload{

background:#2563eb;
color:white;

}

.back{

background:#6c757d;
color:white;
padding:10px 18px;
border-radius:6px;
text-decoration:none;
margin-left:10px;

}

.delete{

background:#dc3545;
color:white;
padding:8px 14px;
border-radius:5px;
text-decoration:none;

}

table{

width:100%;
margin-top:35px;
border-collapse:collapse;

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

<h1>Submit Project Work</h1>

<p>Upload your reports and project documents</p>

</div>

<div class="card">

<?php if(isset($_GET['status'])): ?>
    <?php if($_GET['status'] == 'success'): ?>
        <div style="padding: 15px; background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; border-radius: 5px; margin-bottom: 20px;">Project Submitted Successfully!</div>
    <?php elseif($_GET['status'] == 'deleted'): ?>
        <div style="padding: 15px; background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; border-radius: 5px; margin-bottom: 20px;">Submission Deleted Successfully!</div>
    <?php elseif($_GET['status'] == 'error'): ?>
        <div style="padding: 15px; background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; border-radius: 5px; margin-bottom: 20px;">Database insertion failed.</div>
    <?php elseif($_GET['status'] == 'upload_failed'): ?>
        <div style="padding: 15px; background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; border-radius: 5px; margin-bottom: 20px;">File upload failed.</div>
    <?php endif; ?>
<?php endif; ?>

<form method="post" enctype="multipart/form-data">

<input
type="text"
name="title"
placeholder="Submission Title"
required>

<textarea
name="description"
placeholder="Description"
required></textarea>

<input
type="file"
name="file"
required>

<button
type="submit"
name="upload"
class="btn upload">

<i class="fa fa-upload"></i>

 Upload File

</button>

<a
href="dashboard.php"
class="back">

Dashboard

</a>

</form>

<h2 style="margin-top:35px;">My Submissions</h2>

<table>

<tr>

<th>Submission ID</th>

<th>Title</th>

<th>File</th>

<th>Date</th>

<th>Action</th>

</tr>

<?php while($row=mysqli_fetch_assoc($result)){ ?>

<tr>

<td><?php echo $row['submission_id']; ?></td>

<td><?php echo $row['title']; ?></td>

<td>

<a href="uploads/<?php echo $row['file_name']; ?>" target="_blank">

<?php echo $row['file_name']; ?>

</a>

</td>

<td><?php echo $row['submission_date']; ?></td>

<td>
    <a
        href="submit.php?delete=<?php echo $row['submission_id']; ?>"
        class="delete"
        onclick="return confirm('Are you sure you want to delete this submission?');">

        <i class="fa fa-trash"></i>

        Delete

    </a>
</td>
</tr>
<?php } mysqli_stmt_close($stmt); ?>