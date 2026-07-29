<?php
// C:\xampp\htdocs\NewProject\admin\includes\admin_footer.php
// Admin Panel Footer layout closing tags

if (basename($_SERVER['PHP_SELF']) !== 'login.php'):
?>
            </main>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Bootstrap 5 JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- Admin Custom Scripts if any -->
<script>
// Toggle modal validations or helper functions for admin panel
document.addEventListener("DOMContentLoaded", function () {
    const validateForms = document.querySelectorAll(".needs-validation");
    Array.prototype.slice.call(validateForms).forEach(function (form) {
        form.addEventListener("submit", function (event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add("was-validated");
        }, false);
    });
});
</script>
</body>
</html>
