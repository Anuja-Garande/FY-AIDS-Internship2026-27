<?php
session_start();
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

if (!isAdminLoggedIn()) {
    redirect('login.php');
}

$db = Database::getInstance();

$search = $_GET['search'] ?? '';
$status_filter = $_GET['booking_status'] ?? '';
$payment_filter = $_GET['payment_status'] ?? '';
$page = max(1, intval($_GET['page'] ?? 1));
$per_page = 15;
$offset = ($page - 1) * $per_page;

$where = "1=1";
$params = [];

if (!empty($search)) {
    $where .= " AND (f.booking_ref LIKE :s1 OR f.passenger_name LIKE :s2 OR f.passenger_email LIKE :s3 OR f.from_city LIKE :s4 OR f.to_city LIKE :s5)";
    $params[':s1'] = "%$search%";
    $params[':s2'] = "%$search%";
    $params[':s3'] = "%$search%";
    $params[':s4'] = "%$search%";
    $params[':s5'] = "%$search%";
}
if ($status_filter !== '') {
    $where .= " AND f.booking_status = :bs";
    $params[':bs'] = $status_filter;
}
if ($payment_filter !== '') {
    $where .= " AND f.payment_status = :ps";
    $params[':ps'] = $payment_filter;
}

$total = $db->query("SELECT COUNT(*) as c FROM flight_bookings f WHERE $where", $params)->fetch()['c'];
$total_pages = max(1, ceil($total / $per_page));

$bookings = $db->query("SELECT f.*, u.name as user_name FROM flight_bookings f LEFT JOIN users u ON f.user_id = u.id WHERE $where ORDER BY f.id DESC LIMIT $per_page OFFSET $offset", $params)->fetchAll();

