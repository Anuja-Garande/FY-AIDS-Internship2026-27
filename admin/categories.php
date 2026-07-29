<?php
session_start();
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

if (!isAdminLoggedIn()) {
    redirect('login.php');
}

$db = Database::getInstance();

if (isset($_POST['add_category'])) {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        flash('error', 'Invalid request.');
    } else {
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $parent_id = !empty($_POST['parent_id']) ? intval($_POST['parent_id']) : null;
        $status = intval($_POST['status'] ?? 1);
        $sort_order = intval($_POST['sort_order'] ?? 0);

        if (empty($name)) {
            flash('error', 'Category name is required.');
        } else {
            $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name), '-'));
            $existing = $db->query("SELECT id FROM categories WHERE slug=?", [$slug])->fetch();
            if ($existing) $slug .= '-' . time();

            $filename = '';
            if (!empty($_FILES['image']['tmp_name'])) {
                $filename = uploadImage($_FILES['image'], 'uploads/categories');
            }

            $db->insert("INSERT INTO categories (name, slug, description, image, parent_id, status, sort_order, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())", [
                $name, $slug, $description, $filename, $parent_id, $status, $sort_order
            ]);
            flash('success', 'Category added successfully!');
        }
    }
    unset($_SESSION['csrf_token']);
    redirect('categories.php');
}

