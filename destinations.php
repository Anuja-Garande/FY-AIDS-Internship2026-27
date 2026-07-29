<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';
$pageTitle = 'Destinations';

// ---- Filters ----
$search   = trim($_GET['q'] ?? '');
$stateId  = $_GET['state'] ?? '';
$category = $_GET['category'] ?? '';

$sql = "SELECT d.*, s.name AS state_name FROM destinations d
        JOIN states s ON s.state_id = d.state_id WHERE 1=1";
$params = [];

if ($search !== '') {
    $sql .= " AND (d.name LIKE ? OR d.location LIKE ? OR s.name LIKE ?)";
    $like = "%$search%";
    $params[] = $like; $params[] = $like; $params[] = $like;
}
if ($stateId !== '') {
    $sql .= " AND d.state_id = ?";
    $params[] = $stateId;
}
if ($category !== '') {
    $sql .= " AND d.category = ?";
    $params[] = $category;
}
$sql .= " ORDER BY d.popularity DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$destinations = $stmt->fetchAll();

$states = $pdo->query("SELECT * FROM states ORDER BY name ASC")->fetchAll();
$categories = $pdo->query("SELECT DISTINCT category FROM destinations ORDER BY category ASC")->fetchAll();

require __DIR__ . '/includes/header.php';
?>

<section class="page-banner">
  <div class="container">
    <h1 data-aos="fade-up">Explore Destinations</h1>
    <p class="breadcrumb-custom" data-aos="fade-up" data-aos-delay="100"><a href="/tourism-portal/index.php">Home</a> / Destinations</p>
  </div>
</section>

<section class="section">
  <div class="container">
    <form method="get" class="filter-bar row g-3 align-items-center" data-aos="fade-up">
      <div class="col-lg-4">
        <input type="text" name="q" class="form-control" placeholder="Search by name or location" value="<?= h($search) ?>">
      </div>
      <div class="col-lg-3">
        <select name="state" class="form-select">
          <option value="">All States</option>
          <?php foreach ($states as $s): ?>
            <option value="<?= $s['state_id'] ?>" <?= $stateId == $s['state_id'] ? 'selected' : '' ?>><?= h($s['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-lg-3">
        <select name="category" class="form-select">
          <option value="">All Categories</option>
          <?php foreach ($categories as $c): ?>
            <option value="<?= h($c['category']) ?>" <?= $category === $c['category'] ? 'selected' : '' ?>><?= h($c['category']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-lg-2">
        <button type="submit" class="btn-teal w-100">Filter <i class="bi bi-funnel"></i></button>
      </div>
    </form>

    <p class="text-muted mt-4 mb-4"><?= count($destinations) ?> destination(s) found</p>

    <div class="row g-4">
      <?php if (empty($destinations)): ?>
        <div class="col-12 text-center py-5">
          <i class="bi bi-emoji-frown" style="font-size:3rem;color:var(--text-muted);"></i>
          <h5 class="mt-3">No destinations match your filters</h5>
          <a href="/tourism-portal/destinations.php" class="btn-teal mt-3">Reset Filters</a>
        </div>
      <?php endif; ?>
      <?php foreach ($destinations as $i => $d): $r = getAverageRating($pdo, 'destination', $d['destination_id']); ?>
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
              <span class="tile-price"><i class="bi bi-fire text-danger"></i> <?= $d['popularity'] ?>%</span>
              <a href="/tourism-portal/destination-details.php?id=<?= $d['destination_id'] ?>" class="tile-link">Explore <i class="bi bi-arrow-right"></i></a>
            </div>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
