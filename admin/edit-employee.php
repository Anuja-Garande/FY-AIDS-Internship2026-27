<?php
session_start();
require_once '../config/db.php';

// Industry Standard RBAC Gatekeeper
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

$id = (int)($_GET['id'] ?? 0);
$successMsg = '';
$errorMsg = '';

// DYNAMIC LIVE CHECK: Get the absolute up-to-date classification of the logged-in administrator
$adminCheck = $pdo->prepare("SELECT emp_type FROM users WHERE id = ?");
$adminCheck->execute([$_SESSION['user_id']]);
$currentUserType = $adminCheck->fetchColumn() ?: '';

// Fetch targeted record context profile metrics before processing actions
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$id]);
$emp = $stmt->fetch();

if (!$emp) {
    die("Target identity profile reference pointer out of scope.");
}

// Handle Personnel Deletion Action Engine
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_employee'])) {
    
    // PERMISSION BOUND MATRIX LOGIC:
    // 1. If the logged-in user is 'head', they bypass all blocks and can delete anyone.
    // 2. If the logged-in user is NOT 'head' and tries to delete an 'admin' or another 'head', intercept and block it.
    if ($currentUserType !== 'head' && ($emp['role'] === 'admin' || $emp['emp_type'] === 'head')) {
        $errorMsg = 'CRITICAL RESTRICTION: Regular administrators can only remove standard employees. Only the system HEAD can purge administrative profiles.';
    } else {
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$id]);
        
        // Redirect cleanly back to the classifications view
        header("Location: classifications.php");
        exit;
    }
}

// Handle updating employee details data payload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['delete_employee'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $role = trim($_POST['role']);
    $emp_type = trim($_POST['emp_type']);
    $contact = trim($_POST['contact_number']);
    $address = trim($_POST['address']);

    $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ?, role = ?, emp_type = ?, contact_number = ?, address = ? WHERE id = ?");
    $stmt->execute([$name, $email, $role, $emp_type, $contact, $address, $id]);
    $successMsg = 'Employee records successfully modified and deployed.';
    
    // Re-fetch clean variables context
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$id]);
    $emp = $stmt->fetch();
}

// Attendance aggregation analysis metrics for the visual audit grid
$daysWorked = $pdo->prepare("SELECT COUNT(*) FROM attendance WHERE user_id = ? AND status = 'present'");
$daysWorked->execute([$id]);
$totalPresent = $daysWorked->fetchColumn();

