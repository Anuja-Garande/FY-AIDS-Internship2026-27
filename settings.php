<?php

session_start();

if(!isset($_SESSION['username']))
{
    header("Location: login.php");
    exit();
}

$conn = mysqli_connect(
    "localhost",
    "root",
    "",
    "inventory_management"
);

if(!$conn)
{
    die("Database Connection Failed");
}

$user_id = $_SESSION['user_id'];

/* Logged-in User */

$userResult = mysqli_query(
    $conn,
    "SELECT * FROM users WHERE id='$user_id'"
);

$user = mysqli_fetch_assoc($userResult);

/* Company Settings */

$settingsResult = mysqli_query(
    $conn,
    "SELECT * FROM settings LIMIT 1"
);

$settings = mysqli_fetch_assoc($settingsResult);

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
content="width=device-width, initial-scale=1.0">

<title>Settings</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"
rel="stylesheet">

<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css"
rel="stylesheet">

<link rel="stylesheet"
href="assets/css/admin.css">

<style>

.settings-card{

    border:none;

    border-radius:18px;

    overflow:hidden;

    box-shadow:0 10px 30px rgba(0,0,0,.08);

}

.settings-card .card-header{

    font-weight:600;

    font-size:18px;

    padding:16px 20px;

}

.settings-card .card-body{

    padding:25px;

}

.form-control{

    border-radius:10px;

}

.info-text{

    color:#6c757d;

}

</style>

</head>

<body>

<?php include("includes/sidebar.php"); ?>

<div id="main-content">

<div class="d-flex justify-content-between align-items-center mb-4">

    <h2 class="page-title">
        <i class="bi bi-gear-fill"></i>
        Settings
    </h2>

</div>

<?php if(isset($_GET['updated'])){ ?>

<div class="alert alert-success alert-dismissible fade show">

    <i class="bi bi-check-circle-fill"></i>

    Settings Updated Successfully.

    <button class="btn-close" data-bs-dismiss="alert"></button>

</div>

<?php } ?>

<div class="row">

    <!-- LEFT MENU -->

    <div class="col-lg-3">

        <div class="card settings-menu shadow-sm border-0">

            <div class="list-group list-group-flush">

                <a href="#general"
                class="list-group-item list-group-item-action active">

                    <i class="bi bi-person-fill me-2"></i>

                    General

                </a>

                <a href="#company"
                class="list-group-item list-group-item-action">

                    <i class="bi bi-building me-2"></i>

                    Company

                </a>

                <a href="#security"
                class="list-group-item list-group-item-action">

                    <i class="bi bi-shield-lock-fill me-2"></i>

                    Security

                </a>

                <a href="#appearance"
                class="list-group-item list-group-item-action">

                    <i class="bi bi-palette-fill me-2"></i>

                    Appearance

                </a>

                <a href="#about"
                class="list-group-item list-group-item-action">

                    <i class="bi bi-info-circle-fill me-2"></i>

                    About

                </a>

            </div>

        </div>

    </div>

    <!-- RIGHT CONTENT -->

    <div class="col-lg-9">

        <!-- ================= GENERAL SETTINGS ================= -->

