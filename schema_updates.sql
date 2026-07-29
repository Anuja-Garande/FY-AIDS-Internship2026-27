USE expense_tracker;

-- Role column for Admin Panel access control
ALTER TABLE users ADD COLUMN role ENUM('user','admin') DEFAULT 'user' AFTER password;

-- 2FA flag used on the Security page
ALTER TABLE settings ADD COLUMN two_factor TINYINT DEFAULT 0 AFTER email_notification;

-- Login activity log used on the Security page
CREATE TABLE login_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    ip_address VARCHAR(45),
    user_agent VARCHAR(255),
    login_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY(user_id) REFERENCES users(id)
);

-- income / expenses were missing indexes commonly queried on date + user
ALTER TABLE income ADD INDEX idx_user_date (user_id, income_date);
ALTER TABLE expenses ADD INDEX idx_user_date (user_id, expense_date);

-- Promote your own account to admin so you can see the Admin Panel.
-- Replace the email with your own registered account, then run this line:
-- UPDATE users SET role='admin' WHERE email='you@example.com';
