<?php
// C:\xampp\htdocs\NewProject\hotel-detail.php
// Hotel Detail & Room Booking Calculator Page

require_once 'config/db_connect.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$hotel_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($hotel_id <= 0) {
    header("Location: hotels.php");
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT h.*, d.name AS destination_name, d.country AS destination_country, d.id AS dest_id 
                           FROM hotels h 
                           LEFT JOIN destinations d ON h.destination_id = d.id 
                           WHERE h.id = ?");
    $stmt->execute([$hotel_id]);
    $hotel = $stmt->fetch();

    if (!$hotel) {
        $_SESSION['error'] = "Hotel not found.";
        header("Location: hotels.php");
        exit;
    }
} catch (\PDOException $e) {
    die("Database query error: " . $e->getMessage());
}

require_once 'includes/header.php';
?>

<div class="container py-4">
    <!-- Breadcrumbs -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
            <li class="breadcrumb-item"><a href="hotels.php">Hotels</a></li>
            <li class="breadcrumb-item active"><?php echo htmlspecialchars($hotel['name']); ?></li>
        </ol>
    </nav>

    <div class="row g-4">
        <!-- Left Column: Photos & Details -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white mb-4">
                <div class="position-relative" style="height: 380px;">
                    <img src="<?php echo htmlspecialchars(get_image_url($hotel['image'])); ?>" class="w-100 h-100" style="object-fit: cover;" alt="<?php echo htmlspecialchars($hotel['name']); ?>" onerror="this.onerror=null; this.src='assets/images/default_destination.jpg';">
                    <span class="position-absolute top-0 end-0 m-3 badge bg-warning text-dark font-weight-bold fs-6 px-3 py-2 rounded-pill">
                        <?php for ($s=0; $s<$hotel['star_rating']; $s++): ?>★<?php endfor; ?> <?php echo $hotel['star_rating']; ?> Star Hotel
                    </span>
                </div>
                <div class="card-body p-4">
                    <h2 class="fw-extrabold text-dark mb-1"><?php echo htmlspecialchars($hotel['name']); ?></h2>
                    <div class="text-muted mb-4">
                        <i class="bi-geo-alt-fill text-danger me-1"></i>
                        <a href="destination-detail.php?id=<?php echo $hotel['dest_id']; ?>" class="text-primary fw-semibold"><?php echo htmlspecialchars($hotel['destination_name'] . ', ' . $hotel['destination_country']); ?></a>
                    </div>

                    <h5 class="fw-bold text-dark border-bottom pb-2 mb-3">Hotel Amenities & Services</h5>
                    <div class="row g-3 mb-4">
                        <?php 
                        $amenities = explode(',', $hotel['amenities']);
                        foreach ($amenities as $am):
                        ?>
                            <div class="col-sm-6">
                                <div class="p-3 bg-light rounded-3 d-flex align-items-center gap-2">
                                    <i class="bi-check-circle-fill text-success fs-5"></i>
                                    <span class="fw-semibold text-dark small"><?php echo htmlspecialchars(trim($am)); ?></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <h5 class="fw-bold text-dark border-bottom pb-2 mb-3">Contact & Property Rules</h5>
                    <div class="row g-3 small text-muted">
                        <div class="col-sm-6">
                            <i class="bi-telephone-fill me-2 text-primary"></i><strong>Phone:</strong> <?php echo htmlspecialchars($hotel['contact'] ?? 'Front Desk Support'); ?>
                        </div>
                        <div class="col-sm-6">
                            <i class="bi-clock-fill me-2 text-warning"></i><strong>Check-In:</strong> 12:00 PM | <strong>Check-Out:</strong> 11:00 AM
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column: Interactive Booking Calculator Form -->
        <div class="col-lg-4">
            <div class="sticky-planner-card">
                <h4 class="fw-bold text-dark mb-1">Book Hotel Room</h4>
                <p class="text-muted small mb-3">Select dates and guest details to calculate final price.</p>

                <div class="bg-light p-3 rounded-3 mb-4 text-center">
                    <span class="text-muted small d-block">Nightly Rate</span>
                    <span class="fs-2 fw-extrabold text-success">₹<?php echo number_format($hotel['price_per_night'], 2); ?></span>
                </div>

                <form action="checkout.php" method="GET" class="needs-validation" novalidate>
                    <input type="hidden" name="type" value="hotel">
                    <input type="hidden" name="id" value="<?php echo $hotel['id']; ?>">

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">Check-In Date</label>
                        <input type="date" name="check_in" id="check_in" class="form-control" required min="<?php echo date('Y-m-d'); ?>" value="<?php echo date('Y-m-d'); ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">Check-Out Date</label>
                        <input type="date" name="check_out" id="check_out" class="form-control" required min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>" value="<?php echo date('Y-m-d', strtotime('+2 days')); ?>">
                    </div>

                    <div class="row g-2 mb-4">
                        <div class="col-6">
                            <label class="form-label small fw-bold text-muted">Rooms</label>
                            <select name="rooms" class="form-select">
                                <option value="1">1 Room</option>
                                <option value="2">2 Rooms</option>
                                <option value="3">3 Rooms</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold text-muted">Guests</label>
                            <select name="guests" class="form-select">
                                <option value="1">1 Guest</option>
                                <option value="2" selected>2 Guests</option>
                                <option value="4">4 Guests</option>
                            </select>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 py-3 rounded-pill fw-extrabold shadow-sm fs-5">
                        Proceed to Checkout <i class="bi-arrow-right ms-1"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
