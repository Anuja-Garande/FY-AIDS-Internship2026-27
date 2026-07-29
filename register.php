<?php
require_once 'config.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name'] ?? '');
    $email     = trim($_POST['email'] ?? '');
    $username  = trim($_POST['username'] ?? '');
    $phone     = trim($_POST['phone'] ?? '');
    $gender    = trim($_POST['gender'] ?? '');
    $password  = $_POST['password'] ?? '';

    // Basic Validation
    if (empty($full_name) || empty($email) || empty($username) || empty($phone) || empty($password)) {
        $error = 'Please fill in all required fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (!preg_match('/^[0-9]{10}$/', $phone)) {
        // Enforces exactly 10 numeric digits in PHP backend
        $error = 'Phone number must be exactly 10 digits.';
    } else {
        try {
            // Check if username or email already exists
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$username, $email]);

            if ($stmt->fetch()) {
                $error = 'Username or Email is already registered.';
            } else {
                // Hash Password & Insert User
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $role = 'patient';

                $stmt = $pdo->prepare("INSERT INTO users (name, email, username, phone, gender, password, role) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$full_name, $email, $username, $phone, $gender, $hashed_password, $role]);

                header("Location: login.php?msg=registered");
                exit();
            }
        } catch (PDOException $e) {
            $error = 'Database error: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Registration | ApexCare</title>
    <link rel="stylesheet" href="assets/css/style.css"> <!-- Replace with your CSS path if different -->
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: sans-serif; }
        body { background: #0a1128; color: #fff; display: flex; flex-direction: column; align-items: center; justify-content: center; min-height: 100vh; padding: 20px; }
        .logo { text-align: center; margin-bottom: 20px; }
        .logo h2 { font-size: 24px; color: #00d2ff; }
        .logo p { font-size: 14px; color: #8a99ad; }
        .card { background: #111a36; padding: 30px; border-radius: 12px; width: 100%; max-width: 500px; border: 1px solid #1f2d5a; box-shadow: 0 8px 24px rgba(0,0,0,0.3); }
        .card h3 { text-align: center; margin-bottom: 20px; font-size: 22px; }
        .alert { padding: 10px; border-radius: 6px; margin-bottom: 15px; font-size: 14px; text-align: center; }
        .alert-danger { background: #ff4d4d22; border: 1px solid #ff4d4d; color: #ff4d4d; }
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
        .form-group { display: flex; flex-direction: column; margin-bottom: 15px; }
        .form-group.full { grid-column: span 2; }
        label { font-size: 12px; margin-bottom: 6px; color: #a0aec0; text-transform: uppercase; letter-spacing: 0.5px; }
        input, select { background: #0a1128; border: 1px solid #2a3a6e; padding: 12px; border-radius: 8px; color: #fff; font-size: 14px; outline: none; }
        input:focus, select:focus { border-color: #00d2ff; }
        button { grid-column: span 2; background: linear-gradient(135deg, #00d2ff, #0072ff); color: #fff; border: none; padding: 12px; border-radius: 8px; font-weight: bold; cursor: pointer; transition: 0.3s; margin-top: 10px; }
        button:hover { opacity: 0.9; }
        .login-link { text-align: center; margin-top: 15px; font-size: 14px; color: #8a99ad; }
        .login-link a { color: #00d2ff; text-decoration: none; }
    </style>
</head>
<body>

    <div class="logo">
        <h2>ApexCare</h2>
        <p>Create a new patient account</p>
    </div>

    <div class="card">
        <h3>Create Account</h3>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST" action="register.php" class="form-grid">
            <div class="form-group full">
                <label for="full_name">Full Name *</label>
                <input type="text" id="full_name" name="full_name" placeholder="John Doe" value="<?php echo htmlspecialchars($_POST['full_name'] ?? ''); ?>" required>
            </div>

            <div class="form-group">
                <label for="email">Email Address *</label>
                <input type="email" id="email" name="email" placeholder="john@example.com" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
            </div>

            <div class="form-group">
                <label for="username">Choose Username *</label>
                <input type="text" id="username" name="username" placeholder="Yash" value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>" required>
            </div>

            <div class="form-group">
                <label for="phone">Phone Number *</label>
                <!-- 10 Digit Limit Attributes Applied Here -->
                <input 
                    type="tel" 
                    id="phone" 
                    name="phone" 
                    placeholder="10-digit phone number"
                    maxlength="10"
                    minlength="10"
                    pattern="[0-9]{10}"
                    oninput="this.value = this.value.replace(/[^0-9]/g, '');"
                    value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>" 
                    required
                >
            </div>

            <div class="form-group">
                <label for="gender">Gender</label>
                <select id="gender" name="gender">
                    <option value="Male" <?php echo (($_POST['gender'] ?? '') === 'Male') ? 'selected' : ''; ?>>Male</option>
                    <option value="Female" <?php echo (($_POST['gender'] ?? '') === 'Female') ? 'selected' : ''; ?>>Female</option>
                    <option value="Other" <?php echo (($_POST['gender'] ?? '') === 'Other') ? 'selected' : ''; ?>>Other</option>
                </select>
            </div>

            <div class="form-group full">
                <label for="password">Password *</label>
                <input type="password" id="password" name="password" placeholder="••••••••" required>
            </div>

            <button type="submit">Create Account</button>
        </form>

        <div class="login-link">
            Already have an account? <a href="login.php">Sign In</a>
        </div>
    </div>

</body>
</html>