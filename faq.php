<?php
// C:\xampp\htdocs\NewProject\faq.php
// Why Choose Us Section & Accordion FAQ Page

require_once 'config/db_connect.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$search = isset($_GET['search']) ? trim($_GET['search']) : '';

try {
    if ($search !== '') {
        $stmt = $pdo->prepare("SELECT * FROM faqs WHERE question LIKE ? OR answer LIKE ? ORDER BY display_order ASC");
        $stmt->execute(["%$search%", "%$search%"]);
    } else {
        $stmt = $pdo->query("SELECT * FROM faqs ORDER BY display_order ASC");
    }
    $faqs = $stmt->fetchAll();
} catch (\PDOException $e) {
    $faqs = [];
}

require_once 'includes/header.php';
?>

<!-- Hero Section -->
<div class="bg-primary text-white py-5 mb-4" style="background: linear-gradient(135deg, var(--primary-dark) 0%, #153e6d 100%) !important;">
    <div class="container text-center">
        <h1 class="fw-extrabold text-white mb-2"><i class="bi-question-circle me-2"></i>Why Choose Us & FAQs</h1>
        <p class="mb-0 text-white-50 fs-5">Everything you need to know about booking tours, hotels, and guides with TravelPortal</p>
    </div>
</div>

<!-- Why Choose Us Trust Section -->
<section class="container my-5">
    <div class="text-center mb-5">
        <h5 class="text-uppercase text-primary small fw-bold">Our Promise</h5>
        <h2 class="fw-extrabold text-dark">Why Choose TravelPortal?</h2>
    </div>

    <div class="row g-4">
        <div class="col-md-4">
            <div class="card h-100 border-0 shadow-sm rounded-4 p-4 text-center bg-white">
                <div class="bg-primary-subtle text-primary rounded-circle p-3 d-inline-flex mx-auto mb-3" style="width: 70px; height: 70px;">
                    <i class="bi-shield-check fs-2"></i>
                </div>
                <h5 class="fw-bold text-dark mb-2">Verified Guides & Hotels</h5>
                <p class="small text-muted mb-0">Every property and guide listed on our portal undergoes thorough background checks and quality auditing.</p>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card h-100 border-0 shadow-sm rounded-4 p-4 text-center bg-white">
                <div class="bg-success-subtle text-success rounded-circle p-3 d-inline-flex mx-auto mb-3" style="width: 70px; height: 70px;">
                    <i class="bi-currency-dollar fs-2"></i>
                </div>
                <h5 class="fw-bold text-dark mb-2">Best Price Guarantee</h5>
                <p class="small text-muted mb-0">We guarantee the most competitive rates for holiday packages and hotels with zero hidden platform fees.</p>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card h-100 border-0 shadow-sm rounded-4 p-4 text-center bg-white">
                <div class="bg-warning-subtle text-warning rounded-circle p-3 d-inline-flex mx-auto mb-3" style="width: 70px; height: 70px;">
                    <i class="bi-headset fs-2"></i>
                </div>
                <h5 class="fw-bold text-dark mb-2">24/7 Dedicated Support</h5>
                <p class="small text-muted mb-0">Our friendly customer support team is available round-the-clock to assist you before, during, and after your trip.</p>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card h-100 border-0 shadow-sm rounded-4 p-4 text-center bg-white">
                <div class="bg-info-subtle text-info rounded-circle p-3 d-inline-flex mx-auto mb-3" style="width: 70px; height: 70px;">
                    <i class="bi-lightning-charge fs-2"></i>
                </div>
                <h5 class="fw-bold text-dark mb-2">Instant Confirmations</h5>
                <p class="small text-muted mb-0">Get immediate in-app booking vouchers and email confirmations the moment you place your order.</p>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card h-100 border-0 shadow-sm rounded-4 p-4 text-center bg-white">
                <div class="bg-danger-subtle text-danger rounded-circle p-3 d-inline-flex mx-auto mb-3" style="width: 70px; height: 70px;">
                    <i class="bi-percent fs-2"></i>
                </div>
                <h5 class="fw-bold text-dark mb-2">Exclusive Deals & Offers</h5>
                <p class="small text-muted mb-0">Regular seasonal sales and promo coupon codes to make luxury traveling affordable for everyone.</p>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card h-100 border-0 shadow-sm rounded-4 p-4 text-center bg-white">
                <div class="bg-secondary-subtle text-secondary rounded-circle p-3 d-inline-flex mx-auto mb-3" style="width: 70px; height: 70px;">
                    <i class="bi-arrow-counterclockwise fs-2"></i>
                </div>
                <h5 class="fw-bold text-dark mb-2">Flexible Cancellation</h5>
                <p class="small text-muted mb-0">Hassle-free 100% full refund policy for cancellations made up to 48 hours before check-in.</p>
            </div>
        </div>
    </div>
</section>

<!-- Dynamic Accordion FAQ Section -->
<section class="container my-5">
    <div class="text-center mb-4">
        <h2 class="fw-extrabold text-dark">Frequently Asked Questions</h2>
        <p class="text-muted">Find quick answers to common questions about our travel portal</p>

        <!-- Search Bar -->
        <div class="row justify-content-center mt-3">
            <div class="col-md-6">
                <form action="faq.php" method="GET" class="input-group">
                    <input type="text" name="search" class="form-control" placeholder="Search questions..." value="<?php echo htmlspecialchars($search); ?>">
                    <button class="btn btn-primary" type="submit"><i class="bi-search"></i> Search</button>
                </form>
            </div>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-10">
            <?php if (!empty($faqs)): ?>
                <div class="accordion shadow-sm rounded-4 overflow-hidden" id="faqAccordion">
                    <?php foreach ($faqs as $index => $faq): ?>
                        <div class="accordion-item border-0 border-bottom">
                            <h2 class="accordion-header" id="heading<?php echo $faq['id']; ?>">
                                <button class="accordion-button <?php echo $index === 0 ? '' : 'collapsed'; ?> fw-bold text-dark py-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?php echo $faq['id']; ?>" aria-expanded="<?php echo $index === 0 ? 'true' : 'false'; ?>">
                                    <i class="bi-question-circle-fill text-primary me-2"></i><?php echo htmlspecialchars($faq['question']); ?>
                                </button>
                            </h2>
                            <div id="collapse<?php echo $faq['id']; ?>" class="accordion-collapse collapse <?php echo $index === 0 ? 'show' : ''; ?>" data-bs-parent="#faqAccordion">
                                <div class="accordion-body text-muted small leading-relaxed bg-white">
                                    <?php echo htmlspecialchars($faq['answer']); ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="text-center py-5 bg-white rounded-3 shadow-sm">
                    <i class="bi-emoji-frown text-muted display-1 mb-3"></i>
                    <h4 class="fw-bold text-dark">No FAQs Found</h4>
                    <p class="text-muted">Try searching with a different keyword.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
