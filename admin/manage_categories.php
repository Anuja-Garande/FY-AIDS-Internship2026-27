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
    $_SESSION['category_flash'] = [
        'type' => $type,
        'message' => $message
    ];
}

function getFlash()
{
    $flash = $_SESSION['category_flash'] ?? null;
    unset($_SESSION['category_flash']);
    return $flash;
}

function redirectToCategories()
{
    header('Location: manage_categories.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add' || $action === 'edit') {
        $categoryId = (int)($_POST['category_id'] ?? 0);
        $categoryName = trim($_POST['category_name'] ?? '');

        if ($categoryName === '') {
            setFlash('error', 'Category name is required.');
            redirectToCategories();
        }

        if ($action === 'add') {
            $check = $conn->prepare(
                "SELECT category_id FROM categories WHERE category_name = ?"
            );
            $check->bind_param("s", $categoryName);
            $check->execute();

            if ($check->get_result()->num_rows > 0) {
                $check->close();
                setFlash('error', 'This category already exists.');
                redirectToCategories();
            }

            $check->close();

            $stmt = $conn->prepare(
                "INSERT INTO categories (category_name) VALUES (?)"
            );
            $stmt->bind_param("s", $categoryName);

            if ($stmt->execute()) {
                setFlash('success', 'Category added successfully.');
            } else {
                setFlash('error', 'Unable to add category.');
            }

            $stmt->close();
            redirectToCategories();
        }

        if ($action === 'edit') {
            if ($categoryId <= 0) {
                setFlash('error', 'Invalid category selected.');
                redirectToCategories();
            }

            $check = $conn->prepare(
                "SELECT category_id
                 FROM categories
                 WHERE category_name = ? AND category_id != ?"
            );
            $check->bind_param("si", $categoryName, $categoryId);
            $check->execute();

            if ($check->get_result()->num_rows > 0) {
                $check->close();
                setFlash('error', 'Another category already has this name.');
                redirectToCategories();
            }

            $check->close();

            $stmt = $conn->prepare(
                "UPDATE categories SET category_name = ? WHERE category_id = ?"
            );
            $stmt->bind_param("si", $categoryName, $categoryId);

            if ($stmt->execute()) {
                setFlash('success', 'Category updated successfully.');
            } else {
                setFlash('error', 'Unable to update category.');
            }

            $stmt->close();
            redirectToCategories();
        }
    }

    if ($action === 'delete') {
        $categoryId = (int)($_POST['category_id'] ?? 0);

        if ($categoryId <= 0) {
            setFlash('error', 'Invalid category selected.');
            redirectToCategories();
        }

        $bookCheck = $conn->prepare(
            "SELECT book_id FROM books WHERE category_id = ?"
        );
        $bookCheck->bind_param("i", $categoryId);
        $bookCheck->execute();

        if ($bookCheck->get_result()->num_rows > 0) {
            $bookCheck->close();
            setFlash('error', 'This category cannot be deleted because books are assigned to it.');
            redirectToCategories();
        }

        $bookCheck->close();

        $stmt = $conn->prepare(
            "DELETE FROM categories WHERE category_id = ?"
        );
        $stmt->bind_param("i", $categoryId);

        if ($stmt->execute() && $stmt->affected_rows > 0) {
            setFlash('success', 'Category deleted successfully.');
        } else {
            setFlash('error', 'Category was not found or could not be deleted.');
        }

        $stmt->close();
        redirectToCategories();
    }
}

/* Edit selected category */
$editCategory = null;

if (isset($_GET['edit'])) {
    $editId = (int)$_GET['edit'];

    if ($editId > 0) {
        $stmt = $conn->prepare(
            "SELECT * FROM categories WHERE category_id = ?"
        );
        $stmt->bind_param("i", $editId);
        $stmt->execute();
        $editCategory = $stmt->get_result()->fetch_assoc();
        $stmt->close();
    }
}

/* Search categories */
$search = trim($_GET['search'] ?? '');

if ($search !== '') {
    $searchValue = '%' . $search . '%';

    $stmt = $conn->prepare(
        "SELECT c.category_id, c.category_name, c.created_at,
                COUNT(b.book_id) AS total_books
         FROM categories c
         LEFT JOIN books b ON c.category_id = b.category_id
         WHERE c.category_name LIKE ?
         GROUP BY c.category_id
         ORDER BY c.category_id DESC"
    );

    $stmt->bind_param("s", $searchValue);
    $stmt->execute();
    $categories = $stmt->get_result();
} else {
    $categories = $conn->query(
        "SELECT c.category_id, c.category_name, c.created_at,
                COUNT(b.book_id) AS total_books
         FROM categories c
         LEFT JOIN books b ON c.category_id = b.category_id
         GROUP BY c.category_id
         ORDER BY c.category_id DESC"
    );
}

