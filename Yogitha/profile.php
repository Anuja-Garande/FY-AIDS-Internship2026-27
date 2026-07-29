<?php
// C:\xampp\htdocs\NewProject\profile.php
// User Profile & Trip Planner Dashboard

require_once 'config/db_connect.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Session Guard
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "You must be logged in to view your profile.";
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// 1. Process Actions (Delete Wishlist, Itinerary, Review)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = isset($_POST['action']) ? trim($_POST['action']) : '';

    if ($action === 'delete_wishlist') {
        $wishlist_id = isset($_POST['wishlist_id']) ? intval($_POST['wishlist_id']) : 0;
        try {
            $stmt = $pdo->prepare("DELETE FROM wishlist WHERE id = ? AND user_id = ?");
            $stmt->execute([$wishlist_id, $user_id]);
            $_SESSION['success'] = "Destination removed from wishlist.";
        } catch (\PDOException $e) {
            $_SESSION['error'] = "Error removing wishlist item: " . $e->getMessage();
        }
        header("Location: profile.php");
        exit;
    }

    if ($action === 'delete_itinerary') {
        $itinerary_id = isset($_POST['itinerary_id']) ? intval($_POST['itinerary_id']) : 0;
        try {
            $stmt = $pdo->prepare("DELETE FROM itineraries WHERE id = ? AND user_id = ?");
            $stmt->execute([$itinerary_id, $user_id]);
            $_SESSION['success'] = "Trip itinerary deleted.";
        } catch (\PDOException $e) {
            $_SESSION['error'] = "Error deleting itinerary: " . $e->getMessage();
        }
        header("Location: profile.php");
        exit;
    }

    if ($action === 'delete_review') {
        $review_id = isset($_POST['review_id']) ? intval($_POST['review_id']) : 0;
        $dest_id = isset($_POST['destination_id']) ? intval($_POST['destination_id']) : 0;
        try {
            $stmt = $pdo->prepare("DELETE FROM reviews WHERE id = ? AND user_id = ?");
            $stmt->execute([$review_id, $user_id]);
            
            // Recalculate average rating of the destination
            $updRating = $pdo->prepare("UPDATE destinations SET rating = (SELECT IFNULL(AVG(rating), 0) FROM reviews WHERE destination_id = ?) WHERE id = ?");
            $updRating->execute([$dest_id, $dest_id]);

            $_SESSION['success'] = "Review deleted successfully.";
        } catch (\PDOException $e) {
            $_SESSION['error'] = "Error deleting review: " . $e->getMessage();
        }
        header("Location: profile.php");
        exit;
    }

    if ($action === 'cancel_booking') {
        $booking_id = isset($_POST['booking_id']) ? intval($_POST['booking_id']) : 0;
        try {
            $stmt = $pdo->prepare("UPDATE bookings SET status = 'cancelled' WHERE id = ? AND user_id = ?");
            $stmt->execute([$booking_id, $user_id]);
            
            create_notification($pdo, $user_id, "Booking has been cancelled as requested.", "booking");
            $_SESSION['success'] = "Booking status updated to cancelled.";
        } catch (\PDOException $e) {
            $_SESSION['error'] = "Error cancelling booking: " . $e->getMessage();
        }
        header("Location: profile.php");
        exit;
    }
}

