<?php
$pageTitle = 'Destination Gallery';
require __DIR__ . '/includes/header.php';

$uploadDir = __DIR__ . '/../assets/images/destinations/';
$destinationId = (int)($_GET['destination_id'] ?? 0);

$destStmt = $pdo->prepare("SELECT * FROM destinations WHERE destination_id = ?");
$destStmt->execute([$destinationId]);
$destination = $destStmt->fetch();
if (!$destination) { redirect('/tourism-portal/admin/destinations.php'); }

// Upload new gallery images (supports multiple files at once)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_images'])) {
    $count = 0;
    if (!empty($_FILES['images']['name'][0])) {
        foreach ($_FILES['images']['name'] as $i => $name) {
            if ($_FILES['images']['error'][$i] === UPLOAD_ERR_OK) {
                $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
                if (in_array($ext, ['jpg','jpeg','png','webp'])) {
                    $filename = uniqid('gal_') . '.' . $ext;
                    move_uploaded_file($_FILES['images']['tmp_name'][$i], $uploadDir . $filename);
                    $ins = $pdo->prepare("INSERT INTO destination_images (destination_id, image, caption) VALUES (?, ?, ?)");
                    $ins->execute([$destinationId, $filename, trim($_POST['caption'] ?? '')]);
                    $count++;
                }
            }
        }
    }
    setFlash($count ? 'success' : 'danger', $count ? "$count photo(s) uploaded successfully." : 'No valid images were uploaded.');
    redirect('/tourism-portal/admin/destination-gallery.php?destination_id=' . $destinationId);
}

if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM destination_images WHERE image_id = ? AND destination_id = ?");
    $stmt->execute([(int)$_GET['delete'], $destinationId]);
    setFlash('success', 'Photo removed from gallery.');
    redirect('/tourism-portal/admin/destination-gallery.php?destination_id=' . $destinationId);
}

$images = $pdo->prepare("SELECT * FROM destination_images WHERE destination_id = ? ORDER BY image_id DESC");
$images->execute([$destinationId]);
$images = $images->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-3">
  <h6 class="mb-0">Gallery for: <?= h($destination['name']) ?></h6>
  <a href="/tourism-portal/admin/destinations.php" class="btn btn-outline-secondary btn-sm">&larr; Back to Destinations</a>
</div>

<div class="admin-card mb-4">
  <form method="post" enctype="multipart/form-data">
    <div class="row g-3 align-items-end">
      <div class="col-md-6">
        <label class="form-label">Upload Photos <span class="text-muted small">(you can select multiple)</span></label>
        <input type="file" name="images[]" class="form-control" accept=".jpg,.jpeg,.png,.webp" multiple required>
      </div>
      <div class="col-md-4">
        <label class="form-label">Caption <span class="text-muted small">(applied to all selected)</span></label>
        <input type="text" name="caption" class="form-control" placeholder="e.g. Sunset view">
      </div>
      <div class="col-md-2">
        <button type="submit" name="upload_images" class="btn-brand w-100">Upload</button>
      </div>
    </div>
  </form>
</div>

<div class="admin-card">
  <h6 class="mb-3">Current Photos (<?= count($images) ?>)</h6>
  <div class="row g-3">
    <?php foreach ($images as $img): ?>
    <div class="col-md-3 col-6">
      <div class="position-relative">
        <img src="/tourism-portal/assets/images/destinations/<?= h($img['image']) ?>" class="w-100 rounded-3"
             style="height:130px;object-fit:cover;" onerror="this.src='https://source.unsplash.com/300x200/?india'">
        <a href="?destination_id=<?= $destinationId ?>&delete=<?= $img['image_id'] ?>"
           class="btn btn-sm btn-danger position-absolute top-0 end-0 m-1"
           onclick="return confirm('Remove this photo?')"><i class="bi bi-trash"></i></a>
      </div>
      <?php if ($img['caption']): ?><p class="small text-muted mt-1 mb-0"><?= h($img['caption']) ?></p><?php endif; ?>
    </div>
    <?php endforeach; ?>
    <?php if (empty($images)): ?><p class="text-muted">No photos uploaded yet for this destination.</p><?php endif; ?>
  </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
