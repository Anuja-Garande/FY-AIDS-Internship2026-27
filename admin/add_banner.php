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

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $errors[] = 'Invalid request.';
    } else {
        $title = trim($_POST['title'] ?? '');
        $subtitle = trim($_POST['subtitle'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $badge = trim($_POST['badge'] ?? '');
        $badge_icon = trim($_POST['badge_icon'] ?? 'fas fa-tag');
        $button_text = trim($_POST['button_text'] ?? '');
        $button_icon = trim($_POST['button_icon'] ?? 'fas fa-arrow-right');
        $gradient = trim($_POST['gradient'] ?? 'linear-gradient(135deg, #F472B6 0%, #E879F9 50%, #FB7185 100%)');
        $link = trim($_POST['link'] ?? '');
        $type = $_POST['type'] ?? 'home';
        $position = trim($_POST['position'] ?? 'home');
        $status = intval($_POST['status'] ?? 1);
        $sort_order = intval($_POST['sort_order'] ?? 0);
        $start_date = !empty($_POST['start_date']) ? $_POST['start_date'] : null;
        $end_date = !empty($_POST['end_date']) ? $_POST['end_date'] : null;

        if (empty($title)) $errors[] = 'Title is required.';
        if (empty($_FILES['image']['tmp_name'])) $errors[] = 'Image is required.';

        if (empty($errors)) {
            $upload = uploadImage($_FILES['image'], 'banners');
            if (!is_array($upload) || !$upload['success']) {
                $errors[] = is_array($upload) ? $upload['message'] : 'Failed to upload image.';
            } else {
                $db->insert("INSERT INTO banners (title, subtitle, description, badge, badge_icon, button_text, button_icon, gradient, image, link, type, position, status, sort_order, start_date, end_date, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())", [
                    $title, $subtitle, $description, $badge, $badge_icon, $button_text, $button_icon, $gradient, $upload['filename'], $link, $type, $position, $status, $sort_order, $start_date, $end_date
                ]);
                unset($_SESSION['csrf_token']);
                flash('success', 'Banner added successfully!');
                redirect('banners.php');
            }
        }
    }
}

$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
$pageTitle = 'Add Banner';
include __DIR__ . '/includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="fw-bold mb-0">Add Banner</h5>
    <a href="<?= BASE_URL ?>/admin/banners.php" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Back</a>
</div>

<?php if (!empty($errors)): ?>
<div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errors as $e): ?><li><?= sanitize($e) ?></li><?php endforeach; ?></ul></div>
<?php endif; ?>

