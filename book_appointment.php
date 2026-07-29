<?php
require_once 'config.php';
requireLogin();

$error = '';
$success = '';

// Pre-selected doctor if came from doctor card click
$preselected_doc_id = intval($_GET['doctor_id'] ?? 0);

// Fetch All Active Doctors with Department Info
$doctors = $pdo->query("
    SELECT d.*, u.name as doctor_name, dept.name as department_name 
    FROM doctors d 
    JOIN users u ON d.user_id = u.id 
    JOIN departments dept ON d.department_id = dept.id
    ORDER BY dept.name ASC, u.name ASC
")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $patient_id = $_SESSION['user_id'];
    $doctor_id = intval($_POST['doctor_id'] ?? 0);
    $appointment_date = trim($_POST['appointment_date'] ?? '');
    $appointment_time = trim($_POST['appointment_time'] ?? '');
    $symptoms = trim($_POST['symptoms'] ?? '');

    if ($doctor_id <= 0 || empty($appointment_date) || empty($appointment_time)) {
        $error = 'Please select a doctor, appointment date, and time slot.';
    } else {
        try {
            // Generate unique appointment reference (e.g., APX-20260724-854)
            $ref_num = 'APX-' . date('Ymd') . '-' . rand(100, 999);

            $insStmt = $pdo->prepare("
                INSERT INTO appointments (appointment_number, patient_id, doctor_id, appointment_date, appointment_time, symptoms, status)
                VALUES (?, ?, ?, ?, ?, ?, 'pending')
            ");
            $insStmt->execute([$ref_num, $patient_id, $doctor_id, $appointment_date, $appointment_time, $symptoms]);

            header("Location: patient_dashboard.php?msg=booked&ref=" . $ref_num);
            exit;
        } catch (PDOException $e) {
            $error = 'Failed to process booking: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Appointment | ApexCare</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body style="display: flex; flex-direction: column; min-height: 100vh;">

    <div class="liquid-bg-container">
        <div class="blob blob-1"></div>
        <div class="blob blob-2"></div>
    </div>

    <!-- Glass Header Navigation -->
    <header class="glass-header">
        <a href="index.php" class="logo-brand">
            <div class="logo-icon"><i class="fa-solid fa-notes-medical"></i></div>
            <span>Apex<span class="text-gradient">Care</span></span>
        </a>
        <div style="display: flex; gap: 1rem; align-items: center;">
            <a href="patient_dashboard.php" class="btn-glass btn-glass-secondary" style="padding: 0.6rem 1.2rem; font-size: 0.85rem;"><i class="fa-solid fa-calendar-check"></i> My Appointments</a>
            <a href="logout.php" class="btn-glass btn-glass-danger" style="padding: 0.6rem 1.2rem; font-size: 0.85rem;"><i class="fa-solid fa-power-off"></i> Logout</a>
        </div>
    </header>

    <main class="main-wrapper" style="max-width: 750px; padding-bottom: 5rem;">

        <div style="text-align: center; margin-bottom: 2rem;">
            <h1 class="hero-title" style="font-size: 2.4rem; margin-bottom: 0.5rem;">Book <span class="text-gradient">Doctor Appointment</span></h1>
            <p style="color: var(--text-muted);">Select your specialist, date, and preferred time slot for consultation.</p>
        </div>

        <div class="glass-panel" style="padding: 2.5rem;">

            <?php if ($error): ?>
                <div class="alert-glass alert-error">
                    <i class="fa-solid fa-circle-exclamation"></i>
                    <span><?= htmlspecialchars($error) ?></span>
                </div>
            <?php endif; ?>

            <form action="book_appointment.php" method="POST">

                <!-- Doctor Selection Dropdown -->
                <div class="form-group">
                    <label class="form-label" for="doctor_id"><i class="fa-solid fa-user-doctor" style="color: var(--primary-cyan);"></i> Select Specialist Doctor *</label>
                    <select id="doctor_id" name="doctor_id" class="form-control" required onchange="updateDocDetails(this)">
                        <option value="">-- Choose Specialist Doctor --</option>
                        <?php foreach ($doctors as $doc): ?>
                            <option value="<?= $doc['id'] ?>" 
                                data-fee="<?= number_format($doc['consultation_fee'], 2) ?>" 
                                data-days="<?= htmlspecialchars($doc['availability_days']) ?>"
                                <?= $preselected_doc_id === $doc['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($doc['doctor_name']) ?> (<?= htmlspecialchars($doc['department_name']) ?> &bull; Fee: ₹<?= number_format($doc['consultation_fee'], 2) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Info Box for Selected Doctor -->
                <div id="docInfoBox" class="glass-card" style="display: none; margin-bottom: 1.5rem; background: rgba(0, 242, 254, 0.05); border-color: rgba(0, 242, 254, 0.2);">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <div style="font-size: 0.8rem; color: var(--text-muted);">Consultation Fee</div>
                            <div id="infoFee" style="font-size: 1.3rem; font-weight: 800; color: var(--accent-emerald);">₹0.00</div>
                        </div>
                        <div>
                            <div style="font-size: 0.8rem; color: var(--text-muted);">Available Days</div>
                            <div id="infoDays" style="font-size: 0.9rem; font-weight: 600; color: var(--primary-cyan);">Mon-Fri</div>
                        </div>
                    </div>
                </div>

                <!-- Date and Time Picker Grid -->
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem;">
                    <div class="form-group">
                        <label class="form-label" for="appointment_date"><i class="fa-solid fa-calendar-day" style="color: var(--primary-cyan);"></i> Preferred Date *</label>
                        <input type="date" id="appointment_date" name="appointment_date" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="appointment_time"><i class="fa-solid fa-clock" style="color: var(--primary-cyan);"></i> Preferred Time *</label>
                        <select id="appointment_time" name="appointment_time" class="form-control" required>
                            <option value="09:00:00">09:00 AM (Morning Slot)</option>
                            <option value="10:30:00">10:30 AM (Morning Slot)</option>
                            <option value="11:45:00">11:45 AM (Morning Slot)</option>
                            <option value="14:00:00">02:00 PM (Afternoon Slot)</option>
                            <option value="15:30:00">03:30 PM (Afternoon Slot)</option>
                            <option value="17:00:00">05:00 PM (Evening Slot)</option>
                        </select>
                    </div>
                </div>

                <!-- Health Notes / Symptoms -->
                <div class="form-group" style="margin-bottom: 2rem;">
                    <label class="form-label" for="symptoms"><i class="fa-solid fa-notes-medical" style="color: var(--primary-cyan);"></i> Health Concerns / Symptoms (Optional)</label>
                    <textarea id="symptoms" name="symptoms" class="form-control" rows="3" placeholder="Briefly describe your symptoms or reason for visit..."></textarea>
                </div>

                <button type="submit" class="btn-glass" style="width: 100%; padding: 1rem; font-size: 1.05rem;">
                    Confirm & Reserve Slot <i class="fa-solid fa-check-double"></i>
                </button>
            </form>

        </div>
    </main>

    <script src="assets/js/main.js"></script>
    <script>
        function updateDocDetails(select) {
            const opt = select.options[select.selectedIndex];
            const infoBox = document.getElementById('docInfoBox');
            if (select.value) {
                document.getElementById('infoFee').textContent = '₹' + opt.getAttribute('data-fee');
                document.getElementById('infoDays').textContent = opt.getAttribute('data-days');
                infoBox.style.display = 'block';
            } else {
                infoBox.style.display = 'none';
            }
        }

        // Trigger on initial page load if doctor preselected
        document.addEventListener('DOMContentLoaded', () => {
            const docSelect = document.getElementById('doctor_id');
            if (docSelect.value) updateDocDetails(docSelect);
        });
    </script>
</body>
</html>