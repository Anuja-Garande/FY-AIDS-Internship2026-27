<?php

session_start();

include "../includes/db_connect.php";

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard | Digital Library Management System</title>

    <link rel="stylesheet" href="../assets/css/dashboard.css">

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
</head>

<body>

<div class="sidebar">

    <div class="logo">
        <h2>📚 Library</h2>
        <p>Student Panel</p>
    </div>

    <ul>

        <li class="active">
            <a href="dashboard.php">
                <i class="fa-solid fa-house"></i> Dashboard
            </a>
        </li>

        <li>
            <a href="search_books.php">
                <i class="fa-solid fa-magnifying-glass"></i> Search Books
            </a>
        </li>

        <li>
    <a href="borrow_books.php">
        <i class="fa-solid fa-book-open-reader"></i> Borrow Books
    </a>
</li>

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

<h1>Student Dashboard</h1>

<div class="admin-profile">
Welcome, Komal 👋
</div>

</header>

<div class="cards">

<div class="card">
<i class="fa-solid fa-book"></i>
<h2>2</h2>
<p>Borrowed Books</p>
</div>

<div class="card">
<i class="fa-solid fa-calendar-days"></i>
<h2>12 Days</h2>
<p>Due In</p>
</div>

<div class="card">
<i class="fa-solid fa-indian-rupee-sign"></i>
<h2>₹0</h2>
<p>Fine</p>
</div>

<div class="card">
<i class="fa-solid fa-circle-check"></i>
<h2>5</h2>
<p>Books Returned</p>
</div>

</div>

<div class="recent">

<h2>My Borrowed Books</h2>

<table>

<tr>
<th>Book Name</th>
<th>Author</th>
<th>Issue Date</th>
<th>Due Date</th>
<th>Status</th>
</tr>


<?php

$student_id = 2;

$borrowed_books = mysqli_query($conn,

"SELECT 
books.title,
books.author,
issued_books.issue_date,
issued_books.due_date,
issued_books.status

FROM issued_books

INNER JOIN books
ON issued_books.book_id = books.book_id

WHERE issued_books.student_id='$student_id'

ORDER BY issued_books.issue_id DESC

LIMIT 5"

);


if(mysqli_num_rows($borrowed_books) > 0)
{

while($row = mysqli_fetch_assoc($borrowed_books))
{

?>

<tr>

<td><?php echo $row['title']; ?></td>

<td><?php echo $row['author']; ?></td>

<td><?php echo $row['issue_date']; ?></td>

<td><?php echo $row['due_date']; ?></td>

<td><?php echo $row['status']; ?></td>

</tr>


<?php

}

}
else
{

?>

<tr>
<td colspan="5">No Borrowed Books</td>
</tr>

<?php

}

?>

</table>

</div>

</div>

</body>
</html>