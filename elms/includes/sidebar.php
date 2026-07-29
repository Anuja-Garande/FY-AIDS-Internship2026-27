<?php
$current_page = basename($_SERVER['PHP_SELF']);
$role = $_SESSION['role'] ?? 'employee';
$base_url = "/elms";

// Dynamic check to ensure active up-to-date tracking variables are captured
require_once __DIR__ . '/../config/db.php';
$sidebarType = '';
if (isset($_SESSION['user_id'])) {
    $sidebarStmt = $pdo->prepare("SELECT emp_type FROM users WHERE id = ?");
    $sidebarStmt->execute([$_SESSION['user_id']]);
    $sidebarType = $sidebarStmt->fetchColumn() ?: '';
}
?>
<div class="fixed inset-y-0 left-0 w-64 bg-[#0d1324] border-r border-slate-800 dark:border-slate-800 text-slate-400 flex flex-col z-50 shadow-2xl transition-all duration-300">
    <!-- Platform Logo -->
    <div class="h-20 flex items-center px-6 border-b border-slate-800 bg-[#070b14]/50">
        <div class="flex items-center space-x-3">
            <div class="w-8 h-8 rounded-xl bg-gradient-to-tr from-blue-500 to-indigo-600 flex items-center justify-center font-bold text-white shadow-lg shadow-indigo-500/30">Z</div>
            <span class="text-base font-extrabold text-white tracking-wide">Zeal Core Engine</span>
        </div>
    </div>

    <!-- Navigation Directory Links Matrix -->
    <nav class="flex-1 px-4 py-8 space-y-2 overflow-y-auto">
        <?php if ($role === 'admin'): ?>
            <a href="<?= $base_url ?>/admin/dashboard.php" class="flex items-center space-x-3 px-4 py-3 rounded-2xl font-semibold text-sm transition-all duration-200 <?= $current_page === 'dashboard.php' ? 'bg-gradient-to-r from-blue-600 to-indigo-600 text-white' : 'hover:bg-slate-800/40 hover:text-slate-200' ?>">
                <span>📊 Dashboard Matrix</span>
            </a>
            <a href="<?= $base_url ?>/admin/employees.php" class="flex items-center space-x-3 px-4 py-3 rounded-2xl font-semibold text-sm transition-all duration-200 <?= $current_page === 'employees.php' ? 'bg-gradient-to-r from-blue-600 to-indigo-600 text-white' : 'hover:bg-slate-800/40 hover:text-slate-200' ?>">
                <span>👥 Personnel Vault</span>
            </a>
            <a href="<?= $base_url ?>/admin/classifications.php" class="flex items-center space-x-3 px-4 py-3 rounded-2xl font-semibold text-sm transition-all duration-200 <?= $current_page === 'classifications.php' ? 'bg-gradient-to-r from-blue-600 to-indigo-600 text-white' : 'hover:bg-slate-800/40 hover:text-slate-200' ?>">
                <span>🎛️ Classification Matrix</span>
            </a>
            <a href="<?= $base_url ?>/admin/manage-leaves.php" class="flex items-center space-x-3 px-4 py-3 rounded-2xl font-semibold text-sm transition-all duration-200 <?= $current_page === 'manage-leaves.php' ? 'bg-gradient-to-r from-blue-600 to-indigo-600 text-white' : 'hover:bg-slate-800/40 hover:text-slate-200' ?>">
                <span>✉️ Operations Audit</span>
            </a>
            
            <!-- EXCLUSIVE SYNC: Allows sub-admins to mark attendance & request leaves just like regular workers -->
            <?php if ($sidebarType !== 'head'): ?>
                <div class="pt-4 mt-4 border-t border-slate-800/60">
                    <span class="px-4 text-[10px] font-bold uppercase tracking-widest text-slate-500 block mb-2">My Operational Tracking</span>
                    <a href="<?= $base_url ?>/employee/attendance.php" class="flex items-center space-x-3 px-4 py-3 rounded-2xl font-semibold text-sm transition-all duration-200 <?= $current_page === 'attendance.php' ? 'bg-gradient-to-r from-indigo-600 to-violet-600 text-white' : 'hover:bg-slate-800/40 hover:text-slate-200' ?>">
                        <span>⏱️ Mark Attendance</span>
                    </a>
                    <a href="<?= $base_url ?>/admin/dashboard.php?request_mode=personal" class="flex items-center space-x-3 px-4 py-3 rounded-2xl font-semibold text-sm transition-all duration-200 <?= isset($_GET['request_mode']) ? 'bg-gradient-to-r from-indigo-600 to-violet-600 text-white' : 'hover:bg-slate-800/40 hover:text-slate-200' ?>">
                        <span>✉️ Request Leave</span>
                    </a>
                </div>
            <?php endif; ?>

        <?php else: ?>
            <a href="<?= $base_url ?>/employee/dashboard.php" class="flex items-center space-x-3 px-4 py-3 rounded-2xl font-semibold text-sm transition-all duration-200 <?= $current_page === 'dashboard.php' ? 'bg-gradient-to-r from-indigo-600 to-violet-600 text-white' : 'hover:bg-slate-800/40 hover:text-slate-200' ?>">
                <span>💻 Request Portal</span>
            </a>
            <a href="<?= $base_url ?>/employee/attendance.php" class="flex items-center space-x-3 px-4 py-3 rounded-2xl font-semibold text-sm transition-all duration-200 <?= $current_page === 'attendance.php' ? 'bg-gradient-to-r from-indigo-600 to-violet-600 text-white' : 'hover:bg-slate-800/40 hover:text-slate-200' ?>">
                <span>⏱️ Mark Attendance</span>
            </a>
            <a href="<?= $base_url ?>/employee/my-leaves.php" class="flex items-center space-x-3 px-4 py-3 rounded-2xl font-semibold text-sm transition-all duration-200 <?= $current_page === 'my-leaves.php' ? 'bg-gradient-to-r from-indigo-600 to-violet-600 text-white' : 'hover:bg-slate-800/40 hover:text-slate-200' ?>">
                <span>📅 My Absences Engine</span>
            </a>
        <?php endif; ?>

        <a href="<?= $base_url ?>/shared/profile.php" class="flex items-center space-x-3 px-4 py-3 rounded-2xl font-semibold text-sm transition-all duration-200 <?= $current_page === 'profile.php' ? 'bg-emerald-600 text-white' : 'hover:bg-slate-800/40 hover:text-slate-200' ?>">
            <span>👤 Identity Profile</span>
        </a>
    </nav>

    <!-- Theme Control & Exit Segment -->
    <div class="p-4 bg-[#070b14]/50 border-t border-slate-800 space-y-3">
        <button onclick="toggleSystemTheme()" class="flex items-center justify-between w-full px-4 py-2.5 rounded-xl bg-slate-800/50 hover:bg-slate-800 border border-slate-700/50 text-slate-300 font-semibold text-xs transition duration-200">
            <span id="theme-mode-text">Switch To Light Mode</span>
            <span id="theme-mode-icon">☀️</span>
        </button>
        <a href="<?= $base_url ?>/auth/logout.php" class="flex items-center justify-center w-full py-2.5 rounded-xl bg-red-950/25 border border-red-900/40 text-red-400 font-bold text-xs uppercase tracking-widest hover:bg-red-900/40 transition active:scale-95">
            Terminate Session 🛑
        </a>
    </div>
</div>

<script>
    function applySavedTheme() {
        if (localStorage.getItem('zeal-theme') === 'light') {
            document.documentElement.classList.remove('dark');
            document.getElementById('theme-mode-text').innerText = "Switch To Dark Mode";
            document.getElementById('theme-mode-icon').innerText = "🌙";
        } else {
            document.documentElement.classList.add('dark');
            document.getElementById('theme-mode-text').innerText = "Switch To Light Mode";
            document.getElementById('theme-mode-icon').innerText = "☀️";
        }
    }
    function toggleSystemTheme() {
        if (document.documentElement.classList.contains('dark')) {
            localStorage.setItem('zeal-theme', 'light');
        } else {
            localStorage.setItem('zeal-theme', 'dark');
        }
        applySavedTheme();
    }
    applySavedTheme();
</script>