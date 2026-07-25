<?php

$conn = mysqli_connect(
    "localhost",
    "root",
    "",
    "inventory_management"
);


if(!$conn){
    die("Database Connection Failed");
}


$search = "";


if(isset($_GET['search'])){

    $search = mysqli_real_escape_string(
        $conn,
        $_GET['search']
    );


    $sql = "
    SELECT * FROM products
    WHERE product_name LIKE '%$search%'
    OR category LIKE '%$search%'
    OR supplier LIKE '%$search%'
    ORDER BY id DESC
    ";


}
else{

    $sql = "
    SELECT * FROM products
    ORDER BY id DESC
    ";

}


$result = mysqli_query($conn,$sql);

?>

<?php

session_start();


if(!isset($_SESSION['username']))
{
    header("Location: login.php");
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">


<title>Product Management</title>


<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">


<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">


<link rel="stylesheet" href="assets/css/admin.css">


</head>



<body>


<?php include("includes/sidebar.php"); ?>



<div id="main-content">



<!-- ALERTS -->



<div class="alert alert-success alert-dismissible fade show">

<i class="bi bi-check-circle"></i>

Product Added Successfully

<button class="btn-close" data-bs-dismiss="alert"></button>

</div>





<?php if(isset($_GET['updated'])){ ?>

<div class="alert alert-primary alert-dismissible fade show">

<i class="bi bi-pencil"></i>

Product Updated Successfully

<button class="btn-close" data-bs-dismiss="alert"></button>

</div>

<?php } ?>




<?php if(isset($_GET['deleted'])){ ?>

<div class="alert alert-danger alert-dismissible fade show">

<i class="bi bi-trash"></i>

Product Deleted Successfully

<button class="btn-close" data-bs-dismiss="alert"></button>

</div>

<?php } ?>




<!-- HEADER -->


<div class="d-flex justify-content-between align-items-center mb-4">


<h2 class="page-title mb-0">

<i class="bi bi-box"></i>

Product Management

</h2>




<a href="add_product.php" class="btn btn-primary">

<i class="bi bi-plus-circle"></i>

Add Product

</a>


</div>



<!-- TABLE -->


<div class="table-container shadow">



<form method="GET">


<div class="input-group mb-4 search-box">


<input 
type="text"
name="search"
class="form-control"
placeholder="Search Product..."
value="<?php echo $search; ?>"
>


<button class="btn btn-primary">

<i class="bi bi-search"></i>

</button>


</div>


</form>






<div class="table-responsive">


<table class="table table-bordered table-hover align-middle">


<thead>


<tr>

<th>ID</th>

<th>Product Name</th>

<th>Category</th>

<th>Supplier</th>

<th>SKU</th>

<th>Cost Price</th>

<th>Selling Price</th>

<th>Quantity</th>

<th>Unit</th>

<th width="160">Action</th>


</tr>


</thead>




<tbody>



<?php


if(mysqli_num_rows($result)>0){


while($row=mysqli_fetch_assoc($result)){


?>


<tr>


<td>
<?php echo $row['id']; ?>
</td>



<td>
<?php echo htmlspecialchars($row['product_name']); ?>
</td>



<td>
<?php echo htmlspecialchars($row['category']); ?>
</td>



<td>
<?php echo htmlspecialchars($row['supplier']); ?>
</td>



<td>
<?php echo htmlspecialchars($row['sku']); ?>
</td>



<td>
₹<?php echo number_format($row['cost_price'],2); ?>
</td>



<td>
₹<?php echo number_format($row['selling_price'],2); ?>
</td>



<td>
<?php echo $row['quantity']; ?>
</td>



<td>
<?php echo htmlspecialchars($row['unit']); ?>
</td>




<td>

    <a href="edit_product.php?id=<?php echo $row['id']; ?>"
       class="btn btn-success btn-sm"
       title="Edit Product">

        <i class="bi bi-pencil"></i>

    </a>

    <a href="delete_product.php?id=<?php echo $row['id']; ?>"
       class="btn btn-danger btn-sm"
       title="Delete Product"
       onclick="return confirm('Are you sure you want to delete this product?');">

        <i class="bi bi-trash"></i>

    </a>

</td>


</tr>



<?php

}

}

else{


?>


<tr>


<td colspan="10" class="text-center py-5">


<i class="bi bi-box display-4"></i>


<h5 class="mt-3">

No Products Found

</h5>


<p>

Add your first product.

</p>


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




<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script src="assets/js/darkmode.js"></script>
</body>

</html>