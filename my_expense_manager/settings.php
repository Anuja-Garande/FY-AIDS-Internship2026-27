<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: auth/login.php");
    exit();
}

include("database/db.php");

$user_id = $_SESSION['user_id'];
$message = "";

/* ============================
   Load User Settings
============================ */

$settingQuery = mysqli_query($conn,"
SELECT *
FROM settings
WHERE user_id='$user_id'
LIMIT 1
");

if(mysqli_num_rows($settingQuery)>0)
{
    $settings = mysqli_fetch_assoc($settingQuery);
}
else
{
    mysqli_query($conn,"
    INSERT INTO settings
    (user_id,currency,theme,notification)
    VALUES
    ('$user_id','INR','Light',1)
    ");

    $settingQuery = mysqli_query($conn,"
    SELECT *
    FROM settings
    WHERE user_id='$user_id'
    LIMIT 1
    ");

    $settings = mysqli_fetch_assoc($settingQuery);
}

/* ============================
   Save Settings
============================ */

if(isset($_POST['save_settings']))
{

    $currency = mysqli_real_escape_string($conn,$_POST['currency']);
    $theme = mysqli_real_escape_string($conn,$_POST['theme']);
    $notification = isset($_POST['notification']) ? 1 : 0;

    $update = mysqli_query($conn,"
    UPDATE settings

    SET

    currency='$currency',
    theme='$theme',
    notification='$notification'

    WHERE user_id='$user_id'
    ");

    if($update)
    {

        $message='
        <div class="alert alert-success alert-dismissible fade show">

        Settings Updated Successfully.

        <button class="btn-close"
        data-bs-dismiss="alert"></button>

        </div>';

        $settingQuery=mysqli_query($conn,"
        SELECT *
        FROM settings
        WHERE user_id='$user_id'
        ");

        $settings=mysqli_fetch_assoc($settingQuery);

    }

    else

    {

        $message='
        <div class="alert alert-danger alert-dismissible fade show">

        Failed To Save Settings.

        <button class="btn-close"
        data-bs-dismiss="alert"></button>

        </div>';

    }

}

include("includes/header.php");
include("includes/sidebar.php");
?>

<div class="container-fluid">

<h2 class="fw-bold mb-4">

Settings

</h2>

<?php echo $message; ?>

<div class="card shadow">

<div class="card-header bg-primary text-white">

<h4 class="mb-0">

Application Settings

</h4>

</div>

<div class="card-body">

<form method="POST">

<div class="row">

<div class="col-md-6 mb-3">

<label class="form-label">

Currency

</label>

<select
name="currency"
class="form-select">

<option value="INR"
<?php if($settings['currency']=="INR") echo "selected"; ?>>

Indian Rupee (₹)

</option>

<option value="USD"
<?php if($settings['currency']=="USD") echo "selected"; ?>>

US Dollar ($)

</option>

<option value="EUR"
<?php if($settings['currency']=="EUR") echo "selected"; ?>>

Euro (€)

</option>

</select>

</div>

<div class="col-md-6 mb-3">

<label class="form-label">

Theme

</label>

<select
name="theme"
class="form-select">

<option value="Light"
<?php if($settings['theme']=="Light") echo "selected"; ?>>

Light

</option>

<option value="Dark"
<?php if($settings['theme']=="Dark") echo "selected"; ?>>

Dark

</option>

</select>

</div>
<div class="col-md-12 mb-3">

<label class="form-label">

Notifications

</label>

<div class="form-check form-switch">

<input
class="form-check-input"
type="checkbox"
name="notification"
id="notification"

<?php
if($settings['notification']==1)
echo "checked";
?>

>

<label
class="form-check-label"
for="notification">

Enable Notifications

</label>

</div>

</div>

<div class="col-md-12">

<button
type="submit"
name="save_settings"
class="btn btn-primary">

<i class="bi bi-save"></i>

Save Settings

</button>

<button
type="reset"
class="btn btn-secondary">

Reset

</button>

</div>

</div>

</form>

</div>

</div>

<br>

<div class="row">

<div class="col-lg-6">

<div class="card shadow">

<div class="card-header bg-success text-white">

<h4 class="mb-0">

Application Information

</h4>

</div>

<div class="card-body">

<table class="table table-bordered">

<tr>

<th>

Application

</th>

<td>

My Expense Manager

</td>

</tr>

<tr>

<th>

Version

</th>

<td>

1.0

</td>

</tr>

<tr>

<th>

Technology

</th>

<td>

PHP, MySQL, Bootstrap 5

</td>

</tr>

<tr>

<th>

Database

</th>

<td>

my_expense_manager

</td>

</tr>

</table>

</div>

</div>

</div>

<div class="col-lg-6">

<div class="card shadow">

<div class="card-header bg-info text-white">

<h4 class="mb-0">

System Information

</h4>

</div>

<div class="card-body">

<table class="table table-bordered">

<tr>

<th>

PHP Version

</th>

<td>

<?php echo phpversion(); ?>

</td>

</tr>

<tr>

<th>

Server Software

</th>

<td>

<?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown'; ?>

</td>

</tr>

<tr>

<th>

Current Time

</th>

<td>

<?php echo date("d M Y h:i:s A"); ?>

</td>

</tr>

<tr>

<th>

Logged In User ID

</th>

<td>

<?php echo $user_id; ?>

</td>

</tr>

</table>

</div>

</div>

</div>

</div>

<br>
<div class="row">

<div class="col-lg-6">

<div class="card shadow">

<div class="card-header bg-warning">

<h4 class="mb-0">

Current Preferences

</h4>

</div>

<div class="card-body">

<table class="table table-bordered">

<tr>

<th width="40%">

Currency

</th>

<td>

<?php echo htmlspecialchars($settings['currency']); ?>

</td>

</tr>

<tr>

<th>

Theme

</th>

<td>

<?php echo htmlspecialchars($settings['theme']); ?>

</td>

</tr>

<tr>

<th>

Notifications

</th>

<td>

<?php
echo ($settings['notification'] == 1)
    ? '<span class="badge bg-success">Enabled</span>'
    : '<span class="badge bg-secondary">Disabled</span>';
?>

</td>

</tr>

</table>

</div>

</div>

</div>

<div class="col-lg-6">

<div class="card shadow">

<div class="card-header bg-secondary text-white">

<h4 class="mb-0">

Quick Actions

</h4>

</div>

<div class="card-body">

<div class="d-grid gap-2">

<a href="dashboard.php" class="btn btn-primary">

<i class="bi bi-speedometer2"></i>

Go to Dashboard

</a>

<a href="income.php" class="btn btn-success">

<i class="bi bi-cash-stack"></i>

Manage Income

</a>

<a href="expenses.php" class="btn btn-danger">

<i class="bi bi-wallet2"></i>

Manage Expenses

</a>

<a href="reports.php" class="btn btn-info text-white">

<i class="bi bi-bar-chart"></i>

View Reports

</a>

</div>

</div>

</div>

</div>

</div>

<div class="card shadow mt-4">

<div class="card-body">

<div class="alert alert-primary mb-0">

<i class="bi bi-info-circle-fill"></i>

Your settings are stored in the <strong>my_expense_manager</strong> database and will be applied the next time you use the application. Theme support can be expanded later by applying the selected theme throughout the interface.

</div>

</div>

</div>

</div>

<?php

include("includes/footer.php");

?>