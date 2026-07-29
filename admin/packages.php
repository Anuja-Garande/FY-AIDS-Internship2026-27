<?php
// C:\xampp\htdocs\NewProject\admin\packages.php
// Admin CRUD Management for Tour Packages

require_once '../config/db_connect.php';
require_once 'includes/admin_header.php';

// Fetch destinations for dropdown select
$destinations = $pdo->query("SELECT id, name, country FROM destinations ORDER BY name ASC")->fetchAll();

// 1. Handle CRUD Actions POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = isset($_POST['action']) ? trim($_POST['action']) : '';

    if ($action === 'add_package') {
        $name = trim($_POST['name']);
        $description = trim($_POST['description']);
        $duration_days = intval($_POST['duration_days']);
        $price = floatval($_POST['price']);
        $destination_id = intval($_POST['destination_id']);
        $image = trim($_POST['image']);
        $inclusions = trim($_POST['inclusions']);

        try {
            $stmt = $pdo->prepare("INSERT INTO packages (name, description, duration_days, price, destination_id, image, inclusions) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$name, $description, $duration_days, $price, $destination_id, $image, $inclusions]);
            $_SESSION['admin_success'] = "Tour Package created successfully.";
        } catch (\PDOException $e) {
            $_SESSION['admin_error'] = "Failed to create package: " . $e->getMessage();
        }
        header("Location: packages.php");
        exit;
    }

    if ($action === 'edit_package') {
        $id = intval($_POST['id']);
        $name = trim($_POST['name']);
        $description = trim($_POST['description']);
        $duration_days = intval($_POST['duration_days']);
        $price = floatval($_POST['price']);
        $destination_id = intval($_POST['destination_id']);
        $image = trim($_POST['image']);
        $inclusions = trim($_POST['inclusions']);

        try {
            $stmt = $pdo->prepare("UPDATE packages SET name=?, description=?, duration_days=?, price=?, destination_id=?, image=?, inclusions=? WHERE id=?");
            $stmt->execute([$name, $description, $duration_days, $price, $destination_id, $image, $inclusions, $id]);
            $_SESSION['admin_success'] = "Tour Package updated.";
        } catch (\PDOException $e) {
            $_SESSION['admin_error'] = "Failed to update package: " . $e->getMessage();
        }
        header("Location: packages.php");
        exit;
    }

    if ($action === 'delete_package') {
        $id = intval($_POST['id']);
        try {
            $stmt = $pdo->prepare("DELETE FROM packages WHERE id = ?");
            $stmt->execute([$id]);
            $_SESSION['admin_success'] = "Tour Package deleted.";
        } catch (\PDOException $e) {
            $_SESSION['admin_error'] = "Failed to delete package: " . $e->getMessage();
        }
        header("Location: packages.php");
        exit;
    }
}

// 2. Fetch Packages
$packages = $pdo->query("SELECT p.*, d.name AS dest_name FROM packages p LEFT JOIN destinations d ON p.destination_id = d.id ORDER BY p.id DESC")->fetchAll();
?>

