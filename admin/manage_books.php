<?php
session_start();

require_once __DIR__ . '/../includes/db_connect.php';

if (empty($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit;
}

if (!isset($conn) || !($conn instanceof mysqli)) {
    exit('Database connection failed. Please check includes/db_connect.php');
}

function e($value)
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function setFlash($type, $message)
{
    $_SESSION['book_flash'] = [
        'type' => $type,
        'message' => $message
    ];
}

function getFlash()
{
    $flash = $_SESSION['book_flash'] ?? null;
    unset($_SESSION['book_flash']);
    return $flash;
}

function redirectToBooks()
{
    header('Location: manage_books.php');
    exit;
}

/* Fetch categories for the add/edit form */
$categories = [];
$categoryQuery = $conn->query("SELECT category_id, category_name FROM categories ORDER BY category_name ASC");

if ($categoryQuery) {
    while ($category = $categoryQuery->fetch_assoc()) {
        $categories[] = $category;
    }
}

/* Add, edit, and delete actions */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add' || $action === 'edit') {
        $bookId = (int)($_POST['book_id'] ?? 0);
        $title = trim($_POST['title'] ?? '');
        $author = trim($_POST['author'] ?? '');
        $isbn = trim($_POST['isbn'] ?? '');
        $categoryId = (int)($_POST['category_id'] ?? 0);
        $publisher = trim($_POST['publisher'] ?? '');
        $publicationYear = (int)($_POST['publication_year'] ?? 0);
        $quantity = (int)($_POST['quantity'] ?? 0);
        $availableQuantity = (int)($_POST['available_quantity'] ?? 0);

        if (
            $title === '' ||
            $author === '' ||
            $isbn === '' ||
            $categoryId <= 0 ||
            $quantity < 0 ||
            $availableQuantity < 0 ||
            $availableQuantity > $quantity
        ) {
            setFlash('error', 'Please fill all required fields correctly. Available quantity cannot be greater than total quantity.');
            redirectToBooks();
        }

        $categoryCheck = $conn->prepare("SELECT category_id FROM categories WHERE category_id = ?");
        $categoryCheck->bind_param("i", $categoryId);
        $categoryCheck->execute();
        $categoryExists = $categoryCheck->get_result()->num_rows > 0;
        $categoryCheck->close();

        if (!$categoryExists) {
            setFlash('error', 'Selected category does not exist.');
            redirectToBooks();
        }

        if ($action === 'add') {
            $isbnCheck = $conn->prepare("SELECT book_id FROM books WHERE isbn = ?");
            $isbnCheck->bind_param("s", $isbn);
            $isbnCheck->execute();

            if ($isbnCheck->get_result()->num_rows > 0) {
                $isbnCheck->close();
                setFlash('error', 'A book with this ISBN already exists.');
                redirectToBooks();
            }

            $isbnCheck->close();

            $stmt = $conn->prepare(
                "INSERT INTO books
                (title, author, isbn, category_id, publisher, publication_year, quantity, available_quantity)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
            );

            $stmt->bind_param(
                "sssisiii",
                $title,
                $author,
                $isbn,
                $categoryId,
                $publisher,
                $publicationYear,
                $quantity,
                $availableQuantity
            );

            if ($stmt->execute()) {
                setFlash('success', 'Book added successfully.');
            } else {
                setFlash('error', 'Unable to add the book. Please try again.');
            }

            $stmt->close();
            redirectToBooks();
        }

        if ($action === 'edit') {
            if ($bookId <= 0) {
                setFlash('error', 'Invalid book selected.');
                redirectToBooks();
            }

            $isbnCheck = $conn->prepare("SELECT book_id FROM books WHERE isbn = ? AND book_id != ?");
            $isbnCheck->bind_param("si", $isbn, $bookId);
            $isbnCheck->execute();

            if ($isbnCheck->get_result()->num_rows > 0) {
                $isbnCheck->close();
                setFlash('error', 'Another book already uses this ISBN.');
                redirectToBooks();
            }

            $isbnCheck->close();

            $stmt = $conn->prepare(
                "UPDATE books
                SET title = ?, author = ?, isbn = ?, category_id = ?, publisher = ?,
                    publication_year = ?, quantity = ?, available_quantity = ?
                WHERE book_id = ?"
            );

            $stmt->bind_param(
                "sssisiiii",
                $title,
                $author,
                $isbn,
                $categoryId,
                $publisher,
                $publicationYear,
                $quantity,
                $availableQuantity,
                $bookId
            );

            if ($stmt->execute()) {
                setFlash('success', 'Book updated successfully.');
            } else {
                setFlash('error', 'Unable to update the book.');
            }

            $stmt->close();
            redirectToBooks();
        }
    }

    if ($action === 'delete') {
        $bookId = (int)($_POST['book_id'] ?? 0);

        if ($bookId <= 0) {
            setFlash('error', 'Invalid book selected.');
            redirectToBooks();
        }

        $issueCheck = $conn->prepare(
            "SELECT issue_id
             FROM issue_books
             WHERE book_id = ? AND status = 'Issued'"
        );

        $issueCheck->bind_param("i", $bookId);
        $issueCheck->execute();

        if ($issueCheck->get_result()->num_rows > 0) {
            $issueCheck->close();
            setFlash('error', 'This book cannot be deleted because it is currently issued to a student.');
            redirectToBooks();
        }

        $issueCheck->close();

        $stmt = $conn->prepare("DELETE FROM books WHERE book_id = ?");
        $stmt->bind_param("i", $bookId);

        if ($stmt->execute() && $stmt->affected_rows > 0) {
            setFlash('success', 'Book deleted successfully.');
        } else {
            setFlash('error', 'Book was not found or could not be deleted.');
        }

        $stmt->close();
        redirectToBooks();
    }
}

