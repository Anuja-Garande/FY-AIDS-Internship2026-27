<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';
$pageTitle = 'Tour Packages';

$stateId = $_GET['state'] ?? '';
$sql = "SELECT p.*, s.name AS state_name FROM packages p LEFT JOIN states s ON s.state_id = p.state_id WHERE 1=1";
$params = [];
if ($stateId !== '') { $sql .= " AND p.state_id = ?"; $params[] = $stateId; }
$sql .= " ORDER BY p.package_id ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$packages = $stmt->fetchAll();
$states = $pdo->query("SELECT * FROM states ORDER BY name ASC")->fetchAll();

require __DIR__ . '/includes/header.php';
?>

<section class="page-banner">
  <div class="container">
    <h1 data-aos="fade-up">Tour Packages</h1>
    <p class="breadcrumb-custom" data-aos="fade-up" data-aos-delay="100"><a href="/tourism-portal/index.php">Home</a> / Packages</p>
  </div>
</section>

<section class="section">
  <div class="container">
    <form method="get" class="filter-bar row g-3 align-items-center" data-aos="fade-up">
      <div class="col-lg-9">
        <select name="state" class="form-select">
          <option value="">All States</option>
          <?php foreach ($states as $s): ?>
            <option value="<?= $s['state_id'] ?>" <?= $stateId == $s['state_id'] ? 'selected' : '' ?>><?= h($s['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-lg-3">
        <button type="submit" class="btn-teal w-100">Filter</button>
      </div>
    </form>

    <div class="row g-4 mt-2">
      <?php foreach ($packages as $i => $p): ?>
      <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="<?= ($i % 3) * 100 ?>">
        <div class="card-tile">
          <div class="tile-img-wrap">
            <img src="/tourism-portal/assets/images/destinations/<?= h($p['image']) ?>" alt="<?= h($p['name']) ?>"
                 onerror="this.src='https://source.unsplash.com/500x400/?<?= urlencode($p['state_name'] ?? 'india') ?>,travel'">
            <span class="tile-category"><?= h($p['duration']) ?></span>
          </div>
          <div class="tile-body">
            <h5><?= h($p['name']) ?></h5>
            <div class="tile-location"><i class="bi bi-signpost-2"></i> <?= h($p['state_name'] ?? 'Multi-state') ?></div>
            <p class="small text-muted mb-2"><?= h(mb_strimwidth($p['itinerary'], 0, 90, '...')) ?></p>
            <div class="tile-footer">
              <span class="tile-price">₹<?= number_format($p['price'], 0) ?></span>
              <a href="/tourism-portal/package-details.php?id=<?= $p['package_id'] ?>" class="tile-link">Details <i class="bi bi-arrow-right"></i></a>
            </div>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
