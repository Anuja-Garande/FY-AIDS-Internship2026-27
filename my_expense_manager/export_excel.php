<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: auth/login.php");
    exit();
}

include("database/db.php");

$user_id = $_SESSION['user_id'];

$from = isset($_GET['from']) ? $_GET['from'] : date("Y-m-01");
$to   = isset($_GET['to']) ? $_GET['to'] : date("Y-m-d");

header("Content-Type: text/csv");
header("Content-Disposition: attachment; filename=Expense_Report.csv");

$output = fopen("php://output", "w");

/* Title */
fputcsv($output, array("PERSONAL EXPENSE TRACKER"));
fputcsv($output, array("Report From", $from, "To", $to));
fputcsv($output, array());

/* Income Section */
fputcsv($output, array("INCOME REPORT"));
fputcsv($output, array("ID", "Date", "Category", "Description", "Amount"));

$income = mysqli_query($conn,"
SELECT income.id,
       income.income_date,
       categories.category_name,
       income.description,
       income.amount
FROM income
INNER JOIN categories
ON income.category_id = categories.id
WHERE income.user_id='$user_id'
AND income.income_date BETWEEN '$from' AND '$to'
ORDER BY income.income_date DESC
");

$totalIncome = 0;

while($row = mysqli_fetch_assoc($income))
{
    fputcsv($output, array(
        $row['id'],
        $row['income_date'],
        $row['category_name'],
        $row['description'],
        $row['amount']
    ));

    $totalIncome += $row['amount'];
}

fputcsv($output, array("", "", "", "Total Income", $totalIncome));
fputcsv($output, array());

/* Expense Section */
fputcsv($output, array("EXPENSE REPORT"));
fputcsv($output, array("ID", "Date", "Category", "Description", "Amount"));

$expense = mysqli_query($conn,"
SELECT expenses.id,
       expenses.expense_date,
       categories.category_name,
       expenses.description,
       expenses.amount
FROM expenses
INNER JOIN categories
ON expenses.category_id = categories.id
WHERE expenses.user_id='$user_id'
AND expenses.expense_date BETWEEN '$from' AND '$to'
ORDER BY expenses.expense_date DESC
");

$totalExpense = 0;

while($row = mysqli_fetch_assoc($expense))
{
    fputcsv($output, array(
        $row['id'],
        $row['expense_date'],
        $row['category_name'],
        $row['description'],
        $row['amount']
    ));

    $totalExpense += $row['amount'];
}

fputcsv($output, array("", "", "", "Total Expense", $totalExpense));
fputcsv($output, array());

/* Summary */
$balance = $totalIncome - $totalExpense;

fputcsv($output, array("SUMMARY"));
fputcsv($output, array("Total Income", $totalIncome));
fputcsv($output, array("Total Expense", $totalExpense));
fputcsv($output, array("Balance", $balance));

fclose($output);
exit();
?>