<div class="card border-0 shadow-sm rounded-3 bg-white p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0 text-dark"><i class="bi-box-seam-fill text-primary me-2"></i>Tour Packages Directory</h4>
        <button class="btn btn-primary btn-sm rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#addPackageModal">
            <i class="bi-plus-lg me-1"></i>Add New Package
        </button>
    </div>

    <?php if (!empty($packages)): ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle small">
                <thead class="table-light">
                    <tr>
                        <th>Image</th>
                        <th>Package Name</th>
                        <th>Destination</th>
                        <th>Duration</th>
                        <th>Price</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($packages as $pkg): ?>
                        <tr>
                            <td><img src="<?php echo htmlspecialchars(get_image_url($pkg['image'])); ?>" style="width: 50px; height: 50px; object-fit: cover;" class="rounded-3"></td>
                            <td><strong><?php echo htmlspecialchars($pkg['name']); ?></strong></td>
                            <td><?php echo htmlspecialchars($pkg['dest_name'] ?? 'General'); ?></td>
                            <td><?php echo $pkg['duration_days']; ?> Days</td>
                            <td><strong class="text-success fs-6">₹<?php echo number_format($pkg['price'], 2); ?></strong></td>
                            <td>
                                <button class="btn btn-sm btn-outline-secondary me-1" data-bs-toggle="modal" data-bs-target="#editPkgModal<?php echo $pkg['id']; ?>"><i class="bi-pencil"></i> Edit</button>
                                <form action="packages.php" method="POST" class="d-inline" onsubmit="return confirm('Delete package?');">
                                    <input type="hidden" name="action" value="delete_package">
                                    <input type="hidden" name="id" value="<?php echo $pkg['id']; ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi-trash"></i> Delete</button>
                                </form>
                            </td>
                        </tr>

                        <!-- Edit Modal -->
                        <div class="modal fade" id="editPkgModal<?php echo $pkg['id']; ?>" tabindex="-1">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <form action="packages.php" method="POST">
                                        <input type="hidden" name="action" value="edit_package">
                                        <input type="hidden" name="id" value="<?php echo $pkg['id']; ?>">
                                        <div class="modal-header">
                                            <h5 class="modal-title fw-bold">Edit Tour Package</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body row g-3">
                                            <div class="col-md-6">
                                                <label class="form-label small fw-bold">Package Name</label>
                                                <input type="text" name="name" class="form-control" required value="<?php echo htmlspecialchars($pkg['name']); ?>">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label small fw-bold">Destination</label>
                                                <select name="destination_id" class="form-select" required>
                                                    <?php foreach ($destinations as $d): ?>
                                                        <option value="<?php echo $d['id']; ?>" <?php echo $pkg['destination_id'] == $d['id'] ? 'selected' : ''; ?>>
                                                            <?php echo htmlspecialchars($d['name']); ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label small fw-bold">Duration (Days)</label>
                                                <input type="number" name="duration_days" class="form-control" required value="<?php echo $pkg['duration_days']; ?>">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label small fw-bold">Price (₹)</label>
                                                <input type="number" step="0.01" name="price" class="form-control" required value="<?php echo $pkg['price']; ?>">
                                            </div>
                                            <div class="col-12">
                                                <label class="form-label small fw-bold">Image URL</label>
                                                <input type="text" name="image" class="form-control" required value="<?php echo htmlspecialchars($pkg['image']); ?>">
                                            </div>
                                            <div class="col-12">
                                                <label class="form-label small fw-bold">Inclusions (Comma Separated)</label>
                                                <input type="text" name="inclusions" class="form-control" value="<?php echo htmlspecialchars($pkg['inclusions']); ?>">
                                            </div>
                                            <div class="col-12">
                                                <label class="form-label small fw-bold">Description</label>
                                                <textarea name="description" rows="4" class="form-control" required><?php echo htmlspecialchars($pkg['description']); ?></textarea>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="submit" class="btn btn-primary rounded-pill px-4">Save Changes</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <p class="text-muted text-center py-4 mb-0">No tour packages created yet.</p>
    <?php endif; ?>
</div>

<!-- Add Modal -->
<div class="modal fade" id="addPackageModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="packages.php" method="POST">
                <input type="hidden" name="action" value="add_package">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Add Tour Package</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body row g-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Package Name</label>
                        <input type="text" name="name" class="form-control" required placeholder="e.g. Goa Beach & Water Sports">
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
                        <label class="form-label small fw-bold">Duration (Days)</label>
                        <input type="number" name="duration_days" class="form-control" required value="4">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Price (₹)</label>
                        <input type="number" step="0.01" name="price" class="form-control" required placeholder="12999">
                    </div>
                    <div class="col-12">
                        <label class="form-label small fw-bold">Image URL</label>
                        <input type="text" name="image" class="form-control" required placeholder="https://images.unsplash.com/...">
                    </div>
                    <div class="col-12">
                        <label class="form-label small fw-bold">Inclusions (Comma Separated)</label>
                        <input type="text" name="inclusions" class="form-control" placeholder="Hotel Stay, Breakfast, Airport Transfer">
                    </div>
                    <div class="col-12">
                        <label class="form-label small fw-bold">Description</label>
                        <textarea name="description" rows="4" class="form-control" required placeholder="Detailed itinerary description..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary rounded-pill px-4">Create Package</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once 'includes/admin_footer.php'; ?>
