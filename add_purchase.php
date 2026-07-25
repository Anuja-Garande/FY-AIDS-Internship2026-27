<?php

$host = "localhost";
$user = "root";
$password = "";
$database = "inventory_management";


$conn = mysqli_connect($host,$user,$password,$database);


if(!$conn)
{
    die("Connection Failed : ".mysqli_connect_error());
}


$suppliers = mysqli_query($conn,
"SELECT * FROM suppliers ORDER BY supplier_name ASC"
);


$products = mysqli_query($conn,
"SELECT * FROM products ORDER BY product_name ASC"
);

?>

<!DOCTYPE html>
<html lang="en">

<head>


<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">


<title>Add Purchase</title>



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


<i class="bi bi-cart-plus"></i>


Add Purchase


</h2>



<a href="purchases.php" class="btn btn-secondary">


<i class="bi bi-arrow-left"></i>


Back


</a>



</div>






<div class="card shadow border-0">



<div class="card-header bg-primary text-white">


<h5 class="mb-0">


<i class="bi bi-cart-plus"></i>


Purchase Details


</h5>


</div>





<div class="card-body">



<form action="purchase_process.php" method="POST">





<div class="mb-3">


<label class="form-label fw-semibold">

Supplier

</label>



<select name="supplier_id" class="form-select" required>


<option value="">

-- Select Supplier --

</option>



<?php

while($supplier=mysqli_fetch_assoc($suppliers))

{

?>

<option value="<?php echo $supplier['id']; ?>">


<?php echo htmlspecialchars($supplier['supplier_name']); ?>


</option>


<?php

}

?>


</select>



</div>







<div class="mb-3">


<label class="form-label fw-semibold">


Product


</label>



<select name="product_id" class="form-select" required>



<option value="">


-- Select Product --

</option>



<?php

while($product=mysqli_fetch_assoc($products))

{

?>


<option value="<?php echo $product['id']; ?>">


<?php echo htmlspecialchars($product['product_name']); ?>


</option>



<?php

}

?>


</select>


</div>







<div class="row">



<div class="col-md-6 mb-3">


<label class="form-label fw-semibold">

Quantity

</label>



<input

type="number"

name="quantity"

id="quantity"

class="form-control"

min="1"

required

onkeyup="calculateTotal()"

onchange="calculateTotal()">


</div>





<div class="col-md-6 mb-3">


<label class="form-label fw-semibold">


Purchase Price (Per Unit)


</label>



<input

type="number"

step="0.01"

name="purchase_price"

id="purchase_price"

class="form-control"

min="0"

required

onkeyup="calculateTotal()"

onchange="calculateTotal()">



</div>



</div>







<div class="row">



<div class="col-md-6 mb-3">


<label class="form-label fw-semibold">


Total Price


</label>



<input

type="text"

id="total_price_display"

class="form-control"

readonly

value="0.00">



<input

type="hidden"

name="total_price"

id="total_price">



</div>






<div class="col-md-6 mb-3">


<label class="form-label fw-semibold">


Purchase Date


</label>



<input

type="date"

name="purchase_date"

class="form-control"

value="<?php echo date('Y-m-d'); ?>"

required>



</div>



</div>







<div class="mt-3">



<button type="submit" class="btn btn-primary">


<i class="bi bi-save"></i>


Save Purchase


</button>




<a href="purchases.php" class="btn btn-secondary ms-2">


<i class="bi bi-x-circle"></i>


Cancel


</a>



</div>





</form>




</div>


</div>





</div>







<script>


function calculateTotal()

{


let qty=document.getElementById("quantity").value;


let price=document.getElementById("purchase_price").value;



qty=parseFloat(qty)||0;


price=parseFloat(price)||0;



let total=qty*price;



document.getElementById("total_price_display").value=total.toFixed(2);


document.getElementById("total_price").value=total.toFixed(2);



}



</script>





<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>


<script src="assets/js/darkmode.js"></script>

</body>


</html>


<?php

mysqli_close($conn);

?>