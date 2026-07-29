<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: auth/login.php");
    exit();
}

include("database/db.php");

$user_id = $_SESSION['user_id'];

/* Total Income */
$incomeQuery = mysqli_query($conn, "
SELECT IFNULL(SUM(amount),0) AS total
FROM income
WHERE user_id='$user_id'
");
$totalIncome = mysqli_fetch_assoc($incomeQuery)['total'];

/* Total Expense */
$expenseQuery = mysqli_query($conn, "
SELECT IFNULL(SUM(amount),0) AS total
FROM expenses
WHERE user_id='$user_id'
");
$totalExpense = mysqli_fetch_assoc($expenseQuery)['total'];

$balance = $totalIncome - $totalExpense;

include("includes/header.php");
include("includes/sidebar.php");
?>

<div class="container-fluid">

    <h2 class="mb-4 fw-bold">
        Dashboard
    </h2>

    <div class="row">

        <div class="col-md-4 mb-4">

            <div class="card border-success shadow">

                <div class="card-body text-center">

                    <h5>Total Income</h5>

                    <h2 class="text-success">
                        ₹ <?php echo number_format($totalIncome,2); ?>
                    </h2>

                </div>

            </div>

        </div>

        <div class="col-md-4 mb-4">

            <div class="card border-danger shadow">

                <div class="card-body text-center">

                    <h5>Total Expense</h5>

                    <h2 class="text-danger">
                        ₹ <?php echo number_format($totalExpense,2); ?>
                    </h2>

                </div>

            </div>

        </div>

        <div class="col-md-4 mb-4">

            <div class="card border-primary shadow">

                <div class="card-body text-center">

                    <h5>Balance</h5>

                    <h2 class="text-primary">
                        ₹ <?php echo number_format($balance,2); ?>
                    </h2>

                </div>

            </div>

        </div>

    </div>

    <div class="card shadow">

        <div class="card-header bg-dark text-white">

            <h5 class="mb-0">Quick Actions</h5>

        </div>

        <div class="card-body">

            <a href="income.php" class="btn btn-success me-2">
                Add Income
            </a>

            <a href="expenses.php" class="btn btn-danger me-2">
                Add Expense
            </a>

            <a href="categories.php" class="btn btn-warning me-2">
                Categories
            </a>

            <a href="reports.php" class="btn btn-info text-white me-2">
                Reports
            </a>

            <a href="profile.php" class="btn btn-secondary me-2">
                Profile
            </a>

            <a href="settings.php" class="btn btn-dark">
                Settings
            </a>

        </div>

    </div>

</div>

<?php
include("includes/footer.php");
?>