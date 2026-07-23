<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

$selected_type = trim($_GET['type'] ?? 'full-time');
$allTypes = ['head', 'full-time', 'part-time', 'temporary', 'contract', 'freelancer', 'intern'];

if (!in_array($selected_type, $allTypes)) {
    $selected_type = 'full-time';
}

$stmt = $pdo->prepare("SELECT * FROM users WHERE emp_type = ? ORDER BY name ASC");
$stmt->execute([$selected_type]);
$employees = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <title>Zeal - Classification Matrix</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config = { darkMode: 'class' }</script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>body { font-family: 'Plus Jakarta Sans', sans-serif; }</style>
</head>
<body class="bg-slate-50 dark:bg-[#0b0f19] text-slate-800 dark:text-slate-100 pl-64 min-h-screen font-sans antialiased transition-colors duration-200">

    <?php include '../includes/sidebar.php'; ?>

    <div class="max-w-7xl mx-auto px-8 py-10 space-y-8">
        
        <div class="bg-white dark:bg-[#0e1626] border border-slate-200 dark:border-slate-800/60 p-8 rounded-3xl shadow-xl">
            <h2 class="text-3xl font-black text-slate-900 dark:text-white tracking-tight">Workforce Classification Matrix</h2>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Isolate system user indices by operational grouping categories.</p>
        </div>

        <div class="bg-white dark:bg-[#0e1626] border border-slate-200 dark:border-slate-800/60 p-3 rounded-2xl shadow-xl flex flex-wrap gap-2">
            <?php foreach ($allTypes as $type): 
                $isActive = ($type === $selected_type);
                $tabStyles = $isActive 
                    ? 'bg-gradient-to-r from-blue-600 to-indigo-600 text-white shadow-lg scale-[1.02]' 
                    : 'bg-slate-100 dark:bg-[#070b14] border border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white';
            ?>
                <a href="classifications.php?type=<?= urlencode($type) ?>" class="px-5 py-2.5 text-xs font-bold uppercase tracking-wider rounded-xl transition-all duration-300 <?= $tabStyles ?>">
                    <?= htmlspecialchars($type) ?>
                </a>
            <?php endforeach; ?>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php if (empty($employees)): ?>
                <div class="col-span-full bg-white dark:bg-[#0e1626] border border-slate-200 dark:border-slate-800/60 p-12 text-center rounded-3xl">
                    <p class="text-slate-400 font-medium">No active personnel files found matching the selected track criteria.</p>
                </div>
            <?php else: ?>
                <?php foreach ($employees as $emp): ?>
                    <div class="bg-white dark:bg-[#0e1626] border border-slate-200 dark:border-slate-800/60 rounded-3xl shadow-xl p-6 transition-all duration-300 transform hover:scale-[1.02] flex flex-col justify-between space-y-6">
                        <div class="flex items-center space-x-4">
                            <div class="w-12 h-12 rounded-xl bg-blue-600 flex items-center justify-center text-white font-extrabold text-lg shadow-sm">
                                <?= strtoupper(substr($emp['name'], 0, 1)) ?>
                            </div>
                            <div class="space-y-0.5">
                                <h4 class="text-base font-bold text-slate-900 dark:text-white tracking-tight"><?= htmlspecialchars($emp['name']) ?></h4>
                                <span class="px-2 py-0.5 bg-indigo-500/10 border border-indigo-500/30 rounded text-[10px] font-black uppercase text-indigo-600 dark:text-indigo-400 tracking-widest inline-block">
                                    <?= htmlspecialchars($emp['role']) ?>
                                </span>
                                <p class="text-slate-500 dark:text-slate-400 font-mono text-[11px] truncate max-w-[160px]"><?= htmlspecialchars($emp['email']) ?></p>
                            </div>
                        </div>

                        <div class="border-t border-slate-100 dark:border-slate-800/60 pt-4 space-y-3 text-xs">
                            <div>
                                <span class="text-slate-400 dark:text-slate-500 font-bold uppercase tracking-wider text-[10px] block mb-0.5">Contact Line Endpoint</span>
                                <p class="text-slate-700 dark:text-slate-300 font-mono font-semibold"><?= htmlspecialchars($emp['contact_number'] ?: 'Not configured') ?></p>
                            </div>
                            <div>
                                <span class="text-slate-400 dark:text-slate-500 font-bold uppercase tracking-wider text-[10px] block mb-0.5">Residential Location</span>
                                <p class="text-slate-700 dark:text-slate-300 leading-relaxed truncate max-w-full"><?= htmlspecialchars($emp['address'] ?: 'No physical coordinates configured.') ?></p>
                            </div>
                        </div>

                        <div class="border-t border-slate-100 dark:border-slate-800/60 pt-4">
                            <a href="edit-employee.php?id=<?= $emp['id'] ?>" class="w-full text-center bg-slate-100 dark:bg-slate-900 hover:bg-slate-200 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 font-bold text-xs py-2.5 rounded-xl transition block border border-slate-200 dark:border-slate-800 shadow-sm">
                                Access Full Directory File 📝
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>