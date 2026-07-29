<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

$userId = $_SESSION['user_id'];
$msg = '';

// DYNAMIC LIVE CHECK: Establish up-to-date tracking classification limits
$adminCheck = $pdo->prepare("SELECT emp_type FROM users WHERE id = ?");
$adminCheck->execute([$userId]);
$currentUserType = $adminCheck->fetchColumn() ?: '';

// Process exceptions operations audit status updates
if (isset($_GET['action']) && isset($_GET['id'])) {
    $action = $_GET['action'];
    $requestId = (int)$_GET['id'];
    
    if (in_array($action, ['approved', 'rejected'])) {
        // SECURITY PRE-FLIGHT INTERCEPT: Ensure a standard admin isn't trying to approve another admin's request via URL injection
        $verifyStmt = $pdo->prepare("SELECT u.role FROM leave_requests lr JOIN users u ON lr.user_id = u.id WHERE lr.id = ?");
        $verifyStmt->execute([$requestId]);
        $targetRole = $verifyStmt->fetchColumn();

        if ($targetRole === 'admin' && $currentUserType !== 'head') {
            $msg = "ACCESS RESTRICTION FAILURE: Standard administrators cannot authorize requests submitted by fellow system sub-admins.";
        } else {
            $stmt = $pdo->prepare("UPDATE leave_requests SET status = ? WHERE id = ?");
            $stmt->execute([$action, $requestId]);
            $msg = "Operational record reference successfully set to: " . strtoupper($action);
        }
    }
}

// HIERARCHY FILTERED LOG ENGINE QUERY:
// 1. If the logged-in user is 'head', fetch all leave records across the system.
// 2. If a regular sub-admin is logged in, hide all admin requests and pull only standard employees.
if ($currentUserType === 'head') {
    $allLeaves = $pdo->query("SELECT lr.*, u.name, u.emp_type, u.role FROM leave_requests lr JOIN users u ON lr.user_id = u.id ORDER BY lr.applied_at DESC")->fetchAll();
} else {
    $allLeaves = $pdo->query("SELECT lr.*, u.name, u.emp_type, u.role FROM leave_requests lr JOIN users u ON lr.user_id = u.id WHERE u.role = 'employee' ORDER BY lr.applied_at DESC")->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <title>Zeal - Audit Queue Management</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <script>tailwind.config = { darkMode: 'class' }</script>
    <style>body { font-family: 'Plus Jakarta Sans', sans-serif; }</style>
</head>
<body class="bg-slate-50 dark:bg-[#0b0f19] text-slate-800 dark:text-slate-100 pl-64 min-h-screen font-sans antialiased transition-colors duration-200">

    <?php include '../includes/sidebar.php'; ?>

    <div class="max-w-7xl mx-auto px-8 py-10 space-y-6">
        
        <div class="bg-white dark:bg-[#0e1626] border border-slate-200 dark:border-slate-800/60 p-8 rounded-3xl shadow-xl">
            <h2 class="text-3xl font-black text-slate-900 dark:text-white tracking-tight">Leave Allocation Management Engine</h2>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Audit, authorize, or reject exceptions generated within active organizational tiers.</p>
        </div>

        <?php if($msg): ?>
            <div class="bg-blue-500/10 border border-blue-500/30 text-blue-600 dark:text-blue-400 rounded-2xl p-4 text-sm font-semibold tracking-wide animate-pulse">
                <?= htmlspecialchars($msg) ?>
            </div>
        <?php endif; ?>

        <div class="bg-white dark:bg-[#0e1626] border border-slate-200 dark:border-slate-800/60 rounded-3xl shadow-xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-100 dark:bg-[#070b14] text-slate-500 dark:text-slate-400 uppercase text-xs font-bold tracking-wider border-b border-slate-200 dark:border-slate-800/60">
                            <th class="px-6 py-4">Employee</th>
                            <th class="px-6 py-4">Classification</th>
                            <th class="px-6 py-4">Leave Type</th>
                            <th class="px-6 py-4">Duration Metric</th>
                            <th class="px-6 py-4">Reason Justification</th>
                            <th class="px-6 py-4">Current Status</th>
                            <th class="px-6 py-4 text-center">Operational Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-800/40 text-sm font-semibold text-slate-700 dark:text-slate-300">
                        <?php if(empty($allLeaves)): ?>
                            <tr><td colspan="7" class="px-6 py-12 text-center text-slate-400 dark:text-slate-500">No organizational exceptions currently loaded within systemic operational loops.</td></tr>
                        <?php else: ?>
                            <?php foreach($allLeaves as $row): ?>
                                <tr class="hover:bg-slate-50/80 dark:hover:bg-[#121c30]/40 transition duration-150">
                                    <td class="px-6 py-5 text-slate-900 dark:text-white font-bold">
                                        <?= htmlspecialchars($row['name']) ?>
                                        <?php if($row['role'] === 'admin'): ?>
                                            <span class="ml-1 px-1.5 py-0.5 bg-purple-500/20 text-purple-400 border border-purple-500/30 rounded text-[9px] font-black uppercase">Admin Profile</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-5 text-xs"><span class="px-2 py-0.5 bg-slate-200 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded text-slate-600 dark:text-slate-400 uppercase tracking-widest font-black text-[10px]"><?= htmlspecialchars($row['emp_type']) ?></span></td>
                                    <td class="px-6 py-5 text-slate-600 dark:text-slate-400"><?= htmlspecialchars($row['leave_type']) ?></td>
                                    <td class="px-6 py-5 font-mono text-xs text-slate-500 dark:text-slate-400"><?= $row['start_date'] ?> to <?= $row['end_date'] ?></td>
                                    <td class="px-6 py-5 text-slate-600 dark:text-slate-400 max-w-xs truncate" title="<?= htmlspecialchars($row['reason']) ?>"><?= htmlspecialchars($row['reason']) ?></td>
                                    <td class="px-6 py-5">
                                        <?php $c = $row['status'] === 'approved' ? 'text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-500/10 border-emerald-200 dark:border-emerald-500/30' : ($row['status'] === 'rejected' ? 'text-rose-600 dark:text-rose-400 bg-rose-50 dark:bg-rose-500/10 border-rose-200 dark:border-rose-500/30' : 'text-amber-600 dark:text-amber-400 bg-amber-50 dark:bg-amber-500/10 border-amber-200 dark:border-amber-500/30'); ?>
                                        <span class="px-3 py-1 border rounded-full text-xs font-black capitalize tracking-wide <?= $c ?>"><?= htmlspecialchars($row['status']) ?></span>
                                    </td>
                                    <td class="px-6 py-5 text-center whitespace-nowrap">
                                        <?php if($row['status'] === 'pending'): ?>
                                            <a href="manage-leaves.php?action=approved&id=<?= $row['id'] ?>" class="bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white text-xs font-bold px-4 py-2 rounded-xl transition duration-300 shadow-md transform hover:scale-105 inline-block active:scale-95 mr-2">Approve</a>
                                            <a href="manage-leaves.php?action=rejected&id=<?= $row['id'] ?>" class="bg-gradient-to-r from-rose-600 to-red-600 hover:from-rose-500 hover:to-red-500 text-white text-xs font-bold px-4 py-2 rounded-xl transition duration-300 shadow-md transform hover:scale-105 inline-block active:scale-95">Reject</a>
                                        <?php else: ?>
                                            <span class="text-xs text-slate-400 dark:text-slate-500 font-bold uppercase tracking-widest bg-slate-100 dark:bg-slate-800/20 px-3 py-1.5 border border-slate-200 dark:border-slate-800 rounded-xl">Processed</span>
                                        <?php endif; ?>
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