<div class="card shadow-sm border-0 mb-4" id="general">

    <div class="card-header bg-primary text-white">

        <h5 class="mb-0">
            <i class="bi bi-person-fill me-2"></i>
            General Settings
        </h5>

    </div>

    <div class="card-body">

        <form action="update_account.php" method="POST">

            <div class="row">

                <div class="col-md-6 mb-3">

                    <label class="form-label">Username</label>

                    <input
                        type="text"
                        name="username"
                        class="form-control"
                        value="<?php echo htmlspecialchars($user['username']); ?>"
                        required>

                </div>

                <div class="col-md-6 mb-3">

                    <label class="form-label">Email</label>

                    <input
                        type="email"
                        name="email"
                        class="form-control"
                        value="<?php echo htmlspecialchars($user['email']); ?>"
                        required>

                </div>

            </div>

            <div class="row">

                <div class="col-md-6 mb-3">

                    <label class="form-label">Role</label>

                    <input
                        type="text"
                        class="form-control"
                        value="<?php echo htmlspecialchars($user['role']); ?>"
                        readonly>

                </div>

                <div class="col-md-6 mb-3">

                    <label class="form-label">Account Created</label>

                    <input
                        type="text"
                        class="form-control"
                        value="<?php echo date("d M Y", strtotime($user['created_at'])); ?>"
                        readonly>

                </div>

            </div>

            <button class="btn btn-primary">

                <i class="bi bi-save-fill"></i>

                Save Changes

            </button>

        </form>

    </div>

</div>





<!-- ================= COMPANY SETTINGS ================= -->

<div class="card shadow-sm border-0 mb-4" id="company">

    <div class="card-header bg-success text-white">

        <h5 class="mb-0">

            <i class="bi bi-building me-2"></i>

            Company Settings

        </h5>

    </div>

    <div class="card-body">

        <form action="update_company.php" method="POST">

            <div class="row">

                <div class="col-md-6 mb-3">

                    <label class="form-label">

                        Company Name

                    </label>

                    <input
                        type="text"
                        name="company_name"
                        class="form-control"
                        value="<?php echo htmlspecialchars($settings['company_name']); ?>"
                        required>

                </div>

                <div class="col-md-6 mb-3">

                    <label class="form-label">

                        Company Email

                    </label>

                    <input
                        type="email"
                        name="company_email"
                        class="form-control"
                        value="<?php echo htmlspecialchars($settings['company_email']); ?>">

                </div>

            </div>

            <div class="row">

                <div class="col-md-6 mb-3">

                    <label class="form-label">

                        Company Phone

                    </label>

                    <input
                        type="text"
                        name="company_phone"
                        class="form-control"
                        value="<?php echo htmlspecialchars($settings['company_phone']); ?>">

                </div>

                <div class="col-md-6 mb-3">

                    <label class="form-label">

                        Company Logo

                    </label>

                    <input
                        type="file"
                        class="form-control"
                        disabled>

                    <small class="text-muted">
                        Logo upload coming next.
                    </small>

                </div>

            </div>

            <div class="mb-3">

                <label class="form-label">

                    Company Address

                </label>

                <textarea
                    name="company_address"
                    rows="4"
                    class="form-control"><?php echo htmlspecialchars($settings['company_address']); ?></textarea>

            </div>

            <button class="btn btn-success">

                <i class="bi bi-save-fill"></i>

                Save Company

            </button>

        </form>

    </div>

</div>    <!-- SECURITY -->

   <!-- ================= SECURITY ================= -->

<div class="card shadow-sm border-0 mb-4" id="security">

    <div class="card-header bg-warning">

        <h5 class="mb-0">

            <i class="bi bi-shield-lock-fill me-2"></i>

            Security

        </h5>

    </div>

    <div class="card-body">

        <div class="row align-items-center">

            <div class="col-md-8">

    <h5 class="security-title">
        Change Password
    </h5>

    <p class="security-text mb-0">
        Keep your account secure by changing your password regularly.
    </p>

</div>

            <div class="col-md-4 text-md-end">

                <a href="change_password.php"
                class="btn btn-warning">

                    <i class="bi bi-    y-fill"></i>

                    Change Password

                </a>

            </div>

        </div>

    </div>

</div>





<!-- ================= APPEARANCE ================= -->

