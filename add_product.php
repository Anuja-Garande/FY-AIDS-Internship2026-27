<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$conn = mysqli_connect(
    "localhost",
    "root",
    "",
    "inventory_management"
);

if (!$conn) {
    die("Database Connection Failed");
}

/* Load Categories */
$categories = mysqli_query($conn,"
SELECT *
FROM categories
ORDER BY category_name ASC
");

/* Load Suppliers */
$suppliers = mysqli_query($conn,"
SELECT *
FROM suppliers
ORDER BY supplier_name ASC
");
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Add Product</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

<link rel="stylesheet" href="assets/css/admin.css">

<style>

:root{
    --dark-blue:#0a2540;
}

body{
    font-family:'Segoe UI',sans-serif;
}

#main-content{
    margin-left:260px;
    padding:30px;
}

.card{
    border:none;
    border-radius:14px;
}

.card-header{
    background:#0d6efd;
    color:#fff;
    padding:16px 22px;
}

.form-control,
.form-select{
    border-radius:8px;
}

label{
    font-weight:600;
    margin-bottom:6px;
}

</style>

</head>

<body>

<?php include("includes/sidebar.php"); ?>

<div id="main-content">

<div class="d-flex justify-content-between align-items-center mb-4">

<h2 class="fw-bold">

<i class="bi bi-box-seam"></i>

Add Product

</h2>

<a href="products.php" class="btn btn-secondary">

<i class="bi bi-arrow-left"></i>

Back

</a>

</div>

<div class="card shadow">

<div class="card-header">

<h5 class="mb-0">

<i class="bi bi-plus-circle"></i>

Product Details

</h5>

</div>

<div class="card-body p-4">

<form action="product_process.php" method="POST">

<div class="mb-3">

<label>Product Name</label>

<input
type="text"
name="product_name"
class="form-control"
required>

</div>

<div class="row">

<div class="col-md-6 mb-3">

<label>Category</label>

<select
name="category"
class="form-select"
required>

<option value="">Select Category</option>

<?php while($cat=mysqli_fetch_assoc($categories)){ ?>

<option value="<?php echo htmlspecialchars($cat['category_name']); ?>">

<?php echo htmlspecialchars($cat['category_name']); ?>

</option>

<?php } ?>

</select>

</div>

<div class="col-md-6 mb-3">

<label>Supplier</label>

<select
name="supplier"
class="form-select"
required>

<option value="">Select Supplier</option>

<?php while($sup=mysqli_fetch_assoc($suppliers)){ ?>

<option value="<?php echo htmlspecialchars($sup['supplier_name']); ?>">

<?php echo htmlspecialchars($sup['supplier_name']); ?>

</option>

<?php } ?>

</select>

</div>

</div>

<div class="row">

<div class="col-md-4 mb-3">

<label>SKU</label>

<input
type="text"
name="sku"
class="form-control">

</div>

<div class="col-md-4 mb-3">

<label>Cost Price</label>

<input
type="number"
step="0.01"
name="cost_price"
class="form-control"
required>

</div>

<div class="col-md-4 mb-3">

<label>Selling Price</label>

<input
type="number"
step="0.01"
name="selling_price"
class="form-control"
required>

</div>

</div>

<div class="row">

<div class="col-md-6 mb-3">

<label>Quantity</label>

<input
type="number"
name="quantity"
class="form-control"
required>

</div>

<div class="col-md-6 mb-3">

<label>Unit</label>

<input
type="text"
name="unit"
class="form-control"
placeholder="pcs, box, kg, litre">

</div>

</div>

<div class="mb-3">

<label>Description</label>

<textarea
name="description"
rows="4"
class="form-control"></textarea>

</div>

<div class="mt-4">

<button
type="submit"
class="btn btn-primary">

<i class="bi bi-save"></i>

Save Product

</button>

<a
href="products.php"
class="btn btn-secondary ms-2">

Cancel

</a>

</div>

</form>

</div>

</div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script src="assets/js/darkmode.js"></script>

</body>
</html>

<?php
mysqli_close($conn);
?>