<?php
// C:\xampp\htdocs\NewProject\admin\restaurants.php
// Admin CRUD Management for Restaurants

require_once '../config/db_connect.php';
require_once 'includes/admin_header.php';

$destinations = $pdo->query("SELECT id, name FROM destinations ORDER BY name ASC")->fetchAll();

// 1. Handle CRUD Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = isset($_POST['action']) ? trim($_POST['action']) : '';

    if ($action === 'add_restaurant') {
        $name = trim($_POST['name']);
        $destination_id = intval($_POST['destination_id']);
        $cuisine_type = trim($_POST['cuisine_type']);
        $price_range = trim($_POST['price_range']);
        $rating = floatval($_POST['rating']);
        $image = trim($_POST['image']);
        $contact = trim($_POST['contact']);

        try {
            $stmt = $pdo->prepare("INSERT INTO restaurants (name, destination_id, cuisine_type, price_range, rating, image, contact) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$name, $destination_id, $cuisine_type, $price_range, $rating, $image, $contact]);
            $_SESSION['admin_success'] = "Restaurant added successfully.";
        } catch (\PDOException $e) {
            $_SESSION['admin_error'] = "Failed to add restaurant: " . $e->getMessage();
        }
        header("Location: restaurants.php");
        exit;
    }

    if ($action === 'delete_restaurant') {
        $id = intval($_POST['id']);
        try {
            $stmt = $pdo->prepare("DELETE FROM restaurants WHERE id = ?");
            $stmt->execute([$id]);
            $_SESSION['admin_success'] = "Restaurant deleted.";
        } catch (\PDOException $e) {
            $_SESSION['admin_error'] = "Failed to delete restaurant: " . $e->getMessage();
        }
        header("Location: restaurants.php");
        exit;
    }
}

// 2. Fetch Restaurants
$restaurants = $pdo->query("SELECT r.*, d.name AS dest_name FROM restaurants r LEFT JOIN destinations d ON r.destination_id = d.id ORDER BY r.id DESC")->fetchAll();
?>

<div class="card border-0 shadow-sm rounded-3 bg-white p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0 text-dark"><i class="bi-cup-hot-fill text-danger me-2"></i>Destination Restaurants</h4>
        <button class="btn btn-primary btn-sm rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#addRestModal">
            <i class="bi-plus-lg me-1"></i>Add Restaurant
        </button>
    </div>

    <?php if (!empty($restaurants)): ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle small">
                <thead class="table-light">
                    <tr>
                        <th>Image</th>
                        <th>Restaurant Name</th>
                        <th>Destination</th>
                        <th>Cuisine</th>
                        <th>Price Range</th>
                        <th>Rating</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($restaurants as $r): ?>
                        <tr>
                            <td><img src="<?php echo htmlspecialchars(get_image_url($r['image'])); ?>" style="width: 50px; height: 50px; object-fit: cover;" class="rounded-3"></td>
                            <td><strong><?php echo htmlspecialchars($r['name']); ?></strong></td>
                            <td><?php echo htmlspecialchars($r['dest_name'] ?? 'General'); ?></td>
                            <td><?php echo htmlspecialchars($r['cuisine_type']); ?></td>
                            <td><span class="badge bg-secondary"><?php echo htmlspecialchars($r['price_range']); ?></span></td>
                            <td><span class="badge bg-warning text-dark">⭐ <?php echo number_format($r['rating'], 1); ?></span></td>
                            <td>
                                <form action="restaurants.php" method="POST" class="d-inline" onsubmit="return confirm('Delete restaurant?');">
                                    <input type="hidden" name="action" value="delete_restaurant">
                                    <input type="hidden" name="id" value="<?php echo $r['id']; ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi-trash"></i> Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <p class="text-muted text-center py-4 mb-0">No restaurants added yet.</p>
    <?php endif; ?>
</div>

<!-- Add Modal -->
<div class="modal fade" id="addRestModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="restaurants.php" method="POST">
                <input type="hidden" name="action" value="add_restaurant">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Add Restaurant</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body row g-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Restaurant Name</label>
                        <input type="text" name="name" class="form-control" required placeholder="e.g. Johnson's Cafe">
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
                        <label class="form-label small fw-bold">Cuisine Type</label>
                        <input type="text" name="cuisine_type" class="form-control" required placeholder="e.g. Continental, Seafood">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold">Price Range</label>
                        <select name="price_range" class="form-select">
                            <option value="Budget">Budget</option>
                            <option value="Mid-Range" selected>Mid-Range</option>
                            <option value="Luxury">Luxury</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold">Rating</label>
                        <input type="number" step="0.1" name="rating" class="form-control" value="4.5" min="1" max="5">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Image URL</label>
                        <input type="text" name="image" class="form-control" required placeholder="https://images.unsplash.com/...">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Contact Number</label>
                        <input type="text" name="contact" class="form-control" placeholder="+91 98765 12345">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary rounded-pill px-4">Add Restaurant</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once 'includes/admin_footer.php'; ?>
