<?php
session_start();

if(!isset($_SESSION['username'])){
    header("Location: login.php");
    exit();
}

$host = "localhost";
$user = "root";
$password = "";
$database = "inventory_management";

$conn = mysqli_connect($host, $user, $password, $database);

if(!$conn){
    die("Connection Failed: " . mysqli_connect_error());
}

if(!isset($_GET['id'])){
    header("Location: suppliers.php");
    exit();
}

$id = intval($_GET['id']);

$result = mysqli_query($conn,"SELECT * FROM suppliers WHERE id='$id'");

if(mysqli_num_rows($result)==0){
    header("Location: suppliers.php");
    exit();
}

$row = mysqli_fetch_assoc($result);
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Edit Supplier</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
<link rel="stylesheet" href="assets/css/admin.css">

</head>

<body>

<?php include("includes/sidebar.php"); ?>

<div id="main-content">

<div class="d-flex justify-content-between align-items-center mb-4">

<h2 class="page-title mb-0">
<i class="bi bi-truck"></i>
Edit Supplier
</h2>

<a href="suppliers.php" class="btn btn-secondary">
<i class="bi bi-arrow-left"></i>
Back
</a>

</div>

<div class="card table-container shadow">

<div class="card-header">

<h5 class="mb-0">
<i class="bi bi-pencil-square text-primary"></i>
Update Supplier Information
</h5>

</div>

<div class="card-body">

<form action="update_supplier.php" method="POST">

<input type="hidden" name="id" value="<?php echo $row['id']; ?>">

<div class="row">

<div class="col-md-6 mb-3">
<label class="form-label">Supplier Name</label>
<input
type="text"
name="supplier_name"
class="form-control"
value="<?php echo htmlspecialchars($row['supplier_name']); ?>"
required>
</div>

<div class="col-md-6 mb-3">
<label class="form-label">Contact Person</label>
<input
type="text"
name="contact_person"
class="form-control"
value="<?php echo htmlspecialchars($row['contact_person']); ?>"
required>
</div>

<div class="col-md-6 mb-3">
<label class="form-label">Phone Number</label>
<input
type="text"
name="phone"
class="form-control"
value="<?php echo htmlspecialchars($row['phone']); ?>"
required>
</div>

<div class="col-md-6 mb-3">
<label class="form-label">Email Address</label>
<input
type="email"
name="email"
class="form-control"
value="<?php echo htmlspecialchars($row['email']); ?>"
required>
</div>

<div class="col-12 mb-3">
<label class="form-label">Address</label>
<textarea
name="address"
rows="4"
class="form-control"
required><?php echo htmlspecialchars($row['address']); ?></textarea>
</div>

<div class="col-12 d-flex gap-2">

<button type="submit" class="btn btn-primary">
<i class="bi bi-check-circle"></i>
Update Supplier
</button>

<a href="suppliers.php" class="btn btn-secondary">
Cancel
</a>

</div>

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