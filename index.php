<?php
// C:\xampp\htdocs\NewProject\index.php
// Home Page

require_once 'config/db_connect.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

try {
    // 1. Fetch Categories for the Search form and the Category strip
    $categoriesStmt = $pdo->query("SELECT * FROM categories ORDER BY id ASC");
    $categories = $categoriesStmt->fetchAll();

    // 2. Fetch Featured destinations for the Carousel (e.g. top-rated 3 destinations)
    $carouselStmt = $pdo->query("SELECT d.*, c.name AS category_name FROM destinations d LEFT JOIN categories c ON d.category_id = c.id ORDER BY d.rating DESC LIMIT 3");
    $carouselDestinations = $carouselStmt->fetchAll();

    // 3. Fetch Popular destinations (next 6 top destinations)
    $popularStmt = $pdo->query("SELECT d.*, c.name AS category_name FROM destinations d LEFT JOIN categories c ON d.category_id = c.id ORDER BY d.rating DESC LIMIT 6");
    $popularDestinations = $popularStmt->fetchAll();

    // 4. Fetch Active Offers for home promo banner
    $today = date('Y-m-d');
    $offersStmt = $pdo->prepare("SELECT * FROM offers WHERE valid_to >= ? ORDER BY valid_to ASC LIMIT 2");
    $offersStmt->execute([$today]);
    $homeOffers = $offersStmt->fetchAll();

} catch (\PDOException $e) {
    die("Database error: " . $e->getMessage());
}

require_once 'includes/header.php';
?>

<!-- Hero Banner Section -->
<section class="hero-section text-center">
    <div class="container">
        <h1 class="hero-title"><span class="text-gradient">Find Your Next<br>Dream Destination</span></h1>
        <p class="hero-subtitle">Find stunning destinations, build custom itineraries, and read ratings from real travelers.</p>
    </div>
</section>

<!-- Floating Search Widget (MakeMyTrip Style) -->
<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="search-widget">
                <form action="destinations.php" method="GET">
                    <div class="row align-items-center g-3">
                        <!-- Search Name/Country -->
                        <div class="col-md-4">
                            <div class="search-input-group">
                                <div class="search-input-label">Where to?</div>
                                <input type="text" name="search" class="search-input-val" placeholder="Search destination, country...">
                            </div>
                        </div>
                        
                        <!-- Select Region -->
                        <div class="col-md-3">
                            <div class="search-input-group">
                                <div class="search-input-label">Region</div>
                                <select name="region" class="search-input-val form-select border-0 px-0">
                                    <option value="">All Regions</option>
                                    <option value="National">National (India 🇮🇳)</option>
                                    <option value="International">International ✈️</option>
                                </select>
                            </div>
                        </div>

                        <!-- Select Category -->
                        <div class="col-md-3">
                            <div class="search-input-group">
                                <div class="search-input-label">Category</div>
                                <select name="category" class="search-input-val form-select border-0 px-0">
                                    <option value="">All Categories</option>
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <!-- Select Budget -->
                        <div class="col-md-2">
                            <div class="search-input-group border-0">
                                <div class="search-input-label">Budget</div>
                                <select name="budget" class="search-input-val form-select border-0 px-0">
                                    <option value="">Any</option>
                                    <option value="Budget">Budget</option>
                                    <option value="Mid-Range">Mid</option>
                                    <option value="Luxury">Luxury</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="text-center">
                        <button type="submit" class="btn btn-search-submit px-5 rounded-pill">
                            <i class="bi-search me-2"></i>Search Packages
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Category Strip Section -->
<section class="container mt-5 pt-4">
    <div class="text-center mb-4">
        <h5 class="text-uppercase text-muted small fw-bold">Explore by Interest</h5>
        <h2 class="fw-bold">Popular Categories</h2>
    </div>
    <div class="category-strip">
        <?php foreach ($categories as $cat): ?>
            <a href="destinations.php?category=<?php echo $cat['id']; ?>" class="category-item">
                <i class="<?php echo htmlspecialchars($cat['icon']); ?>"></i>
                <span><?php echo htmlspecialchars($cat['name']); ?></span>
            </a>
        <?php endforeach; ?>
    </div>
</section>

