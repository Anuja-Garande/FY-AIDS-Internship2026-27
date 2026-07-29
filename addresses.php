<?php
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'includes/functions.php';

if (!isLoggedIn()) { redirect('login.php'); }

$page_title = 'My Addresses';
$db = Database::getInstance();
$user = getUser();

$addresses = $db->fetchAll("SELECT * FROM addresses WHERE user_id = :uid ORDER BY is_default DESC, created_at DESC", [':uid' => $user['id']]);

if (isset($_POST['save_address'])) {
    if (verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $name = trim($_POST['name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $address_line1 = trim($_POST['address_line1'] ?? '');
        $address_line2 = trim($_POST['address_line2'] ?? '');
        $city = trim($_POST['city'] ?? '');
        $state = trim($_POST['state'] ?? '');
        $pincode = trim($_POST['pincode'] ?? '');
        $is_default = isset($_POST['is_default']) ? 1 : 0;
        $edit_id = (int)($_POST['address_id'] ?? 0);

        if (empty($name) || empty($phone) || empty($address_line1) || empty($city) || empty($state) || empty($pincode)) {
            flash('error', 'Please fill all required fields.');
        } elseif (count($addresses) >= 5 && !$edit_id) {
            flash('error', 'Maximum 5 addresses allowed.');
        } else {
            if ($is_default) {
                $db->update("UPDATE addresses SET is_default = 0 WHERE user_id = :uid", [':uid' => $user['id']]);
            }
            if (empty($addresses) && !$is_default) {
                $is_default = 1;
            }

            $data = [':name' => $name, ':phone' => $phone, ':address_line1' => $address_line1, ':address_line2' => $address_line2, ':city' => $city, ':state' => $state, ':pincode' => $pincode, ':is_default' => $is_default, ':uid' => $user['id']];

            if ($edit_id) {
                $db->update("UPDATE addresses SET name=:name, phone=:phone, address_line1=:address_line1, address_line2=:address_line2, city=:city, state=:state, pincode=:pincode, is_default=:is_default WHERE id=:eid AND user_id=:uid", array_merge($data, [':eid' => $edit_id]));
            } else {
                $db->insert("INSERT INTO addresses (user_id, name, phone, address_line1, address_line2, city, state, pincode, is_default, created_at) VALUES (:uid, :name, :phone, :address_line1, :address_line2, :city, :state, :pincode, :is_default, NOW())", $data);
            }
            flash('success', 'Address saved successfully!');
            redirect('addresses.php');
        }
    }
}

if (isset($_POST['delete_address'])) {
    if (verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $del_id = (int)$_POST['address_id'];
        $db->delete("DELETE FROM addresses WHERE id = :id AND user_id = :uid", [':id' => $del_id, ':uid' => $user['id']]);
        flash('success', 'Address deleted.');
        redirect('addresses.php');
    }
}

if (isset($_POST['set_default'])) {
    if (verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $def_id = (int)$_POST['address_id'];
        $db->update("UPDATE addresses SET is_default = 0 WHERE user_id = :uid", [':uid' => $user['id']]);
        $db->update("UPDATE addresses SET is_default = 1 WHERE id = :id AND user_id = :uid", [':id' => $def_id, ':uid' => $user['id']]);
        flash('success', 'Default address updated.');
        redirect('addresses.php');
    }
}

$edit_address = null;
if (isset($_GET['edit'])) {
    $edit_id = (int)$_GET['edit'];
    foreach ($addresses as $a) { if ($a['id'] == $edit_id) { $edit_address = $a; break; } }
}
?>
<?php include 'includes/header.php'; ?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold"><i class="fas fa-map-marker-alt me-2"></i>My Addresses</h2>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addressModal" onclick="resetForm()"><i class="fas fa-plus me-1"></i>Add Address</button>
    </div>

    <?php if (count($addresses) >= 5): ?>
        <div class="alert alert-info"><i class="fas fa-info-circle me-1"></i>You have reached the maximum limit of 5 addresses.</div>
    <?php endif; ?>

    <?php if (empty($addresses)): ?>
    <div class="text-center py-5">
        <i class="fas fa-map-marker-alt fa-4x text-muted mb-3"></i>
        <h4>No saved addresses</h4>
        <p class="text-muted">Add an address for faster checkout.</p>
    </div>
    <?php else: ?>
    <div class="row g-4">
        <?php foreach ($addresses as $addr): ?>
        <div class="col-md-6 col-lg-4">
            <div class="card border-0 shadow-sm h-100 <?= $addr['is_default'] ? 'border-primary' : '' ?>" style="<?= $addr['is_default'] ? 'border:2px solid #667eea !important;' : '' ?>">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <strong><?= htmlspecialchars($addr['name']) ?></strong>
                        <?php if ($addr['is_default']): ?>
                            <span class="badge bg-primary">Default</span>
                        <?php endif; ?>
                    </div>
                    <p class="text-muted small mb-2">
                        <?= htmlspecialchars($addr['address_line1']) ?>
                        <?= !empty($addr['address_line2']) ? '<br>' . htmlspecialchars($addr['address_line2']) : '' ?><br>
                        <?= htmlspecialchars($addr['city'] . ', ' . $addr['state'] . ' - ' . $addr['pincode']) ?><br>
                        <i class="fas fa-phone me-1"></i><?= htmlspecialchars($addr['phone']) ?>
                    </p>
                    <div class="d-flex gap-2">
                        <button class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addressModal" onclick="editAddress(<?= htmlspecialchars(json_encode($addr)) ?>)"><i class="fas fa-edit me-1"></i>Edit</button>
                        <?php if (!$addr['is_default']): ?>
                        <form method="POST" class="d-inline">
                            <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                            <input type="hidden" name="address_id" value="<?= $addr['id'] ?>">
                            <button type="submit" name="set_default" class="btn btn-outline-secondary btn-sm">Set Default</button>
                        </form>
                        <?php endif; ?>
                        <form method="POST" class="d-inline" onsubmit="return confirm('Delete this address?')">
                            <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                            <input type="hidden" name="address_id" value="<?= $addr['id'] ?>">
                            <button type="submit" name="delete_address" class="btn btn-outline-danger btn-sm"><i class="fas fa-trash"></i></button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <div class="modal fade" id="addressModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content" style="border-radius:16px;">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="addressModalLabel"><i class="fas fa-map-marker-alt me-2"></i>Add Address</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                        <input type="hidden" name="address_id" id="address_id" value="">
                        <div class="row g-3">
                            <div class="col-md-6"><label class="form-label">Full Name *</label><input type="text" class="form-control" name="name" id="addr_name" required></div>
                            <div class="col-md-6"><label class="form-label">Phone *</label><input type="tel" class="form-control" name="phone" id="addr_phone" required></div>
                            <div class="col-12"><label class="form-label">Address Line 1 *</label><input type="text" class="form-control" name="address_line1" id="addr_line1" required></div>
                            <div class="col-12"><label class="form-label">Address Line 2</label><input type="text" class="form-control" name="address_line2" id="addr_line2"></div>
                            <div class="col-md-4"><label class="form-label">City *</label><input type="text" class="form-control" name="city" id="addr_city" required></div>
                            <div class="col-md-4"><label class="form-label">State *</label><input type="text" class="form-control" name="state" id="addr_state" required></div>
                            <div class="col-md-4"><label class="form-label">Pincode *</label><input type="text" class="form-control" name="pincode" id="addr_pincode" required></div>
                            <div class="col-12"><div class="form-check"><input type="checkbox" class="form-check-input" name="is_default" id="addr_default"><label class="form-check-label" for="addr_default">Set as default address</label></div></div>
                        </div>
                    </div>
                    <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" name="save_address" class="btn btn-primary"><i class="fas fa-save me-1"></i>Save Address</button></div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function resetForm() {
    document.getElementById('addressModalLabel').innerHTML = '<i class="fas fa-map-marker-alt me-2"></i>Add Address';
    document.getElementById('address_id').value = '';
    document.getElementById('addr_name').value = '';
    document.getElementById('addr_phone').value = '';
    document.getElementById('addr_line1').value = '';
    document.getElementById('addr_line2').value = '';
    document.getElementById('addr_city').value = '';
    document.getElementById('addr_state').value = '';
    document.getElementById('addr_pincode').value = '';
    document.getElementById('addr_default').checked = false;
}

function editAddress(addr) {
    document.getElementById('addressModalLabel').innerHTML = '<i class="fas fa-edit me-2"></i>Edit Address';
    document.getElementById('address_id').value = addr.id;
    document.getElementById('addr_name').value = addr.name;
    document.getElementById('addr_phone').value = addr.phone;
    document.getElementById('addr_line1').value = addr.address_line1;
    document.getElementById('addr_line2').value = addr.address_line2 || '';
    document.getElementById('addr_city').value = addr.city;
    document.getElementById('addr_state').value = addr.state;
    document.getElementById('addr_pincode').value = addr.pincode;
    document.getElementById('addr_default').checked = addr.is_default == 1;
}
</script>

<?php include 'includes/footer.php'; ?>
