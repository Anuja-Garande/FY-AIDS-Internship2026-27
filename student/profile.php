<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Student Profile</title>

<link rel="stylesheet" href="../assets/css/dashboard.css">

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">

<style>

.profile-box{
    background:#fff;
    padding:30px;
    border-radius:10px;
    margin-top:20px;
    width:70%;
    box-shadow:0 2px 8px rgba(0,0,0,0.1);
}

.profile-box table{
    width:100%;
    border-collapse:collapse;
}

.profile-box td{
    padding:15px;
    border-bottom:1px solid #ddd;
}

.profile-box td:first-child{
    font-weight:bold;
    width:35%;
}

.edit-btn{
    margin-top:20px;
    padding:10px 25px;
    background:#2d89ef;
    color:#fff;
    border:none;
    border-radius:5px;
    cursor:pointer;
}

.edit-btn:hover{
    background:#1c6fd1;
}

</style>

</head>

<body>

<div class="sidebar">

<div class="logo">
<h2>📚 Library</h2>
<p>Student Panel</p>
</div>

<ul>

<li><a href="dashboard.php"><i class="fa-solid fa-house"></i> Dashboard</a></li>

<li><a href="search_books.php"><i class="fa-solid fa-magnifying-glass"></i> Search Books</a></li>

<li><a href="borrow_books.php"><i class="fa-solid fa-book-open-reader"></i> Borrow Books</a></li>

<li><a href="borrowed_books.php"><i class="fa-solid fa-book-open-reader"></i> Borrowed Books</a></li>

<li class="active"><a href="profile.php"><i class="fa-solid fa-user"></i> Profile</a></li>

<li><a href="../login.php"><i class="fa-solid fa-right-from-bracket"></i> Logout</a></li>

</ul>

</div>

<div class="main-content">

<header>

<h1>Student Profile</h1>

<div class="admin-profile">
Welcome, Komal 👋
</div>

</header>

<div class="profile-box">

<table>

<tr>
<td>Student ID</td>
<td>STU001</td>
</tr>

<tr>
<td>Name</td>
<td>Komal Ghandge</td>
</tr>

<tr>
<td>Email</td>
<td>komal@gmail.com</td>
</tr>

<tr>
<td>Department</td>
<td>AI & Data Science</td>
</tr>

<tr>
<td>Year</td>
<td>Second Year</td>
</tr>

<tr>
<td>Division</td>
<td>E</td>
</tr>

<tr>
<td>Phone</td>
<td>9876543210</td>
</tr>

</table>
<br>

<a href="edit_profile.php" class="edit-btn">
    <i class="fa-solid fa-user-pen"></i> Edit Profile
</a>

</div>

</div>

</body>
</html>