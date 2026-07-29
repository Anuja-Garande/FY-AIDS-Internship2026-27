<?php
// C:\xampp\htdocs\NewProject\package-detail.php
// Package Detail Page

require_once 'config/db_connect.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pkg_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($pkg_id <= 0) {
    header("Location: packages.php");
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT p.*, d.name AS destination_name, d.country AS destination_country, d.id AS dest_id 
                           FROM packages p 
                           LEFT JOIN destinations d ON p.destination_id = d.id 
                           WHERE p.id = ?");
    $stmt->execute([$pkg_id]);
    $package = $stmt->fetch();

    if (!$package) {
        $_SESSION['error'] = "Package not found.";
        header("Location: packages.php");
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
            <li class="breadcrumb-item"><a href="packages.php">Packages</a></li>
            <li class="breadcrumb-item active"><?php echo htmlspecialchars($package['name']); ?></li>
        </ol>
    </nav>

    <div class="row g-4">
        <!-- Left Side Image & Information -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white mb-4">
                <div class="position-relative" style="height: 380px;">
                    <img src="<?php echo htmlspecialchars(get_image_url($package['image'])); ?>" alt="<?php echo htmlspecialchars($package['name']); ?>" class="w-100 h-100" style="object-fit: cover;" onerror="this.onerror=null; this.src='assets/images/default_destination.jpg';">
                    <span class="position-absolute top-0 end-0 m-3 badge bg-warning text-dark font-weight-bold fs-6 px-3 py-2 rounded-pill">
                        <i class="bi-clock-history me-1"></i><?php echo $package['duration_days']; ?> Days Tour
                    </span>
                </div>
                <div class="card-body p-4">
                    <h2 class="fw-extrabold text-dark mb-2"><?php echo htmlspecialchars($package['name']); ?></h2>
                    <?php if (!empty($package['destination_name'])): ?>
                        <div class="text-muted mb-4 fs-6">
                            <i class="bi-geo-alt-fill text-danger me-1"></i>
                            Destination: <a href="destination-detail.php?id=<?php echo $package['dest_id']; ?>" class="fw-bold text-primary"><?php echo htmlspecialchars($package['destination_name'] . ', ' . $package['destination_country']); ?></a>
                        </div>
                    <?php endif; ?>

                    <h5 class="fw-bold text-dark border-bottom pb-2 mb-3">Package Overview</h5>
                    <p class="text-muted" style="white-space: pre-line; line-height: 1.8;"><?php echo htmlspecialchars($package['description']); ?></p>

                    <?php if (!empty($package['inclusions'])): ?>
                        <h5 class="fw-bold text-dark border-bottom pb-2 mt-4 mb-3"><i class="bi-patch-check-fill text-success me-2"></i>What's Included in this Tour</h5>
                        <div class="row g-2">
                            <?php 
                            $inclusions = explode(',', $package['inclusions']);
                            foreach ($inclusions as $inc):
                            ?>
                                <div class="col-sm-6">
                                    <div class="p-3 bg-light rounded-3 d-flex align-items-center gap-2">
                                        <i class="bi-check-circle-fill text-success fs-5"></i>
                                        <span class="fw-semibold text-dark small"><?php echo htmlspecialchars(trim($inc)); ?></span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Right Side Pricing & Booking Form Card -->
        <div class="col-lg-4">
            <div class="sticky-planner-card">
                <h4 class="fw-bold text-dark mb-1">Book Tour Package</h4>
                <p class="text-muted small mb-4">Instant reservation with price guarantee and discount coupon support.</p>

                <div class="bg-light p-3 rounded-3 mb-4 text-center">
                    <span class="text-muted small d-block">Starting Price / Person</span>
                    <span class="fs-2 fw-extrabold text-success">₹<?php echo number_format($package['price'], 2); ?></span>
                </div>

                <div class="d-flex flex-column gap-3 mb-4 small text-muted">
                    <div class="d-flex justify-content-between border-bottom pb-2">
                        <span><i class="bi-calendar-event me-2 text-primary"></i>Duration</span>
                        <strong class="text-dark"><?php echo $package['duration_days']; ?> Days / <?php echo ($package['duration_days']-1); ?> Nights</strong>
                    </div>
                    <div class="d-flex justify-content-between border-bottom pb-2">
                        <span><i class="bi-shield-check me-2 text-success"></i>Cancellation</span>
                        <strong class="text-dark">100% Refundable (48h prior)</strong>
                    </div>
                    <div class="d-flex justify-content-between border-bottom pb-2">
                        <span><i class="bi-headset me-2 text-warning"></i>Support</span>
                        <strong class="text-dark">24/7 Tour Concierge</strong>
                    </div>
                </div>

                <a href="checkout.php?type=package&id=<?php echo $package['id']; ?>" class="btn btn-warning w-100 py-3 rounded-pill text-dark fw-extrabold shadow-sm fs-5">
                    Proceed to Booking <i class="bi-arrow-right ms-1"></i>
                </a>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
