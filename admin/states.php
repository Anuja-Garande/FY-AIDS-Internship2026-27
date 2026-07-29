<?php
$pageTitle = 'Manage States';
require __DIR__ . '/includes/header.php';

$uploadDir = __DIR__ . '/../assets/images/states/';
$action = $_GET['action'] ?? 'list';
$editId = (int)($_GET['id'] ?? 0);

function handleStateImageUpload($fieldName, $uploadDir) {
    if (!empty($_FILES[$fieldName]['name']) && $_FILES[$fieldName]['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES[$fieldName]['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg','jpeg','png','webp'])) {
            $filename = uniqid('state_') . '.' . $ext;
            move_uploaded_file($_FILES[$fieldName]['tmp_name'], $uploadDir . $filename);
            return $filename;
        }
    }
    return null;
}

// ---- Save (Add / Edit) ----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_state'])) {
    $name = trim($_POST['name']);
    $tagline = trim($_POST['tagline']);
    $description = trim($_POST['description']);
    $existingImage = $_POST['existing_image'] ?? '';
    $uploaded = handleStateImageUpload('image_file', $uploadDir);
    $image = $uploaded ?: ($existingImage ?: 'default-state.jpg');

    if ($name === '') {
        setFlash('danger', 'State name is required.');
        redirect('/tourism-portal/admin/states.php?action=' . ($_POST['state_id'] ? 'edit&id='.$_POST['state_id'] : 'add'));
    }

    if ($_POST['state_id']) {
        $stmt = $pdo->prepare("UPDATE states SET name=?, tagline=?, description=?, image=? WHERE state_id=?");
        $stmt->execute([$name, $tagline, $description, $image, $_POST['state_id']]);
        setFlash('success', 'State updated successfully.');
    } else {
        $stmt = $pdo->prepare("INSERT INTO states (name, tagline, description, image) VALUES (?,?,?,?)");
        $stmt->execute([$name, $tagline, $description, $image]);
        setFlash('success', 'State added successfully.');
    }
    redirect('/tourism-portal/admin/states.php');
}

// ---- Delete ----
if (isset($_GET['delete'])) {
    $stateId = (int)$_GET['delete'];
    $countStmt = $pdo->prepare("SELECT COUNT(*) FROM destinations WHERE state_id = ?");
    $countStmt->execute([$stateId]);
    $destCount = $countStmt->fetchColumn();

    if ($destCount > 0) {
        setFlash('danger', "Cannot delete this state — it still has $destCount destination(s) linked to it. Delete or reassign those destinations first.");
    } else {
        $stmt = $pdo->prepare("DELETE FROM states WHERE state_id = ?");
        $stmt->execute([$stateId]);
        setFlash('success', 'State deleted.');
    }
    redirect('/tourism-portal/admin/states.php');
}

$editData = null;
if ($action === 'edit' && $editId) {
    $stmt = $pdo->prepare("SELECT * FROM states WHERE state_id = ?");
    $stmt->execute([$editId]);
    $editData = $stmt->fetch();
    if (!$editData) redirect('/tourism-portal/admin/states.php');
}

$states = $pdo->query("SELECT s.*, (SELECT COUNT(*) FROM destinations d WHERE d.state_id = s.state_id) AS dest_count
                        FROM states s ORDER BY s.name ASC")->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-3">
  <h6 class="mb-0"><?= $action === 'list' ? 'All States' : ($editData ? 'Edit State' : 'Add New State') ?></h6>
  <?php if ($action === 'list'): ?>
    <a href="?action=add" class="btn-brand btn-sm">+ Add State</a>
  <?php else: ?>
    <a href="/tourism-portal/admin/states.php" class="btn btn-outline-secondary btn-sm">&larr; Back to List</a>
  <?php endif; ?>
</div>

<?php if ($action === 'list'): ?>
<div class="admin-card">
  <div class="table-responsive">
    <table class="table table-admin align-middle">
      <thead><tr><th>#</th><th>Image</th><th>Name</th><th>Tagline</th><th>Destinations</th><th>Actions</th></tr></thead>
      <tbody>
        <?php foreach ($states as $s): ?>
        <tr>
          <td><?= $s['state_id'] ?></td>
          <td>
            <img src="/tourism-portal/assets/images/states/<?= h($s['image']) ?>" alt="<?= h($s['name']) ?>"
                 style="width:60px;height:44px;object-fit:cover;border-radius:8px;"
                 onerror="this.src='https://source.unsplash.com/100x80/?<?= urlencode($s['name']) ?>,india'">
          </td>
          <td><?= h($s['name']) ?></td>
          <td><?= h($s['tagline']) ?></td>
          <td><span class="badge-status badge-confirmed"><?= $s['dest_count'] ?> destination(s)</span></td>
          <td>
            <a href="?action=edit&id=<?= $s['state_id'] ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
            <a href="?delete=<?= $s['state_id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this state?')"><i class="bi bi-trash"></i></a>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($states)): ?><tr><td colspan="6" class="text-center text-muted">No states added yet.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php else: ?>
<div class="admin-card">
  <form method="post" enctype="multipart/form-data">
    <input type="hidden" name="state_id" value="<?= $editData['state_id'] ?? '' ?>">
    <input type="hidden" name="existing_image" value="<?= h($editData['image'] ?? '') ?>">
    <div class="row g-3">
      <div class="col-md-6">
        <label class="form-label">State Name</label>
        <input type="text" name="name" class="form-control" placeholder="e.g. Punjab" value="<?= h($editData['name'] ?? '') ?>" required>
      </div>
      <div class="col-md-6">
        <label class="form-label">Tagline</label>
        <input type="text" name="tagline" class="form-control" placeholder="e.g. Land of Five Rivers" value="<?= h($editData['tagline'] ?? '') ?>">
      </div>
      <div class="col-md-6">
        <label class="form-label">Cover Image Upload <span class="text-muted small">(optional — keeps existing if blank)</span></label>
        <input type="file" name="image_file" class="form-control" accept=".jpg,.jpeg,.png,.webp">
        <?php if (!empty($editData['image'])): ?>
          <div class="mt-2">
            <img src="/tourism-portal/assets/images/states/<?= h($editData['image']) ?>" style="width:120px;height:80px;object-fit:cover;border-radius:8px;"
                 onerror="this.src='https://source.unsplash.com/200x140/?<?= urlencode($editData['name']) ?>,india'">
          </div>
        <?php endif; ?>
      </div>
      <div class="col-12">
        <label class="form-label">Description</label>
        <textarea name="description" class="form-control" rows="4" placeholder="Short description of the state for travellers..."><?= h($editData['description'] ?? '') ?></textarea>
      </div>
    </div>
    <button type="submit" name="save_state" class="btn-brand mt-4">Save State</button>
  </form>

  <?php if ($editData): ?>
  <hr class="my-4">
  <p class="text-muted small mb-0">
    <i class="bi bi-info-circle"></i>
    This state currently has
    <?php
      $c = $pdo->prepare("SELECT COUNT(*) FROM destinations WHERE state_id = ?");
      $c->execute([$editData['state_id']]);
      echo $c->fetchColumn();
    ?> destination(s) linked to it.
    <a href="/tourism-portal/admin/destinations.php">Manage destinations</a>
  </p>
  <?php endif; ?>
</div>
<?php endif; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>
