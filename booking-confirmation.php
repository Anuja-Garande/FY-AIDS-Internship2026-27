<?php
// C:\xampp\htdocs\NewProject\booking-confirmation.php
// Booking Confirmation & Receipt Page

require_once 'config/db_connect.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$booking_number = isset($_GET['booking_number']) ? trim($_GET['booking_number']) : '';
if (empty($booking_number)) {
    header("Location: profile.php");
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT b.*, u.name AS user_name, u.email AS user_email FROM bookings b JOIN users u ON b.user_id = u.id WHERE b.booking_number = ? AND b.user_id = ?");
    $stmt->execute([$booking_number, $_SESSION['user_id']]);
    $booking = $stmt->fetch();

    if (!$booking) {
        $_SESSION['error'] = "Booking record not found.";
        header("Location: profile.php");
        exit;
    }
} catch (\PDOException $e) {
    die("Database query error: " . $e->getMessage());
}

require_once 'includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-lg rounded-4 overflow-hidden bg-white p-4 p-md-5 text-center">
                <div class="mb-4">
                    <div class="bg-success-subtle text-success d-inline-flex align-items-center justify-content-center rounded-circle p-4 mb-3" style="width: 90px; height: 90px;">
                        <i class="bi-check-circle-fill display-4"></i>
                    </div>
                    <h2 class="fw-extrabold text-dark mb-1">Booking Confirmed!</h2>
                    <p class="text-muted">Thank you for booking with TravelPortal. An in-app confirmation has been generated.</p>
                </div>

                <div class="bg-light p-4 rounded-3 border text-start mb-4">
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <span class="text-muted small d-block">Booking Reference</span>
                            <strong class="fs-5 text-primary font-monospace"><?php echo htmlspecialchars($booking['booking_number']); ?></strong>
                        </div>
                        <div class="col-sm-6 text-sm-end">
                            <span class="text-muted small d-block">Status</span>
                            <span class="badge bg-success fs-6 rounded-pill px-3 py-1"><?php echo ucfirst($booking['status']); ?></span>
                        </div>
                        <div class="col-sm-6">
                            <span class="text-muted small d-block">Booking Type</span>
                            <strong class="text-dark"><?php echo ucfirst($booking['booking_type']); ?> Reservation</strong>
                        </div>
                        <div class="col-sm-6 text-sm-end">
                            <span class="text-muted small d-block">Total Paid</span>
                            <strong class="fs-4 text-success">₹<?php echo number_format($booking['total_price'], 2); ?></strong>
                        </div>
                        <div class="col-sm-6">
                            <span class="text-muted small d-block">Check-In / Travel Date</span>
                            <strong class="text-dark"><?php echo date('M d, Y', strtotime($booking['check_in'])); ?></strong>
                        </div>
                        <div class="col-sm-6 text-sm-end">
                            <span class="text-muted small d-block">Check-Out Date</span>
                            <strong class="text-dark"><?php echo date('M d, Y', strtotime($booking['check_out'])); ?></strong>
                        </div>
                    </div>
                </div>

                <div class="d-flex flex-wrap gap-3 justify-content-center">
                    <a href="profile.php" class="btn btn-primary rounded-pill px-4 fw-bold">
                        View My Bookings <i class="bi-person-circle ms-1"></i>
                    </a>
                    <button onclick="window.print()" class="btn btn-outline-dark rounded-pill px-4 fw-bold">
                        Print Receipt <i class="bi-printer ms-1"></i>
                    </button>
                    <a href="destinations.php" class="btn btn-outline-secondary rounded-pill px-4">
                        Explore More Trips
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
