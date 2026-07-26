<?php
session_start();

if(!isset($_SESSION['username']))
{
    header("Location: login.php");
    exit();
}

if(!isset($_SESSION['role']))
{
    header("Location: login.php");
    exit();
}

if($_SESSION['role'] != "Admin")
{
    header("Location: user_dashboard.php");
    exit();
}
?>

<?php

$conn=mysqli_connect(
"localhost",
"root",
"",
"inventory_management"
);


if(!$conn){

    die("Database Connection Failed");

}



// ================= COUNTS =================


$product_count=mysqli_fetch_assoc(
mysqli_query($conn,"
SELECT COUNT(*) total 
FROM products
")
)['total'];



$category_count=mysqli_fetch_assoc(
mysqli_query($conn,"
SELECT COUNT(*) total 
FROM categories
")
)['total'];



$supplier_count=mysqli_fetch_assoc(
mysqli_query($conn,"
SELECT COUNT(*) total 
FROM suppliers
")
)['total'];



$customer_count=mysqli_fetch_assoc(
mysqli_query($conn,"
SELECT COUNT(*) total 
FROM customers
")
)['total'];




// TODAY PURCHASE


$purchase_total=mysqli_fetch_assoc(
mysqli_query($conn,"
SELECT IFNULL(SUM(total_price),0) total
FROM purchases
WHERE DATE(created_at)=CURDATE()
")
)['total'];




// TODAY SALES


$sales_total=mysqli_fetch_assoc(
mysqli_query($conn,"
SELECT IFNULL(SUM(total_price),0) total
FROM sales
WHERE DATE(created_at)=CURDATE()
")
)['total'];




// CURRENT STOCK


$current_stock=mysqli_fetch_assoc(
mysqli_query($conn,"
SELECT IFNULL(SUM(quantity),0) total
FROM products
")
)['total'];




// LOW STOCK COUNT


$low_stock_count=mysqli_fetch_assoc(
mysqli_query($conn,"
SELECT COUNT(*) total
FROM products
WHERE quantity<=5
")
)['total'];




// LOW STOCK PRODUCTS


$low_stock_products=mysqli_query($conn,"
SELECT product_name,sku,quantity

FROM products

WHERE quantity<=5

ORDER BY quantity ASC

LIMIT 5

");




// RECENT OPERATIONS


$recent_operations=mysqli_query($conn,"
(
SELECT

id,
'Sale' type,
total_price,
created_at

FROM sales
)


UNION ALL


(
SELECT

id,
'Purchase' type,
total_price,
created_at

FROM purchases

)


ORDER BY created_at DESC

LIMIT 10

");


?>



<!DOCTYPE html>

<html lang="en">

<head>


<meta charset="UTF-8">


<meta name="viewport" content="width=device-width, initial-scale=1.0">



<title>Dashboard</title>



<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">



<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">



<link rel="stylesheet" href="assets/css/admin.css">



</head>



<body>



<?php include("includes/sidebar.php"); ?>



<div id="main-content">



<!-- HEADER -->


<div class="d-flex justify-content-between align-items-center mb-4">


<h2 class="page-title">


<i class="bi bi-speedometer2"></i>

Dashboard Overview


</h2>



<button class="btn btn-outline-primary"
onclick="window.print()">


<i class="bi bi-printer"></i>

Print Summary


</button>


</div>





<!-- DASHBOARD CARDS -->

<?php if($_SESSION['role']=="Admin"){ ?>

<div class="row g-4 mb-4">



<div class="col-md-3">

<div class="card card-counter shadow-sm p-3 border-start border-primary border-4">


<h6>Total Products</h6>


<h3 class="fw-bold">

<?php echo $product_count; ?>

</h3>


<i class="bi bi-box fs-2 text-primary"></i>


</div>

</div>






<div class="col-md-3">


<div class="card card-counter shadow-sm p-3 border-start border-primary border-4">


<h6>Total Categories</h6>


<h3 class="fw-bold">

<?php echo $category_count; ?>

</h3>


<i class="bi bi-tags fs-2 text-primary"></i>


</div>


</div>







<div class="col-md-3">


<div class="card card-counter shadow-sm p-3 border-start border-primary border-4">


<h6>Total Suppliers</h6>


<h3 class="fw-bold">

<?php echo $supplier_count; ?>

</h3>


<i class="bi bi-truck fs-2 text-primary"></i>


</div>


</div>







<div class="col-md-3">


<div class="card card-counter shadow-sm p-3 border-start border-primary border-4">


<h6>Total Customers</h6>


<h3 class="fw-bold">

<?php echo $customer_count; ?>

</h3>


<i class="bi bi-people fs-2 text-primary"></i>


</div>


</div>





<div class="col-md-3">


<div class="card card-counter shadow-sm p-3 border-start border-info border-4">


<h6>Today's Purchase</h6>


<h3>

₹<?php echo number_format($purchase_total,2); ?>

</h3>


<i class="bi bi-cart-plus fs-2 text-info"></i>


</div>


</div>





<div class="col-md-3">


<div class="card card-counter shadow-sm p-3 border-start border-success border-4">


<h6>Today's Sales</h6>


<h3>

₹<?php echo number_format($sales_total,2); ?>

</h3>


<i class="bi bi-cash-coin fs-2 text-success"></i>


</div>


</div>





<div class="col-md-3">


<div class="card card-counter shadow-sm p-3 border-start border-warning border-4">


<h6>Current Stock</h6>


<h3>

<?php echo $current_stock; ?>

Units

</h3>


<i class="bi bi-boxes fs-2 text-warning"></i>


</div>


</div>





<div class="col-md-3">


<div class="card low-stock-alert-card low-stock-pulse shadow-sm p-3">


<h6>Low Stock</h6>


<h3>

<?php echo $low_stock_count; ?>

Items

</h3>


<i class="bi bi-exclamation-triangle fs-2"></i>


</div>


</div>



</div>
<?php } ?>

<!-- RECENT ACTIVITY + LOW STOCK -->

<div class="row g-4">


<!-- RECENT OPERATIONS -->

<div class="col-lg-8">


<div class="card shadow-sm border-0">


<div class="card-header bg-white">

<h5 class="fw-bold mb-0">

<i class="bi bi-clock-history text-primary"></i>

Recent Activity

</h5>

</div>



<div class="card-body p-0">


<div class="table-responsive">


<table class="table table-hover align-middle mb-0">


<thead class="table-light">


<tr>

<th>ID</th>

<th>Type</th>

<th>Date</th>

<th>Amount</th>

</tr>


</thead>



<tbody>



<?php


if(mysqli_num_rows($recent_operations)>0)

{


while($row=mysqli_fetch_assoc($recent_operations))

{


if($row['type']=="Sale"){

$badge="success";

$prefix="SALE";

}

else{

$badge="primary";

$prefix="PUR";

}


?>


<tr>


<td>

<strong>

<?php echo $prefix."-".$row['id']; ?>

</strong>


</td>




<td>


<span class="badge bg-<?php echo $badge; ?>">


<?php echo $row['type']; ?>


</span>


</td>




<td>


<?php echo date(
"d M Y",
strtotime($row['created_at'])
); ?>


</td>




<td>


₹<?php echo number_format(
$row['total_price'],
2
); ?>


</td>



</tr>



<?php

}

}

else

{


?>

<tr>


<td colspan="4" class="text-center py-4">


No Recent Operations Found


</td>


</tr>



<?php

}

?>


</tbody>



</table>



</div>


</div>


</div>


</div>

















<div class="card-body">



<?php


if(mysqli_num_rows($low_stock_products)>0)

{


while($row=mysqli_fetch_assoc($low_stock_products))

{


?>


<div class="d-flex justify-content-between align-items-center border-bottom py-3">


<div>


<h6 class="mb-1 fw-bold">


<?php echo htmlspecialchars($row['product_name']); ?>


</h6>



<small class="text-muted">


SKU:

<?php echo htmlspecialchars($row['sku']); ?>


</small>



</div>





<span class="badge bg-danger rounded-pill">


<?php echo $row['quantity']; ?>


Left


</span>



</div>



<?php


}

}


else

{


?>


<div class="text-center text-muted py-4">


<i class="bi bi-check-circle fs-3"></i>


<p class="mb-0">

No Low Stock Products

</p>


</div>



<?php


}

?>



</div>


</div>


</div>



</div>






</div>







<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script src="assets/js/darkmode.js"></script>

</body>


</html>

