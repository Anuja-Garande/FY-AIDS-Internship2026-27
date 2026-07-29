<?php
session_start();
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

if (!isAdminLoggedIn()) {
    redirect('login.php');
}

$db = Database::getInstance();
$id = intval($_GET['id'] ?? 0);

if (!$id) {
    flash('error', 'Invalid category ID.');
    redirect('categories.php');
}

$category = $db->query("SELECT * FROM categories WHERE id=?", [$id])->fetch();
if (!$category) {
    flash('error', 'Category not found.');
    redirect('categories.php');
}

$categories = $db->query("SELECT id, name FROM categories WHERE id!=? ORDER BY name ASC", [$id])->fetchAll();

if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $errors[] = 'Invalid request.';
    } else {
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $parent_id = !empty($_POST['parent_id']) ? intval($_POST['parent_id']) : null;
        $status = intval($_POST['status'] ?? 1);
        $sort_order = intval($_POST['sort_order'] ?? 0);

        if (empty($name)) {
            $errors[] = 'Category name is required.';
        }

        if (empty($errors)) {
            if (!empty($_FILES['image']['tmp_name'])) {
                if (!empty($category['image'])) {
                    $oldPath = __DIR__ . '/../uploads/categories/' . $category['image'];
                    if (file_exists($oldPath)) unlink($oldPath);
                }
                $filename = uploadImage($_FILES['image'], 'uploads/categories');
                $db->update("UPDATE categories SET name=?, description=?, image=?, parent_id=?, status=?, sort_order=? WHERE id=?", [
                    $name, $description, $filename, $parent_id, $status, $sort_order, $id
                ]);
            } else {
                $db->update("UPDATE categories SET name=?, description=?, parent_id=?, status=?, sort_order=? WHERE id=?", [
                    $name, $description, $parent_id, $status, $sort_order, $id
                ]);
            }

            unset($_SESSION['csrf_token']);
            flash('success', 'Category updated successfully!');
            redirect('categories.php');
        }
    }
    $category = array_merge($category, $_POST);
}

$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
$pageTitle = 'Edit Category';
include __DIR__ . '/includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="fw-bold mb-0">Edit Category: <?= sanitize($category['name']) ?></h5>
    <a href="<?= BASE_URL ?>/admin/categories.php" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Back</a>
</div>

<?php if (!empty($errors)): ?>
<div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errors as $e): ?><li><?= sanitize($e) ?></li><?php endforeach; ?></ul></div>
<?php endif; ?>

<div class="row">
    <div class="col-lg-8">
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?= sanitize($_SESSION['csrf_token']) ?>">
            <div class="card mb-4">
                <div class="card-header"><h6 class="fw-bold mb-0">Category Details</h6></div>
                <div class="card-body">
                    <div class="mb-3"><label class="form-label fw-semibold">Name <span class="text-danger">*</span></label><input type="text" name="name" class="form-control" required value="<?= sanitize($category['name']) ?>"></div>
                    <div class="mb-3"><label class="form-label fw-semibold">Description</label><textarea name="description" class="form-control" rows="4"><?= sanitize($category['description']) ?></textarea></div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Image</label>
                        <?php if (!empty($category['image'])): ?>
                        <div class="mb-2"><img src="<?= BASE_URL ?>/uploads/categories/<?= sanitize($category['image']) ?>" class="img-thumbnail" style="max-height:100px;"></div>
                        <?php endif; ?>
                        <input type="file" name="image" class="form-control" accept="image/*"><div id="catImgPreview" class="mt-2"></div>
                    </div>
                    <div class="mb-3"><label class="form-label fw-semibold">Parent Category</label><select name="parent_id" class="form-select"><option value="">None (Top Level)</option><?php foreach ($categories as $cat): ?><option value="<?= $cat['id'] ?>" <?= $category['parent_id'] == $cat['id'] ? 'selected' : '' ?>><?= sanitize($cat['name']) ?></option><?php endforeach; ?></select></div>
                    <div class="row g-3">
                        <div class="col-md-6"><label class="form-label fw-semibold">Status</label><select name="status" class="form-select"><option value="1" <?= $category['status'] == 1 ? 'selected' : '' ?>>Active</option><option value="0" <?= $category['status'] == 0 ? 'selected' : '' ?>>Inactive</option></select></div>
                        <div class="col-md-6"><label class="form-label fw-semibold">Sort Order</label><input type="number" name="sort_order" class="form-control" value="<?= sanitize($category['sort_order']) ?>"></div>
                    </div>
                </div>
            </div>
            <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Update Category</button>
        </form>
    </div>
</div>

<?php
$extraScripts = '<script>
document.querySelector("input[name=image]").addEventListener("change", function(e) {
    var preview = document.getElementById("catImgPreview");
    preview.innerHTML = "";
    if (e.target.files[0]) {
        var reader = new FileReader();
        reader.onload = function(ev) { preview.innerHTML = \'<img src="\' + ev.target.result + \'" class="img-thumbnail" style="max-height:150px;">\'; };
        reader.readAsDataURL(e.target.files[0]);
    }
});
</script>';
include __DIR__ . '/includes/footer.php';
?>