<div class="card shadow-sm border-0 mb-4" id="appearance">

    <div class="card-header bg-info text-white">

        <h5 class="mb-0">

            <i class="bi bi-palette-fill me-2"></i>

            Appearance

        </h5>

    </div>

    <div class="card-body">

        <div class="row">

            <div class="col-md-6">

                <div class="form-check form-switch">

                    <input
                        class="form-check-input"
                        type="checkbox"
                        id="darkModeSwitch">

                    <label
                        class="form-check-label"
                        for="darkModeSwitch">

                        Enable Dark Mode

                    </label>

                </div>

            </div>

            <div class="col-md-6">

                <button
                    class="btn btn-outline-primary"
                    disabled>

                    <i class="bi bi-palette2"></i>

                    Theme Customization (Coming Soon)

                </button>

            </div>

        </div>

    </div>

</div>





<!-- ================= ABOUT ================= -->

<!-- ================= ABOUT ================= -->

<div class="card shadow-sm border-0 mb-4" id="about">

    <div class="card-header bg-dark text-white">

        <h5 class="mb-0">
            <i class="bi bi-info-circle-fill me-2"></i>
            About InventoryPro
        </h5>

    </div>

    <div class="card-body">

        <div class="text-center mb-4">

            <i class="bi bi-box-seam-fill text-primary" style="font-size:70px;"></i>

            <h2 class="mt-3 mb-1">
                InventoryPro
            </h2>

            <p class="text-muted">
                Inventory & Stock Management System
            </p>

        </div>

        <div class="row text-center mb-4">

            <div class="col-md-3">
                <h6 class="fw-bold">Version</h6>
                <p>1.0.0</p>
            </div>

            <div class="col-md-3">
                <h6 class="fw-bold">Developer</h6>
                <p>M-Share</p>
            </div>

            <div class="col-md-3">
                <h6 class="fw-bold">Technology</h6>
                <p>PHP • MySQL • Bootstrap 5</p>
            </div>

            <div class="col-md-3">
                <h6 class="fw-bold">Year</h6>
                <p><?php echo date("Y"); ?></p>
            </div>

        </div>

        <hr>

        <div class="row">

            <div class="col-md-6">

                <table class="table table-borderless">

                    <tr>
                        <th width="40%">Current User</th>
                        <td><?php echo htmlspecialchars($user['username']); ?></td>
                    </tr>

                    <tr>
                        <th>Role</th>
                        <td><?php echo htmlspecialchars($user['role']); ?></td>
                    </tr>

                    <tr>
                        <th>Company</th>
                        <td><?php echo htmlspecialchars($settings['company_name']); ?></td>
                    </tr>

                    <tr>
                        <th>Database</th>
                        <td>MySQL</td>
                    </tr>

                </table>

            </div>

            <div class="col-md-6">

                <table class="table table-borderless">

                    <tr>
                        <th width="40%">Web Server</th>
                        <td>Apache (XAMPP)</td>
                    </tr>

                    <tr>
                        <th>Theme</th>
                        <td id="themeStatus">Light Mode</td>
                    </tr>

                    <tr>
                        <th>License</th>
                        <td>Educational Project</td>
                    </tr>

                    <tr>
                        <th>Status</th>
                        <td>
                            <span class="badge bg-success">
                                Running
                            </span>
                        </td>
                    </tr>

                </table>

            </div>

        </div>

        <hr>

        <p class="mb-0">
            InventoryPro is a web-based inventory and stock management system designed to simplify product, customer, supplier, purchase, and sales management through an intuitive dashboard and modern interface.
        </p>

    </div>

</div>
<script>

document.addEventListener("DOMContentLoaded",function(){

    const switchBtn=document.getElementById("darkModeSwitch");

    if(localStorage.getItem("theme")==="dark"){

        switchBtn.checked=true;

    }

    switchBtn.addEventListener("change",function(){

        if(this.checked){

            localStorage.setItem("theme","dark");

        }else{

            localStorage.setItem("theme","light");

        }

        location.reload();

    });

});

</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script src="assets/js/darkmode.js"></script>

</body>

</html>

<?php

mysqli_close($conn);

?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script src="assets/js/darkmode.js"></script>

</body>

</html>

<?php



?>