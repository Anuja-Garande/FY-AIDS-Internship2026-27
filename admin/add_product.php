<?php
session_start();
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

if (!isAdminLoggedIn()) {
    redirect('login.php');
}

$db = Database::getInstance();

if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$categories = $db->query("SELECT id, name FROM categories WHERE status=1 ORDER BY name ASC")->fetchAll();
$brands = $db->query("SELECT id, name FROM brands WHERE status=1 ORDER BY name ASC")->fetchAll();

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $errors[] = 'Invalid request.';
    } else {
        $name = trim($_POST['name'] ?? '');
        $short_description = trim($_POST['short_description'] ?? '');
        $full_description = trim($_POST['full_description'] ?? '');
        $price = floatval($_POST['price'] ?? 0);
        $discount_price = !empty($_POST['discount_price']) ? floatval($_POST['discount_price']) : null;
        $category_id = intval($_POST['category_id'] ?? 0);
        $brand_id = !empty($_POST['brand_id']) ? intval($_POST['brand_id']) : null;
        $quantity = intval($_POST['quantity'] ?? 0);
        $sku = !empty($_POST['sku']) ? trim($_POST['sku']) : null;
        $rating = floatval($_POST['rating'] ?? 0);
        $status = intval($_POST['status'] ?? 1);
        $is_featured = isset($_POST['is_featured']) ? 1 : 0;
        $is_trending = isset($_POST['is_trending']) ? 1 : 0;
        $is_bestseller = isset($_POST['is_bestseller']) ? 1 : 0;
        $is_flash_sale = isset($_POST['is_flash_sale']) ? 1 : 0;
        $flash_sale_price = !empty($_POST['flash_sale_price']) ? floatval($_POST['flash_sale_price']) : null;
        $flash_sale_end = !empty($_POST['flash_sale_end']) ? $_POST['flash_sale_end'] : null;
        $meta_title = trim($_POST['meta_title'] ?? '');
        $meta_description = trim($_POST['meta_description'] ?? '');

        if (empty($name)) $errors[] = 'Product name is required.';
        if ($price <= 0) $errors[] = 'Price must be greater than 0.';
        if ($category_id <= 0) $errors[] = 'Please select a category.';

        if (empty($errors)) {
            $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name), '-'));
            $existing = $db->query("SELECT id FROM products WHERE slug=?", [$slug])->fetch();
            if ($existing) $slug .= '-' . time();

            $product_id = $db->insert("INSERT INTO products (name, slug, short_description, description, price, discount_price, category_id, brand_id, quantity, sku, rating, status, is_featured, is_trending, is_bestseller, is_flash_sale, flash_sale_price, flash_sale_end, meta_title, meta_description, image_url, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, '', NOW())", [
                $name, $slug, $short_description, $full_description, $price, $discount_price, $category_id, $brand_id, $quantity, $sku, $rating, $status, $is_featured, $is_trending, $is_bestseller, $is_flash_sale, $flash_sale_price, $flash_sale_end, $meta_title, $meta_description
            ]);

            if (!$product_id) {
                $errors[] = 'Failed to save product. Please check that the category and SKU are valid.';
            }

            if (empty($errors) && !empty($_FILES['images']['name'][0])) {
                $uploadDir = __DIR__ . '/../assets/uploads/products/';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
                $uploadedCount = 0;

                foreach ($_FILES['images']['tmp_name'] as $idx => $tmp) {
                    if (!empty($_FILES['images']['error'][$idx]) || $idx >= 5) continue;
                    $ext = strtolower(pathinfo($_FILES['images']['name'][$idx], PATHINFO_EXTENSION));
                    if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif'])) continue;
                    $filename = uniqid('prod_') . '.' . $ext;
                    if (move_uploaded_file($tmp, $uploadDir . $filename)) {
                        $isPrimary = ($idx === 0) ? 1 : 0;
                        $db->insert("INSERT INTO product_images (product_id, image, is_primary) VALUES (?, ?, ?)", [$product_id, $filename, $isPrimary]);
                        if ($idx === 0) {
                            $db->update("UPDATE products SET image_url = ? WHERE id = ?", [BASE_URL . 'assets/uploads/products/' . $filename, $product_id]);
                        }
                        $uploadedCount++;
                    }
                }

                if ($uploadedCount === 0) {
                    $errors[] = 'No images were uploaded. Check file types (jpg, png, webp, gif).';
                }
            }

            if (empty($errors)) {
                if (!empty($_POST['spec_names']) && !empty($_POST['spec_values'])) {
                    foreach ($_POST['spec_names'] as $sIdx => $specName) {
                        $specName = trim($specName);
                        $specValue = trim($_POST['spec_values'][$sIdx] ?? '');
                        if (!empty($specName) && !empty($specValue)) {
                            $db->insert("INSERT INTO product_specifications (product_id, spec_name, spec_value) VALUES (?, ?, ?)", [$product_id, $specName, $specValue]);
                        }
                    }
                }

                unset($_SESSION['csrf_token']);
                flash('success', 'Product added successfully!');
                redirect('products.php');
            }
        }
    }
}

