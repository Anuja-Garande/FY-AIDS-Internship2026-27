<?php
session_start();
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

if (!isAdminLoggedIn()) {
    redirect('login.php');
}

$db = Database::getInstance();

if (isset($_POST['add_brand'])) {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        flash('error', 'Invalid request.');
    } else {
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $status = intval($_POST['status'] ?? 1);

        if (empty($name)) {
            flash('error', 'Brand name is required.');
        } else {
            $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name), '-'));
            $existing = $db->query("SELECT id FROM brands WHERE slug=?", [$slug])->fetch();
            if ($existing) $slug .= '-' . time();

            $filename = '';
            if (!empty($_FILES['logo']['tmp_name'])) {
                $filename = uploadImage($_FILES['logo'], 'uploads/brands');
            }

            $db->insert("INSERT INTO brands (name, slug, logo, description, status, created_at) VALUES (?, ?, ?, ?, ?, NOW())", [
                $name, $slug, $filename, $description, $status
            ]);
            flash('success', 'Brand added successfully!');
        }
    }
    unset($_SESSION['csrf_token']);
    redirect('brands.php');
}

if (isset($_POST['edit_brand'])) {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        flash('error', 'Invalid request.');
    } else {
        $id = intval($_POST['edit_id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $status = intval($_POST['status'] ?? 1);

        if (empty($name)) {
            flash('error', 'Brand name is required.');
        } else {
            if (!empty($_FILES['logo']['tmp_name'])) {
                $old = $db->query("SELECT logo FROM brands WHERE id=?", [$id])->fetch();
                if ($old && !empty($old['logo'])) {
                    $oldPath = __DIR__ . '/../uploads/brands/' . $old['logo'];
                    if (file_exists($oldPath)) unlink($oldPath);
                }
                $filename = uploadImage($_FILES['logo'], 'uploads/brands');
                $db->update("UPDATE brands SET name=?, logo=?, description=?, status=? WHERE id=?", [$name, $filename, $description, $status, $id]);
            } else {
                $db->update("UPDATE brands SET name=?, description=?, status=? WHERE id=?", [$name, $description, $status, $id]);
            }
            flash('success', 'Brand updated successfully!');
        }
    }
    unset($_SESSION['csrf_token']);
    redirect('brands.php');
}

if (isset($_POST['delete_brand'])) {
    $id = intval($_POST['id'] ?? 0);
    $brand = $db->query("SELECT logo FROM brands WHERE id=?", [$id])->fetch();
    if ($brand) {
        if (!empty($brand['logo'])) {
            $path = __DIR__ . '/../uploads/brands/' . $brand['logo'];
            if (file_exists($path)) unlink($path);
        }
        $db->query("UPDATE products SET brand_id=NULL WHERE brand_id=?", [$id]);
        $db->query("DELETE FROM brands WHERE id=?", [$id]);
        flash('success', 'Brand deleted.');
    }
    redirect('brands.php');
}

$search = $_GET['search'] ?? '';
$page = max(1, intval($_GET['page'] ?? 1));
$per_page = 15;
$offset = ($page - 1) * $per_page;

$where = "1=1";
$params = [];
if (!empty($search)) {
    $where .= " AND b.name LIKE :search";
    $params[':search'] = "%$search%";
}

$total = $db->query("SELECT COUNT(*) as count FROM brands b WHERE $where", $params)->fetch()['count'];
$total_pages = max(1, ceil($total / $per_page));

$brands = $db->query("SELECT b.*, (SELECT COUNT(*) FROM products WHERE brand_id=b.id) as product_count FROM brands b WHERE $where ORDER BY b.id DESC LIMIT $per_page OFFSET $offset", $params)->fetchAll();

$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
$pageTitle = 'Brands';
include __DIR__ . '/includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="fw-bold mb-0">Brands (<?= number_format($total) ?>)</h5>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addBrandModal"><i class="fas fa-plus me-2"></i>Add Brand</button>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-6"><input type="text" name="search" class="form-control" placeholder="Search brands..." value="<?= sanitize($search) ?>"></div>
            <div class="col-md-2"><button type="submit" class="btn btn-primary w-100"><i class="fas fa-search"></i></button></div>
            <div class="col-md-2"><a href="<?= BASE_URL ?>/admin/brands.php" class="btn btn-outline-secondary w-100">Clear</a></div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead><tr><th>ID</th><th>Logo</th><th>Name</th><th>Slug</th><th>Products</th><th>Status</th><th>Actions</th></tr></thead>
                <tbody>
                    <?php if (empty($brands)): ?>
                    <tr><td colspan="7" class="text-center text-muted py-4">No brands found</td></tr>
                    <?php endif; ?>
                    <?php foreach ($brands as $brand): ?>
                    <tr>
                        <td class="fw-semibold">#<?= $brand['id'] ?></td>
                        <td>
                            <?php if (!empty($brand['logo'])): ?>
                            <img src="<?= BASE_URL ?>/uploads/brands/<?= sanitize($brand['logo']) ?>" width="40" height="40" style="object-fit:cover;border-radius:8px;">
                            <?php else: ?>
                            <div class="bg-light d-flex align-items-center justify-content-center" style="width:40px;height:40px;border-radius:8px;"><i class="fas fa-trademark text-muted"></i></div>
                            <?php endif; ?>
                        </td>
                        <td class="fw-semibold"><?= sanitize($brand['name']) ?></td>
                        <td><code><?= sanitize($brand['slug']) ?></code></td>
                        <td><span class="badge bg-info"><?= $brand['product_count'] ?></span></td>
                        <td><button class="btn btn-sm btn-<?= $brand['status'] ? 'success' : 'danger' ?> btn-action" onclick="toggleStatus('brands',<?= $brand['id'] ?>,'status',this)"><?= $brand['status'] ? 'Active' : 'Inactive' ?></button></td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary" onclick="editBrand(<?= $brand['id'] ?>,'<?= sanitize(addslashes($brand['name'])) ?>','<?= sanitize(addslashes($brand['description'] ?? '')) ?>',<?= $brand['status'] ?>)"><i class="fas fa-edit"></i></button>
                            <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this brand?')">
                                <input type="hidden" name="delete_brand" value="1"><input type="hidden" name="id" value="<?= $brand['id'] ?>">
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

<div class="modal fade" id="addBrandModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?= sanitize($_SESSION['csrf_token']) ?>">
                <input type="hidden" name="add_brand" value="1">
                <div class="modal-header"><h5 class="modal-title fw-bold">Add Brand</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <div class="mb-3"><label class="form-label fw-semibold">Name *</label><input type="text" name="name" class="form-control" required></div>
                    <div class="mb-3"><label class="form-label fw-semibold">Description</label><textarea name="description" class="form-control" rows="3"></textarea></div>
                    <div class="mb-3"><label class="form-label fw-semibold">Logo</label><input type="file" name="logo" class="form-control" accept="image/*"></div>
                    <div class="mb-3"><label class="form-label fw-semibold">Status</label><select name="status" class="form-select"><option value="1">Active</option><option value="0">Inactive</option></select></div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary">Save</button></div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="editBrandModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?= sanitize($_SESSION['csrf_token']) ?>">
                <input type="hidden" name="edit_brand" value="1">
                <input type="hidden" name="edit_id" id="editBrandId">
                <div class="modal-header"><h5 class="modal-title fw-bold">Edit Brand</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <div class="mb-3"><label class="form-label fw-semibold">Name *</label><input type="text" name="name" id="editBrandName" class="form-control" required></div>
                    <div class="mb-3"><label class="form-label fw-semibold">Description</label><textarea name="description" id="editBrandDesc" class="form-control" rows="3"></textarea></div>
                    <div class="mb-3"><label class="form-label fw-semibold">Logo</label><input type="file" name="logo" class="form-control" accept="image/*"></div>
                    <div class="mb-3"><label class="form-label fw-semibold">Status</label><select name="status" id="editBrandStatus" class="form-select"><option value="1">Active</option><option value="0">Inactive</option></select></div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary">Update</button></div>
            </form>
        </div>
    </div>
</div>

<?php
$extraScripts = '<script>
function editBrand(id, name, desc, status) {
    document.getElementById("editBrandId").value = id;
    document.getElementById("editBrandName").value = name;
    document.getElementById("editBrandDesc").value = desc;
    document.getElementById("editBrandStatus").value = status;
    new bootstrap.Modal(document.getElementById("editBrandModal")).show();
}
</script>';
include __DIR__ . '/includes/footer.php';
?>
