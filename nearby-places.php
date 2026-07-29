<?php
// C:\xampp\htdocs\NewProject\nearby-places.php
// Nearby Attractions & Places to Visit Directory Page

require_once 'config/db_connect.php';
require_once 'includes/header.php';

// Fetch destinations for filter dropdown
try {
    $destStmt = $pdo->query("SELECT id, name FROM destinations ORDER BY name ASC");
    $allDestinations = $destStmt->fetchAll();
} catch (\PDOException $e) {
    $allDestinations = [];
}

// 1. Process Filters
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$destination_id = isset($_GET['destination_id']) ? intval($_GET['destination_id']) : 0;
$max_distance = isset($_GET['max_distance']) ? floatval($_GET['max_distance']) : 0;

$sql = "SELECT np.*, d.name AS dest_name, d.id AS dest_id, d.country AS dest_country 
        FROM nearby_places np 
        JOIN destinations d ON np.destination_id = d.id 
        WHERE 1=1";
$params = [];

if (!empty($search)) {
    $sql .= " AND (np.name LIKE ? OR np.description LIKE ? OR d.name LIKE ?)";
    $term = "%{$search}%";
    $params[] = $term;
    $params[] = $term;
    $params[] = $term;
}

if ($destination_id > 0) {
    $sql .= " AND np.destination_id = ?";
    $params[] = $destination_id;
}

if ($max_distance > 0) {
    $sql .= " AND np.distance_km <= ?";
    $params[] = $max_distance;
}

$sql .= " ORDER BY np.distance_km ASC, np.id DESC";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $nearbyPlaces = $stmt->fetchAll();
} catch (\PDOException $e) {
    $nearbyPlaces = [];
}
?>

<!-- Hero Header -->
<div class="bg-primary text-white py-5 mb-4 position-relative overflow-hidden" style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%) !important;">
    <div class="container py-4 text-center position-relative" style="z-index: 2;">
        <span class="badge bg-warning text-dark font-weight-bold text-uppercase px-3 py-2 rounded-pill mb-2"><i class="bi-pin-map-fill me-1"></i>Side Trips & Excursions</span>
        <h1 class="display-5 fw-extrabold mb-2 text-white">Nearby Places to Visit</h1>
        <p class="lead text-white-50 max-w-2xl mx-auto">Discover top side trips, scenic viewpoints, hidden waterfalls, and heritage monuments near your favorite destinations</p>
    </div>
</div>

<div class="container py-3">
    <!-- Filter Card -->
    <div class="card border-0 shadow-sm rounded-4 p-4 mb-5 bg-white">
        <form action="nearby-places.php" method="GET">
            <div class="row g-3 align-items-end">
                <div class="col-md-5">
                    <label class="form-label small fw-bold text-muted">Search Attraction</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="bi-search text-muted"></i></span>
                        <input type="text" name="search" class="form-control border-start-0" placeholder="e.g. Solang Valley, Kufri, Fort..." value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                </div>

                <div class="col-md-4">
                    <label class="form-label small fw-bold text-muted">Filter by Destination</label>
                    <select name="destination_id" class="form-select" onchange="this.form.submit()">
                        <option value="">All Destinations</option>
                        <?php foreach ($allDestinations as $dest): ?>
                            <option value="<?php echo $dest['id']; ?>" <?php echo $destination_id === $dest['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($dest['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary rounded-pill w-100 font-weight-bold py-2">
                        <i class="bi-funnel me-1"></i>Filter
                    </button>
                    <?php if (!empty($search) || $destination_id > 0): ?>
                        <a href="nearby-places.php" class="btn btn-outline-secondary rounded-pill py-2 px-3" title="Reset Filters">
                            <i class="bi-arrow-counterclockwise"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </form>
    </div>

    <!-- Results Grid -->
    <?php if (!empty($nearbyPlaces)): ?>
        <div class="row g-4 mb-5">
            <?php foreach ($nearbyPlaces as $place): ?>
                <div class="col-lg-4 col-md-6">
                    <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden position-relative hover-top transition">
                        <div class="position-relative" style="height: 220px;">
                            <img src="<?php echo htmlspecialchars(get_image_url($place['image'])); ?>" class="w-100 h-100" style="object-fit: cover;" alt="<?php echo htmlspecialchars($place['name']); ?>" onerror="this.onerror=null; this.src='assets/images/default_destination.jpg';">
                            
                            <!-- Distance Badge -->
                            <div class="position-absolute top-0 end-0 m-3">
                                <span class="badge bg-dark text-warning border border-warning fs-6 rounded-pill px-3 py-1 shadow-sm">
                                    <i class="bi-geo-alt-fill me-1"></i><?php echo number_format($place['distance_km'], 1); ?> km away
                                </span>
                            </div>

                            <!-- Destination Name Badge -->
                            <div class="position-absolute bottom-0 start-0 m-3">
                                <span class="badge bg-primary text-white rounded-pill px-3 py-1 shadow-sm">
                                    📍 Near <?php echo htmlspecialchars($place['dest_name']); ?>
                                </span>
                            </div>
                        </div>

                        <div class="card-body p-4 d-flex flex-column justify-content-between">
                            <div>
                                <h4 class="fw-bold text-dark mb-2"><?php echo htmlspecialchars($place['name']); ?></h4>
                                <p class="text-muted small mb-4 line-clamp-3">
                                    <?php echo htmlspecialchars($place['description']); ?>
                                </p>
                            </div>

                            <div class="pt-3 border-top d-flex justify-content-between align-items-center gap-2">
                                <a href="https://www.google.com/maps/search/?api=1&query=<?php echo urlencode($place['name'] . ', ' . $place['dest_name']); ?>" target="_blank" rel="noopener noreferrer" class="btn btn-outline-secondary btn-sm rounded-pill px-3 fw-bold">
                                    <i class="bi-map-fill me-1 text-danger"></i>View on Map
                                </a>
                                <a href="destination-detail.php?id=<?php echo $place['dest_id']; ?>#attractions" class="btn btn-primary btn-sm rounded-pill px-3 fw-bold">
                                    Explore <i class="bi-arrow-right ms-1"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="text-center py-5 my-4">
            <i class="bi-pin-map text-muted display-3 mb-3 d-block"></i>
            <h3 class="fw-bold text-dark">No Nearby Places Found</h3>
            <p class="text-muted">Try selecting another destination or resetting your search filter.</p>
            <a href="nearby-places.php" class="btn btn-primary rounded-pill px-4 mt-2">View All Nearby Places</a>
        </div>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>
