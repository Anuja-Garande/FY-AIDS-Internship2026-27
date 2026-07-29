<?php
// C:\xampp\htdocs\NewProject\offers.php
// Special Offers & Discount Coupons Page

require_once 'config/db_connect.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

try {
    $today = date('Y-m-d');
    $stmt = $pdo->prepare("SELECT * FROM offers WHERE valid_to >= ? ORDER BY valid_to ASC");
    $stmt->execute([$today]);
    $offers = $stmt->fetchAll();
} catch (\PDOException $e) {
    $offers = [];
}

require_once 'includes/header.php';
?>

<!-- Hero Section -->
<div class="bg-danger text-white py-5 mb-4" style="background: linear-gradient(135deg, #e04a22 0%, #ff5e36 100%) !important;">
    <div class="container text-center">
        <h1 class="fw-extrabold text-white mb-2"><i class="bi-percent me-2"></i>Exclusive Travel Offers & Deals</h1>
        <p class="mb-0 text-white-50 fs-5">Apply promo codes during checkout for instant discounts on packages, hotels, and guides</p>
    </div>
</div>

<div class="container py-3">
    <?php if (!empty($offers)): ?>
        <div class="row g-4">
            <?php foreach ($offers as $offer): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card offer-card h-100 shadow-sm rounded-4 overflow-hidden position-relative">
                        <div class="card-body p-4 d-flex flex-column">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-3 py-2 rounded-pill font-weight-bold">
                                    <?php echo $offer['discount_type'] === 'percent' ? intval($offer['discount_value']) . '% OFF' : '₹' . number_format($offer['discount_value']) . ' OFF'; ?>
                                </span>
                                <span class="small text-muted"><i class="bi-tag-fill text-warning me-1"></i><?php echo ucfirst($offer['applicable_to']); ?></span>
                            </div>

                            <h4 class="fw-bold mb-2"><?php echo htmlspecialchars($offer['title']); ?></h4>
                            <p class="small text-muted mb-4 flex-grow-1"><?php echo htmlspecialchars($offer['description']); ?></p>

                            <div class="offer-code-box p-3 rounded-3 text-center mb-3">
                                <div class="small text-muted mb-1">PROMO CODE</div>
                                <div class="fs-4 fw-extrabold text-primary letter-spacing-1 font-monospace select-all"><?php echo htmlspecialchars($offer['code']); ?></div>
                            </div>

                            <div class="d-flex justify-content-between align-items-center text-muted small mt-auto pt-2 border-top">
                                <span><i class="bi-calendar-check me-1"></i>Valid till <?php echo date('M d, Y', strtotime($offer['valid_to'])); ?></span>
                                <button onclick="copyCode('<?php echo htmlspecialchars($offer['code']); ?>')" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                    Copy Code
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="text-center py-5 rounded-3 shadow-sm" style="background-color: var(--card-bg);">
            <i class="bi-ticket-perforated display-1 text-muted mb-3"></i>
            <h4 class="fw-bold">No Active Offers Right Now</h4>
            <p class="text-muted">Check back soon for upcoming holiday sale coupon codes!</p>
        </div>
    <?php endif; ?>
</div>

<script>
function copyCode(code) {
    navigator.clipboard.writeText(code).then(() => {
        alert("Promo code '" + code + "' copied to clipboard!");
    });
}
</script>

<?php require_once 'includes/footer.php'; ?>
