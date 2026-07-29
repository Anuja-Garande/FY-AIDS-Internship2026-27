<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';
$pageTitle = 'Restaurants';

$search  = trim($_GET['q'] ?? '');
$stateId = $_GET['state'] ?? '';

$sql = "SELECT h.*, d.name AS dest_name, s.name AS state_name, s.state_id FROM restaurants h
        JOIN destinations d ON d.destination_id = h.destination_id
        JOIN states s ON s.state_id = d.state_id WHERE 1=1";
$params = [];
if ($search !== '') { $sql .= " AND (h.name LIKE ? OR d.name LIKE ?)"; $params[] = "%$search%"; $params[] = "%$search%"; }
if ($stateId !== '') { $sql .= " AND s.state_id = ?"; $params[] = $stateId; }
$sql .= " ORDER BY h.name ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$hotels = $stmt->fetchAll();
$states = $pdo->query("SELECT * FROM states ORDER BY name ASC")->fetchAll();

require __DIR__ . '/includes/header.php';
?>

<section class="page-banner">
  <div class="container">
    <h1 data-aos="fade-up">Restaurants Across India</h1>
    <p class="breadcrumb-custom" data-aos="fade-up" data-aos-delay="100"><a href="/tourism-portal/index.php">Home</a> / Restaurants</p>
  </div>
</section>

<section class="section">
  <div class="container">
    <form method="get" class="filter-bar row g-3 align-items-center" data-aos="fade-up">
      <div class="col-lg-6">
        <input type="text" name="q" class="form-control" placeholder="Search restaurants or destinations" value="<?= h($search) ?>">
      </div>
      <div class="col-lg-4">
        <select name="state" class="form-select">
          <option value="">All States</option>
          <?php foreach ($states as $s): ?>
            <option value="<?= $s['state_id'] ?>" <?= $stateId == $s['state_id'] ? 'selected' : '' ?>><?= h($s['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-lg-2">
        <button type="submit" class="btn-teal w-100">Filter</button>
      </div>
    </form>

    <p class="text-muted mt-4 mb-4"><?= count($hotels) ?> restaurant(s) found</p>

    <div class="row g-4">
      <?php foreach ($hotels as $i => $h): ?>
      <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="<?= ($i % 4) * 100 ?>">
        <div class="card-tile">
          <div class="tile-img-wrap">
            <img src="/tourism-portal/assets/images/destinations/<?= h($h['image']) ?>" alt="<?= h($h['name']) ?>"
                 onerror="this.src='https://source.unsplash.com/500x400/?restaurant,<?= urlencode($h['dest_name']) ?>'">
            <span class="tile-category"><?= h($h["cuisine_type"]) ?></span>
          </div>
          <div class="tile-body">
            <h5><?= h($h['name']) ?></h5>
            <div class="tile-location"><i class="bi bi-signpost-2"></i> <?= h($h['dest_name']) ?>, <?= h($h['state_name']) ?></div>
            <p class="small text-muted mb-1"><i class="bi bi-telephone"></i> <?= h($h['contact']) ?></p>
            <div class="tile-footer">
              <span class="tile-price"><?= h($h['price_range']) ?></span>
              <a href="/tourism-portal/destination-details.php?id=<?= $h['destination_id'] ?>" class="tile-link">View Destination <i class="bi bi-arrow-right"></i></a>
            </div>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
