<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'employee') {
    header("Location: ../auth/login.php");
    exit;
}

$userId = $_SESSION['user_id'];
$successMessage = '';
$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $leaveType = trim($_POST['leave_type']);
    $startDate = trim($_POST['start_date']);
    $endDate = trim($_POST['end_date']);
    $reason    = trim($_POST['reason']);

    if (!empty($leaveType) && !empty($startDate) && !empty($endDate) && !empty($reason)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO leave_requests (user_id, leave_type, start_date, end_date, reason, status) VALUES (?, ?, ?, ?, ?, 'pending')");
            $stmt->execute([$userId, $leaveType, $startDate, $endDate, $reason]);
            $successMessage = 'Leave request broadcast successfully to the Zeal admin audit queue.';
        } catch (\PDOException $e) {
            $errorMessage = 'Database tracking execution failure.';
        }
    } else {
        $errorMessage = 'Please complete all required forms.';
    }
}

// Fixed database fetch placeholder array optimization query
$stmt = $pdo->prepare("SELECT * FROM leave_requests WHERE user_id = ? ORDER BY applied_at DESC");
$stmt->execute([$userId]);
$myLeaves = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <title>Zeal - Employee Portal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <script>tailwind.config = { darkMode: 'class' }</script>
    <style>body { font-family: 'Plus Jakarta Sans', sans-serif; }</style>
