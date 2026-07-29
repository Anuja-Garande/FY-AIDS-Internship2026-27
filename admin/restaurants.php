<?php
$pageTitle = 'Manage Restaurants';
require __DIR__ . '/includes/header.php';

$uploadDir = __DIR__ . '/../assets/images/destinations/';
$action = $_GET['action'] ?? 'list';
$editId = (int)($_GET['id'] ?? 0);

function handleImageUpload3($fieldName, $uploadDir) {
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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_restaurant'])) {
    $destinationId = $_POST['destination_id'];
    $name = trim($_POST['name']);
    $cuisine = trim($_POST['cuisine_type']);
    $priceRange = trim($_POST['price_range']);
    $contact = trim($_POST['contact']);
    $existingImage = $_POST['existing_image'] ?? '';
    $uploaded = handleImageUpload3('image_file', $uploadDir);
    $image = $uploaded ?: ($existingImage ?: 'default-restaurant.jpg');

    if ($_POST['restaurant_id']) {
        $stmt = $pdo->prepare("UPDATE restaurants SET destination_id=?, name=?, cuisine_type=?, price_range=?, contact=?, image=? WHERE restaurant_id=?");
        $stmt->execute([$destinationId, $name, $cuisine, $priceRange, $contact, $image, $_POST['restaurant_id']]);
        setFlash('success', 'Restaurant updated successfully.');
    } else {
        $stmt = $pdo->prepare("INSERT INTO restaurants (destination_id, name, cuisine_type, price_range, contact, image) VALUES (?,?,?,?,?,?)");
        $stmt->execute([$destinationId, $name, $cuisine, $priceRange, $contact, $image]);
        setFlash('success', 'Restaurant added successfully.');
    }
    redirect('/tourism-portal/admin/restaurants.php');
}

if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM restaurants WHERE restaurant_id = ?");
    $stmt->execute([(int)$_GET['delete']]);
    setFlash('success', 'Restaurant deleted.');
    redirect('/tourism-portal/admin/restaurants.php');
}

$destinationsList = $pdo->query("SELECT * FROM destinations ORDER BY name ASC")->fetchAll();

$editData = null;
if ($action === 'edit' && $editId) {
    $stmt = $pdo->prepare("SELECT * FROM restaurants WHERE restaurant_id = ?");
    $stmt->execute([$editId]);
    $editData = $stmt->fetch();
}

$restaurants = $pdo->query("SELECT r.*, d.name AS dest_name FROM restaurants r
                             JOIN destinations d ON d.destination_id = r.destination_id ORDER BY r.restaurant_id DESC")->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-3">
  <h6 class="mb-0"><?= $action === 'list' ? 'All Restaurants' : ($editData ? 'Edit Restaurant' : 'Add New Restaurant') ?></h6>
  <?php if ($action === 'list'): ?>
    <a href="?action=add" class="btn-brand btn-sm">+ Add Restaurant</a>
  <?php else: ?>
    <a href="/tourism-portal/admin/restaurants.php" class="btn btn-outline-secondary btn-sm">&larr; Back to List</a>
  <?php endif; ?>
</div>

<?php if ($action === 'list'): ?>
<div class="admin-card">
  <div class="table-responsive">
    <table class="table table-admin align-middle">
      <thead><tr><th>#</th><th>Restaurant</th><th>Destination</th><th>Cuisine</th><th>Price Range</th><th>Actions</th></tr></thead>
      <tbody>
        <?php foreach ($restaurants as $r): ?>
        <tr>
          <td><?= $r['restaurant_id'] ?></td>
          <td><?= h($r['name']) ?></td>
          <td><?= h($r['dest_name']) ?></td>
          <td><?= h($r['cuisine_type']) ?></td>
          <td><?= h($r['price_range']) ?></td>
          <td>
            <a href="?action=edit&id=<?= $r['restaurant_id'] ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
            <a href="?delete=<?= $r['restaurant_id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this restaurant?')"><i class="bi bi-trash"></i></a>
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
    <input type="hidden" name="restaurant_id" value="<?= $editData['restaurant_id'] ?? '' ?>">
    <input type="hidden" name="existing_image" value="<?= h($editData['image'] ?? '') ?>">
    <div class="row g-3">
      <div class="col-md-6">
        <label class="form-label">Restaurant Name</label>
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
        <label class="form-label">Cuisine Type</label>
        <input type="text" name="cuisine_type" class="form-control" placeholder="Rajasthani, Seafood, Multi-cuisine..." value="<?= h($editData['cuisine_type'] ?? '') ?>">
      </div>
      <div class="col-md-6">
        <label class="form-label">Price Range</label>
        <input type="text" name="price_range" class="form-control" placeholder="₹300 - ₹900" value="<?= h($editData['price_range'] ?? '') ?>">
      </div>
      <div class="col-md-6">
        <label class="form-label">Contact Number</label>
        <input type="text" name="contact" class="form-control" value="<?= h($editData['contact'] ?? '') ?>">
      </div>
      <div class="col-md-6">
        <label class="form-label">Image Upload <span class="text-muted small">(optional)</span></label>
        <input type="file" name="image_file" class="form-control" accept=".jpg,.jpeg,.png,.webp">
      </div>
    </div>
    <button type="submit" name="save_restaurant" class="btn-brand mt-4">Save Restaurant</button>
  </form>
</div>
<?php endif; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>
