<?php
// C:\xampp\htdocs\NewProject\admin\index.php
// Admin Dashboard Stats

require_once '../config/db_connect.php';
require_once 'includes/admin_header.php';

try {
    // Fetch stats
    $destCount    = $pdo->query("SELECT COUNT(*) FROM destinations")->fetchColumn();
    $bookingCount = $pdo->query("SELECT COUNT(*) FROM bookings")->fetchColumn();
    $packageCount = $pdo->query("SELECT COUNT(*) FROM packages")->fetchColumn();
    $hotelCount   = $pdo->query("SELECT COUNT(*) FROM hotels")->fetchColumn();
    $guideCount   = $pdo->query("SELECT COUNT(*) FROM guides")->fetchColumn();
    $userCount    = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();

    // Fetch 5 recent bookings
    $recentBookings = $pdo->query("SELECT b.*, u.name AS user_name FROM bookings b JOIN users u ON b.user_id = u.id ORDER BY b.created_at DESC LIMIT 5")->fetchAll();

    // Fetch 5 recent reviews
    $recentReviews = $pdo->query("SELECT r.*, u.name AS user_name, d.name AS dest_name FROM reviews r 
                                  JOIN users u ON r.user_id = u.id 
                                  JOIN destinations d ON r.destination_id = d.id 
                                  ORDER BY r.created_at DESC LIMIT 5")->fetchAll();

    // Fetch 5 recent contact messages
    $recentMessages = $pdo->query("SELECT * FROM contact_messages ORDER BY created_at DESC LIMIT 5")->fetchAll();

} catch (\PDOException $e) {
    die("Database stats fetch error: " . $e->getMessage());
}
?>

<div class="row g-4">
    <!-- Stat 1: Bookings -->
    <div class="col-md-4 col-lg-3 col-sm-6">
        <a href="bookings.php" class="text-decoration-none">
            <div class="card admin-card-stat admin-card-1 p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="small text-white-50 text-uppercase fw-bold">Bookings</div>
                        <h2 class="fw-bold mb-0"><?php echo $bookingCount; ?></h2>
                    </div>
                    <i class="bi-ticket-perforated-fill fs-1 opacity-50"></i>
                </div>
            </div>
        </a>
    </div>

    <!-- Stat 2: Destinations -->
    <div class="col-md-4 col-lg-3 col-sm-6">
        <a href="destinations.php" class="text-decoration-none">
            <div class="card admin-card-stat admin-card-2 p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="small text-white-50 text-uppercase fw-bold">Destinations</div>
                        <h2 class="fw-bold mb-0"><?php echo $destCount; ?></h2>
                    </div>
                    <i class="bi-geo-alt-fill fs-1 opacity-50"></i>
                </div>
            </div>
        </a>
    </div>

    <!-- Stat 3: Packages -->
    <div class="col-md-4 col-lg-3 col-sm-6">
        <a href="packages.php" class="text-decoration-none">
            <div class="card admin-card-stat admin-card-3 p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="small text-white-50 text-uppercase fw-bold">Tour Packages</div>
                        <h2 class="fw-bold mb-0"><?php echo $packageCount; ?></h2>
                    </div>
                    <i class="bi-box-seam-fill fs-1 opacity-50"></i>
                </div>
            </div>
        </a>
    </div>

    <!-- Stat 4: Hotels -->
    <div class="col-md-4 col-lg-3 col-sm-6">
        <a href="hotels.php" class="text-decoration-none">
            <div class="card admin-card-stat admin-card-4 p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="small text-white-50 text-uppercase fw-bold">Hotels</div>
                        <h2 class="fw-bold mb-0"><?php echo $hotelCount; ?></h2>
                    </div>
                    <i class="bi-building fs-1 opacity-50"></i>
                </div>
            </div>
        </a>
    </div>
</div>

<div class="row mt-5 g-4">
    <!-- Recent Reviews List -->
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm rounded-3 bg-white p-4">
            <h5 class="fw-bold text-dark mb-4 border-bottom pb-2">
                <i class="bi-chat-left-quote text-warning me-2"></i>Recent Reviews
            </h5>
            <?php if (!empty($recentReviews)): ?>
                <div class="table-responsive">
                    <table class="table table-hover table-striped align-middle small">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Destination</th>
                                <th>Rating</th>
                                <th>Comment</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentReviews as $rev): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($rev['user_name']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($rev['dest_name']); ?></td>
                                    <td>
                                        <span class="text-warning text-nowrap">
                                            <?php for ($i=1; $i<=5; $i++): ?>
                                                <i class="bi-star-fill <?php echo $i <= $rev['rating'] ? '' : 'text-black-50'; ?>"></i>
                                            <?php endfor; ?>
                                        </span>
                                    </td>
                                    <td class="text-truncate" style="max-width: 150px;" title="<?php echo htmlspecialchars($rev['comment']); ?>">
                                        <?php echo htmlspecialchars($rev['comment']); ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="text-end">
                    <a href="reviews.php" class="small fw-bold">Moderate Reviews <i class="bi-chevron-right small"></i></a>
                </div>
            <?php else: ?>
                <p class="text-muted small">No reviews submitted yet.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Recent Contact Messages -->
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm rounded-3 bg-white p-4">
            <h5 class="fw-bold text-dark mb-4 border-bottom pb-2">
                <i class="bi-envelope-paper text-primary me-2"></i>Recent Contact Submissions
            </h5>
            <?php if (!empty($recentMessages)): ?>
                <div class="d-flex flex-column gap-3">
                    <?php foreach ($recentMessages as $msg): ?>
                        <div class="p-3 bg-light rounded border">
                            <div class="d-flex justify-content-between mb-1">
                                <strong class="text-dark small"><?php echo htmlspecialchars($msg['name']); ?></strong>
                                <span class="text-muted small"><?php echo date('M d, Y', strtotime($msg['created_at'])); ?></span>
                            </div>
                            <div class="text-muted small mb-2"><?php echo htmlspecialchars($msg['email']); ?></div>
                            <p class="mb-0 text-muted small text-truncate" title="<?php echo htmlspecialchars($msg['message']); ?>">
                                <?php echo htmlspecialchars($msg['message']); ?>
                            </p>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="text-end mt-3">
                    <a href="messages.php" class="small fw-bold">View Inbox <i class="bi-chevron-right small"></i></a>
                </div>
            <?php else: ?>
                <p class="text-muted small">No message submissions in the database.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once 'includes/admin_footer.php'; ?>
