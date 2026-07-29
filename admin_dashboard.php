<?php
require_once 'config.php';
requireAdmin();

$message = '';
$message_type = 'success';

// Handle Actions (Add Doctor, Edit Doctor, Delete Doctor, Update Appointment Status)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // Action 1: Add Doctor
    if ($action === 'add_doctor') {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $username = trim($_POST['username'] ?? '');
        $password = trim($_POST['password'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $department_id = intval($_POST['department_id'] ?? 0);
        $specialization = trim($_POST['specialization'] ?? '');
        $qualification = trim($_POST['qualification'] ?? '');
        $experience_years = intval($_POST['experience_years'] ?? 0);
        $consultation_fee = floatval($_POST['consultation_fee'] ?? 0);
        $availability_days = trim($_POST['availability_days'] ?? 'Mon, Tue, Wed, Thu, Fri');
        $available_time_start = trim($_POST['available_time_start'] ?? '09:00');
        $available_time_end = trim($_POST['available_time_end'] ?? '17:00');
        $bio = trim($_POST['bio'] ?? '');
        $image_url = trim($_POST['image_url'] ?? '');

        // Phone validation: MUST be exactly 10 digits
        if (!empty($phone) && !preg_match('/^[0-9]{10}$/', $phone)) {
            $message = "Phone number must be exactly 10 digits.";
            $message_type = "error";
        } elseif (empty($name) || empty($email) || empty($username) || empty($password) || empty($department_id)) {
            $message = "Please fill in all required fields for the new doctor.";
            $message_type = "error";
        } else {
            try {
                $pdo->beginTransaction();

                // 1. Insert into users table
                $hashed = password_hash($password, PASSWORD_DEFAULT);
                $uStmt = $pdo->prepare("INSERT INTO users (name, email, username, password, role, phone) VALUES (?, ?, ?, ?, 'doctor', ?)");
                $uStmt->execute([$name, $email, $username, $hashed, $phone]);
                $user_id = $pdo->lastInsertId();

                // 2. Insert into doctors table
                if (empty($image_url)) {
                    $image_url = 'https://images.unsplash.com/photo-1622253692010-333f2da6031d?q=80&w=400&auto=format&fit=crop';
                }

                $dStmt = $pdo->prepare("INSERT INTO doctors (user_id, department_id, specialization, qualification, experience_years, consultation_fee, availability_days, available_time_start, available_time_end, bio, image_url) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $dStmt->execute([$user_id, $department_id, $specialization, $qualification, $experience_years, $consultation_fee, $availability_days, $available_time_start, $available_time_end, $bio, $image_url]);

                $pdo->commit();
                $message = "Doctor successfully registered and added to the medical system!";
            } catch (PDOException $e) {
                $pdo->rollBack();
                $message = "Error adding doctor: " . $e->getMessage();
                $message_type = "error";
            }
        }
    }

    // Action 2: Edit Doctor Info
    if ($action === 'edit_doctor') {
        $doctor_id = intval($_POST['doctor_id'] ?? 0);
        $user_id = intval($_POST['user_id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $department_id = intval($_POST['department_id'] ?? 0);
        $specialization = trim($_POST['specialization'] ?? '');
        $qualification = trim($_POST['qualification'] ?? '');
        $experience_years = intval($_POST['experience_years'] ?? 0);
        $consultation_fee = floatval($_POST['consultation_fee'] ?? 0);
        $availability_days = trim($_POST['availability_days'] ?? '');
        $bio = trim($_POST['bio'] ?? '');
        $image_url = trim($_POST['image_url'] ?? '');

        // Phone validation: MUST be exactly 10 digits
        if (!empty($phone) && !preg_match('/^[0-9]{10}$/', $phone)) {
            $message = "Phone number must be exactly 10 digits.";
            $message_type = "error";
        } else {
            try {
                $pdo->beginTransaction();

                // Update user table
                $uUpdate = $pdo->prepare("UPDATE users SET name = ?, email = ?, phone = ? WHERE id = ?");
                $uUpdate->execute([$name, $email, $phone, $user_id]);

                // Update doctor table
                $dUpdate = $pdo->prepare("UPDATE doctors SET department_id = ?, specialization = ?, qualification = ?, experience_years = ?, consultation_fee = ?, availability_days = ?, bio = ?, image_url = ? WHERE id = ?");
                $dUpdate->execute([$department_id, $specialization, $qualification, $experience_years, $consultation_fee, $availability_days, $bio, $image_url, $doctor_id]);

                $pdo->commit();
                $message = "Doctor profile updated successfully!";
            } catch (PDOException $e) {
                $pdo->rollBack();
                $message = "Error updating doctor: " . $e->getMessage();
                $message_type = "error";
            }
        }
    }

    // Action 3: Delete Doctor Account
    if ($action === 'delete_doctor') {
        $user_id = intval($_POST['user_id'] ?? 0);
        if ($user_id > 0) {
            try {
                // Delete user (cascade automatically deletes doctor profile & appointments)
                $delStmt = $pdo->prepare("DELETE FROM users WHERE id = ? AND role = 'doctor'");
                $delStmt->execute([$user_id]);
                $message = "Doctor account permanently removed from database.";
            } catch (PDOException $e) {
                $message = "Error deleting doctor account: " . $e->getMessage();
                $message_type = "error";
            }
        }
    }

    // Action 4: Update Appointment Status
    if ($action === 'update_appointment_status') {
        $app_id = intval($_POST['appointment_id'] ?? 0);
        $new_status = $_POST['status'] ?? 'pending';

        try {
            $stStmt = $pdo->prepare("UPDATE appointments SET status = ? WHERE id = ?");
            $stStmt->execute([$new_status, $app_id]);
            $message = "Appointment status updated to '" . strtoupper($new_status) . "'.";
        } catch (PDOException $e) {
            $message = "Failed to update appointment: " . $e->getMessage();
            $message_type = "error";
        }
    }
}

// Fetch Metrics for Analytics Dashboard
$totalDoctors = $pdo->query("SELECT COUNT(*) FROM doctors")->fetchColumn();
$totalPatients = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'patient'")->fetchColumn();
$totalAppointments = $pdo->query("SELECT COUNT(*) FROM appointments")->fetchColumn();
$totalRevenue = $pdo->query("SELECT COALESCE(SUM(d.consultation_fee), 0) FROM appointments a JOIN doctors d ON a.doctor_id = d.id WHERE a.status = 'completed'")->fetchColumn();

// Fetch All Departments
$departments = $pdo->query("SELECT * FROM departments ORDER BY name ASC")->fetchAll();

// Fetch All Doctors
$doctorsList = $pdo->query("
    SELECT d.*, u.name as doctor_name, u.email, u.username, u.phone, dept.name as department_name 
    FROM doctors d 
    JOIN users u ON d.user_id = u.id 
    JOIN departments dept ON d.department_id = dept.id
    ORDER BY d.id DESC
")->fetchAll();

// Fetch All Appointments
$appointmentsList = $pdo->query("
    SELECT a.*, p.name as patient_name, p.phone as patient_phone, d_user.name as doctor_name, dept.name as department_name
    FROM appointments a
    JOIN users p ON a.patient_id = p.id
    JOIN doctors d ON a.doctor_id = d.id
    JOIN users d_user ON d.user_id = d_user.id
    JOIN departments dept ON d.department_id = dept.id
    ORDER BY a.appointment_date DESC, a.appointment_time ASC
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Control Center | ApexCare</title>
    
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
            <div class="logo-icon"><i class="fa-solid fa-user-shield"></i></div>
            <span>Admin<span class="text-gradient">Portal</span></span>
        </a>

        <div style="display: flex; gap: 1rem; align-items: center;">
            <span style="color: var(--text-muted); font-size: 0.9rem;">Welcome, <strong style="color: #fff;"><?= htmlspecialchars($_SESSION['name'] ?? 'Admin') ?></strong></span>
            <a href="index.php" class="btn-glass btn-glass-secondary" style="padding: 0.6rem 1.2rem; font-size: 0.85rem;"><i class="fa-solid fa-globe"></i> Website</a>
            <a href="logout.php" class="btn-glass btn-glass-danger" style="padding: 0.6rem 1.2rem; font-size: 0.85rem;"><i class="fa-solid fa-power-off"></i> Logout</a>
        </div>
    </header>

    <main class="main-wrapper" style="padding-bottom: 5rem;">

        <!-- Alert Notification Banner -->
        <?php if ($message): ?>
            <div class="alert-glass alert-<?= $message_type === 'error' ? 'error' : 'success' ?>">
                <i class="fa-solid fa-<?= $message_type === 'error' ? 'circle-exclamation' : 'circle-check' ?>"></i>
                <span><?= htmlspecialchars($message) ?></span>
            </div>
        <?php endif; ?>

        <!-- Dashboard Analytics Grid -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1.5rem; margin-bottom: 3rem;">
            <div class="glass-card" style="display: flex; align-items: center; justify-content: space-between;">
                <div>
                    <div style="color: var(--text-muted); font-size: 0.85rem; font-weight: 600;">Total Active Doctors</div>
                    <div style="font-size: 2.2rem; font-weight: 800; color: var(--primary-cyan);"><?= $totalDoctors ?></div>
                </div>
                <div style="width: 50px; height: 50px; border-radius: 14px; background: rgba(0, 242, 254, 0.15); color: var(--primary-cyan); display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">
                    <i class="fa-solid fa-user-doctor"></i>
                </div>
            </div>

            <div class="glass-card" style="display: flex; align-items: center; justify-content: space-between;">
                <div>
                    <div style="color: var(--text-muted); font-size: 0.85rem; font-weight: 600;">Registered Patients</div>
                    <div style="font-size: 2.2rem; font-weight: 800; color: #4facfe;"><?= $totalPatients ?></div>
                </div>
                <div style="width: 50px; height: 50px; border-radius: 14px; background: rgba(79, 172, 254, 0.15); color: #4facfe; display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">
                    <i class="fa-solid fa-hospital-user"></i>
                </div>
            </div>

            <div class="glass-card" style="display: flex; align-items: center; justify-content: space-between;">
                <div>
                    <div style="color: var(--text-muted); font-size: 0.85rem; font-weight: 600;">Total Appointments</div>
                    <div style="font-size: 2.2rem; font-weight: 800; color: #c084fc;"><?= $totalAppointments ?></div>
                </div>
                <div style="width: 50px; height: 50px; border-radius: 14px; background: rgba(192, 132, 252, 0.15); color: #c084fc; display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">
                    <i class="fa-solid fa-calendar-check"></i>
                </div>
            </div>

            <div class="glass-card" style="display: flex; align-items: center; justify-content: space-between;">
                <div>
                    <div style="color: var(--text-muted); font-size: 0.85rem; font-weight: 600;">Consultation Revenue</div>
                    <div style="font-size: 2.2rem; font-weight: 800; color: var(--accent-emerald);">₹<?= number_format($totalRevenue, 2) ?></div>
                </div>
                <div style="width: 50px; height: 50px; border-radius: 14px; background: rgba(0, 230, 118, 0.15); color: var(--accent-emerald); display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">
                    <i class="fa-solid fa-wallet"></i>
                </div>
            </div>
        </div>

        <!-- DOCTOR MANAGEMENT SECTION -->
        <div class="glass-panel" style="padding: 2rem; margin-bottom: 3.5rem;">
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; margin-bottom: 2rem;">
                <div>
                    <h2 style="font-size: 1.6rem;"><i class="fa-solid fa-user-doctor" style="color: var(--primary-cyan);"></i> Doctor Management</h2>
                    <p style="color: var(--text-muted); font-size: 0.9rem;">Add new doctors, edit specialization & fees, or remove medical staff accounts.</p>
                </div>
                <button onclick="toggleModal('addDoctorModal')" class="btn-glass">
                    <i class="fa-solid fa-user-plus"></i> Add New Doctor
                </button>
            </div>

            <div style="overflow-x: auto;">
                <table class="table-glass">
                    <thead>
                        <tr>
                            <th>Doctor Profile</th>
                            <th>Department & Specialty</th>
                            <th>Experience & Fees</th>
                            <th>Availability</th>
                            <th>Contact</th>
                            <th style="text-align: right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($doctorsList)): ?>
                            <tr>
                                <td colspan="6" style="text-align: center; color: var(--text-muted); padding: 2rem;">No doctors registered in system yet.</td>
                            </tr>
                        <?php endif; ?>

                        <?php foreach ($doctorsList as $doc): ?>
                            <tr>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 0.9rem;">
                                        <img src="<?= htmlspecialchars($doc['image_url'] ?: 'https://images.unsplash.com/photo-1622253692010-333f2da6031d?q=80&w=400&auto=format&fit=crop') ?>" alt="Doctor" style="width: 48px; height: 48px; border-radius: 12px; object-fit: cover; border: 1px solid var(--glass-border);">
                                        <div>
                                            <strong style="display: block; font-size: 0.98rem;"><?= htmlspecialchars($doc['doctor_name']) ?></strong>
                                            <span style="color: var(--text-muted); font-size: 0.8rem;">@<?= htmlspecialchars($doc['username']) ?></span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <strong style="color: var(--primary-cyan); font-size: 0.9rem;"><?= htmlspecialchars($doc['department_name']) ?></strong>
                                    <div style="font-size: 0.82rem; color: var(--text-muted);"><?= htmlspecialchars($doc['specialization']) ?></div>
                                </td>
                                <td>
                                    <div style="font-weight: 700; color: var(--accent-emerald);">₹<?= number_format($doc['consultation_fee'], 2) ?></div>
                                    <div style="font-size: 0.8rem; color: var(--text-muted);"><?= htmlspecialchars($doc['experience_years']) ?> Years Exp</div>
                                </td>
                                <td>
                                    <div style="font-size: 0.85rem; font-weight: 500;"><?= htmlspecialchars($doc['availability_days']) ?></div>
                                    <div style="font-size: 0.78rem; color: var(--text-muted);"><?= date("g:i A", strtotime($doc['available_time_start'])) ?> - <?= date("g:i A", strtotime($doc['available_time_end'])) ?></div>
                                </td>
                                <td>
                                    <div style="font-size: 0.85rem;"><?= htmlspecialchars($doc['email']) ?></div>
                                    <div style="font-size: 0.78rem; color: var(--text-muted);"><?= htmlspecialchars($doc['phone'] ?: 'N/A') ?></div>
                                </td>
                                <td style="text-align: right;">
                                    <div style="display: inline-flex; gap: 0.5rem;">
                                        <!-- Edit Doctor Button -->
                                        <button onclick='openEditModal(<?= htmlspecialchars(json_encode($doc, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP), ENT_QUOTES, "UTF-8") ?>)' class="btn-glass btn-glass-secondary" style="padding: 0.4rem 0.8rem; font-size: 0.8rem;" title="Edit Doctor Info">
                                            <i class="fa-solid fa-pen-to-square"></i> Edit
                                        </button>

                                        <!-- Delete Doctor Form -->
                                        <form action="admin_dashboard.php" method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete Dr. <?= addslashes(htmlspecialchars($doc['doctor_name'])) ?>? All associated records will be removed.');">
                                            <input type="hidden" name="action" value="delete_doctor">
                                            <input type="hidden" name="user_id" value="<?= $doc['user_id'] ?>">
                                            <button type="submit" class="btn-glass btn-glass-danger" style="padding: 0.4rem 0.8rem; font-size: 0.8rem;" title="Delete Doctor Account">
                                                <i class="fa-solid fa-trash-can"></i> Delete
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- SYSTEM APPOINTMENTS OVERVIEW -->
        <div class="glass-panel" style="padding: 2rem;">
            <div style="margin-bottom: 1.75rem;">
                <h2 style="font-size: 1.6rem;"><i class="fa-solid fa-calendar-days" style="color: #4facfe;"></i> System Appointments Overview</h2>
                <p style="color: var(--text-muted); font-size: 0.9rem;">View and manage all patient bookings across all hospital departments.</p>
            </div>

            <div style="overflow-x: auto;">
                <table class="table-glass">
                    <thead>
                        <tr>
                            <th>Booking Ref</th>
                            <th>Patient Name</th>
                            <th>Doctor & Department</th>
                            <th>Date & Time</th>
                            <th>Symptoms</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($appointmentsList)): ?>
                            <tr>
                                <td colspan="7" style="text-align: center; color: var(--text-muted); padding: 2rem;">No appointments booked yet.</td>
                            </tr>
                        <?php endif; ?>

                        <?php foreach ($appointmentsList as $app): ?>
                            <tr>
                                <td><strong style="color: var(--primary-cyan); font-family: monospace; font-size: 0.95rem;"><?= htmlspecialchars($app['appointment_number']) ?></strong></td>
                                <td>
                                    <strong><?= htmlspecialchars($app['patient_name']) ?></strong>
                                    <div style="font-size: 0.8rem; color: var(--text-muted);"><?= htmlspecialchars($app['patient_phone'] ?: 'No Phone') ?></div>
                                </td>
                                <td>
                                    <div><?= htmlspecialchars($app['doctor_name']) ?></div>
                                    <span style="font-size: 0.78rem; color: var(--text-muted);"><?= htmlspecialchars($app['department_name']) ?></span>
                                </td>
                                <td>
                                    <strong><?= date("M d, Y", strtotime($app['appointment_date'])) ?></strong>
                                    <div style="font-size: 0.8rem; color: var(--text-muted);"><?= date("g:i A", strtotime($app['appointment_time'])) ?></div>
                                </td>
                                <td><span style="font-size: 0.85rem; color: var(--text-muted);"><?= htmlspecialchars($app['symptoms'] ?: 'General Consultation') ?></span></td>
                                <td>
                                    <span class="badge-status badge-<?= htmlspecialchars($app['status']) ?>">
                                        <?= strtoupper($app['status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <form action="admin_dashboard.php" method="POST" style="display: flex; gap: 0.4rem;">
                                        <input type="hidden" name="action" value="update_appointment_status">
                                        <input type="hidden" name="appointment_id" value="<?= $app['id'] ?>">
                                        
                                        <select name="status" onchange="this.form.submit()" class="form-control" style="padding: 0.35rem 0.6rem; font-size: 0.8rem; border-radius: 8px;">
                                            <option value="pending" <?= $app['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                                            <option value="confirmed" <?= $app['status'] === 'confirmed' ? 'selected' : '' ?>>Confirm</option>
                                            <option value="completed" <?= $app['status'] === 'completed' ? 'selected' : '' ?>>Complete</option>
                                            <option value="cancelled" <?= $app['status'] === 'cancelled' ? 'selected' : '' ?>>Cancel</option>
                                        </select>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </main>

    <!-- MODAL: ADD DOCTOR -->
    <div id="addDoctorModal" style="display: none; position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; background: rgba(0, 0, 0, 0.75); backdrop-filter: blur(10px); z-index: 2000; overflow-y: auto; padding: 2rem 1rem;">
        <div class="glass-panel" style="max-width: 650px; margin: 2rem auto; padding: 2.5rem; position: relative;">
            <button onclick="toggleModal('addDoctorModal')" style="position: absolute; top: 1.5rem; right: 1.5rem; background: none; border: none; color: #fff; font-size: 1.5rem; cursor: pointer;"><i class="fa-solid fa-xmark"></i></button>

            <h2 style="font-size: 1.6rem; margin-bottom: 1.5rem;"><i class="fa-solid fa-user-plus" style="color: var(--primary-cyan);"></i> Add New Doctor Account</h2>

            <form action="admin_dashboard.php" method="POST">
                <input type="hidden" name="action" value="add_doctor">

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label class="form-label">Doctor Full Name *</label>
                        <input type="text" name="name" class="form-control" placeholder="Dr. Jane Smith" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Department *</label>
                        <select name="department_id" class="form-control" required>
                            <?php foreach ($departments as $dept): ?>
                                <option value="<?= $dept['id'] ?>"><?= htmlspecialchars($dept['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label class="form-label">Email Address *</label>
                        <input type="email" name="email" class="form-control" placeholder="dr.jane@apexcare.com" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Login Username *</label>
                        <input type="text" name="username" class="form-control" placeholder="dr_jane" required>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label class="form-label">Password *</label>
                        <input type="password" name="password" class="form-control" placeholder="••••••••" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Phone Number (10 Digits)</label>
                        <input type="tel" name="phone" class="form-control" placeholder="9876543210" pattern="[0-9]{10}" maxlength="10" title="Must be exactly 10 digits">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label class="form-label">Specialization</label>
                        <input type="text" name="specialization" class="form-control" placeholder="e.g. Interventional Cardiology">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Qualifications</label>
                        <input type="text" name="qualification" class="form-control" placeholder="e.g. MD, FACC, Harvard">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label class="form-label">Experience (Years)</label>
                        <input type="number" name="experience_years" class="form-control" value="8">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Consultation Fee (₹)</label>
                        <input type="number" step="0.01" name="consultation_fee" class="form-control" value="500.00">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Availability Days</label>
                    <input type="text" name="availability_days" class="form-control" value="Mon, Tue, Wed, Thu, Fri">
                </div>

                <div class="form-group">
                    <label class="form-label">Profile Image URL (Optional)</label>
                    <input type="url" name="image_url" class="form-control" placeholder="https://images.unsplash.com/...">
                </div>

                <div class="form-group">
                    <label class="form-label">Doctor Bio</label>
                    <textarea name="bio" class="form-control" rows="3" placeholder="Brief professional description..."></textarea>
                </div>

                <div style="display: flex; gap: 1rem; justify-content: flex-end; margin-top: 2rem;">
                    <button type="button" onclick="toggleModal('addDoctorModal')" class="btn-glass btn-glass-secondary">Cancel</button>
                    <button type="submit" class="btn-glass">Save & Create Doctor</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL: EDIT DOCTOR -->
    <div id="editDoctorModal" style="display: none; position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; background: rgba(0, 0, 0, 0.75); backdrop-filter: blur(10px); z-index: 2000; overflow-y: auto; padding: 2rem 1rem;">
        <div class="glass-panel" style="max-width: 650px; margin: 2rem auto; padding: 2.5rem; position: relative;">
            <button onclick="toggleModal('editDoctorModal')" style="position: absolute; top: 1.5rem; right: 1.5rem; background: none; border: none; color: #fff; font-size: 1.5rem; cursor: pointer;"><i class="fa-solid fa-xmark"></i></button>

            <h2 style="font-size: 1.6rem; margin-bottom: 1.5rem;"><i class="fa-solid fa-user-pen" style="color: var(--primary-cyan);"></i> Edit Doctor Details</h2>

            <form action="admin_dashboard.php" method="POST">
                <input type="hidden" name="action" value="edit_doctor">
                <input type="hidden" id="edit_doctor_id" name="doctor_id">
                <input type="hidden" id="edit_user_id" name="user_id">

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label class="form-label">Doctor Full Name</label>
                        <input type="text" id="edit_name" name="name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Department</label>
                        <select id="edit_department_id" name="department_id" class="form-control" required>
                            <?php foreach ($departments as $dept): ?>
                                <option value="<?= $dept['id'] ?>"><?= htmlspecialchars($dept['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label class="form-label">Email Address</label>
                        <input type="email" id="edit_email" name="email" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Phone Number (10 Digits)</label>
                        <input type="tel" id="edit_phone" name="phone" class="form-control" pattern="[0-9]{10}" maxlength="10" title="Must be exactly 10 digits">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label class="form-label">Specialization</label>
                        <input type="text" id="edit_specialization" name="specialization" class="form-control">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Qualifications</label>
                        <input type="text" id="edit_qualification" name="qualification" class="form-control">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label class="form-label">Experience (Years)</label>
                        <input type="number" id="edit_experience_years" name="experience_years" class="form-control">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Consultation Fee (₹)</label>
                        <input type="number" step="0.01" id="edit_consultation_fee" name="consultation_fee" class="form-control">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Availability Days</label>
                    <input type="text" id="edit_availability_days" name="availability_days" class="form-control">
                </div>

                <div class="form-group">
                    <label class="form-label">Profile Image URL</label>
                    <input type="url" id="edit_image_url" name="image_url" class="form-control">
                </div>

                <div class="form-group">
                    <label class="form-label">Doctor Bio</label>
                    <textarea id="edit_bio" name="bio" class="form-control" rows="3"></textarea>
                </div>

                <div style="display: flex; gap: 1rem; justify-content: flex-end; margin-top: 2rem;">
                    <button type="button" onclick="toggleModal('editDoctorModal')" class="btn-glass btn-glass-secondary">Cancel</button>
                    <button type="submit" class="btn-glass">Update Profile</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function toggleModal(modalId) {
            const modal = document.getElementById(modalId);
            if (modal.style.display === 'none' || modal.style.display === '') {
                modal.style.display = 'block';
            } else {
                modal.style.display = 'none';
            }
        }

        function openEditModal(doctor) {
            document.getElementById('edit_doctor_id').value = doctor.id || '';
            document.getElementById('edit_user_id').value = doctor.user_id || '';
            document.getElementById('edit_name').value = doctor.doctor_name || '';
            document.getElementById('edit_department_id').value = doctor.department_id || '';
            document.getElementById('edit_email').value = doctor.email || '';
            document.getElementById('edit_phone').value = doctor.phone || '';
            document.getElementById('edit_specialization').value = doctor.specialization || '';
            document.getElementById('edit_qualification').value = doctor.qualification || '';
            document.getElementById('edit_experience_years').value = doctor.experience_years || 0;
            document.getElementById('edit_consultation_fee').value = doctor.consultation_fee || 0;
            document.getElementById('edit_availability_days').value = doctor.availability_days || '';
            document.getElementById('edit_image_url').value = doctor.image_url || '';
            document.getElementById('edit_bio').value = doctor.bio || '';

            toggleModal('editDoctorModal');
        }
    </script>
</body>
</html>