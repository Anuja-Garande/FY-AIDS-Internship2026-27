<?php
// C:\xampp\htdocs\NewProject\admin\offers.php
// Admin CRUD Management for Offers & Promo Coupon Codes

require_once '../config/db_connect.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Handle Actions POST (Add, Edit, Delete) BEFORE HTML output
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = isset($_POST['action']) ? trim($_POST['action']) : '';

    // ADD OFFER / COUPON
    if ($action === 'add_offer' || $action === 'add_coupon') {
        $title = trim($_POST['title']);
        $description = trim($_POST['description']);
        $discount_type = trim($_POST['discount_type']);
        $discount_value = floatval($_POST['discount_value']);
        $valid_from = trim($_POST['valid_from']);
        $valid_to = trim($_POST['valid_to']);
        $applicable_to = trim($_POST['applicable_to']);
        $code = strtoupper(trim($_POST['code']));

        if (empty($code) || empty($title) || $discount_value <= 0 || empty($valid_from) || empty($valid_to)) {
            $_SESSION['admin_error'] = "Please fill in all required fields for the coupon offer.";
        } else {
            try {
                // Check code uniqueness
                $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM offers WHERE code = ?");
                $checkStmt->execute([$code]);
                if ($checkStmt->fetchColumn() > 0) {
                    $_SESSION['admin_error'] = "Coupon code '$code' already exists. Please use a unique coupon code.";
                } else {
                    $stmt = $pdo->prepare("INSERT INTO offers (title, description, discount_type, discount_value, valid_from, valid_to, applicable_to, code) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$title, $description, $discount_type, $discount_value, $valid_from, $valid_to, $applicable_to, $code]);
                    $_SESSION['admin_success'] = "Promo Coupon '$code' created successfully!";
                }
            } catch (\PDOException $e) {
                $_SESSION['admin_error'] = "Failed to create coupon: " . $e->getMessage();
            }
        }
        $redirectPage = (basename($_SERVER['PHP_SELF']) === 'coupons.php') ? 'coupons.php' : 'offers.php';
        header("Location: $redirectPage");
        exit;
    }

    // EDIT OFFER / COUPON
    if ($action === 'edit_offer' || $action === 'edit_coupon') {
        $id = intval($_POST['id']);
        $title = trim($_POST['title']);
        $description = trim($_POST['description']);
        $discount_type = trim($_POST['discount_type']);
        $discount_value = floatval($_POST['discount_value']);
        $valid_from = trim($_POST['valid_from']);
        $valid_to = trim($_POST['valid_to']);
        $applicable_to = trim($_POST['applicable_to']);
        $code = strtoupper(trim($_POST['code']));

        if ($id <= 0 || empty($code) || empty($title) || $discount_value <= 0 || empty($valid_from) || empty($valid_to)) {
            $_SESSION['admin_error'] = "Please fill in all required fields for the coupon offer.";
        } else {
            try {
                // Check code uniqueness excluding current ID
                $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM offers WHERE code = ? AND id != ?");
                $checkStmt->execute([$code, $id]);
                if ($checkStmt->fetchColumn() > 0) {
                    $_SESSION['admin_error'] = "Coupon code '$code' is already used by another offer.";
                } else {
                    $stmt = $pdo->prepare("UPDATE offers SET title = ?, description = ?, discount_type = ?, discount_value = ?, valid_from = ?, valid_to = ?, applicable_to = ?, code = ? WHERE id = ?");
                    $stmt->execute([$title, $description, $discount_type, $discount_value, $valid_from, $valid_to, $applicable_to, $code, $id]);
                    $_SESSION['admin_success'] = "Promo Coupon '$code' updated successfully!";
                }
            } catch (\PDOException $e) {
                $_SESSION['admin_error'] = "Failed to update coupon: " . $e->getMessage();
            }
        }
        $redirectPage = (basename($_SERVER['PHP_SELF']) === 'coupons.php') ? 'coupons.php' : 'offers.php';
        header("Location: $redirectPage");
        exit;
    }

    // DELETE OFFER / COUPON
    if ($action === 'delete_offer' || $action === 'delete_coupon') {
        $id = intval($_POST['id']);
        try {
            $stmt = $pdo->prepare("DELETE FROM offers WHERE id = ?");
            $stmt->execute([$id]);
            $_SESSION['admin_success'] = "Promo Offer deleted successfully.";
        } catch (\PDOException $e) {
            $_SESSION['admin_error'] = "Failed to delete offer: " . $e->getMessage();
        }
        $redirectPage = (basename($_SERVER['PHP_SELF']) === 'coupons.php') ? 'coupons.php' : 'offers.php';
        header("Location: $redirectPage");
        exit;
    }
}

