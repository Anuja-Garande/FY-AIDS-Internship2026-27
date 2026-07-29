<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT d.*, s.name AS state_name, s.state_id FROM destinations d
                        JOIN states s ON s.state_id = d.state_id WHERE d.destination_id = ?");
$stmt->execute([$id]);
$dest = $stmt->fetch();
if (!$dest) { redirect('/tourism-portal/destinations.php'); }

$pageTitle = $dest['name'];

// Handle review submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_review'])) {
    requireLogin();
    $rating = (int)$_POST['rating'];
    $text   = trim($_POST['review_text']);
    if ($rating >= 1 && $rating <= 5 && $text !== '') {
        $ins = $pdo->prepare("INSERT INTO reviews (user_id, item_type, item_id, rating, review_text) VALUES (?, 'destination', ?, ?, ?)");
        $ins->execute([$_SESSION['user_id'], $id, $rating, $text]);
        setFlash('success', 'Thank you! Your review has been posted.');
    } else {
        setFlash('danger', 'Please provide a rating and review text.');
    }
    redirect('/tourism-portal/destination-details.php?id=' . $id . '#reviews');
}

// Handle wishlist add
if (isset($_GET['wishlist']) && $_GET['wishlist'] === 'add') {
    requireLogin();
    $ins = $pdo->prepare("INSERT IGNORE INTO wishlist (user_id, item_type, item_id) VALUES (?, 'destination', ?)");
    $ins->execute([$_SESSION['user_id'], $id]);
    setFlash('success', 'Added to your wishlist.');
    redirect('/tourism-portal/destination-details.php?id=' . $id);
}

$hotels = $pdo->prepare("SELECT * FROM hotels WHERE destination_id = ?");
$hotels->execute([$id]);
$hotels = $hotels->fetchAll();

$restaurants = $pdo->prepare("SELECT * FROM restaurants WHERE destination_id = ?");
$restaurants->execute([$id]);
$restaurants = $restaurants->fetchAll();

