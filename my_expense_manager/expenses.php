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
   Add Expense
============================= */

if (isset($_POST['add_expense'])) {

    $category_id = mysqli_real_escape_string($conn, $_POST['category_id']);
    $amount = mysqli_real_escape_string($conn, $_POST['amount']);
    $expense_date = mysqli_real_escape_string($conn, $_POST['expense_date']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);

    $insert = mysqli_query($conn,"
        INSERT INTO expenses
        (user_id,category_id,amount,expense_date,description)
        VALUES
        ('$user_id','$category_id','$amount','$expense_date','$description')
    ");

    if($insert){

        $message='
        <div class="alert alert-success alert-dismissible fade show">
            Expense Added Successfully.
            <button class="btn-close" data-bs-dismiss="alert"></button>
        </div>';

    }else{

        $message='
        <div class="alert alert-danger alert-dismissible fade show">
            Failed to Add Expense.
            <button class="btn-close" data-bs-dismiss="alert"></button>
        </div>';

    }

}

include("includes/header.php");
include("includes/sidebar.php");
?>

<div class="container-fluid">

<div class="row">

<div class="col-lg-12">

<h2 class="mb-4 fw-bold">

Expense Management

</h2>

<?php echo $message; ?>

<div class="card shadow">

<div class="card-header bg-danger text-white">

<h4 class="mb-0">

Add New Expense

</h4>

</div>

<div class="card-body">

<form method="POST">

<div class="row">

<div class="col-md-6 mb-3">

<label class="form-label">

Category

</label>

<select
name="category_id"
class="form-select"
required>

<option value="">

Select Category

</option>

<?php

$category=mysqli_query($conn,"
SELECT *
FROM categories
WHERE category_type='Expense'
ORDER BY category_name ASC
");

while($row=mysqli_fetch_assoc($category))
{

?>

<option value="<?php echo $row['id']; ?>">

<?php echo $row['category_name']; ?>

</option>

<?php

}

?>

</select>

</div>

<div class="col-md-6 mb-3">

<label class="form-label">

Amount

</label>

<input
type="number"
step="0.01"
name="amount"
class="form-control"
placeholder="Enter Amount"
required>

</div>

<div class="col-md-6 mb-3">

<label class="form-label">

Expense Date

</label>

<input
type="date"
name="expense_date"
class="form-control"
required>

</div>

<div class="col-md-6 mb-3">

<label class="form-label">

Description

</label>

<input
type="text"
name="description"
class="form-control"
placeholder="Expense Description">

</div>

<div class="col-md-12">

<button
type="submit"
name="add_expense"
class="btn btn-danger">

<i class="bi bi-plus-circle"></i>

Add Expense

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
<!-- Search and Total Expense -->

<div class="row mb-4">

<div class="col-md-6">

<form method="GET">

<div class="input-group">

<input
type="text"
name="search"
class="form-control"
placeholder="Search Description..."
value="<?php if(isset($_GET['search'])) echo $_GET['search']; ?>">

<button class="btn btn-primary" type="submit">

<i class="bi bi-search"></i>

Search

</button>

</div>

</form>

</div>

<div class="col-md-6 text-end">

<?php

$totalExpense = mysqli_query($conn,"
SELECT SUM(amount) AS total
FROM expenses
WHERE user_id='$user_id'
");

$totalExpense = mysqli_fetch_assoc($totalExpense);

?>

<h4 class="text-danger">

Total Expense :

₹ <?php echo number_format($totalExpense['total'] ?? 0,2); ?>

</h4>

</div>

</div>

<!-- Expense Records -->

<div class="card shadow">

<div class="card-header bg-primary text-white">

<h4 class="mb-0">

Expense Records

</h4>

</div>

<div class="card-body">

<div class="table-responsive">

<table class="table table-bordered table-hover align-middle">

<thead class="table-dark">

<tr>

<th>ID</th>

<th>Category</th>

<th>Amount</th>

<th>Date</th>

<th>Description</th>

<th width="170">

Action

</th>

</tr>

</thead>

<tbody>

<?php

$where="";

if(isset($_GET['search']) && $_GET['search']!="")
{

$search=mysqli_real_escape_string($conn,$_GET['search']);

$where="AND expenses.description LIKE '%$search%'";

}

$query=mysqli_query($conn,"
SELECT expenses.*,
categories.category_name

FROM expenses

INNER JOIN categories

ON expenses.category_id=categories.id

WHERE expenses.user_id='$user_id'
$where

ORDER BY expenses.id DESC
");

if(mysqli_num_rows($query)>0)
{

while($row=mysqli_fetch_assoc($query))
{

?>

<tr>

<td>

<?php echo $row['id']; ?>

</td>

<td>

<?php echo $row['category_name']; ?>

</td>

<td class="text-danger fw-bold">

₹ <?php echo number_format($row['amount'],2); ?>

</td>

<td>

<?php echo $row['expense_date']; ?>

</td>

<td>

<?php echo $row['description']; ?>

</td>

<td>

<a
href="expenses.php?edit=<?php echo $row['id']; ?>"
class="btn btn-warning btn-sm">

<i class="bi bi-pencil-square"></i>

Edit

</a>

<a
href="expenses.php?delete=<?php echo $row['id']; ?>"
class="btn btn-danger btn-sm"
onclick="return confirm('Delete this expense?');">

<i class="bi bi-trash"></i>

Delete

</a>

</td>

</tr>

<?php

}

}

else

{

?>

<tr>

<td colspan="6" class="text-center text-danger">

No Expense Records Found.

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

<br>
<?php

/* ============================
   Delete Expense
============================= */

if (isset($_GET['delete'])) {

    $delete_id = (int)$_GET['delete'];

    $delete = mysqli_query($conn,"
        DELETE FROM expenses
        WHERE id='$delete_id'
        AND user_id='$user_id'
    ");

    if ($delete) {

        echo "<script>
                alert('Expense Deleted Successfully.');
                window.location='expenses.php';
              </script>";

        exit();

    } else {

        echo "<script>alert('Unable to Delete Record.');</script>";

    }
}

/* ============================
   Update Expense
============================= */

if (isset($_POST['update_expense'])) {

    $edit_id = (int)$_POST['edit_id'];

    $category_id = mysqli_real_escape_string($conn,$_POST['category_id']);
    $amount = mysqli_real_escape_string($conn,$_POST['amount']);
    $expense_date = mysqli_real_escape_string($conn,$_POST['expense_date']);
    $description = mysqli_real_escape_string($conn,$_POST['description']);

    $update = mysqli_query($conn,"
        UPDATE expenses
        SET
            category_id='$category_id',
            amount='$amount',
            expense_date='$expense_date',
            description='$description'
        WHERE id='$edit_id'
        AND user_id='$user_id'
    ");

    if ($update) {

        echo "<script>
                alert('Expense Updated Successfully.');
                window.location='expenses.php';
              </script>";

        exit();

    } else {

        echo "<script>alert('Unable to Update Record.');</script>";

    }
}

/* ============================
   Edit Expense
============================= */

if (isset($_GET['edit'])) {

    $edit_id = (int)$_GET['edit'];

    $edit = mysqli_query($conn,"
        SELECT *
        FROM expenses
        WHERE id='$edit_id'
        AND user_id='$user_id'
    ");

    if(mysqli_num_rows($edit)>0){

    $editData = mysqli_fetch_assoc($edit);

?>

<div class="card shadow mt-4">

<div class="card-header bg-warning">

<h4 class="mb-0">

Edit Expense

</h4>

</div>

<div class="card-body">

<form method="POST">

<input
type="hidden"
name="edit_id"
value="<?php echo $editData['id']; ?>">

<div class="row">

<div class="col-md-6 mb-3">

<label>Category</label>

<select
name="category_id"
class="form-select"
required>

<?php

$category = mysqli_query($conn,"
SELECT *
FROM categories
WHERE category_type='Expense'
ORDER BY category_name
");

while($cat=mysqli_fetch_assoc($category))
{

?>

<option
value="<?php echo $cat['id']; ?>"

<?php

if($cat['id']==$editData['category_id'])
echo "selected";

?>

>

<?php echo $cat['category_name']; ?>

</option>

<?php

}

?>

</select>

</div>

<div class="col-md-6 mb-3">

<label>Amount</label>

<input
type="number"
step="0.01"
name="amount"
class="form-control"
value="<?php echo $editData['amount']; ?>"
required>

</div>

<div class="col-md-6 mb-3">

<label>Expense Date</label>

<input
type="date"
name="expense_date"
class="form-control"
value="<?php echo $editData['expense_date']; ?>"
required>

</div>

<div class="col-md-6 mb-3">

<label>Description</label>

<input
type="text"
name="description"
class="form-control"
value="<?php echo $editData['description']; ?>">

</div>

<div class="col-12">

<button
type="submit"
name="update_expense"
class="btn btn-warning">

<i class="bi bi-pencil-square"></i>

Update Expense

</button>

<a
href="expenses.php"
class="btn btn-secondary">

Cancel

</a>

</div>

</div>

</form>

</div>

</div>

<?php

}

}

include("includes/footer.php");

?>