$attendanceLog = $pdo->prepare("SELECT work_date, status FROM attendance WHERE user_id = ?");
$attendanceLog->execute([$id]);
$records = $attendanceLog->fetchAll(PDO::FETCH_KEY_PAIR);
$today = date('Y-m-d');
?>
<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <title>Modify Workspace Profile Record</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config = { darkMode: 'class' }</script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>body { font-family: 'Plus Jakarta Sans', sans-serif; }</style>
</head>
<body class="bg-slate-50 dark:bg-[#0b0f19] text-slate-800 dark:text-slate-100 pl-64 min-h-screen font-sans antialiased transition-colors duration-200">

    <?php include '../includes/sidebar.php'; ?>

    <div class="max-w-4xl mx-auto px-8 py-12 space-y-6">
        
        <!-- Header Banner Container Component -->
        <div class="flex justify-between items-center bg-white dark:bg-[#0e1626] border border-slate-200 dark:border-slate-800/60 p-8 rounded-3xl shadow-xl transition-all duration-300">
            <div>
                <h2 class="text-3xl font-black text-slate-900 dark:text-white tracking-tight">Modify Identity Profile Matrix</h2>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Override user structural permission metrics and deployment criteria values.</p>
            </div>
            <a href="classifications.php" class="text-xs font-bold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white px-4 py-2.5 rounded-xl transition border border-slate-200 dark:border-slate-700/60 shadow-sm active:scale-95 transform">
                ← Return to Matrix
            </a>
        </div>

        <?php if($successMsg): ?>
            <div class="bg-emerald-500/10 border border-emerald-500/30 text-emerald-600 dark:text-emerald-400 rounded-2xl p-4 text-sm font-semibold animate-pulse"><?= htmlspecialchars($successMsg) ?></div>
        <?php endif; ?>
        <?php if($errorMsg): ?>
            <div class="bg-rose-500/10 border border-rose-500/30 text-rose-600 dark:text-rose-400 rounded-2xl p-4 text-sm font-semibold"><?= htmlspecialchars($errorMsg) ?></div>
        <?php endif; ?>

        <!-- Employee Parameters Modification Form -->
        <form action="" method="POST" class="bg-white dark:bg-[#0e1626] p-8 rounded-3xl shadow-xl border border-slate-200 dark:border-slate-800/60 space-y-6 transition-all duration-300">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-400 tracking-wider mb-2">Legal Identity Full Name</label>
                    <input type="text" name="name" value="<?= htmlspecialchars($emp['name']) ?>" required class="w-full px-4 py-2.5 bg-slate-50 dark:bg-[#070b14] border border-slate-200 dark:border-slate-800 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-900 dark:text-white transition">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-400 tracking-wider mb-2">Network Corporate Email Endpoint</label>
                    <input type="email" name="email" value="<?= htmlspecialchars($emp['email']) ?>" required class="w-full px-4 py-2.5 bg-slate-50 dark:bg-[#070b14] border border-slate-200 dark:border-slate-800 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-900 dark:text-white transition">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-400 tracking-wider mb-2">System Authorization Role</label>
                    <select name="role" required class="w-full px-4 py-2.5 bg-slate-50 dark:bg-[#070b14] border border-slate-200 dark:border-slate-800 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-900 dark:text-white transition">
                        <option value="employee" <?= $emp['role'] === 'employee' ? 'selected' : '' ?>>Employee</option>
                        <option value="admin" <?= $emp['role'] === 'admin' ? 'selected' : '' ?>>Administrator</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-400 tracking-wider mb-2">Workplace Classification Track</label>
                    <select name="emp_type" required class="w-full px-4 py-2.5 bg-slate-50 dark:bg-[#070b14] border border-slate-200 dark:border-slate-800 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-900 dark:text-white transition capitalize">
                        <?php foreach(['head', 'full-time', 'part-time', 'temporary', 'contract', 'freelancer', 'intern'] as $t): ?>
                            <option value="<?= $t ?>" <?= $emp['emp_type'] === $t ? 'selected' : '' ?>><?= $t ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-400 tracking-wider mb-2">Secure Contact Phone Number</label>
                    <input type="text" name="contact_number" value="<?= htmlspecialchars($emp['contact_number'] ?? '') ?>" class="w-full px-4 py-2.5 bg-slate-50 dark:bg-[#070b14] border border-slate-200 dark:border-slate-800 rounded-xl font-mono text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-900 dark:text-white transition">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase text-slate-400 tracking-wider mb-2">Regional Residential Coordinates Address</label>
                <textarea name="address" rows="3" class="w-full px-4 py-2.5 bg-slate-50 dark:bg-[#070b14] border border-slate-200 dark:border-slate-800 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-900 dark:text-white transition"><?= htmlspecialchars($emp['address'] ?? '') ?></textarea>
            </div>

            <div class="pt-4 border-t border-slate-100 dark:border-slate-800/60 flex justify-between items-center">
                <!-- UPDATED CONDITIONAL BUTTON RULE GRID: Unlocks completely for 'head' status profiles -->
                <?php if ($currentUserType === 'head'): ?>
                    <button type="submit" name="delete_employee" value="1" onclick="return confirm('WARNING: You are logged in as HEAD. Are you completely sure you want to permanently purge this account from the infrastructure directory?');" class="bg-gradient-to-r from-red-600 to-rose-600 text-white text-sm font-bold px-6 py-3 rounded-xl shadow-md transition-all transform active:scale-95 hover:scale-105">
                        Purge Account 🗑️
                    </button>
                <?php elseif ($emp['role'] === 'admin' || $emp['emp_type'] === 'head'): ?>
                    <button type="button" disabled class="bg-slate-200 dark:bg-slate-800 text-slate-400 dark:text-slate-500 cursor-not-allowed text-sm font-bold px-6 py-3 rounded-xl border border-slate-300 dark:border-slate-700/60 opacity-60 shadow-inner">
                        Purge Restricted 🔒
                    </button>
                <?php else: ?>
                    <button type="submit" name="delete_employee" value="1" onclick="return confirm('Are you sure you want to remove this employee registry track profile?');" class="bg-gradient-to-r from-red-600 to-rose-600 text-white text-sm font-bold px-6 py-3 rounded-xl shadow-md transition-all transform active:scale-95 hover:scale-105">
                        Purge Employee Record 🗑️
                    </button>
                <?php endif; ?>

                <button type="submit" class="bg-gradient-to-r from-blue-600 to-indigo-600 text-white text-sm font-semibold px-6 py-3 rounded-xl shadow-md transition-all transform active:scale-95 hover:scale-105">
                    Save Internal Records Configuration
                </button>
            </div>
        </form>

        <!-- AUDIT ATTENDANCE CALENDAR VISUALIZATION COMPONENT BLOCK -->
        <div class="bg-white dark:bg-[#0e1626] border border-slate-200 dark:border-slate-800/60 p-8 rounded-3xl shadow-xl space-y-6 transition-all duration-300">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h3 class="text-xl font-black text-slate-900 dark:text-white tracking-tight">Personnel Log Audit Matrix</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Calculated tracking parameters generated via active employee clock-in events.</p>
                </div>
                <div class="bg-emerald-500/10 border border-emerald-500/30 text-emerald-600 dark:text-emerald-400 font-bold px-4 py-2 rounded-xl text-xs uppercase tracking-wider shadow-sm">
                    Total Tracked: <?= $totalPresent ?> Days Present
                </div>
            </div>

            <div class="grid grid-cols-7 gap-2 text-center text-xs font-bold uppercase text-slate-400 dark:text-slate-500 border-b border-slate-100 dark:border-slate-800/60 pb-2">
                <div>Sun</div><div>Mon</div><div>Tue</div><div>Wed</div><div>Thu</div><div>Fri</div><div>Sat</div>
            </div>
            
            <div class="grid grid-cols-7 gap-2">
                <?php
                $startOfMonth = date('Y-m-01');
                $daysInMonth = date('t');
                $dayOfWeekOffset = date('w', strtotime($startOfMonth));

                for ($i = 0; $i < $dayOfWeekOffset; $i++) {
                    echo '<div class="p-4 bg-slate-50/50 dark:bg-slate-900/10 rounded-xl border border-dashed border-slate-200/40 dark:border-slate-800/40"></div>';
                }

                for ($day = 1; $day <= $daysInMonth; $day++) {
                    $currentDateStr = date('Y-m-') . sprintf('%02d', $day);
                    $bgClass = 'bg-slate-50 dark:bg-slate-900/60 text-slate-400 dark:text-slate-600 border border-slate-200/40 dark:border-slate-800/40';
                    $badge = '<span class="block text-[8px] font-medium opacity-50 mt-1">—</span>';

                    if (isset($records[$currentDateStr])) {
                        if ($records[$currentDateStr] === 'present') {
                            $bgClass = 'bg-emerald-500/10 border border-emerald-500/30 text-emerald-600 dark:text-emerald-400 shadow-sm shadow-emerald-500/5';
                            $badge = '<span class="block text-[9px] font-black uppercase mt-1 tracking-wider text-emerald-500">PRESENT</span>';
                        }
                    } else if ($currentDateStr < $today) {
                        $bgClass = 'bg-rose-500/10 border border-rose-500/30 text-rose-600 dark:text-rose-400 shadow-sm shadow-rose-500/5';
                        $badge = '<span class="block text-[9px] font-black uppercase mt-1 tracking-wider text-rose-500">ABSENT</span>';
                    } else if ($currentDateStr === $today) {
                        $bgClass = 'bg-blue-500/5 border-2 border-blue-500 text-blue-600 dark:text-blue-400 font-extrabold';
                        $badge = '<span class="block text-[9px] font-black uppercase mt-1 tracking-wider text-blue-500">TODAY</span>';
                    }

                    echo '<div class="p-3 rounded-xl text-center font-bold text-sm transition-all duration-200 hover:scale-105 ' . $bgClass . '">';
                    echo $day;
                    echo $badge;
                    echo '</div>';
                }
                ?>
            </div>
        </div>

    </div>
</body>
</html>