<?php
// C:\xampp\htdocs\NewProject\destination-detail.php
// Destination Detail Page

require_once 'config/db_connect.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Get Destination ID
$dest_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($dest_id <= 0) {
    header("Location: destinations.php");
    exit;
}

// 2. Process POST Actions (Auth Guard for actions)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_SESSION['user_id'])) {
        $_SESSION['error'] = "You must be logged in to perform this action.";
        header("Location: login.php");
        exit;
    }
    
    $user_id = $_SESSION['user_id'];
    $action = isset($_POST['action']) ? trim($_POST['action']) : '';

    // Action A: Toggle Wishlist
    if ($action === 'toggle_wishlist') {
        try {
            // Check if exists
            $stmt = $pdo->prepare("SELECT id FROM wishlist WHERE user_id = ? AND destination_id = ?");
            $stmt->execute([$user_id, $dest_id]);
            if ($stmt->rowCount() > 0) {
                // Remove
                $del = $pdo->prepare("DELETE FROM wishlist WHERE user_id = ? AND destination_id = ?");
                $del->execute([$user_id, $dest_id]);
                $_SESSION['success'] = "Removed from wishlist.";
            } else {
                // Add
                $ins = $pdo->prepare("INSERT INTO wishlist (user_id, destination_id) VALUES (?, ?)");
                $ins->execute([$user_id, $dest_id]);
                $_SESSION['success'] = "Added to wishlist!";
            }
        } catch (\PDOException $e) {
            $_SESSION['error'] = "Wishlist action failed: " . $e->getMessage();
        }
        header("Location: destination-detail.php?id=" . $dest_id);
        exit;
    }

    // Action B: Create Itinerary
    if ($action === 'create_itinerary') {
        $travel_date = isset($_POST['travel_date']) ? trim($_POST['travel_date']) : '';
        $notes = isset($_POST['notes']) ? trim($_POST['notes']) : '';
        
        if (empty($travel_date)) {
            $_SESSION['error'] = "Please select a travel date.";
        } else {
            try {
                $ins = $pdo->prepare("INSERT INTO itineraries (user_id, destination_id, travel_date, notes) VALUES (?, ?, ?, ?)");
                $ins->execute([$user_id, $dest_id, $travel_date, $notes]);
                $_SESSION['success'] = "Trip itinerary added successfully! View in your profile.";
            } catch (\PDOException $e) {
                $_SESSION['error'] = "Failed to create itinerary: " . $e->getMessage();
            }
        }
        header("Location: destination-detail.php?id=" . $dest_id);
        exit;
    }

    // Action C: Submit Review
    if ($action === 'add_review') {
        $rating = isset($_POST['rating']) ? intval($_POST['rating']) : 0;
        $comment = isset($_POST['comment']) ? trim($_POST['comment']) : '';

        if ($rating < 1 || $rating > 5) {
            $_SESSION['error'] = "Please select a star rating between 1 and 5.";
        } elseif (empty($comment)) {
            $_SESSION['error'] = "Please write a comment for your review.";
        } else {
            try {
                // Check if user has already reviewed this destination
                $check = $pdo->prepare("SELECT id FROM reviews WHERE user_id = ? AND destination_id = ?");
                $check->execute([$user_id, $dest_id]);
                
                if ($check->rowCount() > 0) {
                    // Update review
                    $upd = $pdo->prepare("UPDATE reviews SET rating = ?, comment = ? WHERE user_id = ? AND destination_id = ?");
                    $upd->execute([$rating, $comment, $user_id, $dest_id]);
                    $_SESSION['success'] = "Your review was updated successfully.";
                } else {
                    // Insert review
                    $ins = $pdo->prepare("INSERT INTO reviews (user_id, destination_id, rating, comment) VALUES (?, ?, ?, ?)");
                    $ins->execute([$user_id, $dest_id, $rating, $comment]);
                    $_SESSION['success'] = "Thank you for reviewing this destination!";
                }

                // Recalculate and update cached rating in destinations table
                $updRating = $pdo->prepare("UPDATE destinations SET rating = (SELECT IFNULL(AVG(rating), 0) FROM reviews WHERE destination_id = ?) WHERE id = ?");
                $updRating->execute([$dest_id, $dest_id]);

            } catch (\PDOException $e) {
                $_SESSION['error'] = "Failed to save review: " . $e->getMessage();
            }
        }
        header("Location: destination-detail.php?id=" . $dest_id);
        exit;
    }

    // Action D: Delete Review
    if ($action === 'delete_review') {
        $review_id = isset($_POST['review_id']) ? intval($_POST['review_id']) : 0;
        try {
            // Delete review (verify owner or admin status)
            $stmt = $pdo->prepare("DELETE FROM reviews WHERE id = ? AND (user_id = ? OR ? = 'admin')");
            $stmt->execute([$review_id, $user_id, $_SESSION['user_role']]);
            
            $_SESSION['success'] = "Review deleted successfully.";

            // Recalculate cached rating
            $updRating = $pdo->prepare("UPDATE destinations SET rating = (SELECT IFNULL(AVG(rating), 0) FROM reviews WHERE destination_id = ?) WHERE id = ?");
            $updRating->execute([$dest_id, $dest_id]);
        } catch (\PDOException $e) {
            $_SESSION['error'] = "Failed to delete review: " . $e->getMessage();
        }
        header("Location: destination-detail.php?id=" . $dest_id);
        exit;
    }
}

