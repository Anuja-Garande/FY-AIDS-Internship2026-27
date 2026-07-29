<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/functions.php';

class Auth {

    public function login($email, $password) {
        $email = trim(strtolower($email));

        if (empty($email) || empty($password)) {
            return ['success' => false, 'message' => 'Email and password are required.'];
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'message' => 'Invalid email format.'];
        }

        $db = Database::getInstance();
        $user = $db->fetch("SELECT * FROM users WHERE email = ?", [$email]);

        if (!$user) {
            $admin = $db->fetch("SELECT * FROM admins WHERE email = ?", [$email]);
            if ($admin) {
                if ($admin['status'] != 1) {
                    return ['success' => false, 'message' => 'Your account has been deactivated.'];
                }
                if (!password_verify($password, $admin['password'])) {
                    return ['success' => false, 'message' => 'Incorrect password.'];
                }
                $_SESSION['user_id'] = $admin['id'];
                $_SESSION['user_name'] = $admin['name'];
                $db->update("UPDATE admins SET last_login = NOW() WHERE id = ?", [$admin['id']]);
                return ['success' => true, 'message' => 'Login successful.', 'user' => $admin, 'is_admin' => true];
            }
            return ['success' => false, 'message' => 'No account found with this email.'];
        }

        if ($user['status'] === 'blocked' || $user['status'] == 0) {
            return ['success' => false, 'message' => 'Your account has been deactivated.'];
        }