</head>
<body class="bg-slate-50 dark:bg-[#0b0f19] text-slate-800 dark:text-slate-100 pl-64 min-h-screen font-sans antialiased transition-colors duration-200">

    <?php include '../includes/sidebar.php'; ?>

    <div class="max-w-7xl mx-auto px-8 py-10 space-y-8">
        
        <!-- Welcome Header Component Layout Frame -->
        <div class="bg-white dark:bg-[#0e1626] border border-slate-200 dark:border-slate-800/60 p-6 rounded-3xl shadow-xl flex justify-between items-center transition-all duration-300">
            <div>
                <h1 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight">Active Employee Registry Node</h1>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Welcome, <span class="font-bold underline text-indigo-600 dark:text-indigo-400"><?= htmlspecialchars($_SESSION['user_name']) ?></span>. Log variations and process scheduling allocations metrics.</p>
            </div>
        </div>

        <?php if($successMessage): ?>
            <div class="bg-emerald-500/10 border border-emerald-500/30 text-emerald-600 dark:text-emerald-400 rounded-2xl p-4 text-sm font-semibold animate-pulse"><?= htmlspecialchars($successMessage) ?></div>
        <?php endif; ?>
        <?php if($errorMessage): ?>
            <div class="bg-rose-500/10 border border-rose-500/30 text-rose-600 dark:text-rose-400 rounded-2xl p-4 text-sm font-semibold"><?= htmlspecialchars($errorMessage) ?></div>
        <?php endif; ?>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Form Card Wrapper -->
            <div class="bg-white dark:bg-[#0e1626] p-6 rounded-3xl border border-slate-200 dark:border-slate-800/60 shadow-xl h-fit space-y-6 transition-all duration-300">
                <div>
                    <h3 class="text-base font-bold text-slate-900 dark:text-white tracking-wide">File Leave Request Allocation</h3>
                    <p class="text-slate-400 text-xs mt-0.5">Submissions route instantly to the operations hub.</p>
                </div>
                
                <form action="" method="POST" class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-1">Leave Category Track</label>
                        <select name="leave_type" required class="w-full px-4 py-2.5 bg-slate-50 dark:bg-[#070b14] border border-slate-200 dark:border-slate-800 rounded-xl text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:outline-none transition">
                            <option value="Casual Leave">Casual Leave</option>
                            <option value="Medical Leave">Medical Leave / Sick Track</option>
                            <option value="Maternity/Paternity">Parental Allocation Track</option>
                            <option value="Unpaid Sabbatical">Unpaid Sabbatical</option>
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-400 dark:text-slate-500 tracking-widest mb-1">Execution Start</label>
                            <input type="date" name="start_date" required class="w-full px-4 py-2.5 bg-slate-50 dark:bg-[#070b14] border border-slate-200 dark:border-slate-800 rounded-xl text-sm text-slate-900 dark:text-white font-mono focus:ring-2 focus:ring-indigo-500 focus:outline-none transition">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-400 dark:text-slate-500 tracking-widest mb-1">Termination</label>
                            <input type="date" name="end_date" required class="w-full px-4 py-2.5 bg-slate-50 dark:bg-[#070b14] border border-slate-200 dark:border-slate-800 rounded-xl text-sm text-slate-900 dark:text-white font-mono focus:ring-2 focus:ring-indigo-500 focus:outline-none transition">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-400 dark:text-slate-500 tracking-widest mb-1">Operational Justification</label>
                        <textarea name="reason" rows="4" required placeholder="Provide concise organizational structural reasoning metrics..." class="w-full px-4 py-2.5 bg-slate-50 dark:bg-[#070b14] border border-slate-200 dark:border-slate-800 rounded-xl text-sm text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-600 focus:ring-2 focus:ring-indigo-500 focus:outline-none transition"></textarea>
                    </div>
                    <button type="submit" class="w-full bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-500 hover:to-violet-500 text-white font-bold py-3 rounded-xl transition-all shadow-lg shadow-indigo-950/40 transform active:scale-95 hover:scale-[1.01]">
                        Submit Leave Application
                    </button>
                </form>
            </div>

            <!-- Table Card Wrapper -->
            <div class="lg:col-span-2 bg-white dark:bg-[#0e1626] border border-slate-200 dark:border-slate-800/60 rounded-3xl shadow-xl overflow-hidden transition-all duration-300">
                <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800/60 bg-slate-50 dark:bg-[#0a101d]">
                    <h3 class="text-sm font-bold text-slate-900 dark:text-slate-200 uppercase tracking-widest">Your Allocation Audit Trail Log</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-100 dark:bg-[#070b14] text-slate-500 dark:text-slate-400 uppercase text-xs font-bold tracking-wider border-b border-slate-200 dark:border-slate-800/60">
                                <th class="px-6 py-4">Category</th>
                                <th class="px-6 py-4">Timeline Bound</th>
                                <th class="px-6 py-4">Reason</th>
                                <th class="px-6 py-4 text-right">State Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-800/40 text-sm font-semibold text-slate-700 dark:text-slate-300">
                            <?php if(empty($myLeaves)): ?>
                                <tr><td colspan="4" class="px-6 py-12 text-center text-slate-400 dark:text-slate-500 font-medium">No historic allocations matching current profile index.</td></tr>
                            <?php else: ?>
                                <?php foreach($myLeaves as $row): ?>
                                    <tr class="hover:bg-slate-50/80 dark:hover:bg-[#121c30]/40 transition duration-150">
                                        <td class="px-6 py-4 text-slate-900 dark:text-white font-bold"><?= htmlspecialchars($row['leave_type']) ?></td>
                                        <td class="px-6 py-4 text-slate-500 dark:text-slate-400 font-mono text-xs whitespace-nowrap"><?= $row['start_date'] ?> to <?= $row['end_date'] ?></td>
                                        <td class="px-6 py-4 text-slate-500 dark:text-slate-400 max-w-xs truncate" title="<?= htmlspecialchars($row['reason']) ?>"><?= htmlspecialchars($row['reason']) ?></td>
                                        <td class="px-6 py-4 text-right">
                                            <?php $color = $row['status'] === 'approved' ? 'text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-500/10 border-emerald-200 dark:border-emerald-500/30' : ($row['status'] === 'rejected' ? 'text-rose-600 dark:text-rose-400 bg-rose-50 dark:bg-rose-500/10 border-rose-200 dark:border-rose-500/30' : 'text-amber-600 dark:text-amber-400 bg-amber-50 dark:bg-amber-500/10 border-amber-200 dark:border-amber-500/30'); ?>
                                            <span class="px-3 py-1 border rounded-full text-xs font-bold capitalize tracking-wide <?= $color ?>"><?= htmlspecialchars($row['status']) ?></span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>