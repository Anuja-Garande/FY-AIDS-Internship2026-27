<?php

$host = "localhost";
$user = "root";
$password = "";
$database = "inventory_management";

$conn = mysqli_connect($host,$user,$password,$database);

if(!$conn){
    die("Connection Failed : ".mysqli_connect_error());
}

$id = $_POST['id'];

$category_name = trim($_POST['category_name']);
$description   = trim($_POST['description']);

/* Validate Empty Fields */

if($category_name == "" || $description == "")
{
    header("Location: edit_category.php?id=".$id."&error=empty");
    exit();
}

/* Prevent Duplicate Category Names */

$check = mysqli_query(
    $conn,
    "SELECT id
     FROM categories
     WHERE category_name='".mysqli_real_escape_string($conn,$category_name)."'
     AND id != '$id'"
);

if(mysqli_num_rows($check) > 0)
{
    header("Location: categories.php?exists=1");
    exit();
}

/* Escape Input */

$category_name = mysqli_real_escape_string($conn,$category_name);
$description   = mysqli_real_escape_string($conn,$description);

/* Update Category */

$sql = "
UPDATE categories
SET
category_name='$category_name',
description='$description'
WHERE id='$id'
";

if(mysqli_query($conn,$sql))
{
    header("Location: categories.php?updated=1");
    exit();
}
else
{
    echo "Error : ".mysqli_error($conn);
}

mysqli_close($conn);

?>