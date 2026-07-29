<?php
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'includes/functions.php';

$ref = trim($_GET['ref'] ?? '');
if (!$ref) {
    redirect('book-flight.php');
}

$db = Database::getInstance();
$flight = $db->fetch("SELECT * FROM flight_bookings WHERE booking_ref = ?", [$ref]);

if (!$flight) {
    redirect('book-flight.php');
}

if (isLoggedIn() && $flight['user_id'] != $_SESSION['user_id']) {
    redirect('book-flight.php');
}

$page_title = 'Flight Ticket - ' . $flight['booking_ref'];

$class_labels = [
    'economy' => 'Economy',
    'premium_economy' => 'Premium Economy',
    'business' => 'Business Class',
    'first' => 'First Class',
];
$class_label = $class_labels[$flight['travel_class']] ?? 'Economy';

$class_colors = [
    'economy' => '#2196F3',
    'premium_economy' => '#9C27B0',
    'business' => '#D4A017',
    'first' => '#FF8C00',
];
$class_color = $class_colors[$flight['travel_class']] ?? '#D4A017';

$trip_label = $flight['trip_type'] === 'roundtrip' ? 'Round Trip' : 'One Way';
$booking_date = date('d M Y, h:i A', strtotime($flight['created_at']));
$pnr = strtoupper(substr(md5($flight['booking_ref']), 0, 6));

$barcode_str = $flight['booking_ref'] . '|' . $pnr . '|' . $flight['from_city'] . '|' . $flight['to_city'] . '|' . $flight['departure_date'];
?>
<?php require_once 'includes/header.php'; ?>