// 2. Render Header & Page HTML
require_once 'includes/admin_header.php';

// 3. Fetch Offers
$offers = $pdo->query("SELECT * FROM offers ORDER BY id DESC")->fetchAll();
?>

<div class="card border-0 shadow-sm rounded-3 bg-white p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0 text-dark"><i class="bi-percent text-danger me-2"></i>Offers & Promo Coupon Codes</h4>
        <button class="btn btn-primary btn-sm rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#addOfferModal">
            <i class="bi-plus-lg me-1"></i>Create New Coupon
        </button>
    </div>

    <?php if (!empty($offers)): ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle small">
                <thead class="table-light">
                    <tr>
                        <th>Code</th>
                        <th>Title</th>
                        <th>Discount</th>
                        <th>Applicable To</th>
                        <th>Valid Dates</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($offers as $offer): ?>
                        <tr>
                            <td><strong class="font-monospace text-primary fs-6"><?php echo htmlspecialchars($offer['code']); ?></strong></td>
                            <td>
                                <strong><?php echo htmlspecialchars($offer['title']); ?></strong>
                                <span class="d-block text-muted small"><?php echo htmlspecialchars($offer['description']); ?></span>
                            </td>
                            <td>
                                <span class="badge bg-danger">
                                    <?php echo $offer['discount_type'] === 'percent' ? intval($offer['discount_value']) . '% OFF' : '₹' . number_format($offer['discount_value']) . ' OFF'; ?>
                                </span>
                            </td>
                            <td><span class="badge bg-secondary text-uppercase"><?php echo htmlspecialchars($offer['applicable_to']); ?></span></td>
                            <td><?php echo date('M d', strtotime($offer['valid_from'])); ?> to <?php echo date('M d, Y', strtotime($offer['valid_to'])); ?></td>
                            <td class="text-end">
                                <div class="d-flex justify-content-end gap-2">
                                    <!-- Edit Button -->
                                    <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editOfferModal<?php echo $offer['id']; ?>">
                                        <i class="bi-pencil"></i> Edit
                                    </button>
                                    
                                    <!-- Delete Form -->
                                    <form action="<?php echo htmlspecialchars(basename($_SERVER['PHP_SELF'])); ?>" method="POST" class="d-inline" onsubmit="return confirm('Delete offer coupon?');">
                                        <input type="hidden" name="action" value="delete_offer">
                                        <input type="hidden" name="id" value="<?php echo $offer['id']; ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi-trash"></i> Delete</button>
                                    </form>
                                </div>

                                <!-- Edit Offer Modal -->
                                <div class="modal fade text-start" id="editOfferModal<?php echo $offer['id']; ?>" tabindex="-1">
                                    <div class="modal-dialog modal-lg">
                                        <div class="modal-content">
                                            <form action="<?php echo htmlspecialchars(basename($_SERVER['PHP_SELF'])); ?>" method="POST">
                                                <input type="hidden" name="action" value="edit_offer">
                                                <input type="hidden" name="id" value="<?php echo $offer['id']; ?>">
                                                <div class="modal-header">
                                                    <h5 class="modal-title fw-bold">Edit Promo Coupon Code</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body row g-3">
                                                    <div class="col-md-6">
                                                        <label class="form-label small fw-bold">Coupon Code (Uppercase)</label>
                                                        <input type="text" name="code" class="form-control text-uppercase font-monospace" required value="<?php echo htmlspecialchars($offer['code']); ?>">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label small fw-bold">Title</label>
                                                        <input type="text" name="title" class="form-control" required value="<?php echo htmlspecialchars($offer['title']); ?>">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label small fw-bold">Discount Type</label>
                                                        <select name="discount_type" class="form-select">
                                                            <option value="percent" <?php echo $offer['discount_type'] === 'percent' ? 'selected' : ''; ?>>Percentage (%)</option>
                                                            <option value="flat" <?php echo $offer['discount_type'] === 'flat' ? 'selected' : ''; ?>>Flat Amount (₹)</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label small fw-bold">Discount Value</label>
                                                        <input type="number" step="0.01" name="discount_value" class="form-control" required value="<?php echo htmlspecialchars($offer['discount_value']); ?>">
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label class="form-label small fw-bold">Applicable To</label>
                                                        <select name="applicable_to" class="form-select">
                                                            <option value="all" <?php echo $offer['applicable_to'] === 'all' ? 'selected' : ''; ?>>All Bookings</option>
                                                            <option value="package" <?php echo $offer['applicable_to'] === 'package' ? 'selected' : ''; ?>>Packages Only</option>
                                                            <option value="hotel" <?php echo $offer['applicable_to'] === 'hotel' ? 'selected' : ''; ?>>Hotels Only</option>
                                                            <option value="guide" <?php echo $offer['applicable_to'] === 'guide' ? 'selected' : ''; ?>>Guides Only</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label class="form-label small fw-bold">Valid From</label>
                                                        <input type="date" name="valid_from" class="form-control" required value="<?php echo htmlspecialchars($offer['valid_from']); ?>">
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label class="form-label small fw-bold">Valid To</label>
                                                        <input type="date" name="valid_to" class="form-control" required value="<?php echo htmlspecialchars($offer['valid_to']); ?>">
                                                    </div>
                                                    <div class="col-12">
                                                        <label class="form-label small fw-bold">Description</label>
                                                        <textarea name="description" rows="3" class="form-control" required><?php echo htmlspecialchars($offer['description']); ?></textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="submit" class="btn btn-warning text-dark rounded-pill px-4 fw-bold">Update Coupon</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <p class="text-muted text-center py-4 mb-0">No active promo offers found.</p>
    <?php endif; ?>
