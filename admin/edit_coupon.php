<?php
session_start();
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

if (!isAdminLoggedIn()) {
    redirect('login.php');
}

$db = Database::getInstance();
$id = intval($_GET['id'] ?? 0);

if (!$id) {
    flash('error', 'Invalid coupon ID.');
    redirect('coupons.php');
}

$coupon = $db->query("SELECT * FROM coupons WHERE id=?", [$id])->fetch();
if (!$coupon) {
    flash('error', 'Coupon not found.');
    redirect('coupons.php');
}

if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $errors[] = 'Invalid request.';
    } else {
        $code = strtoupper(trim($_POST['code'] ?? ''));
        $type = $_POST['type'] ?? 'percentage';
        $value = floatval($_POST['value'] ?? 0);
        $min_order = floatval($_POST['min_order_amount'] ?? 0);
        $max_discount = floatval($_POST['max_discount'] ?? 0);
        $usage_limit = intval($_POST['usage_limit'] ?? 0);
        $expiry_date = !empty($_POST['expiry_date']) ? $_POST['expiry_date'] : null;
        $status = intval($_POST['status'] ?? 1);

        if (empty($code)) $errors[] = 'Coupon code is required.';
        if ($value <= 0) $errors[] = 'Value must be greater than 0.';

        if (empty($errors)) {
            $existing = $db->query("SELECT id FROM coupons WHERE code=? AND id!=?", [$code, $id])->fetch();
            if ($existing) {
                $errors[] = 'Coupon code already exists.';
            } else {
                $db->update("UPDATE coupons SET code=?, type=?, value=?, min_order=?, max_discount=?, usage_limit=?, expiry_date=?, status=? WHERE id=?", [
                    $code, $type, $value, $min_order, $max_discount, $usage_limit, $expiry_date, $status, $id
                ]);
                unset($_SESSION['csrf_token']);
                flash('success', 'Coupon updated successfully!');
                redirect('coupons.php');
            }
        }
    }
    $coupon = array_merge($coupon, $_POST);
}

$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
$pageTitle = 'Edit Coupon';
include __DIR__ . '/includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="fw-bold mb-0">Edit Coupon: <?= sanitize($coupon['code']) ?></h5>
    <a href="<?= BASE_URL ?>/admin/coupons.php" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Back</a>
</div>

<?php if (!empty($errors)): ?>
<div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errors as $e): ?><li><?= sanitize($e) ?></li><?php endforeach; ?></ul></div>
<?php endif; ?>

<div class="row">
    <div class="col-lg-8">
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?= sanitize($_SESSION['csrf_token']) ?>">
            <div class="card mb-4">
                <div class="card-header"><h6 class="fw-bold mb-0">Coupon Details</h6></div>
                <div class="card-body">
                    <div class="mb-3"><label class="form-label fw-semibold">Coupon Code <span class="text-danger">*</span></label><input type="text" name="code" class="form-control" required style="text-transform:uppercase;" value="<?= sanitize($coupon['code']) ?>"></div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6"><label class="form-label fw-semibold">Type</label><select name="type" class="form-select"><option value="percentage" <?= $coupon['type'] === 'percentage' ? 'selected' : '' ?>>Percentage (%)</option><option value="flat" <?= $coupon['type'] === 'flat' ? 'selected' : '' ?>>Flat Amount</option></select></div>
                        <div class="col-md-6"><label class="form-label fw-semibold">Value <span class="text-danger">*</span></label><input type="number" name="value" class="form-control" step="0.01" min="0" required value="<?= sanitize($coupon['value']) ?>"></div>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6"><label class="form-label fw-semibold">Minimum Order Amount</label><input type="number" name="min_order_amount" class="form-control" step="0.01" min="0" value="<?= sanitize($coupon['min_order']) ?>"></div>
                        <div class="col-md-6"><label class="form-label fw-semibold">Max Discount</label><input type="number" name="max_discount" class="form-control" step="0.01" min="0" value="<?= sanitize($coupon['max_discount']) ?>"></div>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6"><label class="form-label fw-semibold">Usage Limit</label><input type="number" name="usage_limit" class="form-control" min="0" value="<?= sanitize($coupon['usage_limit']) ?>"></div>
                        <div class="col-md-6"><label class="form-label fw-semibold">Expiry Date</label><input type="date" name="expiry_date" class="form-control" value="<?= sanitize($coupon['expiry_date'] ? date('Y-m-d', strtotime($coupon['expiry_date'])) : '') ?>"></div>
                    </div>
                    <div class="mb-3"><label class="form-label fw-semibold">Status</label><select name="status" class="form-select"><option value="1" <?= $coupon['status'] == 1 ? 'selected' : '' ?>>Active</option><option value="0" <?= $coupon['status'] == 0 ? 'selected' : '' ?>>Inactive</option></select></div>
                </div>
            </div>
            <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Update Coupon</button>
        </form>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
