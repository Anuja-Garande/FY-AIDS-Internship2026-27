<?php
// C:\xampp\htdocs\NewProject\admin\destinations.php
// Manage Destinations CRUD Page with file upload capability

require_once '../config/db_connect.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Process Actions (Add, Edit, Delete) BEFORE header HTML output
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = isset($_POST['action']) ? trim($_POST['action']) : '';

    // Add Destination
    if ($action === 'add') {
        $name = trim($_POST['name']);
        $country = trim($_POST['country']);
        $continent = trim($_POST['continent']);
        $region_type = isset($_POST['region_type']) && in_array($_POST['region_type'], ['National', 'International']) 
            ? $_POST['region_type'] 
            : ($country === 'India' ? 'National' : 'International');
        $category_id = isset($_POST['category_id']) ? intval($_POST['category_id']) : 0;
        $description = trim($_POST['description']);
        $best_time_to_visit = trim($_POST['best_time_to_visit']);
        $price_range = isset($_POST['price_range']) ? trim($_POST['price_range']) : 'Mid-Range';
        $latitude = !empty($_POST['latitude']) ? floatval($_POST['latitude']) : null;
        $longitude = !empty($_POST['longitude']) ? floatval($_POST['longitude']) : null;
        $nearby_attractions = trim($_POST['nearby_attractions'] ?? '');

        // Handle Image Upload
        $image_name = 'default_destination.jpg';
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $file_tmp = $_FILES['image']['tmp_name'];
            $file_name = $_FILES['image']['name'];
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            
            $allowed_exts = ['jpg', 'jpeg', 'png', 'webp'];
            if (in_array($file_ext, $allowed_exts)) {
                $upload_dir = '../uploads/';
                if (!file_exists($upload_dir)) {
                    @mkdir($upload_dir, 0777, true);
                }
                $new_image_name = time() . '_' . preg_replace("/[^a-zA-Z0-9.]/", "_", $file_name);
                
                if (move_uploaded_file($file_tmp, $upload_dir . $new_image_name)) {
                    $image_name = $new_image_name;
                }
            } else {
                $_SESSION['admin_error'] = "Invalid image file type. Only JPG, JPEG, PNG, and WEBP files are allowed.";
            }
        }

        if (empty($name) || empty($country) || empty($continent) || empty($description)) {
            $_SESSION['admin_error'] = "Please fill in all required fields (Name, Country, Continent, Description).";
        } else {
            $catVal = $category_id > 0 ? $category_id : null;
            try {
                $stmt = $pdo->prepare("INSERT INTO destinations (name, country, continent, region_type, category_id, description, best_time_to_visit, image, rating, price_range, latitude, longitude, nearby_attractions) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 4.50, ?, ?, ?, ?)");
                $stmt->execute([$name, $country, $continent, $region_type, $catVal, $description, $best_time_to_visit, $image_name, $price_range, $latitude, $longitude, $nearby_attractions]);
                $_SESSION['admin_success'] = "Destination '$name' created successfully!";
            } catch (\PDOException $e) {
                $_SESSION['admin_error'] = "Database error: " . $e->getMessage();
            }
        }
        header("Location: destinations.php");
        exit;
    }

    // Edit Destination
    if ($action === 'edit') {
        $id = intval($_POST['id']);
        $name = trim($_POST['name']);
        $country = trim($_POST['country']);
        $continent = trim($_POST['continent']);
        $region_type = isset($_POST['region_type']) && in_array($_POST['region_type'], ['National', 'International']) 
            ? $_POST['region_type'] 
            : ($country === 'India' ? 'National' : 'International');
        $category_id = isset($_POST['category_id']) ? intval($_POST['category_id']) : 0;
        $description = trim($_POST['description']);
        $best_time_to_visit = trim($_POST['best_time_to_visit']);
        $price_range = isset($_POST['price_range']) ? trim($_POST['price_range']) : 'Mid-Range';
        $latitude = !empty($_POST['latitude']) ? floatval($_POST['latitude']) : null;
        $longitude = !empty($_POST['longitude']) ? floatval($_POST['longitude']) : null;
        $nearby_attractions = trim($_POST['nearby_attractions'] ?? '');

        // Check current image in DB
        $stmt = $pdo->prepare("SELECT image FROM destinations WHERE id = ?");
        $stmt->execute([$id]);
        $current_image = $stmt->fetchColumn();
        $image_name = $current_image ? $current_image : 'default_destination.jpg';

        // Handle Image Upload
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $file_tmp = $_FILES['image']['tmp_name'];
            $file_name = $_FILES['image']['name'];
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            
            $allowed_exts = ['jpg', 'jpeg', 'png', 'webp'];
            if (in_array($file_ext, $allowed_exts)) {
                $upload_dir = '../uploads/';
                if (!file_exists($upload_dir)) {
                    @mkdir($upload_dir, 0777, true);
                }
                $new_image_name = time() . '_' . preg_replace("/[^a-zA-Z0-9.]/", "_", $file_name);
                
                if (move_uploaded_file($file_tmp, $upload_dir . $new_image_name)) {
                    $image_name = $new_image_name;
                }
            } else {
                $_SESSION['admin_error'] = "Invalid file type. Old image retained.";
            }
        }

        if ($id <= 0 || empty($name) || empty($country) || empty($continent) || empty($description)) {
            $_SESSION['admin_error'] = "Please fill in all required fields.";
        } else {
            $catVal = $category_id > 0 ? $category_id : null;
            try {
                $stmt = $pdo->prepare("UPDATE destinations SET name = ?, country = ?, continent = ?, region_type = ?, category_id = ?, description = ?, best_time_to_visit = ?, image = ?, price_range = ?, latitude = ?, longitude = ?, nearby_attractions = ? WHERE id = ?");
                $stmt->execute([$name, $country, $continent, $region_type, $catVal, $description, $best_time_to_visit, $image_name, $price_range, $latitude, $longitude, $nearby_attractions, $id]);
                $_SESSION['admin_success'] = "Destination '$name' updated successfully.";
            } catch (\PDOException $e) {
                $_SESSION['admin_error'] = "Database error updating destination: " . $e->getMessage();
            }
        }
        header("Location: destinations.php");
        exit;
    }

    // Delete Destination
    if ($action === 'delete') {
        $id = intval($_POST['id']);
        try {
            $stmt = $pdo->prepare("DELETE FROM destinations WHERE id = ?");
            $stmt->execute([$id]);
            $_SESSION['admin_success'] = "Destination deleted successfully.";
        } catch (\PDOException $e) {
            $_SESSION['admin_error'] = "Error deleting destination: " . $e->getMessage();
        }
        header("Location: destinations.php");
        exit;
    }
}

