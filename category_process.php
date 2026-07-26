

<?php

$conn = mysqli_connect("localhost","root","","inventory_management");

if(!$conn){
    die("Connection Failed : ".mysqli_connect_error());
}

$category_name = trim($_POST['category_name']);
$description   = trim($_POST['description']);

if($category_name=="" || $description=="")
{
    header("Location:add_category.php?error=empty");
    exit();
}

// Database Connection
$host = "localhost";
$user = "root";
$password = "";
$database = "inventory_management";

$conn = mysqli_connect($host, $user, $password, $database);

if (!$conn) {
    die("Connection Failed: " . mysqli_connect_error());
}

// Check Form Submission
if (isset($_POST['category_name'])) {

    $category_name = mysqli_real_escape_string($conn, $_POST['category_name']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);

    // Check if category already exists
    $check = "SELECT * FROM categories WHERE category_name='$category_name'";
    $result = mysqli_query($conn, $check);

    if (mysqli_num_rows($result) > 0) {
        header("Location: add_category.php?exists=1");
        exit();
    }

    $sql = "INSERT INTO categories(category_name, description)
            VALUES('$category_name', '$description')";

    if (mysqli_query($conn, $sql)) {
        header("Location: categories.php?success=1");
        exit();
    } else {
        echo "Error: " . mysqli_error($conn);
    }

} else {
    header("Location: add_category.php");
}

mysqli_close($conn);

?>