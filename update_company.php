<?php

session_start();

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
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

if ($_SERVER["REQUEST_METHOD"] != "POST") {
    header("Location: settings.php");
    exit();
}

/* Get Form Data */

$company_name = trim($_POST['company_name']);
$company_email = trim($_POST['company_email']);
$company_phone = trim($_POST['company_phone']);
$company_address = trim($_POST['company_address']);

/* Validation */

if (empty($company_name)) {
    die("Company Name is required.");
}

if (!empty($company_email) && !filter_var($company_email, FILTER_VALIDATE_EMAIL)) {
    die("Invalid Company Email.");
}

/* Check if settings row exists */

$check = mysqli_query($conn, "SELECT id FROM settings LIMIT 1");

if (mysqli_num_rows($check) > 0) {

    $row = mysqli_fetch_assoc($check);

    $id = $row['id'];

    $stmt = mysqli_prepare(
        $conn,
        "UPDATE settings
         SET company_name=?,
             company_email=?,
             company_phone=?,
             company_address=?
         WHERE id=?"
    );

    mysqli_stmt_bind_param(
        $stmt,
        "ssssi",
        $company_name,
        $company_email,
        $company_phone,
        $company_address,
        $id
    );

} else {

    $stmt = mysqli_prepare(
        $conn,
        "INSERT INTO settings
        (
            company_name,
            company_email,
            company_phone,
            company_address
        )
        VALUES
        (?, ?, ?, ?)"
    );

    mysqli_stmt_bind_param(
        $stmt,
        "ssss",
        $company_name,
        $company_email,
        $company_phone,
        $company_address
    );

}

if (mysqli_stmt_execute($stmt)) {

    header("Location: settings.php?updated=1");
    exit();

} else {

    echo "Failed to save company information.";

}

mysqli_stmt_close($stmt);
mysqli_close($conn);

?>