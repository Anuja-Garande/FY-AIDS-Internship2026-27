<?php
$pageTitle = 'Contact Messages';
require __DIR__ . '/includes/header.php';

if (isset($_GET['status']) && isset($_GET['id'])) {
    $valid = ['New','Read','Responded'];
    if (in_array($_GET['status'], $valid)) {
        $stmt = $pdo->prepare("UPDATE contact_messages SET status = ? WHERE message_id = ?");
        $stmt->execute([$_GET['status'], (int)$_GET['id']]);
    }
    redirect('/tourism-portal/admin/contact-messages.php');
}
if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM contact_messages WHERE message_id = ?");
    $stmt->execute([(int)$_GET['delete']]);
    setFlash('success', 'Message deleted.');
    redirect('/tourism-portal/admin/contact-messages.php');
}

$messages = $pdo->query("SELECT * FROM contact_messages ORDER BY created_at DESC")->fetchAll();
?>

<div class="admin-card">
  <div class="table-responsive">
    <table class="table table-admin align-middle">
      <thead><tr><th>Name</th><th>Email</th><th>Subject</th><th>Message</th><th>Date</th><th>Status</th><th>Actions</th></tr></thead>
      <tbody>
        <?php foreach ($messages as $m): ?>
        <tr>
          <td><?= h($m['name']) ?></td>
          <td><?= h($m['email']) ?></td>
          <td><?= h($m['subject']) ?></td>
          <td style="max-width:260px"><?= h(mb_strimwidth($m['message'], 0, 90, '...')) ?></td>
          <td><?= date('d M Y', strtotime($m['created_at'])) ?></td>
          <td><span class="badge-status badge-<?= strtolower($m['status']) ?>"><?= h($m['status']) ?></span></td>
          <td>
            <?php if ($m['status'] === 'New'): ?>
              <a href="?status=Read&id=<?= $m['message_id'] ?>" class="btn btn-sm btn-outline-secondary" title="Mark as Read"><i class="bi bi-eye"></i></a>
            <?php endif; ?>
            <?php if ($m['status'] !== 'Responded'): ?>
              <a href="?status=Responded&id=<?= $m['message_id'] ?>" class="btn btn-sm btn-outline-success" title="Mark as Responded"><i class="bi bi-check2"></i></a>
            <?php endif; ?>
            <a href="?delete=<?= $m['message_id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this message?')"><i class="bi bi-trash"></i></a>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($messages)): ?><tr><td colspan="7" class="text-center text-muted">No messages yet.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
