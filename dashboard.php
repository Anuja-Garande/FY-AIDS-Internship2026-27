<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';
requireLogin();
$pageTitle = 'My Dashboard';

$uid = $_SESSION['user_id'];

$user = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
$user->execute([$uid]);
$user = $user->fetch();

$bookingCount = $pdo->prepare("SELECT COUNT(*) FROM bookings WHERE user_id = ?");
$bookingCount->execute([$uid]);
$bookingCount = $bookingCount->fetchColumn();

$wishlistCount = $pdo->prepare("SELECT COUNT(*) FROM wishlist WHERE user_id = ?");
$wishlistCount->execute([$uid]);
$wishlistCount = $wishlistCount->fetchColumn();

$reviewCount = $pdo->prepare("SELECT COUNT(*) FROM reviews WHERE user_id = ?");
$reviewCount->execute([$uid]);
$reviewCount = $reviewCount->fetchColumn();

$recentBookings = $pdo->prepare("SELECT b.*, p.name AS package_name FROM bookings b
                                  JOIN packages p ON p.package_id = b.package_id
                                  WHERE b.user_id = ? ORDER BY b.created_at DESC LIMIT 5");
$recentBookings->execute([$uid]);
$recentBookings = $recentBookings->fetchAll();

require __DIR__ . '/includes/header.php';
?>

<section class="section" style="padding-top:50px;">
  <div class="container">
    <div class="row g-4">
      <div class="col-lg-3">
        <div class="dash-sidebar" data-aos="fade-up">
          <div class="text-center mb-4">
            <div class="review-avatar mx-auto mb-2" style="width:64px;height:64px;font-size:1.5rem;"><?= strtoupper(substr($user['full_name'],0,1)) ?></div>
            <h6 class="mb-0"><?= h($user['full_name']) ?></h6>
            <p class="text-muted small mb-0"><?= h($user['email']) ?></p>
          </div>
          <a href="/tourism-portal/dashboard.php" class="active"><i class="bi bi-speedometer2"></i> Dashboard</a>
          <a href="/tourism-portal/my-bookings.php"><i class="bi bi-suitcase-lg"></i> My Bookings</a>
          <a href="/tourism-portal/wishlist.php"><i class="bi bi-heart"></i> My Wishlist</a>
          <a href="/tourism-portal/logout.php" class="text-danger"><i class="bi bi-box-arrow-right"></i> Logout</a>
        </div>
      </div>
      <div class="col-lg-9">
        <h3 class="mb-4" data-aos="fade-up">Welcome back, <?= h(explode(' ', $user['full_name'])[0]) ?> 👋</h3>
        <div class="row g-4 mb-4">
          <div class="col-md-4" data-aos="fade-up" data-aos-delay="0">
            <div class="stat-card"><div class="num"><?= $bookingCount ?></div><p class="text-muted mb-0">Total Bookings</p></div>
          </div>
          <div class="col-md-4" data-aos="fade-up" data-aos-delay="100">
            <div class="stat-card"><div class="num"><?= $wishlistCount ?></div><p class="text-muted mb-0">Wishlist Items</p></div>
          </div>
          <div class="col-md-4" data-aos="fade-up" data-aos-delay="200">
            <div class="stat-card"><div class="num"><?= $reviewCount ?></div><p class="text-muted mb-0">Reviews Posted</p></div>
          </div>
        </div>

        <div class="card-tile p-4" data-aos="fade-up">
          <h5 class="mb-3">Recent Bookings</h5>
          <?php if (empty($recentBookings)): ?>
            <p class="text-muted mb-0">No bookings yet. <a href="/tourism-portal/packages.php">Browse tour packages</a> to get started.</p>
          <?php else: ?>
          <div class="table-responsive">
            <table class="table table-admin align-middle">
              <thead><tr><th>Reference</th><th>Package</th><th>Travel Date</th><th>Travellers</th><th>Status</th></tr></thead>
              <tbody>
                <?php foreach ($recentBookings as $b): ?>
                <tr>
                  <td><?= h($b['booking_reference']) ?></td>
                  <td><?= h($b['package_name']) ?></td>
                  <td><?= date('d M Y', strtotime($b['travel_date'])) ?></td>
                  <td><?= $b['travellers'] ?></td>
                  <td><span class="badge-status badge-<?= strtolower($b['status']) ?>"><?= h($b['status']) ?></span></td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
          <a href="/tourism-portal/my-bookings.php" class="tile-link">View all bookings <i class="bi bi-arrow-right"></i></a>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
