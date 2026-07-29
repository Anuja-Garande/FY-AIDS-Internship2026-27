<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

$userId = $_SESSION['user_id'];
$successMessage = '';
$errorMessage = '';

// DYNAMIC LIVE CHECK: Establish up-to-date tracking classification limits
$adminCheck = $pdo->prepare("SELECT emp_type FROM users WHERE id = ?");
$adminCheck->execute([$userId]);
$currentUserType = $adminCheck->fetchColumn() ?: '';

// Process personal leave form submitted by an administrator
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_personal_leave'])) {
    $leaveType = trim($_POST['leave_type']);
    $startDate = trim($_POST['start_date']);
    $endDate = trim($_POST['end_date']);
    $reason    = trim($_POST['reason']);

    if (!empty($leaveType) && !empty($startDate) && !empty($endDate) && !empty($reason)) {
        $stmt = $pdo->prepare("INSERT INTO leave_requests (user_id, leave_type, start_date, end_date, reason, status) VALUES (?, ?, ?, ?, ?, 'pending')");
        $stmt->execute([$userId, $leaveType, $startDate, $endDate, $reason]);
        $successMessage = 'Leave request broadcast successfully to the HEAD approval engine.';
    } else {
        $errorMessage = 'Please complete all form vectors.';
    }
}

// Global dashboard aggregations calculations
$totalEmp = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'employee'")->fetchColumn();
$pendingLeaves = $pdo->query("SELECT COUNT(*) FROM leave_requests WHERE status = 'pending'")->fetchColumn();
$typeCounts = $pdo->query("SELECT emp_type, COUNT(*) as count FROM users GROUP BY emp_type")->fetchAll();
$recentLeaves = $pdo->query("SELECT lr.*, u.name, u.emp_type FROM leave_requests lr JOIN users u ON lr.user_id = u.id ORDER BY lr.applied_at DESC LIMIT 5")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <title>Zeal Command Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <script>tailwind.config = { darkMode: 'class' }</script>
    <style>body { font-family: 'Plus Jakarta Sans', sans-serif; }</style>
