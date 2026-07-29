// C:\xampp\htdocs\NewProject\assets\js\main.js
// Client-side interactions, form validation, and UI behavior

document.addEventListener("DOMContentLoaded", function () {
    // -------------------------------------------------------------
    // 1. Password Match Validation (Register Form)
    // -------------------------------------------------------------
    const registerForm = document.getElementById("registerForm");
    if (registerForm) {
        const passwordInput = document.getElementById("password");
        const confirmPasswordInput = document.getElementById("confirm_password");

        registerForm.addEventListener("submit", function (event) {
            if (passwordInput.value !== confirmPasswordInput.value) {
                event.preventDefault();
                confirmPasswordInput.setCustomValidity("Passwords do not match");
                confirmPasswordInput.reportValidity();
            } else {
                confirmPasswordInput.setCustomValidity("");
            }
        });

        confirmPasswordInput.addEventListener("input", function () {
            if (passwordInput.value === confirmPasswordInput.value) {
                confirmPasswordInput.setCustomValidity("");
            }
        });
    }

    // -------------------------------------------------------------
    // 2. Interactive Star Rating System (Details Page Review Form)
    // -------------------------------------------------------------
    const starContainer = document.querySelector(".interactive-stars");
    if (starContainer) {
        const stars = starContainer.querySelectorAll(".bi-star, .bi-star-fill");
        const ratingInput = document.getElementById("rating_input");

        stars.forEach(star => {
            star.addEventListener("click", function () {
                const rating = this.getAttribute("data-rating");
                ratingInput.value = rating;

                // Toggle filled stars up to the clicked one
                stars.forEach(s => {
                    const r = s.getAttribute("data-rating");
                    if (r <= rating) {
                        s.classList.remove("bi-star");
                        s.classList.add("bi-star-fill");
                        s.classList.add("text-warning");
                    } else {
                        s.classList.remove("bi-star-fill");
                        s.classList.add("bi-star");
                        s.classList.remove("text-warning");
                    }
                });
            });

            // Hover effects
            star.addEventListener("mouseenter", function () {
                const hoverRating = this.getAttribute("data-rating");
                stars.forEach(s => {
                    if (s.getAttribute("data-rating") <= hoverRating) {
                        s.classList.add("text-warning");
                    } else {
                        s.classList.remove("text-warning");
                    }
                });
            });
        });

        // Restore active selection on mouse leave
        starContainer.addEventListener("mouseleave", function () {
            const currentRating = parseInt(ratingInput.value) || 0;
            stars.forEach(s => {
                const r = s.getAttribute("data-rating");
                if (r <= currentRating) {
                    s.classList.remove("bi-star");
                    s.classList.add("bi-star-fill");
                    s.classList.add("text-warning");
                } else {
                    s.classList.remove("bi-star-fill");
                    s.classList.add("bi-star");
                    s.classList.remove("text-warning");
                }
            });
        });
    }

    // -------------------------------------------------------------
    // 3. Generic Bootstrap Form Validation (Bootstrap 5 styles)
    // -------------------------------------------------------------
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

    // -------------------------------------------------------------
    // 4. Newsletter Signup Success Alert (AJAX/JS simulation)
    // -------------------------------------------------------------
    const newsletterForm = document.getElementById("newsletterForm");
    if (newsletterForm) {
        newsletterForm.addEventListener("submit", function (e) {
            e.preventDefault();
            const emailInput = this.querySelector('input[type="email"]');
            if (emailInput && emailInput.value) {
                alert("Thank you for subscribing to our newsletter! Get ready for amazing travel offers.");
                emailInput.value = "";
            }
        });
    }

    // -------------------------------------------------------------
    // 5. Dark / Light Theme Toggle Control
    // -------------------------------------------------------------
    const themeToggleBtn = document.getElementById("themeToggle");
    const themeIcon = document.getElementById("themeIcon");

    if (themeToggleBtn && themeIcon) {
        // Set initial icon state based on resolved theme
        const currentTheme = document.documentElement.getAttribute("data-bs-theme") || "light";
        updateThemeIcon(currentTheme);

        themeToggleBtn.addEventListener("click", function () {
            const currentTheme = document.documentElement.getAttribute("data-bs-theme") || "light";
            const newTheme = currentTheme === "light" ? "dark" : "light";
            
            document.documentElement.setAttribute("data-bs-theme", newTheme);
            localStorage.setItem("theme", newTheme);
            updateThemeIcon(newTheme);
        });
    }

    function updateThemeIcon(theme) {
        if (theme === "dark") {
            themeIcon.classList.remove("bi-moon-fill");
            themeIcon.classList.add("bi-sun-fill", "text-warning");
        } else {
            themeIcon.classList.remove("bi-sun-fill", "text-warning");
            themeIcon.classList.add("bi-moon-fill");
        }
    }
});
