<?php
session_start();
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

if (!isAdminLoggedIn()) {
    redirect('login.php');
}

$db = Database::getInstance();

if (isset($_POST['add_coupon'])) {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        flash('error', 'Invalid request.');
    } else {
        $code = strtoupper(trim($_POST['code'] ?? ''));
        $type = $_POST['type'] ?? 'percentage';
        $value = floatval($_POST['value'] ?? 0);
        $min_order = floatval($_POST['min_order_amount'] ?? 0);
        $max_discount = floatval($_POST['max_discount'] ?? 0);
        $usage_limit = intval($_POST['usage_limit'] ?? 0);
        $expiry_date = $_POST['expiry_date'] ?? null;
        $status = intval($_POST['status'] ?? 1);

        if (empty($code) || $value <= 0) {
            flash('error', 'Code and value are required.');
        } else {
            $existing = $db->query("SELECT id FROM coupons WHERE code=?", [$code])->fetch();
            if ($existing) {
                flash('error', 'Coupon code already exists.');
            } else {
                $db->insert("INSERT INTO coupons (code, type, value, min_order, max_discount, usage_limit, expiry_date, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())", [
                    $code, $type, $value, $min_order, $max_discount, $usage_limit, $expiry_date, $status
                ]);
                flash('success', 'Coupon created successfully!');
            }
        }
    }
    unset($_SESSION['csrf_token']);
    redirect('coupons.php');
}

if (isset($_POST['edit_coupon'])) {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        flash('error', 'Invalid request.');
    } else {
        $id = intval($_POST['edit_id'] ?? 0);
        $code = strtoupper(trim($_POST['code'] ?? ''));
        $type = $_POST['type'] ?? 'percentage';
        $value = floatval($_POST['value'] ?? 0);
        $min_order = floatval($_POST['min_order_amount'] ?? 0);
        $max_discount = floatval($_POST['max_discount'] ?? 0);
        $usage_limit = intval($_POST['usage_limit'] ?? 0);
        $expiry_date = $_POST['expiry_date'] ?? null;
        $status = intval($_POST['status'] ?? 1);

        if (empty($code) || $value <= 0) {
            flash('error', 'Code and value are required.');
        } else {
            $db->update("UPDATE coupons SET code=?, type=?, value=?, min_order=?, max_discount=?, usage_limit=?, expiry_date=?, status=? WHERE id=?", [
                $code, $type, $value, $min_order, $max_discount, $usage_limit, $expiry_date, $status, $id
            ]);
            flash('success', 'Coupon updated successfully!');
        }
    }
    unset($_SESSION['csrf_token']);
    redirect('coupons.php');
}

if (isset($_POST['delete_coupon'])) {
    $id = intval($_POST['id'] ?? 0);
    $db->query("DELETE FROM coupons WHERE id=?", [$id]);
    flash('success', 'Coupon deleted.');
    redirect('coupons.php');
}

$page = max(1, intval($_GET['page'] ?? 1));
$per_page = 15;
$offset = ($page - 1) * $per_page;

$total = $db->query("SELECT COUNT(*) as count FROM coupons")->fetch()['count'];
$total_pages = max(1, ceil($total / $per_page));

$coupons = $db->query("SELECT c.*, (SELECT COUNT(*) FROM orders WHERE coupon_code=c.code) as times_used FROM coupons c ORDER BY c.id DESC LIMIT $per_page OFFSET $offset")->fetchAll();

$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
$pageTitle = 'Coupons';
include __DIR__ . '/includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="fw-bold mb-0">Coupons (<?= number_format($total) ?>)</h5>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCouponModal"><i class="fas fa-plus me-2"></i>Add Coupon</button>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr><th>Code</th><th>Type</th><th>Value</th><th>Min Order</th><th>Max Discount</th><th>Used/Limit</th><th>Expiry</th><th>Status</th><th>Actions</th></tr>
                </thead>
                <tbody>
                    <?php if (empty($coupons)): ?>
                    <tr><td colspan="9" class="text-center text-muted py-4">No coupons found</td></tr>
                    <?php endif; ?>
                    <?php foreach ($coupons as $coupon): ?>
                    <?php $expired = !empty($coupon['expiry_date']) && strtotime($coupon['expiry_date']) < time(); ?>
                    <tr>
                        <td><code class="fw-bold fs-6"><?= sanitize($coupon['code']) ?></code></td>
                        <td><span class="badge bg-<?= $coupon['type'] === 'percentage' ? 'primary' : 'success' ?>"><?= ucfirst($coupon['type']) ?></span></td>
                        <td class="fw-bold"><?= $coupon['type'] === 'percentage' ? $coupon['value'] . '%' : formatPrice($coupon['value']) ?></td>
                        <td><?= formatPrice($coupon['min_order']) ?></td>
                        <td><?= $coupon['max_discount'] > 0 ? formatPrice($coupon['max_discount']) : '-' ?></td>
                        <td><?= $coupon['times_used'] ?>/<?= $coupon['usage_limit'] ?: '∞' ?></td>
                        <td class="<?= $expired ? 'text-danger' : '' ?>"><?= $coupon['expiry_date'] ? date('M d, Y', strtotime($coupon['expiry_date'])) : 'Never' ?></td>
                        <td><button class="btn btn-sm btn-<?= $coupon['status'] ? 'success' : 'danger' ?> btn-action" onclick="toggleStatus('coupons',<?= $coupon['id'] ?>,'status',this)"><?= $coupon['status'] ? 'Active' : 'Inactive' ?></button></td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary" onclick="editCoupon(<?= $coupon['id'] ?>,'<?= sanitize(addslashes($coupon['code'])) ?>','<?= $coupon['type'] ?>',<?= $coupon['value'] ?>,<?= $coupon['min_order'] ?>,<?= $coupon['max_discount'] ?? 0 ?>,<?= $coupon['usage_limit'] ?>,'<?= $coupon['expiry_date'] ?? '' ?>',<?= $coupon['status'] ?>)"><i class="fas fa-edit"></i></button>
                            <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this coupon?')">
                                <input type="hidden" name="delete_coupon" value="1"><input type="hidden" name="id" value="<?= $coupon['id'] ?>">
                                <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="d-flex justify-content-center p-3">
            <nav><ul class="pagination pagination-sm mb-0">
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <li class="page-item <?= $i === $page ? 'active' : '' ?>"><a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a></li>
                <?php endfor; ?>
            </ul></nav>
        </div>
    </div>
