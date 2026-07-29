<?php

session_start();

include "../includes/db_connect.php";


// Statistics

$total_books = mysqli_fetch_assoc(
mysqli_query($conn,"SELECT COUNT(*) AS total FROM books")
)['total'];


$total_students = mysqli_fetch_assoc(
mysqli_query($conn,"SELECT COUNT(*) AS total FROM student")
)['total'];


$issued_books = mysqli_fetch_assoc(
mysqli_query($conn,"SELECT COUNT(*) AS total FROM issued_books WHERE status='Issued'")
)['total'];


$returned_books = mysqli_fetch_assoc(
mysqli_query($conn,"SELECT COUNT(*) AS total FROM returned_books")
)['total'];



// Recent Issued

$issued = mysqli_query($conn,

"SELECT 
student.name,
books.title,
issued_books.issue_date,
issued_books.due_date,
issued_books.status

FROM issued_books

INNER JOIN student
ON issued_books.student_id = student.student_id

INNER JOIN books
ON issued_books.book_id = books.book_id

ORDER BY issued_books.issue_id DESC

LIMIT 5"

);



// Recent Returned

$returned = mysqli_query($conn,

"SELECT

student.name,
books.title,
returned_books.return_date,
returned_books.fine_paid

FROM returned_books

INNER JOIN student
ON returned_books.student_id = student.student_id

INNER JOIN books
ON returned_books.book_id = books.book_id

ORDER BY returned_books.return_id DESC

LIMIT 5"

);


?>


<!DOCTYPE html>
<html>

<head>

<title>Library Reports</title>

<style>

body{
font-family:Arial;
background:#f4f7fc;
}


.container{
width:90%;
margin:40px auto;
}


h1{
color:#1E3A8A;
}


.cards{

display:grid;
grid-template-columns:repeat(4,1fr);
gap:20px;
margin-top:25px;

}

.card{

background:white;
padding:25px;
text-align:center;
border-radius:10px;
box-shadow:0 5px 15px rgba(0,0,0,0.12);

}


.card h2{
color:#1E3A8A;
}



table{

width:100%;
background:white;
border-collapse:collapse;
margin-top:20px;

}


th{

background:#1E3A8A;
color:white;
padding:12px;

}


td{

padding:12px;
text-align:center;
border-bottom:1px solid #ddd;

}


.section{

margin-top:40px;

}

</style>


</head>


<body>


<div class="container">


<h1>📊 Library Reports</h1>


<div class="cards">


<div class="card">
<h2><?= $total_books ?></h2>
<p>Total Books</p>
</div>

<div class="card">
<h2><?= $total_students ?></h2>
<p>Total Students</p>
</div>


<div class="card">
<h2><?= $issued_books ?></h2>
<p>Issued Books</p>
</div>


<div class="card">
<h2><?= $returned_books ?></h2>
<p>Returned Books</p>
</div>


</div>



<div class="section">

<h2>Recent Issued Books</h2>


<table>

<tr>
<th>Student</th>
<th>Book</th>
<th>Issue Date</th>
<th>Due Date</th>
<th>Status</th>
</tr>


<?php while($row=mysqli_fetch_assoc($issued)){ ?>

<tr>

<td><?= $row['name'] ?></td>
<td><?= $row['title'] ?></td>
<td><?= $row['issue_date'] ?></td>
<td><?= $row['due_date'] ?></td>
<td><?= $row['status'] ?></td>

</tr>

<?php } ?>


</table>

</div>




<div class="section">

<h2>Recent Returned Books</h2>


<table>

<tr>
<th>Student</th>
<th>Book</th>
<th>Return Date</th>
<th>Fine Paid</th>
</tr>


<?php while($row=mysqli_fetch_assoc($returned)){ ?>

<tr>

<td><?= $row['name'] ?></td>
<td><?= $row['title'] ?></td>
<td><?= $row['return_date'] ?></td>
<td><?= $row['fine_paid'] ?></td>

</tr>

<?php } ?>


</table>


</div>



</div>


</body>

</html>