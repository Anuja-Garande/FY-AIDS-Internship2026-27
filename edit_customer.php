<?php

$host = "localhost";
$user = "root";
$password = "";
$database = "inventory_management";

$conn = mysqli_connect($host, $user, $password, $database);

if (!$conn) {
    die("Connection Failed: " . mysqli_connect_error());
}

if (!isset($_GET['id'])) {
    header("Location: customers.php");
    exit();
}

$id = intval($_GET['id']);

$sql = "SELECT * FROM customers WHERE id = $id";
$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) == 0) {
    header("Location: customers.php");
    exit();
}

$row = mysqli_fetch_assoc($result);

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Edit Customer</title>

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
            <i class="bi bi-people"></i>
            Edit Customer
        </h2>

        <a href="customers.php" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i>
            Back
        </a>

    </div>

    <!-- FORM CARD -->

    <div class="table-container shadow">

        <form action="update_customer.php" method="POST">

            <input
                type="hidden"
                name="id"
                value="<?php echo $row['id']; ?>">

            <div class="mb-4">

                <label class="form-label fw-semibold">
                    Customer Name
                </label>

                <input
                    type="text"
                    name="customer_name"
                    class="form-control"
                    value="<?php echo htmlspecialchars($row['customer_name']); ?>"
                    required>

            </div>

            <div class="mb-4">

                <label class="form-label fw-semibold">
                    Phone Number
                </label>

                <input
                    type="text"
                    name="phone"
                    class="form-control"
                    value="<?php echo htmlspecialchars($row['phone']); ?>"
                    required>

            </div>

            <div class="mb-4">

                <label class="form-label fw-semibold">
                    Email Address
                </label>

                <input
                    type="email"
                    name="email"
                    class="form-control"
                    value="<?php echo htmlspecialchars($row['email']); ?>"
                    required>

            </div>

            <div class="mb-4">

                <label class="form-label fw-semibold">
                    Address
                </label>

                <textarea
                    name="address"
                    rows="5"
                    class="form-control"
                    required><?php echo htmlspecialchars($row['address']); ?></textarea>

            </div>

            <div class="d-flex gap-2">

                <button
                    type="submit"
                    class="btn btn-primary">

                    <i class="bi bi-pencil-square"></i>
                    Update Customer

                </button>

                <a
                    href="customers.php"
                    class="btn btn-secondary">

                    Cancel

                </a>

            </div>

        </form>

    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/darkmode.js"></script>

</body>

</html>