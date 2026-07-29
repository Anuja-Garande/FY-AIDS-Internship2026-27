<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: auth/login.php");
    exit();
}

include("database/db.php");

$user_id = $_SESSION['user_id'];

include("includes/header.php");
include("includes/sidebar.php");

/* ============================
   Report Filters
============================ */

$from = isset($_GET['from']) ? $_GET['from'] : date("Y-m-01");
$to   = isset($_GET['to']) ? $_GET['to'] : date("Y-m-d");

/* ============================
   Summary
============================ */

$incomeQuery = mysqli_query($conn,"
SELECT IFNULL(SUM(amount),0) total
FROM income
WHERE user_id='$user_id'
AND income_date BETWEEN '$from' AND '$to'
");

$totalIncome = mysqli_fetch_assoc($incomeQuery)['total'];

$expenseQuery = mysqli_query($conn,"
SELECT IFNULL(SUM(amount),0) total
FROM expenses
WHERE user_id='$user_id'
AND expense_date BETWEEN '$from' AND '$to'
");

$totalExpense = mysqli_fetch_assoc($expenseQuery)['total'];

$balance = $totalIncome - $totalExpense;
?>

<div class="container-fluid">

    <h2 class="mb-4 fw-bold">
        Reports
    </h2>

    <!-- Filter Card -->

    <div class="card shadow mb-4">

        <div class="card-header bg-primary text-white">

            <h5 class="mb-0">
                Filter Report
            </h5>

        </div>

        <div class="card-body">

            <form method="GET">

                <div class="row">

                    <div class="col-md-4">

                        <label>From</label>

                        <input
                            type="date"
                            name="from"
                            class="form-control"
                            value="<?php echo $from; ?>">

                    </div>

                    <div class="col-md-4">

                        <label>To</label>

                        <input
                            type="date"
                            name="to"
                            class="form-control"
                            value="<?php echo $to; ?>">

                    </div>

                    <div class="col-md-4 d-flex align-items-end">

                        <button
                            type="submit"
                            class="btn btn-primary w-100">

                            Generate Report

                        </button>

                    </div>

                </div>

            </form>

        </div>

    </div>

    <!-- Summary Cards -->

    <div class="row">

        <div class="col-md-4">

            <div class="card border-success shadow">

                <div class="card-body">

                    <h5>Total Income</h5>

                    <h2 class="text-success">

                        ₹ <?php echo number_format($totalIncome,2); ?>

                    </h2>

                </div>

            </div>

        </div>

        <div class="col-md-4">

            <div class="card border-danger shadow">

                <div class="card-body">

                    <h5>Total Expense</h5>

                    <h2 class="text-danger">

                        ₹ <?php echo number_format($totalExpense,2); ?>

                    </h2>

                </div>

            </div>

        </div>

        <div class="col-md-4">

            <div class="card border-primary shadow">

                <div class="card-body">

                    <h5>Balance</h5>

                    <h2 class="text-primary">

                        ₹ <?php echo number_format($balance,2); ?>

                    </h2>

                </div>

            </div>

        </div>

    </div>

    <br>

    <!-- Income Report -->

    <div class="card shadow mb-4">

        <div class="card-header bg-success text-white">

            <h4 class="mb-0">
                Income Report
            </h4>

        </div>

        <div class="card-body">

            <div class="table-responsive">

                <table class="table table-bordered table-hover">

                    <thead class="table-success">

                        <tr>

                            <th>ID</th>
                            <th>Date</th>
                            <th>Category</th>
                            <th>Description</th>
                            <th>Amount</th>

                        </tr>

                    </thead>

                    <tbody>
                        <?php

$incomeReport = mysqli_query($conn,"
SELECT income.*,
categories.category_name

FROM income

INNER JOIN categories

ON income.category_id = categories.id

WHERE income.user_id = '$user_id'

AND income.income_date
BETWEEN '$from' AND '$to'

ORDER BY income.income_date DESC
");

if(mysqli_num_rows($incomeReport) > 0)
{

    while($row = mysqli_fetch_assoc($incomeReport))
    {

?>

<tr>

    <td><?php echo $row['id']; ?></td>

    <td><?php echo $row['income_date']; ?></td>

    <td><?php echo $row['category_name']; ?></td>

    <td><?php echo $row['description']; ?></td>

    <td class="text-success fw-bold">

        ₹ <?php echo number_format($row['amount'],2); ?>

    </td>

</tr>

<?php

    }

}

else

{

?>

<tr>

    <td colspan="5" class="text-center text-danger">

        No Income Found

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

    <!-- Expense Report -->
     <!-- Expense Report -->

<div class="card shadow mb-4">

    <div class="card-header bg-danger text-white">

        <h4 class="mb-0">

            Expense Report

        </h4>

    </div>

    <div class="card-body">

        <div class="table-responsive">

            <table class="table table-bordered table-hover">

                <thead class="table-danger">

                    <tr>

                        <th>ID</th>
                        <th>Date</th>
                        <th>Category</th>
                        <th>Description</th>
                        <th>Amount</th>

                    </tr>

                </thead>

                <tbody>

<?php

$expenseReport = mysqli_query($conn,"
SELECT expenses.*,
categories.category_name

FROM expenses

INNER JOIN categories

ON expenses.category_id = categories.id

WHERE expenses.user_id = '$user_id'

AND expenses.expense_date
BETWEEN '$from' AND '$to'

ORDER BY expenses.expense_date DESC
");

if(mysqli_num_rows($expenseReport) > 0)
{

    while($row = mysqli_fetch_assoc($expenseReport))
    {

?>

<tr>

    <td><?php echo $row['id']; ?></td>

    <td><?php echo $row['expense_date']; ?></td>

    <td><?php echo $row['category_name']; ?></td>

    <td><?php echo $row['description']; ?></td>

    <td class="text-danger fw-bold">

        ₹ <?php echo number_format($row['amount'],2); ?>

    </td>

</tr>

<?php

    }

}

else

{

?>

<tr>

    <td colspan="5" class="text-center text-danger">

        No Expense Found

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

<!-- Charts -->
 <!-- Charts -->

<div class="row">

    <div class="col-lg-6 mb-4">

        <div class="card shadow">

            <div class="card-header bg-info text-white">

                Income vs Expense

            </div>

            <div class="card-body">

                <canvas id="incomeExpenseChart"></canvas>

            </div>

        </div>

    </div>

    <div class="col-lg-6 mb-4">

        <div class="card shadow">

            <div class="card-header bg-secondary text-white">

                Balance Overview

            </div>

            <div class="card-body">

                <canvas id="balanceChart"></canvas>

            </div>

        </div>

    </div>

</div>

<br>

<!-- Export Buttons -->

<div class="mb-4">

    <a href="export_excel.php?from=<?php echo $from; ?>&to=<?php echo $to; ?>"
       class="btn btn-success">

        <i class="bi bi-file-earmark-excel"></i>

        Export Excel

    </a>

    <button
        type="button"
        class="btn btn-danger"
        onclick="window.print();">

        <i class="bi bi-file-earmark-pdf"></i>

        Print / Save as PDF

    </button>

</div>

<script>
    const incomeExpenseChart = new Chart(
    document.getElementById('incomeExpenseChart'),
    {
        type: 'bar',

        data: {
            labels: [
                'Income',
                'Expense'
            ],

            datasets: [{
                label: 'Amount',

                data: [
                    <?php echo $totalIncome; ?>,
                    <?php echo $totalExpense; ?>
                ]
            }]
        },

        options: {
            responsive: true,

            plugins: {
                legend: {
                    display: false
                }
            }
        }

    }
);

const balanceChart = new Chart(
    document.getElementById('balanceChart'),
    {
        type: 'doughnut',

        data: {

            labels: [
                'Expense',
                'Remaining Balance'
            ],

            datasets: [{

                data: [
                    <?php echo $totalExpense; ?>,
                    <?php echo max($balance,0); ?>
                ]

            }]

        },

        options: {
            responsive: true
        }

    }
);

</script>

<?php

include("includes/footer.php");

?>