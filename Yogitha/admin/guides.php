<?php
// C:\xampp\htdocs\NewProject\admin\guides.php
// Admin CRUD Management for Tour Guides

require_once '../config/db_connect.php';
require_once 'includes/admin_header.php';

$destinations = $pdo->query("SELECT id, name FROM destinations ORDER BY name ASC")->fetchAll();

// 1. Handle CRUD Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = isset($_POST['action']) ? trim($_POST['action']) : '';

    if ($action === 'add_guide') {
        $name = trim($_POST['name']);
        $destination_id = intval($_POST['destination_id']);
        $photo = trim($_POST['photo']);
        $languages = trim($_POST['languages']);
        $experience_years = intval($_POST['experience_years']);
        $contact_number = trim($_POST['contact_number']);
        $email = trim($_POST['email']);
        $rating = floatval($_POST['rating']);
        $price_per_day = floatval($_POST['price_per_day']);

        try {
            $stmt = $pdo->prepare("INSERT INTO guides (name, destination_id, photo, languages, experience_years, contact_number, email, rating, price_per_day) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$name, $destination_id, $photo, $languages, $experience_years, $contact_number, $email, $rating, $price_per_day]);
            $_SESSION['admin_success'] = "Tour Guide registered successfully.";
        } catch (\PDOException $e) {
            $_SESSION['admin_error'] = "Failed to add guide: " . $e->getMessage();
        }
        header("Location: guides.php");
        exit;
    }

    if ($action === 'delete_guide') {
        $id = intval($_POST['id']);
        try {
            $stmt = $pdo->prepare("DELETE FROM guides WHERE id = ?");
            $stmt->execute([$id]);
            $_SESSION['admin_success'] = "Tour Guide removed.";
        } catch (\PDOException $e) {
            $_SESSION['admin_error'] = "Failed to delete guide: " . $e->getMessage();
        }
        header("Location: guides.php");
        exit;
    }
}

// 2. Fetch Guides
$guides = $pdo->query("SELECT g.*, d.name AS dest_name FROM guides g LEFT JOIN destinations d ON g.destination_id = d.id ORDER BY g.id DESC")->fetchAll();
?>

<div class="card border-0 shadow-sm rounded-3 bg-white p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0 text-dark"><i class="bi-person-badge-fill text-warning me-2"></i>Tour Guides Directory</h4>
        <button class="btn btn-primary btn-sm rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#addGuideModal">
            <i class="bi-plus-lg me-1"></i>Register New Guide
        </button>
    </div>

    <?php if (!empty($guides)): ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle small">
                <thead class="table-light">
                    <tr>
                        <th>Photo</th>
                        <th>Guide Name</th>
                        <th>Destination</th>
                        <th>Languages</th>
                        <th>Exp.</th>
                        <th>Rating</th>
                        <th>Daily Rate</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($guides as $guide): ?>
                        <tr>
                            <td><img src="<?php echo htmlspecialchars(get_guide_photo($guide['photo'], $guide['id'], $guide['name'])); ?>" style="width: 45px; height: 45px; object-fit: cover;" class="rounded-circle"></td>
                            <td>
                                <strong><?php echo htmlspecialchars($guide['name']); ?></strong>
                                <span class="d-block text-muted small"><?php echo htmlspecialchars($guide['email']); ?></span>
                            </td>
                            <td><?php echo htmlspecialchars($guide['dest_name'] ?? 'General'); ?></td>
                            <td><?php echo htmlspecialchars($guide['languages']); ?></td>
                            <td><?php echo $guide['experience_years']; ?> Yrs</td>
                            <td><span class="badge bg-warning text-dark">⭐ <?php echo number_format($guide['rating'], 1); ?></span></td>
                            <td><strong class="text-success">₹<?php echo number_format($guide['price_per_day'], 2); ?></strong></td>
                            <td>
                                <form action="guides.php" method="POST" class="d-inline" onsubmit="return confirm('Delete guide?');">
                                    <input type="hidden" name="action" value="delete_guide">
                                    <input type="hidden" name="id" value="<?php echo $guide['id']; ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi-trash"></i> Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <p class="text-muted text-center py-4 mb-0">No tour guides registered yet.</p>
    <?php endif; ?>
</div>

<!-- Add Guide Modal -->
<div class="modal fade" id="addGuideModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="guides.php" method="POST">
                <input type="hidden" name="action" value="add_guide">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Register Tour Guide</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body row g-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Guide Name</label>
                        <input type="text" name="name" class="form-control" required placeholder="e.g. Vikram Sharma">
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
                        <label class="form-label small fw-bold">Languages Spoken</label>
                        <input type="text" name="languages" class="form-control" required placeholder="English, Hindi, French">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold">Experience (Years)</label>
                        <input type="number" name="experience_years" class="form-control" value="5" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold">Daily Fee (₹)</label>
                        <input type="number" step="0.01" name="price_per_day" class="form-control" value="1500" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Contact Number</label>
                        <input type="text" name="contact_number" class="form-control" required placeholder="+91 98765 11223">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Email Address</label>
                        <input type="email" name="email" class="form-control" required placeholder="guide@gmail.com">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Photo URL</label>
                        <input type="text" name="photo" class="form-control" required placeholder="https://images.unsplash.com/...">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Rating</label>
                        <input type="number" step="0.1" name="rating" class="form-control" value="4.8" min="1" max="5">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary rounded-pill px-4">Register Guide</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once 'includes/admin_footer.php'; ?>
