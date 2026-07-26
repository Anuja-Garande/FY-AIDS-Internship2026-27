<?php

$host = "localhost";
$user = "root";
$password = "";
$database = "inventory_management";

$conn = mysqli_connect($host, $user, $password, $database);

if (!$conn) {
    die("Connection Failed : " . mysqli_connect_error());
}

if (!isset($_GET['id'])) {
    header("Location: products.php");
    exit();
}

$id = intval($_GET['id']);

$sql = "SELECT * FROM products WHERE id='$id'";
$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) == 0) {
    header("Location: products.php");
    exit();
}

$row = mysqli_fetch_assoc($result);

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Edit Product</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

<link rel="stylesheet" href="assets/css/admin.css">

</head>

<body>

<?php include("includes/sidebar.php"); ?>

<div id="main-content">

    <!-- PAGE HEADER -->

    <div class="d-flex justify-content-between align-items-center mb-4">

        <h2 class="page-title mb-0">
            <i class="bi bi-box-seam"></i>
            Edit Product
        </h2>

        <a href="products.php" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i>
            Back
        </a>

    </div>

    <!-- FORM -->

    <div class="table-container shadow">

        <form action="update_product.php" method="POST">

            <input
                type="hidden"
                name="id"
                value="<?php echo $row['id']; ?>">

            <div class="row">

                <div class="col-md-6 mb-4">

                    <label class="form-label fw-semibold">
                        Product Name
                    </label>

                    <input
                        type="text"
                        name="product_name"
                        class="form-control"
                        value="<?php echo htmlspecialchars($row['product_name']); ?>"
                        required>

                </div>

                <div class="col-md-6 mb-4">

                    <label class="form-label fw-semibold">
                        Category
                    </label>

                    <input
                        type="text"
                        name="category"
                        class="form-control"
                        value="<?php echo htmlspecialchars($row['category']); ?>"
                        required>

                </div>

                <div class="col-md-6 mb-4">

                    <label class="form-label fw-semibold">
                        Supplier
                    </label>

                    <input
                        type="text"
                        name="supplier"
                        class="form-control"
                        value="<?php echo htmlspecialchars($row['supplier']); ?>"
                        required>

                </div>

                <div class="col-md-6 mb-4">

                    <label class="form-label fw-semibold">
                        SKU
                    </label>

                    <input
                        type="text"
                        name="sku"
                        class="form-control"
                        value="<?php echo htmlspecialchars($row['sku']); ?>">

                </div>

                <div class="col-md-6 mb-4">

                    <label class="form-label fw-semibold">
                        Cost Price
                    </label>

                    <input
                        type="number"
                        step="0.01"
                        name="cost_price"
                        class="form-control"
                        value="<?php echo $row['cost_price']; ?>"
                        required>

                </div>

                <div class="col-md-6 mb-4">

                    <label class="form-label fw-semibold">
                        Selling Price
                    </label>

                    <input
                        type="number"
                        step="0.01"
                        name="selling_price"
                        class="form-control"
                        value="<?php echo $row['selling_price']; ?>"
                        required>

                </div>

                <div class="col-md-6 mb-4">

                    <label class="form-label fw-semibold">
                        Quantity
                    </label>

                    <input
                        type="number"
                        name="quantity"
                        class="form-control"
                        value="<?php echo $row['quantity']; ?>"
                        required>

                </div>

                <div class="col-md-6 mb-4">

                    <label class="form-label fw-semibold">
                        Unit
                    </label>

                    <select
                        name="unit"
                        class="form-select">

                        <option value="Pieces" <?php if($row['unit']=="Pieces") echo "selected"; ?>>Pieces</option>
                        <option value="Box" <?php if($row['unit']=="Box") echo "selected"; ?>>Box</option>
                        <option value="Kg" <?php if($row['unit']=="Kg") echo "selected"; ?>>Kg</option>
                        <option value="Litre" <?php if($row['unit']=="Litre") echo "selected"; ?>>Litre</option>

                    </select>

                </div>

                <div class="col-12 mb-4">

                    <label class="form-label fw-semibold">
                        Description
                    </label>

                    <textarea
                        name="description"
                        rows="5"
                        class="form-control"
                        required><?php echo htmlspecialchars($row['description']); ?></textarea>

                </div>

                <div class="col-12 d-flex gap-2">

                    <button
                        type="submit"
                        class="btn btn-primary">

                        <i class="bi bi-pencil-square"></i>
                        Update Product

                    </button>

                    <a
                        href="products.php"
                        class="btn btn-secondary">

                        Cancel

                    </a>

                </div>

            </div>

        </form>

    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/darkmode.js"></script>

</body>

</html>