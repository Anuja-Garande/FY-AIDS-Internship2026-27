<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';
requireLogin();
$pageTitle = 'My Bookings';
$uid = $_SESSION['user_id'];

// Allow user to cancel a pending booking
if (isset($_GET['cancel'])) {
    $bid = (int)$_GET['cancel'];
    $upd = $pdo->prepare("UPDATE bookings SET status = 'Cancelled' WHERE booking_id = ? AND user_id = ? AND status = 'Pending'");
    $upd->execute([$bid, $uid]);
    setFlash('success', 'Booking cancelled.');
    redirect('/tourism-portal/my-bookings.php');
}

$bookings = $pdo->prepare("SELECT b.*, p.name AS package_name, p.duration FROM bookings b
                            JOIN packages p ON p.package_id = b.package_id
                            WHERE b.user_id = ? ORDER BY b.created_at DESC");
$bookings->execute([$uid]);
$bookings = $bookings->fetchAll();

require __DIR__ . '/includes/header.php';
?>

<section class="section" style="padding-top:50px;">
  <div class="container">
    <div class="row g-4">
      <div class="col-lg-3">
        <div class="dash-sidebar" data-aos="fade-up">
          <a href="/tourism-portal/dashboard.php"><i class="bi bi-speedometer2"></i> Dashboard</a>
          <a href="/tourism-portal/my-bookings.php" class="active"><i class="bi bi-suitcase-lg"></i> My Bookings</a>
          <a href="/tourism-portal/wishlist.php"><i class="bi bi-heart"></i> My Wishlist</a>
          <a href="/tourism-portal/logout.php" class="text-danger"><i class="bi bi-box-arrow-right"></i> Logout</a>
        </div>
      </div>
      <div class="col-lg-9">
        <h3 class="mb-4" data-aos="fade-up">My Bookings</h3>
        <?php if (empty($bookings)): ?>
          <div class="card-tile p-5 text-center" data-aos="fade-up">
            <i class="bi bi-suitcase" style="font-size:2.5rem;color:var(--text-muted);"></i>
            <h5 class="mt-3">No bookings yet</h5>
            <p class="text-muted">Start planning your next trip across India.</p>
            <a href="/tourism-portal/packages.php" class="btn-brand mx-auto">Browse Packages</a>
          </div>
        <?php else: ?>
        <div class="card-tile p-4" data-aos="fade-up">
          <div class="table-responsive">
            <table class="table table-admin align-middle">
              <thead><tr><th>Reference</th><th>Package</th><th>Duration</th><th>Travel Date</th><th>Travellers</th><th>Total</th><th>Status</th><th></th></tr></thead>
              <tbody>
                <?php foreach ($bookings as $b): ?>
                <tr>
                  <td><?= h($b['booking_reference']) ?></td>
                  <td><?= h($b['package_name']) ?></td>
                  <td><?= h($b['duration']) ?></td>
                  <td><?= date('d M Y', strtotime($b['travel_date'])) ?></td>
                  <td><?= $b['travellers'] ?></td>
                  <td>₹<?= number_format($b['total_cost'],0) ?></td>
                  <td><span class="badge-status badge-<?= strtolower($b['status']) ?>"><?= h($b['status']) ?></span></td>
                  <td>
                    <?php if ($b['status'] === 'Pending'): ?>
                    <a href="?cancel=<?= $b['booking_id'] ?>" class="text-danger small" onclick="return confirm('Cancel this booking?')">Cancel</a>
                    <?php endif; ?>
                  </td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