$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
$pageTitle = 'Add Product';
include __DIR__ . '/includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="fw-bold mb-0">Add New Product</h5>
    <a href="<?= BASE_URL ?>/admin/products.php" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Back to Products</a>
</div>

<?php if (!empty($errors)): ?>
<div class="alert alert-danger">
    <ul class="mb-0"><?php foreach ($errors as $e): ?><li><?= sanitize($e) ?></li><?php endforeach; ?></ul>
</div>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data" id="productForm">
    <input type="hidden" name="csrf_token" value="<?= sanitize($_SESSION['csrf_token']) ?>">

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header"><h6 class="fw-bold mb-0">Product Information</h6></div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Product Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" required value="<?= sanitize($_POST['name'] ?? '') ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Short Description</label>
                        <textarea name="short_description" class="form-control" rows="2"><?= sanitize($_POST['short_description'] ?? '') ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Full Description</label>
                        <textarea name="full_description" class="form-control" rows="6"><?= sanitize($_POST['full_description'] ?? '') ?></textarea>
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header"><h6 class="fw-bold mb-0">Pricing & Inventory</h6></div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Price <span class="text-danger">*</span></label>
                            <input type="number" name="price" class="form-control" step="0.01" min="0" required value="<?= sanitize($_POST['price'] ?? '') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Discount Price</label>
                            <input type="number" name="discount_price" class="form-control" step="0.01" min="0" value="<?= sanitize($_POST['discount_price'] ?? '') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Quantity <span class="text-danger">*</span></label>
                            <input type="number" name="quantity" class="form-control" min="0" required value="<?= sanitize($_POST['quantity'] ?? 0) ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">SKU</label>
                            <input type="text" name="sku" class="form-control" value="<?= sanitize($_POST['sku'] ?? '') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Rating</label>
                            <input type="number" name="rating" class="form-control" step="0.1" min="0" max="5" value="<?= sanitize($_POST['rating'] ?? 0) ?>">
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0">Specifications</h6>
                    <button type="button" class="btn btn-sm btn-primary" onclick="addSpec()"><i class="fas fa-plus me-1"></i>Add Row</button>
                </div>
                <div class="card-body">
                    <div id="specContainer">
                        <div class="row g-2 mb-2 spec-row">
                            <div class="col-md-5"><input type="text" name="spec_names[]" class="form-control form-control-sm" placeholder="Specification name"></div>
                            <div class="col-md-5"><input type="text" name="spec_values[]" class="form-control form-control-sm" placeholder="Value"></div>
                            <div class="col-md-2"><button type="button" class="btn btn-sm btn-outline-danger w-100" onclick="removeSpec(this)"><i class="fas fa-times"></i></button></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header"><h6 class="fw-bold mb-0">SEO</h6></div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Meta Title</label>
                        <input type="text" name="meta_title" class="form-control" value="<?= sanitize($_POST['meta_title'] ?? '') ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Meta Description</label>
                        <textarea name="meta_description" class="form-control" rows="2"><?= sanitize($_POST['meta_description'] ?? '') ?></textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header"><h6 class="fw-bold mb-0">Publish</h6></div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Status</label>
                        <select name="status" class="form-select">
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>
                    <div class="form-check form-switch mb-2">
                        <input class="form-check-input" type="checkbox" name="is_featured" id="is_featured">
                        <label class="form-check-label" for="is_featured">Featured</label>
                    </div>
                    <div class="form-check form-switch mb-2">
                        <input class="form-check-input" type="checkbox" name="is_trending" id="is_trending">
                        <label class="form-check-label" for="is_trending">Trending</label>
                    </div>
                    <div class="form-check form-switch mb-2">
                        <input class="form-check-input" type="checkbox" name="is_bestseller" id="is_bestseller">
                        <label class="form-check-label" for="is_bestseller">Bestseller</label>
                    </div>
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="is_flash_sale" id="is_flash_sale" onchange="document.getElementById('flashSaleFields').style.display=this.checked?'block':'none'">
                        <label class="form-check-label" for="is_flash_sale">Flash Sale</label>
                    </div>
                    <div id="flashSaleFields" style="display:none;">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Flash Sale Price</label>
                            <input type="number" name="flash_sale_price" class="form-control" step="0.01" min="0">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Flash Sale End Date</label>
                            <input type="datetime-local" name="flash_sale_end" class="form-control">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary w-100"><i class="fas fa-save me-1"></i>Save Product</button>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header"><h6 class="fw-bold mb-0">Category & Brand</h6></div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Category <span class="text-danger">*</span></label>
                        <select name="category_id" class="form-select" required>
                            <option value="">Select Category</option>
                            <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>"><?= sanitize($cat['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Brand</label>
                        <select name="brand_id" class="form-select">
                            <option value="">Select Brand</option>
                            <?php foreach ($brands as $brand): ?>
                            <option value="<?= $brand['id'] ?>"><?= sanitize($brand['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header"><h6 class="fw-bold mb-0">Product Images</h6></div>
                <div class="card-body">
                    <input type="file" name="images[]" class="form-control mb-2" multiple accept="image/*" id="imageInput" onchange="previewImages(this)">
                    <small class="text-muted">Upload up to 5 images. First image will be primary.</small>
                    <div id="imagePreview" class="mt-3 row g-2"></div>
                </div>
            </div>
        </div>
    </div>
</form>

<?php
$extraScripts = '<script>
function addSpec() {
    var html = \'<div class="row g-2 mb-2 spec-row"><div class="col-md-5"><input type="text" name="spec_names[]" class="form-control form-control-sm" placeholder="Specification name"></div><div class="col-md-5"><input type="text" name="spec_values[]" class="form-control form-control-sm" placeholder="Value"></div><div class="col-md-2"><button type="button" class="btn btn-sm btn-outline-danger w-100" onclick="removeSpec(this)"><i class="fas fa-times"></i></button></div></div>\';
    document.getElementById("specContainer").insertAdjacentHTML("beforeend", html);
}
function removeSpec(btn) { btn.closest(".spec-row").remove(); }
function previewImages(input) {
    var container = document.getElementById("imagePreview");
    container.innerHTML = "";
    Array.from(input.files).slice(0,5).forEach(function(file, idx) {
        var reader = new FileReader();
        reader.onload = function(e) {
            container.insertAdjacentHTML("beforeend", \'<div class="col-4"><div class="position-relative"><img src="\' + e.target.result + \'" class="img-fluid rounded" style="height:100px;width:100%;object-fit:cover;"><small class="text-muted d-block text-center">\' + (idx === 0 ? "Primary" : "Image " + (idx+1)) + \'</small></div></div>\');
        };
        reader.readAsDataURL(file);
    });
}
</script>';
include __DIR__ . '/includes/footer.php';
?>
