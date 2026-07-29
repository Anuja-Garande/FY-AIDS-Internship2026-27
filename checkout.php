<?php
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'includes/functions.php';
require_once 'includes/functions_product.php';

if (!isLoggedIn()) {
    $_SESSION['redirect_after_login'] = 'checkout.php';
    redirect('login.php');
}

$page_title = 'Checkout';
$db = Database::getInstance();
$user = getUser();

$cart_items = $db->fetchAll(
    "SELECT c.id as cart_id, c.product_id, c.quantity, p.name, p.slug, p.price, p.discount_price, p.quantity as stock, p.image_url,
            REPLACE((SELECT image FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1), 'products/', '') as image
     FROM cart c JOIN products p ON c.product_id = p.id WHERE c.user_id = ? AND p.status = 1",
    [$user['id']]
);

if (empty($cart_items)) {
    redirect('cart.php');
}

$subtotal = 0;
foreach ($cart_items as &$item) {
    $price = $item['discount_price'] ?? $item['price'];
    $item['effective_price'] = $price;
    $item['line_total'] = $price * $item['quantity'];
    $subtotal += $item['line_total'];
}
unset($item);

$shipping = $subtotal >= FREE_SHIPPING_MIN ? 0 : SHIPPING_CHARGES;
$tax = 0;
$coupon_discount = $_SESSION['coupon']['discount'] ?? 0;
$total = $subtotal + $shipping + $tax - $coupon_discount;

$addresses = $db->fetchAll("SELECT * FROM addresses WHERE user_id = ? ORDER BY is_default DESC, created_at DESC", [$user['id']]);

$step = (int)($_GET['step'] ?? $_POST['step'] ?? 1);
$step = max(1, min(3, $step));

$shipping_id = $_POST['shipping_address_id'] ?? ($addresses[0]['id'] ?? null);
$payment_method = $_POST['payment_method'] ?? 'cod';

if (isset($_POST['place_order'])) {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        flash('error', 'Invalid security token. Please try again.');
        redirect('checkout.php');
    }

    $addr_id = (int)($_POST['shipping_address_id'] ?? 0);
    $payment = $_POST['payment_method'] ?? 'cod';

    if (!$addr_id) {
        flash('error', 'Please select a shipping address.');
        redirect('checkout.php?step=1');
    }

    $address = $db->fetch("SELECT * FROM addresses WHERE id = ? AND user_id = ?", [$addr_id, $user['id']]);
    if (!$address) {
        flash('error', 'Invalid address selected.');
        redirect('checkout.php?step=1');
    }

    $cart = $db->fetchAll(
        "SELECT c.id as cart_id, c.product_id, c.quantity, p.name, p.price, p.discount_price, p.quantity as stock
         FROM cart c JOIN products p ON c.product_id = p.id WHERE c.user_id = ? AND p.status = 1",
        [$user['id']]
    );

    if (empty($cart)) {
        flash('error', 'Your cart is empty.');
        redirect('cart.php');
    }

    foreach ($cart as $ci) {
        if ($ci['quantity'] > $ci['stock']) {
            flash('error', $ci['name'] . ' has insufficient stock.');
            redirect('cart.php');
        }
    }

    $db->beginTransaction();
    try {
        $order_total = $subtotal + $shipping + $tax - $coupon_discount;

        $order_id = $db->insert(
            "INSERT INTO orders (user_id, order_number, subtotal, discount_amount, coupon_code, shipping_charge, total_amount, payment_method, payment_status, order_status, shipping_name, shipping_email, shipping_phone, shipping_address, shipping_city, shipping_state, shipping_pincode, notes, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending', 'pending', ?, ?, ?, ?, ?, ?, ?, ?, NOW())",
            [
                $user['id'],
                generateOrderNumber(),
                $subtotal,
                $coupon_discount,
                $_SESSION['coupon']['code'] ?? null,
                $shipping,
                $order_total,
                $payment,
                $address['name'],
                $user['email'],
                $address['phone'],
                $address['address_line1'] . ($address['address_line2'] ? ', ' . $address['address_line2'] : ''),
                $address['city'],
                $address['state'],
                $address['pincode'],
                $_POST['order_notes'] ?? ''
            ]
        );

        if (!$order_id) throw new Exception('Failed to create order.');

        foreach ($cart as $ci) {
            $price = $ci['discount_price'] ?? $ci['price'];
            $db->insert(
                "INSERT INTO order_items (order_id, product_id, product_name, product_price, quantity, total, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())",
                [$order_id, $ci['product_id'], $ci['name'], $price, $ci['quantity'], $ci['quantity'] * $price]
            );
        }

        $db->delete("DELETE FROM cart WHERE user_id = ?", [$user['id']]);
        unset($_SESSION['coupon']);

        $db->commit();

        setNotification($user['id'], 'Order Placed', "Your order has been placed successfully!", 'success');

        redirect('order_confirmation.php?order=' . $order_id);
    } catch (Exception $e) {
        $db->rollback();
        error_log("Order creation failed: " . $e->getMessage());
        flash('error', 'Failed to place order. Please try again.');
        redirect('checkout.php');
    }
}

