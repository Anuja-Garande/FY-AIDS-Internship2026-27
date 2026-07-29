<?php
session_start();
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

if (!isAdminLoggedIn()) {
    redirect('login.php');
}

$db = Database::getInstance();
$ref = trim($_GET['ref'] ?? '');
if (!$ref) redirect('flight_bookings.php');

$flight = $db->fetch("SELECT f.*, u.name as user_name, u.email as user_email FROM flight_bookings f LEFT JOIN users u ON f.user_id = u.id WHERE f.booking_ref = ?", [$ref]);
if (!$flight) {
    flash('error', 'Booking not found.');
    redirect('flight_bookings.php');
}

function sendFlightTicketEmailAdmin($flight) {
    $to = $flight['passenger_email'];
    $subject = "✈ Your Flight Ticket - {$flight['booking_ref']}";

    $class_labels = [
        'economy' => 'Economy',
        'premium_economy' => 'Premium Economy',
        'business' => 'Business Class',
        'first' => 'First Class',
    ];
    $class_label = $class_labels[$flight['travel_class']] ?? 'Economy';
    $trip_label = $flight['trip_type'] === 'roundtrip' ? 'Round Trip' : 'One Way';
    $pnr = strtoupper(substr(md5($flight['booking_ref']), 0, 6));
    $from_code = strtoupper(substr(md5($flight['from_city']), 0, 3));
    $to_code = strtoupper(substr(md5($flight['to_city']), 0, 3));

    $barcode_str = $flight['booking_ref'] . '|' . $pnr . '|' . $flight['from_city'] . '|' . $flight['to_city'] . '|' . $flight['departure_date'];
    $hash = md5($barcode_str);
    $barcode_html = '';
    for ($i = 0; $i < 70; $i++) {
        $h = hexdec($hash[$i % 32]) * 2 + 2;
        $w = ($i % 3 === 0) ? 3 : 1;
        $barcode_html .= '<td style="width:' . $w . 'px;padding:0;"><div style="width:' . $w . 'px;height:' . $h . 'px;background:#1a1a2e;"></div></td>';
    }

    $payment_badge = $flight['payment_status'] === 'paid'
        ? '<span style="display:inline-block;background:rgba(46,204,113,0.2);color:#2ecc71;padding:4px 14px;border-radius:20px;font-size:11px;font-weight:700;text-transform:uppercase;border:1px solid rgba(46,204,113,0.3);">✓ PAID</span>'
        : '<span style="display:inline-block;background:rgba(241,196,15,0.2);color:#f1c40f;padding:4px 14px;border-radius:20px;font-size:11px;font-weight:700;text-transform:uppercase;border:1px solid rgba(241,196,15,0.3);">⏳ PENDING</span>';

    $return_row = '';
    if ($flight['return_date']) {
        $return_row = '
        <tr>
          <td style="padding:8px 0;color:#8a8fa8;font-size:12px;width:35%;letter-spacing:0.5px;">RETURN</td>
          <td style="padding:8px 0;color:#fff;font-size:14px;font-weight:600;">' . date('D, d M Y', strtotime($flight['return_date'])) . '</td>
        </tr>
        <tr><td colspan="2" style="padding:0;"><div style="border-top:1px dashed rgba(255,255,255,0.1);margin:4px 0;"></div></td></tr>';
    }

    $html = '
    <!DOCTYPE html>
    <html>
    <head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0"></head>
    <body style="margin:0;padding:0;background:#e8ecf1;font-family:\'Segoe UI\',Tahoma,Geneva,Verdana,sans-serif;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background:#e8ecf1;padding:30px 0;">
      <tr><td align="center">

        <!-- Pre-header -->
        <table width="600" cellpadding="0" cellspacing="0" style="margin-bottom:0;">
          <tr><td style="padding:0 0 20px;text-align:center;">
            <p style="margin:0;color:#5a6270;font-size:13px;">Your flight ticket is ready — please present this email at check-in</p>
          </td></tr>
        </table>

        <!-- TICKET CARD -->
        <table width="600" cellpadding="0" cellspacing="0" style="background:#fff;border-radius:16px;overflow:hidden;box-shadow:0 8px 30px rgba(0,0,0,0.12);">

          <!-- TOP: Dark Header -->
          <tr>
            <td style="background:linear-gradient(135deg,#0a1628 0%,#162544 40%,#1a3a5c 100%);padding:28px 36px;color:#fff;position:relative;">
              <table width="100%" cellpadding="0" cellspacing="0">
                <tr>
                  <td>
                    <p style="margin:0 0 4px;color:rgba(255,255,255,0.5);font-size:12px;letter-spacing:1px;text-transform:uppercase;">' . SITE_NAME . ' Flights</p>
                    <p style="margin:0;font-size:22px;font-weight:800;letter-spacing:2px;color:#fff;">' . htmlspecialchars($flight['booking_ref']) . '</p>
                  </td>
                  <td align="right" valign="top">
                    ' . $payment_badge . '
                    <p style="margin:6px 0 0;color:rgba(255,255,255,0.4);font-size:11px;">PNR: <strong style="color:rgba(255,255,255,0.7);letter-spacing:2px;">' . $pnr . '</strong></p>
                  </td>
                </tr>
              </table>
            </td>
          </tr>

          <!-- ROUTE SECTION -->
          <tr>
            <td style="padding:32px 36px 28px;">
              <table width="100%" cellpadding="0" cellspacing="0">
                <tr>
                  <!-- FROM -->
                  <td width="40%" style="text-align:center;vertical-align:top;">
                    <p style="margin:0;font-size:32px;font-weight:800;color:#0a1628;letter-spacing:1px;">' . $from_code . '</p>
                    <p style="margin:4px 0 0;color:#8a8fa8;font-size:12px;">' . htmlspecialchars($flight['from_city']) . '</p>
                    <p style="margin:4px 0 0;color:#b0b5c0;font-size:11px;">' . date('D, d M Y', strtotime($flight['departure_date'])) . '</p>
                  </td>
                  <!-- FLIGHT LINE -->
                  <td width="20%" align="center" style="position:relative;">
                    <table cellpadding="0" cellspacing="0" width="100%">
                      <tr><td style="padding:0 8px;"><div style="border-top:2px dashed #d1d5de;margin:0 0 6px;"></div></td></tr>
                      <tr><td align="center">
                        <table cellpadding="0" cellspacing="0" style="margin:0 auto;">
                          <tr>
                            <td style="background:#0a1628;border-radius:50%;width:36px;height:36px;text-align:center;vertical-align:middle;">
                              <span style="color:#fff;font-size:16px;line-height:36px;">✈</span>
                            </td>
                          </tr>
                        </table>
                      </td></tr>
                      <tr><td style="padding:6px 8px 0;"><div style="border-top:2px dashed #d1d5de;margin:0;"></div></td></tr>
                      <tr><td align="center" style="padding-top:4px;">
                        <span style="display:inline-block;background:#f0f2f7;color:#5a6270;padding:3px 12px;border-radius:12px;font-size:10px;font-weight:600;letter-spacing:0.5px;">' . $trip_label . ' · ' . $class_label . '</span>
                      </td></tr>
                    </table>
                  </td>
                  <!-- TO -->
                  <td width="40%" style="text-align:center;vertical-align:top;">
                    <p style="margin:0;font-size:32px;font-weight:800;color:#0a1628;letter-spacing:1px;">' . $to_code . '</p>
                    <p style="margin:4px 0 0;color:#8a8fa8;font-size:12px;">' . htmlspecialchars($flight['to_city']) . '</p>
                    ' . ($flight['return_date'] ? '<p style="margin:4px 0 0;color:#b0b5c0;font-size:11px;">Return: ' . date('D, d M Y', strtotime($flight['return_date'])) . '</p>' : '') . '
                  </td>
                </tr>
              </table>
            </td>
          </tr>

          <!-- DETAILS GRID -->
          <tr>
            <td style="padding:0 36px 28px;">
              <table width="100%" cellpadding="0" cellspacing="0" style="background:#f8f9fc;border-radius:12px;border:1px solid #e8ecf1;">
                <tr>
                  <td style="padding:20px 24px;" width="33%">
                    <p style="margin:0 0 4px;color:#8a8fa8;font-size:10px;text-transform:uppercase;letter-spacing:1px;">Passenger</p>
                    <p style="margin:0;color:#1a1a2e;font-size:14px;font-weight:700;">' . htmlspecialchars($flight['passenger_name']) . '</p>
                  </td>
                  <td style="padding:20px 24px;border-left:1px solid #e8ecf1;" width="33%">
                    <p style="margin:0 0 4px;color:#8a8fa8;font-size:10px;text-transform:uppercase;letter-spacing:1px;">Travelers</p>
                    <p style="margin:0;color:#1a1a2e;font-size:14px;font-weight:700;">' . $flight['travelers'] . ' ' . ($flight['travelers'] > 1 ? 'Passengers' : 'Passenger') . '</p>
                  </td>
                  <td style="padding:20px 24px;border-left:1px solid #e8ecf1;" width="33%">
                    <p style="margin:0 0 4px;color:#8a8fa8;font-size:10px;text-transform:uppercase;letter-spacing:1px;">Class</p>
                    <p style="margin:0;color:#D4A017;font-size:14px;font-weight:700;">' . $class_label . '</p>
                  </td>
                </tr>
                <tr>
                  <td colspan="3" style="padding:0 24px;"><div style="border-top:1px solid #e8ecf1;"></div></td>
                </tr>
                <tr>
                  <td style="padding:16px 24px;" width="33%">
                    <p style="margin:0 0 4px;color:#8a8fa8;font-size:10px;text-transform:uppercase;letter-spacing:1px;">Booked On</p>
                    <p style="margin:0;color:#1a1a2e;font-size:13px;font-weight:600;">' . date('d M Y', strtotime($flight['created_at'])) . '</p>
                  </td>
                  <td style="padding:16px 24px;border-left:1px solid #e8ecf1;" width="33%">
                    <p style="margin:0 0 4px;color:#8a8fa8;font-size:10px;text-transform:uppercase;letter-spacing:1px;">Departure</p>
                    <p style="margin:0;color:#1a1a2e;font-size:13px;font-weight:600;">' . date('d M Y, h:i A', strtotime($flight['departure_date'])) . '</p>
                  </td>
                  <td style="padding:16px 24px;border-left:1px solid #e8ecf1;" width="33%">
                    <p style="margin:0 0 4px;color:#8a8fa8;font-size:10px;text-transform:uppercase;letter-spacing:1px;">Total Fare</p>
                    <p style="margin:0;color:#D4A017;font-size:20px;font-weight:800;">₹' . number_format($flight['total_amount'], 0) . '</p>
                  </td>
                </tr>
              </table>
            </td>
          </tr>

          <!-- SCISSORS DIVIDER -->
          <tr>
            <td style="padding:0 20px;">
              <table width="100%" cellpadding="0" cellspacing="0">
                <tr>
                  <td width="24" style="width:24px;height:24px;background:#e8ecf1;border-radius:50%;"></td>
                  <td style="border-top:2px dashed #d1d5de;"></td>
                  <td width="24" style="width:24px;height:24px;background:#e8ecf1;border-radius:50%;"></td>
                </tr>
              </table>
            </td>
          </tr>

          <!-- BARCODE SECTION -->
          <tr>
            <td style="padding:20px 36px 28px;background:#f8f9fc;">
              <table width="100%" cellpadding="0" cellspacing="0">
                <tr>
                  <td>
                    <table cellpadding="0" cellspacing="1" style="margin-bottom:8px;">
                      <tr>' . $barcode_html . '</tr>
                    </table>
                    <p style="margin:0;font-family:\'Courier New\',monospace;font-size:10px;color:#1a1a2e;letter-spacing:2px;word-break:break-all;">' . htmlspecialchars($flight['booking_ref'] . '  │  ' . $pnr . '  │  ' . $flight['from_city'] . '  →  ' . $flight['to_city']) . '</p>
                  </td>
                  <td width="120" align="right" valign="bottom">
                    <table cellpadding="0" cellspacing="0">
                      <tr><td style="text-align:right;">
                        <p style="margin:0;color:#8a8fa8;font-size:9px;text-transform:uppercase;letter-spacing:1px;">Fare</p>
                        <p style="margin:0;color:#0a1628;font-size:20px;font-weight:800;">₹' . number_format($flight['total_amount'], 0) . '</p>
                      </td></tr>
                    </table>
                  </td>
                </tr>
              </table>
            </td>
          </tr>

        </table>
        <!-- END TICKET CARD -->

        <!-- VIEW TICKET BUTTON -->
        <table width="600" cellpadding="0" cellspacing="0" style="margin-top:20px;">
          <tr>
            <td align="center" style="padding:10px 0;">
              <a href="' . BASE_URL . 'flight-ticket.php?ref=' . htmlspecialchars($flight['booking_ref']) . '" style="display:inline-block;background:linear-gradient(135deg,#D4A017,#DAA520);color:#fff;padding:12px 36px;border-radius:50px;text-decoration:none;font-weight:700;font-size:14px;box-shadow:0 4px 15px rgba(212,160,23,0.35);">View & Print Ticket</a>
            </td>
          </tr>
          <tr>
            <td align="center" style="padding:8px 0 0;">
              <p style="margin:0;color:#8a8fa8;font-size:12px;line-height:1.6;">
                Please carry a valid photo ID during travel.<br>
                Show this email at the airport check-in counter.
              </p>
            </td>
          </tr>
        </table>

        <!-- FOOTER -->
        <table width="600" cellpadding="0" cellspacing="0" style="margin-top:20px;">
          <tr>
            <td align="center" style="padding:16px 0;">
              <p style="margin:0;color:#8a8fa8;font-size:11px;">This is an automated email. Please do not reply directly.</p>
              <p style="margin:4px 0 0;color:#b0b5c0;font-size:10px;">© ' . date('Y') . ' ' . SITE_NAME . ' | AI Powered Shopping</p>
            </td>
          </tr>
        </table>

      </td></tr>
    </table>
    </body>
    </html>';

    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=UTF-8\r\n";
    $headers .= "From: " . SITE_NAME . " Flights <" . ADMIN_EMAIL . ">\r\n";

    $sent = @mail($to, $subject, $html, $headers);
    if ($sent) {
        $db = Database::getInstance();
        $db->update("UPDATE flight_bookings SET ticket_emailed = 1 WHERE id = ?", [$flight['id']]);
    }
    return $sent;
}

