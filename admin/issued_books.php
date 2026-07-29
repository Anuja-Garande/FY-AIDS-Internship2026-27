<?php

session_start();

include "../includes/db_connect.php";


$issued = mysqli_query($conn,

"SELECT
issued_books.issue_id,
student.name AS student_name,
books.title AS book_title,
issued_books.issue_date,
issued_books.due_date,
issued_books.status,
issued_books.fine

FROM issued_books

LEFT JOIN student
ON issued_books.student_id = student.student_id

LEFT JOIN books
ON issued_books.book_id = books.book_id

WHERE issued_books.status='Issued'

ORDER BY issued_books.issue_id DESC"

);

?>


<!DOCTYPE html>
<html>

<head>

<title>Issued Books</title>

<style>

body{
    font-family: Arial, sans-serif;
    background:#f4f7fc;
}

.container{
    width:90%;
    margin:40px auto;
}

h1{
    color:#1E3A8A;
    margin-bottom:20px;
}

table{
    width:100%;
    background:white;
    border-collapse:collapse;
    border-radius:12px;
    overflow:hidden;
    box-shadow:0 5px 15px rgba(0,0,0,0.1);
}

th{
    background:#1E3A8A;
    color:white;
    padding:14px;
}

td{
    padding:12px;
    text-align:center;
    border-bottom:1px solid #eee;
}


tr:hover{
    background:#f1f5ff;
}


.status{
    background:#dcfce7;
    color:#166534;
    padding:6px 12px;
    border-radius:20px;
    font-size:14px;
}


.fine{
    color:#dc2626;
    font-weight:bold;
}


.empty{
    background:white;
    padding:30px;
    text-align:center;
    margin-top:20px;
    border-radius:10px;
}


</style>

</head>


<body>


<div class="container">


<h1>📚 Issued Books</h1>



<?php

if(mysqli_num_rows($issued)>0)

{

?>


<table>


<tr>

<th>ID</th>
<th>Student</th>
<th>Book</th>
<th>Issue Date</th>
<th>Due Date</th>
<th>Status</th>
<th>Fine</th>

</tr>


<?php

while($row=mysqli_fetch_assoc($issued))

{

?>


<tr>

<td><?php echo $row['issue_id']; ?></td>

<td><?php echo $row['student_name']; ?></td>

<td><?php echo $row['book_title']; ?></td>

<td><?php echo $row['issue_date']; ?></td>

<td><?php echo $row['due_date']; ?></td>


<td>
<span class="status">
<?php echo $row['status']; ?>
</span>
</td>


<td class="fine">
₹ <?php echo $row['fine']; ?>
</td>


</tr>


<?php

}

?>


</table>


<?php

}

else

{

?>

<div class="empty">

📚 No books are currently issued.

</div>


<?php

}

?>


</div>


</body>

</html>