<?php
// C:\xampp\htdocs\NewProject\destinations.php
// Destination Listing and Filtering Page

require_once 'config/db_connect.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Get Categories for the Filter list
try {
    $categories = $pdo->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();
    
    // Get unique continents present in the DB for filtering
    $continents = $pdo->query("SELECT DISTINCT continent FROM destinations WHERE continent IS NOT NULL AND continent != '' ORDER BY continent ASC")->fetchAll(PDO::FETCH_COLUMN);
} catch (\PDOException $e) {
    die("Database query error: " . $e->getMessage());
}

// 2. Parse Search & Filter variables from GET
$search   = isset($_GET['search']) ? trim($_GET['search']) : '';
$category = isset($_GET['category']) ? trim($_GET['category']) : '';
$continent= isset($_GET['continent']) ? trim($_GET['continent']) : '';
$region   = isset($_GET['region']) ? trim($_GET['region']) : '';
$budget   = isset($_GET['budget']) ? trim($_GET['budget']) : '';
$rating   = isset($_GET['rating']) ? floatval($_GET['rating']) : 0.0;
$sort     = isset($_GET['sort']) ? trim($_GET['sort']) : 'rating_desc';

// 3. Build SQL query dynamically with Prepared Statements
$sql = "SELECT d.*, c.name AS category_name FROM destinations d 
        LEFT JOIN categories c ON d.category_id = c.id 
        WHERE 1=1";
$params = [];

if ($search !== '') {
    $sql .= " AND (d.name LIKE ? OR d.country LIKE ? OR d.continent LIKE ? OR d.description LIKE ?)";
    $searchWildcard = "%$search%";
    $params[] = $searchWildcard;
    $params[] = $searchWildcard;
    $params[] = $searchWildcard;
    $params[] = $searchWildcard;
}

if ($category !== '') {
    $sql .= " AND d.category_id = ?";
    $params[] = intval($category);
}

if ($region !== '') {
    // Check by region_type or fallback by country
    if ($region === 'National') {
        $sql .= " AND (d.region_type = 'National' OR d.country = 'India')";
    } elseif ($region === 'International') {
        $sql .= " AND (d.region_type = 'International' OR d.country != 'India')";
    }
}

if ($continent !== '') {
    $sql .= " AND d.continent = ?";
    $params[] = $continent;
}

if ($budget !== '') {
    $sql .= " AND d.price_range = ?";
    $params[] = $budget;
}

if ($rating > 0.0) {
    $sql .= " AND d.rating >= ?";
    $params[] = $rating;
}

// Apply Sorting
switch ($sort) {
    case 'name_asc':
        $sql .= " ORDER BY d.name ASC";
        break;
    case 'name_desc':
        $sql .= " ORDER BY d.name DESC";
        break;
    case 'rating_desc':
        $sql .= " ORDER BY d.rating DESC";
        break;
    case 'budget_asc':
        // Sorts by Enum order: Budget -> Mid-Range -> Luxury
        $sql .= " ORDER BY FIELD(d.price_range, 'Budget', 'Mid-Range', 'Luxury') ASC, d.name ASC";
        break;
    case 'budget_desc':
        $sql .= " ORDER BY FIELD(d.price_range, 'Budget', 'Mid-Range', 'Luxury') DESC, d.name ASC";
        break;
    default:
        $sql .= " ORDER BY d.rating DESC";
}

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $destinations = $stmt->fetchAll();
} catch (\PDOException $e) {
    die("Error fetching destinations: " . $e->getMessage());
}

require_once 'includes/header.php';
?>

<!-- Hero Banner for Listings -->
<div class="bg-primary text-white py-4 mb-4" style="background: linear-gradient(135deg, var(--primary-dark) 0%, #153e6d 100%) !important;">
    <div class="container text-center">
        <h1 class="fw-bold mb-1 text-white">Explore Destinations</h1>
        <p class="mb-0 text-white-50">Filter through categories, budgets, and reviews to find your perfect getaway</p>
    </div>
</div>