if (isset($_POST['accept_booking'])) {
    if ($flight['booking_status'] === 'on_hold') {
        $db->update("UPDATE flight_bookings SET booking_status = 'confirmed' WHERE booking_ref = ?", [$ref]);
        sendFlightTicketEmailAdmin($flight);
        setNotification($flight['user_id'], 'Flight Confirmed!', "Your flight from {$flight['from_city']} to {$flight['to_city']} has been approved. Your ticket has been emailed to {$flight['passenger_email']}.", 'success');
        flash('success', 'Booking accepted! Ticket has been emailed to ' . $flight['passenger_email']);
    } else {
        flash('error', 'This booking cannot be accepted (current status: ' . ucfirst(str_replace('_', ' ', $flight['booking_status'])) . ')');
    }
    redirect('view_flight_booking.php?ref=' . urlencode($ref));
}

if (isset($_POST['reject_booking'])) {
    if ($flight['booking_status'] !== 'cancelled' && $flight['booking_status'] !== 'completed') {
        $db->update("UPDATE flight_bookings SET booking_status = 'cancelled' WHERE booking_ref = ?", [$ref]);
        setNotification($flight['user_id'], 'Flight Rejected', "Your flight from {$flight['from_city']} to {$flight['to_city']} has been rejected by admin.", 'error');
        flash('success', 'Booking has been cancelled and customer notified.');
    } else {
        flash('error', 'This booking cannot be rejected (current status: ' . ucfirst(str_replace('_', ' ', $flight['booking_status'])) . ')');
    }
    redirect('view_flight_booking.php?ref=' . urlencode($ref));
}

