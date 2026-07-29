<?php
$pageTitle = 'Moderate Reviews';
require __DIR__ . '/includes/header.php';

if (isset($_GET['status']) && isset($_GET['id'])) {
    $valid = ['Pending','Approved','Rejected'];
    if (in_array($_GET['status'], $valid)) {
        $stmt = $pdo->prepare("UPDATE reviews SET status = ? WHERE review_id = ?");
        $stmt->execute([$_GET['status'], (int)$_GET['id']]);
        setFlash('success', 'Review status updated.');
    }
    redirect('/tourism-portal/admin/reviews.php');
}
if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM reviews WHERE review_id = ?");
    $stmt->execute([(int)$_GET['delete']]);
    setFlash('success', 'Review deleted.');
    redirect('/tourism-portal/admin/reviews.php');
}

$reviews = $pdo->query("SELECT r.*, u.full_name, u.email FROM reviews r
                         JOIN users u ON u.user_id = r.user_id ORDER BY r.created_at DESC")->fetchAll();

function itemLabel($pdo, $type, $id) {
    $table = $type === 'destination' ? 'destinations' : ($type === 'hotel' ? 'hotels' : 'restaurants');
    $idCol = $type . '_id';
    $stmt = $pdo->prepare("SELECT name FROM $table WHERE $idCol = ?");
    $stmt->execute([$id]);
    $r = $stmt->fetch();
    return $r ? $r['name'] : 'Unknown';
}
?>

<div class="admin-card">
  <div class="table-responsive">
    <table class="table table-admin align-middle">
      <thead><tr><th>User</th><th>On</th><th>Rating</th><th>Review</th><th>Status</th><th>Actions</th></tr></thead>
      <tbody>
        <?php foreach ($reviews as $r): ?>
        <tr>
          <td><?= h($r['full_name']) ?><br><span class="text-muted small"><?= h($r['email']) ?></span></td>
          <td><?= h(ucfirst($r['item_type'])) ?>: <?= h(itemLabel($pdo, $r['item_type'], $r['item_id'])) ?></td>
          <td><?= renderStars($r['rating']) ?></td>
          <td style="max-width:280px"><?= h(mb_strimwidth($r['review_text'], 0, 100, '...')) ?></td>
          <td><span class="badge-status badge-<?= strtolower($r['status']) ?>"><?= h($r['status']) ?></span></td>
          <td>
            <?php if ($r['status'] !== 'Approved'): ?>
              <a href="?status=Approved&id=<?= $r['review_id'] ?>" class="btn btn-sm btn-outline-success" title="Approve"><i class="bi bi-check2"></i></a>
            <?php endif; ?>
            <?php if ($r['status'] !== 'Rejected'): ?>
              <a href="?status=Rejected&id=<?= $r['review_id'] ?>" class="btn btn-sm btn-outline-warning" title="Reject"><i class="bi bi-eye-slash"></i></a>
            <?php endif; ?>
            <a href="?delete=<?= $r['review_id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this review?')"><i class="bi bi-trash"></i></a>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($reviews)): ?><tr><td colspan="6" class="text-center text-muted">No reviews yet.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