<!-- Featured Destinations Carousel (Top Rated) -->
<?php if (!empty($carouselDestinations)): ?>
<section class="container my-5">
    <div class="mb-4">
        <h2 class="fw-bold"><i class="bi-stars text-warning me-2"></i>Handpicked Featured Trips</h2>
        <p class="text-muted">Specially curated high-rating destinations for your perfect getaway</p>
    </div>

    <div id="featuredCarousel" class="carousel slide carousel-dark shadow-lg rounded-4 overflow-hidden" data-bs-ride="carousel">
        <div class="carousel-indicators">
            <?php foreach ($carouselDestinations as $index => $cDest): ?>
                <button type="button" data-bs-target="#featuredCarousel" data-bs-slide-to="<?php echo $index; ?>" class="<?php echo $index === 0 ? 'active' : ''; ?>" aria-current="true" aria-label="Slide <?php echo $index + 1; ?>"></button>
            <?php endforeach; ?>
        </div>
        <div class="carousel-inner">
            <?php foreach ($carouselDestinations as $index => $cDest): ?>
                <div class="carousel-item <?php echo $index === 0 ? 'active' : ''; ?>" data-bs-interval="5000">
                    <div class="row g-0 featured-carousel-card">
                        <!-- Image Block -->
                        <div class="col-md-6 featured-carousel-img-wrapper position-relative">
                            <img src="<?php echo htmlspecialchars(get_image_url($cDest['image'])); ?>" alt="<?php echo htmlspecialchars($cDest['name']); ?>" onerror="this.onerror=null; this.src='assets/images/default_destination.jpg';">
                            <div class="card-badge bg-primary text-white position-absolute top-0 start-0 m-3"><?php echo htmlspecialchars($cDest['category_name'] ?? 'General'); ?></div>
                        </div>
                        <!-- Text Block -->
                        <div class="col-md-6 d-flex flex-column justify-content-center p-4 p-md-5">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="badge bg-success-subtle text-success border border-success-subtle fs-6 rounded-pill px-3 py-1">
                                    <i class="bi-star-fill me-1"></i><?php echo number_format($cDest['rating'], 1); ?> Rated
                                </span>
                                <span class="text-muted small"><i class="bi-globe-asia-australia me-1"></i><?php echo htmlspecialchars($cDest['continent']); ?></span>
                            </div>
                            <h3 class="fw-extrabold text-dark mb-3"><?php echo htmlspecialchars($cDest['name']); ?>, <span class="text-muted"><?php echo htmlspecialchars($cDest['country']); ?></span></h3>
                            <p class="text-muted mb-4 line-clamp-3"><?php echo htmlspecialchars(substr($cDest['description'], 0, 180)) . '...'; ?></p>
                            
                            <div class="row mb-4">
                                <div class="col-6">
                                    <div class="small text-muted"><i class="bi-calendar-range text-primary me-1"></i>Best Time</div>
                                    <strong class="text-dark small"><?php echo htmlspecialchars($cDest['best_time_to_visit']); ?></strong>
                                </div>
                                <div class="col-6">
                                    <div class="small text-muted"><i class="bi-wallet2 text-primary me-1"></i>Budget Tier</div>
                                    <strong class="text-danger small"><?php echo htmlspecialchars($cDest['price_range']); ?></strong>
                                </div>
                            </div>
                            
                            <div>
                                <a href="destination-detail.php?id=<?php echo $cDest['id']; ?>" class="btn btn-warning px-4 py-2 font-weight-bold text-dark rounded-pill">
                                    View Itinerary Detail <i class="bi-arrow-right-short ms-1"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <button class="carousel-control-prev" type="button" data-bs-target="#featuredCarousel" data-bs-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true" style="filter: invert(1);"></span>
            <span class="visually-hidden">Previous</span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#featuredCarousel" data-bs-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true" style="filter: invert(1);"></span>
            <span class="visually-hidden">Next</span>
        </button>
    </div>
</section>
<?php endif; ?>