</head>
<body class="bg-slate-50 dark:bg-[#0b0f19] text-slate-800 dark:text-slate-100 pl-64 min-h-screen font-sans antialiased transition-colors duration-200">

    <?php include '../includes/sidebar.php'; ?>

    <div class="max-w-7xl mx-auto px-8 py-10 space-y-10">
        
        <!-- Welcome Dynamic Banner Header Component -->
        <div class="bg-white dark:bg-[#0e1626] border border-slate-200 dark:border-slate-800/60 p-8 rounded-3xl shadow-xl flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 transition-all duration-300">
            <div>
                <h1 class="text-3xl font-black text-slate-900 dark:text-white tracking-tight">Zeal Workspace Operations Hub</h1>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Strategic resource allocation infrastructure active at corporate workspace node.</p>
            </div>
            <span class="px-4 py-2 bg-blue-500/10 border border-blue-500/30 text-blue-600 dark:text-blue-400 font-bold text-xs rounded-xl tracking-widest uppercase">Admin Active Platform</span>
        </div>

        <?php if($successMessage): ?><div class="bg-emerald-500/10 border border-emerald-500/30 text-emerald-600 dark:text-emerald-400 rounded-2xl p-4 text-sm font-semibold animate-pulse"><?= htmlspecialchars($successMessage) ?></div><?php endif; ?>
        <?php if($errorMessage): ?><div class="bg-rose-500/10 border border-rose-500/30 text-rose-600 dark:text-rose-400 rounded-2xl p-4 text-sm font-semibold"><?= htmlspecialchars($errorMessage) ?></div><?php endif; ?>

        <!-- PERSONAL REQUEST MODULE INJECTION LAYER -->
        <?php if (isset($_GET['request_mode']) && $_GET['request_mode'] === 'personal' && $currentUserType !== 'head'): ?>
            <div class="bg-white dark:bg-[#0e1626] p-8 rounded-3xl border border-slate-200 dark:border-slate-800/60 shadow-xl space-y-6">
                <div>
                    <h3 class="text-xl font-bold text-slate-900 dark:text-white tracking-wide">Submit Personal Absence Request</h3>
                    <p class="text-slate-400 text-xs mt-0.5">Filing updates route securely to the **HEAD** console workspace only. Other admins cannot process this record.</p>
                </div>
                
                <form action="" method="POST" class="space-y-4">
                    <input type="hidden" name="submit_personal_leave" value="1">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-1">Leave Category Track</label>
                            <select name="leave_type" required class="w-full px-4 py-2.5 bg-slate-50 dark:bg-[#070b14] border border-slate-200 dark:border-slate-800 rounded-xl text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:outline-none transition">
                                <option value="Casual Leave">Casual Leave</option>
                                <option value="Medical Leave">Medical Leave</option>
                                <option value="Unpaid Sabbatical">Unpaid Sabbatical</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-400 tracking-widest mb-1">Execution Start</label>
                            <input type="date" name="start_date" required class="w-full px-4 py-2.5 bg-slate-50 dark:bg-[#070b14] border border-slate-200 dark:border-slate-800 rounded-xl text-sm text-slate-900 dark:text-white font-mono focus:ring-2 focus:ring-indigo-500 focus:outline-none transition">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-400 tracking-widest mb-1">Termination Date</label>
                            <input type="date" name="end_date" required class="w-full px-4 py-2.5 bg-slate-50 dark:bg-[#070b14] border border-slate-200 dark:border-slate-800 rounded-xl text-sm text-slate-900 dark:text-white font-mono focus:ring-2 focus:ring-indigo-500 focus:outline-none transition">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-400 tracking-widest mb-1">Justification Reason</label>
                        <textarea name="reason" rows="3" required placeholder="State systemic reason for request..." class="w-full px-4 py-2.5 bg-slate-50 dark:bg-[#070b14] border border-slate-200 dark:border-slate-800 rounded-xl text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:outline-none transition"></textarea>
                    </div>
                    <div class="flex justify-end space-x-2">
                        <a href="dashboard.php" class="px-5 py-2.5 bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs font-bold rounded-xl transition">Cancel</a>
                        <button type="submit" class="bg-gradient-to-r from-indigo-600 to-violet-600 text-white font-bold text-xs px-6 py-2.5 rounded-xl shadow-md transform active:scale-95 transition">Submit Application</button>
                    </div>
                </form>
            </div>
        <?php endif; ?>

        <!-- Metric Command Cards Grid Layout -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-gradient-to-br from-blue-600 via-blue-700 to-indigo-800 p-6 rounded-3xl text-white shadow-xl shadow-blue-900/20 transition-all duration-300 hover:scale-[1.02] hover:-translate-y-1 group">
                <p class="text-xs font-bold uppercase tracking-widest text-blue-200">Active Members</p>
                <h3 class="text-5xl font-black mt-3 tracking-tight group-hover:scale-105 transition duration-300"><?= $totalEmp ?></h3>
                <div class="w-full bg-black/20 h-1.5 rounded-full mt-5 overflow-hidden"><div class="bg-blue-300 h-full w-4/5 rounded-full"></div></div>
            </div>
            <div class="bg-gradient-to-br from-amber-500 via-orange-500 to-red-600 p-6 rounded-3xl text-white shadow-xl shadow-orange-900/20 transition-all duration-300 hover:scale-[1.02] hover:-translate-y-1 group">
                <p class="text-xs font-bold uppercase tracking-widest text-amber-100">Pending Leave Logs</p>
                <h3 class="text-5xl font-black mt-3 tracking-tight group-hover:scale-105 transition duration-300"><?= $pendingLeaves ?></h3>
                <div class="w-full bg-black/20 h-1.5 rounded-full mt-5 overflow-hidden"><div class="bg-amber-300 h-full w-2/5 rounded-full"></div></div>
            </div>
            <div class="bg-gradient-to-br from-emerald-500 via-teal-500 to-cyan-600 p-6 rounded-3xl text-white shadow-xl shadow-emerald-900/20 transition-all duration-300 hover:scale-[1.02] hover:-translate-y-1 group">
                <p class="text-xs font-bold uppercase tracking-widest text-emerald-100">Engine Performance</p>
                <h3 class="text-5xl font-black mt-3 tracking-tight group-hover:scale-105 transition duration-300">100%</h3>
                <div class="w-full bg-black/20 h-1.5 rounded-full mt-5 overflow-hidden"><div class="bg-emerald-300 h-full w-full rounded-full"></div></div>
            </div>
        </div>

        <!-- Segment Category Distribution Matrix Grid -->
        <div class="bg-white dark:bg-[#0e1626] border border-slate-200 dark:border-slate-800/60 p-8 rounded-3xl shadow-xl">
            <h3 class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-6">Classification Distribution Matrix</h3>
            <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-7 gap-4">
                <?php 
                $mappedCounts = array_column($typeCounts, 'count', 'emp_type');
                $allTypes = ['head', 'full-time', 'part-time', 'temporary', 'contract', 'freelancer', 'intern'];
                $styles = [
                    'head'=>'border-purple-200 dark:border-purple-900/40 bg-purple-50 dark:bg-purple-950/20 text-purple-600 dark:text-purple-400',
                    'full-time'=>'border-blue-200 dark:border-blue-900/40 bg-blue-50 dark:bg-blue-950/20 text-blue-600 dark:text-blue-400',
                    'part-time'=>'border-green-200 dark:border-green-900/40 bg-green-50 dark:bg-green-950/20 text-green-600 dark:text-green-400',
                    'temporary'=>'border-amber-200 dark:border-amber-900/40 bg-amber-50 dark:bg-amber-950/20 text-amber-600 dark:text-amber-400',
                    'contract'=>'border-orange-200 dark:border-orange-900/40 bg-orange-50 dark:bg-orange-950/20 text-orange-600 dark:text-orange-400',
                    'freelancer'=>'border-pink-200 dark:border-pink-900/40 bg-pink-50 dark:bg-pink-950/20 text-pink-600 dark:text-pink-400',
                    'intern'=>'border-teal-200 dark:border-teal-900/40 bg-teal-50 dark:bg-teal-950/20 text-teal-600 dark:text-teal-400'
                ];
                foreach($allTypes as $type): 
                    $count = $mappedCounts[$type] ?? 0;
                ?>
                    <div class="border rounded-2xl p-4 text-center transition-all duration-300 transform hover:scale-105 shadow-sm dark:shadow-black/20 <?= $styles[$type] ?>">
                        <p class="text-xs font-bold uppercase tracking-wider opacity-80"><?= $type ?></p>
                        <p class="text-3xl font-black mt-2 tracking-tight text-slate-900 dark:text-white"><?= $count ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Exception Process Table Log Layout Framework Panel -->
        <div class="bg-white dark:bg-[#0e1626] border border-slate-200 dark:border-slate-800/60 rounded-3xl shadow-xl overflow-hidden">
            <div class="px-8 py-5 border-b border-slate-200 dark:border-slate-800/60 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-slate-50/70 dark:bg-[#0a101d]">
                <h3 class="text-sm font-bold text-slate-900 dark:text-slate-200 uppercase tracking-widest">Real-Time Exception Processing Feed</h3>
                <a href="manage-leaves.php" class="text-xs font-bold text-blue-600 dark:text-blue-400 hover:text-white dark:hover:text-white bg-blue-50 dark:bg-blue-500/10 border border-blue-200 dark:border-blue-500/30 px-4 py-2 rounded-xl transition-all duration-300 hover:bg-blue-600 hover:scale-105 active:scale-95 shadow-sm">Open Manager Queue →</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-100 dark:bg-[#070b14] text-slate-500 dark:text-slate-400 uppercase text-xs font-bold tracking-wider border-b border-slate-200 dark:border-slate-800/60">
                            <th class="px-8 py-4">Employee Identity</th>
                            <th class="px-8 py-4">Classification</th>
                            <th class="px-8 py-4">Category Track</th>
                            <th class="px-8 py-4">Timeline Span</th>
                            <th class="px-8 py-4">Pipeline State</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-800/40 text-sm font-semibold text-slate-700 dark:text-slate-300">
                        <?php if(empty($recentLeaves)): ?>
                            <tr><td colspan="5" class="px-8 py-12 text-center text-slate-400 dark:text-slate-500 font-medium">No active workforce exceptions tracked inside internal data pools.</td></tr>
                        <?php else: ?>
                            <?php foreach($recentLeaves as $row): ?>
                                <tr class="hover:bg-slate-50/80 dark:hover:bg-[#121c30]/40 transition-colors duration-200">
                                    <td class="px-8 py-5 text-slate-900 dark:text-white font-bold"><?= htmlspecialchars($row['name']) ?></td>
                                    <td class="px-8 py-5 text-xs"><span class="px-2.5 py-1 bg-slate-200 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-700 dark:text-slate-300 uppercase tracking-wider font-extrabold text-[10px]"><?= htmlspecialchars($row['emp_type']) ?></span></td>
                                    <td class="px-8 py-5 text-slate-600 dark:text-slate-400"><?= htmlspecialchars($row['leave_type']) ?></td>
                                    <td class="px-8 py-5 text-slate-500 dark:text-slate-400 font-mono text-xs tracking-wide"><?= $row['start_date'] ?> / <?= $row['end_date'] ?></td>
                                    <td class="px-8 py-5">
                                        <?php $c = $row['status'] === 'approved' ? 'text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-500/10 border-emerald-200 dark:border-emerald-500/30' : ($row['status'] === 'rejected' ? 'text-rose-600 dark:text-rose-400 bg-rose-50 dark:bg-rose-500/10 border-rose-200 dark:border-rose-500/30' : 'text-amber-600 dark:text-amber-400 bg-amber-50 dark:bg-amber-500/10 border-amber-200 dark:border-amber-500/30'); ?>
                                        <span class="px-3 py-1 border rounded-full text-xs font-black capitalize tracking-wide <?= $c ?>"><?= htmlspecialchars($row['status']) ?></span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>