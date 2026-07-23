<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

$successMsg = '';
$errorMsg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register_employee'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $emp_type = trim($_POST['emp_type']);
    $role = trim($_POST['role']);

    if (!empty($name) && !empty($email) && !empty($password) && !empty($emp_type) && !empty($role)) {
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        try {
            $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role, emp_type) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$name, $email, $hashedPassword, $role, $emp_type]);
            $successMsg = "Employee profile '$name' successfully provisioned into Zeal system directory.";
        } catch (\PDOException $e) {
            $errorMsg = ($e->getCode() == 23000) ? "Collision: An account matching that email already exists." : "Execution tracking error.";
        }
    } else {
        $errorMsg = "Please populate all fields.";
    }
}

$allEmployees = $pdo->query("SELECT id, name, email, role, emp_type FROM users ORDER BY created_at DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <title>Zeal - Directory Provisioning</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <script>tailwind.config = { darkMode: 'class' }</script>
    <style>body { font-family: 'Plus Jakarta Sans', sans-serif; }</style>
</head>
<body class="bg-slate-50 dark:bg-[#0b0f19] text-slate-800 dark:text-slate-100 pl-64 min-h-screen font-sans antialiased transition-colors duration-200">

    <?php include '../includes/sidebar.php'; ?>

    <div class="max-w-7xl mx-auto px-8 py-10 space-y-8">
        
        <div class="bg-white dark:bg-[#0e1626] border border-slate-200 dark:border-slate-800/60 p-8 rounded-3xl shadow-xl">
            <h2 class="text-3xl font-black text-slate-900 dark:text-white tracking-tight">Workforce Directory Provisioning</h2>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Deploy fresh production roles or monitor systemic permission bounds inside your internal matrix.</p>
        </div>

        <?php if($successMsg): ?><div class="bg-emerald-500/10 border border-emerald-500/30 text-emerald-600 dark:text-emerald-400 rounded-2xl p-4 text-sm font-semibold animate-pulse"><?= htmlspecialchars($successMsg) ?></div><?php endif; ?>
        <?php if($errorMsg): ?><div class="bg-rose-500/10 border border-rose-500/30 text-rose-600 dark:text-rose-400 rounded-2xl p-4 text-sm font-semibold"><?= htmlspecialchars($errorMsg) ?></div><?php endif; ?>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="bg-white dark:bg-[#0e1626] p-6 rounded-3xl border border-slate-200 dark:border-slate-800/60 shadow-xl h-fit space-y-6">
                <div>
                    <h3 class="text-base font-bold text-slate-900 dark:text-white tracking-wide">Provision Corporate Identity</h3>
                    <p class="text-slate-400 text-xs mt-0.5">Generate verified infrastructure access arrays.</p>
                </div>
                
                <form action="" method="POST" class="space-y-4">
                    <input type="hidden" name="register_employee" value="1">
                    <div>
                        <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-1">Full Legal Name</label>
                        <input type="text" name="name" required placeholder="Jane Smith" class="w-full px-4 py-2.5 bg-slate-50 dark:bg-[#070b14] border border-slate-200 dark:border-slate-800 rounded-xl text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:outline-none transition">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-1">Email Endpoint</label>
                        <input type="email" name="email" required placeholder="jane@company.com" class="w-full px-4 py-2.5 bg-slate-50 dark:bg-[#070b14] border border-slate-200 dark:border-slate-800 rounded-xl text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:outline-none transition">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-1">Temporary Password</label>
                        <input type="text" name="password" required value="emp123" class="w-full px-4 py-2.5 bg-slate-50 dark:bg-[#070b14] border border-slate-200 dark:border-slate-800 rounded-xl text-sm text-slate-900 dark:text-white font-mono focus:ring-2 focus:ring-blue-500 focus:outline-none transition">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-1">System Role</label>
                            <select name="role" required class="w-full px-3 py-2.5 bg-slate-50 dark:bg-[#070b14] border border-slate-200 dark:border-slate-800 rounded-xl text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500 transition">
                                <option value="employee">Employee</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-1">Classification</label>
                            <select name="emp_type" required class="w-full px-3 py-2.5 bg-slate-50 dark:bg-[#070b14] border border-slate-200 dark:border-slate-800 rounded-xl text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500 transition capitalize">
                                <option value="full-time">full-time</option><option value="part-time">part-time</option><option value="temporary">temporary</option><option value="contract">contract</option><option value="freelancer">freelancer</option><option value="intern">intern</option><option value="head">head</option>
                            </select>
                        </div>
                    </div>
                    <button type="submit" class="w-full bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white font-semibold py-3 rounded-xl transition-all shadow-lg transform active:scale-95">
                        Commit To Directory Matrix
                    </button>
                </form>
            </div>

            <div class="lg:col-span-2 bg-white dark:bg-[#0e1626] border border-slate-200 dark:border-slate-800/60 rounded-3xl shadow-xl overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800/60 bg-slate-50 dark:bg-[#0a101d]">
                    <h3 class="text-sm font-bold text-slate-900 dark:text-slate-200 uppercase tracking-widest">Active Directory Log</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-100 dark:bg-[#070b14] text-slate-500 dark:text-slate-400 uppercase text-xs font-bold tracking-wider border-b border-slate-200 dark:border-slate-800/60"><th class="px-6 py-4">Identity</th><th class="px-6 py-4">Email</th><th class="px-6 py-4">Role</th><th class="px-6 py-4">Classification</th><th class="px-6 py-4 text-center">Actions</th></tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-800/40 text-sm font-semibold text-slate-700 dark:text-slate-300">
                            <?php foreach($allEmployees as $row): ?>
                                <tr class="hover:bg-slate-50/80 dark:hover:bg-[#121c30]/40 transition duration-150">
                                    <td class="px-6 py-4 text-slate-900 dark:text-white font-bold"><?= htmlspecialchars($row['name']) ?></td>
                                    <td class="px-6 py-4 text-slate-500 dark:text-slate-400 font-mono text-xs"><?= htmlspecialchars($row['email']) ?></td>
                                    <td class="px-6 py-4"><span class="px-2.5 py-0.5 rounded text-[10px] font-black uppercase tracking-wider <?= $row['role'] === 'admin' ? 'bg-purple-100 dark:bg-purple-500/10 border border-purple-200 dark:border-purple-500/30 text-purple-700 dark:text-purple-400' : 'bg-blue-100 dark:bg-blue-500/10 border border-blue-200 dark:border-blue-500/30 text-blue-700 dark:text-blue-400' ?>"><?= $row['role'] ?></span></td>
                                    <td class="px-6 py-4"><span class="px-2.5 py-0.5 rounded text-[10px] font-black bg-slate-200 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-600 dark:text-slate-400 uppercase tracking-wider"><?= htmlspecialchars($row['emp_type']) ?></span></td>
                                    <td class="px-6 py-4 text-center">
                                        <a href="edit-employee.php?id=<?= $row['id'] ?>" class="text-xs font-bold bg-gradient-to-r from-amber-600 to-orange-600 text-white px-4 py-2 rounded-xl transition duration-300 shadow-sm transform hover:scale-105 inline-block active:scale-95">Edit Record 📝</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>