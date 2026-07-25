<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

if ($_SESSION['role'] != "Staff") {
    header("Location: dashboard.php");
    exit();
}

$conn = mysqli_connect(
    "localhost",
    "root",
    "",
    "inventory_management"
);

if (!$conn) {
    die("Database Connection Failed");
}

/* ================= COUNTS ================= */

$product_count = mysqli_fetch_assoc(
    mysqli_query($conn,"
    SELECT COUNT(*) total
    FROM products
")
)['total'];

$customer_count = mysqli_fetch_assoc(
    mysqli_query($conn,"
    SELECT COUNT(*) total
    FROM customers
")
)['total'];

$purchase_total = mysqli_fetch_assoc(
    mysqli_query($conn,"
    SELECT IFNULL(SUM(total_price),0) total
    FROM purchases
    WHERE DATE(created_at)=CURDATE()
")
)['total'];

$sales_total = mysqli_fetch_assoc(
    mysqli_query($conn,"
    SELECT IFNULL(SUM(total_price),0) total
    FROM sales
    WHERE DATE(created_at)=CURDATE()
")
)['total'];

$current_stock = mysqli_fetch_assoc(
    mysqli_query($conn,"
    SELECT IFNULL(SUM(quantity),0) total
    FROM products
")
)['total'];

$low_stock_count = mysqli_fetch_assoc(
    mysqli_query($conn,"
    SELECT COUNT(*) total
    FROM products
    WHERE quantity<=5
")
)['total'];

$low_stock_products = mysqli_query($conn,"
SELECT product_name,sku,quantity
FROM products
WHERE quantity<=5
ORDER BY quantity ASC
LIMIT 5
");

$recent_operations = mysqli_query($conn,"
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

<title>Staff Dashboard</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

<link rel="stylesheet" href="assets/css/admin.css">

</head>

<body>

<?php include("includes/sidebar.php"); ?>

<div id="main-content">

<div class="d-flex justify-content-between align-items-center mb-4">

<h2 class="page-title">

<i class="bi bi-speedometer2"></i>

Staff Dashboard

</h2>

<div>

<span class="badge bg-success fs-6">

Welcome,

<?php echo $_SESSION['username']; ?>

</span>

</div>

</div>

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

<div class="card card-counter shadow-sm p-3 border-start border-success border-4">

<h6>Total Customers</h6>

<h3 class="fw-bold">

<?php echo $customer_count; ?>

</h3>

<i class="bi bi-people fs-2 text-success"></i>

</div>

</div>

<div class="col-md-3">

<div class="card card-counter shadow-sm p-3 border-start border-info border-4">

<h6>Today's Sales</h6>

<h3>

₹<?php echo number_format($sales_total,2); ?>

</h3>

<i class="bi bi-cash-stack fs-2 text-info"></i>

</div>

</div>

<div class="col-md-3">

<div class="card card-counter shadow-sm p-3 border-start border-warning border-4">

<h6>Today's Purchases</h6>

<h3>

₹<?php echo number_format($purchase_total,2); ?>

</h3>

<i class="bi bi-cart-plus fs-2 text-warning"></i>

</div>

</div>

<div class="col-md-3">

<div class="card card-counter shadow-sm p-3 border-start border-secondary border-4">

<h6>Current Stock</h6>

<h3>

<?php echo $current_stock; ?>

Units

</h3>

<i class="bi bi-boxes fs-2 text-secondary"></i>

</div>

</div>

<div class="col-md-3">

<div class="card low-stock-alert-card low-stock-pulse shadow-sm p-3">

<h6>Low Stock Items</h6>

<h3>

<?php echo $low_stock_count; ?>

Items

</h3>

<i class="bi bi-exclamation-triangle fs-2"></i>

</div>

</div>

<div class="col-md-6">

<div class="card shadow-sm border-0 h-100">

<div class="card-body">

<h5 class="fw-bold mb-3">

Quick Actions

</h5>

<div class="d-grid gap-3">

<a href="add_product.php" class="btn btn-primary">

<i class="bi bi-box-seam"></i>

Add Product

</a>

<a href="add_sale.php" class="btn btn-success">

<i class="bi bi-cart-check"></i>

New Sale

</a>

<a href="add_purchase.php" class="btn btn-warning">

<i class="bi bi-cart-plus"></i>

New Purchase

</a>

</div>

</div>

</div>

</div>

<div class="row g-4">

    <!-- Recent Activity -->

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

                                if($row['type']=="Sale")
                                {
                                    $badge="success";
                                    $prefix="SALE";
                                }
                                else
                                {
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

                                    <?php echo date("d M Y",strtotime($row['created_at'])); ?>

                                </td>

                                <td>

                                    ₹<?php echo number_format($row['total_price'],2); ?>

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

                                    No Recent Activity

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

    <!-- Low Stock -->

    <div class="col-lg-4">

        <div class="card shadow-sm border-0">

            <div class="card-header bg-white">

                <h5 class="fw-bold text-danger mb-0">

                    <i class="bi bi-exclamation-triangle-fill"></i>

                    Low Stock Products

                </h5>

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

                            <h6 class="fw-bold mb-1">

                                <?php echo htmlspecialchars($row['product_name']); ?>

                            </h6>

                            <small class="text-muted">

                                SKU :
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

                    <div class="text-center py-5">

                        <i class="bi bi-check-circle fs-2 text-success"></i>

                        <p class="mt-3 mb-0">

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
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>

setTimeout(function(){

    let alerts=document.querySelectorAll(".alert");

    alerts.forEach(function(alert){

        let bsAlert=bootstrap.Alert.getOrCreateInstance(alert);

        bsAlert.close();

    });

},4000);

</script>

<script src="assets/js/darkmode.js"></script>

</body>

</html>