$reviews = $pdo->prepare("SELECT r.*, u.full_name FROM reviews r
                           JOIN users u ON u.user_id = r.user_id
                           WHERE r.item_type = 'destination' AND r.item_id = ? AND r.status = 'Approved'
                           ORDER BY r.created_at DESC");
$reviews->execute([$id]);
$reviews = $reviews->fetchAll();

$avg = getAverageRating($pdo, 'destination', $id);

// Photo gallery
$gallery = $pdo->prepare("SELECT * FROM destination_images WHERE destination_id = ? ORDER BY image_id ASC");
$gallery->execute([$id]);
$gallery = $gallery->fetchAll();

// Track this view (Trending Now + Recently Viewed)
trackDestinationView($pdo, $id);
$trending = getTrendingDestinations($pdo, 4);
$recentlyViewed = getRecentlyViewed($pdo, $id, 4);

// Google Maps embed (no API key required)
$mapQuery = urlencode($dest['location'] ?: ($dest['name'] . ', ' . $dest['state_name'] . ', India'));
$mapEmbedUrl = "https://maps.google.com/maps?q={$mapQuery}&output=embed";

require __DIR__ . '/includes/header.php';
?>

<section class="section pb-0">
  <div class="container">
    <p class="breadcrumb-custom text-muted mb-3" data-aos="fade-up">
      <a href="/tourism-portal/index.php" class="text-muted">Home</a> /
      <a href="/tourism-portal/destinations.php" class="text-muted">Destinations</a> /
      <span class="text-dark"><?= h($dest['name']) ?></span>
    </p>
    <div class="detail-hero" data-aos="fade-up">
      <img src="/tourism-portal/assets/images/destinations/<?= h($dest['image']) ?>" alt="<?= h($dest['name']) ?>"
           onerror="this.src='https://source.unsplash.com/1200x600/?<?= urlencode($dest['name']) ?>,india'">
      <div class="detail-hero-overlay">
        <div>
          <span class="tile-category"><?= h($dest['category']) ?></span>
          <h1 class="text-white mt-2 mb-1"><?= h($dest['name']) ?></h1>
          <p class="text-white mb-0"><i class="bi bi-geo-alt-fill"></i> <?= h($dest['location']) ?>, <?= h($dest['state_name']) ?></p>
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
          <div class="tile-rating fs-5"><?= renderStars($avg['avg_rating'] ?? 0) ?> <span class="text-muted fs-6">(<?= $avg['total'] ?? 0 ?> reviews)</span></div>
          <a href="?id=<?= $id ?>&wishlist=add" class="btn-icon"><i class="bi bi-heart"></i></a>
        </div>
        <h3 data-aos="fade-up">About <?= h($dest['name']) ?></h3>
        <p class="text-muted" data-aos="fade-up"><?= nl2br(h($dest['description'])) ?></p>
        <div data-aos="fade-up">
          <span class="info-chip"><i class="bi bi-tag-fill"></i> <?= h($dest['category']) ?></span>
          <span class="info-chip"><i class="bi bi-map-fill"></i> <?= h($dest['state_name']) ?></span>
          <span class="info-chip"><i class="bi bi-fire"></i> Popularity <?= $dest['popularity'] ?>%</span>
          <span class="info-chip"><i class="bi bi-eye-fill"></i> <?= number_format($dest['views'] + 1) ?> views</span>
        </div>

        <!-- Photo Gallery -->
        <?php if ($gallery): ?>
        <h4 class="mt-5" data-aos="fade-up"><i class="bi bi-images"></i> Photo Gallery</h4>
        <div class="row g-3" data-aos="fade-up">
          <?php foreach ($gallery as $g): ?>
          <div class="col-4 col-md-3">
            <a href="/tourism-portal/assets/images/destinations/<?= h($g['image']) ?>" data-bs-toggle="modal" data-bs-target="#galleryModal<?= $g['image_id'] ?>">
              <img src="/tourism-portal/assets/images/destinations/<?= h($g['image']) ?>" class="rounded-3 w-100"
                   style="height:90px;object-fit:cover;" alt="<?= h($g['caption'] ?: $dest['name']) ?>"
                   onerror="this.src='https://source.unsplash.com/300x200/?<?= urlencode($dest['name']) ?>'">
            </a>
          </div>
          <!-- Lightbox modal -->
          <div class="modal fade" id="galleryModal<?= $g['image_id'] ?>" tabindex="-1">
            <div class="modal-dialog modal-lg modal-dialog-centered">
              <div class="modal-content bg-transparent border-0">
                <img src="/tourism-portal/assets/images/destinations/<?= h($g['image']) ?>" class="w-100 rounded-3"
                     onerror="this.src='https://source.unsplash.com/900x600/?<?= urlencode($dest['name']) ?>'">
                <?php if ($g['caption']): ?><p class="text-white text-center mt-2"><?= h($g['caption']) ?></p><?php endif; ?>
              </div>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- Map -->
        <h4 class="mt-5" data-aos="fade-up"><i class="bi bi-geo-alt-fill"></i> Location on Map</h4>
        <div class="rounded-4 overflow-hidden" style="height:320px;" data-aos="fade-up">
          <iframe src="<?= h($mapEmbedUrl) ?>" width="100%" height="100%" style="border:0;" loading="lazy"></iframe>
        </div>

        <!-- Hotels -->
        <?php if ($hotels): ?>
        <h4 class="mt-5" data-aos="fade-up"><i class="bi bi-building"></i> Hotels Nearby</h4>
        <div class="row g-3">
          <?php foreach ($hotels as $h): ?>
          <div class="col-md-6" data-aos="fade-up">
            <div class="card-tile p-3">
              <h6><?= h($h['name']) ?></h6>
              <p class="small text-muted mb-1"><i class="bi bi-currency-rupee"></i> <?= h($h['price_range']) ?></p>
              <p class="small text-muted mb-1"><i class="bi bi-check2-circle"></i> <?= h($h['amenities']) ?></p>
              <p class="small text-muted mb-0"><i class="bi bi-telephone"></i> <?= h($h['contact']) ?></p>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- Restaurants -->
        <?php if ($restaurants): ?>
        <h4 class="mt-5" data-aos="fade-up"><i class="bi bi-cup-hot"></i> Restaurants Nearby</h4>
        <div class="row g-3">
          <?php foreach ($restaurants as $r2): ?>
          <div class="col-md-6" data-aos="fade-up">
            <div class="card-tile p-3">
              <h6><?= h($r2['name']) ?></h6>
              <p class="small text-muted mb-1"><i class="bi bi-egg-fried"></i> <?= h($r2['cuisine_type']) ?></p>
              <p class="small text-muted mb-1"><i class="bi bi-currency-rupee"></i> <?= h($r2['price_range']) ?></p>
              <p class="small text-muted mb-0"><i class="bi bi-telephone"></i> <?= h($r2['contact']) ?></p>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- Reviews -->
        <div id="reviews" class="mt-5">
          <h4 data-aos="fade-up"><i class="bi bi-chat-square-text"></i> Traveller Reviews</h4>
          <?php if (isLoggedIn()): ?>
          <form method="post" class="card-tile p-4 mb-4" data-aos="fade-up">
            <label class="form-label">Your Rating</label>
            <select name="rating" class="form-select mb-3" style="max-width:150px" required>
              <option value="">Select</option>
              <option value="5">5 - Excellent</option>
              <option value="4">4 - Very Good</option>
              <option value="3">3 - Average</option>
              <option value="2">2 - Poor</option>
              <option value="1">1 - Terrible</option>
            </select>
            <label class="form-label">Your Review</label>
            <textarea name="review_text" class="form-control mb-3" rows="3" placeholder="Share your experience..." required></textarea>
            <button type="submit" name="submit_review" class="btn-brand">Post Review</button>
          </form>
          <?php else: ?>
          <p class="text-muted" data-aos="fade-up"><a href="/tourism-portal/login.php">Login</a> to post a review.</p>
          <?php endif; ?>

          <?php if (empty($reviews)): ?>
            <p class="text-muted">No reviews yet. Be the first to share your experience!</p>
          <?php endif; ?>
          <?php foreach ($reviews as $rev): ?>
          <div class="review-item d-flex gap-3">
            <div class="review-avatar"><?= strtoupper(substr($rev['full_name'],0,1)) ?></div>
            <div>
              <h6 class="mb-1"><?= h($rev['full_name']) ?> <span class="tile-rating small"><?= renderStars($rev['rating']) ?></span></h6>
              <p class="text-muted small mb-1"><?= date('d M Y', strtotime($rev['created_at'])) ?></p>
              <p class="mb-0"><?= nl2br(h($rev['review_text'])) ?></p>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>

      <div class="col-lg-4">
        <div class="card-tile p-4 mb-4" data-aos="fade-up">
          <h5 class="mb-3">Plan a Trip Here</h5>
          <p class="text-muted small">Explore curated tour packages that include <?= h($dest['name']) ?> and nearby attractions.</p>
          <a href="/tourism-portal/packages.php?state=<?= $dest['state_id'] ?>" class="btn-brand w-100 text-center d-block">View Related Packages</a>
        </div>

        <?php if ($trending): ?>
        <div class="card-tile p-4 mb-4" data-aos="fade-up">
          <h6 class="mb-3"><i class="bi bi-fire text-danger"></i> Trending Now</h6>
          <?php foreach ($trending as $t): ?>
          <a href="/tourism-portal/destination-details.php?id=<?= $t['destination_id'] ?>" class="d-flex gap-3 align-items-center mb-3 text-decoration-none">
            <img src="/tourism-portal/assets/images/destinations/<?= h($t['image']) ?>" style="width:56px;height:56px;object-fit:cover;border-radius:10px;"
                 onerror="this.src='https://source.unsplash.com/100x100/?<?= urlencode($t['name']) ?>'">
            <div>
              <p class="mb-0 text-dark fw-medium small"><?= h($t['name']) ?></p>
              <p class="mb-0 text-muted" style="font-size:0.75rem;"><?= h($t['state_name']) ?> · <?= number_format($t['views']) ?> views</p>
            </div>
          </a>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <?php if ($recentlyViewed): ?>
        <div class="card-tile p-4" data-aos="fade-up">
          <h6 class="mb-3"><i class="bi bi-clock-history"></i> Recently Viewed</h6>
          <?php foreach ($recentlyViewed as $rv): ?>
          <a href="/tourism-portal/destination-details.php?id=<?= $rv['destination_id'] ?>" class="d-flex gap-3 align-items-center mb-3 text-decoration-none">
            <img src="/tourism-portal/assets/images/destinations/<?= h($rv['image']) ?>" style="width:56px;height:56px;object-fit:cover;border-radius:10px;"
                 onerror="this.src='https://source.unsplash.com/100x100/?<?= urlencode($rv['name']) ?>'">
            <div>
              <p class="mb-0 text-dark fw-medium small"><?= h($rv['name']) ?></p>
              <p class="mb-0 text-muted" style="font-size:0.75rem;"><?= h($rv['state_name']) ?></p>
            </div>
          </a>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
