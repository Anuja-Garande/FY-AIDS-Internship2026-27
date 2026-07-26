<?php

$host="localhost";
$user="root";
$password="";
$database="inventory_management";

$conn=mysqli_connect($host,$user,$password,$database);

if(!$conn){
    die(mysqli_connect_error());
}

if(!isset($_GET['id'])){
    header("Location:purchases.php");
    exit();
}

$id=$_GET['id'];

$purchase=mysqli_query($conn,"
SELECT *
FROM purchases
WHERE id='$id'
");

$data=mysqli_fetch_assoc($purchase);

$suppliers=mysqli_query($conn,"
SELECT *
FROM suppliers
ORDER BY supplier_name ASC
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

<title>Edit Purchase</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

<link rel="stylesheet" href="assets/css/admin.css">

</head>

<body>

<?php include("includes/sidebar.php"); ?>

<div id="main-content">

    <div class="d-flex justify-content-between align-items-center mb-4">

        <h2 class="page-title mb-0">
            <i class="bi bi-cart-plus"></i>
            Edit Purchase
        </h2>

        <a href="purchases.php" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i>
            Back
        </a>

    </div>

    <div class="table-container shadow">

        <form action="update_purchase.php" method="POST">

            <input type="hidden" name="id" value="<?php echo $data['id']; ?>">

            <div class="mb-4">

                <label class="form-label fw-semibold">
                    Supplier
                </label>

                <select name="supplier_id" class="form-select" required>

                    <?php while($supplier=mysqli_fetch_assoc($suppliers)){ ?>

                        <option
                            value="<?php echo $supplier['id']; ?>"
                            <?php if($supplier['id']==$data['supplier_id']) echo "selected"; ?>>

                            <?php echo htmlspecialchars($supplier['supplier_name']); ?>

                        </option>

                    <?php } ?>

                </select>

            </div>

            <div class="mb-4">

                <label class="form-label fw-semibold">
                    Product
                </label>

                <select name="product_id" class="form-select" required>

                    <?php while($product=mysqli_fetch_assoc($products)){ ?>

                        <option
                            value="<?php echo $product['id']; ?>"
                            <?php if($product['id']==$data['product_id']) echo "selected"; ?>>

                            <?php echo htmlspecialchars($product['product_name']); ?>

                        </option>

                    <?php } ?>

                </select>

            </div>

            <div class="row">

                <div class="col-md-6 mb-4">

                    <label class="form-label fw-semibold">
                        Quantity
                    </label>

                    <input
                        type="number"
                        name="quantity"
                        id="quantity"
                        class="form-control"
                        value="<?php echo $data['quantity']; ?>"
                        required
                        onkeyup="calculateTotal()"
                        onchange="calculateTotal()">

                </div>

                <div class="col-md-6 mb-4">

                    <label class="form-label fw-semibold">
                        Purchase Price
                    </label>

                    <input
                        type="number"
                        step="0.01"
                        name="purchase_price"
                        id="purchase_price"
                        class="form-control"
                        value="<?php echo $data['purchase_price']; ?>"
                        required
                        onkeyup="calculateTotal()"
                        onchange="calculateTotal()">

                </div>

            </div>

            <div class="row">

                <div class="col-md-6 mb-4">

                    <label class="form-label fw-semibold">
                        Total Price
                    </label>

                    <input
                        type="text"
                        id="total_display"
                        class="form-control"
                        value="<?php echo number_format($data['total_price'],2,'.',''); ?>"
                        readonly>

                    <input
                        type="hidden"
                        name="total_price"
                        id="total_price"
                        value="<?php echo $data['total_price']; ?>">

                </div>

                <div class="col-md-6 mb-4">

                    <label class="form-label fw-semibold">
                        Purchase Date
                    </label>

                    <input
                        type="date"
                        name="purchase_date"
                        class="form-control"
                        value="<?php echo $data['purchase_date']; ?>"
                        required>

                </div>

            </div>

            <div class="d-flex gap-2">

                <button
                    type="submit"
                    class="btn btn-primary">

                    <i class="bi bi-pencil-square"></i>
                    Update Purchase

                </button>

                <a
                    href="purchases.php"
                    class="btn btn-secondary">

                    Cancel

                </a>

            </div>

        </form>

    </div>

</div>

<script>

function calculateTotal(){

    let qty=parseFloat(document.getElementById('quantity').value)||0;
    let price=parseFloat(document.getElementById('purchase_price').value)||0;
    let total=qty*price;

    document.getElementById("total_display").value=total.toFixed(2);
    document.getElementById("total_price").value=total.toFixed(2);

}

calculateTotal();

</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/darkmode.js"></script>

</body>

</html>

<?php
mysqli_close($conn);
?>