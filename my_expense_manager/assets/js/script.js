// ==========================================
// Personal Expense Tracker
// Custom JavaScript
// ==========================================

// Sidebar Active Link
document.addEventListener("DOMContentLoaded", function () {

    const currentPage = window.location.pathname.split("/").pop();

    const links = document.querySelectorAll(".sidebar .nav-link");

    links.forEach(function (link) {

        if (link.getAttribute("href") === currentPage) {

            link.classList.add("active");

        }

    });

});

// Delete Confirmation
function confirmDelete() {

    return confirm("Are you sure you want to delete this record?");

}

// Auto Close Alerts
document.addEventListener("DOMContentLoaded", function () {

    const alerts = document.querySelectorAll(".alert");

    alerts.forEach(function (alert) {

        setTimeout(function () {

            alert.style.transition = "0.5s";
            alert.style.opacity = "0";

            setTimeout(function () {

                alert.remove();

            }, 500);

        }, 3000);

    });

});

// Number Animation
document.addEventListener("DOMContentLoaded", function () {

    const counters = document.querySelectorAll(".counter");

    counters.forEach(function (counter) {

        const target = Number(counter.innerText);

        let count = 0;

        const speed = target / 80;

        function updateCounter() {

            if (count < target) {

                count += speed;

                counter.innerText = Math.ceil(count);

                requestAnimationFrame(updateCounter);

            } else {

                counter.innerText = target;

            }

        }

        if (!isNaN(target)) {

            updateCounter();

        }

    });

});

// Back to Top Button
const topButton = document.createElement("button");

topButton.innerHTML = "↑";

topButton.id = "topButton";

topButton.style.position = "fixed";
topButton.style.bottom = "20px";
topButton.style.right = "20px";
topButton.style.display = "none";
topButton.style.padding = "10px 15px";
topButton.style.border = "none";
topButton.style.borderRadius = "50%";
topButton.style.cursor = "pointer";
topButton.style.background = "#0d6efd";
topButton.style.color = "#fff";
topButton.style.fontSize = "20px";

document.body.appendChild(topButton);

window.addEventListener("scroll", function () {

    if (window.scrollY > 200) {

        topButton.style.display = "block";

    } else {

        topButton.style.display = "none";

    }

});

topButton.addEventListener("click", function () {

    window.scrollTo({

        top: 0,

        behavior: "smooth"

    });

});

// Print Report
function printReport() {

    window.print();

}

console.log("Personal Expense Tracker Loaded Successfully");