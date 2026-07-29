-- ============================================================
-- UPDATE SCRIPT — run this in phpMyAdmin (Import tab) if you
-- ALREADY imported tourism_db.sql before and don't want to
-- lose your existing data. Adds support for:
--   - Destination photo galleries
--   - Trending / recently viewed tracking (view counter)
--   - Login attempt rate-limiting (brute-force protection)
-- Safe to run even if some parts already exist.
-- ============================================================

USE tourism_db;

-- View counter for "Trending Now"
ALTER TABLE destinations ADD COLUMN IF NOT EXISTS views INT NOT NULL DEFAULT 0;

-- Photo gallery per destination
CREATE TABLE IF NOT EXISTS destination_images (
    image_id INT AUTO_INCREMENT PRIMARY KEY,
    destination_id INT NOT NULL,
    image VARCHAR(255) NOT NULL,
    caption VARCHAR(150) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (destination_id) REFERENCES destinations(destination_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Login attempt tracking (used for both user & admin login lockout)
CREATE TABLE IF NOT EXISTS login_attempts (
    attempt_id INT AUTO_INCREMENT PRIMARY KEY,
    identifier VARCHAR(150) NOT NULL,   -- email/username + ip combined
    attempts INT NOT NULL DEFAULT 1,
    last_attempt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    locked_until TIMESTAMP NULL DEFAULT NULL,
    UNIQUE KEY unique_identifier (identifier)
) ENGINE=InnoDB;
