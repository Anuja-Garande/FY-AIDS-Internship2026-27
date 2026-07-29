<?php
$pageTitle = 'Dashboard';
require __DIR__ . '/includes/header.php';

$stats = [
    'states'       => $pdo->query("SELECT COUNT(*) FROM states")->fetchColumn(),
    'destinations' => $pdo->query("SELECT COUNT(*) FROM destinations")->fetchColumn(),
    'hotels'       => $pdo->query("SELECT COUNT(*) FROM hotels")->fetchColumn(),
    'restaurants'  => $pdo->query("SELECT COUNT(*) FROM restaurants")->fetchColumn(),
    'packages'     => $pdo->query("SELECT COUNT(*) FROM packages")->fetchColumn(),
    'bookings'     => $pdo->query("SELECT COUNT(*) FROM bookings")->fetchColumn(),
    'pending'      => $pdo->query("SELECT COUNT(*) FROM bookings WHERE status='Pending'")->fetchColumn(),
    'users'        => $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn(),
    'messages'     => $pdo->query("SELECT COUNT(*) FROM contact_messages WHERE status='New'")->fetchColumn(),
];

$recentBookings = $pdo->query("SELECT b.*, p.name AS package_name, u.full_name FROM bookings b
                                JOIN packages p ON p.package_id = b.package_id
                                JOIN users u ON u.user_id = b.user_id
                                ORDER BY b.created_at DESC LIMIT 6")->fetchAll();

$recentMessages = $pdo->query("SELECT * FROM contact_messages ORDER BY created_at DESC LIMIT 5")->fetchAll();

// ---- Chart data: bookings & revenue for the last 6 months ----
$monthlyStats = $pdo->query("
    SELECT DATE_FORMAT(created_at, '%Y-%m') AS ym, DATE_FORMAT(created_at, '%b %Y') AS label,
           COUNT(*) AS total_bookings, SUM(total_cost) AS revenue
    FROM bookings
    WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
    GROUP BY ym ORDER BY ym ASC
")->fetchAll();
$chartLabels = array_column($monthlyStats, 'label');
$chartBookings = array_column($monthlyStats, 'total_bookings');
$chartRevenue = array_map('floatval', array_column($monthlyStats, 'revenue'));

// ---- Chart data: booking status breakdown ----
$statusStats = $pdo->query("SELECT status, COUNT(*) AS total FROM bookings GROUP BY status")->fetchAll();
$statusLabels = array_column($statusStats, 'status');
$statusCounts = array_column($statusStats, 'total');
?>

<div class="row g-4 mb-4">
  <div class="col-lg-3 col-md-6">
    <div class="admin-card admin-stat">
      <div class="icon-box" style="background:#6b4226;"><i class="bi bi-flag"></i></div>
      <div><h4 class="mb-0"><?= $stats['states'] ?></h4><p class="text-muted small mb-0">States</p></div>
    </div>
  </div>
  <div class="col-lg-3 col-md-6">
    <div class="admin-card admin-stat">
      <div class="icon-box" style="background:var(--primary);"><i class="bi bi-geo-alt"></i></div>
      <div><h4 class="mb-0"><?= $stats['destinations'] ?></h4><p class="text-muted small mb-0">Destinations</p></div>
    </div>
  </div>
  <div class="col-lg-3 col-md-6">
    <div class="admin-card admin-stat">
      <div class="icon-box" style="background:var(--accent);"><i class="bi bi-suitcase-lg"></i></div>
      <div><h4 class="mb-0"><?= $stats['packages'] ?></h4><p class="text-muted small mb-0">Tour Packages</p></div>
    </div>
  </div>
  <div class="col-lg-3 col-md-6">
    <div class="admin-card admin-stat">
      <div class="icon-box" style="background:var(--secondary);"><i class="bi bi-journal-check"></i></div>
      <div><h4 class="mb-0"><?= $stats['bookings'] ?></h4><p class="text-muted small mb-0">Total Bookings (<?= $stats['pending'] ?> pending)</p></div>
    </div>
  </div>
  <div class="col-lg-3 col-md-6">
    <div class="admin-card admin-stat">
      <div class="icon-box" style="background:#3c7a89;"><i class="bi bi-people"></i></div>
      <div><h4 class="mb-0"><?= $stats['users'] ?></h4><p class="text-muted small mb-0">Registered Users</p></div>
    </div>
  </div>
</div>

<div class="row g-4 mb-4">
  <div class="col-lg-3 col-md-6">
    <div class="admin-card admin-stat">
      <div class="icon-box" style="background:#8C2F39;"><i class="bi bi-building"></i></div>
      <div><h4 class="mb-0"><?= $stats['hotels'] ?></h4><p class="text-muted small mb-0">Hotels</p></div>
    </div>
  </div>
  <div class="col-lg-3 col-md-6">
    <div class="admin-card admin-stat">
      <div class="icon-box" style="background:#E8A33D;"><i class="bi bi-cup-hot"></i></div>
      <div><h4 class="mb-0"><?= $stats['restaurants'] ?></h4><p class="text-muted small mb-0">Restaurants</p></div>
    </div>
  </div>
  <div class="col-lg-3 col-md-6">
    <div class="admin-card admin-stat">
      <div class="icon-box" style="background:#0E4F52;"><i class="bi bi-envelope"></i></div>
      <div><h4 class="mb-0"><?= $stats['messages'] ?></h4><p class="text-muted small mb-0">New Messages</p></div>
    </div>
  </div>
</div>

<div class="row g-4 mb-4">
  <div class="col-lg-8">
    <div class="admin-card">
      <h6 class="mb-3">Bookings &amp; Revenue — Last 6 Months</h6>
      <canvas id="bookingsChart" height="90"></canvas>
    </div>
  </div>
  <div class="col-lg-4">
    <div class="admin-card">
      <h6 class="mb-3">Booking Status Breakdown</h6>
      <canvas id="statusChart" height="90"></canvas>
    </div>
  </div>
</div>

<div class="row g-4">
  <div class="col-lg-7">
    <div class="admin-card">
      <div class="d-flex justify-content-between mb-3">
        <h6 class="mb-0">Recent Bookings</h6>
        <a href="/tourism-portal/admin/bookings.php" class="small">View all</a>
      </div>
      <div class="table-responsive">
        <table class="table table-admin align-middle">
          <thead><tr><th>Ref</th><th>User</th><th>Package</th><th>Date</th><th>Status</th></tr></thead>
          <tbody>
          <?php foreach ($recentBookings as $b): ?>
            <tr>
              <td><?= h($b['booking_reference']) ?></td>
              <td><?= h($b['full_name']) ?></td>
              <td><?= h($b['package_name']) ?></td>
              <td><?= date('d M Y', strtotime($b['travel_date'])) ?></td>
              <td><span class="badge-status badge-<?= strtolower($b['status']) ?>"><?= h($b['status']) ?></span></td>
            </tr>
          <?php endforeach; ?>
          <?php if (empty($recentBookings)): ?><tr><td colspan="5" class="text-muted text-center">No bookings yet.</td></tr><?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
  <div class="col-lg-5">
    <div class="admin-card">
      <div class="d-flex justify-content-between mb-3">
        <h6 class="mb-0">Recent Contact Messages</h6>
        <a href="/tourism-portal/admin/contact-messages.php" class="small">View all</a>
      </div>
      <?php foreach ($recentMessages as $m): ?>
        <div class="border-bottom py-2">
          <div class="d-flex justify-content-between">
            <strong class="small"><?= h($m['name']) ?></strong>
            <span class="text-muted small"><?= date('d M', strtotime($m['created_at'])) ?></span>
          </div>
          <p class="small text-muted mb-0"><?= h(mb_strimwidth($m['message'], 0, 70, '...')) ?></p>
        </div>
      <?php endforeach; ?>
      <?php if (empty($recentMessages)): ?><p class="text-muted text-center">No messages yet.</p><?php endif; ?>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
<script>
const bookingsCtx = document.getElementById('bookingsChart');
new Chart(bookingsCtx, {
  type: 'bar',
  data: {
    labels: <?= json_encode($chartLabels) ?>,
    datasets: [
      {
        label: 'Bookings',
        data: <?= json_encode($chartBookings) ?>,
        backgroundColor: '#0E4F52',
        borderRadius: 6,
        yAxisID: 'y'
      },
      {
        label: 'Revenue (₹)',
        data: <?= json_encode($chartRevenue) ?>,
        type: 'line',
        borderColor: '#E8A33D',
        backgroundColor: '#E8A33D',
        tension: 0.35,
        yAxisID: 'y1'
      }
    ]
  },
  options: {
    responsive: true,
    scales: {
      y: { beginAtZero: true, position: 'left', title: { display: true, text: 'Bookings' } },
      y1: { beginAtZero: true, position: 'right', grid: { drawOnChartArea: false }, title: { display: true, text: 'Revenue (₹)' } }
    }
  }
});

const statusCtx = document.getElementById('statusChart');
new Chart(statusCtx, {
  type: 'doughnut',
  data: {
    labels: <?= json_encode($statusLabels) ?>,
    datasets: [{
      data: <?= json_encode($statusCounts) ?>,
      backgroundColor: ['#E8A33D', '#0E4F52', '#8C2F39']
    }]
  },
  options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
});
</script>

<?php require __DIR__ . '/includes/footer.php'; ?>
