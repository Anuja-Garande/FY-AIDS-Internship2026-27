<?php
$pageTitle = 'Manage Destinations';
require __DIR__ . '/includes/header.php';

$uploadDir = __DIR__ . '/../assets/images/destinations/';
$action = $_GET['action'] ?? 'list';
$editId = (int)($_GET['id'] ?? 0);

/** Handle image upload, returns filename or null */
function handleImageUpload($fieldName, $uploadDir) {
    if (!empty($_FILES[$fieldName]['name']) && $_FILES[$fieldName]['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES[$fieldName]['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg','jpeg','png','webp'];
        if (in_array($ext, $allowed)) {
            $filename = uniqid('img_') . '.' . $ext;
            move_uploaded_file($_FILES[$fieldName]['tmp_name'], $uploadDir . $filename);
            return $filename;
        }
    }
    return null;
}

// ---- Handle Save (Add / Edit) ----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_destination'])) {
    $name = trim($_POST['name']);
    $stateId = $_POST['state_id'];
    $category = trim($_POST['category']);
    $location = trim($_POST['location']);
    $description = trim($_POST['description']);
    $popularity = (int)$_POST['popularity'];
    $existingImage = $_POST['existing_image'] ?? '';
    $uploaded = handleImageUpload('image_file', $uploadDir);
    $image = $uploaded ?: ($existingImage ?: 'default-destination.jpg');

    if ($_POST['destination_id']) {
        $stmt = $pdo->prepare("UPDATE destinations SET state_id=?, name=?, category=?, description=?, location=?, image=?, popularity=? WHERE destination_id=?");
        $stmt->execute([$stateId, $name, $category, $description, $location, $image, $popularity, $_POST['destination_id']]);
        setFlash('success', 'Destination updated successfully.');
    } else {
        $stmt = $pdo->prepare("INSERT INTO destinations (state_id, name, category, description, location, image, popularity) VALUES (?,?,?,?,?,?,?)");
        $stmt->execute([$stateId, $name, $category, $description, $location, $image, $popularity]);
        setFlash('success', 'Destination added successfully.');
    }
    redirect('/tourism-portal/admin/destinations.php');
}

// ---- Handle Delete ----
if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM destinations WHERE destination_id = ?");
    $stmt->execute([(int)$_GET['delete']]);
    setFlash('success', 'Destination deleted.');
    redirect('/tourism-portal/admin/destinations.php');
}

$states = $pdo->query("SELECT * FROM states ORDER BY name ASC")->fetchAll();

$editData = null;
if ($action === 'edit' && $editId) {
    $stmt = $pdo->prepare("SELECT * FROM destinations WHERE destination_id = ?");
    $stmt->execute([$editId]);
    $editData = $stmt->fetch();
}

$destinations = $pdo->query("SELECT d.*, s.name AS state_name FROM destinations d
                              JOIN states s ON s.state_id = d.state_id ORDER BY d.destination_id DESC")->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-3">
  <h6 class="mb-0"><?= $action === 'list' ? 'All Destinations' : ($editData ? 'Edit Destination' : 'Add New Destination') ?></h6>
  <?php if ($action === 'list'): ?>
    <a href="?action=add" class="btn-brand btn-sm">+ Add Destination</a>
  <?php else: ?>
    <a href="/tourism-portal/admin/destinations.php" class="btn btn-outline-secondary btn-sm">&larr; Back to List</a>
  <?php endif; ?>
</div>

<?php if ($action === 'list'): ?>
<div class="admin-card">
  <div class="table-responsive">
    <table class="table table-admin align-middle">
      <thead><tr><th>#</th><th>Name</th><th>State</th><th>Category</th><th>Popularity</th><th>Actions</th></tr></thead>
      <tbody>
        <?php foreach ($destinations as $d): ?>
        <tr>
          <td><?= $d['destination_id'] ?></td>
          <td><?= h($d['name']) ?></td>
          <td><?= h($d['state_name']) ?></td>
          <td><?= h($d['category']) ?></td>
          <td><?= $d['popularity'] ?>%</td>
          <td>
            <a href="?action=edit&id=<?= $d['destination_id'] ?>" class="btn btn-sm btn-outline-primary" title="Edit"><i class="bi bi-pencil"></i></a>
            <a href="/tourism-portal/admin/destination-gallery.php?destination_id=<?= $d['destination_id'] ?>" class="btn btn-sm btn-outline-info" title="Manage Gallery"><i class="bi bi-images"></i></a>
            <a href="?delete=<?= $d['destination_id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this destination? This will also remove related hotels/restaurants.')" title="Delete"><i class="bi bi-trash"></i></a>
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
    <input type="hidden" name="destination_id" value="<?= $editData['destination_id'] ?? '' ?>">
    <input type="hidden" name="existing_image" value="<?= h($editData['image'] ?? '') ?>">
    <div class="row g-3">
      <div class="col-md-6">
        <label class="form-label">Destination Name</label>
        <input type="text" name="name" class="form-control" value="<?= h($editData['name'] ?? '') ?>" required>
      </div>
      <div class="col-md-6">
        <label class="form-label">State</label>
        <select name="state_id" class="form-select" required>
          <?php foreach ($states as $s): ?>
            <option value="<?= $s['state_id'] ?>" <?= (($editData['state_id'] ?? '') == $s['state_id']) ? 'selected' : '' ?>><?= h($s['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-6">
        <label class="form-label">Category</label>
        <input type="text" name="category" class="form-control" placeholder="Heritage, Beach, Hill Station..." value="<?= h($editData['category'] ?? '') ?>" required>
      </div>
      <div class="col-md-6">
        <label class="form-label">Location</label>
        <input type="text" name="location" class="form-control" value="<?= h($editData['location'] ?? '') ?>">
      </div>
      <div class="col-md-6">
        <label class="form-label">Popularity (0-100)</label>
        <input type="number" name="popularity" class="form-control" min="0" max="100" value="<?= h($editData['popularity'] ?? 70) ?>">
      </div>
      <div class="col-md-6">
        <label class="form-label">Image Upload <span class="text-muted small">(optional — keeps existing if blank)</span></label>
        <input type="file" name="image_file" class="form-control" accept=".jpg,.jpeg,.png,.webp">
      </div>
      <div class="col-12">
        <label class="form-label">Description</label>
        <textarea name="description" class="form-control" rows="4"><?= h($editData['description'] ?? '') ?></textarea>
      </div>
    </div>
    <button type="submit" name="save_destination" class="btn-brand mt-4">Save Destination</button>
  </form>
</div>
<?php endif; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>