if (isset($_POST['update_status'])) {
    $new_status = trim($_POST['booking_status'] ?? '');
    $payment_status = trim($_POST['payment_status'] ?? '');
    $notes = trim($_POST['notes'] ?? '');
    $valid_booking = ['confirmed', 'on_hold', 'completed', 'cancelled'];
    $valid_payment = ['pending', 'paid', 'failed', 'refunded'];

    $updates = [];
    $params = [];
    if (in_array($new_status, $valid_booking)) {
        $updates[] = "booking_status = :bs";
        $params[':bs'] = $new_status;
    }
    if (in_array($payment_status, $valid_payment)) {
        $updates[] = "payment_status = :ps";
        $params[':ps'] = $payment_status;
    }
    if ($notes !== '') {
        $updates[] = "notes = :notes";
        $params[':notes'] = $notes;
    }

    if (!empty($updates)) {
        $params[':ref'] = $ref;
        $db->query("UPDATE flight_bookings SET " . implode(', ', $updates) . " WHERE booking_ref = :ref", $params);
        flash('success', 'Booking updated successfully!');
    }
    redirect('view_flight_booking.php?ref=' . urlencode($ref));
}

if (isset($_POST['resend_ticket'])) {
    if ($flight['booking_status'] === 'confirmed') {
        $sent = sendFlightTicketEmailAdmin($flight);
        if ($sent) {
            flash('success', 'Ticket email resent to ' . $flight['passenger_email']);
        } else {
            flash('error', 'Failed to send email. Check mail configuration.');
        }
    } else {
        flash('error', 'Cannot send ticket for a booking that is not confirmed.');
    }
    redirect('view_flight_booking.php?ref=' . urlencode($ref));
}

