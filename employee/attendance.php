<?php
session_start();
require_once '../config/db.php';

// UPDATED ACCESSIBILITY GATE: Allows both active admins and employees to log entries
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin', 'employee'])) {
    header("Location: ../auth/login.php");
    exit;
}

$userId = $_SESSION['user_id'];
$today = date('Y-m-d');
$msg = '';

// Process Attendance Check-In Request Action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['check_in'])) {
    try {
        $stmt = $pdo->prepare("INSERT INTO attendance (user_id, work_date, status) VALUES (?, ?, 'present')");
        $stmt->execute([$userId, $today]);
        $msg = 'Success: Attendance logged for today!';
    } catch (\PDOException $e) {
        $msg = 'Alert: Today\'s work timestamp has already been recorded.';
    }
}

// Fetch general analytics data vectors
$daysWorked = $pdo->prepare("SELECT COUNT(*) FROM attendance WHERE user_id = ? AND status = 'present'");
$daysWorked->execute([$userId]);
$totalPresent = $daysWorked->fetchColumn();

// Load absolute calendar mapping coordinates matrix
$attendanceLog = $pdo->prepare("SELECT work_date, status FROM attendance WHERE user_id = ?");
$attendanceLog->execute([$userId]);
$records = $attendanceLog->fetchAll(PDO::FETCH_KEY_PAIR);
?>
<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <title>Zeal - Attendance Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config = { darkMode: 'class' }</script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>body { font-family: 'Plus Jakarta Sans', sans-serif; }</style>
</head>
<body class="bg-slate-50 dark:bg-[#0b0f19] text-slate-800 dark:text-slate-100 pl-64 min-h-screen antialiased transition-colors duration-200">

    <?php include '../includes/sidebar.php'; ?>

    <div class="max-w-4xl mx-auto px-8 py-10 space-y-6">
        <div class="bg-white dark:bg-[#0e1626] border border-slate-200 dark:border-slate-800/60 p-8 rounded-3xl shadow-xl flex justify-between items-center transition-all duration-300">
            <div>
                <h2 class="text-3xl font-black tracking-tight">Time & Attendance Engine</h2>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Log localized active working hours tracking indexes.</p>
            </div>
            
            <form action="" method="POST">
                <button type="submit" name="check_in" class="bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-bold px-6 py-3 rounded-xl shadow-lg transform active:scale-95 transition-all duration-300 hover:scale-105">
                    ☀️ Clock In Today
                </button>
            </form>
        </div>

        <?php if($msg): ?>
            <div class="bg-blue-500/10 border border-blue-500/30 text-blue-600 dark:text-blue-400 rounded-xl p-4 text-xs font-bold"><?= htmlspecialchars($msg) ?></div>
        <?php endif; ?>

        <!-- Statistics Node -->
        <div class="bg-white dark:bg-[#0e1626] border border-slate-200 dark:border-slate-800/60 p-6 rounded-3xl shadow-md flex items-center justify-between transition-all duration-300">
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Total Validated Operational Days worked</p>
                <h4 class="text-4xl font-black mt-1 text-indigo-600 dark:text-indigo-400"><?= $totalPresent ?> Days</h4>
            </div>
            <span class="text-3xl">💼</span>
        </div>

        <!-- Visual Calendar Representation -->
        <div class="bg-white dark:bg-[#0e1626] border border-slate-200 dark:border-slate-800/60 p-8 rounded-3xl shadow-xl space-y-4 transition-all duration-300">
            <h3 class="text-lg font-bold">Dynamic Monthly Calendar Visualization Matrix</h3>
            <div class="grid grid-cols-7 gap-2 text-center text-xs font-bold uppercase text-slate-400 mb-2">
                <div>Sun</div><div>Mon</div><div>Tue</div><div>Wed</div><div>Thu</div><div>Fri</div><div>Sat</div>
            </div>
            <div class="grid grid-cols-7 gap-2" id="calendar-grid">
                <?php
                $startOfMonth = date('Y-m-01');
                $daysInMonth = date('t');
                $dayOfWeekOffset = date('w', strtotime($startOfMonth));

                // Render blank offsets to align calendar perfectly
                for ($i = 0; $i < $dayOfWeekOffset; $i++) {
                    echo '<div class="p-4 bg-slate-100/30 dark:bg-slate-900/20 rounded-xl"></div>';
                }

                // Render active days
                for ($day = 1; $day <= $daysInMonth; $day++) {
                    $currentDateStr = date('Y-m-') . sprintf('%02d', $day);
                    $bgClass = 'bg-slate-100 dark:bg-slate-900 text-slate-500';
                    $badge = '';

                    if (isset($records[$currentDateStr])) {
                        if ($records[$currentDateStr] === 'present') {
                            $bgClass = 'bg-emerald-500/20 border border-emerald-500 text-emerald-400';
                            $badge = '<span class="block text-[9px] font-bold uppercase mt-1">WORKED</span>';
                        }
                    } else if ($currentDateStr < $today) {
                        $bgClass = 'bg-rose-500/20 border border-rose-500 text-rose-400';
                        $badge = '<span class="block text-[9px] font-bold uppercase mt-1">ABSENT</span>';
                    } else if ($currentDateStr === $today) {
                        $bgClass = 'bg-blue-500/5 border-2 border-blue-500 text-blue-600 dark:text-blue-400 font-extrabold';
                        $badge = '<span class="block text-[9px] font-black uppercase mt-1 tracking-wider text-blue-500">TODAY</span>';
                    }

                    echo '<div class="p-3 rounded-xl text-center font-bold text-sm ' . $bgClass . '">';
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