</div>

<div class="modal fade" id="addCouponModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?= sanitize($_SESSION['csrf_token']) ?>">
                <input type="hidden" name="add_coupon" value="1">
                <div class="modal-header"><h5 class="modal-title fw-bold">Add Coupon</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <div class="mb-3"><label class="form-label fw-semibold">Code *</label><input type="text" name="code" class="form-control" required placeholder="e.g., SAVE20" style="text-transform:uppercase;"></div>
                    <div class="row g-3 mb-3">
                        <div class="col-6"><label class="form-label fw-semibold">Type</label><select name="type" class="form-select"><option value="percentage">Percentage</option><option value="flat">Flat</option></select></div>
                        <div class="col-6"><label class="form-label fw-semibold">Value *</label><input type="number" name="value" class="form-control" step="0.01" min="0" required></div>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-6"><label class="form-label fw-semibold">Min Order Amount</label><input type="number" name="min_order_amount" class="form-control" step="0.01" min="0" value="0"></div>
                        <div class="col-6"><label class="form-label fw-semibold">Max Discount</label><input type="number" name="max_discount" class="form-control" step="0.01" min="0" value="0"></div>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-6"><label class="form-label fw-semibold">Usage Limit</label><input type="number" name="usage_limit" class="form-control" min="0" value="0" placeholder="0=unlimited"></div>
                        <div class="col-6"><label class="form-label fw-semibold">Expiry Date</label><input type="date" name="expiry_date" class="form-control"></div>
                    </div>
                    <div class="mb-3"><label class="form-label fw-semibold">Status</label><select name="status" class="form-select"><option value="1">Active</option><option value="0">Inactive</option></select></div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary">Save</button></div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="editCouponModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?= sanitize($_SESSION['csrf_token']) ?>">
                <input type="hidden" name="edit_coupon" value="1">
                <input type="hidden" name="edit_id" id="editCouponId">
                <div class="modal-header"><h5 class="modal-title fw-bold">Edit Coupon</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <div class="mb-3"><label class="form-label fw-semibold">Code *</label><input type="text" name="code" id="editCouponCode" class="form-control" required style="text-transform:uppercase;"></div>
                    <div class="row g-3 mb-3">
                        <div class="col-6"><label class="form-label fw-semibold">Type</label><select name="type" id="editCouponType" class="form-select"><option value="percentage">Percentage</option><option value="flat">Flat</option></select></div>
                        <div class="col-6"><label class="form-label fw-semibold">Value *</label><input type="number" name="value" id="editCouponValue" class="form-control" step="0.01" min="0" required></div>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-6"><label class="form-label fw-semibold">Min Order Amount</label><input type="number" name="min_order_amount" id="editCouponMin" class="form-control" step="0.01" min="0"></div>
                        <div class="col-6"><label class="form-label fw-semibold">Max Discount</label><input type="number" name="max_discount" id="editCouponMax" class="form-control" step="0.01" min="0"></div>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-6"><label class="form-label fw-semibold">Usage Limit</label><input type="number" name="usage_limit" id="editCouponLimit" class="form-control" min="0"></div>
                        <div class="col-6"><label class="form-label fw-semibold">Expiry Date</label><input type="date" name="expiry_date" id="editCouponExpiry" class="form-control"></div>
                    </div>
                    <div class="mb-3"><label class="form-label fw-semibold">Status</label><select name="status" id="editCouponStatus" class="form-select"><option value="1">Active</option><option value="0">Inactive</option></select></div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary">Update</button></div>
            </form>
        </div>
    </div>
</div>

<?php
$extraScripts = '<script>
function editCoupon(id, code, type, value, minOrder, maxDiscount, limit, expiry, status) {
    document.getElementById("editCouponId").value = id;
    document.getElementById("editCouponCode").value = code;
    document.getElementById("editCouponType").value = type;
    document.getElementById("editCouponValue").value = value;
    document.getElementById("editCouponMin").value = minOrder;
    document.getElementById("editCouponMax").value = maxDiscount;
    document.getElementById("editCouponLimit").value = limit;
    document.getElementById("editCouponExpiry").value = expiry;
    document.getElementById("editCouponStatus").value = status;
    new bootstrap.Modal(document.getElementById("editCouponModal")).show();
}
</script>';
include __DIR__ . '/includes/footer.php';
?>