// 2. Fetch Header & Template (HTML Output starts here)
require_once 'includes/admin_header.php';

// 3. Fetch Categories for select lists
try {
    $categories = $pdo->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();
} catch (\PDOException $e) {
    die("Database error: " . $e->getMessage());
}

// 4. GET Query Edit Data
$edit_mode = false;
$edit_dest = ['id' => 0, 'name' => '', 'country' => '', 'continent' => '', 'region_type' => 'National', 'category_id' => 0, 'description' => '', 'best_time_to_visit' => '', 'price_range' => 'Mid-Range', 'latitude' => '', 'longitude' => '', 'nearby_attractions' => ''];
if (isset($_GET['edit'])) {
    $edit_id = intval($_GET['edit']);
    if ($edit_id > 0) {
        $stmt = $pdo->prepare("SELECT * FROM destinations WHERE id = ?");
        $stmt->execute([$edit_id]);
        $res = $stmt->fetch();
        if ($res) {
            $edit_mode = true;
            $edit_dest = $res;
        }
    }
}

// 5. Fetch all Destinations
try {
    $destinations = $pdo->query("SELECT d.*, c.name AS category_name FROM destinations d 
                                 LEFT JOIN categories c ON d.category_id = c.id 
                                 ORDER BY d.id DESC")->fetchAll();
} catch (\PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>

<div class="row g-4">
    <!-- CRUD Form Box -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm rounded-3 bg-white p-4">
            <h5 class="fw-bold mb-4 border-bottom pb-2">
                <?php echo $edit_mode ? '<i class="bi-pencil-square text-warning me-2"></i>Edit Destination' : '<i class="bi-plus-circle text-primary me-2"></i>Add Destination'; ?>
            </h5>
            
            <form action="destinations.php" method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
                <input type="hidden" name="action" value="<?php echo $edit_mode ? 'edit' : 'add'; ?>">
                <?php if ($edit_mode): ?>
                    <input type="hidden" name="id" value="<?php echo $edit_dest['id']; ?>">
                <?php endif; ?>

                <!-- Destination Name -->
                <div class="mb-3">
                    <label for="name" class="form-label small fw-bold text-muted">Destination Name *</label>
                    <input type="text" name="name" id="name" class="form-control form-control-sm" placeholder="e.g. Paris" value="<?php echo htmlspecialchars($edit_dest['name']); ?>" required>
                    <div class="invalid-feedback">Enter destination name.</div>
                </div>

                <!-- Country & Continent -->
                <div class="row mb-3 g-2">
                    <div class="col-6">
                        <label for="country" class="form-label small fw-bold text-muted">Country *</label>
                        <input type="text" name="country" id="country" class="form-control form-control-sm" placeholder="e.g. France" value="<?php echo htmlspecialchars($edit_dest['country']); ?>" required>
                        <div class="invalid-feedback">Enter country.</div>
                    </div>
                    <div class="col-6">
                        <label for="continent" class="form-label small fw-bold text-muted">Continent *</label>
                        <select name="continent" id="continent" class="form-select form-select-sm" required>
                            <option value="">Select...</option>
                            <option value="Asia" <?php echo $edit_dest['continent'] === 'Asia' ? 'selected' : ''; ?>>Asia</option>
                            <option value="Europe" <?php echo $edit_dest['continent'] === 'Europe' ? 'selected' : ''; ?>>Europe</option>
                            <option value="North America" <?php echo $edit_dest['continent'] === 'North America' ? 'selected' : ''; ?>>North America</option>
                            <option value="South America" <?php echo $edit_dest['continent'] === 'South America' ? 'selected' : ''; ?>>South America</option>
                            <option value="Africa" <?php echo $edit_dest['continent'] === 'Africa' ? 'selected' : ''; ?>>Africa</option>
                            <option value="Oceania" <?php echo $edit_dest['continent'] === 'Oceania' ? 'selected' : ''; ?>>Oceania</option>
                        </select>
                        <div class="invalid-feedback">Select continent.</div>
                    </div>
                </div>

                <!-- Region Type & Category -->
                <div class="row mb-3 g-2">
                    <div class="col-6">
                        <label for="region_type" class="form-label small fw-bold text-muted">Region Type</label>
                        <select name="region_type" id="region_type" class="form-select form-select-sm">
                            <option value="National" <?php echo $edit_dest['region_type'] === 'National' ? 'selected' : ''; ?>>National</option>
                            <option value="International" <?php echo $edit_dest['region_type'] === 'International' ? 'selected' : ''; ?>>International</option>
                        </select>
                    </div>
                    <div class="col-6">
                        <label for="category_id" class="form-label small fw-bold text-muted">Category</label>
                        <select name="category_id" id="category_id" class="form-select form-select-sm">
                            <option value="0">General / None</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo $cat['id']; ?>" <?php echo $edit_dest['category_id'] == $cat['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cat['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <!-- Budget Tier & Best Time -->
                <div class="row mb-3 g-2">
                    <div class="col-6">
                        <label for="price_range" class="form-label small fw-bold text-muted">Budget Tier</label>
                        <select name="price_range" id="price_range" class="form-select form-select-sm">
                            <option value="Budget" <?php echo $edit_dest['price_range'] === 'Budget' ? 'selected' : ''; ?>>Budget</option>
                            <option value="Mid-Range" <?php echo $edit_dest['price_range'] === 'Mid-Range' ? 'selected' : ''; ?>>Mid-Range</option>
                            <option value="Luxury" <?php echo $edit_dest['price_range'] === 'Luxury' ? 'selected' : ''; ?>>Luxury</option>
                        </select>
                    </div>
                    <div class="col-6">
                        <label for="best_time_to_visit" class="form-label small fw-bold text-muted">Best Time to Visit</label>
                        <input type="text" name="best_time_to_visit" id="best_time_to_visit" class="form-control form-control-sm" placeholder="e.g. April to October" value="<?php echo htmlspecialchars($edit_dest['best_time_to_visit']); ?>">
                    </div>
                </div>

                <!-- Latitude & Longitude -->
                <div class="row mb-3 g-2">
                    <div class="col-6">
                        <label for="latitude" class="form-label small fw-bold text-muted">Latitude</label>
                        <input type="number" step="any" name="latitude" id="latitude" class="form-control form-control-sm" placeholder="e.g. 48.8566" value="<?php echo htmlspecialchars($edit_dest['latitude'] ?? ''); ?>">
                    </div>
                    <div class="col-6">
                        <label for="longitude" class="form-label small fw-bold text-muted">Longitude</label>
                        <input type="number" step="any" name="longitude" id="longitude" class="form-control form-control-sm" placeholder="e.g. 2.3522" value="<?php echo htmlspecialchars($edit_dest['longitude'] ?? ''); ?>">
                    </div>
                </div>

                <!-- Attractions list -->
                <div class="mb-3">
                    <label for="nearby_attractions" class="form-label small fw-bold text-muted">Attractions (Comma separated)</label>
                    <input type="text" name="nearby_attractions" id="nearby_attractions" class="form-control form-control-sm" placeholder="Eiffel Tower, Louvre Museum..." value="<?php echo htmlspecialchars($edit_dest['nearby_attractions'] ?? ''); ?>">
                </div>

                <!-- Description -->
                <div class="mb-3">
                    <label for="description" class="form-label small fw-bold text-muted">Description *</label>
                    <textarea name="description" id="description" rows="4" class="form-control form-control-sm" required><?php echo htmlspecialchars($edit_dest['description']); ?></textarea>
                    <div class="invalid-feedback">Enter description content.</div>
                </div>

                <!-- Image upload -->
                <div class="mb-4">
                    <label for="image" class="form-label small fw-bold text-muted">Upload Image File</label>
                    <input type="file" name="image" id="image" class="form-control form-control-sm" accept="image/png, image/jpeg, image/jpg, image/webp">
                </div>

                <!-- Action Button controls -->
                <div class="d-flex gap-2 pt-2">
                    <button type="submit" class="btn <?php echo $edit_mode ? 'btn-warning text-dark' : 'btn-primary'; ?> w-100 rounded-pill fw-bold">
                        <?php echo $edit_mode ? 'Update Destination' : 'Save Destination'; ?>
                    </button>
                    <?php if ($edit_mode): ?>
                        <a href="destinations.php" class="btn btn-outline-secondary w-100 rounded-pill fw-bold">Cancel</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <!-- Listings Table Grid -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm rounded-3 bg-white p-4">
            <h5 class="fw-bold mb-4 border-bottom pb-2"><i class="bi-geo-alt-fill text-primary me-2"></i>Destination Inventory</h5>
            
            <div class="table-responsive">
                <table class="table table-hover align-middle small">
                    <thead>
                        <tr>
                            <th>Image</th>
                            <th>Destination Details</th>
                            <th>Category</th>
                            <th>Region</th>
                            <th>Budget</th>
                            <th class="text-end" style="width: 150px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($destinations)): ?>
                            <?php foreach ($destinations as $d): ?>
                                <tr>
                                    <td>
                                        <img src="<?php echo htmlspecialchars(get_image_url($d['image'])); ?>" class="rounded" style="width: 60px; height: 40px; object-fit: cover;" alt="">
                                    </td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($d['name']); ?></strong><br>
                                        <span class="text-muted small"><?php echo htmlspecialchars($d['country']); ?>, <?php echo htmlspecialchars($d['continent']); ?></span>
                                    </td>
                                    <td><?php echo htmlspecialchars($d['category_name'] ?? 'General'); ?></td>
                                    <td>
                                        <span class="badge <?php echo $d['region_type'] === 'National' ? 'bg-primary-subtle text-primary' : 'bg-info-subtle text-info'; ?>">
                                            <?php echo htmlspecialchars($d['region_type'] ?? 'National'); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="fw-bold text-danger"><?php echo htmlspecialchars($d['price_range']); ?></span>
                                    </td>
                                    <td class="text-end">
                                        <div class="d-flex justify-content-end gap-2">
                                            <!-- Edit toggle -->
                                            <a href="destinations.php?edit=<?php echo $d['id']; ?>" class="btn btn-sm btn-outline-primary" title="Edit">
                                                <i class="bi-pencil"></i>
                                            </a>
                                            <!-- Delete hook -->
                                            <form action="destinations.php" method="POST" onsubmit="return confirm('Delete this destination?');" class="d-inline">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="id" value="<?php echo $d['id']; ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                                    <i class="bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">No destinations available.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/admin_footer.php'; ?>
