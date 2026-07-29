<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: auth/login.php");
    exit();
}

include("database/db.php");

$user_id = $_SESSION['user_id'];

/* ==========================
   TOTAL INCOME
========================== */
$income_query = mysqli_query($conn,"
SELECT SUM(amount) AS total_income
FROM income
WHERE user_id='$user_id'
");

$income_data = mysqli_fetch_assoc($income_query);
$total_income = $income_data['total_income'] ?? 0;


/* ==========================
   TOTAL EXPENSE
========================== */
$expense_query = mysqli_query($conn,"
SELECT SUM(amount) AS total_expense
FROM expenses
WHERE user_id='$user_id'
");

$expense_data = mysqli_fetch_assoc($expense_query);
$total_expense = $expense_data['total_expense'] ?? 0;


/* ==========================
   BALANCE
========================== */
$balance = $total_income - $total_expense;


/* ==========================
   INCOME COUNT
========================== */
$income_count = mysqli_query($conn,"
SELECT COUNT(*) AS total
FROM income
WHERE user_id='$user_id'
");

$income_count = mysqli_fetch_assoc($income_count);


/* ==========================
   EXPENSE COUNT
========================== */
$expense_count = mysqli_query($conn,"
SELECT COUNT(*) AS total
FROM expenses
WHERE user_id='$user_id'
");

$expense_count = mysqli_fetch_assoc($expense_count);

$total_transactions =
$income_count['total'] + $expense_count['total'];


/* ==========================
   HEADER
========================== */

include("includes/header.php");
?>

<div class="container-fluid">

    <!-- Page Heading -->

    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>

            <h2 class="fw-bold mb-1">
                Welcome,
                <?php echo $_SESSION['user_name']; ?>
            </h2>

            <p class="text-muted mb-0">
                Here's your financial overview.
            </p>

        </div>

    </div>

    <!-- Statistics Cards -->

    <div class="row g-4">

        <!-- Income -->

        <div class="col-xl-3 col-lg-6 col-md-6">

            <div class="stats-card success">

                <div class="d-flex justify-content-between align-items-center">

                    <div>

                        <h6>Total Income</h6>

                        <h2>
                            ₹ <?php echo number_format($total_income,2); ?>
                        </h2>

                        <small>Income Received</small>

                    </div>

                    <div class="stats-icon">

                        <i class="bi bi-cash-stack"></i>

                    </div>

                </div>

            </div>

        </div>

        <!-- Expense -->

        <div class="col-xl-3 col-lg-6 col-md-6">

            <div class="stats-card danger">

                <div class="d-flex justify-content-between align-items-center">

                    <div>

                        <h6>Total Expense</h6>

                        <h2>
                            ₹ <?php echo number_format($total_expense,2); ?>
                        </h2>

                        <small>Money Spent</small>

                    </div>

                    <div class="stats-icon">

                        <i class="bi bi-wallet-fill"></i>

                    </div>

                </div>

            </div>

        </div>

        <!-- Balance -->

        <div class="col-xl-3 col-lg-6 col-md-6">

            <div class="stats-card primary">

                <div class="d-flex justify-content-between align-items-center">

                    <div>

                        <h6>Current Balance</h6>

                        <h2>
                            ₹ <?php echo number_format($balance,2); ?>
                        </h2>

                        <small>Available Amount</small>

                    </div>

                    <div class="stats-icon">

                        <i class="bi bi-bank"></i>

                    </div>

                </div>

            </div>

        </div>

        <!-- Transactions -->

        <div class="col-xl-3 col-lg-6 col-md-6">

            <div class="stats-card warning">

                <div class="d-flex justify-content-between align-items-center">

                    <div>

                        <h6>Transactions</h6>

                        <h2>

                            <?php echo $total_transactions; ?>

                        </h2>

                        <small>Total Records</small>

                    </div>

                    <div class="stats-icon">

                        <i class="bi bi-receipt"></i>

                    </div>

                </div>

            </div>

        </div>

    </div>

    <!-- Monthly Summary Starts -->    <!-- ==========================
         MONTHLY SUMMARY
    =========================== -->

    <div class="row mt-4">

        <!-- Monthly Income -->

        <div class="col-lg-6 mb-4">

            <div class="card shadow border-0 rounded-4">

                <div class="card-header bg-success text-white rounded-top-4">

                    <h5 class="mb-0">
                        <i class="bi bi-cash-stack me-2"></i>
                        This Month Income
                    </h5>

                </div>

                <div class="card-body">

                    <?php

                    $currentMonth = date("m");
                    $currentYear  = date("Y");

                    $monthIncome = mysqli_query($conn,"
                        SELECT SUM(amount) AS total
                        FROM income
                        WHERE user_id='$user_id'
                        AND MONTH(income_date)='$currentMonth'
                        AND YEAR(income_date)='$currentYear'
                    ");

                    $monthIncome = mysqli_fetch_assoc($monthIncome);

                    ?>

                    <h2 class="text-success fw-bold">

                        ₹ <?php echo number_format($monthIncome['total'] ?? 0,2); ?>

                    </h2>

                    <p class="text-muted mb-0">
                        Total income received this month.
                    </p>

                </div>

            </div>

        </div>

        <!-- Monthly Expense -->

        <div class="col-lg-6 mb-4">

            <div class="card shadow border-0 rounded-4">

                <div class="card-header bg-danger text-white rounded-top-4">

                    <h5 class="mb-0">
                        <i class="bi bi-wallet2 me-2"></i>
                        This Month Expenses
                    </h5>

                </div>

                <div class="card-body">

                    <?php

                    $monthExpense = mysqli_query($conn,"
                        SELECT SUM(amount) AS total
                        FROM expenses
                        WHERE user_id='$user_id'
                        AND MONTH(expense_date)='$currentMonth'
                        AND YEAR(expense_date)='$currentYear'
                    ");

                    $monthExpense = mysqli_fetch_assoc($monthExpense);

                    ?>

                    <h2 class="text-danger fw-bold">

                        ₹ <?php echo number_format($monthExpense['total'] ?? 0,2); ?>

                    </h2>

                    <p class="text-muted mb-0">
                        Total expenses this month.
                    </p>

                </div>

            </div>

        </div>

    </div>

    <!-- ==========================
         CHARTS
    =========================== -->

    <div class="row">

        <div class="col-lg-6 mb-4">

            <div class="card shadow border-0 rounded-4">

                <div class="card-header bg-primary text-white rounded-top-4">

                    <h5 class="mb-0">
                        <i class="bi bi-bar-chart-fill me-2"></i>
                        Income vs Expense
                    </h5>

                </div>

                <div class="card-body">

                    <canvas id="expenseChart"></canvas>

                </div>

            </div>

        </div>

        <div class="col-lg-6 mb-4">

            <div class="card shadow border-0 rounded-4">

                <div class="card-header bg-warning rounded-top-4">

                    <h5 class="mb-0">
                        <i class="bi bi-pie-chart-fill me-2"></i>
                        Balance Overview
                    </h5>

                </div>

                <div class="card-body">

                    <canvas id="balanceChart"></canvas>

                </div>

            </div>

        </div>

    </div>

    <!-- ==========================
         RECENT INCOME
    =========================== -->

    <div class="card shadow border-0 rounded-4 mb-4">

        <div class="card-header bg-success text-white rounded-top-4">

            <h5 class="mb-0">

                <i class="bi bi-clock-history me-2"></i>

                Recent Income

            </h5>

        </div>

        <div class="card-body">

            <div class="table-responsive">

                <table class="table table-hover align-middle">

                    <thead>

                        <tr>

                            <th>ID</th>
                            <th>Amount</th>
                            <th>Date</th>
                            <th>Description</th>

                        </tr>

                    </thead>

                    <tbody>

                    <?php

                    $incomeList = mysqli_query($conn,"
                        SELECT *
                        FROM income
                        WHERE user_id='$user_id'
                        ORDER BY id DESC
                        LIMIT 5
                    ");

                    while($row=mysqli_fetch_assoc($incomeList))
                    {

                    ?>

                        <tr>

                            <td><?php echo $row['id']; ?></td>

                            <td class="text-success fw-bold">
                                ₹ <?php echo number_format($row['amount'],2); ?>
                            </td>

                            <td><?php echo $row['income_date']; ?></td>

                            <td><?php echo $row['description']; ?></td>

                        </tr>

                    <?php } ?>

                    </tbody>

                </table>

            </div>

        </div>

    </div>

    <!-- ==========================
         RECENT EXPENSE
    =========================== -->

    <div class="card shadow border-0 rounded-4 mb-4">

        <div class="card-header bg-danger text-white rounded-top-4">

            <h5 class="mb-0">

                <i class="bi bi-clock-history me-2"></i>

                Recent Expenses

            </h5>

        </div>

        <div class="card-body">

            <div class="table-responsive">

                <table class="table table-hover align-middle">

                    <thead>

                        <tr>

                            <th>ID</th>
                            <th>Amount</th>
                            <th>Date</th>
                            <th>Description</th>

                        </tr>

                    </thead>

                    <tbody>

                    <?php

                    $expenseList = mysqli_query($conn,"
                        SELECT *
                        FROM expenses
                        WHERE user_id='$user_id'
                        ORDER BY id DESC
                        LIMIT 5
                    ");

                    while($row=mysqli_fetch_assoc($expenseList))
                    {

                    ?>

                        <tr>

                            <td><?php echo $row['id']; ?></td>

                            <td class="text-danger fw-bold">
                                ₹ <?php echo number_format($row['amount'],2); ?>
                            </td>

                            <td><?php echo $row['expense_date']; ?></td>

                            <td><?php echo $row['description']; ?></td>

                        </tr>

                    <?php } ?>

                    </tbody>

                </table>

            </div>

        </div>

    </div>    <!-- ==========================
         CHART.JS
    =========================== -->

</div> <!-- End container-fluid -->

<script>

const income = <?php echo (float)$total_income; ?>;
const expense = <?php echo (float)$total_expense; ?>;
const balance = <?php echo (float)$balance; ?>;

/* ==========================
   Income vs Expense
========================== */

new Chart(document.getElementById("expenseChart"),{

    type:"bar",

    data:{

        labels:["Income","Expense"],

        datasets:[{

            label:"Amount (₹)",

            data:[income,expense],

            backgroundColor:[
                "#198754",
                "#dc3545"
            ],

            borderRadius:12

        }]

    },

    options:{

        responsive:true,

        maintainAspectRatio:false,

        plugins:{
            legend:{
                display:false
            }
        },

        scales:{
            y:{
                beginAtZero:true
            }
        }

    }

});


/* ==========================
   Balance Chart
========================== */

new Chart(document.getElementById("balanceChart"),{

    type:"doughnut",

    data:{

        labels:["Income","Expense","Balance"],

        datasets:[{

            data:[income,expense,balance],

            backgroundColor:[
                "#198754",
                "#dc3545",
                "#0d6efd"
            ],

            hoverOffset:12

        }]

    },

    options:{

        responsive:true,

        maintainAspectRatio:false,

        plugins:{

            legend:{

                position:"bottom"

            }

        }

    }

});

</script>

<?php include("includes/footer.php"); ?>