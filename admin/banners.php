<?php
session_start();
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

if (!isAdminLoggedIn()) {
    redirect('login.php');
}

$db = Database::getInstance();

if (isset($_POST['delete_banner'])) {
    $id = intval($_POST['id'] ?? 0);
    $banner = $db->query("SELECT image FROM banners WHERE id=?", [$id])->fetch();
    if ($banner && !empty($banner['image'])) {
        $path = BANNERS_UPLOAD . $banner['image'];
        if (file_exists($path)) unlink($path);
    }
    $db->query("DELETE FROM banners WHERE id=?", [$id]);
    flash('success', 'Banner deleted.');
    redirect('banners.php');
}

$banners = $db->query("SELECT * FROM banners ORDER BY sort_order ASC, id DESC")->fetchAll();

$pageTitle = 'Banners';
include __DIR__ . '/includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="fw-bold mb-0">Banners (<?= count($banners) ?>)</h5>
    <a href="<?= BASE_URL ?>/admin/add_banner.php" class="btn btn-primary"><i class="fas fa-plus me-2"></i>Add Banner</a>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr><th>ID</th><th>Image</th><th>Title</th><th>Type</th><th>Position</th><th>Status</th><th>Sort</th><th>Start</th><th>End</th><th>Actions</th></tr>
                </thead>
                <tbody>
                    <?php if (empty($banners)): ?>
                    <tr><td colspan="10" class="text-center text-muted py-4">No banners found</td></tr>
                    <?php endif; ?>
                    <?php foreach ($banners as $banner): ?>
                    <tr>
                        <td class="fw-semibold">#<?= $banner['id'] ?></td>
                        <td>
                            <?php if (!empty($banner['image'])): ?>
                            <img src="<?= BASE_URL ?>assets/uploads/banners/<?= sanitize($banner['image']) ?>" width="120" height="50" style="object-fit:cover;border-radius:6px;">
                            <?php else: ?>
                            <div class="bg-light d-flex align-items-center justify-content-center" style="width:120px;height:50px;border-radius:6px;"><i class="fas fa-image text-muted"></i></div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="fw-semibold"><?= sanitize($banner['title']) ?></div>
                            <?php if (!empty($banner['subtitle'])): ?><small class="text-muted"><?= sanitize($banner['subtitle']) ?></small><?php endif; ?>
                            <?php if (!empty($banner['badge'])): ?><br><small class="text-primary"><i class="fas fa-tag me-1"></i><?= sanitize($banner['badge']) ?></small><?php endif; ?>
                        </td>
                        <td><span class="badge bg-<?= $banner['type'] === 'offer' ? 'success' : ($banner['type'] === 'festival' ? 'warning' : 'primary') ?>"><?= sanitize(ucfirst($banner['type'])) ?></span></td>
                        <td><?= sanitize($banner['position'] ?? 'home') ?></td>
                        <td><button class="btn btn-sm btn-<?= $banner['status'] ? 'success' : 'danger' ?> btn-action" onclick="toggleStatus('banners',<?= $banner['id'] ?>,'status',this)"><?= $banner['status'] ? 'Active' : 'Inactive' ?></button></td>
                        <td><span class="badge bg-secondary"><?= $banner['sort_order'] ?></span></td>
                        <td><?= $banner['start_date'] ? date('M d', strtotime($banner['start_date'])) : '-' ?></td>
                        <td><?= $banner['end_date'] ? date('M d', strtotime($banner['end_date'])) : '-' ?></td>
                        <td>
                            <a href="<?= BASE_URL ?>/admin/edit_banner.php?id=<?= $banner['id'] ?>" class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i></a>
                            <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this banner?')">
                                <input type="hidden" name="delete_banner" value="1"><input type="hidden" name="id" value="<?= $banner['id'] ?>">
                                <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