/* Edit book details */
$editBook = null;

if (isset($_GET['edit'])) {
    $editId = (int)$_GET['edit'];

    if ($editId > 0) {
        $stmt = $conn->prepare("SELECT * FROM books WHERE book_id = ?");
        $stmt->bind_param("i", $editId);
        $stmt->execute();
        $editBook = $stmt->get_result()->fetch_assoc();
        $stmt->close();
    }
}

/* Search books */
$search = trim($_GET['search'] ?? '');
$books = null;

if ($search !== '') {
    $searchValue = '%' . $search . '%';

    $stmt = $conn->prepare(
        "SELECT b.*, c.category_name
         FROM books b
         LEFT JOIN categories c ON b.category_id = c.category_id
         WHERE b.title LIKE ?
            OR b.author LIKE ?
            OR b.isbn LIKE ?
            OR b.publisher LIKE ?
            OR c.category_name LIKE ?
         ORDER BY b.book_id DESC"
    );

    $stmt->bind_param(
        "sssss",
        $searchValue,
        $searchValue,
        $searchValue,
        $searchValue,
        $searchValue
    );

    $stmt->execute();
    $books = $stmt->get_result();
} else {
    $books = $conn->query(
        "SELECT b.*, c.category_name
         FROM books b
         LEFT JOIN categories c ON b.category_id = c.category_id
         ORDER BY b.book_id DESC"
    );
}

