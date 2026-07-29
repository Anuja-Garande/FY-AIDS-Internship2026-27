<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT p.*, s.name AS state_name FROM packages p LEFT JOIN states s ON s.state_id = p.state_id WHERE p.package_id = ?");
$stmt->execute([$id]);
$pkg = $stmt->fetch();
if (!$pkg) { redirect('/tourism-portal/packages.php'); }
$pageTitle = $pkg['name'];

// Handle booking submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['book_now'])) {
    requireLogin();
    $travelDate = $_POST['travel_date'] ?? '';
    $travellers = max(1, (int)($_POST['travellers'] ?? 1));

    if ($travelDate === '' || strtotime($travelDate) < strtotime('today')) {
        setFlash('danger', 'Please select a valid future travel date.');
        redirect('/tourism-portal/package-details.php?id=' . $id);
    }

    $total = $pkg['price'] * $travellers;
    $ref = generateBookingReference();
    $ins = $pdo->prepare("INSERT INTO bookings (user_id, package_id, travel_date, travellers, total_cost, booking_reference)
                           VALUES (?, ?, ?, ?, ?, ?)");
    $ins->execute([$_SESSION['user_id'], $id, $travelDate, $travellers, $total, $ref]);
    setFlash('success', "Booking request submitted! Your reference number is $ref. Our team will confirm it shortly.");
    redirect('/tourism-portal/my-bookings.php');
}

// Handle wishlist add
if (isset($_GET['wishlist']) && $_GET['wishlist'] === 'add') {
    requireLogin();
    $ins = $pdo->prepare("INSERT IGNORE INTO wishlist (user_id, item_type, item_id) VALUES (?, 'package', ?)");
    $ins->execute([$_SESSION['user_id'], $id]);
    setFlash('success', 'Added to your wishlist.');
    redirect('/tourism-portal/package-details.php?id=' . $id);
}

$itineraryDays = array_map('trim', explode('|', $pkg['itinerary']));

require __DIR__ . '/includes/header.php';
?>

<section class="section pb-0">
  <div class="container">
    <p class="breadcrumb-custom text-muted mb-3" data-aos="fade-up">
      <a href="/tourism-portal/index.php" class="text-muted">Home</a> /
      <a href="/tourism-portal/packages.php" class="text-muted">Packages</a> /
      <span class="text-dark"><?= h($pkg['name']) ?></span>
    </p>
    <div class="detail-hero" data-aos="fade-up">
      <img src="/tourism-portal/assets/images/destinations/<?= h($pkg['image']) ?>" alt="<?= h($pkg['name']) ?>"
           onerror="this.src='https://source.unsplash.com/1200x600/?<?= urlencode($pkg['state_name'] ?? 'india') ?>,travel'">
      <div class="detail-hero-overlay">
        <div>
          <span class="tile-category"><?= h($pkg['duration']) ?></span>
          <h1 class="text-white mt-2 mb-1"><?= h($pkg['name']) ?></h1>
          <p class="text-white mb-0"><i class="bi bi-geo-alt-fill"></i> <?= h($pkg['state_name'] ?? 'Multi-state') ?></p>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="section">
  <div class="container">
    <div class="row g-5">
      <div class="col-lg-8">
        <div class="d-flex justify-content-between align-items-center mb-3" data-aos="fade-up">
          <h3 class="mb-0">Itinerary</h3>
          <a href="?id=<?= $id ?>&wishlist=add" class="btn-icon"><i class="bi bi-heart"></i></a>
        </div>
        <div class="mb-4" data-aos="fade-up">
          <?php foreach ($itineraryDays as $i => $day): ?>
            <div class="d-flex gap-3 mb-3">
              <div class="review-avatar"><?= $i + 1 ?></div>
              <p class="mb-0 pt-2"><?= h($day) ?></p>
            </div>
          <?php endforeach; ?>
        </div>
        <div data-aos="fade-up">
          <span class="info-chip"><i class="bi bi-clock"></i> <?= h($pkg['duration']) ?></span>
          <span class="info-chip"><i class="bi bi-geo"></i> <?= h($pkg['state_name'] ?? 'Multi-state') ?></span>
          <span class="info-chip"><i class="bi bi-currency-rupee"></i> <?= number_format($pkg['price'],0) ?> / person</span>
        </div>
      </div>

      <div class="col-lg-4">
        <div class="card-tile p-4" data-aos="fade-up">
          <h4 class="mb-1">₹<?= number_format($pkg['price'], 0) ?> <small class="text-muted fs-6">/ person</small></h4>
          <p class="text-muted small mb-4">Fill in your travel details to request a booking.</p>

          <?php if (isLoggedIn()): ?>
          <form method="post">
            <div class="mb-3">
              <label class="form-label">Travel Date</label>
              <input type="date" name="travel_date" class="form-control" min="<?= date('Y-m-d', strtotime('+1 day')) ?>" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Number of Travellers</label>
              <input type="number" name="travellers" class="form-control" value="1" min="1" max="20" required>
            </div>
            <button type="submit" name="book_now" class="btn-brand w-100">Book Now</button>
          </form>
          <?php else: ?>
            <a href="/tourism-portal/login.php" class="btn-brand w-100 text-center d-block">Login to Book</a>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
