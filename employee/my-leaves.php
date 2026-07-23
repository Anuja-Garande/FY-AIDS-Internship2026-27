<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'employee') {
    header("Location: ../auth/login.php");
    exit;
}

$userId = $_SESSION['user_id'];

// Fetch detailed record list for the active user instance
$stmt = $pdo->prepare("SELECT * FROM leave_requests WHERE user_id = ? ORDER BY applied_at DESC");
$stmt->execute([$userId]);
$myLeaves = $stmt->fetchAll();

// Dynamic SQL aggregation to calculate specific data arrays for chart mapping
$chartStmt = $pdo->prepare("SELECT status, COUNT(*) as count FROM leave_requests WHERE user_id = ? GROUP BY status");
$chartStmt->execute([$userId]);
$chartDataRaw = $chartStmt->fetchAll();

$chartMetrics = ['pending' => 0, 'approved' => 0, 'rejected' => 0];
foreach ($chartDataRaw as $row) {
    if (array_key_exists($row['status'], $chartMetrics)) {
        $chartMetrics[$row['status']] = (int)$row['count'];
    }
}
?>
<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <title>Zeal - My Absences Engine</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Include premium ChartJS distribution framework -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <script>tailwind.config = { darkMode: 'class' }</script>
    <style>body { font-family: 'Plus Jakarta Sans', sans-serif; }</style>
</head>
<body class="bg-slate-50 dark:bg-[#0b0f19] text-slate-800 dark:text-slate-100 pl-64 min-h-screen font-sans antialiased transition-colors duration-200">

    <?php include '../includes/sidebar.php'; ?>

    <div class="max-w-7xl mx-auto px-8 py-10 space-y-8">
        
        <!-- Header Section -->
        <div class="bg-white dark:bg-[#0e1626] border border-slate-200 dark:border-slate-800/60 p-8 rounded-3xl shadow-xl">
            <h2 class="text-3xl font-black text-slate-900 dark:text-white tracking-tight">Personal Absence Tracking Engine</h2>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Review comprehensive dynamic exception allocation histories and pipeline metrics logs.</p>
        </div>

        <!-- Master Leave History Ledger Table -->
        <div class="bg-white dark:bg-[#0e1626] border border-slate-200 dark:border-slate-800/60 rounded-3xl shadow-xl overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800/60 bg-slate-50 dark:bg-[#0a101d]">
                <h3 class="text-sm font-bold text-slate-900 dark:text-slate-200 uppercase tracking-widest">Complete Absences Log Ledger</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-100 dark:bg-[#070b14] text-slate-500 dark:text-slate-400 uppercase text-xs font-bold tracking-wider border-b border-slate-200 dark:border-slate-800/60">
                            <th class="px-6 py-4">Leave Category Type</th>
                            <th class="px-6 py-4">Execution Timeline</th>
                            <th class="px-6 py-4">Justification Narrative</th>
                            <th class="px-6 py-4 text-right">Pipeline Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-800/40 text-sm font-semibold text-slate-700 dark:text-slate-300">
                        <?php if(empty($myLeaves)): ?>
                            <tr><td colspan="4" class="px-6 py-12 text-center text-slate-400 dark:text-slate-500 font-medium">No historical metrics matching current session token parameters.</td></tr>
                        <?php else: ?>
                            <?php foreach($myLeaves as $row): ?>
                                <tr class="hover:bg-slate-50/80 dark:hover:bg-[#121c30]/40 transition duration-150">
                                    <td class="px-6 py-4 text-slate-900 dark:text-white font-bold"><?= htmlspecialchars($row['leave_type']) ?></td>
                                    <td class="px-6 py-4 text-slate-500 dark:text-slate-400 font-mono text-xs whitespace-nowrap"><?= $row['start_date'] ?> to <?= $row['end_date'] ?></td>
                                    <td class="px-6 py-4 text-slate-500 dark:text-slate-400 max-w-md truncate" title="<?= htmlspecialchars($row['reason']) ?>"><?= htmlspecialchars($row['reason']) ?></td>
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

        <!-- Visual Analytics Block Grid (Data Representation Section) -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 pt-4">
            <!-- Numeric Metric Summaries -->
            <div class="md:col-span-1 space-y-4 flex flex-col justify-between">
                <div class="bg-white dark:bg-[#0e1626] border border-slate-200 dark:border-slate-800/60 rounded-3xl p-6 shadow-md flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Total Validated</p>
                        <h4 class="text-3xl font-black mt-1 text-emerald-600 dark:text-emerald-400"><?= $chartMetrics['approved'] ?></h4>
                    </div>
                    <span class="text-2xl">✅</span>
                </div>
                <div class="bg-white dark:bg-[#0e1626] border border-slate-200 dark:border-slate-800/60 rounded-3xl p-6 shadow-md flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Under Review</p>
                        <h4 class="text-3xl font-black mt-1 text-amber-600 dark:text-amber-400"><?= $chartMetrics['pending'] ?></h4>
                    </div>
                    <span class="text-2xl">⏳</span>
                </div>
                <div class="bg-white dark:bg-[#0e1626] border border-slate-200 dark:border-slate-800/60 rounded-3xl p-6 shadow-md flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Denied Requests</p>
                        <h4 class="text-3xl font-black mt-1 text-rose-600 dark:text-rose-400"><?= $chartMetrics['rejected'] ?></h4>
                    </div>
                    <span class="text-2xl">❌</span>
                </div>
            </div>

            <!-- Chart.js Graph Canvas Container Card -->
            <div class="md:col-span-2 bg-white dark:bg-[#0e1626] border border-slate-200 dark:border-slate-800/60 rounded-3xl p-6 shadow-xl flex flex-col justify-between">
                <div class="mb-4">
                    <h3 class="text-base font-bold text-slate-900 dark:text-white tracking-wide">Leave Distribution Vector Chart</h3>
                    <p class="text-xs text-slate-400 mt-0.5">Graphical analytics representation of historical requests status metrics parameters.</p>
                </div>
                <div class="w-full max-h-[260px] flex items-center justify-center">
                    <canvas id="employeeLeaveChart"></canvas>
                </div>
            </div>
        </div>

    </div>

    <!-- Active Chart Rendering Automation Engine Script Logic -->
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const ctx = document.getElementById('employeeLeaveChart').getContext('2d');
            
            // Read active values evaluated via PHP array loops
            const dataMetrics = {
                labels: ['Approved Requests', 'Pending Verification', 'Rejected Logs'],
                datasets: [{
                    label: 'Absences Count',
                    data: [
                        <?= $chartMetrics['approved'] ?>, 
                        <?= $chartMetrics['pending'] ?>, 
                        <?= $chartMetrics['rejected'] ?>
                    ],
                    backgroundColor: [
                        'rgba(16, 185, 129, 0.85)', // Emerald
                        'rgba(245, 158, 11, 0.85)', // Amber
                        'rgba(244, 63, 94, 0.85)'   // Rose
                    ],
                    borderColor: [
                        '#10b981', '#f59e0b', '#f43f5e'
                    ],
                    borderWidth: 2,
                    borderRadius: 8,
                    barThickness: 28
                }]
            };

            new Chart(ctx, {
                type: 'bar',
                data: dataMetrics,
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0,
                                color: '#94a3b8'
                            },
                            grid: { color: 'rgba(148, 163, 184, 0.1)' }
                        },
                        x: {
                            ticks: { color: '#94a3b8' },
                            grid: { display: false }
                        }
                    }
                }
            });
        });
    </script>
</body>
</html>