        if (!password_verify($password, $user['password'])) {
            return ['success' => false, 'message' => 'Incorrect password.'];
        }

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];

        $db->query("UPDATE users SET last_login = NOW() WHERE id = ?", [$user['id']]);

        return ['success' => true, 'message' => 'Login successful.', 'user' => $user];
    }

    public function register($name, $email, $phone, $password) {
        $name = trim($name);
        $email = trim(strtolower($email));
        $phone = trim($phone);

        if (empty($name) || empty($email) || empty($password) || empty($phone)) {
            return ['success' => false, 'message' => 'All fields are required.'];
        }

        if (strlen($name) < 2 || strlen($name) > 100) {
            return ['success' => false, 'message' => 'Name must be between 2 and 100 characters.'];
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'message' => 'Invalid email format.'];
        }

        if (strlen($password) < 6) {
            return ['success' => false, 'message' => 'Password must be at least 6 characters.'];
        }

        if (!preg_match('/^[0-9]{10}$/', $phone)) {
            return ['success' => false, 'message' => 'Phone number must be 10 digits.'];
        }

        $db = Database::getInstance();

        $existing = $db->fetch("SELECT id FROM users WHERE email = ?", [$email]);
        if ($existing) {
            return ['success' => false, 'message' => 'An account with this email already exists.'];
        }

        $hashed = password_hash($password, PASSWORD_DEFAULT);

        $user_id = $db->insert(
            "INSERT INTO users (name, email, password, phone, status, created_at) VALUES (?, ?, ?, ?, 1, NOW())",
            [$name, $email, $hashed, $phone]
        );

        if (!$user_id) {
            return ['success' => false, 'message' => 'Registration failed. Please try again.'];
        }

        $_SESSION['user_id'] = $user_id;
        $_SESSION['user_name'] = $name;

        return ['success' => true, 'message' => 'Registration successful.'];
    }

    public function logout() {
        session_unset();
        session_destroy();
        session_start();
        return ['success' => true, 'message' => 'Logged out successfully.'];
    }

    public function adminLogin($email, $password) {
        $email = trim(strtolower($email));

        if (empty($email) || empty($password)) {
            return ['success' => false, 'message' => 'Email and password are required.'];
        }

        $db = Database::getInstance();
        $admin = $db->fetch("SELECT * FROM admins WHERE email = ?", [$email]);

        if (!$admin) {
            return ['success' => false, 'message' => 'No admin account found.'];
        }

        if ($admin['status'] != 1) {
            return ['success' => false, 'message' => 'Your admin account has been deactivated.'];
        }

        if (!password_verify($password, $admin['password'])) {
            return ['success' => false, 'message' => 'Incorrect password.'];
        }

        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_name'] = $admin['name'];

        $db->update("UPDATE admins SET last_login = NOW() WHERE id = ?", [$admin['id']]);

        return ['success' => true, 'message' => 'Admin login successful.', 'admin' => $admin];
    }

    public function adminLogout() {
        unset($_SESSION['admin_id']);
        unset($_SESSION['admin_name']);
        return ['success' => true, 'message' => 'Admin logged out.'];
    }

    public function updateProfile($user_id, $data) {
        if (empty($user_id) || empty($data)) {
            return ['success' => false, 'message' => 'Invalid data.'];
        }

        $name = trim($data['name'] ?? '');
        $phone = trim($data['phone'] ?? '');

        if (strlen($name) < 2) {
            return ['success' => false, 'message' => 'Name must be at least 2 characters.'];
        }

        if (!empty($phone) && !preg_match('/^[0-9]{10}$/', $phone)) {
            return ['success' => false, 'message' => 'Phone number must be 10 digits.'];
        }

        $db = Database::getInstance();

        $fields = ['name = ?', 'phone = ?'];
        $params = [$name, $phone];

        if (!empty($data['address'])) {
            $fields[] = 'address = ?';
            $params[] = trim($data['address']);
        }
        if (!empty($data['city'])) {
            $fields[] = 'city = ?';
            $params[] = trim($data['city']);
        }
        if (!empty($data['state'])) {
            $fields[] = 'state = ?';
            $params[] = trim($data['state']);
        }
        if (!empty($data['pincode'])) {
            $fields[] = 'pincode = ?';
            $params[] = trim($data['pincode']);
        }

        $params[] = $user_id;
        $sql = "UPDATE users SET " . implode(', ', $fields) . " WHERE id = ?";
        $result = $db->update($sql, $params);

        if ($result !== false) {
            $_SESSION['user_name'] = $name;
            return ['success' => true, 'message' => 'Profile updated successfully.'];
        }

        return ['success' => false, 'message' => 'Failed to update profile.'];
    }

    public function changePassword($user_id, $old_pass, $new_pass) {
        if (empty($old_pass) || empty($new_pass)) {
            return ['success' => false, 'message' => 'Both passwords are required.'];
        }

        if (strlen($new_pass) < 6) {
            return ['success' => false, 'message' => 'New password must be at least 6 characters.'];
        }

        $db = Database::getInstance();
        $user = $db->fetch("SELECT password FROM users WHERE id = ?", [$user_id]);

        if (!$user || !password_verify($old_pass, $user['password'])) {
            return ['success' => false, 'message' => 'Current password is incorrect.'];
        }

        $hashed = password_hash($new_pass, PASSWORD_DEFAULT);
        $result = $db->update("UPDATE users SET password = ? WHERE id = ?", [$hashed, $user_id]);

        if ($result !== false) {
            return ['success' => true, 'message' => 'Password changed successfully.'];
        }

        return ['success' => false, 'message' => 'Failed to change password.'];
    }

    public function forgotPassword($email) {
        $email = trim(strtolower($email));

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'message' => 'Valid email is required.'];
        }

        $db = Database::getInstance();
        $user = $db->fetch("SELECT id FROM users WHERE email = ?", [$email]);

        if (!$user) {
            return ['success' => false, 'message' => 'No account found with this email.'];
        }

        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

        $db->update("UPDATE users SET reset_token = ?, reset_token_expires = ? WHERE id = ?", [$token, $expires, $user['id']]);

        return [
            'success' => true,
            'message' => 'Password reset link generated.',
            'token'   => $token
        ];
    }

    public function resetPassword($token, $password) {
        if (empty($token) || empty($password)) {
            return ['success' => false, 'message' => 'Token and password are required.'];
        }

        if (strlen($password) < 6) {
            return ['success' => false, 'message' => 'Password must be at least 6 characters.'];
        }

        $db = Database::getInstance();
        $user = $db->fetch(
            "SELECT id FROM users WHERE reset_token = ? AND reset_token_expires > NOW()",
            [$token]
        );

        if (!$user) {
            return ['success' => false, 'message' => 'Invalid or expired reset token.'];
        }

        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $db->update(
            "UPDATE users SET password = ?, reset_token = NULL, reset_token_expires = NULL WHERE id = ?",
            [$hashed, $user['id']]
        );

        return ['success' => true, 'message' => 'Password has been reset successfully.'];
    }
}
