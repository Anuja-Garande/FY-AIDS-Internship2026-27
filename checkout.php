<?php
// C:\xampp\htdocs\NewProject\checkout.php
// Unified Booking Checkout & Coupon Code Processing

require_once 'config/db_connect.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Session Guard: Must be logged in to book
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "Please log in to complete your booking.";
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$type    = isset($_GET['type']) ? trim($_GET['type']) : (isset($_POST['type']) ? trim($_POST['type']) : '');
$id      = isset($_GET['id']) ? intval($_GET['id']) : (isset($_POST['id']) ? intval($_POST['id']) : 0);

if (!in_array($type, ['package', 'hotel', 'guide']) || $id <= 0) {
    $_SESSION['error'] = "Invalid booking selection.";
    header("Location: index.php");
    exit;
}

// 1. Fetch Selected Item Details
$itemTitle = '';
$itemImage = '';
$basePrice = 0.00;

try {
    if ($type === 'package') {
        $stmt = $pdo->prepare("SELECT p.*, d.name AS dest_name FROM packages p LEFT JOIN destinations d ON p.destination_id = d.id WHERE p.id = ?");
        $stmt->execute([$id]);
        $item = $stmt->fetch();
        if ($item) {
            $itemTitle = $item['name'];
            $itemImage = $item['image'];
            $basePrice = floatval($item['price']);
        }
    } elseif ($type === 'hotel') {
        $stmt = $pdo->prepare("SELECT h.*, d.name AS dest_name FROM hotels h LEFT JOIN destinations d ON h.destination_id = d.id WHERE h.id = ?");
        $stmt->execute([$id]);
        $item = $stmt->fetch();
        if ($item) {
            $itemTitle = $item['name'];
            $itemImage = $item['image'];
            $basePrice = floatval($item['price_per_night']);
        }
    } elseif ($type === 'guide') {
        $stmt = $pdo->prepare("SELECT g.*, d.name AS dest_name FROM guides g LEFT JOIN destinations d ON g.destination_id = d.id WHERE g.id = ?");
        $stmt->execute([$id]);
        $item = $stmt->fetch();
        if ($item) {
            $itemTitle = "Local Guide: " . $item['name'];
            $itemImage = $item['photo'];
            $basePrice = floatval($item['price_per_day']);
        }
    }

    if (empty($item)) {
        $_SESSION['error'] = "Selected item was not found.";
        header("Location: index.php");
        exit;
    }
} catch (\PDOException $e) {
    die("Database fetch error: " . $e->getMessage());
}

// 2. Default Form Inputs
$check_in  = isset($_REQUEST['check_in']) ? trim($_REQUEST['check_in']) : date('Y-m-d');
$check_out = isset($_REQUEST['check_out']) ? trim($_REQUEST['check_out']) : date('Y-m-d', strtotime('+2 days'));
$guests    = isset($_REQUEST['guests']) ? max(1, intval($_REQUEST['guests'])) : 1;
$rooms     = isset($_REQUEST['rooms']) ? max(1, intval($_REQUEST['rooms'])) : 1;
$offer_code = isset($_REQUEST['offer_code']) ? trim($_REQUEST['offer_code']) : '';

// Calculate duration in days
$date1 = new DateTime($check_in);
$date2 = new DateTime($check_out);
$num_days = max(1, $date1->diff($date2)->days);

// Calculate Gross Price
$grossPrice = 0.00;
if ($type === 'package') {
    $grossPrice = $basePrice * $guests;
} elseif ($type === 'hotel') {
    $grossPrice = $basePrice * $rooms * $num_days;
} elseif ($type === 'guide') {
    $grossPrice = $basePrice * $num_days;
}

// 3. Process Coupon Offer Application
$discountAmount = 0.00;
$appliedOffer = null;
$offerError = '';

