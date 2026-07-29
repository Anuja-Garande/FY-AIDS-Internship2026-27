<?php
// C:\xampp\htdocs\NewProject\admin\bookings.php
// Admin Booking Management Page

require_once '../config/db_connect.php';
require_once 'includes/admin_header.php';

// 1. Handle Status Update POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    $booking_id = isset($_POST['booking_id']) ? intval($_POST['booking_id']) : 0;
    $new_status = isset($_POST['status']) ? trim($_POST['status']) : 'pending';

    if (in_array($new_status, ['pending', 'confirmed', 'cancelled']) && $booking_id > 0) {
        try {
            // Get user_id and booking number
            $stmt = $pdo->prepare("SELECT user_id, booking_number, booking_type FROM bookings WHERE id = ?");
            $stmt->execute([$booking_id]);
            $bInfo = $stmt->fetch();

            if ($bInfo) {
                $upd = $pdo->prepare("UPDATE bookings SET status = ? WHERE id = ?");
                $upd->execute([$new_status, $booking_id]);

                // Create user notification
                $msg = "Your " . ucfirst($bInfo['booking_type']) . " booking #" . $bInfo['booking_number'] . " status has been updated to: " . strtoupper($new_status);
                create_notification($pdo, $bInfo['user_id'], $msg, 'booking');

                $_SESSION['admin_success'] = "Booking status updated to " . ucfirst($new_status);
            }
        } catch (\PDOException $e) {
            $_SESSION['admin_error'] = "Status update failed: " . $e->getMessage();
        }
    }
    header("Location: bookings.php");
    exit;
}

// 2. Fetch Bookings List
$status_filter = isset($_GET['status']) ? trim($_GET['status']) : '';
$sql = "SELECT b.*, u.name AS user_name, u.email AS user_email FROM bookings b JOIN users u ON b.user_id = u.id WHERE 1=1";
$params = [];

if ($status_filter !== '') {
    $sql .= " AND b.status = ?";
    $params[] = $status_filter;
}

$sql .= " ORDER BY b.created_at DESC";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $bookings = $stmt->fetchAll();
} catch (\PDOException $e) {
    $bookings = [];
}
?>

<div class="card border-0 shadow-sm rounded-3 bg-white p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0 text-dark"><i class="bi-ticket-perforated text-warning me-2"></i>All User Bookings</h4>
        
        <!-- Filter Form -->
        <form action="bookings.php" method="GET" class="d-flex gap-2">
            <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="">All Statuses</option>
                <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                <option value="confirmed" <?php echo $status_filter === 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                <option value="cancelled" <?php echo $status_filter === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
            </select>
        </form>
    </div>

    <?php if (!empty($bookings)): ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle small">
                <thead class="table-light">
                    <tr>
                        <th>Booking #</th>
                        <th>User</th>
                        <th>Type</th>
                        <th>Dates</th>
                        <th>Guests</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($bookings as $bk): ?>
                        <tr>
                            <td><strong class="font-monospace text-primary"><?php echo htmlspecialchars($bk['booking_number']); ?></strong></td>
                            <td>
                                <strong class="text-dark d-block"><?php echo htmlspecialchars($bk['user_name']); ?></strong>
                                <span class="text-muted small"><?php echo htmlspecialchars($bk['user_email']); ?></span>
                            </td>
                            <td><span class="badge bg-secondary text-uppercase"><?php echo htmlspecialchars($bk['booking_type']); ?></span></td>
                            <td>
                                <span class="d-block">In: <?php echo date('M d, Y', strtotime($bk['check_in'])); ?></span>
                                <span class="text-muted">Out: <?php echo date('M d, Y', strtotime($bk['check_out'])); ?></span>
                            </td>
                            <td><?php echo $bk['guests']; ?> Guests</td>
                            <td><strong class="text-success fs-6">₹<?php echo number_format($bk['total_price'], 2); ?></strong></td>
                            <td>
                                <?php 
                                $badgeClass = $bk['status'] === 'confirmed' ? 'bg-success' : ($bk['status'] === 'cancelled' ? 'bg-danger' : 'bg-warning text-dark');
                                ?>
                                <span class="badge <?php echo $badgeClass; ?>"><?php echo ucfirst($bk['status']); ?></span>
                            </td>
                            <td>
                                <form action="bookings.php" method="POST" class="d-inline-flex gap-1">
                                    <input type="hidden" name="action" value="update_status">
                                    <input type="hidden" name="booking_id" value="<?php echo $bk['id']; ?>">
                                    <select name="status" class="form-select form-select-sm" style="width: 110px;" onchange="this.form.submit()">
                                        <option value="pending" <?php echo $bk['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                        <option value="confirmed" <?php echo $bk['status'] === 'confirmed' ? 'selected' : ''; ?>>Confirm</option>
                                        <option value="cancelled" <?php echo $bk['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancel</option>
                                    </select>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <p class="text-muted text-center py-4 mb-0">No bookings match the selected status.</p>
    <?php endif; ?>
</div>

<?php require_once 'includes/admin_footer.php'; ?>
