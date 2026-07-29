CREATE DATABASE IF NOT EXISTS expense_tracker;
USE expense_tracker;

-- ===========================
-- USERS
-- ===========================
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    phone VARCHAR(15),
    password VARCHAR(255) NOT NULL,
    profile_image VARCHAR(255) DEFAULT 'default.png',
    currency VARCHAR(10) DEFAULT 'INR',
    theme VARCHAR(20) DEFAULT 'dark',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ===========================
-- CATEGORIES
-- ===========================
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_name VARCHAR(100) NOT NULL,
    icon VARCHAR(50),
    type ENUM('Income','Expense') NOT NULL
);

INSERT INTO categories(category_name,icon,type) VALUES
('Food','🍔','Expense'),
('Transport','🚗','Expense'),
('Shopping','🛍','Expense'),
('Bills','💡','Expense'),
('Entertainment','🎮','Expense'),
('Medical','🏥','Expense'),
('Education','🎓','Expense'),
('Salary','💰','Income'),
('Business','🏢','Income'),
('Freelancing','💻','Income');

-- ===========================
-- INCOME
-- ===========================
CREATE TABLE income (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    category VARCHAR(100),
    amount DECIMAL(10,2),
    income_date DATE,
    note TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY(user_id) REFERENCES users(id)
);

-- ===========================
-- EXPENSE
-- ===========================
CREATE TABLE expenses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    category VARCHAR(100),
    amount DECIMAL(10,2),
    expense_date DATE,
    note TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY(user_id) REFERENCES users(id)
);

-- ===========================
-- SAVINGS
-- ===========================
CREATE TABLE savings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    goal_name VARCHAR(100),
    target_amount DECIMAL(10,2),
    saved_amount DECIMAL(10,2),
    deadline DATE,
    FOREIGN KEY(user_id) REFERENCES users(id)
);

-- ===========================
-- BUDGET
-- ===========================
CREATE TABLE budgets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    monthly_budget DECIMAL(10,2),
    month VARCHAR(20),
    year YEAR,
    FOREIGN KEY(user_id) REFERENCES users(id)
);

-- ===========================
-- NOTIFICATIONS
-- ===========================
CREATE TABLE notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    message TEXT,
    status ENUM('Read','Unread') DEFAULT 'Unread',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY(user_id) REFERENCES users(id)
);

-- ===========================
-- SETTINGS
-- ===========================
CREATE TABLE settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    dark_mode TINYINT DEFAULT 1,
    email_notification TINYINT DEFAULT 1,
    currency VARCHAR(10) DEFAULT 'INR',
    language VARCHAR(20) DEFAULT 'English',
    FOREIGN KEY(user_id) REFERENCES users(id)
);

-- ===========================
-- TRANSACTIONS (VIEW TABLE)
-- ===========================
CREATE TABLE transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    type ENUM('Income','Expense'),
    category VARCHAR(100),
    amount DECIMAL(10,2),
    transaction_date DATE,
    note TEXT,
    FOREIGN KEY(user_id) REFERENCES users(id)
);