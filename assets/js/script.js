document.addEventListener("DOMContentLoaded", function () {
    /* Mobile navigation toggle */
    const menuButton = document.querySelector(".menu-toggle");
    const navLinks = document.querySelector(".nav-links");

    if (menuButton && navLinks) {
        menuButton.addEventListener("click", function () {
            navLinks.classList.toggle("show");

            const icon = menuButton.querySelector("i");

            if (icon) {
                icon.classList.toggle("fa-bars");
                icon.classList.toggle("fa-xmark");
            }
        });
    }

    /* Close mobile navigation after clicking a navigation link */
    document.querySelectorAll(".nav-links a").forEach(function (link) {
        link.addEventListener("click", function () {
            if (navLinks) {
                navLinks.classList.remove("show");
            }
        });
    });

    /* Confirm delete buttons/forms */
    document.querySelectorAll(".delete-form").forEach(function (form) {
        form.addEventListener("submit", function (event) {
            const confirmed = confirm("Are you sure you want to delete this record?");

            if (!confirmed) {
                event.preventDefault();
            }
        });
    });

    /* Automatically hide success/error messages */
    document.querySelectorAll(".message, .alert, .notice").forEach(function (message) {
        setTimeout(function () {
            message.style.transition = "opacity 0.4s ease";
            message.style.opacity = "0";

            setTimeout(function () {
                message.remove();
            }, 400);
        }, 4000);
    });

    /* Prevent available quantity from becoming greater than total quantity */
    const bookForm = document.querySelector("#bookForm");

    if (bookForm) {
        bookForm.addEventListener("submit", function (event) {
            const quantityField = bookForm.querySelector('[name="quantity"]');
            const availableField = bookForm.querySelector('[name="available_quantity"]');

            if (quantityField && availableField) {
                const quantity = Number(quantityField.value);
                const available = Number(availableField.value);

                if (available > quantity) {
                    event.preventDefault();
                    alert("Available quantity cannot be greater than total quantity.");
                    availableField.focus();
                }
            }
        });
    }

    /* Password confirmation validation */
    const passwordForm = document.querySelector("#settingsForm");

    if (passwordForm) {
        passwordForm.addEventListener("submit", function (event) {
            const currentPassword = passwordForm.querySelector('[name="current_password"]');
            const newPassword = passwordForm.querySelector('[name="new_password"]');
            const confirmPassword = passwordForm.querySelector('[name="confirm_password"]');

            if (!currentPassword || !newPassword || !confirmPassword) {
                return;
            }

            if (
                currentPassword.value !== "" ||
                newPassword.value !== "" ||
                confirmPassword.value !== ""
            ) {
                if (currentPassword.value === "") {
                    event.preventDefault();
                    alert("Please enter your current password.");
                    currentPassword.focus();
                    return;
                }

                if (newPassword.value.length < 6) {
                    event.preventDefault();
                    alert("New password must contain at least 6 characters.");
                    newPassword.focus();
                    return;
                }

                if (newPassword.value !== confirmPassword.value) {
                    event.preventDefault();
                    alert("New password and confirm password do not match.");
                    confirmPassword.focus();
                }
            }
        });
    }

    /* Contact form basic validation */
    const contactForm = document.querySelector("#contactForm");

    if (contactForm) {
        contactForm.addEventListener("submit", function (event) {
            const name = contactForm.querySelector('[name="name"]');
            const email = contactForm.querySelector('[name="email"]');
            const message = contactForm.querySelector('[name="message"]');

            if (!name.value.trim() || !email.value.trim() || !message.value.trim()) {
                event.preventDefault();
                alert("Please complete all required fields.");
                return;
            }

            const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

            if (!emailPattern.test(email.value.trim())) {
                event.preventDefault();
                alert("Please enter a valid email address.");
                email.focus();
            }
        });
    }
});