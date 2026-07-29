<?php

session_start();

include "../includes/db_connect.php";


// Temporary student id
// Later replace with $_SESSION['student_id']
$student_id = 2;


$query = mysqli_query($conn,

"SELECT 
    issued_books.issue_id,
    books.title,
    books.author,
    issued_books.issue_date,
    issued_books.due_date,
    issued_books.status

FROM issued_books

INNER JOIN books
ON issued_books.book_id = books.book_id

WHERE issued_books.student_id = '$student_id'

ORDER BY issued_books.issue_id DESC"

);

?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Borrowed Books</title>

<link rel="stylesheet" href="../assets/css/dashboard.css">

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">

<style>
table{
    width:100%;
    border-collapse:collapse;
    margin-top:20px;
    background:#fff;
}
table th,table td{
    border:1px solid #ddd;
    padding:12px;
    text-align:center;
}
table th{
    background:#2d89ef;
    color:#fff;
}
h2{
    margin-top:20px;
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

<li class="active"><a href="borrowed_books.php"><i class="fa-solid fa-book-open-reader"></i> Borrowed Books</a></li>

<li><a href="borrow_books.php"><i class="fa-solid fa-book-open-reader"></i> Borrow Books</a></li>

<li><a href="profile.php"><i class="fa-solid fa-user"></i> Profile</a></li>

<li><a href="../login.php"><i class="fa-solid fa-right-from-bracket"></i> Logout</a></li>

</ul>

</div>

<div class="main-content">

<header>

<h1>Borrowed Books</h1>

<div class="admin-profile">
Student Panel
</div>

</header>

<h2>Issued Books</h2>

<table>

<tr>
<th>Book ID</th>
<th>Book Name</th>
<th>Author</th>
<th>Issue Date</th>
<th>Due Date</th>
<th>Status</th>
<th>Action</th>
</tr>
<?php

if(mysqli_num_rows($query) > 0)
{

while($row = mysqli_fetch_assoc($query))
{

?>

<tr>

<td>
<?php echo $row['issue_id']; ?>
</td>

<td>
<?php echo $row['title']; ?>
</td>

<td>
<?php echo $row['author']; ?>
</td>

<td>
<?php echo $row['issue_date']; ?>
</td>

<td>
<?php echo $row['due_date']; ?>
</td>

<td>
<?php echo $row['status']; ?>
</td>

<td>

<?php if($row['status']=="Issued"){ ?>

<form method="POST" action="return_book.php">

<input type="hidden" name="issue_id" value="<?php echo $row['issue_id']; ?>">

<button type="submit">
Return Book
</button>

</form>

<?php } else { ?>

Returned

<?php } ?>

</td>

</tr>


<?php

}

}

else

{

?>

<tr>
<td colspan="7">
No Borrowed Books Found
</td>
</tr>

<?php

}

?>
</table>
</div>

</body>
</html>