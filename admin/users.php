<?php
$pageTitle = 'Manage Users';
require __DIR__ . '/includes/header.php';

if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM users WHERE user_id = ?");
    $stmt->execute([(int)$_GET['delete']]);
    setFlash('success', 'User account deleted.');
    redirect('/tourism-portal/admin/users.php');
}

$users = $pdo->query("SELECT u.*,
                       (SELECT COUNT(*) FROM bookings b WHERE b.user_id = u.user_id) AS booking_count,
                       (SELECT COUNT(*) FROM reviews r WHERE r.user_id = u.user_id) AS review_count
                       FROM users u ORDER BY u.created_at DESC")->fetchAll();
?>

<div class="admin-card">
  <div class="table-responsive">
    <table class="table table-admin align-middle">
      <thead><tr><th>#</th><th>Name</th><th>Email</th><th>Contact</th><th>Bookings</th><th>Reviews</th><th>Joined</th><th>Actions</th></tr></thead>
      <tbody>
        <?php foreach ($users as $u): ?>
        <tr>
          <td><?= $u['user_id'] ?></td>
          <td><?= h($u['full_name']) ?></td>
          <td><?= h($u['email']) ?></td>
          <td><?= h($u['contact_number']) ?></td>
          <td><?= $u['booking_count'] ?></td>
          <td><?= $u['review_count'] ?></td>
          <td><?= date('d M Y', strtotime($u['created_at'])) ?></td>
          <td>
            <a href="?delete=<?= $u['user_id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this user? This will also remove their bookings, reviews and wishlist.')"><i class="bi bi-trash"></i></a>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($users)): ?><tr><td colspan="8" class="text-center text-muted">No registered users yet.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