<div class="row">
    <div class="col-lg-8">
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?= sanitize($_SESSION['csrf_token']) ?>">
            <div class="card mb-4">
                <div class="card-header"><h6 class="fw-bold mb-0">Banner Details</h6></div>
                <div class="card-body">
                    <div class="mb-3"><label class="form-label fw-semibold">Title <span class="text-danger">*</span></label><input type="text" name="title" class="form-control" required value="<?= sanitize($_POST['title'] ?? '') ?>"></div>
                    <div class="mb-3"><label class="form-label fw-semibold">Subtitle</label><input type="text" name="subtitle" class="form-control" value="<?= sanitize($_POST['subtitle'] ?? '') ?>"></div>
                    <div class="mb-3"><label class="form-label fw-semibold">Description</label><textarea name="description" class="form-control" rows="2"><?= sanitize($_POST['description'] ?? '') ?></textarea></div>
                    <div class="mb-3"><label class="form-label fw-semibold">Image <span class="text-danger">*</span></label><input type="file" name="image" class="form-control" accept="image/*" required><div id="bannerImgPreview" class="mt-2"></div></div>
                    <div class="mb-3"><label class="form-label fw-semibold">Link URL</label><input type="text" name="link" class="form-control" placeholder="products.php, https://example.com, or leave empty" value="<?= sanitize($_POST['link'] ?? '') ?>"></div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-4"><label class="form-label fw-semibold">Type</label><select name="type" class="form-select" id="bannerType"><option value="home">Home</option><option value="offer">Offer / Carousel Slide</option><option value="festival">Festival</option></select></div>
                        <div class="col-md-4"><label class="form-label fw-semibold">Position</label><select name="position" class="form-select" id="bannerPosition"><option value="home">Home</option><option value="carousel">Carousel</option><option value="sidebar">Sidebar</option><option value="footer">Footer</option></select></div>
                        <div class="col-md-4"><label class="form-label fw-semibold">Sort Order</label><input type="number" name="sort_order" class="form-control" value="0" min="0"></div>
                    </div>
                    <div id="carouselFields" style="display:none;">
                        <hr><h6 class="fw-bold text-muted mb-3">Carousel Slide Settings</h6>
                        <div class="row g-3 mb-3">
                            <div class="col-md-4"><label class="form-label fw-semibold">Badge Text</label><input type="text" name="badge" class="form-control" placeholder="e.g. Shoes Sale" value="<?= sanitize($_POST['badge'] ?? '') ?>"></div>
                            <div class="col-md-4"><label class="form-label fw-semibold">Badge Icon</label><input type="text" name="badge_icon" class="form-control" placeholder="fas fa-tag" value="<?= sanitize($_POST['badge_icon'] ?? 'fas fa-tag') ?>"></div>
                            <div class="col-md-4"><label class="form-label fw-semibold">Button Text</label><input type="text" name="button_text" class="form-control" placeholder="Shop Now" value="<?= sanitize($_POST['button_text'] ?? '') ?>"></div>
                        </div>
                        <div class="row g-3 mb-3">
                            <div class="col-md-4"><label class="form-label fw-semibold">Button Icon</label><input type="text" name="button_icon" class="form-control" placeholder="fas fa-arrow-right" value="<?= sanitize($_POST['button_icon'] ?? 'fas fa-arrow-right') ?>"></div>
                            <div class="col-md-8"><label class="form-label fw-semibold">Gradient</label><select name="gradient" class="form-select">
                                <option value="linear-gradient(135deg, #F472B6 0%, #DB2777 50%, #FB7185 100%)">Pink Rose</option>
                                <option value="linear-gradient(135deg, #E879F9 0%, #7C3AED 50%, #A855F7 100%)">Purple</option>
                                <option value="linear-gradient(135deg, #FB7185 0%, #FBBF24 50%, #F97316 100%)">Orange</option>
                                <option value="linear-gradient(135deg, #34D399 0%, #10B981 50%, #059669 100%)">Green</option>
                                <option value="linear-gradient(135deg, #60A5FA 0%, #3B82F6 50%, #2563EB 100%)">Blue</option>
                                <option value="linear-gradient(135deg, #0EA5E9 0%, #6366F1 50%, #8B5CF6 100%)">Indigo</option>
                                <option value="linear-gradient(135deg, #F43F5E 0%, #E11D48 50%, #BE123C 100%)">Red</option>
                            </select></div>
                        </div>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-4"><label class="form-label fw-semibold">Start Date</label><input type="date" name="start_date" class="form-control"></div>
                        <div class="col-md-4"><label class="form-label fw-semibold">End Date</label><input type="date" name="end_date" class="form-control"></div>
                        <div class="col-md-4"><label class="form-label fw-semibold">Status</label><select name="status" class="form-select"><option value="1">Active</option><option value="0">Inactive</option></select></div>
                    </div>
                </div>
            </div>
            <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Save Banner</button>
        </form>
    </div>
</div>

<?php
$extraScripts = '<script>
document.querySelector("input[name=image]").addEventListener("change", function(e) {
    var preview = document.getElementById("bannerImgPreview");
    preview.innerHTML = "";
    if (e.target.files[0]) {
        var reader = new FileReader();
        reader.onload = function(ev) { preview.innerHTML = \'<img src="\' + ev.target.result + \'" class="img-fluid rounded" style="max-height:200px;">\'; };
        reader.readAsDataURL(e.target.files[0]);
    }
});
function toggleCarouselFields() {
    var v = document.getElementById("bannerType").value;
    document.getElementById("carouselFields").style.display = (v === "offer") ? "block" : "none";
    if (v === "offer") document.getElementById("bannerPosition").value = "carousel";
}
document.getElementById("bannerType").addEventListener("change", toggleCarouselFields);
toggleCarouselFields();
</script>';
include __DIR__ . '/includes/footer.php';
?>
