<?php
include 'includes/db_connect.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Books | Digital Library Management System</title>

<link rel="stylesheet" href="assets/css/style.css">

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">

</head>

<body>

<!-- ================= NAVBAR ================= -->

<nav>

    <div class="logo">

        <img src="assets/images/logo/logo.jpg" alt="College Logo">

        <div>

            <h2>ZEAL EDUCATION SOCIETY</h2>

            <p>Digital Library Management System</p>

        </div>

    </div>

    <ul>

        <li><a href="index.php">Home</a></li>

        <li><a href="about.php">About</a></li>

        <li><a href="books.php" class="active">Books</a></li>

        <li><a href="contact.php">Contact</a></li>

        <li><a href="login.php" class="login-btn">Login</a></li>

    </ul>

</nav>

<!-- ================= HERO ================= -->

<section class="hero">

<div class="overlay">

<h1>Library Books</h1>

<p>Browse books available in our Digital Library.</p>

</div>

</section>

<!-- ================= BOOKS ================= -->

<section class="featured">

<h2>Available Books</h2>

<div class="book-container">

<?php

$result = mysqli_query($conn,"SELECT * FROM books");

while($row = mysqli_fetch_assoc($result))
{

?>

<div class="book">

<img src="assets/images/books/<?php echo $row['image']; ?>" alt="Book">

<h3><?php echo $row['title']; ?></h3>

<p><strong>Author:</strong> <?php echo $row['author']; ?></p>

<p><strong>Publisher:</strong> <?php echo $row['publisher']; ?></p>

<p><strong>Available Quantity:</strong> <?php echo $row['available_quantity']; ?></p>

</div>

<?php

}

?>

</div>

</section>

<!-- ================= FOOTER ================= -->

<footer>

<p>© 2026 Zeal Education Society | Digital Library Management System</p>

</footer>

</body>

</html>