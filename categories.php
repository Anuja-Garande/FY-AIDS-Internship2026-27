<?php
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'includes/functions.php';
require_once 'includes/functions_product.php';

$page_title = 'All Categories';
$db = Database::getInstance();
$categories = $db->fetchAll("SELECT c.*,
  (SELECT COUNT(*) FROM products p
   WHERE p.status = 1
   AND (p.category_id = c.id OR p.category_id IN (SELECT id FROM categories WHERE parent_id = c.id AND status = 1))
  ) as product_count
FROM categories c WHERE c.status = 1 ORDER BY c.name ASC");
?>
<?php include 'includes/header.php'; ?>

<style>
.category-card-lg { border: none; border-radius: 16px; overflow: hidden; transition: all 0.3s; box-shadow: 0 2px 10px rgba(0,0,0,0.06); }
.category-card-lg:hover { transform: translateY(-8px); box-shadow: 0 12px 40px rgba(0,0,0,0.12); }
.category-card-lg .cat-img { height: 150px; object-fit: cover; }
.category-card-lg .cat-body { padding: 20px; text-align: center; }
.category-card-lg .cat-icon { width: 70px; height: 70px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 12px; }
.featured-badge { position: absolute; top: 12px; right: 12px; }
</style>

<div class="container py-4">
    <div class="text-center mb-5">
        <h2 class="fw-bold">All Categories</h2>
        <p class="text-muted">Browse through our wide range of categories</p>
    </div>

    <?php if (empty($categories)): ?>
    <div class="text-center py-5">
        <i class="fas fa-folder-open fa-4x text-muted mb-3"></i>
        <h4>No categories available</h4>
        <p class="text-muted">Check back soon for new categories.</p>
    </div>
    <?php else: ?>
    <div class="row g-4">
        <?php foreach ($categories as $cat): ?>
        <div class="col-sm-6 col-md-4 col-lg-3">
            <a href="products.php?category=<?= htmlspecialchars($cat['slug']) ?>" class="text-decoration-none">
                <div class="card category-card-lg h-100 position-relative">
                    <?php if (!empty($cat['image'])): ?>
                        <img src="<?= BASE_URL ?>assets/uploads/categories/<?= htmlspecialchars($cat['image']) ?>" class="cat-img" alt="<?= htmlspecialchars($cat['name']) ?>">
                    <?php else: ?>
                        <div class="cat-img bg-gradient d-flex align-items-center justify-content-center" style="background:linear-gradient(135deg,#667eea,#764ba2);">
                            <i class="fas fa-tag fa-3x text-white"></i>
                        </div>
                    <?php endif; ?>
                    <div class="cat-body">
                        <h5 class="fw-bold text-dark"><?= htmlspecialchars($cat['name']) ?></h5>
                        <p class="text-muted mb-2"><?= $cat['product_count'] ?> Products</p>
                        <span class="btn btn-sm btn-outline-primary">Browse <i class="fas fa-arrow-right ms-1"></i></span>
                    </div>
                </div>
            </a>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