$selected_addr = null;
if ($shipping_id) {
    foreach ($addresses as $a) {
        if ($a['id'] == $shipping_id) { $selected_addr = $a; break; }
    }
}
if (!$selected_addr && !empty($addresses)) {
    $selected_addr = $addresses[0];
}
?>
<?php include 'includes/header.php'; ?>

<style>
.step-indicator { display: flex; justify-content: center; margin-bottom: 32px; }
.step { display: flex; flex-direction: column; align-items: center; position: relative; flex: 1; }
.step .step-circle { width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 600; background: #e9ecef; color: #6c757d; transition: all 0.3s; z-index: 1; }
.step.active .step-circle, .step.completed .step-circle { background: linear-gradient(135deg, #667eea, #764ba2); color: white; }
.step .step-label { margin-top: 8px; font-size: 0.85rem; color: #6c757d; }
.step.active .step-label { color: #667eea; font-weight: 600; }
.step::after { content: ''; position: absolute; top: 20px; left: 50%; width: 100%; height: 2px; background: #e9ecef; z-index: 0; }
.step:last-child::after { display: none; }
.step.completed::after { background: #667eea; }
.address-card { border: 2px solid #e9ecef; border-radius: 12px; padding: 16px; cursor: pointer; transition: all 0.3s; position: relative; }
.address-card:hover, .address-card.selected { border-color: #667eea; background: #f0f4ff; }
.payment-option { border: 2px solid #e9ecef; border-radius: 12px; padding: 16px; cursor: pointer; transition: all 0.3s; }
.payment-option:hover, .payment-option.selected { border-color: #667eea; background: #f0f4ff; }
.order-item { display: flex; align-items: center; gap: 12px; padding: 10px 0; border-bottom: 1px solid #f0f0f0; }
.order-item:last-child { border-bottom: none; }
.order-item img { width: 60px; height: 60px; object-fit: cover; border-radius: 8px; }
.summary-row { display: flex; justify-content: space-between; padding: 8px 0; }
.summary-row.total { font-size: 1.2rem; font-weight: 700; border-top: 2px solid #dee2e6; padding-top: 12px; margin-top: 4px; }
</style>

<div class="container py-4">
    <h2 class="fw-bold mb-4"><i class="fas fa-lock me-2"></i>Checkout</h2>

    <div class="step-indicator">
        <div class="step <?= $step >= 1 ? ($step > 1 ? 'completed active' : 'active') : '' ?>">
            <div class="step-circle"><?= $step > 1 ? '<i class="fas fa-check"></i>' : '1' ?></div>
            <span class="step-label">Address</span>
        </div>
        <div class="step <?= $step >= 2 ? ($step > 2 ? 'completed active' : 'active') : '' ?>">
            <div class="step-circle"><?= $step > 2 ? '<i class="fas fa-check"></i>' : '2' ?></div>
            <span class="step-label">Payment</span>
        </div>
        <div class="step <?= $step >= 3 ? 'active' : '' ?>">
            <div class="step-circle">3</div>
            <span class="step-label">Review</span>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <form method="POST" id="checkoutForm">
                <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">

                <?php if ($step == 1): ?>
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body p-4">
                        <h5 class="fw-bold mb-3"><i class="fas fa-map-marker-alt me-2"></i>Shipping Address</h5>

                        <?php if (!empty($addresses)): ?>
                        <div class="row g-3 mb-4">
                            <?php foreach ($addresses as $addr): ?>
                            <div class="col-md-6">
                                <div class="address-card <?= ($selected_addr && $selected_addr['id'] == $addr['id']) ? 'selected' : '' ?>" onclick="selectAddress(this, <?= $addr['id'] ?>)">
                                    <input type="radio" name="shipping_address_id" value="<?= $addr['id'] ?>" <?= ($selected_addr && $selected_addr['id'] == $addr['id']) ? 'checked' : '' ?> class="d-none">
                                    <strong><?= sanitize($addr['name']) ?></strong>
                                    <?php if (!empty($addr['is_default'])): ?>
                                        <span class="badge bg-primary ms-1">Default</span>
                                    <?php endif; ?>
                                    <p class="mb-0 small text-muted mt-1"><?= sanitize($addr['address_line1'] . ', ' . $addr['city'] . ', ' . $addr['state'] . ' - ' . $addr['pincode']) ?></p>
                                    <small class="text-muted"><?= sanitize($addr['phone']) ?></small>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php else: ?>
                        <div class="text-center py-4 mb-3">
                            <i class="fas fa-map-marker-alt fa-3x text-muted mb-3"></i>
                            <h6 class="text-muted">No addresses saved yet</h6>
                            <p class="text-muted small">Add a shipping address to continue.</p>
                        </div>
                        <?php endif; ?>

                        <button type="button" class="btn btn-outline-primary mb-3" data-bs-toggle="modal" data-bs-target="#addAddressModal"><i class="fas fa-plus me-1"></i>Add New Address</button>

                        <button type="submit" class="btn btn-primary btn-lg"><i class="fas fa-arrow-right me-2"></i>Continue to Payment</button>
                    </div>
                </div>
                <input type="hidden" name="step" value="2">

                <?php elseif ($step == 2): ?>
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body p-4">
                        <h5 class="fw-bold mb-3"><i class="fas fa-credit-card me-2"></i>Payment Method</h5>

                        <div class="payment-option selected mb-3" onclick="selectPayment(this, 'cod')">
                            <input type="radio" name="payment_method" value="cod" checked class="d-none">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-money-bill-wave fa-2x text-success me-3"></i>
                                <div>
                                    <strong>Cash on Delivery</strong>
                                    <p class="mb-0 small text-muted">Pay when your order is delivered</p>
                                </div>
                            </div>
                        </div>

                        <div class="payment-option mb-3" onclick="selectPayment(this, 'razorpay')">
                            <input type="radio" name="payment_method" value="razorpay" class="d-none">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-credit-card fa-2x text-primary me-3"></i>
                                <div>
                                    <strong>UPI / Online Payment</strong>
                                    <p class="mb-0 small text-muted">Pay securely via Razorpay (UPI, Cards, NetBanking)</p>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex gap-3 mt-4">
                            <a href="checkout.php?step=1" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-2"></i>Back</a>
                            <button type="submit" class="btn btn-primary btn-lg">Review Order <i class="fas fa-arrow-right ms-2"></i></button>
                        </div>
                    </div>
                </div>
                <input type="hidden" name="step" value="3">
                <input type="hidden" name="shipping_address_id" value="<?= $shipping_id ?>">

                <?php elseif ($step == 3): ?>
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body p-4">
                        <h5 class="fw-bold mb-3"><i class="fas fa-clipboard-check me-2"></i>Review Your Order</h5>

                        <h6 class="fw-semibold mt-3">Shipping Address</h6>
                        <?php if ($selected_addr): ?>
                        <div class="bg-light p-3 rounded mb-3">
                            <strong><?= sanitize($selected_addr['name']) ?></strong><br>
                            <?= sanitize($selected_addr['address_line1']) ?><br>
                            <?= sanitize($selected_addr['city'] . ', ' . $selected_addr['state'] . ' - ' . $selected_addr['pincode']) ?><br>
                            <small class="text-muted"><?= sanitize($selected_addr['phone']) ?></small>
                        </div>
                        <?php endif; ?>

                        <h6 class="fw-semibold">Payment Method</h6>
                        <div class="bg-light p-3 rounded mb-3">
                            <i class="fas fa-money-bill-wave me-2 text-success"></i> Cash on Delivery
                        </div>

                        <h6 class="fw-semibold">Order Items</h6>
                        <?php foreach ($cart_items as $item): ?>
                        <div class="order-item">
                            <?php $co_img = !empty($item['image']) ? BASE_URL . 'assets/uploads/products/' . $item['image'] : ($item['image_url'] ?: BASE_URL . 'assets/uploads/products/default.png'); ?>
                            <img src="<?= htmlspecialchars($co_img) ?>" alt="">
                            <div class="flex-grow-1">
                                <strong><?= sanitize($item['name']) ?></strong>
                                <small class="d-block text-muted">Qty: <?= $item['quantity'] ?> &times; <?= formatPrice($item['effective_price']) ?></small>
                            </div>
                            <strong><?= formatPrice($item['line_total']) ?></strong>
                        </div>
                        <?php endforeach; ?>

                        <div class="mt-3">
                            <label class="form-label fw-semibold">Order Notes (optional)</label>
                            <textarea class="form-control" name="order_notes" rows="2" placeholder="Any special instructions for delivery..."></textarea>
                        </div>

                        <div class="d-flex gap-3 mt-4">
                            <a href="checkout.php?step=2" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-2"></i>Back</a>
                            <button type="submit" name="place_order" class="btn btn-success btn-lg flex-grow-1"><i class="fas fa-check me-2"></i>Place Order - <?= formatPrice($total) ?></button>
                        </div>
                    </div>
                </div>
                <input type="hidden" name="shipping_address_id" value="<?= $shipping_id ?>">
                <input type="hidden" name="payment_method" value="<?= $payment_method ?>">
                <?php endif; ?>
            </form>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm" style="position:sticky;top:80px;">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-3">Order Summary</h5>
                    <?php foreach ($cart_items as $item): ?>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div class="d-flex align-items-center" style="flex:1;min-width:0;">
                            <?php $co_img2 = !empty($item['image']) ? BASE_URL . 'assets/uploads/products/' . $item['image'] : ($item['image_url'] ?: BASE_URL . 'assets/uploads/products/default.png'); ?>
                            <img src="<?= htmlspecialchars($co_img2) ?>" class="rounded me-2" style="width:40px;height:40px;object-fit:cover;flex-shrink:0;" alt="">
                            <div style="min-width:0;">
                                <small class="d-block text-truncate" style="max-width:180px;"><?= sanitize($item['name']) ?></small>
                                <small class="text-muted">x<?= $item['quantity'] ?></small>
                            </div>
                        </div>
                        <strong class="ms-2" style="white-space:nowrap;"><?= formatPrice($item['line_total']) ?></strong>
                    </div>
                    <?php endforeach; ?>
                    <hr>
                    <div class="summary-row"><span>Subtotal</span><span><?= formatPrice($subtotal) ?></span></div>
                    <?php if ($coupon_discount > 0): ?>
                        <div class="summary-row text-success"><span>Coupon Discount</span><span>-<?= formatPrice($coupon_discount) ?></span></div>
                    <?php endif; ?>
                    <div class="summary-row"><span>Shipping</span><span><?= $shipping == 0 ? '<span class="text-success">FREE</span>' : formatPrice($shipping) ?></span></div>
                    <div class="summary-row total"><span>Total</span><span class="text-primary"><?= formatPrice($total) ?></span></div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function selectAddress(el, id) {
    document.querySelectorAll('.address-card').forEach(c => c.classList.remove('selected'));
    el.classList.add('selected');
    el.querySelector('input[type=radio]').checked = true;
}
function selectPayment(el, method) {
    document.querySelectorAll('.payment-option').forEach(c => c.classList.remove('selected'));
    el.classList.add('selected');
    el.querySelector('input[type=radio]').checked = true;
}

function handleCheckoutSubmit(e) {
    var paymentMethod = document.querySelector('input[name="payment_method"]:checked') ||
                       document.querySelector('input[name="payment_method"][type="hidden"]');
    if (!paymentMethod || paymentMethod.value !== 'razorpay') return true;

    e.preventDefault();
    var btn = document.querySelector('button[name="place_order"]');
    if (btn) { btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...'; }

    var addressId = document.querySelector('input[name="shipping_address_id"]');
    if (!addressId || !addressId.value) {
        alert('Please select a shipping address.');
        if (btn) { btn.disabled = false; btn.innerHTML = '<i class="fas fa-check me-2"></i>Place Order'; }
        return false;
    }

    fetch('ajax/payment.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'action=create_order&address_id=' + addressId.value
    }).then(r => r.json()).then(data => {
        if (!data.success) {
            alert(data.message || 'Failed to initiate payment.');
            if (btn) { btn.disabled = false; btn.innerHTML = '<i class="fas fa-check me-2"></i>Place Order'; }
            return;
        }

        var options = {
            key: data.key,
            amount: data.amount,
            currency: data.currency,
            name: data.name,
            description: data.description,
            order_id: data.razorpay_order_id,
            prefill: data.prefill,
            theme: { color: '#667eea' },
            handler: function(response) {
                fetch('ajax/payment.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    body: 'action=verify_payment&razorpay_order_id=' + response.razorpay_order_id +
                          '&razorpay_payment_id=' + response.razorpay_payment_id +
                          '&razorpay_signature=' + response.razorpay_signature +
                          '&address_id=' + addressId.value
                }).then(r => r.json()).then(verifyData => {
                    if (verifyData.success) {
                        window.location.href = verifyData.redirect || 'order_confirmation.php';
                    } else {
                        alert(verifyData.message || 'Payment verification failed. Please contact support.');
                        if (btn) { btn.disabled = false; btn.innerHTML = '<i class="fas fa-check me-2"></i>Place Order'; }
                    }
                }).catch(function() {
                    alert('Network error. Please contact support with Payment ID: ' + response.razorpay_payment_id);
                    if (btn) { btn.disabled = false; btn.innerHTML = '<i class="fas fa-check me-2"></i>Place Order'; }
                });
            },
            modal: {
                ondismiss: function() {
                    if (btn) { btn.disabled = false; btn.innerHTML = '<i class="fas fa-check me-2"></i>Place Order'; }
                }
            }
        };

        var rzp = new Razorpay(options);
        rzp.open();
    }).catch(function() {
        alert('Network error. Please try again.');
        if (btn) { btn.disabled = false; btn.innerHTML = '<i class="fas fa-check me-2"></i>Place Order'; }
    });

    return false;
}

function saveNewAddress() {
    const form = document.getElementById('newAddressForm');
    const formData = new FormData(form);
    formData.append('save_address', '1');
    formData.append('csrf_token', '<?= generateCSRFToken() ?>');

    fetch('ajax/save_address.php', {
        method: 'POST',
        body: formData
    }).then(r => r.json()).then(data => {
        if (data.success) {
            location.reload();
        } else {
            if (typeof Swal !== 'undefined') Swal.fire({icon:'error',title:'Error',text:data.message});
            else alert(data.message);
        }
    }).catch(() => {
        form.submit();
    });
}

document.getElementById('checkoutForm').addEventListener('submit', handleCheckoutSubmit);
</script>

<div class="modal fade" id="addAddressModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content" style="border-radius:16px;">
            <div class="modal-header">
                <h5 class="modal-title fw-bold"><i class="fas fa-map-marker-alt me-2"></i>Add New Address</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="newAddressForm" method="POST" action="ajax/save_address.php">
                <input type="hidden" name="save_address" value="1">
                <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6"><label class="form-label">Full Name *</label><input type="text" class="form-control" name="name" required></div>
                        <div class="col-md-6"><label class="form-label">Phone *</label><input type="tel" class="form-control" name="phone" required></div>
                        <div class="col-12"><label class="form-label">Address Line 1 *</label><input type="text" class="form-control" name="address_line1" required></div>
                        <div class="col-12"><label class="form-label">Address Line 2</label><input type="text" class="form-control" name="address_line2"></div>
                        <div class="col-md-4"><label class="form-label">City *</label><input type="text" class="form-control" name="city" required></div>
                        <div class="col-md-4"><label class="form-label">State *</label><input type="text" class="form-control" name="state" required></div>
                        <div class="col-md-4"><label class="form-label">Pincode *</label><input type="text" class="form-control" name="pincode" required></div>
                        <div class="col-12"><div class="form-check"><input type="checkbox" class="form-check-input" name="is_default" id="new_addr_default"><label class="form-check-label" for="new_addr_default">Set as default address</label></div></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="saveNewAddress()"><i class="fas fa-save me-1"></i>Save Address</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
