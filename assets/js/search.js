document.addEventListener("DOMContentLoaded", function () {
    const searchInput = document.getElementById("bookSearch");
    const bookRows = document.querySelectorAll(".book-row");
    const resultMessage = document.getElementById("noResults");
    const totalResults = document.getElementById("totalResults");

    if (!searchInput || bookRows.length === 0) {
        return;
    }

    function searchBooks() {
        const searchText = searchInput.value.toLowerCase().trim();
        let visibleCount = 0;

        bookRows.forEach(function (row) {
            const title = row.dataset.title || "";
            const author = row.dataset.author || "";
            const isbn = row.dataset.isbn || "";
            const category = row.dataset.category || "";

            const bookText = (
                title + " " +
                author + " " +
                isbn + " " +
                category
            ).toLowerCase();

            if (bookText.includes(searchText)) {
                row.style.display = "";
                visibleCount++;
            } else {
                row.style.display = "none";
            }
        });

        if (resultMessage) {
            resultMessage.style.display = visibleCount === 0 ? "table-row" : "none";
        }

        if (totalResults) {
            totalResults.textContent = visibleCount + " book(s) found";
        }
    }

    searchInput.addEventListener("input", searchBooks);

    const clearButton = document.getElementById("clearSearch");

    if (clearButton) {
        clearButton.addEventListener("click", function () {
            searchInput.value = "";
            searchBooks();
            searchInput.focus();
        });
    }
});