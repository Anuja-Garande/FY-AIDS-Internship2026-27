<?php
// C:\xampp\htdocs\NewProject\packages.php
// Tour Packages Directory & Listing Page

require_once 'config/db_connect.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Fetch destinations list for dropdown filter
try {
    $destList = $pdo->query("SELECT id, name, country FROM destinations ORDER BY name ASC")->fetchAll();
} catch (\PDOException $e) {
    $destList = [];
}

// 2. Parse Search & Filters
$search      = isset($_GET['search']) ? trim($_GET['search']) : '';
$destination = isset($_GET['destination']) ? intval($_GET['destination']) : 0;
$max_price   = isset($_GET['max_price']) ? floatval($_GET['max_price']) : 0;
$duration    = isset($_GET['duration']) ? intval($_GET['duration']) : 0;

// 3. Build Dynamic SQL Query
$sql = "SELECT p.*, d.name AS destination_name, d.country AS destination_country, d.region_type 
        FROM packages p 
        LEFT JOIN destinations d ON p.destination_id = d.id 
        WHERE 1=1";
$params = [];

if ($search !== '') {
    $sql .= " AND (p.name LIKE ? OR p.description LIKE ? OR p.inclusions LIKE ? OR d.name LIKE ?)";
    $w = "%$search%";
    $params[] = $w; $params[] = $w; $params[] = $w; $params[] = $w;
}

if ($destination > 0) {
    $sql .= " AND p.destination_id = ?";
    $params[] = $destination;
}

if ($max_price > 0) {
    $sql .= " AND p.price <= ?";
    $params[] = $max_price;
}

if ($duration > 0) {
    $sql .= " AND p.duration_days <= ?";
    $params[] = $duration;
}

$sql .= " ORDER BY p.created_at DESC";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $packages = $stmt->fetchAll();
} catch (\PDOException $e) {
    $packages = [];
}

require_once 'includes/header.php';
?>

<!-- Hero Banner -->
<div class="bg-primary text-white py-5 mb-4" style="background: linear-gradient(135deg, var(--primary-dark) 0%, #1a365d 100%) !important;">
    <div class="container text-center">
        <h1 class="fw-bold mb-2 text-white">Curated Holiday & Tour Packages</h1>
        <p class="mb-0 text-white-50 fs-5">All-inclusive travel itineraries with hotel stays, guided sightseeing, and transfers</p>
    </div>
</div>

<div class="container py-3">
    <div class="row">
        <!-- Sidebar Filter Form -->
        <div class="col-lg-3 mb-4">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0 text-dark"><i class="bi-funnel-fill text-primary me-2"></i>Package Filters</h5>
                    <a href="packages.php" class="text-danger small fw-bold">Reset</a>
                </div>
                <div class="card-body pt-0">
                    <form action="packages.php" method="GET">
                        <!-- Keyword Search -->
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">Keyword Search</label>
                            <input type="text" name="search" class="form-control form-control-sm" placeholder="e.g. Snow, Beach, Romantic..." value="<?php echo htmlspecialchars($search); ?>">
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

                        <!-- Max Duration -->
                        <div class="mb-3 border-top pt-3">
                            <label class="form-label small fw-bold text-muted">Max Duration (Days)</label>
                            <select name="duration" class="form-select form-select-sm" onchange="this.form.submit()">
                                <option value="">Any Duration</option>
                                <option value="3" <?php echo $duration == 3 ? 'selected' : ''; ?>>Up to 3 Days</option>
                                <option value="5" <?php echo $duration == 5 ? 'selected' : ''; ?>>Up to 5 Days</option>
                                <option value="7" <?php echo $duration == 7 ? 'selected' : ''; ?>>Up to 7 Days</option>
                            </select>
                        </div>

                        <!-- Max Budget -->
                        <div class="mb-4 border-top pt-3">
                            <label class="form-label small fw-bold text-muted">Max Budget (₹)</label>
                            <input type="number" name="max_price" class="form-control form-control-sm" placeholder="e.g. 30000" value="<?php echo $max_price > 0 ? htmlspecialchars($max_price) : ''; ?>">
                        </div>

                        <button type="submit" class="btn btn-primary btn-sm w-100 rounded-pill py-2">Apply Filters <i class="bi-check-lg ms-1"></i></button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Main Package Grid -->
        <div class="col-lg-9">
            <div class="d-flex justify-content-between align-items-center bg-white p-3 rounded-3 shadow-sm mb-4">
                <span class="fw-bold text-dark"><?php echo count($packages); ?> tour packages available</span>
            </div>

            <?php if (!empty($packages)): ?>
                <div class="row g-4">
                    <?php foreach ($packages as $pkg): ?>
                        <div class="col-md-6">
                            <div class="card h-100 border-0 shadow-sm rounded-3 overflow-hidden destination-card">
                                <div class="card-img-wrapper position-relative" style="height: 220px;">
                                    <img src="<?php echo htmlspecialchars(get_image_url($pkg['image'])); ?>" alt="<?php echo htmlspecialchars($pkg['name']); ?>" class="w-100 h-100" style="object-fit: cover;" onerror="this.onerror=null; this.src='assets/images/default_destination.jpg';">
                                    <span class="card-badge bg-warning text-dark font-weight-bold">
                                        <i class="bi-clock-history me-1"></i><?php echo $pkg['duration_days']; ?> Days / <?php echo ($pkg['duration_days']-1); ?> Nights
                                    </span>
                                    <?php if ($pkg['destination_name']): ?>
                                        <span class="position-absolute bottom-0 start-0 m-3 badge bg-dark text-white rounded-pill">
                                            <i class="bi-geo-alt me-1"></i><?php echo htmlspecialchars($pkg['destination_name']); ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <div class="card-body p-4 d-flex flex-column">
                                    <h5 class="fw-bold mb-2 text-dark"><?php echo htmlspecialchars($pkg['name']); ?></h5>
                                    <p class="small text-muted line-clamp-2 mb-3"><?php echo htmlspecialchars(substr($pkg['description'], 0, 110)) . '...'; ?></p>

                                    <?php if (!empty($pkg['inclusions'])): ?>
                                        <div class="mb-3 bg-light p-2 rounded small">
                                            <strong class="text-primary d-block mb-1"><i class="bi-check-all me-1"></i>Inclusions:</strong>
                                            <span class="text-muted text-truncate d-block" style="max-width: 100%;"><?php echo htmlspecialchars($pkg['inclusions']); ?></span>
                                        </div>
                                    <?php endif; ?>

                                    <div class="mt-auto pt-3 border-top d-flex justify-content-between align-items-center">
                                        <div>
                                            <span class="text-muted small d-block">Price per person</span>
                                            <span class="fs-4 fw-extrabold text-success">₹<?php echo number_format($pkg['price'], 2); ?></span>
                                        </div>
                                        <a href="package-detail.php?id=<?php echo $pkg['id']; ?>" class="btn btn-warning text-dark font-weight-bold rounded-pill px-4">
                                            View Details
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="text-center py-5 bg-white rounded-3 shadow-sm">
                    <i class="bi-box-seam display-1 text-muted mb-3"></i>
                    <h4 class="fw-bold text-dark">No Tour Packages Found</h4>
                    <p class="text-muted">Try adjusting your keyword or filter options.</p>
                    <a href="packages.php" class="btn btn-primary rounded-pill px-4 mt-2">Reset Filters</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