if (isset($_POST['edit_category'])) {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        flash('error', 'Invalid request.');
    } else {
        $id = intval($_POST['edit_id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $parent_id = !empty($_POST['parent_id']) ? intval($_POST['parent_id']) : null;
        $status = intval($_POST['status'] ?? 1);
        $sort_order = intval($_POST['sort_order'] ?? 0);

        if (empty($name)) {
            flash('error', 'Category name is required.');
        } else {
            $imageUpdate = '';
            $params = [$name, $description, $parent_id, $status, $sort_order, $id];

            if (!empty($_FILES['image']['tmp_name'])) {
                $old = $db->query("SELECT image FROM categories WHERE id=?", [$id])->fetch();
                if ($old && !empty($old['image'])) {
                    $oldPath = __DIR__ . '/../uploads/categories/' . $old['image'];
                    if (file_exists($oldPath)) unlink($oldPath);
                }
                $filename = uploadImage($_FILES['image'], 'uploads/categories');
                $db->update("UPDATE categories SET name=?, description=?, parent_id=?, status=?, sort_order=?, image=? WHERE id=?", [$name, $description, $parent_id, $status, $sort_order, $filename, $id]);
            } else {
                $db->update("UPDATE categories SET name=?, description=?, parent_id=?, status=?, sort_order=? WHERE id=?", [$name, $description, $parent_id, $status, $sort_order, $id]);
            }
            flash('success', 'Category updated successfully!');
        }
    }
    unset($_SESSION['csrf_token']);
    redirect('categories.php');
}

if (isset($_POST['delete_category'])) {
    $id = intval($_POST['id'] ?? 0);
    $cat = $db->query("SELECT image FROM categories WHERE id=?", [$id])->fetch();
    if ($cat) {
        if (!empty($cat['image'])) {
            $path = __DIR__ . '/../uploads/categories/' . $cat['image'];
            if (file_exists($path)) unlink($path);
        }
        $db->query("UPDATE categories SET parent_id=NULL WHERE parent_id=?", [$id]);
        $db->query("UPDATE products SET category_id=NULL WHERE category_id=?", [$id]);
        $db->query("DELETE FROM categories WHERE id=?", [$id]);
        flash('success', 'Category deleted.');
    }
    redirect('categories.php');
}

$search = $_GET['search'] ?? '';
$page = max(1, intval($_GET['page'] ?? 1));
$per_page = 15;
$offset = ($page - 1) * $per_page;

$where = "1=1";
$params = [];
if (!empty($search)) {
    $where .= " AND c.name LIKE :search";
    $params[':search'] = "%$search%";
}

$total = $db->query("SELECT COUNT(*) as count FROM categories c WHERE $where", $params)->fetch()['count'];
$total_pages = max(1, ceil($total / $per_page));

$categories = $db->query("SELECT c.*, p.name as parent_name, (SELECT COUNT(*) FROM products WHERE category_id=c.id) as product_count FROM categories c LEFT JOIN categories p ON c.parent_id=p.id WHERE $where ORDER BY c.sort_order ASC, c.id DESC LIMIT $per_page OFFSET $offset", $params)->fetchAll();

$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
$pageTitle = 'Categories';
include __DIR__ . '/includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="fw-bold mb-0">Categories (<?= number_format($total) ?>)</h5>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCategoryModal"><i class="fas fa-plus me-2"></i>Add Category</button>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-6"><input type="text" name="search" class="form-control" placeholder="Search categories..." value="<?= sanitize($search) ?>"></div>
            <div class="col-md-2"><button type="submit" class="btn btn-primary w-100"><i class="fas fa-search"></i></button></div>
            <div class="col-md-2"><a href="<?= BASE_URL ?>/admin/categories.php" class="btn btn-outline-secondary w-100">Clear</a></div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr><th>ID</th><th>Image</th><th>Name</th><th>Slug</th><th>Parent</th><th>Products</th><th>Status</th><th>Sort</th><th>Actions</th></tr>
                </thead>
                <tbody>
                    <?php if (empty($categories)): ?>
                    <tr><td colspan="9" class="text-center text-muted py-4">No categories found</td></tr>
                    <?php endif; ?>
                    <?php foreach ($categories as $cat): ?>
                    <tr>
                        <td class="fw-semibold">#<?= $cat['id'] ?></td>
                        <td>
                            <?php if (!empty($cat['image'])): ?>
                            <img src="<?= BASE_URL ?>/uploads/categories/<?= sanitize($cat['image']) ?>" width="40" height="40" style="object-fit:cover;border-radius:8px;">
                            <?php else: ?>
                            <div class="bg-light d-flex align-items-center justify-content-center" style="width:40px;height:40px;border-radius:8px;"><i class="fas fa-folder text-muted"></i></div>
                            <?php endif; ?>
                        </td>
                        <td class="fw-semibold"><?= sanitize($cat['name']) ?></td>
                        <td><code><?= sanitize($cat['slug']) ?></code></td>
                        <td><?= sanitize($cat['parent_name'] ?? '-') ?></td>
                        <td><span class="badge bg-info"><?= $cat['product_count'] ?></span></td>
                        <td><button class="btn btn-sm btn-<?= $cat['status'] ? 'success' : 'danger' ?> btn-action" onclick="toggleStatus('categories',<?= $cat['id'] ?>,'status',this)"><?= $cat['status'] ? 'Active' : 'Inactive' ?></button></td>
                        <td><span class="badge bg-secondary"><?= $cat['sort_order'] ?></span></td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary" onclick="editCategory(<?= $cat['id'] ?>,'<?= sanitize(addslashes($cat['name'])) ?>','<?= sanitize(addslashes($cat['description'] ?? '')) ?>',<?= $cat['parent_id'] ?? 0 ?>,<?= $cat['status'] ?>,<?= $cat['sort_order'] ?>)"><i class="fas fa-edit"></i></button>
                            <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this category?')">
                                <input type="hidden" name="delete_category" value="1">
                                <input type="hidden" name="id" value="<?= $cat['id'] ?>">
                                <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="d-flex justify-content-center p-3">
            <nav><ul class="pagination pagination-sm mb-0">
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <li class="page-item <?= $i === $page ? 'active' : '' ?>"><a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>"><?= $i ?></a></li>
                <?php endfor; ?>
            </ul></nav>
        </div>
    </div>
</div>

<div class="modal fade" id="addCategoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?= sanitize($_SESSION['csrf_token']) ?>">
                <input type="hidden" name="add_category" value="1">
                <div class="modal-header"><h5 class="modal-title fw-bold">Add Category</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <div class="mb-3"><label class="form-label fw-semibold">Name *</label><input type="text" name="name" class="form-control" required></div>
                    <div class="mb-3"><label class="form-label fw-semibold">Description</label><textarea name="description" class="form-control" rows="3"></textarea></div>
                    <div class="mb-3"><label class="form-label fw-semibold">Image</label><input type="file" name="image" class="form-control" accept="image/*"></div>
                    <div class="mb-3"><label class="form-label fw-semibold">Parent Category</label><select name="parent_id" class="form-select"><option value="">None</option><?php foreach ($categories as $c): ?><option value="<?= $c['id'] ?>"><?= sanitize($c['name']) ?></option><?php endforeach; ?></select></div>
                    <div class="row g-3">
                        <div class="col-6"><label class="form-label fw-semibold">Status</label><select name="status" class="form-select"><option value="1">Active</option><option value="0">Inactive</option></select></div>
                        <div class="col-6"><label class="form-label fw-semibold">Sort Order</label><input type="number" name="sort_order" class="form-control" value="0"></div>
                    </div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary">Save</button></div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="editCategoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?= sanitize($_SESSION['csrf_token']) ?>">
                <input type="hidden" name="edit_category" value="1">
                <input type="hidden" name="edit_id" id="editCatId">
                <div class="modal-header"><h5 class="modal-title fw-bold">Edit Category</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <div class="mb-3"><label class="form-label fw-semibold">Name *</label><input type="text" name="name" id="editCatName" class="form-control" required></div>
                    <div class="mb-3"><label class="form-label fw-semibold">Description</label><textarea name="description" id="editCatDesc" class="form-control" rows="3"></textarea></div>
                    <div class="mb-3"><label class="form-label fw-semibold">Image</label><input type="file" name="image" class="form-control" accept="image/*"></div>
                    <div class="mb-3"><label class="form-label fw-semibold">Parent Category</label><select name="parent_id" id="editCatParent" class="form-select"><option value="">None</option><?php foreach ($categories as $c): ?><option value="<?= $c['id'] ?>"><?= sanitize($c['name']) ?></option><?php endforeach; ?></select></div>
                    <div class="row g-3">
                        <div class="col-6"><label class="form-label fw-semibold">Status</label><select name="status" id="editCatStatus" class="form-select"><option value="1">Active</option><option value="0">Inactive</option></select></div>
                        <div class="col-6"><label class="form-label fw-semibold">Sort Order</label><input type="number" name="sort_order" id="editCatSort" class="form-control"></div>
                    </div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary">Update</button></div>
            </form>
        </div>
    </div>
</div>

<?php
$extraScripts = '<script>
function editCategory(id, name, desc, parentId, status, sortOrder) {
    document.getElementById("editCatId").value = id;
    document.getElementById("editCatName").value = name;
    document.getElementById("editCatDesc").value = desc;
    document.getElementById("editCatParent").value = parentId || "";
    document.getElementById("editCatStatus").value = status;
    document.getElementById("editCatSort").value = sortOrder;
    new bootstrap.Modal(document.getElementById("editCategoryModal")).show();
}
</script>';
include __DIR__ . '/includes/footer.php';
?>
