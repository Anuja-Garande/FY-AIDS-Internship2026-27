<?php

include "../includes/db_connect.php";

$search = "";

if(isset($_GET['search'])){
    $search = mysqli_real_escape_string($conn, $_GET['search']);
}

$query = "SELECT books.*, categories.category_name
FROM books
LEFT JOIN categories
ON books.category_id = categories.category_id
WHERE books.title LIKE '%$search%'
OR books.author LIKE '%$search%'
OR categories.category_name LIKE '%$search%'
OR books.publisher LIKE '%$search%'
OR books.isbn LIKE '%$search%'";

$result = mysqli_query($conn, $query);

?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Search Books | Digital Library Management System</title>

<link rel="stylesheet" href="../assets/css/dashboard.css">

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">

<style>

.search-box{
    background:#fff;
    padding:20px;
    border-radius:10px;
    margin-top:20px;
    box-shadow:0 2px 8px rgba(0,0,0,0.1);
}

.search-box input{
    width:80%;
    padding:12px;
    border:1px solid #ccc;
    border-radius:5px;
    font-size:15px;
}

.search-box button{
    padding:12px 20px;
    border:none;
    background:#2d89ef;
    color:#fff;
    border-radius:5px;
    cursor:pointer;
}

.search-box button:hover{
    background:#1d6fd6;
}

table{
    width:100%;
    margin-top:25px;
    border-collapse:collapse;
    background:#fff;
}

table th, table td{
    padding:12px;
    border:1px solid #ddd;
    text-align:center;
}

table th{
    background:#2d89ef;
    color:#fff;
}

.available{
    color:green;
    font-weight:bold;
}

.notavailable{
    color:red;
    font-weight:bold;
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

<li>
<a href="dashboard.php">
<i class="fa-solid fa-house"></i> Dashboard
</a>
</li>

<li class="active">
<a href="search_books.php">
<i class="fa-solid fa-magnifying-glass"></i> Search Books
</a>
</li>

<li><a href="borrow_books.php"><i class="fa-solid fa-book-open-reader"></i> Borrow Books</a></li>

<li>
<a href="borrowed_books.php">
<i class="fa-solid fa-book-open-reader"></i> Borrowed Books
</a>
</li>

<li>
<a href="profile.php">
<i class="fa-solid fa-user"></i> Profile
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

<header>

<h1>Search Books</h1>

<div class="admin-profile">
Welcome, Komal 👋
</div>

</header>

<div class="search-box">

<form method="GET">

<input
type="text"
name="search"
value="<?php echo $search; ?>"
placeholder="Search by Book Title, Author, Category or ISBN">

<button type="submit">
<i class="fa-solid fa-magnifying-glass"></i> Search
</button>

</form>

</div>

<table>

<tr>
<th>ID</th>
<th>Cover</th>
<th>Title</th>
<th>Author</th>
<th>Category</th>
<th>Publisher</th>
<th>Available</th>
</tr>

<?php while($row = mysqli_fetch_assoc($result)){ ?>

<tr>

<td><?php echo $row['book_id']; ?></td>

<td>
<img
src="../assets/images/books/<?php echo $row['image']; ?>"
width="60">
</td>

<td><?php echo $row['title']; ?></td>

<td><?php echo $row['author']; ?></td>

<td><?php echo $row['category_name']; ?></td>

<td><?php echo $row['publisher']; ?></td>

<td>

<?php

if($row['available_quantity'] > 0){
    echo "<span class='available'>Yes</span>";
}
else{
    echo "<span class='notavailable'>No</span>";
}

?>

</td>

</tr>

<?php } ?>

</table>

</div>

</body>
</html>