

<?php


?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Add Category</title>


<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.css">


<!-- COMMON ADMIN CSS -->
<link rel="stylesheet" href="assets/css/admin.css">


</head>


<body>


<?php include("includes/sidebar.php"); ?>



<div id="main-content">

<?php if(isset($_GET['error']) && $_GET['error']=="empty"){ ?>

<div class="alert alert-danger alert-dismissible fade show">
    <i class="bi bi-exclamation-circle-fill"></i>
    Category Name and Description cannot be empty.
    <button class="btn-close" data-bs-dismiss="alert"></button>
</div>

<?php } ?>

<div class="d-flex justify-content-between align-items-center mb-4">


<h2 class="fw-bold text-blue-dark">

<i class="bi bi-tags"></i>

Add New Category

</h2>


<a href="categories.php" class="btn btn-secondary">

<i class="bi bi-arrow-left"></i>

Back

</a>


</div>




<div class="card shadow border-0">


<div class="card-header bg-primary text-white">

<h5 class="mb-0">

<i class="bi bi-plus-circle"></i>

Category Details

</h5>


</div>




<div class="card-body">



<form action="category_process.php" method="POST">



<div class="mb-3">


<label class="form-label fw-semibold">

Category Name

</label>


<input

type="text"

name="category_name"

class="form-control"

placeholder="Enter Category Name"

required>


</div>




<div class="mb-3">


<label class="form-label fw-semibold">

Description

</label>


<textarea
name="description"
rows="5"
class="form-control"
placeholder="Enter category description"
required></textarea>


</div>





<button

type="submit"

class="btn btn-primary">


<i class="bi bi-save"></i>

Save Category


</button>



<a href="categories.php"

class="btn btn-secondary ms-2">


<i class="bi bi-x-circle"></i>

Cancel


</a>




</form>



</div>


</div>



</div>




<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script src="assets/js/darkmode.js"></script>

</body>

</html>