$flash = getFlash();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Categories | Digital Library</title>

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
            max-width: 1400px;
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

        .form-row {
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 15px;
            align-items: end;
        }

        label {
            display: block;
            margin-bottom: 6px;
            font-weight: bold;
        }

        input {
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
            max-width: 480px;
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
            .search-form,
            .form-row {
                display: flex;
                flex-direction: column;
                align-items: stretch;
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
        <a class="active" href="manage_categories.php"><i class="fa-solid fa-tags"></i> Categories</a>
        <a href="settings.php"><i class="fa-solid fa-gear"></i> Settings</a>
    </aside>

    <main class="main-content">

        <div class="page-heading">
            <div>
                <h1>Manage Categories</h1>
                <p>Add, edit, search, and delete book categories.</p>
            </div>

            <a class="btn btn-secondary" href="manage_categories.php">
                <i class="fa-solid fa-plus"></i> New Category
            </a>
        </div>

        <?php if ($flash): ?>
            <div class="message <?= e($flash['type']) ?>">
                <?= e($flash['message']) ?>
            </div>
        <?php endif; ?>

        <section class="card">
            <h2><?= $editCategory ? 'Edit Category' : 'Add New Category' ?></h2>

            <form method="post">
                <input type="hidden" name="action" value="<?= $editCategory ? 'edit' : 'add' ?>">

                <?php if ($editCategory): ?>
                    <input type="hidden" name="category_id" value="<?= (int)$editCategory['category_id'] ?>">
                <?php endif; ?>

                <div class="form-row">
                    <div>
                        <label>Category Name *</label>
                        <input
                            type="text"
                            name="category_name"
                            maxlength="150"
                            required
                            value="<?= e($editCategory['category_name'] ?? '') ?>"
                            placeholder="Example: Computer Science"
                        >
                    </div>

                    <div>
                        <button class="btn" type="submit">
                            <i class="fa-solid fa-floppy-disk"></i>
                            <?= $editCategory ? 'Update Category' : 'Add Category' ?>
                        </button>

                        <?php if ($editCategory): ?>
                            <a class="btn btn-secondary" href="manage_categories.php">Cancel</a>
                        <?php endif; ?>
                    </div>
                </div>
            </form>
        </section>

        <section class="card">
            <div class="toolbar">
                <h2>Category List</h2>

                <form method="get" class="search-form">
                    <input
                        type="text"
                        name="search"
                        placeholder="Search category..."
                        value="<?= e($search) ?>"
                    >

                    <button type="submit" class="btn">
                        <i class="fa-solid fa-magnifying-glass"></i> Search
                    </button>

                    <?php if ($search !== ''): ?>
                        <a class="btn btn-secondary" href="manage_categories.php">Clear</a>
                    <?php endif; ?>
                </form>
            </div>

            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Category Name</th>
                            <th>Total Books</th>
                            <th>Created At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php if (!$categories || $categories->num_rows === 0): ?>
                            <tr>
                                <td colspan="5">No categories found.</td>
                            </tr>
                        <?php else: ?>
                            <?php while ($category = $categories->fetch_assoc()): ?>
                                <tr>
                                    <td>#<?= (int)$category['category_id'] ?></td>
                                    <td><strong><?= e($category['category_name']) ?></strong></td>

                                    <td>
                                        <span class="badge">
                                            <?= (int)$category['total_books'] ?> Books
                                        </span>
                                    </td>

                                    <td><?= e($category['created_at'] ?? '-') ?></td>

                                    <td class="action-buttons">
                                        <a
                                            class="btn"
                                            href="manage_categories.php?edit=<?= (int)$category['category_id'] ?>"
                                            title="Edit Category"
                                        >
                                            <i class="fa-solid fa-pen"></i>
                                        </a>

                                        <form method="post" style="display:inline;" onsubmit="return confirm('Delete this category?');">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="category_id" value="<?= (int)$category['category_id'] ?>">

                                            <button class="btn btn-danger" type="submit" title="Delete Category">
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

</body>
</html>