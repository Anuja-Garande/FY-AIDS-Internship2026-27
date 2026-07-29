-- ==========================================
-- Personal Expense Tracker Database
-- Database: my_expense_manager
-- ==========================================

CREATE DATABASE IF NOT EXISTS my_expense_manager;
USE my_expense_manager;

-- ==========================================
-- Users Table
-- ==========================================

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ==========================================
-- Categories Table
-- ==========================================

CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_name VARCHAR(100) NOT NULL,
    type ENUM('Income','Expense') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ==========================================
-- Income Table
-- ==========================================

CREATE TABLE income (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    category_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    income_date DATE NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(id)
    ON DELETE CASCADE,

    FOREIGN KEY (category_id) REFERENCES categories(id)
    ON DELETE CASCADE
);

-- ==========================================
-- Expenses Table
-- ==========================================

CREATE TABLE expenses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    category_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    expense_date DATE NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(id)
    ON DELETE CASCADE,

    FOREIGN KEY (category_id) REFERENCES categories(id)
    ON DELETE CASCADE
);

-- ==========================================
-- Default Categories
-- ==========================================

INSERT INTO categories (category_name, type) VALUES
('Salary','Income'),
('Business','Income'),
('Freelancing','Income'),
('Investment','Income'),
('Bonus','Income'),

('Food','Expense'),
('Travel','Expense'),
('Shopping','Expense'),
('Electricity Bill','Expense'),
('Internet Bill','Expense'),
('Mobile Recharge','Expense'),
('Medical','Expense'),
('Education','Expense'),
('Entertainment','Expense'),
('Rent','Expense'),
('Fuel','Expense'),
('Other','Expense');