// 2. Fetch User Profile Data
try {
    // User details
    $userStmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $userStmt->execute([$user_id]);
    $profileUser = $userStmt->fetch();

    if (!$profileUser) {
        // Destroy session if user no longer exists
        session_destroy();
        header("Location: login.php");
        exit;
    }

    // User Bookings
    $bStmt = $pdo->prepare("SELECT * FROM bookings WHERE user_id = ? ORDER BY created_at DESC");
    $bStmt->execute([$user_id]);
    $userBookings = $bStmt->fetchAll();

    // Wishlist items
    $wStmt = $pdo->prepare("SELECT w.id AS wishlist_id, d.*, c.name AS category_name FROM wishlist w 
                            JOIN destinations d ON w.destination_id = d.id 
                            LEFT JOIN categories c ON d.category_id = c.id 
                            WHERE w.user_id = ?");
    $wStmt->execute([$user_id]);
    $wishlistItems = $wStmt->fetchAll();

    // Itinerary items
    $iStmt = $pdo->prepare("SELECT i.id AS itinerary_id, i.travel_date, i.notes, d.name AS destination_name, d.id AS destination_id, d.image FROM itineraries i 
                            JOIN destinations d ON i.destination_id = d.id 
                            WHERE i.user_id = ? 
                            ORDER BY i.travel_date ASC");
    $iStmt->execute([$user_id]);
    $itineraries = $iStmt->fetchAll();

    // Reviews items
    $rStmt = $pdo->prepare("SELECT r.id AS review_id, r.rating, r.comment, r.created_at, d.name AS destination_name, d.id AS destination_id FROM reviews r 
                            JOIN destinations d ON r.destination_id = d.id 
                            WHERE r.user_id = ? 
                            ORDER BY r.created_at DESC");
    $rStmt->execute([$user_id]);
    $reviews = $rStmt->fetchAll();

} catch (\PDOException $e) {
    die("Database fetch error: " . $e->getMessage());
}

require_once 'includes/header.php';
?>

<div class="container py-5">
    <div class="row">
        <!-- Left Side Profile Summary Card (col-md-4) -->
        <div class="col-md-4 mb-4">
            <div class="card border-0 shadow-sm rounded-3 bg-white p-4 text-center">
                <div class="position-relative d-inline-block mx-auto mb-3">
                    <i class="bi-person-circle text-primary" style="font-size: 5rem;"></i>
                    <span class="position-absolute bottom-0 end-0 badge rounded-pill bg-success">Active</span>
                </div>
                <h4 class="fw-bold mb-1"><?php echo htmlspecialchars($profileUser['name']); ?></h4>
                <p class="text-muted small mb-3"><?php echo htmlspecialchars($profileUser['email']); ?></p>
                <div class="badge bg-primary-subtle text-primary rounded-pill px-3 py-2 mb-4">
                    Member Since: <?php echo date('M Y', strtotime($profileUser['created_at'])); ?>
                </div>
                
                <div class="border-top pt-3 text-start small">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Role</span>
                        <strong class="text-dark"><?php echo ucfirst($profileUser['role']); ?></strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Wishlist Items</span>
                        <strong class="text-dark"><?php echo count($wishlistItems); ?></strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Itineraries Scheduled</span>
                        <strong class="text-dark"><?php echo count($itineraries); ?></strong>
                    </div>
                </div>
                
                <?php if ($profileUser['role'] === 'admin'): ?>
                    <a href="admin/index.php" class="btn btn-outline-primary btn-sm w-100 mt-4 rounded-pill">
                        <i class="bi-speedometer2 me-1"></i>Go to Admin Panel
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Right Side Tabs Panel (col-md-8) -->
        <div class="col-md-8">
            <div class="card border-0 shadow-sm rounded-3 bg-white p-4">
                <!-- Navigation Tabs -->
                <ul class="nav nav-tabs border-bottom mb-4 flex-wrap" id="profileTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active fw-bold" id="bookings-tab" data-bs-toggle="tab" data-bs-target="#user-bookings" type="button" role="tab">
                            <i class="bi-ticket-perforated me-1"></i>My Bookings (<?php echo count($userBookings); ?>)
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link fw-bold" id="wishlist-tab" data-bs-toggle="tab" data-bs-target="#wishlist" type="button" role="tab">
                            <i class="bi-heart me-1"></i>Wishlist (<?php echo count($wishlistItems); ?>)
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link fw-bold" id="itineraries-tab" data-bs-toggle="tab" data-bs-target="#itineraries-list" type="button" role="tab">
                            <i class="bi-calendar-check me-1"></i>Itineraries (<?php echo count($itineraries); ?>)
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link fw-bold" id="user-reviews-tab" data-bs-toggle="tab" data-bs-target="#user-reviews" type="button" role="tab">
                            <i class="bi-chat-left-quote me-1"></i>My Reviews (<?php echo count($reviews); ?>)
                        </button>
                    </li>
                </ul>

                <!-- Tabs Content -->
                <div class="tab-content" id="profileTabsContent">
                    
                    <!-- Tab 0: My Bookings -->
                    <div class="tab-pane fade show active" id="user-bookings" role="tabpanel">
                        <?php if (!empty($userBookings)): ?>
                            <div class="d-flex flex-column gap-3">
                                <?php foreach ($userBookings as $bk): ?>
                                    <div class="card border p-3 rounded-3 shadow-sm">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <div>
                                                <span class="badge bg-primary text-uppercase me-2"><?php echo htmlspecialchars($bk['booking_type']); ?></span>
                                                <strong class="font-monospace text-dark fs-6"><?php echo htmlspecialchars($bk['booking_number']); ?></strong>
                                            </div>
                                            <div>
                                                <?php 
                                                $statusClass = $bk['status'] === 'confirmed' ? 'bg-success' : ($bk['status'] === 'cancelled' ? 'bg-danger' : 'bg-warning text-dark');
                                                ?>
                                                <span class="badge <?php echo $statusClass; ?> rounded-pill px-3 py-1"><?php echo ucfirst($bk['status']); ?></span>
                                            </div>
                                        </div>
                                        
                                        <div class="row g-2 text-muted small my-2">
                                            <div class="col-sm-6"><i class="bi-calendar-event me-1"></i>Check-In: <strong><?php echo date('M d, Y', strtotime($bk['check_in'])); ?></strong></div>
                                            <div class="col-sm-6"><i class="bi-calendar-event me-1"></i>Check-Out: <strong><?php echo date('M d, Y', strtotime($bk['check_out'])); ?></strong></div>
                                            <div class="col-sm-6"><i class="bi-people me-1"></i>Guests: <strong><?php echo $bk['guests']; ?></strong></div>
                                            <div class="col-sm-6"><i class="bi-wallet2 me-1"></i>Total: <strong class="text-success fs-6">₹<?php echo number_format($bk['total_price'], 2); ?></strong></div>
                                        </div>

                                        <div class="d-flex justify-content-between align-items-center pt-2 border-top mt-2">
                                            <a href="booking-confirmation.php?booking_number=<?php echo urlencode($bk['booking_number']); ?>" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                                View Receipt <i class="bi-receipt ms-1"></i>
                                            </a>
                                            <?php if ($bk['status'] !== 'cancelled'): ?>
                                                <form action="profile.php" method="POST" onsubmit="return confirm('Are you sure you want to cancel this booking?');" class="d-inline">
                                                    <input type="hidden" name="action" value="cancel_booking">
                                                    <input type="hidden" name="booking_id" value="<?php echo $bk['id']; ?>">
                                                    <button type="submit" class="btn btn-sm btn-link text-danger p-0 text-decoration-none">
                                                        <i class="bi-x-circle me-1"></i>Cancel Booking
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-5">
                                <i class="bi-ticket-perforated text-muted display-4"></i>
                                <p class="text-muted mt-2">No bookings found yet. Explore packages, hotels, or guides to get started!</p>
                                <a href="packages.php" class="btn btn-primary btn-sm rounded-pill px-4 mt-2">Browse Packages</a>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Tab 1: Wishlist -->
                    <div class="tab-pane fade" id="wishlist" role="tabpanel" aria-labelledby="wishlist-tab">
                        <?php if (!empty($wishlistItems)): ?>
                            <div class="row g-3">
                                <?php foreach ($wishlistItems as $item): ?>
                                    <div class="col-md-6">
                                        <div class="card h-100 border">
                                            <img src="<?php echo htmlspecialchars(get_image_url($item['image'])); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($item['name']); ?>" style="height: 140px; object-fit: cover;" onerror="this.onerror=null; this.src='assets/images/default_destination.jpg';">
                                            <div class="card-body p-3">
                                                <div class="d-flex justify-content-between align-items-start mb-1">
                                                    <h6 class="fw-bold mb-0"><?php echo htmlspecialchars($item['name']); ?></h6>
                                                    <span class="badge bg-success-subtle text-success small">
                                                        <i class="bi-star-fill me-1"></i><?php echo number_format($item['rating'], 1); ?>
                                                    </span>
                                                </div>
                                                <div class="small text-muted mb-2"><i class="bi-geo-alt me-1"></i><?php echo htmlspecialchars($item['country']); ?></div>
                                                
                                                <div class="d-flex justify-content-between align-items-center mt-3 pt-2 border-top">
                                                    <a href="destination-detail.php?id=<?php echo $item['id']; ?>" class="btn btn-sm btn-outline-primary rounded-pill px-3">Plan Trip</a>
                                                    
                                                    <form action="profile.php" method="POST" onsubmit="return confirm('Remove from wishlist?');">
                                                        <input type="hidden" name="action" value="delete_wishlist">
                                                        <input type="hidden" name="wishlist_id" value="<?php echo $item['wishlist_id']; ?>">
                                                        <button type="submit" class="btn btn-sm btn-link text-danger p-0 text-decoration-none">
                                                            <i class="bi-trash3 me-1"></i>Remove
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-5">
                                <i class="bi-heartbreak text-muted display-4"></i>
                                <p class="text-muted mt-2">Your wishlist is empty. Start exploring and save destinations!</p>
                                <a href="destinations.php" class="btn btn-primary btn-sm rounded-pill px-4 mt-2">Explore Now</a>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Tab 2: My Itineraries -->
                    <div class="tab-pane fade" id="itineraries-list" role="tabpanel" aria-labelledby="itineraries-tab">
                        <?php if (!empty($itineraries)): ?>
                            <div class="d-flex flex-column gap-3">
                                <?php foreach ($itineraries as $it): ?>
                                    <div class="card border">
                                        <div class="row g-0">
                                            <div class="col-md-3">
                                                <img src="<?php echo htmlspecialchars(get_image_url($it['image'])); ?>" class="w-100 h-100" style="object-fit: cover; min-height: 120px;" alt="<?php echo htmlspecialchars($it['destination_name']); ?>" onerror="this.onerror=null; this.src='assets/images/default_destination.jpg';">
                                            </div>
                                            <div class="col-md-9 p-3">
                                                <div class="d-flex justify-content-between align-items-start mb-2">
                                                    <div>
                                                        <h5 class="fw-bold mb-1"><?php echo htmlspecialchars($it['destination_name']); ?></h5>
                                                        <span class="badge bg-warning text-dark font-weight-bold"><i class="bi-calendar-check me-1"></i><?php echo date('M d, Y', strtotime($it['travel_date'])); ?></span>
                                                    </div>
                                                    
                                                    <form action="profile.php" method="POST" onsubmit="return confirm('Are you sure you want to delete this trip itinerary?');">
                                                        <input type="hidden" name="action" value="delete_itinerary">
                                                        <input type="hidden" name="itinerary_id" value="<?php echo $it['itinerary_id']; ?>">
                                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete Trip"><i class="bi-trash"></i></button>
                                                    </form>
                                                </div>
                                                
                                                <?php if (!empty($it['notes'])): ?>
                                                    <div class="bg-light p-2 rounded text-muted small mt-2" style="white-space: pre-line;">
                                                        <strong>Planned Activities:</strong><br>
                                                        <?php echo htmlspecialchars($it['notes']); ?>
                                                    </div>
                                                <?php endif; ?>
                                                
                                                <div class="text-end mt-2">
                                                    <a href="destination-detail.php?id=<?php echo $it['destination_id']; ?>" class="btn btn-sm btn-link text-primary text-decoration-none">View Destination Details <i class="bi-chevron-right small"></i></a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-5">
                                <i class="bi-calendar-x text-muted display-4"></i>
                                <p class="text-muted mt-2">No itineraries scheduled yet. Plan a trip from destination detail page.</p>
                                <a href="destinations.php" class="btn btn-primary btn-sm rounded-pill px-4 mt-2">Browse Destinations</a>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Tab 3: My Reviews -->
                    <div class="tab-pane fade" id="user-reviews" role="tabpanel" aria-labelledby="user-reviews-tab">
                        <?php if (!empty($reviews)): ?>
                            <div class="d-flex flex-column gap-3">
                                <?php foreach ($reviews as $r): ?>
                                    <div class="p-3 border rounded shadow-sm">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <div>
                                                <h6 class="fw-bold mb-1"><?php echo htmlspecialchars($r['destination_name']); ?></h6>
                                                <div class="review-stars small">
                                                    <?php for ($i=1; $i<=5; $i++): ?>
                                                        <i class="bi-star-fill <?php echo $i <= $r['rating'] ? 'text-warning' : 'text-black-50'; ?>"></i>
                                                    <?php endfor; ?>
                                                </div>
                                            </div>
                                            <div class="d-flex align-items-center gap-2">
                                                <span class="text-muted small"><?php echo date('M d, Y', strtotime($r['created_at'])); ?></span>
                                                <form action="profile.php" method="POST" onsubmit="return confirm('Delete this review?');" class="d-inline">
                                                    <input type="hidden" name="action" value="delete_review">
                                                    <input type="hidden" name="review_id" value="<?php echo $r['review_id']; ?>">
                                                    <input type="hidden" name="destination_id" value="<?php echo $r['destination_id']; ?>">
                                                    <button type="submit" class="btn btn-link text-danger p-0 border-0 text-decoration-none small"><i class="bi-trash-fill"></i></button>
                                                </form>
                                            </div>
                                        </div>
                                        <p class="text-muted small mb-0"><?php echo htmlspecialchars($r['comment']); ?></p>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-5">
                                <i class="bi-chat-left-text text-muted display-4"></i>
                                <p class="text-muted mt-2">You haven't posted any reviews yet.</p>
                            </div>
                        <?php endif; ?>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