$pageTitle = 'Flight Bookings';
include __DIR__ . '/includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="fw-bold mb-0"><i class="fas fa-plane me-2 text-primary"></i>Flight Bookings (<?= number_format($total) ?>)</h5>
    <a href="<?= BASE_URL ?>/admin/flight_bookings.php" class="btn btn-sm btn-outline-secondary"><i class="fas fa-sync me-1"></i>Refresh</a>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-3">
                <input type="text" name="search" class="form-control" placeholder="Search ref, name, email, city..." value="<?= sanitize($search) ?>">
            </div>
            <div class="col-md-2">
                <select name="booking_status" class="form-select">
                    <option value="">All Booking Status</option>
                    <option value="confirmed" <?= $status_filter === 'confirmed' ? 'selected' : '' ?>>Confirmed</option>
                    <option value="on_hold" <?= $status_filter === 'on_hold' ? 'selected' : '' ?>>On Hold</option>
                    <option value="completed" <?= $status_filter === 'completed' ? 'selected' : '' ?>>Completed</option>
                    <option value="cancelled" <?= $status_filter === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                </select>
            </div>
            <div class="col-md-2">
                <select name="payment_status" class="form-select">
                    <option value="">All Payment Status</option>
                    <option value="pending" <?= $payment_filter === 'pending' ? 'selected' : '' ?>>Pending</option>
                    <option value="paid" <?= $payment_filter === 'paid' ? 'selected' : '' ?>>Paid</option>
                    <option value="failed" <?= $payment_filter === 'failed' ? 'selected' : '' ?>>Failed</option>
                    <option value="refunded" <?= $payment_filter === 'refunded' ? 'selected' : '' ?>>Refunded</option>
                </select>
            </div>
            <div class="col-md-2"><button type="submit" class="btn btn-primary w-100"><i class="fas fa-search me-1"></i>Filter</button></div>
            <div class="col-md-1"><a href="<?= BASE_URL ?>/admin/flight_bookings.php" class="btn btn-outline-secondary w-100">Clear</a></div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Booking Ref</th>
                        <th>Passenger</th>
                        <th>Route</th>
                        <th>Date</th>
                        <th>Class</th>
                        <th>Travelers</th>
                        <th>Amount</th>
                        <th>Payment</th>
                        <th>Status</th>
                        <th>Booked</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($bookings)): ?>
                    <tr><td colspan="11" class="text-center text-muted py-4"><i class="fas fa-plane-slash fa-2x mb-2 d-block opacity-25"></i>No flight bookings found</td></tr>
                    <?php endif; ?>
                    <?php foreach ($bookings as $b):
                        $class_badges = [
                            'economy' => 'primary',
                            'premium_economy' => 'info',
                            'business' => 'warning',
                            'first' => 'danger',
                        ];
                        $class_labels = [
                            'economy' => 'Economy',
                            'premium_economy' => 'Premium Economy',
                            'business' => 'Business',
                            'first' => 'First Class',
                        ];
                        $payment_colors = [
                            'pending' => 'warning',
                            'paid' => 'success',
                            'failed' => 'danger',
                            'refunded' => 'info',
                        ];
                        $booking_colors = [
                            'confirmed' => 'success',
                            'on_hold' => 'warning',
                            'completed' => 'primary',
                            'cancelled' => 'danger',
                        ];
                    ?>
                    <tr>
                        <td><code class="fw-bold" style="font-size:0.82rem;"><?= sanitize($b['booking_ref']) ?></code></td>
                        <td>
                            <div class="fw-semibold" style="font-size:0.85rem;"><?= sanitize($b['passenger_name']) ?></div>
                            <small class="text-muted"><?= sanitize($b['passenger_email']) ?></small>
                        </td>
                        <td>
                            <span class="fw-semibold"><?= sanitize($b['from_city']) ?></span>
                            <i class="fas fa-arrow-right mx-1 text-muted" style="font-size:0.7rem;"></i>
                            <span class="fw-semibold"><?= sanitize($b['to_city']) ?></span>
                            <?php if ($b['trip_type'] === 'roundtrip'): ?>
                                <br><small class="text-muted"><i class="fas fa-exchange-alt me-1"></i>Round Trip</small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div style="font-size:0.82rem;"><?= date('d M Y', strtotime($b['departure_date'])) ?></div>
                            <?php if ($b['return_date']): ?>
                                <small class="text-muted">to <?= date('d M Y', strtotime($b['return_date'])) ?></small>
                            <?php endif; ?>
                        </td>
                        <td><span class="badge bg-<?= $class_badges[$b['travel_class']] ?? 'secondary' ?>"><?= $class_labels[$b['travel_class']] ?? 'Economy' ?></span></td>
                        <td class="text-center fw-semibold"><?= $b['travelers'] ?></td>
                        <td class="fw-bold">₹<?= number_format($b['total_amount'], 0) ?></td>
                        <td><span class="badge bg-<?= $payment_colors[$b['payment_status']] ?? 'secondary' ?>"><?= ucfirst($b['payment_status']) ?></span></td>
                        <td><span class="badge bg-<?= $booking_colors[$b['booking_status']] ?? 'secondary' ?>"><?= ucfirst(str_replace('_',' ',$b['booking_status'])) ?></span></td>
                        <td><small class="text-muted"><?= date('d M y, h:i A', strtotime($b['created_at'])) ?></small></td>
                        <td class="text-nowrap">
                            <a href="<?= BASE_URL ?>/admin/view_flight_booking.php?ref=<?= urlencode($b['booking_ref']) ?>" class="btn btn-sm btn-outline-primary" title="View Details"><i class="fas fa-eye"></i></a>
                            <?php if ($b['booking_status'] === 'on_hold'): ?>
                            <form method="POST" action="<?= BASE_URL ?>/admin/view_flight_booking.php?ref=<?= urlencode($b['booking_ref']) ?>" class="d-inline" onsubmit="return confirm('Accept this booking? Ticket will be emailed automatically.')">
                                <input type="hidden" name="accept_booking" value="1">
                                <button type="submit" class="btn btn-sm btn-success" title="Accept & Send Ticket"><i class="fas fa-check"></i></button>
                            </form>
                            <form method="POST" action="<?= BASE_URL ?>/admin/view_flight_booking.php?ref=<?= urlencode($b['booking_ref']) ?>" class="d-inline" onsubmit="return confirm('Reject this booking? Customer will be notified.')">
                                <input type="hidden" name="reject_booking" value="1">
                                <button type="submit" class="btn btn-sm btn-danger" title="Reject Booking"><i class="fas fa-times"></i></button>
                            </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php if ($total_pages > 1): ?>
<nav class="mt-3"><ul class="pagination justify-content-center">
    <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
    <li class="page-item <?= $i === $page ? 'active' : '' ?>">
        <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&booking_status=<?= urlencode($status_filter) ?>&payment_status=<?= urlencode($payment_filter) ?>"><?= $i ?></a>
    </li>
    <?php endfor; ?>
</ul></nav>
<?php endif; ?>

<?php include __DIR__ . '/includes/footer.php'; ?>
