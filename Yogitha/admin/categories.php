<?php
// C:\xampp\htdocs\NewProject\admin\categories.php
// Manage Categories CRUD Page

require_once '../config/db_connect.php';
require_once 'includes/admin_header.php';

// 1. Process Post Submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = isset($_POST['action']) ? trim($_POST['action']) : '';

    // Add Category
    if ($action === 'add') {
        $name = trim($_POST['name']);
        $icon = trim($_POST['icon']);

        if (empty($name) || empty($icon)) {
            $_SESSION['admin_error'] = "Category name and icon are required.";
        } else {
            try {
                $stmt = $pdo->prepare("INSERT INTO categories (name, icon) VALUES (?, ?)");
                $stmt->execute([$name, $icon]);
                $_SESSION['admin_success'] = "Category '$name' created successfully!";
            } catch (\PDOException $e) {
                $_SESSION['admin_error'] = "Database error: Name must be unique. Details: " . $e->getMessage();
            }
        }
        header("Location: categories.php");
        exit;
    }

    // Edit Category
    if ($action === 'edit') {
        $id = intval($_POST['id']);
        $name = trim($_POST['name']);
        $icon = trim($_POST['icon']);

        if ($id <= 0 || empty($name) || empty($icon)) {
            $_SESSION['admin_error'] = "All fields are required.";
        } else {
            try {
                $stmt = $pdo->prepare("UPDATE categories SET name = ?, icon = ? WHERE id = ?");
                $stmt->execute([$name, $icon, $id]);
                $_SESSION['admin_success'] = "Category updated successfully.";
            } catch (\PDOException $e) {
                $_SESSION['admin_error'] = "Database error. Details: " . $e->getMessage();
            }
        }
        header("Location: categories.php");
        exit;
    }

    // Delete Category
    if ($action === 'delete') {
        $id = intval($_POST['id']);
        try {
            $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
            $stmt->execute([$id]);
            $_SESSION['admin_success'] = "Category deleted successfully.";
        } catch (\PDOException $e) {
            $_SESSION['admin_error'] = "Error deleting category: " . $e->getMessage();
        }
        header("Location: categories.php");
        exit;
    }
}

// 2. Fetch Category for Edit Mode (GET)
$edit_mode = false;
$edit_cat = ['id' => 0, 'name' => '', 'icon' => ''];
if (isset($_GET['edit'])) {
    $edit_id = intval($_GET['edit']);
    if ($edit_id > 0) {
        $stmt = $pdo->prepare("SELECT * FROM categories WHERE id = ?");
        $stmt->execute([$edit_id]);
        $res = $stmt->fetch();
        if ($res) {
            $edit_mode = true;
            $edit_cat = $res;
        }
    }
}

// 3. Fetch all Categories for the list
try {
    $categories = $pdo->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();
} catch (\PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>

<div class="row g-4">
    <!-- Form Card (Add/Edit) -->
    <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-3 bg-white p-4">
            <h5 class="fw-bold mb-4 border-bottom pb-2">
                <?php echo $edit_mode ? '<i class="bi-pencil-square text-warning me-2"></i>Edit Category' : '<i class="bi-plus-circle text-primary me-2"></i>Add Category'; ?>
            </h5>
            
            <form action="categories.php" method="POST" class="needs-validation" novalidate>
                <input type="hidden" name="action" value="<?php echo $edit_mode ? 'edit' : 'add'; ?>">
                <?php if ($edit_mode): ?>
                    <input type="hidden" name="id" value="<?php echo $edit_cat['id']; ?>">
                <?php endif; ?>

                <!-- Category Name -->
                <div class="mb-3">
                    <label for="name" class="form-label small fw-bold text-muted">Category Name</label>
                    <input type="text" name="name" id="name" class="form-control" placeholder="e.g. Beaches, Hill Stations" value="<?php echo htmlspecialchars($edit_cat['name']); ?>" required>
                    <div class="invalid-feedback">Please enter category name.</div>
                </div>

                <!-- Category Bootstrap Icon -->
                <div class="mb-3">
                    <label for="icon" class="form-label small fw-bold text-muted">Bootstrap Icon Class</label>
                    <input type="text" name="icon" id="icon" class="form-control" placeholder="e.g. bi-sun-fill, bi-snow" value="<?php echo htmlspecialchars($edit_cat['icon']); ?>" required>
                    <div class="invalid-feedback">Please enter icon name.</div>
                    <div class="form-text small text-muted">Use standard Bootstrap Icon classes like <code>bi-sun-fill</code> or <code>bi-snow</code>.</div>
                </div>

                <div class="d-flex gap-2 pt-2">
                    <button type="submit" class="btn <?php echo $edit_mode ? 'btn-warning text-dark' : 'btn-primary'; ?> w-100 rounded-pill fw-bold">
                        <?php echo $edit_mode ? 'Update Category' : 'Save Category'; ?>
                    </button>
                    <?php if ($edit_mode): ?>
                        <a href="categories.php" class="btn btn-outline-secondary w-100 rounded-pill fw-bold">Cancel</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <!-- Category Table List -->
    <div class="col-md-8">
        <div class="card border-0 shadow-sm rounded-3 bg-white p-4">
            <h5 class="fw-bold mb-4 border-bottom pb-2"><i class="bi-list text-primary me-2"></i>Existing Categories</h5>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th style="width: 80px;">ID</th>
                            <th>Category Name</th>
                            <th>Icon Preview</th>
                            <th>Icon Class</th>
                            <th class="text-end" style="width: 150px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($categories)): ?>
                            <?php foreach ($categories as $cat): ?>
                                <tr>
                                    <td><?php echo $cat['id']; ?></td>
                                    <td><strong><?php echo htmlspecialchars($cat['name']); ?></strong></td>
                                    <td>
                                        <div class="fs-4 text-primary"><i class="<?php echo htmlspecialchars($cat['icon']); ?>"></i></div>
                                    </td>
                                    <td><code><?php echo htmlspecialchars($cat['icon']); ?></code></td>
                                    <td class="text-end">
                                        <div class="d-flex justify-content-end gap-2">
                                            <!-- Edit link -->
                                            <a href="categories.php?edit=<?php echo $cat['id']; ?>" class="btn btn-sm btn-outline-primary" title="Edit">
                                                <i class="bi-pencil"></i>
                                            </a>
                                            <!-- Delete Form -->
                                            <form action="categories.php" method="POST" onsubmit="return confirm('Deleting category will set category references to NULL on related destinations. Proceed?');" class="d-inline">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="id" value="<?php echo $cat['id']; ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                                    <i class="bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">No categories in the database.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/admin_footer.php'; ?>