if (!empty($offer_code)) {
    try {
        $today = date('Y-m-d');
        $oStmt = $pdo->prepare("SELECT * FROM offers WHERE code = ? AND valid_to >= ?");
        $oStmt->execute([$offer_code, $today]);
        $appliedOffer = $oStmt->fetch();

        if ($appliedOffer) {
            if ($appliedOffer['applicable_to'] === 'all' || $appliedOffer['applicable_to'] === $type) {
                if ($appliedOffer['discount_type'] === 'percent') {
                    $discountAmount = ($grossPrice * floatval($appliedOffer['discount_value'])) / 100;
                } else {
                    $discountAmount = floatval($appliedOffer['discount_value']);
                }
                $discountAmount = min($discountAmount, $grossPrice);
            } else {
                $offerError = "Promo code '" . htmlspecialchars($offer_code) . "' is not applicable for " . $type . " bookings.";
                $appliedOffer = null;
            }
        } else {
            $offerError = "Invalid or expired promo code.";
        }
    } catch (\PDOException $e) {
        $offerError = "Error validating offer code.";
    }
}

$totalPrice = max(0.00, $grossPrice - $discountAmount);

// 4. Handle Order Submission POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'confirm_booking') {
    try {
        $booking_number = 'BK-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -5));
        $offer_id = $appliedOffer ? $appliedOffer['id'] : null;

        $ins = $pdo->prepare("INSERT INTO bookings (booking_number, user_id, booking_type, reference_id, check_in, check_out, guests, rooms, total_price, offer_id, status) 
                              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'confirmed')");
        $ins->execute([
            $booking_number,
            $user_id,
            $type,
            $id,
            $check_in,
            $check_out,
            $guests,
            $rooms,
            $totalPrice,
            $offer_id
        ]);

        // Send In-App Notification
        $notifMsg = "Your " . ucfirst($type) . " booking (#" . $booking_number . ") for " . $itemTitle . " has been confirmed!";
        create_notification($pdo, $user_id, $notifMsg, 'booking');

        $_SESSION['success'] = "Booking placed successfully!";
        header("Location: booking-confirmation.php?booking_number=" . urlencode($booking_number));
        exit;

    } catch (\PDOException $e) {
        $_SESSION['error'] = "Booking creation failed: " . $e->getMessage();
    }
}

require_once 'includes/header.php';
?>