$class_labels = [
    'economy' => 'Economy',
    'premium_economy' => 'Premium Economy',
    'business' => 'Business Class',
    'first' => 'First Class',
];
$class_label = $class_labels[$flight['travel_class']] ?? 'Economy';
$pnr = strtoupper(substr(md5($flight['booking_ref']), 0, 6));

$pageTitle = 'Flight Booking - ' . $flight['booking_ref'];
include __DIR__ . '/includes/header.php';
?>

<a href="<?= BASE_URL ?>/admin/flight_bookings.php" class="btn btn-outline-secondary mb-3"><i class="fas fa-arrow-left me-1"></i>Back to Flight Bookings</a>

<?php if (sessionFlash('success')): ?>
<div class="alert alert-success alert-dismissible fade show"><i class="fas fa-check-circle me-2"></i><?= sessionFlash('success') ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>
<?php if (sessionFlash('error')): ?>
<div class="alert alert-danger alert-dismissible fade show"><i class="fas fa-exclamation-circle me-2"></i><?= sessionFlash('error') ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>

<?php if ($flight['booking_status'] === 'on_hold'): ?>
<div class="alert alert-warning d-flex align-items-center mb-4" role="alert" style="border-left:4px solid #ffc107;">
    <i class="fas fa-hourglass-half me-3 fs-4"></i>
    <div class="flex-grow-1">
        <strong class="d-block">Pending Approval</strong>
        <small>This booking request is waiting for your approval. Accept to confirm and send the ticket, or reject to cancel.</small>
    </div>
    <div class="d-flex gap-2 ms-3">
        <form method="POST" class="d-inline">
            <input type="hidden" name="accept_booking" value="1">
            <button type="submit" class="btn btn-success btn-lg" onclick="return confirm('Accept this booking? Ticket will be emailed to the customer automatically.')"><i class="fas fa-check me-1"></i>Accept & Send Ticket</button>
        </form>
        <form method="POST" class="d-inline">
            <input type="hidden" name="reject_booking" value="1">
            <button type="submit" class="btn btn-danger btn-lg" onclick="return confirm('Reject this booking? Customer will be notified.')"><i class="fas fa-times me-1"></i>Reject</button>
        </form>
    </div>
