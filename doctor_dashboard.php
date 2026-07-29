<?php
require_once 'config.php';
requireDoctor();

$user_id = $_SESSION['user_id'];
$message = '';
$message_type = 'success';

// Fetch Doctor Record
$docStmt = $pdo->prepare("
    SELECT d.*, u.name as doctor_name, u.email, dept.name as dept_name 
    FROM doctors d 
    JOIN users u ON d.user_id = u.id 
    JOIN departments dept ON d.department_id = dept.id
    WHERE d.user_id = ?
");
$docStmt->execute([$user_id]);
$doctor = $docStmt->fetch();

// If user is Admin inspecting doctor dashboard without a dedicated doctor row, handle gracefully
if (!$doctor && getUserRole() === 'admin') {
    // Pick first doctor profile for preview
    $docStmt = $pdo->query("
        SELECT d.*, u.name as doctor_name, u.email, dept.name as dept_name 
        FROM doctors d 
        JOIN users u ON d.user_id = u.id 
        JOIN departments dept ON d.department_id = dept.id 
        LIMIT 1
    ");
    $doctor = $docStmt->fetch();
}

$doctor_id = $doctor['id'] ?? 0;

// Update Appointment Status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $app_id = intval($_POST['appointment_id']);
    $new_status = $_POST['status'];

    try {
        $up = $pdo->prepare("UPDATE appointments SET status = ? WHERE id = ? AND doctor_id = ?");
        $up->execute([$new_status, $app_id, $doctor_id]);
        $message = "Appointment updated to " . strtoupper($new_status);
    } catch (PDOException $e) {
        $message = "Failed to update status: " . $e->getMessage();
        $message_type = "error";
    }
}

// Fetch Doctor's Appointments
$appointments = [];
if ($doctor_id > 0) {
    $appStmt = $pdo->prepare("
        SELECT a.*, p.name as patient_name, p.email as patient_email, p.phone as patient_phone, p.gender as patient_gender
        FROM appointments a
        JOIN users p ON a.patient_id = p.id
        WHERE a.doctor_id = ?
        ORDER BY a.appointment_date DESC, a.appointment_time ASC
    ");
    $appStmt->execute([$doctor_id]);
    $appointments = $appStmt->fetchAll();
}

// Stats
$totalApp = count($appointments);
$todayApp = 0;
$todayDate = date('Y-m-d');
foreach ($appointments as $ap) {
    if ($ap['appointment_date'] === $todayDate) $todayApp++;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctor Portal | ApexCare</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

    <div class="liquid-bg-container">
        <div class="blob blob-1"></div>
        <div class="blob blob-3"></div>
    </div>

    <!-- Header Navigation -->
    <header class="glass-header">
        <a href="index.php" class="logo-brand">
            <div class="logo-icon"><i class="fa-solid fa-stethoscope"></i></div>
            <span>Doctor<span class="text-gradient">Portal</span></span>
        </a>

        <div style="display: flex; gap: 1rem; align-items: center;">
            <span style="color: var(--text-muted); font-size: 0.9rem;">Dr. <strong style="color: #fff;"><?= htmlspecialchars($doctor['doctor_name'] ?? $_SESSION['name']) ?></strong></span>
            <a href="index.php" class="btn-glass btn-glass-secondary" style="padding: 0.6rem 1.2rem; font-size: 0.85rem;"><i class="fa-solid fa-globe"></i> Home</a>
            <a href="logout.php" class="btn-glass btn-glass-danger" style="padding: 0.6rem 1.2rem; font-size: 0.85rem;"><i class="fa-solid fa-power-off"></i> Logout</a>
        </div>
    </header>

    <main class="main-wrapper" style="padding-bottom: 5rem;">

        <?php if ($message): ?>
            <div class="alert-glass alert-<?= $message_type === 'error' ? 'error' : 'success' ?>">
                <i class="fa-solid fa-circle-check"></i>
                <span><?= htmlspecialchars($message) ?></span>
            </div>
        <?php endif; ?>

        <!-- Doctor Banner & Stats -->
        <div style="display: grid; grid-template-columns: 1.5fr 1fr; gap: 1.5rem; margin-bottom: 3rem;">
            <div class="glass-panel" style="padding: 2rem; display: flex; align-items: center; gap: 1.5rem;">
                <img src="<?= htmlspecialchars($doctor['image_url'] ?: 'https://images.unsplash.com/photo-1559839734-2b71ea197ec2?q=80&w=400&auto=format&fit=crop') ?>" alt="Doctor" style="width: 85px; height: 85px; border-radius: 20px; object-fit: cover; border: 2px solid var(--primary-cyan);">
                <div>
                    <h2 style="font-size: 1.6rem;"><?= htmlspecialchars($doctor['doctor_name'] ?? $_SESSION['name']) ?></h2>
                    <div style="color: var(--primary-cyan); font-weight: 600; font-size: 0.95rem; margin-bottom: 0.4rem;"><?= htmlspecialchars($doctor['dept_name'] ?? 'General') ?> &bull; <?= htmlspecialchars($doctor['specialization'] ?? 'Specialist') ?></div>
                    <p style="color: var(--text-muted); font-size: 0.85rem;"><?= htmlspecialchars($doctor['qualification'] ?? '') ?> | Days: <?= htmlspecialchars($doctor['availability_days'] ?? 'Mon-Fri') ?></p>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div class="glass-card" style="text-align: center; padding: 1.5rem;">
                    <div style="font-size: 2.2rem; font-weight: 800; color: var(--primary-cyan);"><?= $todayApp ?></div>
                    <div style="font-size: 0.82rem; color: var(--text-muted);">Today's Patients</div>
                </div>
                <div class="glass-card" style="text-align: center; padding: 1.5rem;">
                    <div style="font-size: 2.2rem; font-weight: 800; color: #4facfe;"><?= $totalApp ?></div>
                    <div style="font-size: 0.82rem; color: var(--text-muted);">Total Consultations</div>
                </div>
            </div>
        </div>

        <!-- Appointment Schedule Table -->
        <div class="glass-panel" style="padding: 2rem;">
            <div style="margin-bottom: 1.5rem;">
                <h2 style="font-size: 1.5rem;"><i class="fa-solid fa-calendar-check" style="color: var(--primary-cyan);"></i> Patient Consultations Schedule</h2>
            </div>

            <div style="overflow-x: auto;">
                <table class="table-glass">
                    <thead>
                        <tr>
                            <th>Ref #</th>
                            <th>Patient Information</th>
                            <th>Date & Time</th>
                            <th>Reported Symptoms</th>
                            <th>Status</th>
                            <th>Update Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($appointments)): ?>
                            <tr>
                                <td colspan="6" style="text-align: center; color: var(--text-muted); padding: 2.5rem;">No patient appointments assigned yet.</td>
                            </tr>
                        <?php endif; ?>

                        <?php foreach ($appointments as $app): ?>
                            <tr>
                                <td><strong style="color: var(--primary-cyan); font-family: monospace;"><?= htmlspecialchars($app['appointment_number']) ?></strong></td>
                                <td>
                                    <strong><?= htmlspecialchars($app['patient_name']) ?></strong>
                                    <div style="font-size: 0.8rem; color: var(--text-muted);"><?= htmlspecialchars($app['patient_phone'] ?: 'No Phone') ?> | Gender: <?= ucfirst($app['patient_gender']) ?></div>
                                </td>
                                <td>
                                    <strong><?= date("M d, Y", strtotime($app['appointment_date'])) ?></strong>
                                    <div style="font-size: 0.8rem; color: var(--text-muted);"><?= date("g:i A", strtotime($app['appointment_time'])) ?></div>
                                </td>
                                <td>
                                    <span style="font-size: 0.85rem; color: var(--text-muted);"><?= htmlspecialchars($app['symptoms'] ?: 'Regular Checkup') ?></span>
                                </td>
                                <td>
                                    <span class="badge-status badge-<?= htmlspecialchars($app['status']) ?>">
                                        <?= strtoupper($app['status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <form action="doctor_dashboard.php" method="POST" style="display: flex; gap: 0.5rem; align-items: center;">
                                        <input type="hidden" name="update_status" value="1">
                                        <input type="hidden" name="appointment_id" value="<?= $app['id'] ?>">
                                        <select name="status" class="form-control" style="padding: 0.35rem 0.6rem; font-size: 0.8rem; border-radius: 8px;">
                                            <option value="pending" <?= $app['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                                            <option value="confirmed" <?= $app['status'] === 'confirmed' ? 'selected' : '' ?>>Confirm</option>
                                            <option value="completed" <?= $app['status'] === 'completed' ? 'selected' : '' ?>>Completed</option>
                                            <option value="cancelled" <?= $app['status'] === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                                        </select>
                                        <button type="submit" class="btn-glass" style="padding: 0.35rem 0.7rem; font-size: 0.75rem;">Save</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </main>

</body>
</html>
