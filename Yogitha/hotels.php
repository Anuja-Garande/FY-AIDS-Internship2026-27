<?php
// C:\xampp\htdocs\NewProject\hotels.php
// Hotel Directory & Listing Page

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
$stars       = isset($_GET['stars']) ? intval($_GET['stars']) : 0;
$max_price   = isset($_GET['max_price']) ? floatval($_GET['max_price']) : 0;

// 3. Dynamic SQL Query
$sql = "SELECT h.*, d.name AS destination_name, d.country AS destination_country 
        FROM hotels h 
        LEFT JOIN destinations d ON h.destination_id = d.id 
        WHERE 1=1";
$params = [];

if ($search !== '') {
    $sql .= " AND (h.name LIKE ? OR h.amenities LIKE ? OR d.name LIKE ?)";
    $w = "%$search%";
    $params[] = $w; $params[] = $w; $params[] = $w;
}

if ($destination > 0) {
    $sql .= " AND h.destination_id = ?";
    $params[] = $destination;
}

if ($stars > 0) {
    $sql .= " AND h.star_rating >= ?";
    $params[] = $stars;
}

if ($max_price > 0) {
    $sql .= " AND h.price_per_night <= ?";
    $params[] = $max_price;
}

$sql .= " ORDER BY h.star_rating DESC, h.price_per_night ASC";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $hotels = $stmt->fetchAll();
} catch (\PDOException $e) {
    $hotels = [];
}

require_once 'includes/header.php';
?>

<!-- Hero Banner -->
<div class="bg-primary text-white py-5 mb-4" style="background: linear-gradient(135deg, #0a192f 0%, #172a45 100%) !important;">
    <div class="container text-center">
        <h1 class="fw-extrabold text-white mb-2"><i class="bi-building me-2"></i>Hotels & Luxury Resorts</h1>
        <p class="mb-0 text-white-50 fs-5">Find handpicked accommodation with top amenities across global destinations</p>
    </div>
</div>

<div class="container py-3">
    <div class="row">
        <!-- Sidebar Filters -->
        <div class="col-lg-3 mb-4">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0 text-dark"><i class="bi-funnel-fill text-primary me-2"></i>Hotel Filters</h5>
                    <a href="hotels.php" class="text-danger small fw-bold">Reset</a>
                </div>
                <div class="card-body pt-0">
                    <form action="hotels.php" method="GET">
                        <!-- Keyword Search -->
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">Hotel Name / Amenity</label>
                            <input type="text" name="search" class="form-control form-control-sm" placeholder="e.g. Taj, Pool, Spa..." value="<?php echo htmlspecialchars($search); ?>">
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

                        <!-- Star Rating -->
                        <div class="mb-3 border-top pt-3">
                            <label class="form-label small fw-bold text-muted">Star Rating</label>
                            <select name="stars" class="form-select form-select-sm" onchange="this.form.submit()">
                                <option value="0">Any Rating</option>
                                <option value="5" <?php echo $stars == 5 ? 'selected' : ''; ?>>5 Star Luxury</option>
                                <option value="4" <?php echo $stars == 4 ? 'selected' : ''; ?>>4 Star & above</option>
                                <option value="3" <?php echo $stars == 3 ? 'selected' : ''; ?>>3 Star & above</option>
                            </select>
                        </div>

                        <!-- Max Price -->
                        <div class="mb-4 border-top pt-3">
                            <label class="form-label small fw-bold text-muted">Max Price / Night (₹)</label>
                            <input type="number" name="max_price" class="form-control form-control-sm" placeholder="e.g. 15000" value="<?php echo $max_price > 0 ? htmlspecialchars($max_price) : ''; ?>">
                        </div>

                        <button type="submit" class="btn btn-primary btn-sm w-100 rounded-pill py-2">Apply Filters <i class="bi-check-lg ms-1"></i></button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Hotels List -->
        <div class="col-lg-9">
            <div class="d-flex justify-content-between align-items-center bg-white p-3 rounded-3 shadow-sm mb-4">
                <span class="fw-bold text-dark"><?php echo count($hotels); ?> hotels found</span>
            </div>

            <?php if (!empty($hotels)): ?>
                <div class="d-flex flex-column gap-4">
                    <?php foreach ($hotels as $hotel): ?>
                        <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white">
                            <div class="row g-0">
                                <div class="col-md-4 position-relative">
                                    <img src="<?php echo htmlspecialchars(get_image_url($hotel['image'])); ?>" class="w-100 h-100" style="object-fit: cover; min-height: 200px;" alt="<?php echo htmlspecialchars($hotel['name']); ?>" onerror="this.onerror=null; this.src='assets/images/default_destination.jpg';">
                                    <span class="position-absolute top-0 start-0 m-3 badge bg-warning text-dark font-weight-bold">
                                        <?php for ($s=0; $s<$hotel['star_rating']; $s++): ?>★<?php endfor; ?>
                                    </span>
                                </div>
                                <div class="col-md-8 p-4 d-flex flex-column justify-content-between">
                                    <div>
                                        <div class="d-flex justify-content-between align-items-start mb-1">
                                            <h4 class="fw-bold text-dark mb-0"><?php echo htmlspecialchars($hotel['name']); ?></h4>
                                        </div>
                                        <div class="text-muted small mb-3">
                                            <i class="bi-geo-alt-fill text-danger me-1"></i><?php echo htmlspecialchars($hotel['destination_name'] . ', ' . $hotel['destination_country']); ?>
                                        </div>
                                        <p class="small text-muted mb-3"><i class="bi-check2-circle text-success me-1"></i>Amenities: <?php echo htmlspecialchars($hotel['amenities']); ?></p>
                                    </div>
                                    
                                    <div class="d-flex justify-content-between align-items-end pt-3 border-top">
                                        <div>
                                            <span class="text-muted small d-block">Price per night</span>
                                            <span class="fs-3 fw-extrabold text-success">₹<?php echo number_format($hotel['price_per_night'], 2); ?></span>
                                        </div>
                                        <a href="hotel-detail.php?id=<?php echo $hotel['id']; ?>" class="btn btn-primary rounded-pill px-4 font-weight-bold">
                                            View & Book Room
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="text-center py-5 bg-white rounded-3 shadow-sm">
                    <i class="bi-building-x display-1 text-muted mb-3"></i>
                    <h4 class="fw-bold text-dark">No Hotels Found</h4>
                    <p class="text-muted">Try resetting your price or star rating filters.</p>
                    <a href="hotels.php" class="btn btn-primary rounded-pill px-4 mt-2">Reset Filters</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
