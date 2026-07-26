<?php

$host = "localhost";
$user = "root";
$password = "";
$database = "inventory_management";

$conn = mysqli_connect($host, $user, $password, $database);

if(!$conn){
    die("Connection Failed : ".mysqli_connect_error());
}

$id = $_GET['id'];

$sql = "
SELECT sales.*,
customers.customer_name,
products.product_name
FROM sales
JOIN customers ON sales.customer_id = customers.id
JOIN products ON sales.product_id = products.id
WHERE sales.id='$id'
";

$result = mysqli_query($conn,$sql);

$row = mysqli_fetch_assoc($result);

$customers = mysqli_query($conn,"
SELECT *
FROM customers
ORDER BY customer_name
");

$products = mysqli_query($conn,"
SELECT *
FROM products
ORDER BY product_name
");

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Edit Sale</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

<link rel="stylesheet" href="assets/css/admin.css">

</head>

<body>

<?php include("includes/sidebar.php"); ?>

<div id="main-content">

    <div class="d-flex justify-content-between align-items-center mb-4">

        <h2 class="page-title mb-0">
            <i class="bi bi-cash-coin"></i>
            Edit Sale
        </h2>

        <a href="sales.php" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i>
            Back
        </a>

    </div>

    <div class="table-container shadow">

        <form action="update_sale.php" method="POST">

            <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
            <input type="hidden" name="old_quantity" value="<?php echo $row['quantity']; ?>">

            <div class="mb-4">

                <label class="form-label fw-semibold">
                    Customer
                </label>

                <select name="customer_id" class="form-select" required>

                    <?php while($customer=mysqli_fetch_assoc($customers)){ ?>

                    <option
                        value="<?php echo $customer['id']; ?>"
                        <?php if($customer['id']==$row['customer_id']) echo "selected"; ?>>

                        <?php echo htmlspecialchars($customer['customer_name']); ?>

                    </option>

                    <?php } ?>

                </select>

            </div>

            <div class="mb-4">

                <label class="form-label fw-semibold">
                    Product
                </label>

                <select
                    name="product_id"
                    id="product"
                    class="form-select"
                    required>

                    <?php while($product=mysqli_fetch_assoc($products)){ ?>

                    <option
                        value="<?php echo $product['id']; ?>"
                        data-price="<?php echo $product['selling_price']; ?>"
                        data-stock="<?php echo $product['quantity']; ?>"
                        <?php if($product['id']==$row['product_id']) echo "selected"; ?>>

                        <?php echo htmlspecialchars($product['product_name']); ?>

                        (Stock : <?php echo $product['quantity']; ?>)

                    </option>

                    <?php } ?>

                </select>

            </div>

            <div class="row">

                <div class="col-md-6 mb-4">

                    <label class="form-label fw-semibold">
                        Available Stock
                    </label>

                    <input
                        type="text"
                        id="stock"
                        class="form-control"
                        readonly>

                </div>

                <div class="col-md-6 mb-4">

                    <label class="form-label fw-semibold">
                        Selling Price
                    </label>

                    <input
                        type="number"
                        step="0.01"
                        name="selling_price"
                        id="price"
                        class="form-control"
                        value="<?php echo $row['selling_price']; ?>"
                        readonly>

                </div>

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
                        value="<?php echo $row['quantity']; ?>"
                        required>

                </div>

                <div class="col-md-6 mb-4">

                    <label class="form-label fw-semibold">
                        Total Price
                    </label>

                    <input
                        type="text"
                        id="total_display"
                        class="form-control"
                        value="<?php echo number_format($row['total_price'],2); ?>"
                        readonly>

                    <input
                        type="hidden"
                        name="total_price"
                        id="total_price"
                        value="<?php echo $row['total_price']; ?>">

                </div>

            </div>

            <div class="mb-4">

                <label class="form-label fw-semibold">
                    Sale Date
                </label>

                <input
                    type="date"
                    name="sale_date"
                    class="form-control"
                    value="<?php echo $row['sale_date']; ?>"
                    required>

            </div>

            <div class="d-flex gap-2">

                <button
                    type="submit"
                    class="btn btn-primary">

                    <i class="bi bi-pencil-square"></i>

                    Update Sale

                </button>

                <a
                    href="sales.php"
                    class="btn btn-secondary">

                    Cancel

                </a>

            </div>

        </form>

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

    let p=parseFloat(option.dataset.price)||0;
    let s=parseInt(option.dataset.stock)||0;
    let q=parseInt(qty.value)||0;

    price.value=p.toFixed(2);
    stock.value=s;

    let t=p*q;

    total.value=t.toFixed(2);
    hidden.value=t.toFixed(2);

}

product.addEventListener("change",calculateSale);
qty.addEventListener("keyup",calculateSale);
qty.addEventListener("change",calculateSale);

calculateSale();

</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/darkmode.js"></script>

</body>
</html>

<?php
mysqli_close($conn);
?>