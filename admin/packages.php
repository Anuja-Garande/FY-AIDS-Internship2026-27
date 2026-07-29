<?php
$pageTitle = 'Manage Tour Packages';
require __DIR__ . '/includes/header.php';

$uploadDir = __DIR__ . '/../assets/images/destinations/';
$action = $_GET['action'] ?? 'list';
$editId = (int)($_GET['id'] ?? 0);

function handleImageUpload4($fieldName, $uploadDir) {
    if (!empty($_FILES[$fieldName]['name']) && $_FILES[$fieldName]['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES[$fieldName]['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg','jpeg','png','webp'])) {
            $filename = uniqid('img_') . '.' . $ext;
            move_uploaded_file($_FILES[$fieldName]['tmp_name'], $uploadDir . $filename);
            return $filename;
        }
    }
    return null;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_package'])) {
    $name = trim($_POST['name']);
    $stateId = $_POST['state_id'] ?: null;
    $duration = trim($_POST['duration']);
    $price = (float)$_POST['price'];
    $itinerary = trim($_POST['itinerary']);
    $existingImage = $_POST['existing_image'] ?? '';
    $uploaded = handleImageUpload4('image_file', $uploadDir);
    $image = $uploaded ?: ($existingImage ?: 'default-package.jpg');

    if ($_POST['package_id']) {
        $stmt = $pdo->prepare("UPDATE packages SET name=?, state_id=?, duration=?, price=?, itinerary=?, image=? WHERE package_id=?");
        $stmt->execute([$name, $stateId, $duration, $price, $itinerary, $image, $_POST['package_id']]);
        setFlash('success', 'Package updated successfully.');
    } else {
        $stmt = $pdo->prepare("INSERT INTO packages (name, state_id, duration, price, itinerary, image) VALUES (?,?,?,?,?,?)");
        $stmt->execute([$name, $stateId, $duration, $price, $itinerary, $image]);
        setFlash('success', 'Package added successfully.');
    }
    redirect('/tourism-portal/admin/packages.php');
}

if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM packages WHERE package_id = ?");
    $stmt->execute([(int)$_GET['delete']]);
    setFlash('success', 'Package deleted.');
    redirect('/tourism-portal/admin/packages.php');
}

$states = $pdo->query("SELECT * FROM states ORDER BY name ASC")->fetchAll();

$editData = null;
if ($action === 'edit' && $editId) {
    $stmt = $pdo->prepare("SELECT * FROM packages WHERE package_id = ?");
    $stmt->execute([$editId]);
    $editData = $stmt->fetch();
}

$packages = $pdo->query("SELECT p.*, s.name AS state_name FROM packages p
                          LEFT JOIN states s ON s.state_id = p.state_id ORDER BY p.package_id DESC")->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-3">
  <h6 class="mb-0"><?= $action === 'list' ? 'All Tour Packages' : ($editData ? 'Edit Package' : 'Add New Package') ?></h6>
  <?php if ($action === 'list'): ?>
    <a href="?action=add" class="btn-brand btn-sm">+ Add Package</a>
  <?php else: ?>
    <a href="/tourism-portal/admin/packages.php" class="btn btn-outline-secondary btn-sm">&larr; Back to List</a>
  <?php endif; ?>
</div>

<?php if ($action === 'list'): ?>
<div class="admin-card">
  <div class="table-responsive">
    <table class="table table-admin align-middle">
      <thead><tr><th>#</th><th>Package</th><th>State</th><th>Duration</th><th>Price</th><th>Actions</th></tr></thead>
      <tbody>
        <?php foreach ($packages as $p): ?>
        <tr>
          <td><?= $p['package_id'] ?></td>
          <td><?= h($p['name']) ?></td>
          <td><?= h($p['state_name'] ?? 'Multi-state') ?></td>
          <td><?= h($p['duration']) ?></td>
          <td>₹<?= number_format($p['price'],0) ?></td>
          <td>
            <a href="?action=edit&id=<?= $p['package_id'] ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
            <a href="?delete=<?= $p['package_id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this package?')"><i class="bi bi-trash"></i></a>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php else: ?>
<div class="admin-card">
  <form method="post" enctype="multipart/form-data">
    <input type="hidden" name="package_id" value="<?= $editData['package_id'] ?? '' ?>">
    <input type="hidden" name="existing_image" value="<?= h($editData['image'] ?? '') ?>">
    <div class="row g-3">
      <div class="col-md-6">
        <label class="form-label">Package Name</label>
        <input type="text" name="name" class="form-control" value="<?= h($editData['name'] ?? '') ?>" required>
      </div>
      <div class="col-md-6">
        <label class="form-label">Primary State</label>
        <select name="state_id" class="form-select">
          <option value="">Multi-state / Not specific</option>
          <?php foreach ($states as $s): ?>
            <option value="<?= $s['state_id'] ?>" <?= (($editData['state_id'] ?? '') == $s['state_id']) ? 'selected' : '' ?>><?= h($s['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-6">
        <label class="form-label">Duration</label>
        <input type="text" name="duration" class="form-control" placeholder="5 Days / 4 Nights" value="<?= h($editData['duration'] ?? '') ?>">
      </div>
      <div class="col-md-6">
        <label class="form-label">Price (₹ per person)</label>
        <input type="number" step="0.01" name="price" class="form-control" value="<?= h($editData['price'] ?? '') ?>" required>
      </div>
      <div class="col-md-6">
        <label class="form-label">Image Upload <span class="text-muted small">(optional)</span></label>
        <input type="file" name="image_file" class="form-control" accept=".jpg,.jpeg,.png,.webp">
      </div>
      <div class="col-12">
        <label class="form-label">Itinerary <span class="text-muted small">(separate each day with a pipe symbol | )</span></label>
        <textarea name="itinerary" class="form-control" rows="4" placeholder="Day 1: ... | Day 2: ... | Day 3: ..."><?= h($editData['itinerary'] ?? '') ?></textarea>
      </div>
    </div>
    <button type="submit" name="save_package" class="btn-brand mt-4">Save Package</button>
  </form>
</div>
<?php endif; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>