<!-- Popular Destinations (Grid view) -->
<section class="container my-5">
    <div class="text-center mb-5">
        <h5 class="text-uppercase text-muted small fw-bold">Ready to travel?</h5>
        <h2 class="fw-bold">Trending Destinations</h2>
    </div>

    <div class="row g-4">
        <?php foreach ($popularDestinations as $dest): ?>
            <div class="col-lg-4 col-md-6">
                <div class="destination-card">
                    <div class="card-img-wrapper">
                        <img src="<?php echo htmlspecialchars(get_image_url($dest['image'])); ?>" alt="<?php echo htmlspecialchars($dest['name']); ?>" onerror="this.onerror=null; this.src='assets/images/default_destination.jpg';">
                        <div class="card-badge"><?php echo htmlspecialchars($dest['category_name'] ?? 'General'); ?></div>
                        <div class="card-rating-badge">
                            <i class="bi-star-fill"></i><?php echo number_format($dest['rating'], 1); ?>
                        </div>
                    </div>
                    <div class="card-body-custom">
                        <h4 class="card-dest-title text-truncate"><?php echo htmlspecialchars($dest['name']); ?></h4>
                        <div class="card-dest-location">
                            <i class="bi-geo-alt-fill text-danger"></i>
                            <span><?php echo htmlspecialchars($dest['country']); ?>, <?php echo htmlspecialchars($dest['continent']); ?></span>
                        </div>
                        <p class="small text-muted text-clamp-2" style="min-height: 48px;">
                            <?php echo htmlspecialchars(substr($dest['description'], 0, 110)) . '...'; ?>
                        </p>
                        
                        <div class="card-dest-footer">
                            <div class="card-dest-price">
                                <span class="text-muted small d-block fw-light">Budget Tier</span>
                                <?php echo htmlspecialchars($dest['price_range']); ?>
                            </div>
                            <a href="destination-detail.php?id=<?php echo $dest['id']; ?>" class="btn btn-outline-primary btn-sm rounded-pill px-3">
                                Explore <i class="bi-chevron-right ms-1 small"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    
    <div class="text-center mt-5">
        <a href="destinations.php" class="btn btn-outline-dark btn-lg rounded-pill px-5">
            View All Destinations <i class="bi-grid ms-2"></i>
        </a>
    </div>
</section>

<!-- Active Offers Strip -->
<?php if (!empty($homeOffers)): ?>
<section class="container my-5">
    <div class="bg-danger text-white p-4 p-md-5 rounded-4 shadow-lg position-relative overflow-hidden" style="background: linear-gradient(135deg, #e04a22 0%, #ff5e36 100%) !important;">
        <div class="row align-items-center position-relative" style="z-index: 2;">
            <div class="col-lg-8">
                <span class="badge bg-white text-danger font-weight-bold uppercase mb-2 px-3 py-2 rounded-pill"><i class="bi-percent me-1"></i>Limited Time Offer</span>
                <h2 class="fw-extrabold text-white mb-2"><?php echo htmlspecialchars($homeOffers[0]['title']); ?></h2>
                <p class="mb-0 text-white-50 fs-5"><?php echo htmlspecialchars($homeOffers[0]['description']); ?></p>
            </div>
            <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
                <a href="offers.php" class="btn btn-light btn-lg rounded-pill px-4 font-weight-bold text-dark shadow-sm">
                    Claim Code: <strong class="text-primary font-monospace"><?php echo htmlspecialchars($homeOffers[0]['code']); ?></strong>
                </a>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Why Choose Us Trust Section -->
<section class="container my-5 py-4">
    <div class="text-center mb-5">
        <h5 class="text-uppercase text-muted small fw-bold">Our Guarantee</h5>
        <h2 class="fw-extrabold text-dark">Why Plan Your Trip With Us?</h2>
    </div>
    <div class="row g-4">
        <div class="col-md-3 col-sm-6 text-center">
            <div class="bg-white p-4 rounded-4 shadow-sm h-100 border">
                <i class="bi-shield-check display-4 text-primary mb-3"></i>
                <h5 class="fw-bold text-dark">Verified Guides</h5>
                <p class="small text-muted mb-0">Certified local guide partners with background verification.</p>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 text-center">
            <div class="bg-white p-4 rounded-4 shadow-sm h-100 border">
                <i class="bi-currency-exchange display-4 text-success mb-3"></i>
                <h5 class="fw-bold text-dark">Best Price Guarantee</h5>
                <p class="small text-muted mb-0">Competitive transparent pricing with zero hidden charges.</p>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 text-center">
            <div class="bg-white p-4 rounded-4 shadow-sm h-100 border">
                <i class="bi-headset display-4 text-warning mb-3"></i>
                <h5 class="fw-bold text-dark">24/7 Support</h5>
                <p class="small text-muted mb-0">Round the clock concierge assistance during your travel.</p>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 text-center">
            <div class="bg-white p-4 rounded-4 shadow-sm h-100 border">
                <i class="bi-lock-fill display-4 text-info mb-3"></i>
                <h5 class="fw-bold text-dark">Secure Booking</h5>
                <p class="small text-muted mb-0">Instant encrypted checkout and automated voucher confirmation.</p>
            </div>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
