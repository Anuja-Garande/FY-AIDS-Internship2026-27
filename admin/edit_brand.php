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
    flash('error', 'Invalid brand ID.');
    redirect('brands.php');
}

$brand = $db->query("SELECT * FROM brands WHERE id=?", [$id])->fetch();
if (!$brand) {
    flash('error', 'Brand not found.');
    redirect('brands.php');
}

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
        $status = intval($_POST['status'] ?? 1);

        if (empty($name)) {
            $errors[] = 'Brand name is required.';
        }

        if (empty($errors)) {
            if (!empty($_FILES['logo']['tmp_name'])) {
                if (!empty($brand['logo'])) {
                    $oldPath = __DIR__ . '/../uploads/brands/' . $brand['logo'];
                    if (file_exists($oldPath)) unlink($oldPath);
                }
                $filename = uploadImage($_FILES['logo'], 'uploads/brands');
                $db->update("UPDATE brands SET name=?, logo=?, description=?, status=? WHERE id=?", [
                    $name, $filename, $description, $status, $id
                ]);
            } else {
                $db->update("UPDATE brands SET name=?, description=?, status=? WHERE id=?", [
                    $name, $description, $status, $id
                ]);
            }

            unset($_SESSION['csrf_token']);
            flash('success', 'Brand updated successfully!');
            redirect('brands.php');
        }
    }
    $brand = array_merge($brand, $_POST);
}

$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
$pageTitle = 'Edit Brand';
include __DIR__ . '/includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="fw-bold mb-0">Edit Brand: <?= sanitize($brand['name']) ?></h5>
    <a href="<?= BASE_URL ?>/admin/brands.php" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Back</a>
</div>

<?php if (!empty($errors)): ?>
<div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errors as $e): ?><li><?= sanitize($e) ?></li><?php endforeach; ?></ul></div>
<?php endif; ?>

<div class="row">
    <div class="col-lg-8">
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?= sanitize($_SESSION['csrf_token']) ?>">
            <div class="card mb-4">
                <div class="card-header"><h6 class="fw-bold mb-0">Brand Details</h6></div>
                <div class="card-body">
                    <div class="mb-3"><label class="form-label fw-semibold">Name <span class="text-danger">*</span></label><input type="text" name="name" class="form-control" required value="<?= sanitize($brand['name']) ?>"></div>
                    <div class="mb-3"><label class="form-label fw-semibold">Description</label><textarea name="description" class="form-control" rows="4"><?= sanitize($brand['description']) ?></textarea></div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Logo</label>
                        <?php if (!empty($brand['logo'])): ?>
                        <div class="mb-2"><img src="<?= BASE_URL ?>/uploads/brands/<?= sanitize($brand['logo']) ?>" class="img-thumbnail" style="max-height:100px;"></div>
                        <?php endif; ?>
                        <input type="file" name="logo" class="form-control" accept="image/*"><div id="logoPreview" class="mt-2"></div>
                    </div>
                    <div class="mb-3"><label class="form-label fw-semibold">Status</label><select name="status" class="form-select"><option value="1" <?= $brand['status'] == 1 ? 'selected' : '' ?>>Active</option><option value="0" <?= $brand['status'] == 0 ? 'selected' : '' ?>>Inactive</option></select></div>
                </div>
            </div>
            <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Update Brand</button>
        </form>
    </div>
</div>

<?php
$extraScripts = '<script>
document.querySelector("input[name=logo]").addEventListener("change", function(e) {
    var preview = document.getElementById("logoPreview");
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