<div class="container py-4">
    <h2 class="fw-extrabold text-dark mb-4"><i class="bi-credit-card-2-front text-primary me-2"></i>Review & Checkout</h2>

    <div class="row g-4">
        <!-- Left Side: Order Form & Items -->
        <div class="col-lg-8">
            <!-- Selected Item Card -->
            <div class="card border-0 shadow-sm rounded-4 bg-white p-4 mb-4">
                <h5 class="fw-bold text-dark border-bottom pb-2 mb-3">Item Details</h5>
                <div class="d-flex gap-3 align-items-center">
                    <img src="<?php echo htmlspecialchars(get_image_url($itemImage)); ?>" class="rounded-3" style="width: 100px; height: 90px; object-fit: cover;" alt="<?php echo htmlspecialchars($itemTitle); ?>" onerror="this.onerror=null; this.src='assets/images/default_destination.jpg';">
                    <div>
                        <span class="badge bg-primary rounded-pill mb-1"><?php echo strtoupper($type); ?></span>
                        <h4 class="fw-bold text-dark mb-1"><?php echo htmlspecialchars($itemTitle); ?></h4>
                        <div class="text-muted small"><i class="bi-geo-alt-fill text-danger me-1"></i><?php echo htmlspecialchars($item['dest_name'] ?? 'Global'); ?></div>
                    </div>
                </div>
            </div>

            <!-- Booking Dates & Guests Form -->
            <div class="card border-0 shadow-sm rounded-4 bg-white p-4 mb-4">
                <h5 class="fw-bold text-dark border-bottom pb-2 mb-3">Travel Dates & Guest Details</h5>
                
                <form action="checkout.php" method="GET" class="row g-3">
                    <input type="hidden" name="type" value="<?php echo htmlspecialchars($type); ?>">
                    <input type="hidden" name="id" value="<?php echo $id; ?>">

                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-muted">Check-In / Travel Date</label>
                        <input type="date" name="check_in" class="form-control" value="<?php echo htmlspecialchars($check_in); ?>" min="<?php echo date('Y-m-d'); ?>" onchange="this.form.submit()">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-muted">Check-Out Date</label>
                        <input type="date" name="check_out" class="form-control" value="<?php echo htmlspecialchars($check_out); ?>" min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>" onchange="this.form.submit()">
                    </div>

                    <?php if ($type === 'hotel'): ?>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted">Number of Rooms</label>
                            <input type="number" name="rooms" class="form-control" min="1" max="10" value="<?php echo $rooms; ?>" onchange="this.form.submit()">
                        </div>
                    <?php endif; ?>

                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-muted">Number of Guests</label>
                        <input type="number" name="guests" class="form-control" min="1" max="20" value="<?php echo $guests; ?>" onchange="this.form.submit()">
                    </div>
                </form>
            </div>

            <!-- Promo Offer Coupon Code Section -->
            <div class="card border-0 shadow-sm rounded-4 bg-white p-4">
                <h5 class="fw-bold text-dark border-bottom pb-2 mb-3"><i class="bi-percent text-danger me-2"></i>Apply Discount Coupon</h5>
                
                <form action="checkout.php" method="GET" class="d-flex gap-2 mb-2">
                    <input type="hidden" name="type" value="<?php echo htmlspecialchars($type); ?>">
                    <input type="hidden" name="id" value="<?php echo $id; ?>">
                    <input type="hidden" name="check_in" value="<?php echo htmlspecialchars($check_in); ?>">
                    <input type="hidden" name="check_out" value="<?php echo htmlspecialchars($check_out); ?>">
                    <input type="hidden" name="guests" value="<?php echo $guests; ?>">
                    <input type="hidden" name="rooms" value="<?php echo $rooms; ?>">

                    <input type="text" name="offer_code" class="form-control text-uppercase font-monospace" placeholder="Enter Code (e.g. WELCOME1500)" value="<?php echo htmlspecialchars($offer_code); ?>">
                    <button type="submit" class="btn btn-outline-primary fw-bold text-nowrap">Apply</button>
                </form>

                <?php if ($appliedOffer): ?>
                    <div class="alert alert-success small mb-0 py-2">
                        <i class="bi-check-circle-fill me-1"></i>Coupon '<strong><?php echo htmlspecialchars($appliedOffer['code']); ?></strong>' applied! Saved ₹<?php echo number_format($discountAmount, 2); ?>.
                    </div>
                <?php elseif ($offerError !== ''): ?>
                    <div class="alert alert-danger small mb-0 py-2">
                        <i class="bi-exclamation-circle-fill me-1"></i><?php echo $offerError; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Right Side Price Summary & Confirmation Form -->
        <div class="col-lg-4">
            <div class="sticky-planner-card">
                <h4 class="fw-bold text-dark mb-3">Price Summary</h4>

                <div class="d-flex justify-content-between mb-2 text-muted small">
                    <span>Base Fare (<?php echo $num_days; ?> day<?php echo $num_days > 1 ? 's' : ''; ?>)</span>
                    <strong class="text-dark">₹<?php echo number_format($grossPrice, 2); ?></strong>
                </div>

                <?php if ($discountAmount > 0): ?>
                    <div class="d-flex justify-content-between mb-2 text-success small">
                        <span>Discount Applied</span>
                        <strong>- ₹<?php echo number_format($discountAmount, 2); ?></strong>
                    </div>
                <?php endif; ?>

                <div class="border-top pt-3 mt-3 d-flex justify-content-between align-items-center">
                    <span class="fw-bold text-dark fs-5">Total Payable</span>
                    <span class="fs-2 fw-extrabold text-success">₹<?php echo number_format($totalPrice, 2); ?></span>
                </div>

                <form action="checkout.php" method="POST" class="mt-4">
                    <input type="hidden" name="action" value="confirm_booking">
                    <input type="hidden" name="type" value="<?php echo htmlspecialchars($type); ?>">
                    <input type="hidden" name="id" value="<?php echo $id; ?>">
                    <input type="hidden" name="check_in" value="<?php echo htmlspecialchars($check_in); ?>">
                    <input type="hidden" name="check_out" value="<?php echo htmlspecialchars($check_out); ?>">
                    <input type="hidden" name="guests" value="<?php echo $guests; ?>">
                    <input type="hidden" name="rooms" value="<?php echo $rooms; ?>">
                    <input type="hidden" name="offer_code" value="<?php echo htmlspecialchars($offer_code); ?>">

                    <button type="submit" class="btn btn-warning w-100 py-3 rounded-pill text-dark fw-extrabold shadow-sm fs-5">
                        Confirm & Place Booking <i class="bi-check-circle-fill ms-1"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
