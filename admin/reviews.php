<?php
session_start();
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

if (!isAdminLoggedIn()) {
    redirect('login.php');
}

$db = Database::getInstance();

if (isset($_POST['approve_review'])) {
    $id = intval($_POST['id'] ?? 0);
    $is_approved = intval($_POST['is_approved'] ?? 0);
    $db->update("UPDATE reviews SET is_approved=? WHERE id=?", [$is_approved, $id]);
    flash('success', $is_approved ? 'Review approved.' : 'Review rejected.');
    redirect('reviews.php');
}

if (isset($_POST['reply_review'])) {
    $id = intval($_POST['id'] ?? 0);
    $reply = trim($_POST['admin_reply'] ?? '');
    $db->update("UPDATE reviews SET admin_reply=? WHERE id=?", [$reply, $id]);
    flash('success', 'Reply saved.');
    redirect('reviews.php');
}

if (isset($_POST['delete_review'])) {
    $id = intval($_POST['id'] ?? 0);
    $db->query("DELETE FROM reviews WHERE id=?", [$id]);
    flash('success', 'Review deleted.');
    redirect('reviews.php');
}

$filter = $_GET['filter'] ?? '';
$page = max(1, intval($_GET['page'] ?? 1));
$per_page = 15;
$offset = ($page - 1) * $per_page;

$where = "1=1";
$params = [];

if ($filter === 'approved') {
    $where .= " AND r.is_approved = 1";
} elseif ($filter === 'pending') {
    $where .= " AND r.is_approved = 0";
}

$total = $db->query("SELECT COUNT(*) as count FROM reviews r WHERE $where", $params)->fetch()['count'];
$total_pages = max(1, ceil($total / $per_page));

$reviews = $db->query("SELECT r.*, u.name as user_name, p.name as product_name FROM reviews r LEFT JOIN users u ON r.user_id=u.id LEFT JOIN products p ON r.product_id=p.id WHERE $where ORDER BY r.id DESC LIMIT $per_page OFFSET $offset", $params)->fetchAll();

$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
$pageTitle = 'Reviews';
include __DIR__ . '/includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="fw-bold mb-0">Reviews (<?= number_format($total) ?>)</h5>
    <div class="d-flex gap-2">
        <a href="<?= BASE_URL ?>/admin/reviews.php" class="btn btn-<?= $filter === '' ? 'primary' : 'outline-primary' ?> btn-sm">All</a>
        <a href="<?= BASE_URL ?>/admin/reviews.php?filter=approved" class="btn btn-<?= $filter === 'approved' ? 'success' : 'outline-success' ?> btn-sm">Approved</a>
        <a href="<?= BASE_URL ?>/admin/reviews.php?filter=pending" class="btn btn-<?= $filter === 'pending' ? 'warning' : 'outline-warning' ?> btn-sm">Pending</a>
    </div>
</div>

<div class="row g-3">
    <?php if (empty($reviews)): ?>
    <div class="col-12"><div class="card"><div class="card-body text-center text-muted py-5">No reviews found</div></div></div>
    <?php endif; ?>
    <?php foreach ($reviews as $review): ?>
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div>
                        <h6 class="fw-bold mb-0"><?= sanitize($review['user_name'] ?? 'Anonymous') ?></h6>
                        <small class="text-muted">Product: <?= sanitize($review['product_name'] ?? 'N/A') ?></small>
                    </div>
                    <span class="badge bg-<?= $review['is_approved'] ? 'success' : 'warning' ?>"><?= $review['is_approved'] ? 'Approved' : 'Pending' ?></span>
                </div>
                <div class="mb-2">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                    <i class="fas fa-star <?= $i <= $review['rating'] ? 'text-warning' : 'text-muted' ?>" style="font-size:14px;"></i>
                    <?php endfor; ?>
                    <span class="ms-1 fw-semibold" style="font-size:13px;"><?= $review['rating'] ?>/5</span>
                </div>
                <p class="mb-2" style="font-size:14px;"><?= sanitize($review['comment'] ?? '') ?></p>
                <?php if (!empty($review['admin_reply'])): ?>
                <div class="bg-light p-2 rounded mb-2" style="font-size:13px;"><strong>Admin Reply:</strong> <?= sanitize($review['admin_reply']) ?></div>
                <?php endif; ?>
                <small class="text-muted"><?= date('M d, Y', strtotime($review['created_at'])) ?></small>
            </div>
            <div class="card-footer bg-white">
                <div class="d-flex gap-2 flex-wrap">
                    <form method="POST" class="d-inline">
                        <input type="hidden" name="approve_review" value="1">
                        <input type="hidden" name="id" value="<?= $review['id'] ?>">
                        <input type="hidden" name="is_approved" value="<?= $review['is_approved'] ? 0 : 1 ?>">
                        <button type="submit" class="btn btn-sm btn-<?= $review['is_approved'] ? 'outline-warning' : 'outline-success' ?>">
                            <i class="fas fa-<?= $review['is_approved'] ? 'times' : 'check' ?> me-1"></i><?= $review['is_approved'] ? 'Reject' : 'Approve' ?>
                        </button>
                    </form>
                    <button class="btn btn-sm btn-outline-info" onclick="document.getElementById('replyForm<?= $review['id'] ?>').style.display='block'"><i class="fas fa-reply me-1"></i>Reply</button>
                    <form method="POST" class="d-inline" onsubmit="return confirm('Delete this review?')">
                        <input type="hidden" name="delete_review" value="1"><input type="hidden" name="id" value="<?= $review['id'] ?>">
                        <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                    </form>
                </div>
                <div id="replyForm<?= $review['id'] ?>" style="display:none;" class="mt-3">
                    <form method="POST">
                        <input type="hidden" name="reply_review" value="1"><input type="hidden" name="id" value="<?= $review['id'] ?>">
                        <textarea name="admin_reply" class="form-control form-control-sm mb-2" rows="2" placeholder="Write your reply..."><?= sanitize($review['admin_reply'] ?? '') ?></textarea>
                        <button type="submit" class="btn btn-sm btn-primary">Save Reply</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<div class="d-flex justify-content-center mt-3">
    <nav><ul class="pagination pagination-sm mb-0">
        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
        <li class="page-item <?= $i === $page ? 'active' : '' ?>"><a class="page-link" href="?page=<?= $i ?>&filter=<?= urlencode($filter) ?>"><?= $i ?></a></li>
        <?php endfor; ?>
    </ul></nav>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
