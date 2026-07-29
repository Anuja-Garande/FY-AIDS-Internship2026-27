<?php
// C:\xampp\htdocs\NewProject\guides.php
// Certified Tour Guides Directory Page

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

// 3. Query
$sql = "SELECT g.*, d.name AS destination_name, d.country AS destination_country 
        FROM guides g 
        LEFT JOIN destinations d ON g.destination_id = d.id 
        WHERE 1=1";
$params = [];

if ($search !== '') {
    $sql .= " AND (g.name LIKE ? OR g.languages LIKE ? OR d.name LIKE ?)";
    $w = "%$search%";
    $params[] = $w; $params[] = $w; $params[] = $w;
}

if ($destination > 0) {
    $sql .= " AND g.destination_id = ?";
    $params[] = $destination;
}

$sql .= " ORDER BY g.rating DESC";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $guides = $stmt->fetchAll();
} catch (\PDOException $e) {
    $guides = [];
}

require_once 'includes/header.php';
?>

<!-- Hero Banner -->
<div class="bg-primary text-white py-5 mb-4" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%) !important;">
    <div class="container text-center">
        <h1 class="fw-extrabold text-white mb-2"><i class="bi-person-badge me-2"></i>Verified Local Tour Guides</h1>
        <p class="mb-0 text-white-50 fs-5">Hire licensed local experts for immersive city tours, historical insights, and personalized adventures</p>
    </div>
</div>

<div class="container py-3">
    <div class="row">
        <!-- Sidebar Filters -->
        <div class="col-lg-3 mb-4">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0 text-dark"><i class="bi-funnel-fill text-primary me-2"></i>Guide Filters</h5>
                    <a href="guides.php" class="text-danger small fw-bold">Reset</a>
                </div>
                <div class="card-body pt-0">
                    <form action="guides.php" method="GET">
                        <!-- Search Name/Language -->
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">Guide / Language</label>
                            <input type="text" name="search" class="form-control form-control-sm" placeholder="e.g. English, French, Vikram..." value="<?php echo htmlspecialchars($search); ?>">
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

                        <button type="submit" class="btn btn-primary btn-sm w-100 rounded-pill py-2">Apply Filters <i class="bi-check-lg ms-1"></i></button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Guides Grid -->
        <div class="col-lg-9">
            <div class="d-flex justify-content-between align-items-center bg-white p-3 rounded-3 shadow-sm mb-4">
                <span class="fw-bold text-dark"><?php echo count($guides); ?> verified guides available</span>
            </div>

            <?php if (!empty($guides)): ?>
                <div class="row g-4">
                    <?php foreach ($guides as $guide): ?>
                        <div class="col-md-6">
                            <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden bg-white p-4">
                                <div class="d-flex gap-3 align-items-center mb-3">
                                    <img src="<?php echo htmlspecialchars(get_guide_photo($guide['photo'], $guide['id'], $guide['name'])); ?>" class="rounded-circle shadow-sm" style="width: 80px; height: 80px; object-fit: cover;" alt="<?php echo htmlspecialchars($guide['name']); ?>" onerror="this.onerror=null; this.src='assets/images/default_destination.jpg';">
                                    <div>
                                        <h5 class="fw-bold text-dark mb-1"><?php echo htmlspecialchars($guide['name']); ?></h5>
                                        <div class="badge bg-success-subtle text-success border border-success-subtle rounded-pill small">
                                            <i class="bi-star-fill me-1"></i><?php echo number_format($guide['rating'], 1); ?> Rating
                                        </div>
                                    </div>
                                </div>

                                <div class="small text-muted mb-2"><i class="bi-geo-alt-fill text-danger me-1"></i><?php echo htmlspecialchars($guide['destination_name'] . ', ' . $guide['destination_country']); ?></div>
                                <div class="small text-muted mb-2"><i class="bi-translate text-primary me-1"></i>Languages: <strong><?php echo htmlspecialchars($guide['languages']); ?></strong></div>
                                <div class="small text-muted mb-3"><i class="bi-award-fill text-warning me-1"></i>Experience: <strong><?php echo $guide['experience_years']; ?> Years</strong></div>

                                <div class="mt-auto pt-3 border-top d-flex justify-content-between align-items-center">
                                    <div>
                                        <span class="text-muted small d-block">Daily Fee</span>
                                        <span class="fs-4 fw-extrabold text-success">₹<?php echo number_format($guide['price_per_day'], 2); ?></span>
                                    </div>
                                    <a href="checkout.php?type=guide&id=<?php echo $guide['id']; ?>" class="btn btn-warning text-dark font-weight-bold rounded-pill px-4">
                                        Book Guide
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="text-center py-5 bg-white rounded-3 shadow-sm">
                    <i class="bi-person-x display-1 text-muted mb-3"></i>
                    <h4 class="fw-bold text-dark">No Tour Guides Found</h4>
                    <p class="text-muted">Try selecting another destination or language.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
