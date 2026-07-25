<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Add Customer</title>


<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.css">


<!-- COMMON ADMIN CSS -->
<link rel="stylesheet" href="assets/css/admin.css">


</head>


<body>


<?php include("includes/sidebar.php"); ?>



<div id="main-content">



<div class="d-flex justify-content-between align-items-center mb-4">


<h2 class="fw-bold text-blue-dark">

<i class="bi bi-people-fill"></i>

Add Customer

</h2>



<a href="customers.php" class="btn btn-secondary">

<i class="bi bi-arrow-left"></i>

Back

</a>



</div>





<div class="card shadow border-0">


<div class="card-header bg-primary text-white">


<h5 class="mb-0">

<i class="bi bi-person-plus"></i>

Customer Details

</h5>


</div>





<div class="card-body">



<form action="customer_process.php" method="POST">



<div class="mb-3">

<label class="form-label fw-semibold">

Customer Name

</label>


<input

type="text"

name="customer_name"

class="form-control"

placeholder="Enter Customer Name"

required>


</div>





<div class="mb-3">


<label class="form-label fw-semibold">

Phone Number

</label>


<input

type="text"

name="phone"

class="form-control"

placeholder="Enter Phone Number">


</div>





<div class="mb-3">


<label class="form-label fw-semibold">

Email Address

</label>


<input

type="email"

name="email"

class="form-control"

placeholder="Enter Email Address">


</div>





<div class="mb-3">


<label class="form-label fw-semibold">

Address

</label>


<textarea

name="address"

rows="4"

class="form-control"

placeholder="Enter Address"></textarea>


</div>






<button type="submit" class="btn btn-primary">


<i class="bi bi-save"></i>

Save Customer


</button>



<a href="customers.php" class="btn btn-secondary ms-2">


<i class="bi bi-x-circle"></i>

Cancel


</a>




</form>



</div>


</div>



</div>




<script src="https://cdn.jsdelivr.net/npm/bootstrap 5.3.2/dist/js/bootstrap.bundle.min.js"></script>


</body>

<script src="assets/js/darkmode.js"></script>

</html>