// 3. Fetch Destination Details
try {
    $stmt = $pdo->prepare("SELECT d.*, c.name AS category_name FROM destinations d 
                           LEFT JOIN categories c ON d.category_id = c.id 
                           WHERE d.id = ?");
    $stmt->execute([$dest_id]);
    $destination = $stmt->fetch();

    if (!$destination) {
        $_SESSION['error'] = "Destination not found.";
        header("Location: destinations.php");
        exit;
    }

    // Fetch all reviews for this destination
    $revStmt = $pdo->prepare("SELECT r.*, u.name AS user_name FROM reviews r 
                              JOIN users u ON r.user_id = u.id 
                              WHERE r.destination_id = ? ORDER BY r.created_at DESC");
    $revStmt->execute([$dest_id]);
    $reviews = $revStmt->fetchAll();

    // Check if current user has wishlisted this
    $isWishlisted = false;
    if (isset($_SESSION['user_id'])) {
        $wCheck = $pdo->prepare("SELECT id FROM wishlist WHERE user_id = ? AND destination_id = ?");
        $wCheck->execute([$_SESSION['user_id'], $dest_id]);
        if ($wCheck->rowCount() > 0) {
            $isWishlisted = true;
        }
    }

    // Fetch Nearby Places to Visit
    $npStmt = $pdo->prepare("SELECT * FROM nearby_places WHERE destination_id = ? ORDER BY distance_km ASC");
    $npStmt->execute([$dest_id]);
    $nearbyPlaces = $npStmt->fetchAll();

    // Fetch Hotels
    $hStmt = $pdo->prepare("SELECT * FROM hotels WHERE destination_id = ? ORDER BY star_rating DESC");
    $hStmt->execute([$dest_id]);
    $destHotels = $hStmt->fetchAll();

    // Fetch Restaurants
    $restStmt = $pdo->prepare("SELECT * FROM restaurants WHERE destination_id = ? ORDER BY rating DESC");
    $restStmt->execute([$dest_id]);
    $destRestaurants = $restStmt->fetchAll();

    // Fetch Tour Guides
    $gStmt = $pdo->prepare("SELECT * FROM guides WHERE destination_id = ? ORDER BY rating DESC");
    $gStmt->execute([$dest_id]);
    $destGuides = $gStmt->fetchAll();

    // Fetch Packages
    $pkgStmt = $pdo->prepare("SELECT * FROM packages WHERE destination_id = ? ORDER BY price ASC");
    $pkgStmt->execute([$dest_id]);
    $destPackages = $pkgStmt->fetchAll();

} catch (\PDOException $e) {
    die("Database query error: " . $e->getMessage());
}

require_once 'includes/header.php';
?>

<div class="container py-3">
    <!-- Breadcrumbs -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
            <li class="breadcrumb-item"><a href="destinations.php">Destinations</a></li>
            <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($destination['name']); ?></li>
        </ol>
    </nav>

    <!-- Destination Hero Block -->
    <div class="detail-hero" style="background-image: url('<?php echo htmlspecialchars(get_image_url($destination['image'])); ?>');">
        <div class="detail-hero-overlay">
            <span class="badge bg-warning text-dark font-weight-bold mb-2 px-3 py-2 rounded-pill fs-6 w-auto align-self-start">
                <?php echo htmlspecialchars($destination['category_name'] ?? 'General'); ?>
            </span>
            <h1 class="detail-hero-title"><?php echo htmlspecialchars($destination['name']); ?></h1>
            <div class="detail-hero-subtitle">
                <i class="bi-geo-alt-fill text-danger me-1"></i><?php echo htmlspecialchars($destination['country']); ?>, <?php echo htmlspecialchars($destination['continent']); ?>
            </div>
        </div>
    </div>

    <!-- Quick Info Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-3 p-3 bg-white">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-success-subtle text-success fs-3 px-3 py-2 rounded-3"><i class="bi-star-fill"></i></div>
                    <div>
                        <div class="text-muted small">Average Rating</div>
                        <h4 class="fw-bold mb-0 text-dark"><?php echo number_format($destination['rating'], 1); ?> / 5.0</h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-3 p-3 bg-white">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-primary-subtle text-primary fs-3 px-3 py-2 rounded-3"><i class="bi-calendar-event"></i></div>
                    <div>
                        <div class="text-muted small">Best Time to Visit</div>
                        <h5 class="fw-bold mb-0 text-dark"><?php echo htmlspecialchars($destination['best_time_to_visit']); ?></h5>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-3 p-3 bg-white">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-danger-subtle text-danger fs-3 px-3 py-2 rounded-3"><i class="bi-wallet2"></i></div>
                    <div>
                        <div class="text-muted small">Budget Tier</div>
                        <h5 class="fw-bold mb-0 text-dark"><?php echo htmlspecialchars($destination['price_range']); ?></h5>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Details Grid -->
    <div class="row">
        <!-- Left Side Details (col-lg-8) -->
        <div class="col-lg-8 mb-4">
            <!-- Tabs Navigation -->
            <ul class="nav nav-pills nav-pills-custom mb-3 flex-wrap gap-1" id="detailsTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="overview-tab" data-bs-toggle="pill" data-bs-target="#overview" type="button" role="tab">
                        <i class="bi-info-circle me-1"></i>Overview
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="attractions-tab" data-bs-toggle="pill" data-bs-target="#attractions" type="button" role="tab">
                        <i class="bi-compass me-1"></i>Attractions <?php echo !empty($nearbyPlaces) ? '('.count($nearbyPlaces).')' : ''; ?>
                    </button>
                </li>
                <?php if (!empty($destHotels)): ?>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="hotels-tab" data-bs-toggle="pill" data-bs-target="#hotels" type="button" role="tab">
                            <i class="bi-building me-1"></i>Hotels (<?php echo count($destHotels); ?>)
                        </button>
                    </li>
                <?php endif; ?>
                <?php if (!empty($destRestaurants)): ?>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="restaurants-tab" data-bs-toggle="pill" data-bs-target="#restaurants" type="button" role="tab">
                            <i class="bi-cup-hot me-1"></i>Dining (<?php echo count($destRestaurants); ?>)
                        </button>
                    </li>
                <?php endif; ?>
                <?php if (!empty($destGuides)): ?>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="guides-tab" data-bs-toggle="pill" data-bs-target="#guides" type="button" role="tab">
                            <i class="bi-person-badge me-1"></i>Guides (<?php echo count($destGuides); ?>)
                        </button>
                    </li>
                <?php endif; ?>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="weather-tab" data-bs-toggle="pill" data-bs-target="#weather" type="button" role="tab">
                        <i class="bi-map me-1"></i>Map
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="reviews-tab" data-bs-toggle="pill" data-bs-target="#reviews" type="button" role="tab">
                        <i class="bi-chat-left-text me-1"></i>Reviews (<?php echo count($reviews); ?>)
                    </button>
                </li>
            </ul>

            <!-- Tabs Content -->
            <div class="tab-content bg-white p-4 rounded-3 shadow-sm" id="detailsTabContent">
                <!-- 1. Overview Tab -->
                <div class="tab-pane fade show active" id="overview" role="tabpanel" aria-labelledby="overview-tab">
                    <h4 class="fw-bold mb-3">About <?php echo htmlspecialchars($destination['name']); ?></h4>
                    <p class="text-muted" style="white-space: pre-line;"><?php echo htmlspecialchars($destination['description']); ?></p>
                    
                    <?php if (!empty($destination['nearby_attractions'])): ?>
                        <div class="mt-4">
                            <h5 class="fw-bold mb-3"><i class="bi-compass text-warning me-2"></i>Must-See Attractions</h5>
                            <div class="row g-2">
                                <?php 
                                $attractions = explode(',', $destination['nearby_attractions']);
                                foreach ($attractions as $attr): 
                                ?>
                                    <div class="col-sm-6">
                                        <div class="d-flex align-items-center gap-2 p-3 border rounded-3 attraction-badge-item">
                                            <i class="bi-check-circle-fill text-success fs-5"></i>
                                            <span class="fw-bold attraction-text"><?php echo htmlspecialchars(trim($attr)); ?></span>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- 2. Attractions Tab (Beside Overview) -->
                <div class="tab-pane fade" id="attractions" role="tabpanel" aria-labelledby="attractions-tab">
                    <h4 class="fw-bold mb-3"><i class="bi-compass text-warning me-2"></i>Attractions & Popular Spots</h4>

                    <?php if (!empty($nearbyPlaces)): ?>
                        <div class="row g-3">
                            <?php foreach ($nearbyPlaces as $np): ?>
                                <div class="col-md-6">
                                    <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden">
                                        <div class="position-relative" style="height: 160px;">
                                            <img src="<?php echo htmlspecialchars(get_image_url($np['image'])); ?>" class="w-100 h-100" style="object-fit: cover;" alt="<?php echo htmlspecialchars($np['name']); ?>" onerror="this.onerror=null; this.src='assets/images/default_destination.jpg';">
                                            <span class="position-absolute top-0 end-0 m-2 badge bg-dark text-warning border border-warning px-3 py-1 rounded-pill small shadow-sm">
                                                <i class="bi-geo-alt-fill me-1 text-danger"></i><?php echo number_format($np['distance_km'], 1); ?> km away
                                            </span>
                                        </div>
                                        <div class="card-body p-3 d-flex flex-column justify-content-between">
                                            <div>
                                                <h6 class="fw-bold mb-2"><?php echo htmlspecialchars($np['name']); ?></h6>
                                                <p class="small text-muted mb-3" style="line-height: 1.5;"><?php echo htmlspecialchars($np['description']); ?></p>
                                            </div>
                                            <div class="pt-2 border-top d-flex justify-content-between align-items-center">
                                                <span class="small text-muted"><i class="bi-compass text-warning me-1"></i>Attraction</span>
                                                <a href="https://www.google.com/maps/search/?api=1&query=<?php echo urlencode($np['name'] . ', ' . $destination['name']); ?>" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline-primary rounded-pill px-3 py-1 fw-bold">
                                                    <i class="bi-map-fill me-1 text-danger"></i>View on Map
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php elseif (empty($destination['nearby_attractions'])): ?>
                        <p class="text-muted">No attractions listed yet for this destination.</p>
                    <?php endif; ?>
                </div>

                <!-- Hotels Tab -->
                <?php if (!empty($destHotels)): ?>
                    <div class="tab-pane fade" id="hotels" role="tabpanel">
                        <h4 class="fw-bold mb-3"><i class="bi-building text-primary me-2"></i>Recommended Hotels in <?php echo htmlspecialchars($destination['name']); ?></h4>
                        <div class="d-flex flex-column gap-3">
                            <?php foreach ($destHotels as $hotel): ?>
                                <div class="card border p-3 rounded-3 shadow-sm">
                                    <div class="row g-3 align-items-center">
                                        <div class="col-md-4">
                                            <img src="<?php echo htmlspecialchars(get_image_url($hotel['image'])); ?>" class="w-100 rounded-3" style="height: 120px; object-fit: cover;" alt="<?php echo htmlspecialchars($hotel['name']); ?>" onerror="this.onerror=null; this.src='assets/images/default_destination.jpg';">
                                        </div>
                                        <div class="col-md-5">
                                            <h6 class="fw-bold mb-1 text-dark"><?php echo htmlspecialchars($hotel['name']); ?></h6>
                                            <div class="text-warning small mb-1">
                                                <?php for ($s=0; $s<$hotel['star_rating']; $s++): ?><i class="bi-star-fill me-1"></i><?php endfor; ?>
                                                <span class="text-muted ms-1">(<?php echo $hotel['star_rating']; ?> Star Hotel)</span>
                                            </div>
                                            <div class="small text-muted text-truncate"><?php echo htmlspecialchars($hotel['amenities']); ?></div>
                                            <div class="small text-primary mt-1"><i class="bi-telephone me-1"></i><?php echo htmlspecialchars($hotel['contact']); ?></div>
                                        </div>
                                        <div class="col-md-3 text-md-end border-start-md pt-2 pt-md-0">
                                            <div class="text-muted small">Price per night</div>
                                            <div class="fs-5 fw-bold text-success">₹<?php echo number_format($hotel['price_per_night'], 2); ?></div>
                                            <a href="hotel-detail.php?id=<?php echo $hotel['id']; ?>" class="btn btn-sm btn-primary rounded-pill mt-2 px-3">Book Hotel</a>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Dining Tab -->
                <?php if (!empty($destRestaurants)): ?>
                    <div class="tab-pane fade" id="restaurants" role="tabpanel">
                        <h4 class="fw-bold mb-3"><i class="bi-cup-hot text-danger me-2"></i>Top Restaurants & Cafes</h4>
                        <div class="row g-3">
                            <?php foreach ($destRestaurants as $r): ?>
                                <div class="col-md-6">
                                    <div class="card h-100 border p-3 rounded-3 shadow-sm">
                                        <div class="d-flex gap-3 align-items-center">
                                            <img src="<?php echo htmlspecialchars(get_image_url($r['image'])); ?>" class="rounded-3" style="width: 80px; height: 80px; object-fit: cover;" alt="<?php echo htmlspecialchars($r['name']); ?>" onerror="this.onerror=null; this.src='assets/images/default_destination.jpg';">
                                            <div>
                                                <h6 class="fw-bold mb-1"><?php echo htmlspecialchars($r['name']); ?></h6>
                                                <div class="badge bg-warning text-dark small mb-1"><i class="bi-star-fill me-1"></i><?php echo number_format($r['rating'], 1); ?></div>
                                                <div class="small text-muted"><?php echo htmlspecialchars($r['cuisine_type']); ?> &bull; <span class="text-dark fw-bold"><?php echo htmlspecialchars($r['price_range']); ?></span></div>
                                                <div class="small text-muted mt-1"><i class="bi-telephone me-1"></i><?php echo htmlspecialchars($r['contact']); ?></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Guides Tab -->
                <?php if (!empty($destGuides)): ?>
                    <div class="tab-pane fade" id="guides" role="tabpanel">
                        <h4 class="fw-bold mb-3"><i class="bi-person-badge text-warning me-2"></i>Certified Tour Guides</h4>
                        <div class="row g-3">
                            <?php foreach ($destGuides as $guide): ?>
                                <div class="col-md-6">
                                    <div class="card h-100 border p-3 rounded-3 shadow-sm">
                                        <div class="d-flex gap-3 align-items-center">
                                            <img src="<?php echo htmlspecialchars(get_image_url($guide['photo'])); ?>" class="rounded-circle" style="width: 70px; height: 70px; object-fit: cover;" alt="<?php echo htmlspecialchars($guide['name']); ?>" onerror="this.onerror=null; this.src='assets/images/default_destination.jpg';">
                                            <div class="flex-grow-1">
                                                <h6 class="fw-bold mb-1"><?php echo htmlspecialchars($guide['name']); ?></h6>
                                                <div class="small text-muted mb-1"><i class="bi-translate me-1"></i><?php echo htmlspecialchars($guide['languages']); ?></div>
                                                <div class="small text-muted"><i class="bi-briefcase me-1"></i><?php echo $guide['experience_years']; ?> Years Exp. &bull; ⭐ <?php echo number_format($guide['rating'], 1); ?></div>
                                                <div class="fw-bold text-success small mt-1">₹<?php echo number_format($guide['price_per_day'], 2); ?> / day</div>
                                            </div>
                                            <div>
                                                <a href="checkout.php?type=guide&id=<?php echo $guide['id']; ?>" class="btn btn-sm btn-outline-warning text-dark font-weight-bold rounded-pill">Book</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- 2. Weather & Map Tab -->
                <div class="tab-pane fade" id="weather" role="tabpanel" aria-labelledby="weather-tab">
                    <h4 class="fw-bold mb-3">Weather Information</h4>
                    <p class="text-muted">The best season for travel to <?php echo htmlspecialchars($destination['name']); ?> is during **<?php echo htmlspecialchars($destination['best_time_to_visit']); ?>** when temperatures are pleasant and sight-seeing is optimal.</p>
                    
                    <?php if (!empty($destination['latitude']) && !empty($destination['longitude'])): ?>
                        <h5 class="fw-bold mt-4 mb-3"><i class="bi-geo-fill text-danger me-2"></i>Location Map</h5>
                        <div class="ratio ratio-21x9 rounded overflow-hidden shadow-sm border">
                            <iframe 
                                src="https://maps.google.com/maps?q=<?php echo $destination['latitude']; ?>,<?php echo $destination['longitude']; ?>&t=&z=13&ie=UTF8&iwloc=&output=embed" 
                                style="border:0;" 
                                allowfullscreen="" 
                                loading="lazy">
                            </iframe>
                        </div>
                        <div class="text-muted small mt-2">
                            Coordinates: Latitude <?php echo $destination['latitude']; ?>, Longitude <?php echo $destination['longitude']; ?>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info mt-4">
                            <i class="bi-info-circle me-2"></i>Map coordinates are not configured for this destination.
                        </div>
                    <?php endif; ?>
                </div>

                <!-- 3. Reviews Tab -->
                <div class="tab-pane fade" id="reviews" role="tabpanel" aria-labelledby="reviews-tab">
                    <!-- Write/Submit Review Form -->
                    <div class="mb-5 border-bottom pb-4">
                        <h4 class="fw-bold mb-3">Submit Your Review</h4>
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <form action="destination-detail.php?id=<?php echo $dest_id; ?>" method="POST" class="needs-validation" novalidate>
                                <input type="hidden" name="action" value="add_review">
                                
                                <div class="mb-3">
                                    <label class="form-label fw-semibold text-muted d-block">Your Rating</label>
                                    <div class="interactive-stars fs-4 d-inline-flex gap-2">
                                        <i class="bi-star text-muted" data-rating="1" style="cursor:pointer;"></i>
                                        <i class="bi-star text-muted" data-rating="2" style="cursor:pointer;"></i>
                                        <i class="bi-star text-muted" data-rating="3" style="cursor:pointer;"></i>
                                        <i class="bi-star text-muted" data-rating="4" style="cursor:pointer;"></i>
                                        <i class="bi-star text-muted" data-rating="5" style="cursor:pointer;"></i>
                                    </div>
                                    <input type="hidden" name="rating" id="rating_input" value="0" required>
                                    <div class="invalid-feedback">Please select a rating of 1 to 5 stars.</div>
                                </div>

                                <div class="mb-3">
                                    <label for="comment" class="form-label fw-semibold text-muted">Comments</label>
                                    <textarea name="comment" id="comment" rows="3" class="form-control" placeholder="Share details of your experience visiting this place..." required></textarea>
                                    <div class="invalid-feedback">Please write a comment.</div>
                                </div>

                                <button type="submit" class="btn btn-warning rounded-pill px-4 text-dark fw-bold">Submit Review</button>
                            </form>
                        <?php else: ?>
                            <div class="alert bg-light border text-center p-4">
                                <p class="mb-2"><i class="bi-lock-fill text-muted me-2"></i>You must be logged in to leave a review.</p>
                                <a href="login.php" class="btn btn-primary btn-sm rounded-pill px-4">Log In</a>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Reviews List -->
                    <h4 class="fw-bold mb-4">User Feedback</h4>
                    <?php if (!empty($reviews)): ?>
                        <div class="reviews-list">
                            <?php foreach ($reviews as $rev): ?>
                                <div class="review-item border">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div>
                                            <h6 class="fw-bold mb-1"><?php echo htmlspecialchars($rev['user_name']); ?></h6>
                                            <span class="review-stars small">
                                                <?php for ($i=1; $i<=5; $i++): ?>
                                                    <i class="bi-star-fill <?php echo $i <= $rev['rating'] ? 'text-warning' : 'text-black-50'; ?>"></i>
                                                <?php endfor; ?>
                                            </span>
                                        </div>
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="text-muted small"><?php echo date('M d, Y', strtotime($rev['created_at'])); ?></span>
                                            <?php if (isset($_SESSION['user_id']) && ($_SESSION['user_id'] == $rev['user_id'] || $_SESSION['user_role'] === 'admin')): ?>
                                                <form action="destination-detail.php?id=<?php echo $dest_id; ?>" method="POST" onsubmit="return confirm('Are you sure you want to delete this review?');" class="d-inline">
                                                    <input type="hidden" name="action" value="delete_review">
                                                    <input type="hidden" name="review_id" value="<?php echo $rev['id']; ?>">
                                                    <button type="submit" class="btn btn-link text-danger p-0 border-0 text-decoration-none small"><i class="bi-trash-fill"></i></button>
                                                </form>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <p class="text-muted small mb-0"><?php echo htmlspecialchars($rev['comment']); ?></p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-muted">No reviews yet for this destination. Be the first to share your experience!</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Right Side Planner Sticky Card (col-lg-4) -->
        <div class="col-lg-4">
            <div class="sticky-planner-card">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <!-- Wishlist toggle form -->
                    <form action="destination-detail.php?id=<?php echo $dest_id; ?>" method="POST" class="mb-4">
                        <input type="hidden" name="action" value="toggle_wishlist">
                        <?php if ($isWishlisted): ?>
                            <button type="submit" class="btn btn-outline-danger w-100 py-3 rounded-pill fw-bold">
                                <i class="bi-heart-break-fill me-2"></i>Remove from Wishlist
                            </button>
                        <?php else: ?>
                            <button type="submit" class="btn btn-warning w-100 py-3 rounded-pill text-dark fw-bold shadow-sm">
                                <i class="bi-heart-fill me-2 text-danger"></i>Add to Wishlist
                            </button>
                        <?php endif; ?>
                    </form>

                    <!-- Itinerary Planning Form -->
                    <div class="border-top pt-4">
                        <h5 class="fw-bold mb-3"><i class="bi-calendar-week text-primary me-2"></i>Plan Your Itinerary</h5>
                        <p class="text-muted small">Choose your travel date and add activities to build a custom travel plan.</p>
                        
                        <form action="destination-detail.php?id=<?php echo $dest_id; ?>" method="POST" class="needs-validation" novalidate>
                            <input type="hidden" name="action" value="create_itinerary">
                            
                            <div class="mb-3">
                                <label for="travel_date" class="form-label small fw-bold text-muted">Travel Date</label>
                                <input type="date" name="travel_date" id="travel_date" class="form-control" required min="<?php echo date('Y-m-d'); ?>">
                                <div class="invalid-feedback">Please select a valid future date.</div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="notes" class="form-label small fw-bold text-muted">Activities & Notes</label>
                                <textarea name="notes" id="notes" rows="4" class="form-control small" placeholder="e.g. Day 1: Visit Eiffel Tower&#10;Day 2: Louvre Museum and cafes..."></textarea>
                            </div>
                            
                            <button type="submit" class="btn btn-primary w-100 py-3 rounded-pill fw-bold shadow-sm">
                                Add to Travel Itinerary <i class="bi-plus-lg ms-1"></i>
                            </button>
                        </form>
                    </div>
                <?php else: ?>
                    <div class="text-center py-4">
                        <i class="bi-geo-alt display-4 text-muted mb-3"></i>
                        <h5 class="fw-bold">Ready to plan?</h5>
                        <p class="text-muted small">Log in to save this destination to your wishlist and schedule a trip itinerary.</p>
                        <a href="login.php" class="btn btn-warning text-dark font-weight-bold rounded-pill px-4 mt-2">Log In to Plan</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