<div class="container py-3">
    <div class="row">
        <!-- 1. Sidebar Filters (col-lg-3) -->
        <div class="col-lg-3 mb-4">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0 text-dark"><i class="bi-funnel-fill text-primary me-2"></i>Filters</h5>
                    <a href="destinations.php" class="text-danger small fw-bold">Reset All</a>
                </div>
                <div class="card-body pt-0">
                    <form action="destinations.php" method="GET">
                        <!-- Search Name/Country -->
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">Keyword Search</label>
                            <div class="input-group">
                                <input type="text" name="search" class="form-control form-control-sm" placeholder="e.g. India, Beach..." value="<?php echo htmlspecialchars($search); ?>">
                                <button class="btn btn-outline-secondary btn-sm" type="submit"><i class="bi-search"></i></button>
                            </div>
                        </div>

                        <!-- Region Filter (National vs International) -->
                        <div class="mb-3 border-top pt-3">
                            <label class="form-label small fw-bold text-muted">Region Type</label>
                            <select name="region" class="form-select form-select-sm" onchange="this.form.submit()">
                                <option value="">All Regions</option>
                                <option value="National" <?php echo $region === 'National' ? 'selected' : ''; ?>>National (India 🇮🇳)</option>
                                <option value="International" <?php echo $region === 'International' ? 'selected' : ''; ?>>International ✈️</option>
                            </select>
                        </div>

                        <!-- Categories Filter -->
                        <div class="mb-3 border-top pt-3">
                            <label class="form-label small fw-bold text-muted">Categories</label>
                            <select name="category" class="form-select form-select-sm" onchange="this.form.submit()">
                                <option value="">All Categories</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?php echo $cat['id']; ?>" <?php echo $category == $cat['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($cat['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Continent Filter -->
                        <div class="mb-3 border-top pt-3">
                            <label class="form-label small fw-bold text-muted">Continent</label>
                            <select name="continent" class="form-select form-select-sm" onchange="this.form.submit()">
                                <option value="">All Continents</option>
                                <?php foreach ($continents as $cont): ?>
                                    <option value="<?php echo htmlspecialchars($cont); ?>" <?php echo $continent === $cont ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($cont); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Budget Tier Filter -->
                        <div class="mb-3 border-top pt-3">
                            <label class="form-label small fw-bold text-muted">Budget Tier</label>
                            <select name="budget" class="form-select form-select-sm" onchange="this.form.submit()">
                                <option value="">Any Budget</option>
                                <option value="Budget" <?php echo $budget === 'Budget' ? 'selected' : ''; ?>>Budget</option>
                                <option value="Mid-Range" <?php echo $budget === 'Mid-Range' ? 'selected' : ''; ?>>Mid-Range</option>
                                <option value="Luxury" <?php echo $budget === 'Luxury' ? 'selected' : ''; ?>>Luxury</option>
                            </select>
                        </div>

                        <!-- Rating Filter -->
                        <div class="mb-4 border-top pt-3">
                            <label class="form-label small fw-bold text-muted">Minimum Rating</label>
                            <div class="d-flex flex-column gap-2">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="rating" id="rating_any" value="0" <?php echo $rating == 0.0 ? 'checked' : ''; ?> onchange="this.form.submit()">
                                    <label class="form-check-label small" for="rating_any">Any Rating</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="rating" id="rating_4" value="4" <?php echo $rating == 4.0 ? 'checked' : ''; ?> onchange="this.form.submit()">
                                    <label class="form-check-label small" for="rating_4">
                                        <i class="bi-star-fill text-warning"></i> 4.0 & above
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="rating" id="rating_3" value="3" <?php echo $rating == 3.0 ? 'checked' : ''; ?> onchange="this.form.submit()">
                                    <label class="form-check-label small" for="rating_3">
                                        <i class="bi-star-fill text-warning"></i> 3.0 & above
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- We carry forward the sorting selection hidden -->
                        <input type="hidden" name="sort" value="<?php echo htmlspecialchars($sort); ?>">

                        <button type="submit" class="btn btn-primary btn-sm w-100 rounded-pill py-2">
                            Apply Filters <i class="bi-check-lg ms-1"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- 2. Results List (col-lg-9) -->
        <div class="col-lg-9">
            <!-- Filter Bar & Sorting -->
            <div class="d-flex flex-wrap justify-content-between align-items-center bg-white p-3 rounded-3 shadow-sm mb-4 g-2">
                <div>
                    <span class="fw-bold text-dark"><?php echo count($destinations); ?></span> destinations found
                    <?php if ($search !== ''): ?>
                        for "<strong class="text-primary"><?php echo htmlspecialchars($search); ?></strong>"
                    <?php endif; ?>
                </div>
                
                <div class="d-flex align-items-center gap-2">
                    <span class="small text-muted text-nowrap">Sort By:</span>
                    <form action="destinations.php" method="GET" class="d-inline">
                        <!-- Keep filters while sorting -->
                        <?php if ($search !== ''): ?><input type="hidden" name="search" value="<?php echo htmlspecialchars($search); ?>"><?php endif; ?>
                        <?php if ($category !== ''): ?><input type="hidden" name="category" value="<?php echo htmlspecialchars($category); ?>"><?php endif; ?>
                        <?php if ($region !== ''): ?><input type="hidden" name="region" value="<?php echo htmlspecialchars($region); ?>"><?php endif; ?>
                        <?php if ($continent !== ''): ?><input type="hidden" name="continent" value="<?php echo htmlspecialchars($continent); ?>"><?php endif; ?>
                        <?php if ($budget !== ''): ?><input type="hidden" name="budget" value="<?php echo htmlspecialchars($budget); ?>"><?php endif; ?>
                        <?php if ($rating > 0.0): ?><input type="hidden" name="rating" value="<?php echo htmlspecialchars($rating); ?>"><?php endif; ?>

                        <select name="sort" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="rating_desc" <?php echo $sort === 'rating_desc' ? 'selected' : ''; ?>>Popularity (Rating)</option>
                            <option value="name_asc" <?php echo $sort === 'name_asc' ? 'selected' : ''; ?>>Name (A to Z)</option>
                            <option value="name_desc" <?php echo $sort === 'name_desc' ? 'selected' : ''; ?>>Name (Z to A)</option>
                            <option value="budget_asc" <?php echo $sort === 'budget_asc' ? 'selected' : ''; ?>>Budget (Low to High)</option>
                            <option value="budget_desc" <?php echo $sort === 'budget_desc' ? 'selected' : ''; ?>>Budget (High to Low)</option>
                        </select>
                    </form>
                </div>
            </div>

            <!-- Destinations Card Grid -->
            <?php if (!empty($destinations)): ?>
                <div class="row g-4">
                    <?php foreach ($destinations as $dest): 
                        $isNational = isset($dest['region_type']) ? ($dest['region_type'] === 'National') : ($dest['country'] === 'India');
                    ?>
                        <div class="col-md-6 col-lg-4">
                            <div class="destination-card">
                                <div class="card-img-wrapper">
                                    <img src="<?php echo htmlspecialchars(get_image_url($dest['image'])); ?>" alt="<?php echo htmlspecialchars($dest['name']); ?>" onerror="this.onerror=null; this.src='assets/images/default_destination.jpg';">
                                    <span class="card-badge bg-primary"><?php echo htmlspecialchars($dest['category_name'] ?? 'General'); ?></span>
                                    <span class="position-absolute bottom-0 start-0 m-2 badge <?php echo $isNational ? 'bg-success' : 'bg-info text-dark'; ?> rounded-pill">
                                        <?php echo $isNational ? '🇮🇳 National' : '✈️ International'; ?>
                                    </span>
                                    <span class="card-rating-badge">
                                        <i class="bi-star-fill"></i><?php echo number_format($dest['rating'], 1); ?>
                                    </span>
                                </div>
                                <div class="card-body-custom">
                                    <h5 class="card-dest-title text-truncate"><?php echo htmlspecialchars($dest['name']); ?></h5>
                                    <div class="card-dest-location mb-2">
                                        <i class="bi-geo-alt-fill text-danger me-1"></i>
                                        <span><?php echo htmlspecialchars($dest['country']); ?>, <?php echo htmlspecialchars($dest['continent']); ?></span>
                                    </div>
                                    <p class="small text-muted line-clamp-3 mb-0" style="min-height: 72px;">
                                        <?php echo htmlspecialchars(substr($dest['description'], 0, 110)) . '...'; ?>
                                    </p>
                                    <div class="card-dest-footer pt-3 mt-3">
                                        <div>
                                            <span class="text-muted small d-block">Budget Range</span>
                                            <span class="fw-bold text-danger"><?php echo htmlspecialchars($dest['price_range']); ?></span>
                                        </div>
                                        <a href="destination-detail.php?id=<?php echo $dest['id']; ?>" class="btn btn-sm btn-outline-primary rounded-pill px-3">Explore</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="text-center py-5 bg-white rounded-3 shadow-sm">
                    <i class="bi-emoji-frown text-muted display-1 mb-3"></i>
                    <h3 class="fw-bold text-dark">No Destinations Found</h3>
                    <p class="text-muted">Try adjusting your keyword filter or switching categories.</p>
                    <a href="destinations.php" class="btn btn-primary rounded-pill mt-3 px-4">Browse All Destinations</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