<style>
  .ticket-wrapper {
    max-width: 760px;
    margin: 0 auto 60px;
  }
  .ticket {
    background: var(--bg-card);
    border-radius: 20px;
    border: 1px solid var(--border-color);
    overflow: hidden;
    box-shadow: 0 10px 40px rgba(0,0,0,0.08);
  }
  .ticket-top {
    background: linear-gradient(135deg, #0a1628 0%, #162544 40%, #1a3a5c 100%);
    padding: 30px 36px;
    color: #fff;
    position: relative;
    overflow: hidden;
  }
  .ticket-top::after {
    content: '✈';
    position: absolute;
    right: 30px;
    top: 50%;
    transform: translateY(-50%) rotate(-15deg);
    font-size: 80px;
    opacity: 0.06;
  }
  .ticket-top .airline-name {
    font-size: 0.85rem;
    color: rgba(255,255,255,0.6);
    margin-bottom: 4px;
  }
  .ticket-top .booking-ref {
    font-size: 1.4rem;
    font-weight: 800;
    letter-spacing: 2px;
  }
  .ticket-top .status-badge {
    display: inline-block;
    padding: 4px 14px;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 700;
    text-transform: uppercase;
    margin-top: 8px;
  }
  .ticket-top .status-paid {
    background: rgba(39,174,96,0.2);
    color: #2ecc71;
    border: 1px solid rgba(39,174,96,0.3);
  }
  .ticket-top .status-pending {
    background: rgba(241,196,15,0.2);
    color: #f1c40f;
    border: 1px solid rgba(241,196,15,0.3);
  }
  .ticket-route {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 30px 36px;
    position: relative;
  }
  .ticket-route::before {
    content: '';
    position: absolute;
    top: 0;
    left: 36px;
    right: 36px;
    height: 1px;
    background: repeating-linear-gradient(90deg, var(--border-color) 0, var(--border-color) 8px, transparent 8px, transparent 16px);
  }
  .route-point { text-align: center; flex: 1; }
  .route-point .code {
    font-size: 2rem;
    font-weight: 800;
    color: var(--text-heading);
  }
  .route-point .city {
    font-size: 0.85rem;
    color: var(--text-muted);
    margin-top: 2px;
  }
  .route-line {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    padding: 0 20px;
  }
  .route-line::before {
    content: '';
    position: absolute;
    left: 20px;
    right: 20px;
    height: 2px;
    background: var(--border-color);
  }
  .route-line .plane-icon {
    position: relative;
    z-index: 1;
    background: var(--bg-card);
    padding: 6px;
    color: var(--primary);
    font-size: 1.2rem;
    border-radius: 50%;
    border: 2px solid var(--border-color);
  }
  .route-line .line-label {
    position: absolute;
    top: -16px;
    left: 50%;
    transform: translateX(-50%);
    background: var(--bg-card);
    padding: 2px 10px;
    font-size: 0.7rem;
    color: var(--text-muted);
    border-radius: 10px;
    border: 1px solid var(--border-color);
    white-space: nowrap;
  }
  .ticket-details {
    padding: 0 36px 30px;
  }
  .detail-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 16px;
  }
  .detail-item label {
    display: block;
    font-size: 0.7rem;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: var(--text-muted);
    margin-bottom: 4px;
  }
  .detail-item .value {
    font-size: 0.95rem;
    font-weight: 600;
    color: var(--text-heading);
  }
  .ticket-divider {
    display: flex;
    align-items: center;
    padding: 0 20px;
  }
  .ticket-divider::before,
  .ticket-divider::after {
    content: '';
    flex: 1;
    height: 1px;
    background: var(--border-color);
  }
  .ticket-divider .notch {
    width: 24px;
    height: 24px;
    border-radius: 50%;
    background: var(--bg-primary);
    border: 1px solid var(--border-color);
    margin: 0 12px;
  }
  .ticket-bottom {
    padding: 24px 36px;
    background: var(--bg-secondary);
  }
  .barcode {
    font-family: 'Courier New', monospace;
    font-size: 0.65rem;
    letter-spacing: 3px;
    color: var(--text-heading);
    word-break: break-all;
    line-height: 1.4;
  }
  .barcode-lines {
    display: flex;
    gap: 1px;
    height: 40px;
    margin-bottom: 8px;
  }
  .barcode-lines span {
    display: inline-block;
    background: var(--text-heading);
    height: 100%;
  }
  .ticket-actions {
    display: flex;
    gap: 12px;
    justify-content: center;
    margin-top: 24px;
  }
  .btn-ticket {
    padding: 10px 28px;
    border-radius: 50px;
    font-weight: 600;
    font-size: 0.9rem;
    text-decoration: none;
    transition: all 0.3s;
    border: none;
    cursor: pointer;
  }
  .btn-ticket-primary {
    background: var(--gradient-primary);
    color: #fff;
  }
  .btn-ticket-primary:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(212,160,23,0.35); color: #fff; }
  .btn-ticket-outline {
    background: transparent;
    border: 2px solid var(--border-color);
    color: var(--text-primary);
  }
  .btn-ticket-outline:hover { border-color: var(--primary); color: var(--primary); }

  @media print {
    .navbar, .newsletter-section, .footer, .ticket-actions, .scroll-to-top { display: none !important; }
    .ticket { box-shadow: none; border: 2px solid #333; }
    body { background: #fff !important; }
    .flight-hero { display: none; }
    main { padding-top: 20px !important; }
  }
</style>

<section class="flight-hero" style="padding:80px 0 40px;">
  <div class="container text-center">
    <h1 class="mb-2" style="font-size:1.8rem;"><i class="fas fa-check-circle me-2" style="color:#2ecc71;"></i>Booking Confirmed</h1>
    <p class="mb-0">Your flight ticket is ready below</p>
  </div>
</section>

<div class="container">
  <div class="ticket-wrapper">
    <div class="ticket" id="flightTicket">
      <!-- Top Section -->
      <div class="ticket-top">
        <div class="airline-name"><?= SITE_NAME ?> Flights</div>
        <div class="booking-ref"><?= htmlspecialchars($flight['booking_ref']) ?></div>
        <div class="status-badge <?= $flight['payment_status'] === 'paid' ? 'status-paid' : 'status-pending' ?>">
          <?= $flight['payment_status'] === 'paid' ? '✓ Payment Confirmed' : '⏳ Payment Pending' ?>
        </div>
      </div>

      <!-- Route Section -->
      <div class="ticket-route">
        <div class="route-point">
          <div class="code"><?= htmlspecialchars(explode(' (', $flight['from_city'])[0] ?? $flight['from_city']) ?></div>
          <div class="city"><?= htmlspecialchars($flight['from_city']) ?></div>
          <div style="font-size:0.75rem;color:var(--text-muted);margin-top:4px;"><?= date('D, d M Y', strtotime($flight['departure_date'])) ?></div>
        </div>
        <div class="route-line">
          <div class="line-label"><?= $trip_label ?> · <?= $class_label ?></div>
          <i class="fas fa-plane plane-icon"></i>
        </div>
        <div class="route-point">
          <div class="code"><?= htmlspecialchars(explode(' (', $flight['to_city'])[0] ?? $flight['to_city']) ?></div>
          <div class="city"><?= htmlspecialchars($flight['to_city']) ?></div>
          <?php if ($flight['return_date']): ?>
          <div style="font-size:0.75rem;color:var(--text-muted);margin-top:4px;">Return: <?= date('D, d M Y', strtotime($flight['return_date'])) ?></div>
          <?php endif; ?>
        </div>
      </div>

      <!-- Details Grid -->
      <div class="ticket-details">
        <div class="detail-grid">
          <div class="detail-item">
            <label>Passenger</label>
            <div class="value"><?= htmlspecialchars($flight['passenger_name']) ?></div>
          </div>
          <div class="detail-item">
            <label>PNR</label>
            <div class="value" style="letter-spacing:2px;"><?= $pnr ?></div>
          </div>
          <div class="detail-item">
            <label>Travelers</label>
            <div class="value"><?= $flight['travelers'] ?></div>
          </div>
          <div class="detail-item">
            <label>Class</label>
            <div class="value" style="color:<?= $class_color ?>;"><?= $class_label ?></div>
          </div>
          <div class="detail-item">
            <label>Booked On</label>
            <div class="value"><?= $booking_date ?></div>
          </div>
          <div class="detail-item">
            <label>Total Fare</label>
            <div class="value" style="color:var(--primary);font-size:1.1rem;">₹<?= number_format($flight['total_amount'], 0) ?></div>
          </div>
        </div>
      </div>

      <!-- Divider -->
      <div class="ticket-divider">
        <div class="notch"></div>
      </div>

      <!-- Bottom / Barcode -->
      <div class="ticket-bottom">
        <div class="barcode-lines">
          <?php
          $hash = md5($barcode_str);
          for ($i = 0; $i < 60; $i++) {
              $h = hexdec($hash[$i % 32]) * 2 + 2;
              $w = ($i % 3 === 0) ? 3 : 1;
              echo '<span style="width:' . $w . 'px;height:' . $h . 'px;"></span>';
          }
          ?>
        </div>
        <div class="barcode"><?= htmlspecialchars($flight['booking_ref'] . ' | ' . $pnr . ' | ' . $flight['from_city'] . ' → ' . $flight['to_city']) ?></div>
      </div>
    </div>

    <!-- Actions -->
    <div class="ticket-actions">
      <button onclick="printTicket()" class="btn-ticket btn-ticket-primary">
        <i class="fas fa-print me-2"></i>Print Ticket
      </button>
      <button onclick="downloadTicket()" class="btn-ticket btn-ticket-outline">
        <i class="fas fa-download me-2"></i>Save as PDF
      </button>
      <a href="book-flight.php" class="btn-ticket btn-ticket-outline">
        <i class="fas fa-plus me-2"></i>Book Another
      </a>
    </div>

    <?php if ($flight['passenger_email']): ?>
    <div class="text-center mt-4" style="color:var(--text-muted);font-size:0.85rem;">
      <i class="fas fa-envelope me-1"></i>A copy of this ticket has been sent to <strong><?= htmlspecialchars($flight['passenger_email']) ?></strong>
    </div>
    <?php endif; ?>
  </div>
</div>

<script>
function printTicket() {
  window.print();
}

function downloadTicket() {
  window.print();
}
</script>

<?php require_once 'includes/footer.php'; ?>
