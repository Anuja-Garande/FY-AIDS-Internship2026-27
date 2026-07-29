<?php
session_start();
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

if (!isAdminLoggedIn()) {
    redirect('login.php');
}

$db = Database::getInstance();

$settings = $db->query("SELECT * FROM settings")->fetchAll();
$settingsMap = [];
foreach ($settings as $s) {
    $settingsMap[$s['setting_key']] = $s['setting_value'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        flash('error', 'Invalid request.');
    } else {
        $fields = ['site_name', 'contact_email', 'phone', 'address', 'facebook_url', 'twitter_url', 'instagram_url', 'youtube_url', 'footer_text'];

        foreach ($fields as $field) {
            $value = trim($_POST[$field] ?? '');
            $exists = $db->query("SELECT id FROM settings WHERE `setting_key`=?", [$field])->fetch();
            if ($exists) {
                $db->update("UPDATE settings SET `setting_value`=? WHERE `setting_key`=?", [$value, $field]);
            } else {
                $db->insert("INSERT INTO settings (`setting_key`, `setting_value`) VALUES (?, ?)", [$field, $value]);
            }
        }

        if (!empty($_FILES['site_logo']['tmp_name'])) {
            $filename = uploadImage($_FILES['site_logo'], 'uploads');
            if ($filename) {
                $exists = $db->query("SELECT id FROM settings WHERE `setting_key`='site_logo'")->fetch();
                if ($exists) {
                    $db->update("UPDATE settings SET `setting_value`=? WHERE `setting_key`=?", [$filename, 'site_logo']);
                } else {
                    $db->insert("INSERT INTO settings (`setting_key`, `setting_value`) VALUES (?, ?)", ['site_logo', $filename]);
                }
            }
        }

        flash('success', 'Settings updated successfully!');
    }
    unset($_SESSION['csrf_token']);
    redirect('settings.php');
}

$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
$pageTitle = 'Settings';
include __DIR__ . '/includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="fw-bold mb-0">Site Settings</h5>
</div>

<?php if (isset($_GET['msg']) && $_GET['msg'] === 'updated'): ?>
<div class="alert alert-success alert-dismissible fade show"><i class="fas fa-check-circle me-2"></i>Settings updated successfully!<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data">
    <input type="hidden" name="csrf_token" value="<?= sanitize($_SESSION['csrf_token']) ?>">

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header"><h6 class="fw-bold mb-0">General Settings</h6></div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Site Name</label>
                        <input type="text" name="site_name" class="form-control" value="<?= sanitize($settingsMap['site_name'] ?? '') ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Contact Email</label>
                        <input type="email" name="contact_email" class="form-control" value="<?= sanitize($settingsMap['contact_email'] ?? '') ?>">
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Phone</label>
                            <input type="text" name="phone" class="form-control" value="<?= sanitize($settingsMap['phone'] ?? '') ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Site Logo</label>
                            <?php if (!empty($settingsMap['site_logo'])): ?>
                            <div class="mb-2"><img src="<?= BASE_URL ?>/uploads/<?= sanitize($settingsMap['site_logo']) ?>" style="max-height:60px;"></div>
                            <?php endif; ?>
                            <input type="file" name="site_logo" class="form-control" accept="image/*">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Address</label>
                        <textarea name="address" class="form-control" rows="2"><?= sanitize($settingsMap['address'] ?? '') ?></textarea>
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header"><h6 class="fw-bold mb-0">Social Media</h6></div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold"><i class="fab fa-facebook me-1 text-primary"></i>Facebook URL</label>
                            <input type="url" name="facebook_url" class="form-control" value="<?= sanitize($settingsMap['facebook_url'] ?? '') ?>" placeholder="https://facebook.com/...">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold"><i class="fab fa-twitter me-1 text-info"></i>Twitter URL</label>
                            <input type="url" name="twitter_url" class="form-control" value="<?= sanitize($settingsMap['twitter_url'] ?? '') ?>" placeholder="https://twitter.com/...">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold"><i class="fab fa-instagram me-1 text-danger"></i>Instagram URL</label>
                            <input type="url" name="instagram_url" class="form-control" value="<?= sanitize($settingsMap['instagram_url'] ?? '') ?>" placeholder="https://instagram.com/...">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold"><i class="fab fa-youtube me-1 text-danger"></i>YouTube URL</label>
                            <input type="url" name="youtube_url" class="form-control" value="<?= sanitize($settingsMap['youtube_url'] ?? '') ?>" placeholder="https://youtube.com/...">
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header"><h6 class="fw-bold mb-0">Footer</h6></div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Footer Text</label>
                        <textarea name="footer_text" class="form-control" rows="3"><?= sanitize($settingsMap['footer_text'] ?? '') ?></textarea>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary btn-lg"><i class="fas fa-save me-2"></i>Save Settings</button>
        </div>

        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header"><h6 class="fw-bold mb-0">Quick Info</h6></div>
                <div class="card-body">
                    <div class="mb-2"><small class="text-muted">Site Name:</small><div class="fw-semibold"><?= sanitize($settingsMap['site_name'] ?? 'Not Set') ?></div></div>
                    <div class="mb-2"><small class="text-muted">Contact Email:</small><div class="fw-semibold"><?= sanitize($settingsMap['contact_email'] ?? 'Not Set') ?></div></div>
                    <div class="mb-2"><small class="text-muted">Phone:</small><div class="fw-semibold"><?= sanitize($settingsMap['phone'] ?? 'Not Set') ?></div></div>
                </div>
            </div>
            <div class="card">
                <div class="card-header"><h6 class="fw-bold mb-0">Current Logo</h6></div>
                <div class="card-body text-center">
                    <?php if (!empty($settingsMap['site_logo'])): ?>
                    <img src="<?= BASE_URL ?>/uploads/<?= sanitize($settingsMap['site_logo']) ?>" class="img-fluid" style="max-height:100px;">
                    <?php else: ?>
                    <p class="text-muted mb-0">No logo uploaded</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</form>

<?php include __DIR__ . '/includes/footer.php'; ?>
