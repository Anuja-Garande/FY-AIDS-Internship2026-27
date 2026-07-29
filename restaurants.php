<?php
// C:\xampp\htdocs\NewProject\restaurants.php
// Local Dining & Restaurant Guide Page

require_once 'config/db_connect.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Fetch destinations list
try {
    $destList = $pdo->query("SELECT id, name, country FROM destinations ORDER BY name ASC")->fetchAll();
} catch (\PDOException $e) {
    $destList = [];
}

// 2. Parse Filters
$search      = isset($_GET['search']) ? trim($_GET['search']) : '';
$destination = isset($_GET['destination']) ? intval($_GET['destination']) : 0;
$cuisine     = isset($_GET['cuisine']) ? trim($_GET['cuisine']) : '';

// 3. Dynamic Query
$sql = "SELECT r.*, d.name AS destination_name, d.country AS destination_country 
        FROM restaurants r 
        LEFT JOIN destinations d ON r.destination_id = d.id 
        WHERE 1=1";
$params = [];

if ($search !== '') {
    $sql .= " AND (r.name LIKE ? OR r.cuisine_type LIKE ? OR d.name LIKE ?)";
    $w = "%$search%";
    $params[] = $w; $params[] = $w; $params[] = $w;
}

if ($destination > 0) {
    $sql .= " AND r.destination_id = ?";
    $params[] = $destination;
}

if ($cuisine !== '') {
    $sql .= " AND r.cuisine_type LIKE ?";
    $params[] = "%$cuisine%";
}

$sql .= " ORDER BY r.rating DESC";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $restaurants = $stmt->fetchAll();
} catch (\PDOException $e) {
    $restaurants = [];
}

require_once 'includes/header.php';
?>

<!-- Hero Banner -->
<div class="bg-danger text-white py-5 mb-4" style="background: linear-gradient(135deg, #c0392b 0%, #e74c3c 100%) !important;">
    <div class="container text-center">
        <h1 class="fw-extrabold text-white mb-2"><i class="bi-cup-hot me-2"></i>Destination Restaurant & Dining Guide</h1>
        <p class="mb-0 text-white-50 fs-5">Discover local delicacies, fine dining, traditional cafes, and street food hubs</p>
    </div>
</div>

<div class="container py-3">
    <div class="row">
        <!-- Sidebar Filters -->
        <div class="col-lg-3 mb-4">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0 text-dark"><i class="bi-funnel-fill text-danger me-2"></i>Filters</h5>
                    <a href="restaurants.php" class="text-danger small fw-bold">Reset</a>
                </div>
                <div class="card-body pt-0">
                    <form action="restaurants.php" method="GET">
                        <!-- Search Name -->
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">Search Restaurant</label>
                            <input type="text" name="search" class="form-control form-control-sm" placeholder="e.g. Cafe, Seafood, French..." value="<?php echo htmlspecialchars($search); ?>">
                        </div>

                        <!-- Destination Select -->
                        <div class="mb-3 border-top pt-3">
                            <label class="form-label small fw-bold text-muted">Destination</label>
                            <select name="destination" class="form-select form-select-sm" onchange="this.form.submit()">
                                <option value="">All Destinations</option>
                                <?php foreach ($destList as $d): ?>
                                    <option value="<?php echo $d['id']; ?>" <?php echo $destination == $d['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($d['name'] . ' (' . $d['country'] . ')'); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <button type="submit" class="btn btn-danger btn-sm w-100 rounded-pill py-2">Apply Filters <i class="bi-check-lg ms-1"></i></button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Restaurants Grid -->
        <div class="col-lg-9">
            <div class="d-flex justify-content-between align-items-center bg-white p-3 rounded-3 shadow-sm mb-4">
                <span class="fw-bold text-dark"><?php echo count($restaurants); ?> recommended dining spots</span>
            </div>

            <?php if (!empty($restaurants)): ?>
                <div class="row g-4">
                    <?php foreach ($restaurants as $r): ?>
                        <div class="col-md-6">
                            <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden bg-white">
                                <div class="position-relative" style="height: 180px;">
                                    <img src="<?php echo htmlspecialchars(get_image_url($r['image'])); ?>" class="w-100 h-100" style="object-fit: cover;" alt="<?php echo htmlspecialchars($r['name']); ?>" onerror="this.onerror=null; this.src='assets/images/default_destination.jpg';">
                                    <span class="position-absolute top-0 end-0 m-3 badge bg-warning text-dark font-weight-bold">
                                        <i class="bi-star-fill me-1"></i><?php echo number_format($r['rating'], 1); ?>
                                    </span>
                                </div>
                                <div class="card-body p-4 d-flex flex-column">
                                    <h5 class="fw-bold text-dark mb-1"><?php echo htmlspecialchars($r['name']); ?></h5>
                                    <div class="text-muted small mb-2"><i class="bi-geo-alt-fill text-danger me-1"></i><?php echo htmlspecialchars($r['destination_name'] . ', ' . $r['destination_country']); ?></div>
                                    <p class="small text-muted mb-3"><i class="bi-egg-fried me-1 text-primary"></i><strong>Cuisine:</strong> <?php echo htmlspecialchars($r['cuisine_type']); ?></p>

                                    <div class="mt-auto pt-3 border-top d-flex justify-content-between align-items-center small">
                                        <span class="fw-bold text-dark"><i class="bi-wallet2 me-1 text-success"></i><?php echo htmlspecialchars($r['price_range']); ?></span>
                                        <span class="text-primary"><i class="bi-telephone me-1"></i><?php echo htmlspecialchars($r['contact'] ?? 'N/A'); ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="text-center py-5 bg-white rounded-3 shadow-sm">
                    <i class="bi-cup-straw display-1 text-muted mb-3"></i>
                    <h4 class="fw-bold text-dark">No Restaurants Found</h4>
                    <p class="text-muted">Try choosing another destination or keyword.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
