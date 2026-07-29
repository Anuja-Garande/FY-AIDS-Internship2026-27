<?php
require_once 'config.php';
requireLogin();

$patient_id = $_SESSION['user_id'];
$message = '';
$message_type = 'success';

if (isset($_GET['msg']) && $_GET['msg'] === 'booked') {
    $ref = htmlspecialchars($_GET['ref'] ?? '');
    $message = "Appointment successfully booked! Reference Number: <strong>$ref</strong>";
}

// Action: Cancel Appointment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_appointment'])) {
    $app_id = intval($_POST['appointment_id']);
    try {
        $stmt = $pdo->prepare("UPDATE appointments SET status = 'cancelled' WHERE id = ? AND patient_id = ?");
        $stmt->execute([$app_id, $patient_id]);
        $message = "Appointment reference cancelled successfully.";
    } catch (PDOException $e) {
        $message = "Failed to cancel appointment: " . $e->getMessage();
        $message_type = "error";
    }
}

// Fetch Patient Appointments
$appStmt = $pdo->prepare("
    SELECT a.*, d_user.name as doctor_name, d.specialization, d.consultation_fee, dept.name as department_name
    FROM appointments a
    JOIN doctors d ON a.doctor_id = d.id
    JOIN users d_user ON d.user_id = d_user.id
    JOIN departments dept ON d.department_id = dept.id
    WHERE a.patient_id = ?
    ORDER BY a.appointment_date DESC, a.appointment_time ASC
");
$appStmt->execute([$patient_id]);
$appointments = $appStmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Appointments | ApexCare</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

    <div class="liquid-bg-container">
        <div class="blob blob-1"></div>
        <div class="blob blob-2"></div>
    </div>

    <!-- Header Navigation -->
    <header class="glass-header">
        <a href="index.php" class="logo-brand">
            <div class="logo-icon"><i class="fa-solid fa-notes-medical"></i></div>
            <span>Apex<span class="text-gradient">Care</span></span>
        </a>

        <div style="display: flex; gap: 1rem; align-items: center;">
            <span style="color: var(--text-muted); font-size: 0.9rem;">Patient: <strong style="color: #fff;"><?= htmlspecialchars($_SESSION['name']) ?></strong></span>
            <a href="book_appointment.php" class="btn-glass" style="padding: 0.6rem 1.2rem; font-size: 0.85rem;"><i class="fa-solid fa-plus"></i> New Booking</a>
            <a href="logout.php" class="btn-glass btn-glass-danger" style="padding: 0.6rem 1.2rem; font-size: 0.85rem;"><i class="fa-solid fa-power-off"></i> Logout</a>
        </div>
    </header>

    <main class="main-wrapper" style="padding-bottom: 5rem;">

        <?php if ($message): ?>
            <div class="alert-glass alert-<?= $message_type === 'error' ? 'error' : 'success' ?>">
                <i class="fa-solid fa-circle-check"></i>
                <span><?= $message ?></span>
            </div>
        <?php endif; ?>

        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; margin-bottom: 2.5rem;">
            <div>
                <h1 style="font-size: 2rem;">My Medical <span class="text-gradient">Appointments</span></h1>
                <p style="color: var(--text-muted);">Track consultation schedule, statuses, and digital slips.</p>
            </div>
            <a href="book_appointment.php" class="btn-glass" style="padding: 0.85rem 1.6rem;">
                <i class="fa-solid fa-calendar-plus"></i> Schedule New Visit
            </a>
        </div>

        <div class="glass-panel" style="padding: 2rem;">
            <div style="overflow-x: auto;">
                <table class="table-glass">
                    <thead>
                        <tr>
                            <th>Ref Number</th>
                            <th>Doctor & Specialty</th>
                            <th>Date & Time</th>
                            <th>Fee</th>
                            <th>Symptoms</th>
                            <th>Status</th>
                            <th style="text-align: right;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($appointments)): ?>
                            <tr>
                                <td colspan="7" style="text-align: center; color: var(--text-muted); padding: 3rem;">
                                    <i class="fa-regular fa-calendar-xmark" style="font-size: 2.5rem; color: var(--text-dim); display: block; margin-bottom: 0.75rem;"></i>
                                    You have no scheduled appointments yet.<br>
                                    <a href="book_appointment.php" style="color: var(--primary-cyan); font-weight: 600; text-decoration: none;">Book your first appointment now</a>
                                </td>
                            </tr>
                        <?php endif; ?>

                        <?php foreach ($appointments as $app): ?>
                            <tr>
                                <td><strong style="color: var(--primary-cyan); font-family: monospace; font-size: 0.95rem;"><?= htmlspecialchars($app['appointment_number']) ?></strong></td>
                                <td>
                                    <strong><?= htmlspecialchars($app['doctor_name']) ?></strong>
                                    <div style="font-size: 0.8rem; color: var(--text-muted);"><?= htmlspecialchars($app['department_name']) ?> &bull; <?= htmlspecialchars($app['specialization']) ?></div>
                                </td>
                                <td>
                                    <strong><?= date("M d, Y", strtotime($app['appointment_date'])) ?></strong>
                                    <div style="font-size: 0.8rem; color: var(--text-muted);"><?= date("g:i A", strtotime($app['appointment_time'])) ?></div>
                                </td>
                                <td><strong style="color: var(--accent-emerald);">$<?= number_format($app['consultation_fee'], 2) ?></strong></td>
                                <td><span style="font-size: 0.85rem; color: var(--text-muted);"><?= htmlspecialchars($app['symptoms'] ?: 'General Checkup') ?></span></td>
                                <td>
                                    <span class="badge-status badge-<?= htmlspecialchars($app['status']) ?>">
                                        <?= strtoupper($app['status']) ?>
                                    </span>
                                </td>
                                <td style="text-align: right;">
                                    <?php if ($app['status'] === 'pending' || $app['status'] === 'confirmed'): ?>
                                        <form action="patient_dashboard.php" method="POST" onsubmit="return confirmDelete('Are you sure you want to cancel this appointment?');" style="display: inline;">
                                            <input type="hidden" name="cancel_appointment" value="1">
                                            <input type="hidden" name="appointment_id" value="<?= $app['id'] ?>">
                                            <button type="submit" class="btn-glass btn-glass-danger" style="padding: 0.4rem 0.85rem; font-size: 0.8rem;">
                                                <i class="fa-solid fa-ban"></i> Cancel
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <span style="color: var(--text-dim); font-size: 0.8rem;">No actions</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </main>
    <script src="assets/js/main.js"></script>
</body>
</html>
