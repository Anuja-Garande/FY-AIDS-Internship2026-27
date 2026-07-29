<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';
$pageTitle = 'About Us';

$stateCount = $pdo->query("SELECT COUNT(*) FROM states")->fetchColumn();
$destCount  = $pdo->query("SELECT COUNT(*) FROM destinations")->fetchColumn();
$pkgCount   = $pdo->query("SELECT COUNT(*) FROM packages")->fetchColumn();

require __DIR__ . '/includes/header.php';
?>

<section class="page-banner">
  <div class="container">
    <h1 data-aos="fade-up">About BharatYatra</h1>
    <p class="breadcrumb-custom" data-aos="fade-up" data-aos-delay="100"><a href="/tourism-portal/index.php">Home</a> / About</p>
  </div>
</section>

<section class="section">
  <div class="container">
    <div class="row align-items-center g-5">
      <div class="col-lg-6" data-aos="fade-up">
        <span class="section-eyebrow">Our Story</span>
        <h2 class="section-title">Bringing Incredible India to Every Traveller</h2>
        <p class="text-muted">BharatYatra is a tourism and destination portal built to help travellers discover India's most
          beautiful states, plan trips with confidence, and share their experiences with a growing community of explorers.</p>
        <p class="text-muted">From the royal forts of Rajasthan to the backwaters of Kerala, the snow peaks of Kashmir to the
          beaches of Goa — we bring together destinations, hotels, restaurants, and curated tour packages on a single, easy-to-use platform.</p>
        <div class="row g-3 mt-3">
          <div class="col-4 text-center"><div class="stat-card p-3"><div class="num"><?= $stateCount ?></div><p class="text-muted small mb-0">States</p></div></div>
          <div class="col-4 text-center"><div class="stat-card p-3"><div class="num"><?= $destCount ?></div><p class="text-muted small mb-0">Destinations</p></div></div>
          <div class="col-4 text-center"><div class="stat-card p-3"><div class="num"><?= $pkgCount ?></div><p class="text-muted small mb-0">Packages</p></div></div>
        </div>
      </div>
      <div class="col-lg-6" data-aos="fade-up" data-aos-delay="150">
        <img src="/tourism-portal/assets/images/hero/about.jpg" class="rounded-4 w-100" alt="About BharatYatra"
             onerror="this.src='https://source.unsplash.com/700x600/?india,culture'">
      </div>
    </div>
  </div>
</section>

<section class="section section-mist">
  <div class="container">
    <div class="text-center mb-5" data-aos="fade-up">
      <span class="section-eyebrow">Our Mission</span>
      <h2 class="section-title">What Drives Us</h2>
    </div>
    <div class="row g-4 text-center">
      <div class="col-lg-4 feature-box" data-aos="fade-up">
        <div class="feature-icon mx-auto"><i class="bi bi-compass"></i></div>
        <h5>Discovery</h5>
        <p class="text-muted small">Making it easy to find the right destination for every kind of traveller.</p>
      </div>
      <div class="col-lg-4 feature-box" data-aos="fade-up" data-aos-delay="100">
        <div class="feature-icon mx-auto"><i class="bi bi-hand-thumbs-up"></i></div>
        <h5>Trust</h5>
        <p class="text-muted small">Genuine reviews and transparent information you can rely on.</p>
      </div>
      <div class="col-lg-4 feature-box" data-aos="fade-up" data-aos-delay="200">
        <div class="feature-icon mx-auto"><i class="bi bi-heart"></i></div>
        <h5>Hospitality</h5>
        <p class="text-muted small">Rooted in "Atithi Devo Bhava" — treating every visitor like family.</p>
      </div>
    </div>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
