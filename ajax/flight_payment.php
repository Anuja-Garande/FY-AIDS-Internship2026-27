<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
    exit;
}

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Please login to continue.']);
    exit;
}

$action = $_POST['action'] ?? '';
$db = Database::getInstance();
$user = getUser();

function generateBookingRef() {
    return 'FL-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -5));
}

function getFlightPrice($travel_class) {
    return match($travel_class) {
        'economy' => 2999,
        'premium_economy' => 5499,
        'business' => 14999,
        'first' => 29999,
        default => 2999,
    };
}

switch ($action) {

    case 'create_order':
        $from = trim($_POST['from'] ?? '');
        $to = trim($_POST['to'] ?? '');
        $departure_date = trim($_POST['departure_date'] ?? '');
        $return_date = trim($_POST['return_date'] ?? '');
        $trip_type = trim($_POST['trip_type'] ?? 'oneway');
        $travelers = (int)($_POST['travelers'] ?? 1);
        $travel_class = trim($_POST['travel_class'] ?? 'economy');
        $full_name = trim($_POST['full_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');

        if (!$from || !$to || !$departure_date || !$full_name || !$email) {
            echo json_encode(['success' => false, 'message' => 'Please fill all required fields.']);
            exit;
        }

        if ($from === $to) {
            echo json_encode(['success' => false, 'message' => 'Origin and destination cannot be the same.']);
            exit;
        }

        $unit_price = getFlightPrice($travel_class);
        $total = $unit_price * $travelers;
        if ($trip_type === 'roundtrip') {
            $total *= 2;
        }

        $amount_in_paise = round($total * 100);
        $booking_ref = generateBookingRef();

        $_SESSION['flight_booking'] = [
            'from' => $from,
            'to' => $to,
            'departure_date' => $departure_date,
            'return_date' => $return_date,
            'trip_type' => $trip_type,
            'travelers' => $travelers,
            'travel_class' => $travel_class,
            'full_name' => $full_name,
            'email' => $email,
            'phone' => $phone,
            'total' => $total,
            'booking_ref' => $booking_ref,
        ];

        $ch = curl_init('https://api.razorpay.com/v1/orders');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_USERPWD => RAZORPAY_KEY_ID . ':' . RAZORPAY_KEY_SECRET,
            CURLOPT_POSTFIELDS => json_encode([
                'amount' => $amount_in_paise,
                'currency' => 'INR',
                'receipt' => 'flight_' . $booking_ref,
                'notes' => [
                    'user_id' => $user['id'],
                    'booking_ref' => $booking_ref,
                    'route' => $from . ' to ' . $to
                ]
            ]),
            CURLOPT_HTTPHEADER => ['Content-Type: application/json']
        ]);

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($http_code !== 200) {
            error_log('Razorpay flight order creation failed: ' . $response);
            echo json_encode(['success' => false, 'message' => 'Payment gateway error. Please try again.']);
            exit;
        }

        $order = json_decode($response, true);

        echo json_encode([
            'success' => true,
            'razorpay_order_id' => $order['id'],
            'amount' => $amount_in_paise,
            'currency' => 'INR',
            'key' => RAZORPAY_KEY_ID,
            'name' => SITE_NAME . ' Flights',
            'description' => 'Flight: ' . $from . ' → ' . $to,
            'prefill' => [
                'name' => $full_name,
                'email' => $email,
                'contact' => $phone
            ]
        ]);
        break;

    case 'verify_payment':
        $razorpay_order_id = $_POST['razorpay_order_id'] ?? '';
        $razorpay_payment_id = $_POST['razorpay_payment_id'] ?? '';
        $razorpay_signature = $_POST['razorpay_signature'] ?? '';

        if (empty($razorpay_order_id) || empty($razorpay_payment_id) || empty($razorpay_signature)) {
            echo json_encode(['success' => false, 'message' => 'Missing payment details.']);
            exit;
        }

        $expected_signature = hash_hmac('sha256', $razorpay_order_id . '|' . $razorpay_payment_id, RAZORPAY_KEY_SECRET);

        if ($expected_signature !== $razorpay_signature) {
            error_log('Razorpay flight signature mismatch: ' . $razorpay_order_id);
            echo json_encode(['success' => false, 'message' => 'Payment verification failed.']);
            exit;
        }

        $booking = $_SESSION['flight_booking'] ?? null;
        if (!$booking) {
            echo json_encode(['success' => false, 'message' => 'Booking session expired. Please try again.']);
            exit;
        }

        $db->beginTransaction();
        try {
            $flight_id = $db->insert(
                "INSERT INTO flight_bookings (user_id, booking_ref, from_city, to_city, departure_date, return_date, trip_type, travelers, travel_class, passenger_name, passenger_email, passenger_phone, total_amount, payment_method, payment_status, booking_status, razorpay_order_id, razorpay_payment_id, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'razorpay', 'paid', 'on_hold', ?, ?, NOW())",
                [
                    $user['id'],
                    $booking['booking_ref'],
                    $booking['from'],
                    $booking['to'],
                    $booking['departure_date'],
                    $booking['return_date'] ?: null,
                    $booking['trip_type'],
                    $booking['travelers'],
                    $booking['travel_class'],
                    $booking['full_name'],
                    $booking['email'],
                    $booking['phone'],
                    $booking['total'],
                    $razorpay_order_id,
                    $razorpay_payment_id,
                ]
            );

            if (!$flight_id) throw new Exception('Failed to create flight booking.');

            setNotification($user['id'], 'Flight Request Submitted', "Your flight from {$booking['from']} to {$booking['to']} is pending admin approval. Ref: {$booking['booking_ref']}", 'info');

            unset($_SESSION['flight_booking']);
            $db->commit();

            echo json_encode([
                'success' => true,
                'message' => 'Payment successful! Your booking request has been submitted and is pending admin approval. You will receive your ticket via email once approved.',
                'redirect' => 'flight-ticket.php?ref=' . $booking['booking_ref']
            ]);
        } catch (Exception $e) {
            $db->rollback();
            error_log("Flight booking after payment failed: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Booking failed after payment. Contact support with Payment ID: ' . $razorpay_payment_id]);
        }
        break;

    case 'cod_booking':
        $from = trim($_POST['from'] ?? '');
        $to = trim($_POST['to'] ?? '');
        $departure_date = trim($_POST['departure_date'] ?? '');
        $return_date = trim($_POST['return_date'] ?? '');
        $trip_type = trim($_POST['trip_type'] ?? 'oneway');
        $travelers = (int)($_POST['travelers'] ?? 1);
        $travel_class = trim($_POST['travel_class'] ?? 'economy');
        $full_name = trim($_POST['full_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');

        if (!$from || !$to || !$departure_date || !$full_name || !$email) {
            echo json_encode(['success' => false, 'message' => 'Please fill all required fields.']);
            exit;
        }

        $unit_price = getFlightPrice($travel_class);
        $total = $unit_price * $travelers;
        if ($trip_type === 'roundtrip') {
            $total *= 2;
        }
        $booking_ref = generateBookingRef();

        $flight_id = $db->insert(
            "INSERT INTO flight_bookings (user_id, booking_ref, from_city, to_city, departure_date, return_date, trip_type, travelers, travel_class, passenger_name, passenger_email, passenger_phone, total_amount, payment_method, payment_status, booking_status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'cod', 'pending', 'on_hold', NOW())",
            [
                $user['id'],
                $booking_ref,
                $from,
                $to,
                $departure_date,
                $return_date ?: null,
                $trip_type,
                $travelers,
                $travel_class,
                $full_name,
                $email,
                $phone,
                $total,
            ]
        );

        if (!$flight_id) {
            echo json_encode(['success' => false, 'message' => 'Failed to create booking.']);
            exit;
        }

        setNotification($user['id'], 'Flight Request Submitted', "Your flight from $from to $to is pending admin approval. Ref: $booking_ref. Pay on departure.", 'info');

        echo json_encode([
            'success' => true,
            'message' => 'Booking request submitted! Awaiting admin approval. Pay on departure.',
            'redirect' => 'flight-ticket.php?ref=' . $booking_ref
        ]);
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action.']);
        break;
}

function sendFlightTicketEmail($flight_id) {
    $db = Database::getInstance();
    $flight = $db->fetch("SELECT * FROM flight_bookings WHERE id = ?", [$flight_id]);
    if (!$flight) return false;

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
        $db->update("UPDATE flight_bookings SET ticket_emailed = 1 WHERE id = ?", [$flight_id]);
    }

    return $sent;
}
