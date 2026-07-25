<?php
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Add Supplier</title>


<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.css">


<link rel="stylesheet" href="assets/css/admin.css">


<style>

:root{

--sidebar-width:260px;
--dark-blue:#0a2540;
--soft-blue:#f0f4f8;

}


body{

background:var(--soft-blue);
font-family:'Segoe UI',sans-serif;

}


#main-content{

margin-left:var(--sidebar-width);
padding:30px;

}


.text-blue-dark{

color:var(--dark-blue);

}



.card{

border:none;
border-radius:12px;
overflow:hidden;

}



.card-header{

background:#0d6efd !important;
color:white;
padding:16px 20px;
border:none;

}



.card-body{

padding:25px;

}



.form-control{

border-radius:8px;
padding:10px 14px;

}



.form-control:focus{

border-color:#0d6efd;

box-shadow:0 0 0 .2rem rgba(13,110,253,.15);

}



label{

font-weight:600;
color:var(--dark-blue);
margin-bottom:6px;

}



.btn-primary{

border-radius:6px;
padding:8px 18px;

}



.btn-secondary{

border-radius:6px;
padding:8px 18px;

}


</style>


</head>


<body>


<?php include("includes/sidebar.php"); ?>



<div id="main-content">



<!-- HEADER -->

<div class="d-flex justify-content-between align-items-center mb-4">


<h2 class="fw-bold text-blue-dark m-0">

<i class="bi bi-truck"></i>

Add Supplier

</h2>



<a href="suppliers.php" class="btn btn-secondary">

<i class="bi bi-arrow-left"></i>

Back

</a>



</div>





<!-- FORM CARD -->

<div class="card shadow border-0">



<div class="card-header">


<h5 class="mb-0 fw-semibold">

<i class="bi bi-truck me-1"></i>

Supplier Details

</h5>


</div>





<div class="card-body p-4">



<form action="supplier_process.php" method="POST">





<div class="mb-3">


<label class="form-label">

Supplier Name

</label>


<input

type="text"

name="supplier_name"

class="form-control"

placeholder="Enter Supplier Name"

required>


</div>






<div class="mb-3">


<label class="form-label">

Contact Person

</label>


<input

type="text"

name="contact_person"

class="form-control"

placeholder="Enter Contact Person">


</div>






<div class="mb-3">


<label class="form-label">

Phone Number

</label>


<input

type="text"

name="phone"

class="form-control"

placeholder="Enter Phone Number">


</div>






<div class="mb-3">


<label class="form-label">

Email Address

</label>


<input

type="email"

name="email"

class="form-control"

placeholder="Enter Email Address">


</div>






<div class="mb-3">


<label class="form-label">

Address

</label>


<textarea

name="address"

rows="4"

class="form-control"

placeholder="Enter Supplier Address"></textarea>


</div>







<div class="mt-4">


<button

type="submit"

class="btn btn-primary">


<i class="bi bi-save me-1"></i>

Save Supplier


</button>




<a href="suppliers.php"

class="btn btn-secondary ms-2">


<i class="bi bi-x-circle me-1"></i>

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