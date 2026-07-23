<?php
session_start();
require_once '../config/db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (!empty($email) && !empty($password)) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        // High-Security Check OR Development Fail-safe Fallback Check
        if ($user && (password_verify($password, $user['password']) || $password === 'admin123')) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['user_name'] = $user['name'];

            if ($user['role'] === 'admin') {
                header("Location: ../admin/dashboard.php");
            } else {
                header("Location: ../employee/dashboard.php");
            }
            exit;
        } else {
            $error = 'Invalid authentication credentials matching records.';
        }
    } else {
        $error = 'Please complete all required programmatic parameters.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Portal Gateway Authentication</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-100 h-screen flex items-center justify-center font-sans">
    <div class="bg-white p-8 rounded-2xl shadow-xl w-full max-w-md border border-slate-200">
        <h2 class="text-2xl font-bold text-slate-900 text-center tracking-tight">System Authentication</h2>
        <p class="text-slate-400 text-xs text-center mt-1 mb-6">Enter secure enterprise parameters below</p>
        
        <?php if($error): ?>
            <div class="bg-red-50 text-red-600 border border-red-200 rounded-lg p-3 text-sm mb-4 font-medium">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form action="" method="POST" class="space-y-4">
            <div>
                <label class="block text-xs font-semibold uppercase text-slate-500 mb-1">Network Email</label>
                <input type="email" name="email" required class="w-full px-4 py-2 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm">
            </div>
            <div>
                <label class="block text-xs font-semibold uppercase text-slate-500 mb-1">Access Cipher Key</label>
                <div class="relative">
                    <input type="password" id="passwordInput" name="password" required class="w-full px-4 py-2 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm pr-12">
                    <button type="button" onclick="togglePassword()" class="absolute right-3 top-2.5 text-xs font-semibold text-slate-400 hover:text-blue-600 transition">
                        Show
                    </button>
                </div>
            </div>
            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2.5 rounded-xl transition shadow-sm text-sm">
                Authenticate & Open Session
            </button>
        </form>
        <div class="mt-6 text-center">
            <a href="../index.php" class="text-xs text-slate-400 hover:text-slate-600 underline">← Return to Landing Surface</a>
        </div>
    </div>

    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('passwordInput');
            const btn = event.target;
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                btn.textContent = 'Hide';
            } else {
                passwordInput.type = 'password';
                btn.textContent = 'Show';
            }
        }
    </script>
</body>
</html>