$flash = getFlash();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Books | Digital Library</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f4f7fb;
            color: #1e293b;
        }

        .topbar {
            background: #12355b;
            color: white;
            padding: 15px 5%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
        }

        .topbar a {
            color: white;
            text-decoration: none;
        }

        .brand {
            font-size: 20px;
            font-weight: bold;
        }

        .layout {
            display: flex;
            min-height: calc(100vh - 56px);
        }

        .sidebar {
            width: 245px;
            background: white;
            padding: 20px 12px;
            box-shadow: 1px 0 8px rgba(0, 0, 0, 0.08);
        }

        .sidebar a {
            display: block;
            padding: 12px 14px;
            margin: 4px 0;
            border-radius: 7px;
            color: #475569;
            text-decoration: none;
        }

        .sidebar a:hover,
        .sidebar a.active {
            background: #e5f0fb;
            color: #0f4c81;
            font-weight: bold;
        }

        .sidebar i {
            width: 22px;
        }

        .main-content {
            flex: 1;
            padding: 30px;
            max-width: 1500px;
        }

        .page-heading,
        .toolbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
        }

        .card {
            background: white;
            border-radius: 12px;
            padding: 22px;
            margin-top: 20px;
            box-shadow: 0 2px 12px rgba(30, 41, 59, 0.1);
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 15px;
        }

        label {
            display: block;
            margin-bottom: 6px;
            font-weight: bold;
        }

        input,
        select {
            width: 100%;
            padding: 10px;
            border: 1px solid #cbd5e1;
            border-radius: 7px;
            font-size: 14px;
        }

        .btn {
            display: inline-block;
            border: none;
            border-radius: 7px;
            padding: 10px 14px;
            cursor: pointer;
            text-decoration: none;
            color: white;
            background: #1464a0;
            font-size: 14px;
        }

        .btn:hover {
            opacity: 0.9;
        }

        .btn-secondary {
            background: #64748b;
        }

        .btn-danger {
            background: #c0392b;
        }

        .message {
            padding: 13px 15px;
            border-radius: 7px;
            margin: 18px 0;
        }

        .success {
            background: #dcfce7;
            color: #166534;
        }

        .error {
            background: #fee2e2;
            color: #991b1b;
        }

        .search-form {
            display: flex;
            gap: 8px;
            width: 100%;
            max-width: 500px;
        }

        .search-form input {
            flex: 1;
        }

        .table-wrap {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        th,
        td {
            padding: 12px 9px;
            border-bottom: 1px solid #e2e8f0;
            text-align: left;
            vertical-align: top;
        }

        th {
            background: #f8fafc;
        }

        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 20px;
            background: #e0f2fe;
            color: #075985;
            font-size: 12px;
        }

        .action-buttons {
            white-space: nowrap;
        }

        .action-buttons .btn {
            padding: 7px 9px;
            margin: 1px;
        }

        @media (max-width: 900px) {
            .sidebar {
                display: none;
            }

            .main-content {
                padding: 18px;
            }

            .form-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 600px) {
            .topbar {
                padding: 14px;
            }

            .brand {
                font-size: 16px;
            }

            .page-heading,
            .toolbar,
            .search-form {
                flex-direction: column;
                align-items: stretch;
            }

            .form-grid {
                grid-template-columns: 1fr;
            }

            table {
                font-size: 13px;
            }
        }
    </style>
</head>

<body>

<header class="topbar">
    <a class="brand" href="dashboard.php">
        <i class="fa-solid fa-book-open"></i> Digital Library
    </a>

    <a href="../login.php">
        <i class="fa-solid fa-right-from-bracket"></i> Logout
    </a>
</header>

