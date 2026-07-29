<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';
$pageTitle = 'Home';

$states = $pdo->query("SELECT * FROM states ORDER BY name ASC")->fetchAll();
$featuredDestinations = $pdo->query("SELECT d.*, s.name AS state_name FROM destinations d
                                      JOIN states s ON s.state_id = d.state_id
                                      ORDER BY popularity DESC LIMIT 8")->fetchAll();
$packages = $pdo->query("SELECT p.*, s.name AS state_name FROM packages p
                          LEFT JOIN states s ON s.state_id = p.state_id
                          ORDER BY package_id ASC LIMIT 6")->fetchAll();
$trending = getTrendingDestinations($pdo, 5);

require __DIR__ . '/includes/header.php';
?>

<!-- ===================== HERO ===================== -->
<section class="hero">
  <div class="container position-relative">
    <div class="row">
      <div class="col-lg-8">
        <span class="hero-eyebrow" data-aos="fade-up"><i class="bi bi-stars"></i> Incredible India Awaits</span>
        <h1 data-aos="fade-up" data-aos-delay="100">
          Discover the Soul of <br>
          <span class="rotating-word" data-words="Rajasthan, Kerala, Goa, Kashmir, Himachal"></span>
        </h1>
        <p class="lead" data-aos="fade-up" data-aos-delay="200">
          From royal deserts to backwaters, snow peaks to golden beaches — explore 10 states,
          plan tour packages, and book your next unforgettable journey across India.
        </p>

        <form class="hero-search" action="/tourism-portal/destinations.php" method="get" data-aos="fade-up" data-aos-delay="300">
          <input type="text" name="q" placeholder="Search destinations e.g. Munnar, Agra, Goa...">
          <button type="submit"><i class="bi bi-search"></i> Explore</button>
        </form>

        <div class="hero-stats" data-aos="fade-up" data-aos-delay="400">
          <div><div class="stat-num">10+</div><div class="stat-label">States</div></div>
          <div><div class="stat-num">40+</div><div class="stat-label">Destinations</div></div>
          <div><div class="stat-num">10</div><div class="stat-label">Tour Packages</div></div>
          <div><div class="stat-num">4.8/5</div><div class="stat-label">Traveller Rating</div></div>
        </div>
      </div>
    </div>
  </div>
  <div class="scroll-cue"><i class="bi bi-chevron-double-down"></i></div>
</section>
<div class="wave-divider"><svg viewBox="0 0 1200 70" preserveAspectRatio="none"><path class="wave-fill-surface" d="M0,40 C300,90 900,-10 1200,40 L1200,70 L0,70 Z"></path></svg></div>

<?php if ($trending): ?>
<!-- ===================== TRENDING STRIP ===================== -->
<section class="pb-0" style="padding-top:50px;">
  <div class="container">
    <div class="d-flex align-items-center gap-2 mb-3" data-aos="fade-up">
      <i class="bi bi-fire text-danger fs-5"></i>
      <h6 class="mb-0 text-uppercase" style="letter-spacing:1px;color:var(--secondary);">Trending Now</h6>
    </div>
    <div class="d-flex gap-3 overflow-auto pb-2" data-aos="fade-up">
      <?php foreach ($trending as $t): ?>
      <a href="/tourism-portal/destination-details.php?id=<?= $t['destination_id'] ?>"
         class="d-flex align-items-center gap-2 flex-shrink-0 text-decoration-none bg-white rounded-pill pe-3"
         style="box-shadow: var(--shadow-soft);">
        <img src="/tourism-portal/assets/images/destinations/<?= h($t['image']) ?>" style="width:44px;height:44px;object-fit:cover;border-radius:50%;"
             onerror="this.src='https://source.unsplash.com/100x100/?<?= urlencode($t['name']) ?>'">
        <span class="text-dark small fw-medium"><?= h($t['name']) ?></span>
        <span class="text-muted small">· <?= number_format($t['views']) ?> views</span>
      </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- ===================== STATES ===================== -->
<section class="section">
  <div class="container">
    <div class="text-center mb-5" data-aos="fade-up">
      <span class="section-eyebrow">Explore by State</span>
      <h2 class="section-title">10 States, Endless Stories</h2>
      <p class="section-sub mx-auto">Every state in India carries its own colour, cuisine and character. Pick a state to begin your journey.</p>
    </div>
    <div class="row g-4">
      <?php foreach ($states as $i => $state): ?>
      <div class="col-lg-3 col-md-4 col-6" data-aos="fade-up" data-aos-delay="<?= ($i % 4) * 100 ?>">
        <a href="/tourism-portal/destinations.php?state=<?= $state['state_id'] ?>" class="state-card d-block">
          <img src="/tourism-portal/assets/images/states/<?= h($state['image']) ?>" alt="<?= h($state['name']) ?>"
               onerror="this.src='https://source.unsplash.com/500x600/?<?= urlencode($state['name']) ?>,india'">
          <div class="state-card-overlay">
            <span class="state-tagline"><?= h($state['tagline']) ?></span>
            <h4><?= h($state['name']) ?></h4>
            <span class="state-meta"><i class="bi bi-geo-alt-fill"></i> Explore destinations</span>
          </div>
        </a>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ===================== FEATURED DESTINATIONS ===================== -->
<section class="section section-mist">
  <div class="container">
    <div class="d-flex justify-content-between align-items-end mb-5 flex-wrap gap-3" data-aos="fade-up">
      <div>
        <span class="section-eyebrow">Handpicked For You</span>
        <h2 class="section-title mb-0">Popular Destinations</h2>
      </div>
      <a href="/tourism-portal/destinations.php" class="btn-teal">View All Destinations <i class="bi bi-arrow-right"></i></a>
    </div>
    <div class="row g-4">
      <?php foreach ($featuredDestinations as $i => $d): $r = getAverageRating($pdo, 'destination', $d['destination_id']); ?>
      <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="<?= ($i % 4) * 100 ?>">
        <div class="card-tile">
          <div class="tile-img-wrap">
            <img src="/tourism-portal/assets/images/destinations/<?= h($d['image']) ?>" alt="<?= h($d['name']) ?>"
                 onerror="this.src='https://source.unsplash.com/500x400/?<?= urlencode($d['name']) ?>,india'">
            <span class="tile-category"><?= h($d['category']) ?></span>
            <button class="tile-fav"><i class="bi bi-heart"></i></button>
          </div>
          <div class="tile-body">
            <h5><?= h($d['name']) ?></h5>
            <div class="tile-location"><i class="bi bi-signpost-2"></i> <?= h($d['state_name']) ?></div>
            <div class="tile-rating"><?= renderStars($r['avg_rating'] ?? 0) ?> <span class="text-muted">(<?= $r['total'] ?? 0 ?>)</span></div>
            <div class="tile-footer">
              <span class="tile-price"><i class="bi bi-fire text-danger"></i> Popularity <?= $d['popularity'] ?>%</span>
              <a href="/tourism-portal/destination-details.php?id=<?= $d['destination_id'] ?>" class="tile-link">Explore <i class="bi bi-arrow-right"></i></a>
            </div>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ===================== WHY CHOOSE US ===================== -->
<section class="section">
  <div class="container">
    <div class="text-center mb-5" data-aos="fade-up">
      <span class="section-eyebrow">Why BharatYatra</span>
      <h2 class="section-title">Travel Planning, Simplified</h2>
    </div>
    <div class="row g-4 text-center">
      <div class="col-lg-3 col-md-6 feature-box" data-aos="fade-up" data-aos-delay="0">
        <div class="feature-icon mx-auto"><i class="bi bi-map"></i></div>
        <h5>Curated Destinations</h5>
        <p class="text-muted small">Handpicked places across 10 states with authentic details and photos.</p>
      </div>
      <div class="col-lg-3 col-md-6 feature-box" data-aos="fade-up" data-aos-delay="100">
        <div class="feature-icon mx-auto"><i class="bi bi-suitcase-lg"></i></div>
        <h5>Easy Booking</h5>
        <p class="text-muted small">Book tour packages in a few clicks and track them from your dashboard.</p>
      </div>
      <div class="col-lg-3 col-md-6 feature-box" data-aos="fade-up" data-aos-delay="200">
        <div class="feature-icon mx-auto"><i class="bi bi-star"></i></div>
        <h5>Verified Reviews</h5>
        <p class="text-muted small">Real ratings from real travellers to help you decide with confidence.</p>
      </div>
      <div class="col-lg-3 col-md-6 feature-box" data-aos="fade-up" data-aos-delay="300">
        <div class="feature-icon mx-auto"><i class="bi bi-shield-check"></i></div>
        <h5>Secure &amp; Reliable</h5>
        <p class="text-muted small">Your data and bookings are protected with secure session handling.</p>
      </div>
    </div>
  </div>
</section>

<!-- ===================== PACKAGES ===================== -->
<section class="section section-mist">
  <div class="container">
    <div class="d-flex justify-content-between align-items-end mb-5 flex-wrap gap-3" data-aos="fade-up">
      <div>
        <span class="section-eyebrow">Plan &amp; Book</span>
        <h2 class="section-title mb-0">Featured Tour Packages</h2>
      </div>
      <a href="/tourism-portal/packages.php" class="btn-teal">View All Packages <i class="bi bi-arrow-right"></i></a>
    </div>
    <div class="row g-4">
      <?php foreach ($packages as $i => $p): ?>
      <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="<?= ($i % 3) * 100 ?>">
        <div class="card-tile">
          <div class="tile-img-wrap">
            <img src="/tourism-portal/assets/images/destinations/<?= h($p['image']) ?>" alt="<?= h($p['name']) ?>"
                 onerror="this.src='https://source.unsplash.com/500x400/?<?= urlencode($p['state_name'] ?? 'india') ?>'">
            <span class="tile-category"><?= h($p['duration']) ?></span>
          </div>
          <div class="tile-body">
            <h5><?= h($p['name']) ?></h5>
            <div class="tile-location"><i class="bi bi-signpost-2"></i> <?= h($p['state_name'] ?? 'Multi-state') ?></div>
            <div class="tile-footer">
              <span class="tile-price">₹<?= number_format($p['price'], 0) ?> / person</span>
              <a href="/tourism-portal/package-details.php?id=<?= $p['package_id'] ?>" class="tile-link">Details <i class="bi bi-arrow-right"></i></a>
            </div>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ===================== CTA ===================== -->
<section class="section pt-0">
  <div class="container">
    <div class="cta-banner text-center" data-aos="zoom-in">
      <h2 class="text-white mb-3">Ready for your next Indian adventure?</h2>
      <p class="mb-4" style="opacity:.9">Create a free account to book packages, save your wishlist and share reviews.</p>
      <a href="/tourism-portal/register.php" class="btn-outline-brand">Get Started — It's Free</a>
    </div>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
