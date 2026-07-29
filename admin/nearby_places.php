<?php
// C:\xampp\htdocs\NewProject\admin\nearby_places.php
// Admin CRUD Management for Nearby Attractions

require_once '../config/db_connect.php';
require_once 'includes/admin_header.php';

$destinations = $pdo->query("SELECT id, name FROM destinations ORDER BY name ASC")->fetchAll();

// 1. Handle Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = isset($_POST['action']) ? trim($_POST['action']) : '';

    if ($action === 'add_nearby') {
        $destination_id = intval($_POST['destination_id']);
        $name = trim($_POST['name']);
        $description = trim($_POST['description']);
        $image = trim($_POST['image']);
        $distance_km = floatval($_POST['distance_km']);

        try {
            $stmt = $pdo->prepare("INSERT INTO nearby_places (destination_id, name, description, image, distance_km) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$destination_id, $name, $description, $image, $distance_km]);
            $_SESSION['admin_success'] = "Nearby Place added successfully.";
        } catch (\PDOException $e) {
            $_SESSION['admin_error'] = "Failed to add place: " . $e->getMessage();
        }
        header("Location: nearby_places.php");
        exit;
    }

    if ($action === 'delete_nearby') {
        $id = intval($_POST['id']);
        try {
            $stmt = $pdo->prepare("DELETE FROM nearby_places WHERE id = ?");
            $stmt->execute([$id]);
            $_SESSION['admin_success'] = "Nearby Place deleted.";
        } catch (\PDOException $e) {
            $_SESSION['admin_error'] = "Failed to delete place: " . $e->getMessage();
        }
        header("Location: nearby_places.php");
        exit;
    }
}

// 2. Fetch Nearby Places
$nearby_places = $pdo->query("SELECT np.*, d.name AS dest_name FROM nearby_places np LEFT JOIN destinations d ON np.destination_id = d.id ORDER BY np.id DESC")->fetchAll();
?>

<div class="card border-0 shadow-sm rounded-3 bg-white p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0 text-dark"><i class="bi-pin-map-fill text-primary me-2"></i>Nearby Attractions to Visit</h4>
        <button class="btn btn-primary btn-sm rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#addNearbyModal">
            <i class="bi-plus-lg me-1"></i>Add Nearby Place
        </button>
    </div>

    <?php if (!empty($nearby_places)): ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle small">
                <thead class="table-light">
                    <tr>
                        <th>Image</th>
                        <th>Attraction Name</th>
                        <th>Parent Destination</th>
                        <th>Distance (km)</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($nearby_places as $np): ?>
                        <tr>
                            <td><img src="<?php echo htmlspecialchars(get_image_url($np['image'])); ?>" style="width: 50px; height: 50px; object-fit: cover;" class="rounded-3"></td>
                            <td><strong><?php echo htmlspecialchars($np['name']); ?></strong></td>
                            <td><?php echo htmlspecialchars($np['dest_name'] ?? 'General'); ?></td>
                            <td><span class="badge bg-secondary"><?php echo number_format($np['distance_km'], 1); ?> km</span></td>
                            <td>
                                <form action="nearby_places.php" method="POST" class="d-inline" onsubmit="return confirm('Delete attraction?');">
                                    <input type="hidden" name="action" value="delete_nearby">
                                    <input type="hidden" name="id" value="<?php echo $np['id']; ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi-trash"></i> Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <p class="text-muted text-center py-4 mb-0">No nearby places added yet.</p>
    <?php endif; ?>
</div>

<!-- Add Modal -->
<div class="modal fade" id="addNearbyModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="nearby_places.php" method="POST">
                <input type="hidden" name="action" value="add_nearby">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Add Nearby Attraction</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body row g-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Attraction Name</label>
                        <input type="text" name="name" class="form-control" required placeholder="e.g. Solang Valley">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Destination</label>
                        <select name="destination_id" class="form-select" required>
                            <option value="">Select Destination</option>
                            <?php foreach ($destinations as $d): ?>
                                <option value="<?php echo $d['id']; ?>"><?php echo htmlspecialchars($d['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Distance (in km)</label>
                        <input type="number" step="0.1" name="distance_km" class="form-control" required value="10.0">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Image URL</label>
                        <input type="text" name="image" class="form-control" required placeholder="https://images.unsplash.com/...">
                    </div>
                    <div class="col-12">
                        <label class="form-label small fw-bold">Description</label>
                        <textarea name="description" rows="3" class="form-control" required placeholder="Short description of this attraction..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary rounded-pill px-4">Add Attraction</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once 'includes/admin_footer.php'; ?>
