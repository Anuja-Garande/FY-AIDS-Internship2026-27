<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: auth/login.php");
    exit();
}

include("database/db.php");

$message = "";

/* ============================
   Add Category
============================= */

if(isset($_POST['add_category']))
{

    $category_name=mysqli_real_escape_string($conn,$_POST['category_name']);

    $category_type=mysqli_real_escape_string($conn,$_POST['category_type']);

    $check=mysqli_query($conn,"
    SELECT *
    FROM categories
    WHERE category_name='$category_name'
    AND category_type='$category_type'
    ");

    if(mysqli_num_rows($check)>0)
    {

        $message='
        <div class="alert alert-warning alert-dismissible fade show">

        Category Already Exists.

        <button class="btn-close"
        data-bs-dismiss="alert"></button>

        </div>';

    }

    else

    {

        $insert=mysqli_query($conn,"
        INSERT INTO categories
        (category_name,category_type)

        VALUES

        ('$category_name','$category_type')
        ");

        if($insert)
        {

            $message='
            <div class="alert alert-success alert-dismissible fade show">

            Category Added Successfully.

            <button class="btn-close"
            data-bs-dismiss="alert"></button>

            </div>';

        }

        else

        {

            $message='
            <div class="alert alert-danger alert-dismissible fade show">

            Failed To Add Category.

            <button class="btn-close"
            data-bs-dismiss="alert"></button>

            </div>';

        }

    }

}

include("includes/header.php");
include("includes/sidebar.php");
?>

<div class="container-fluid">

<div class="row">

<div class="col-lg-12">

<h2 class="fw-bold mb-4">

Category Management

</h2>

<?php echo $message; ?>

<div class="card shadow">

<div class="card-header bg-primary text-white">

<h4 class="mb-0">

Add New Category

</h4>

</div>

<div class="card-body">

<form method="POST">

<div class="row">

<div class="col-md-5 mb-3">

<label class="form-label">

Category Name

</label>

<input
type="text"
name="category_name"
class="form-control"
placeholder="Enter Category Name"
required>

</div>

<div class="col-md-5 mb-3">

<label class="form-label">

Category Type

</label>

<select
name="category_type"
class="form-select"
required>

<option value="">

Select Type

</option>

<option value="Income">

Income

</option>

<option value="Expense">

Expense

</option>

</select>

</div>

<div class="col-md-2 mb-3 d-flex align-items-end">

<button
type="submit"
name="add_category"
class="btn btn-success w-100">

<i class="bi bi-plus-circle"></i>

Add

</button>

</div>

</div>

</form>

</div>

</div>

<br>
<!-- Search & Total Categories -->

<div class="row mb-4">

<div class="col-md-6">

<form method="GET">

<div class="input-group">

<input
type="text"
name="search"
class="form-control"
placeholder="Search Category..."
value="<?php if(isset($_GET['search'])) echo $_GET['search']; ?>">

<button
class="btn btn-primary"
type="submit">

<i class="bi bi-search"></i>

Search

</button>

</div>

</form>

</div>

<div class="col-md-6 text-end">

<?php

$totalCategory=mysqli_query($conn,"
SELECT COUNT(*) AS total
FROM categories
");

$totalCategory=mysqli_fetch_assoc($totalCategory);

?>

<h4 class="text-primary">

Total Categories :

<?php echo $totalCategory['total']; ?>

</h4>

</div>

</div>

<!-- Category Table -->

<div class="card shadow">

<div class="card-header bg-dark text-white">

<h4 class="mb-0">

Category List

</h4>

</div>

<div class="card-body">

<div class="table-responsive">

<table class="table table-bordered table-hover align-middle">

<thead class="table-primary">

<tr>

<th>ID</th>

<th>Category Name</th>

<th>Type</th>

<th width="180">

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

$where="WHERE category_name LIKE '%$search%'";

}

$query=mysqli_query($conn,"
SELECT *
FROM categories
$where
ORDER BY id DESC
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

<td>

<?php

if($row['category_type']=="Income")
{

?>

<span class="badge bg-success">

Income

</span>

<?php

}

else

{

?>

<span class="badge bg-danger">

Expense

</span>

<?php

}

?>

</td>

<td>

<a
href="categories.php?edit=<?php echo $row['id']; ?>"
class="btn btn-warning btn-sm">

<i class="bi bi-pencil-square"></i>

Edit

</a>

<a
href="categories.php?delete=<?php echo $row['id']; ?>"
class="btn btn-danger btn-sm"
onclick="return confirm('Delete this category?');">

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

<td
colspan="4"
class="text-center text-danger">

No Categories Found.

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
   Delete Category
============================= */

if (isset($_GET['delete'])) {

    $delete_id = (int)$_GET['delete'];

    /* Prevent deletion if category is in use */

    $incomeCheck = mysqli_query($conn,"
        SELECT id
        FROM income
        WHERE category_id='$delete_id'
        LIMIT 1
    ");

    $expenseCheck = mysqli_query($conn,"
        SELECT id
        FROM expenses
        WHERE category_id='$delete_id'
        LIMIT 1
    ");

    if (mysqli_num_rows($incomeCheck) > 0 || mysqli_num_rows($expenseCheck) > 0) {

        echo "<script>
                alert('This category is already used in transactions and cannot be deleted.');
                window.location='categories.php';
              </script>";
        exit();
    }

    $delete = mysqli_query($conn,"
        DELETE FROM categories
        WHERE id='$delete_id'
    ");

    if ($delete) {

        echo "<script>
                alert('Category Deleted Successfully.');
                window.location='categories.php';
              </script>";
        exit();

    } else {

        echo "<script>alert('Unable to Delete Category.');</script>";

    }
}

/* ============================
   Update Category
============================= */

if (isset($_POST['update_category'])) {

    $edit_id = (int)$_POST['edit_id'];

    $category_name = mysqli_real_escape_string($conn,$_POST['category_name']);
    $category_type = mysqli_real_escape_string($conn,$_POST['category_type']);

    $update = mysqli_query($conn,"
        UPDATE categories
        SET
            category_name='$category_name',
            category_type='$category_type'
        WHERE id='$edit_id'
    ");

    if ($update) {

        echo "<script>
                alert('Category Updated Successfully.');
                window.location='categories.php';
              </script>";
        exit();

    } else {

        echo "<script>alert('Unable to Update Category.');</script>";

    }
}

/* ============================
   Edit Category Form
============================= */

if (isset($_GET['edit'])) {

    $edit_id = (int)$_GET['edit'];

    $edit = mysqli_query($conn,"
        SELECT *
        FROM categories
        WHERE id='$edit_id'
    ");

    if (mysqli_num_rows($edit) > 0) {

        $editData = mysqli_fetch_assoc($edit);
?>

<div class="card shadow mt-4">

    <div class="card-header bg-warning">

        <h4 class="mb-0">Edit Category</h4>

    </div>

    <div class="card-body">

        <form method="POST">

            <input
                type="hidden"
                name="edit_id"
                value="<?php echo $editData['id']; ?>">

            <div class="row">

                <div class="col-md-5 mb-3">

                    <label class="form-label">

                        Category Name

                    </label>

                    <input
                        type="text"
                        name="category_name"
                        class="form-control"
                        value="<?php echo $editData['category_name']; ?>"
                        required>

                </div>

                <div class="col-md-5 mb-3">

                    <label class="form-label">

                        Category Type

                    </label>

                    <select
                        name="category_type"
                        class="form-select"
                        required>

                        <option value="Income"
                        <?php if($editData['category_type']=="Income") echo "selected"; ?>>

                            Income

                        </option>

                        <option value="Expense"
                        <?php if($editData['category_type']=="Expense") echo "selected"; ?>>

                            Expense

                        </option>

                    </select>

                </div>

                <div class="col-md-2 mb-3 d-flex align-items-end">

                    <button
                        type="submit"
                        name="update_category"
                        class="btn btn-warning w-100">

                        <i class="bi bi-pencil-square"></i>

                        Update

                    </button>

                </div>

            </div>

            <a href="categories.php" class="btn btn-secondary">

                Cancel

            </a>

        </form>

    </div>

</div>

<?php

    }

}

include("includes/footer.php");

?>