<div class="layout">

    <aside class="sidebar">
        <a href="dashboard.php"><i class="fa-solid fa-gauge"></i> Dashboard</a>
        <a class="active" href="manage_books.php"><i class="fa-solid fa-book"></i> Manage Books</a>
        <a href="settings.php"><i class="fa-solid fa-gear"></i> Settings</a>
    </aside>

    <main class="main-content">

        <div class="page-heading">
            <div>
                <h1>Manage Books</h1>
                <p>Add, edit, search, and delete library books.</p>
            </div>

            <a class="btn btn-secondary" href="manage_books.php">
                <i class="fa-solid fa-plus"></i> Add New Book
            </a>
        </div>

        <?php if ($flash): ?>
            <div class="message <?= e($flash['type']) ?>">
                <?= e($flash['message']) ?>
            </div>
        <?php endif; ?>

        <section class="card">
            <h2><?= $editBook ? 'Edit Book' : 'Add New Book' ?></h2>

            <form method="post" id="bookForm">
                <input type="hidden" name="action" value="<?= $editBook ? 'edit' : 'add' ?>">

                <?php if ($editBook): ?>
                    <input type="hidden" name="book_id" value="<?= (int)$editBook['book_id'] ?>">
                <?php endif; ?>

                <div class="form-grid">
                    <div>
                        <label>Book Title *</label>
                        <input
                            type="text"
                            name="title"
                            maxlength="255"
                            required
                            value="<?= e($editBook['title'] ?? '') ?>"
                        >
                    </div>

                    <div>
                        <label>Author *</label>
                        <input
                            type="text"
                            name="author"
                            maxlength="255"
                            required
                            value="<?= e($editBook['author'] ?? '') ?>"
                        >
                    </div>

                    <div>
                        <label>ISBN *</label>
                        <input
                            type="text"
                            name="isbn"
                            maxlength="100"
                            required
                            value="<?= e($editBook['isbn'] ?? '') ?>"
                        >
                    </div>

                    <div>
                        <label>Category *</label>
                        <select name="category_id" required>
                            <option value="">Select Category</option>

                            <?php foreach ($categories as $category): ?>
                                <option
                                    value="<?= (int)$category['category_id'] ?>"
                                    <?= isset($editBook['category_id']) && (int)$editBook['category_id'] === (int)$category['category_id'] ? 'selected' : '' ?>
                                >
                                    <?= e($category['category_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label>Publisher</label>
                        <input
                            type="text"
                            name="publisher"
                            maxlength="255"
                            value="<?= e($editBook['publisher'] ?? '') ?>"
                        >
                    </div>

                    <div>
                        <label>Publication Year</label>
                        <input
                            type="number"
                            name="publication_year"
                            min="0"
                            max="<?= date('Y') ?>"
                            value="<?= e($editBook['publication_year'] ?? '') ?>"
                        >
                    </div>

                    <div>
                        <label>Total Quantity *</label>
                        <input
                            type="number"
                            name="quantity"
                            min="0"
                            required
                            value="<?= e($editBook['quantity'] ?? '1') ?>"
                        >
                    </div>

                    <div>
                        <label>Available Quantity *</label>
                        <input
                            type="number"
                            name="available_quantity"
                            min="0"
                            required
                            value="<?= e($editBook['available_quantity'] ?? '1') ?>"
                        >
                    </div>
                </div>

                <p>
                    <button class="btn" type="submit">
                        <i class="fa-solid fa-floppy-disk"></i>
                        <?= $editBook ? 'Update Book' : 'Add Book' ?>
                    </button>

                    <?php if ($editBook): ?>
                        <a class="btn btn-secondary" href="manage_books.php">Cancel</a>
                    <?php endif; ?>
                </p>
            </form>
        </section>

        <section class="card">
            <div class="toolbar">
                <h2>Library Book List</h2>

                <form method="get" class="search-form">
                    <input
                        type="text"
                        name="search"
                        placeholder="Search title, author, ISBN, publisher..."
                        value="<?= e($search) ?>"
                    >

                    <button type="submit" class="btn">
                        <i class="fa-solid fa-magnifying-glass"></i> Search
                    </button>

                    <?php if ($search !== ''): ?>
                        <a class="btn btn-secondary" href="manage_books.php">Clear</a>
                    <?php endif; ?>
                </form>
            </div>

            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Book / Author</th>
                            <th>ISBN</th>
                            <th>Category</th>
                            <th>Publisher</th>
                            <th>Year</th>
                            <th>Availability</th>
                            <th>Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php if (!$books || $books->num_rows === 0): ?>
                            <tr>
                                <td colspan="8">No books found.</td>
                            </tr>
                        <?php else: ?>
                            <?php while ($book = $books->fetch_assoc()): ?>
                                <tr>
                                    <td>#<?= (int)$book['book_id'] ?></td>

                                    <td>
                                        <strong><?= e($book['title']) ?></strong><br>
                                        <small><?= e($book['author']) ?></small>
                                    </td>

                                    <td><?= e($book['isbn']) ?></td>
                                    <td><?= e($book['category_name'] ?? 'Uncategorized') ?></td>
                                    <td><?= e($book['publisher'] ?: '-') ?></td>
                                    <td><?= e($book['publication_year'] ?: '-') ?></td>

                                    <td>
                                        <span class="badge">
                                            <?= (int)$book['available_quantity'] ?> /
                                            <?= (int)$book['quantity'] ?> Available
                                        </span>
                                    </td>

                                    <td class="action-buttons">
                                        <a
                                            class="btn"
                                            href="manage_books.php?edit=<?= (int)$book['book_id'] ?>"
                                            title="Edit Book"
                                        >
                                            <i class="fa-solid fa-pen"></i>
                                        </a>

                                        <form method="post" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this book?');">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="book_id" value="<?= (int)$book['book_id'] ?>">

                                            <button class="btn btn-danger" type="submit" title="Delete Book">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>

    </main>
</div>

<script>
document.getElementById('bookForm').addEventListener('submit', function (event) {
    const quantity = Number(this.quantity.value);
    const availableQuantity = Number(this.available_quantity.value);

    if (availableQuantity > quantity) {
        event.preventDefault();
        alert('Available quantity cannot be greater than total quantity.');
    }
});
</script>
</body>
</html>