</div>

<!-- Add Offer Modal -->
<div class="modal fade" id="addOfferModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="<?php echo htmlspecialchars(basename($_SERVER['PHP_SELF'])); ?>" method="POST">
                <input type="hidden" name="action" value="add_offer">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Create Promo Offer Code</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body row g-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Coupon Code (Uppercase)</label>
                        <input type="text" name="code" class="form-control text-uppercase font-monospace" required placeholder="SUMMER2026">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Title</label>
                        <input type="text" name="title" class="form-control" required placeholder="Summer Special Discount">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Discount Type</label>
                        <select name="discount_type" class="form-select">
                            <option value="percent">Percentage (%)</option>
                            <option value="flat">Flat Amount (₹)</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Discount Value</label>
                        <input type="number" step="0.01" name="discount_value" class="form-control" required placeholder="15">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Applicable To</label>
                        <select name="applicable_to" class="form-select">
                            <option value="all">All Bookings</option>
                            <option value="package">Packages Only</option>
                            <option value="hotel">Hotels Only</option>
                            <option value="guide">Guides Only</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Valid From</label>
                        <input type="date" name="valid_from" class="form-control" required value="<?php echo date('Y-m-d'); ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Valid To</label>
                        <input type="date" name="valid_to" class="form-control" required value="<?php echo date('Y-m-d', strtotime('+30 days')); ?>">
                    </div>
                    <div class="col-12">
                        <label class="form-label small fw-bold">Description</label>
                        <textarea name="description" rows="3" class="form-control" required placeholder="Offer description details..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary rounded-pill px-4">Create Offer</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once 'includes/admin_footer.php'; ?>
