</div>

<!-- Modern Footer -->
<footer class="footer mt-5">
    <div class="container-fluid">
        <div class="row align-items-center">

            <div class="col-md-6 text-center text-md-start">
                <h5 class="mb-1">
                    💰 My Expense Manager
                </h5>

                <small>
                    Manage your income, expenses and savings efficiently.
                </small>
            </div>

            <div class="col-md-6 text-center text-md-end mt-3 mt-md-0">

                <a href="dashboard.php" class="footer-link">Dashboard</a>

                <a href="income.php" class="footer-link">Income</a>

                <a href="expenses.php" class="footer-link">Expenses</a>

                <a href="reports.php" class="footer-link">Reports</a>

            </div>

        </div>

        <hr class="footer-line">

        <div class="text-center">

            © <span id="year"></span>
            <strong>My Expense Manager</strong>

            <br>

            <small>
               
            </small>

        </div>

    </div>
</footer>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- Custom JS -->
<script src="assets/js/script.js"></script>

<script>
document.getElementById("year").innerHTML = new Date().getFullYear();
</script>

</body>
</html>