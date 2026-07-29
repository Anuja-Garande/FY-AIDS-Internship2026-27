<?php
// C:\xampp\htdocs\NewProject\admin\hotels.php
// Admin CRUD Management for Hotels & Resorts

require_once '../config/db_connect.php';
require_once 'includes/admin_header.php';

$destinations = $pdo->query("SELECT id, name, country FROM destinations ORDER BY name ASC")->fetchAll();

// 1. Handle CRUD Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = isset($_POST['action']) ? trim($_POST['action']) : '';

    if ($action === 'add_hotel') {
        $name = trim($_POST['name']);
        $destination_id = intval($_POST['destination_id']);
        $star_rating = intval($_POST['star_rating']);
        $price_per_night = floatval($_POST['price_per_night']);
        $amenities = trim($_POST['amenities']);
        $image = trim($_POST['image']);
        $contact = trim($_POST['contact']);

        try {
            $stmt = $pdo->prepare("INSERT INTO hotels (name, destination_id, star_rating, price_per_night, amenities, image, contact) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$name, $destination_id, $star_rating, $price_per_night, $amenities, $image, $contact]);
            $_SESSION['admin_success'] = "Hotel added successfully.";
        } catch (\PDOException $e) {
            $_SESSION['admin_error'] = "Failed to add hotel: " . $e->getMessage();
        }
        header("Location: hotels.php");
        exit;
    }

    if ($action === 'delete_hotel') {
        $id = intval($_POST['id']);
        try {
            $stmt = $pdo->prepare("DELETE FROM hotels WHERE id = ?");
            $stmt->execute([$id]);
            $_SESSION['admin_success'] = "Hotel deleted.";
        } catch (\PDOException $e) {
            $_SESSION['admin_error'] = "Failed to delete hotel: " . $e->getMessage();
        }
        header("Location: hotels.php");
        exit;
    }
}

// 2. Fetch Hotels
$hotels = $pdo->query("SELECT h.*, d.name AS dest_name FROM hotels h LEFT JOIN destinations d ON h.destination_id = d.id ORDER BY h.id DESC")->fetchAll();
?>

<div class="card border-0 shadow-sm rounded-3 bg-white p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0 text-dark"><i class="bi-building text-primary me-2"></i>Hotels & Accommodation Directory</h4>
        <button class="btn btn-primary btn-sm rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#addHotelModal">
            <i class="bi-plus-lg me-1"></i>Add New Hotel
        </button>
    </div>

    <?php if (!empty($hotels)): ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle small">
                <thead class="table-light">
                    <tr>
                        <th>Image</th>
                        <th>Hotel Name</th>
                        <th>Destination</th>
                        <th>Stars</th>
                        <th>Price/Night</th>
                        <th>Contact</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($hotels as $h): ?>
                        <tr>
                            <td><img src="<?php echo htmlspecialchars(get_image_url($h['image'])); ?>" style="width: 50px; height: 50px; object-fit: cover;" class="rounded-3"></td>
                            <td><strong><?php echo htmlspecialchars($h['name']); ?></strong></td>
                            <td><?php echo htmlspecialchars($h['dest_name'] ?? 'General'); ?></td>
                            <td><span class="text-warning"><?php for ($s=0; $s<$h['star_rating']; $s++): ?>★<?php endfor; ?></span></td>
                            <td><strong class="text-success fs-6">₹<?php echo number_format($h['price_per_night'], 2); ?></strong></td>
                            <td><?php echo htmlspecialchars($h['contact']); ?></td>
                            <td>
                                <form action="hotels.php" method="POST" class="d-inline" onsubmit="return confirm('Delete hotel?');">
                                    <input type="hidden" name="action" value="delete_hotel">
                                    <input type="hidden" name="id" value="<?php echo $h['id']; ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi-trash"></i> Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <p class="text-muted text-center py-4 mb-0">No hotels added yet.</p>
    <?php endif; ?>
</div>

<!-- Add Hotel Modal -->
<div class="modal fade" id="addHotelModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="hotels.php" method="POST">
                <input type="hidden" name="action" value="add_hotel">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Add Hotel / Resort</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body row g-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Hotel Name</label>
                        <input type="text" name="name" class="form-control" required placeholder="e.g. Grand Palace Resort">
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
                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Star Rating</label>
                        <select name="star_rating" class="form-select">
                            <option value="5">5 Star</option>
                            <option value="4" selected>4 Star</option>
                            <option value="3">3 Star</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Price per Night (₹)</label>
                        <input type="number" step="0.01" name="price_per_night" class="form-control" required placeholder="6500">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Contact Phone</label>
                        <input type="text" name="contact" class="form-control" placeholder="+91 98765 43210">
                    </div>
                    <div class="col-12">
                        <label class="form-label small fw-bold">Image URL</label>
                        <input type="text" name="image" class="form-control" required placeholder="https://images.unsplash.com/...">
                    </div>
                    <div class="col-12">
                        <label class="form-label small fw-bold">Amenities (Comma Separated)</label>
                        <input type="text" name="amenities" class="form-control" placeholder="Free WiFi, Pool, Spa, Breakfast, AC">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary rounded-pill px-4">Add Hotel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once 'includes/admin_footer.php'; ?>
