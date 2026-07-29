<?php
$pageTitle = 'Manage Hotels';
require __DIR__ . '/includes/header.php';

$uploadDir = __DIR__ . '/../assets/images/destinations/';
$action = $_GET['action'] ?? 'list';
$editId = (int)($_GET['id'] ?? 0);

function handleImageUpload2($fieldName, $uploadDir) {
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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_hotel'])) {
    $destinationId = $_POST['destination_id'];
    $name = trim($_POST['name']);
    $priceRange = trim($_POST['price_range']);
    $contact = trim($_POST['contact']);
    $amenities = trim($_POST['amenities']);
    $existingImage = $_POST['existing_image'] ?? '';
    $uploaded = handleImageUpload2('image_file', $uploadDir);
    $image = $uploaded ?: ($existingImage ?: 'default-hotel.jpg');

    if ($_POST['hotel_id']) {
        $stmt = $pdo->prepare("UPDATE hotels SET destination_id=?, name=?, price_range=?, contact=?, amenities=?, image=? WHERE hotel_id=?");
        $stmt->execute([$destinationId, $name, $priceRange, $contact, $amenities, $image, $_POST['hotel_id']]);
        setFlash('success', 'Hotel updated successfully.');
    } else {
        $stmt = $pdo->prepare("INSERT INTO hotels (destination_id, name, price_range, contact, amenities, image) VALUES (?,?,?,?,?,?)");
        $stmt->execute([$destinationId, $name, $priceRange, $contact, $amenities, $image]);
        setFlash('success', 'Hotel added successfully.');
    }
    redirect('/tourism-portal/admin/hotels.php');
}

if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM hotels WHERE hotel_id = ?");
    $stmt->execute([(int)$_GET['delete']]);
    setFlash('success', 'Hotel deleted.');
    redirect('/tourism-portal/admin/hotels.php');
}

$destinationsList = $pdo->query("SELECT * FROM destinations ORDER BY name ASC")->fetchAll();

$editData = null;
if ($action === 'edit' && $editId) {
    $stmt = $pdo->prepare("SELECT * FROM hotels WHERE hotel_id = ?");
    $stmt->execute([$editId]);
    $editData = $stmt->fetch();
}

$hotels = $pdo->query("SELECT h.*, d.name AS dest_name FROM hotels h
                        JOIN destinations d ON d.destination_id = h.destination_id ORDER BY h.hotel_id DESC")->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-3">
  <h6 class="mb-0"><?= $action === 'list' ? 'All Hotels' : ($editData ? 'Edit Hotel' : 'Add New Hotel') ?></h6>
  <?php if ($action === 'list'): ?>
    <a href="?action=add" class="btn-brand btn-sm">+ Add Hotel</a>
  <?php else: ?>
    <a href="/tourism-portal/admin/hotels.php" class="btn btn-outline-secondary btn-sm">&larr; Back to List</a>
  <?php endif; ?>
</div>

<?php if ($action === 'list'): ?>
<div class="admin-card">
  <div class="table-responsive">
    <table class="table table-admin align-middle">
      <thead><tr><th>#</th><th>Hotel</th><th>Destination</th><th>Price Range</th><th>Contact</th><th>Actions</th></tr></thead>
      <tbody>
        <?php foreach ($hotels as $h): ?>
        <tr>
          <td><?= $h['hotel_id'] ?></td>
          <td><?= h($h['name']) ?></td>
          <td><?= h($h['dest_name']) ?></td>
          <td><?= h($h['price_range']) ?></td>
          <td><?= h($h['contact']) ?></td>
          <td>
            <a href="?action=edit&id=<?= $h['hotel_id'] ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
            <a href="?delete=<?= $h['hotel_id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this hotel?')"><i class="bi bi-trash"></i></a>
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
    <input type="hidden" name="hotel_id" value="<?= $editData['hotel_id'] ?? '' ?>">
    <input type="hidden" name="existing_image" value="<?= h($editData['image'] ?? '') ?>">
    <div class="row g-3">
      <div class="col-md-6">
        <label class="form-label">Hotel Name</label>
        <input type="text" name="name" class="form-control" value="<?= h($editData['name'] ?? '') ?>" required>
      </div>
      <div class="col-md-6">
        <label class="form-label">Destination</label>
        <select name="destination_id" class="form-select" required>
          <?php foreach ($destinationsList as $d): ?>
            <option value="<?= $d['destination_id'] ?>" <?= (($editData['destination_id'] ?? '') == $d['destination_id']) ? 'selected' : '' ?>><?= h($d['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-6">
        <label class="form-label">Price Range</label>
        <input type="text" name="price_range" class="form-control" placeholder="₹3,000 - ₹8,000" value="<?= h($editData['price_range'] ?? '') ?>">
      </div>
      <div class="col-md-6">
        <label class="form-label">Contact Number</label>
        <input type="text" name="contact" class="form-control" value="<?= h($editData['contact'] ?? '') ?>">
      </div>
      <div class="col-md-6">
        <label class="form-label">Amenities</label>
        <input type="text" name="amenities" class="form-control" placeholder="Pool, WiFi, Breakfast..." value="<?= h($editData['amenities'] ?? '') ?>">
      </div>
      <div class="col-md-6">
        <label class="form-label">Image Upload <span class="text-muted small">(optional)</span></label>
        <input type="file" name="image_file" class="form-control" accept=".jpg,.jpeg,.png,.webp">
      </div>
    </div>
    <button type="submit" name="save_hotel" class="btn-brand mt-4">Save Hotel</button>
  </form>
</div>
<?php endif; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>
