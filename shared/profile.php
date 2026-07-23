<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

$userId = $_SESSION['user_id'];
$successMsg = '';
$errorMsg = '';

// Form Processing Action Engine
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $contact = trim($_POST['contact_number']);
    $address = trim($_POST['address']);
    
    try {
        $stmt = $pdo->prepare("UPDATE users SET contact_number = ?, address = ? WHERE id = ?");
        $stmt->execute([$contact, $address, $userId]);
        $successMsg = 'Personal configuration matrix securely synchronized.';
    } catch (\PDOException $e) {
        $errorMsg = 'System execution error processing database payload.';
    }
}

// Fetch Latest Context
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <title>Enterprise Profile Registry</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config = { darkMode: 'class' }</script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>body { font-family: 'Plus Jakarta Sans', sans-serif; }</style>
</head>
<body class="bg-slate-50 dark:bg-[#0b0f19] text-slate-800 dark:text-slate-100 pl-64 min-h-screen font-sans antialiased transition-colors duration-200">

    <?php include '../includes/sidebar.php'; ?>

    <div class="max-w-4xl mx-auto px-8 py-12 space-y-6">
        <div class="bg-white dark:bg-[#0e1626] border border-slate-200 dark:border-slate-800/60 p-8 rounded-3xl shadow-xl transition-all duration-300">
            <h2 class="text-3xl font-black text-slate-900 dark:text-white tracking-tight">Identity Profile Matrix</h2>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Manage personal contact parameters and localized residential address values.</p>
        </div>

        <?php if($successMsg): ?>
            <div class="bg-emerald-500/10 border border-emerald-500/30 text-emerald-600 dark:text-emerald-400 rounded-2xl p-4 text-sm font-semibold animate-pulse"><?= htmlspecialchars($successMsg) ?></div>
        <?php endif; ?>
        <?php if($errorMsg): ?>
            <div class="bg-rose-500/10 border border-rose-500/30 text-rose-600 dark:text-rose-400 rounded-2xl p-4 text-sm font-semibold"><?= htmlspecialchars($errorMsg) ?></div>
        <?php endif; ?>

        <form action="" method="POST" class="bg-white dark:bg-[#0e1626] rounded-3xl shadow-xl border border-slate-200 dark:border-slate-800/60 overflow-hidden transition-all duration-300 hover:border-slate-700/60">
            <!-- Top Profile Presentation Banner -->
            <div class="bg-slate-100 dark:bg-gradient-to-r dark:from-[#070b14] dark:via-[#101726] dark:to-[#070b14] p-8 flex items-center space-x-4 border-b border-slate-200 dark:border-slate-800/60">
                <div class="w-12 h-12 rounded-xl bg-indigo-600 flex items-center justify-center text-white font-extrabold text-xl shadow-md shadow-indigo-500/20">
                    <?= strtoupper(substr($user['name'], 0, 1)) ?>
                </div>
                <div>
                    <h3 class="text-xl font-bold text-slate-900 dark:text-white tracking-tight"><?= htmlspecialchars($user['name']) ?></h3>
                    <p class="text-indigo-600 dark:text-indigo-400 text-xs font-mono uppercase tracking-widest mt-0.5 font-bold"><?= htmlspecialchars($user['role']) ?> • <?= htmlspecialchars($user['emp_type']) ?></p>
                    <p class="text-slate-500 dark:text-slate-400 text-xs mt-1 font-medium font-mono"><?= htmlspecialchars($user['email']) ?></p>
                </div>
            </div>

            <!-- Form Inputs -->
            <div class="p-8 space-y-6">
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-400 tracking-wider mb-2">Secure Line Contact Number</label>
                    <input type="text" name="contact_number" value="<?= htmlspecialchars($user['contact_number'] ?? '') ?>" placeholder="+91 XXXXX XXXXX" class="w-full px-4 py-2.5 bg-slate-50 dark:bg-[#070b14] border border-slate-200 dark:border-slate-800 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 font-mono text-sm text-slate-900 dark:text-white transition">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-slate-400 tracking-wider mb-2">Residential Operational Address Matrix</label>
                    <textarea name="address" rows="3" placeholder="Enter localized structural address configurations..." class="w-full px-4 py-2.5 bg-slate-50 dark:bg-[#070b14] border border-slate-200 dark:border-slate-800 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm text-slate-900 dark:text-white transition"><?= htmlspecialchars($user['address'] ?? '') ?></textarea>
                </div>

                <div class="pt-6 border-t border-slate-200 dark:border-slate-800/60 flex justify-end">
                    <button type="submit" class="bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-50 hover:to-violet-500 text-white text-sm font-bold px-6 py-3 rounded-xl shadow-lg transition-all duration-300 transform active:scale-95 hover:scale-105">
                        Synchronize Identity Core
                    </button>
                </div>
            </div>
        </form>
    </div>
</body>
</html>