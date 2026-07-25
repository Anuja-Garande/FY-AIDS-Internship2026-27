<?php

$host="localhost";
$user="root";
$password="";
$database="inventory_management";

$conn=mysqli_connect($host,$user,$password,$database);

if(!$conn){
    die("Connection Failed : ".mysqli_connect_error());
}


$customers=mysqli_query($conn,"
SELECT *
FROM customers
ORDER BY customer_name ASC
");


$products=mysqli_query($conn,"
SELECT *
FROM products
ORDER BY product_name ASC
");

?>


<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Add Sale</title>


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

margin-left:260px;
padding:30px;

}


.card{

border:none;
border-radius:15px;

}


.card-header{

border-radius:15px 15px 0 0 !important;

}


.form-control,
.form-select{

border-radius:10px;

}


label{

font-weight:600;

}


</style>

</head>


<body>


<?php include("includes/sidebar.php"); ?>



<div id="main-content">


<!-- PAGE HEADER -->

<div class="d-flex justify-content-between align-items-center mb-4">


<h2 class="fw-bold text-blue-dark m-0">

<i class="bi bi-cash-coin"></i>

Add Sale

</h2>



<a href="sales.php" class="btn btn-secondary">

<i class="bi bi-arrow-left"></i>

Back

</a>


</div>





<!-- FORM CARD -->

<div class="card shadow border-0">



<div class="card-header bg-primary text-white">


<h4 class="mb-0">

<i class="bi bi-cash-coin"></i>

Add New Sale

</h4>


</div>




<div class="card-body p-4">


<form action="sale_process.php" method="POST">





<div class="mb-3">


<label class="form-label">

Customer

</label>



<select

name="customer_id"

class="form-select"

required>


<option value="">

-- Select Customer --

</option>



<?php

while($customer=mysqli_fetch_assoc($customers))

{

?>


<option value="<?php echo $customer['id']; ?>">

<?php echo htmlspecialchars($customer['customer_name']); ?>

</option>


<?php

}

?>


</select>


</div>






<div class="mb-3">


<label class="form-label">

Product

</label>



<select

name="product_id"

id="product"

class="form-select"

required>


<option value="">

-- Select Product --

</option>



<?php

while($product=mysqli_fetch_assoc($products))

{

?>


<option

value="<?php echo $product['id']; ?>"

data-price="<?php echo $product['selling_price']; ?>"

data-stock="<?php echo $product['quantity']; ?>"

>


<?php echo htmlspecialchars($product['product_name']); ?>

(Stock : <?php echo $product['quantity']; ?>)


</option>


<?php

}

?>


</select>


</div>






<div class="row">



<div class="col-md-6 mb-3">


<label class="form-label">

Available Stock

</label>


<input

type="text"

id="stock"

class="form-control"

readonly>


</div>





<div class="col-md-6 mb-3">


<label class="form-label">

Selling Price

</label>


<input

type="number"

step="0.01"

name="selling_price"

id="price"

class="form-control"

readonly>


</div>


</div>







<div class="row">



<div class="col-md-6 mb-3">


<label class="form-label">

Quantity

</label>


<input

type="number"

name="quantity"

id="quantity"

class="form-control"

min="1"

placeholder="Enter Quantity"

required>


</div>






<div class="col-md-6 mb-3">


<label class="form-label">

Total Price

</label>


<input

type="text"

id="total_display"

class="form-control"

readonly>



<input

type="hidden"

name="total_price"

id="total_price">


</div>


</div>






<div class="mb-3">


<label class="form-label">

Sale Date

</label>


<input

type="date"

name="sale_date"

class="form-control"

value="<?php echo date('Y-m-d'); ?>"

required>


</div>







<div class="mt-4">


<button

type="submit"

class="btn btn-primary">


<i class="bi bi-save"></i>

Save Sale


</button>



<a

href="sales.php"

class="btn btn-secondary ms-2">


<i class="bi bi-x-circle"></i>

Cancel


</a>


</div>




</form>


</div>


</div>



</div>







<script>


const product=document.getElementById("product");

const price=document.getElementById("price");

const stock=document.getElementById("stock");

const qty=document.getElementById("quantity");

const total=document.getElementById("total_display");

const hidden=document.getElementById("total_price");



function calculateSale(){


let option=product.options[product.selectedIndex];


let selling=parseFloat(option.dataset.price)||0;


let available=parseInt(option.dataset.stock)||0;


let quantity=parseInt(qty.value)||0;



price.value=selling.toFixed(2);


stock.value=available;



let amount=selling*quantity;


total.value=amount.toFixed(2);


hidden.value=amount.toFixed(2);


}



product.addEventListener("change",calculateSale);

qty.addEventListener("keyup",calculateSale);

qty.addEventListener("change",calculateSale);



</script>




<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script src="assets/js/darkmode.js"></script>

</body>

</html>


<?php

mysqli_close($conn);

?>