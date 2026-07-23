
<?php

session_start();

if(!isset($_SESSION['username']))
{
    header("Location: login.php");
    exit();
}

?>


<!DOCTYPE html>
<html lang="en">
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Student Dashboard</title>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

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
background:#f5f7fa;
}

.container{
display:flex;
min-height:100vh;
}

/* Sidebar */

.sidebar{
width:250px;
background:#0d6efd;
color:white;
padding:20px;
}

.sidebar h2{
text-align:center;
margin-bottom:30px;
}

.sidebar ul{
list-style:none;
}

.sidebar ul li{
margin:18px 0;
}

.sidebar ul li a{
text-decoration:none;
color:white;
display:block;
padding:12px;
border-radius:8px;
transition:.3s;
}

.sidebar ul li a:hover{
background:white;
color:#0d6efd;
}

/* Main Content */

.main{
flex:1;
padding:30px;
}

.header{
background:white;
padding:20px;
border-radius:10px;
box-shadow:0 5px 10px rgba(0,0,0,.1);
margin-bottom:30px;
}

.header h1{
color:#0d6efd;
}

.cards{

display:grid;

grid-template-columns:repeat(auto-fit,minmax(220px,1fr));

gap:20px;

}

.card{

background:white;

padding:30px;

border-radius:12px;

text-align:center;

box-shadow:0 5px 15px rgba(0,0,0,.1);

transition:.3s;

}

.card:hover{

transform:translateY(-8px);

}

.card i{

font-size:45px;

color:#0d6efd;

margin-bottom:15px;

}

.card h3{

margin-bottom:10px;

}

.card a{

display:inline-block;

margin-top:15px;

padding:10px 18px;

background:#0d6efd;

color:white;

text-decoration:none;

border-radius:5px;

}

.logout{

margin-top:30px;

text-align:right;

}

.logout a{

background:red;

padding:12px 20px;

color:white;

text-decoration:none;

border-radius:5px;

}

@media(max-width:768px){

.container{

flex-direction:column;

}

.sidebar{

width:100%;

}

}

</style>

</head>

<body>

<div class="container">

<div class="sidebar">

<h2>Student Portal</h2>

<ul>

<li><a href="dashboard.php"><i class="fa fa-home"></i> Dashboard</a></li>

<li><a href="profile.php"><i class="fa fa-user"></i> Student Profile</a></li>

<li><a href="project.php"><i class="fa fa-folder"></i> My Project</a></li>

<li><a href="task.php"><i class="fa fa-list-check"></i> Task List</a></li>

<li><a href="team.php"><i class="fa fa-users"></i> Team Members</a></li>

<li><a href="submit.php"><i class="fa fa-upload"></i> Submit Work</a></li>

<li><a href="index.php"><i class="fa fa-sign-out-alt"></i> Logout</a></li>

</ul>

</div>

<div class="main">

<div class="header">

<h1>Welcome, Student 👋</h1>

<p>Student Project Portal Dashboard</p>

</div>

<div class="cards">

<div class="card">
<i class="fa fa-user"></i>
<h3>Student Profile</h3>
<p>View and update your profile.</p>
<a href="profile.php">Open</a>
</div>

<div class="card">
<i class="fa fa-folder-open"></i>
<h3>My Project</h3>
<p>View project details.</p>
<a href="project.php">Open</a>
</div>

<div class="card">
<i class="fa fa-list-check"></i>
<h3>Task List</h3>
<p>Manage assigned tasks.</p>
<a href="task.php">Open</a>
</div>

<div class="card">
<i class="fa fa-users"></i>
<h3>Team Members</h3>
<p>View your team members.</p>
<a href="team.php">Open</a>
</div>

<div class="card">
<i class="fa fa-upload"></i>
<h3>Submit Work</h3>
<p>Upload project files.</p>
<a href="submit.php">Open</a>
</div>



</div>


</div>

</div>

</body>
</html>