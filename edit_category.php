<?php if(isset($_GET['error']) && $_GET['error']=="empty"){ ?>

<div class="alert alert-danger alert-dismissible fade show">

    <i class="bi bi-exclamation-circle-fill"></i>

    Category Name and Description cannot be empty.

    <button class="btn-close" data-bs-dismiss="alert"></button>

</div>

<?php } ?>

<?php

$host = "localhost";
$user = "root";
$password = "";
$database = "inventory_management";

$conn = mysqli_connect($host,$user,$password,$database);

if(!$conn){
    die("Connection Failed : ".mysqli_connect_error());
}

if(!isset($_GET['id'])){
    header("Location: categories.php");
    exit();
}

$id = $_GET['id'];

$sql = "SELECT * FROM categories WHERE id='$id'";
$result = mysqli_query($conn,$sql);

if(mysqli_num_rows($result)==0){
    header("Location: categories.php");
    exit();
}

$row = mysqli_fetch_assoc($result);

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Edit Category</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

<link rel="stylesheet" href="assets/css/admin.css">

</head>

<body>

<?php include("includes/sidebar.php"); ?>

<div id="main-content">

    <!-- PAGE HEADER -->

    <div class="d-flex justify-content-between align-items-center mb-4">

        <h2 class="page-title mb-0">
            <i class="bi bi-tags"></i>
            Edit Category
        </h2>

        <a href="categories.php" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i>
            Back
        </a>

    </div>

    <!-- FORM CARD -->

    <div class="table-container shadow">

        <form action="update_category.php" method="POST">

            <input
                type="hidden"
                name="id"
                value="<?php echo $row['id']; ?>">

            <div class="mb-4">

                <label class="form-label fw-semibold">
                    Category Name
                </label>

                <input
                    type="text"
                    name="category_name"
                    class="form-control"
                    value="<?php echo htmlspecialchars($row['category_name']); ?>"
                    required>

            </div>

            <div class="mb-4">

                <label class="form-label fw-semibold">
                    Description
                </label>

                <textarea
    name="description"
    rows="6"
    class="form-control"
    placeholder="Enter category description"
    required><?php echo htmlspecialchars($row['description']); ?></textarea>

            </div>

            <div class="d-flex gap-2">

                <button
                    type="submit"
                    class="btn btn-primary">

                    <i class="bi bi-pencil-square"></i>
                    Update Category

                </button>

                <a
                    href="categories.php"
                    class="btn btn-secondary">

                    Cancel

                </a>

            </div>

        </form>

    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/darkmode.js"></script>

</body>

</html>