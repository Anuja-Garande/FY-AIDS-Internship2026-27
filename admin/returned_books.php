<?php

session_start();

include "../includes/db_connect.php";


$returned = mysqli_query($conn,

"SELECT
returned_books.return_id,
returned_books.issue_id,
student.name AS student_name,
books.title AS book_title,
returned_books.return_date,
returned_books.fine_paid,
returned_books.remarks

FROM returned_books

LEFT JOIN student
ON returned_books.student_id = student.student_id

LEFT JOIN books
ON returned_books.book_id = books.book_id

ORDER BY returned_books.return_id DESC"

);
?>


<!DOCTYPE html>
<html>

<head>

<title>Returned Books</title>

<style>

body{
    font-family:Arial, sans-serif;
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
    color:#16a34a;
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


<h1>📚 Returned Books</h1>



<?php

if(mysqli_num_rows($returned)>0)

{

?>


<table>


<tr>

<th>Return ID</th>
<th>Student</th>
<th>Book</th>
<th>Return Date</th>
<th>Fine Paid</th>
<th>Status</th>
<th>Remarks</th>

</tr>


<?php

while($row=mysqli_fetch_assoc($returned))

{

?>


<tr>


<td>
<?php echo $row['return_id']; ?>
</td>


<td>
<?php echo $row['student_name']; ?>
</td>


<td>
<?php echo $row['book_title']; ?>
</td>


<td>
<?php echo $row['return_date']; ?>
</td>


<td class="fine">
₹ <?php echo $row['fine_paid']; ?>
</td>


<td>
<span class="status">
Returned
</span>
</td>


<td>
<?php echo $row['remarks']; ?>
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

📚 No returned books found.

</div>


<?php

}

?>


</div>


</body>

</html>