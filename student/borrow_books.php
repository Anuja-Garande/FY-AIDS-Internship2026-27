<?php

error_reporting(E_ALL);
ini_set('display_errors',1);

session_start();

include "../includes/db_connect.php";


$message = "";


// Temporary student id
$student_id = 2;



if(isset($_POST['borrow_book'])){


    $book_id = $_POST['book_id'];



    $check = mysqli_query($conn,

    "SELECT available_quantity 
     FROM books 
     WHERE book_id='$book_id'");



    if(!$check){

        $message = "Error: ".mysqli_error($conn);

    }

    else{


        $book = mysqli_fetch_assoc($check);



        if($book['available_quantity'] > 0){


            $issue_date = date("Y-m-d");

            $due_date = date("Y-m-d", strtotime("+15 days"));



            $insert = mysqli_query($conn,


            "INSERT INTO issued_books

            (student_id, book_id, issue_date, due_date, return_date, status, fine)

            VALUES

            ('$student_id',
             '$book_id',
             '$issue_date',
             '$due_date',
             NULL,
             'Issued',
             '0.00')");




            if($insert){


                mysqli_query($conn,

                "UPDATE books

                 SET available_quantity = available_quantity - 1

                 WHERE book_id='$book_id'");


                $message = "Book Borrowed Successfully";


            }

            else{


                $message = "Insert Error: ".mysqli_error($conn);


            }


        }

        else{


            $message = "Book not available";


        }


    }


}




$books = mysqli_query($conn,


"SELECT books.*, categories.category_name

FROM books

LEFT JOIN categories

ON books.category_id = categories.category_id

WHERE available_quantity > 0

ORDER BY title");



?>



<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">


<title>Borrow Books | Digital Library Management System</title>



<link rel="stylesheet" href="../assets/css/dashboard.css">


<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">


<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">



<style>


.message{

background:#fff;

padding:15px;

margin:20px 0;

border-radius:8px;

color:green;

font-weight:600;

}



table{

width:100%;

margin-top:20px;

border-collapse:collapse;

background:white;

}



table th, table td{

padding:12px;

border:1px solid #ddd;

text-align:center;

}



table th{

background:#2d89ef;

color:white;

}



button{

background:#2d89ef;

color:white;

border:none;

padding:8px 15px;

border-radius:5px;

cursor:pointer;

}



button:hover{

background:#1d6fd6;

}



img{

border-radius:5px;

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



<li>

<a href="search_books.php">

<i class="fa-solid fa-magnifying-glass"></i> Search Books

</a>

</li>



<li class="active">

<a href="borrow_books.php">

<i class="fa-solid fa-book-open-reader"></i> Borrow Books

</a>

</li>



<li>

<a href="borrowed_books.php">

<i class="fa-solid fa-book"></i> Borrowed Books

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


<h1>Borrow Books</h1>


<div class="admin-profile">

Welcome, Komal 👋

</div>


</header>





<?php

if($message!=""){

echo "<div class='message'>$message</div>";

}

?>





<table>


<tr>

<th>ID</th>

<th>Cover</th>

<th>Title</th>

<th>Author</th>

<th>Category</th>

<th>Available</th>

<th>Action</th>


</tr>





<?php while($row=mysqli_fetch_assoc($books)){ ?>



<tr>


<td>

<?php echo $row['book_id']; ?>

</td>




<td>

<img src="../assets/images/books/<?php echo $row['image']; ?>" width="60">

</td>




<td>

<?php echo $row['title']; ?>

</td>




<td>

<?php echo $row['author']; ?>

</td>




<td>

<?php echo $row['category_name']; ?>

</td>




<td>

<?php echo $row['available_quantity']; ?>

</td>




<td>


<form method="POST">


<input type="hidden" 

name="book_id"

value="<?php echo $row['book_id']; ?>">



<button type="submit" name="borrow_book">

Borrow

</button>



</form>



</td>



</tr>



<?php } ?>



</table>




</div>



</body>

</html>