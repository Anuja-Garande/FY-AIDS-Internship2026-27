<?php
$pageTitle = 'Manage Bookings';
require __DIR__ . '/includes/header.php';

if (isset($_GET['status']) && isset($_GET['id'])) {
    $valid = ['Pending','Confirmed','Cancelled'];
    if (in_array($_GET['status'], $valid)) {
        $stmt = $pdo->prepare("UPDATE bookings SET status = ? WHERE booking_id = ?");
        $stmt->execute([$_GET['status'], (int)$_GET['id']]);
        setFlash('success', 'Booking status updated.');
    }
    redirect('/tourism-portal/admin/bookings.php');
}

$filterStatus = $_GET['filter'] ?? '';
$sql = "SELECT b.*, p.name AS package_name, u.full_name, u.email FROM bookings b
        JOIN packages p ON p.package_id = b.package_id
        JOIN users u ON u.user_id = b.user_id WHERE 1=1";
$params = [];
if ($filterStatus !== '') { $sql .= " AND b.status = ?"; $params[] = $filterStatus; }
$sql .= " ORDER BY b.created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$bookings = $stmt->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-3">
  <h6 class="mb-0">All Bookings</h6>
  <form method="get" class="d-flex gap-2">
    <select name="filter" class="form-select form-select-sm" onchange="this.form.submit()">
      <option value="">All Status</option>
      <option value="Pending" <?= $filterStatus==='Pending'?'selected':'' ?>>Pending</option>
      <option value="Confirmed" <?= $filterStatus==='Confirmed'?'selected':'' ?>>Confirmed</option>
      <option value="Cancelled" <?= $filterStatus==='Cancelled'?'selected':'' ?>>Cancelled</option>
    </select>
  </form>
</div>

<div class="admin-card">
  <div class="table-responsive">
    <table class="table table-admin align-middle">
      <thead><tr><th>Ref</th><th>Customer</th><th>Package</th><th>Travel Date</th><th>Travellers</th><th>Total</th><th>Status</th><th>Actions</th></tr></thead>
      <tbody>
        <?php foreach ($bookings as $b): ?>
        <tr>
          <td><?= h($b['booking_reference']) ?></td>
          <td><?= h($b['full_name']) ?><br><span class="text-muted small"><?= h($b['email']) ?></span></td>
          <td><?= h($b['package_name']) ?></td>
          <td><?= date('d M Y', strtotime($b['travel_date'])) ?></td>
          <td><?= $b['travellers'] ?></td>
          <td>₹<?= number_format($b['total_cost'],0) ?></td>
          <td><span class="badge-status badge-<?= strtolower($b['status']) ?>"><?= h($b['status']) ?></span></td>
          <td>
            <?php if ($b['status'] !== 'Confirmed'): ?>
              <a href="?status=Confirmed&id=<?= $b['booking_id'] ?>" class="btn btn-sm btn-outline-success" title="Confirm"><i class="bi bi-check2"></i></a>
            <?php endif; ?>
            <?php if ($b['status'] !== 'Cancelled'): ?>
              <a href="?status=Cancelled&id=<?= $b['booking_id'] ?>" class="btn btn-sm btn-outline-danger" title="Cancel" onclick="return confirm('Cancel this booking?')"><i class="bi bi-x-lg"></i></a>
            <?php endif; ?>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($bookings)): ?><tr><td colspan="8" class="text-center text-muted">No bookings found.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