</div>
<?php endif; ?>

<?php if ($flight['booking_status'] === 'cancelled'): ?>
<div class="alert alert-danger d-flex align-items-center mb-4" role="alert" style="border-left:4px solid #dc3545;">
    <i class="fas fa-ban me-3 fs-4"></i>
    <div>
        <strong class="d-block">Booking Cancelled</strong>
        <small>This booking has been cancelled.</small>
    </div>
</div>
<?php endif; ?>

<?php if ($flight['booking_status'] === 'confirmed'): ?>
<div class="alert alert-success d-flex align-items-center mb-4" role="alert" style="border-left:4px solid #28a745;">
    <i class="fas fa-check-circle me-3 fs-4"></i>
    <div>
        <strong class="d-block">Booking Confirmed</strong>
        <small>This booking has been approved and the ticket has been sent to the customer.</small>
    </div>
</div>
<?php endif; ?>

<div class="row g-4">
    <!-- Left: Booking Details -->
    <div class="col-lg-8">
        <div class="card mb-4">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0"><i class="fas fa-plane me-2 text-primary"></i>Booking Details</h6>
                <div>
                    <span class="badge bg-<?= $flight['payment_status'] === 'paid' ? 'success' : ($flight['payment_status'] === 'pending' ? 'warning' : 'danger') ?> me-1">Payment: <?= ucfirst($flight['payment_status']) ?></span>
                    <span class="badge bg-<?= $flight['booking_status'] === 'confirmed' ? 'success' : ($flight['booking_status'] === 'cancelled' ? 'danger' : ($flight['booking_status'] === 'completed' ? 'primary' : 'warning')) ?>">Status: <?= ucfirst(str_replace('_', ' ', $flight['booking_status'])) ?></span>
                </div>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="p-3 rounded" style="background:var(--bg-secondary,#f8f9fa);">
                            <small class="text-muted d-block mb-1">Booking Reference</small>
                            <div class="fw-bold fs-5" style="color:var(--primary);"><?= sanitize($flight['booking_ref']) ?></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3 rounded" style="background:var(--bg-secondary,#f8f9fa);">
                            <small class="text-muted d-block mb-1">PNR</small>
                            <div class="fw-bold fs-5"><?= $pnr ?></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3 rounded" style="background:var(--bg-secondary,#f8f9fa);">
                            <small class="text-muted d-block mb-1">Route</small>
                            <div class="fw-bold fs-5">
                                <?= sanitize($flight['from_city']) ?>
                                <i class="fas fa-plane mx-2 text-primary" style="font-size:0.8rem;"></i>
                                <?= sanitize($flight['to_city']) ?>
                            </div>
                            <small class="text-muted"><?= $flight['trip_type'] === 'roundtrip' ? 'Round Trip' : 'One Way' ?></small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="p-3 rounded" style="background:var(--bg-secondary,#f8f9fa);">
                            <small class="text-muted d-block mb-1">Departure</small>
                            <div class="fw-bold"><?= date('d M Y', strtotime($flight['departure_date'])) ?></div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="p-3 rounded" style="background:var(--bg-secondary,#f8f9fa);">
                            <small class="text-muted d-block mb-1">Return</small>
                            <div class="fw-bold"><?= $flight['return_date'] ? date('d M Y', strtotime($flight['return_date'])) : '—' ?></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3 rounded" style="background:var(--bg-secondary,#f8f9fa);">
                            <small class="text-muted d-block mb-1">Travel Class</small>
                            <div class="fw-bold"><?= $class_label ?></div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3 rounded" style="background:var(--bg-secondary,#f8f9fa);">
                            <small class="text-muted d-block mb-1">Travelers</small>
                            <div class="fw-bold fs-5"><?= $flight['travelers'] ?></div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3 rounded" style="background:var(--bg-secondary,#f8f9fa);">
                            <small class="text-muted d-block mb-1">Total Amount</small>
                            <div class="fw-bold fs-5 text-primary">₹<?= number_format($flight['total_amount'], 2) ?></div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3 rounded" style="background:var(--bg-secondary,#f8f9fa);">
                            <small class="text-muted d-block mb-1">Booked On</small>
                            <div class="fw-bold"><?= date('d M Y, h:i A', strtotime($flight['created_at'])) ?></div>
                        </div>
                    </div>
                </div>

                <?php if ($flight['payment_method'] || $flight['razorpay_payment_id']): ?>
                <hr>
                <h6 class="fw-bold"><i class="fas fa-credit-card me-2"></i>Payment Info</h6>
                <div class="row g-3">
                    <?php if ($flight['payment_method']): ?>
                    <div class="col-md-4"><small class="text-muted d-block">Method</small><span class="fw-semibold"><?= sanitize(ucfirst($flight['payment_method'])) ?></span></div>
                    <?php endif; ?>
                    <?php if ($flight['razorpay_order_id']): ?>
                    <div class="col-md-4"><small class="text-muted d-block">Razorpay Order</small><code style="font-size:0.75rem;"><?= sanitize($flight['razorpay_order_id']) ?></code></div>
                    <?php endif; ?>
                    <?php if ($flight['razorpay_payment_id']): ?>
                    <div class="col-md-4"><small class="text-muted d-block">Razorpay Payment</small><code style="font-size:0.75rem;"><?= sanitize($flight['razorpay_payment_id']) ?></code></div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <?php if ($flight['ticket_emailed']): ?>
                <hr>
                <h6 class="fw-bold"><i class="fas fa-envelope-open-text me-2 text-success"></i>Email Status</h6>
                <p class="mb-0 text-success fw-semibold"><i class="fas fa-check-circle me-1"></i>Ticket emailed to <?= sanitize($flight['passenger_email']) ?></p>
                <?php endif; ?>

                <?php if ($flight['notes']): ?>
                <hr>
                <h6 class="fw-bold"><i class="fas fa-sticky-note me-2"></i>Admin Notes</h6>
                <p class="mb-0" style="white-space:pre-wrap;"><?= sanitize($flight['notes']) ?></p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Right: Passenger Info + Actions -->
    <div class="col-lg-4">
        <!-- Passenger Card -->
        <div class="card mb-4">
            <div class="card-header bg-white"><h6 class="fw-bold mb-0"><i class="fas fa-user me-2 text-primary"></i>Passenger Info</h6></div>
            <div class="card-body">
                <div class="mb-3">
                    <small class="text-muted d-block">Full Name</small>
                    <div class="fw-semibold"><?= sanitize($flight['passenger_name']) ?></div>
                </div>
                <div class="mb-3">
                    <small class="text-muted d-block">Email</small>
                    <div class="fw-semibold"><?= sanitize($flight['passenger_email']) ?></div>
                </div>
                <div class="mb-3">
                    <small class="text-muted d-block">Phone</small>
                    <div class="fw-semibold"><?= sanitize($flight['passenger_phone'] ?: '—') ?></div>
                </div>
                <div>
                    <small class="text-muted d-block">User Account</small>
                    <div class="fw-semibold"><?= sanitize($flight['user_name'] ?? 'Guest') ?></div>
                    <small class="text-muted">ID: <?= $flight['user_id'] ?></small>
                </div>
            </div>
        </div>

        <!-- Update Status -->
        <div class="card mb-4">
            <div class="card-header bg-white"><h6 class="fw-bold mb-0"><i class="fas fa-edit me-2 text-warning"></i>Update Booking</h6></div>
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="update_status" value="1">
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Booking Status</label>
                        <select name="booking_status" class="form-select">
                            <option value="on_hold" <?= $flight['booking_status'] === 'on_hold' ? 'selected' : '' ?>>On Hold (Pending)</option>
                            <option value="confirmed" <?= $flight['booking_status'] === 'confirmed' ? 'selected' : '' ?>>Confirmed</option>
                            <option value="completed" <?= $flight['booking_status'] === 'completed' ? 'selected' : '' ?>>Completed</option>
                            <option value="cancelled" <?= $flight['booking_status'] === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Payment Status</label>
                        <select name="payment_status" class="form-select">
                            <option value="pending" <?= $flight['payment_status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                            <option value="paid" <?= $flight['payment_status'] === 'paid' ? 'selected' : '' ?>>Paid</option>
                            <option value="failed" <?= $flight['payment_status'] === 'failed' ? 'selected' : '' ?>>Failed</option>
                            <option value="refunded" <?= $flight['payment_status'] === 'refunded' ? 'selected' : '' ?>>Refunded</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Admin Notes</label>
                        <textarea name="notes" class="form-control" rows="3" placeholder="Add notes..."><?= sanitize($flight['notes'] ?? '') ?></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary w-100"><i class="fas fa-save me-1"></i>Update Booking</button>
                </form>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="card mb-4">
            <div class="card-header bg-white"><h6 class="fw-bold mb-0"><i class="fas fa-bolt me-2 text-success"></i>Quick Actions</h6></div>
            <div class="card-body d-grid gap-2">
                <?php if ($flight['booking_status'] === 'on_hold'): ?>
                    <form method="POST" class="d-grid">
                        <input type="hidden" name="accept_booking" value="1">
                        <button type="submit" class="btn btn-success" onclick="return confirm('Accept this booking? Ticket will be emailed automatically.')"><i class="fas fa-check me-1"></i>Accept & Send Ticket</button>
                    </form>
                    <form method="POST" class="d-grid">
                        <input type="hidden" name="reject_booking" value="1">
                        <button type="submit" class="btn btn-danger" onclick="return confirm('Reject this booking? Customer will be notified.')"><i class="fas fa-times me-1"></i>Reject Booking</button>
                    </form>
                <?php elseif ($flight['booking_status'] === 'confirmed'): ?>
                    <a href="<?= BASE_URL ?>flight-ticket.php?ref=<?= urlencode($flight['booking_ref']) ?>" target="_blank" class="btn btn-outline-primary"><i class="fas fa-ticket-alt me-1"></i>View Ticket</a>
                    <form method="POST" class="d-grid">
                        <input type="hidden" name="resend_ticket" value="1">
                        <button type="submit" class="btn btn-outline-success"><i class="fas fa-envelope me-1"></i><?= $flight['ticket_emailed'] ? 'Resend Ticket Email' : 'Send Ticket Email' ?></button>
                    </form>
                <?php elseif ($flight['booking_status'] === 'cancelled'): ?>
                    <div class="text-center text-muted py-2"><i class="fas fa-ban me-1"></i>Booking Cancelled</div>
                <?php else: ?>
                    <a href="<?= BASE_URL ?>flight-ticket.php?ref=<?= urlencode($flight['booking_ref']) ?>" target="_blank" class="btn btn-outline-primary"><i class="fas fa-ticket-alt me-1"></i>View Ticket</a>
                <?php endif; ?>
                <a href="mailto:<?= sanitize($flight['passenger_email']) ?>?subject=Flight Booking <?= sanitize($flight['booking_ref']) ?>" class="btn btn-outline-secondary"><i class="fas fa-envelope-open me-1"></i>Email Passenger</a>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
