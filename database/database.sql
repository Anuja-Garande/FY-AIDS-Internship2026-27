-- ============================================================
-- AI Shopping Website - Complete Database Schema
-- Database: ai_shopping
-- Engine: InnoDB | Charset: utf8mb4
-- ============================================================

CREATE DATABASE IF NOT EXISTS ai_shopping DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE ai_shopping;

-- ============================================================
-- 1. admins
-- ============================================================
CREATE TABLE admins (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(100) NOT NULL,
  password VARCHAR(255) NOT NULL,
  image VARCHAR(255) DEFAULT NULL,
  phone VARCHAR(20) DEFAULT NULL,
  role ENUM('super_admin','admin','manager') NOT NULL DEFAULT 'admin',
  status TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY admins_email_unique (email),
  KEY admins_role_index (role),
  KEY admins_status_index (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 2. users
-- ============================================================
CREATE TABLE users (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(100) NOT NULL,
  password VARCHAR(255) NOT NULL,
  phone VARCHAR(20) DEFAULT NULL,
  avatar VARCHAR(255) DEFAULT NULL,
  address TEXT DEFAULT NULL,
  city VARCHAR(100) DEFAULT NULL,
  state VARCHAR(100) DEFAULT NULL,
  pincode VARCHAR(10) DEFAULT NULL,
  status ENUM('active','blocked') NOT NULL DEFAULT 'active',
  email_verified TINYINT(1) NOT NULL DEFAULT 0,
  verification_token VARCHAR(255) DEFAULT NULL,
  reset_token VARCHAR(255) DEFAULT NULL,
  reset_expires DATETIME DEFAULT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY users_email_unique (email),
  KEY users_status_index (status),
  KEY users_email_verified_index (email_verified)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 3. categories
-- ============================================================
CREATE TABLE categories (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  name VARCHAR(100) NOT NULL,
  slug VARCHAR(150) NOT NULL,
  description TEXT DEFAULT NULL,
  image VARCHAR(255) DEFAULT NULL,
  parent_id INT UNSIGNED DEFAULT NULL,
  status TINYINT(1) NOT NULL DEFAULT 1,
  sort_order INT NOT NULL DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY categories_slug_unique (slug),
  KEY categories_parent_id_index (parent_id),
  KEY categories_status_index (status),
  CONSTRAINT fk_categories_parent FOREIGN KEY (parent_id) REFERENCES categories (id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 4. brands
-- ============================================================
CREATE TABLE brands (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  name VARCHAR(100) NOT NULL,
  slug VARCHAR(150) NOT NULL,
  logo VARCHAR(255) DEFAULT NULL,
  description TEXT DEFAULT NULL,
  status TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY brands_slug_unique (slug),
  KEY brands_status_index (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 5. products
-- ============================================================
CREATE TABLE products (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  name VARCHAR(255) NOT NULL,
  slug VARCHAR(255) NOT NULL,
  description TEXT DEFAULT NULL,
  short_description VARCHAR(500) DEFAULT NULL,
  price DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  discount_price DECIMAL(12,2) DEFAULT NULL,
  discount_percent INT DEFAULT NULL,
  category_id INT UNSIGNED DEFAULT NULL,
  brand_id INT UNSIGNED DEFAULT NULL,
  quantity INT NOT NULL DEFAULT 0,
  sku VARCHAR(100) DEFAULT NULL,
  rating DECIMAL(3,2) NOT NULL DEFAULT 0.00,
  reviews_count INT UNSIGNED NOT NULL DEFAULT 0,
  is_featured TINYINT(1) NOT NULL DEFAULT 0,
  is_trending TINYINT(1) NOT NULL DEFAULT 0,
  is_bestseller TINYINT(1) NOT NULL DEFAULT 0,
  is_flash_sale TINYINT(1) NOT NULL DEFAULT 0,
  flash_sale_price DECIMAL(12,2) DEFAULT NULL,
  flash_sale_end DATETIME DEFAULT NULL,
  status TINYINT(1) NOT NULL DEFAULT 1,
  meta_title VARCHAR(255) DEFAULT NULL,
  meta_description VARCHAR(500) DEFAULT NULL,
  image_url VARCHAR(500) DEFAULT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY products_slug_unique (slug),
  UNIQUE KEY products_sku_unique (sku),
  KEY products_category_id_index (category_id),
  KEY products_brand_id_index (brand_id),
  KEY products_status_index (status),
  KEY products_is_featured_index (is_featured),
  KEY products_is_trending_index (is_trending),
  KEY products_is_bestseller_index (is_bestseller),
  KEY products_is_flash_sale_index (is_flash_sale),
  KEY products_price_index (price),
  FULLTEXT KEY products_search_fulltext (name, description, short_description),
  CONSTRAINT fk_products_category FOREIGN KEY (category_id) REFERENCES categories (id) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT fk_products_brand FOREIGN KEY (brand_id) REFERENCES brands (id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 6. product_images
-- ============================================================
CREATE TABLE product_images (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  product_id INT UNSIGNED NOT NULL,
  image VARCHAR(255) NOT NULL,
  is_primary TINYINT(1) NOT NULL DEFAULT 0,
  sort_order INT NOT NULL DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY product_images_product_id_index (product_id),
  CONSTRAINT fk_product_images_product FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 7. product_specifications
-- ============================================================
CREATE TABLE product_specifications (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  product_id INT UNSIGNED NOT NULL,
  spec_name VARCHAR(150) NOT NULL,
  spec_value TEXT NOT NULL,
  sort_order INT NOT NULL DEFAULT 0,
  PRIMARY KEY (id),
  KEY product_specifications_product_id_index (product_id),
  CONSTRAINT fk_product_specifications_product FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 8. cart
-- ============================================================
CREATE TABLE cart (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id INT UNSIGNED DEFAULT NULL,
  session_id VARCHAR(255) DEFAULT NULL,
  product_id INT UNSIGNED NOT NULL,
  quantity INT NOT NULL DEFAULT 1,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY cart_user_id_index (user_id),
  KEY cart_session_id_index (session_id),
  KEY cart_product_id_index (product_id),
  CONSTRAINT fk_cart_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_cart_product FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 9. wishlist
-- ============================================================
CREATE TABLE wishlist (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id INT UNSIGNED NOT NULL,
  product_id INT UNSIGNED NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY wishlist_user_product_unique (user_id, product_id),
  KEY wishlist_product_id_index (product_id),
  CONSTRAINT fk_wishlist_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_wishlist_product FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 10. orders
-- ============================================================
CREATE TABLE orders (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id INT UNSIGNED NOT NULL,
  order_number VARCHAR(50) NOT NULL,
  subtotal DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  discount_amount DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  coupon_code VARCHAR(50) DEFAULT NULL,
  shipping_charge DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  total_amount DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  payment_method VARCHAR(50) DEFAULT NULL,
  payment_status ENUM('pending','paid','failed','refunded') NOT NULL DEFAULT 'pending',
  order_status ENUM('pending','confirmed','processing','shipped','delivered','cancelled') NOT NULL DEFAULT 'pending',
  shipping_name VARCHAR(100) NOT NULL,
  shipping_email VARCHAR(100) DEFAULT NULL,
  shipping_phone VARCHAR(20) NOT NULL,
  shipping_address TEXT NOT NULL,
  shipping_city VARCHAR(100) NOT NULL,
  shipping_state VARCHAR(100) NOT NULL,
  shipping_pincode VARCHAR(10) NOT NULL,
  tracking_number VARCHAR(100) DEFAULT NULL,
  notes TEXT DEFAULT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY orders_order_number_unique (order_number),
  KEY orders_user_id_index (user_id),
  KEY orders_order_status_index (order_status),
  KEY orders_payment_status_index (payment_status),
  KEY orders_created_at_index (created_at),
  CONSTRAINT fk_orders_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 11. order_items
-- ============================================================
CREATE TABLE order_items (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  order_id INT UNSIGNED NOT NULL,
  product_id INT UNSIGNED DEFAULT NULL,
  product_name VARCHAR(255) NOT NULL,
  product_price DECIMAL(12,2) NOT NULL,
  quantity INT NOT NULL DEFAULT 1,
  total DECIMAL(12,2) NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY order_items_order_id_index (order_id),
  KEY order_items_product_id_index (product_id),
  CONSTRAINT fk_order_items_order FOREIGN KEY (order_id) REFERENCES orders (id) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_order_items_product FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 12. reviews
-- ============================================================
CREATE TABLE reviews (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id INT UNSIGNED NOT NULL,
  product_id INT UNSIGNED NOT NULL,
  rating TINYINT UNSIGNED NOT NULL,
  title VARCHAR(255) DEFAULT NULL,
  comment TEXT DEFAULT NULL,
  is_approved TINYINT(1) NOT NULL DEFAULT 0,
  admin_reply TEXT DEFAULT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY reviews_user_id_index (user_id),
  KEY reviews_product_id_index (product_id),
  KEY reviews_is_approved_index (is_approved),
  CONSTRAINT fk_reviews_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_reviews_product FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT ck_reviews_rating CHECK (rating >= 1 AND rating <= 5)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 13. coupons
-- ============================================================
CREATE TABLE coupons (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  code VARCHAR(50) NOT NULL,
  type ENUM('percentage','flat') NOT NULL DEFAULT 'percentage',
  value DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  min_order DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  max_discount DECIMAL(12,2) DEFAULT NULL,
  usage_limit INT UNSIGNED NOT NULL DEFAULT 0,
  used_count INT UNSIGNED NOT NULL DEFAULT 0,
  expiry_date DATE DEFAULT NULL,
  status TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY coupons_code_unique (code),
  KEY coupons_status_index (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 14. banners
-- ============================================================
CREATE TABLE banners (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  title VARCHAR(255) NOT NULL,
  subtitle VARCHAR(255) DEFAULT NULL,
  image VARCHAR(255) NOT NULL,
  link VARCHAR(255) DEFAULT NULL,
  type ENUM('home','offer','festival') NOT NULL DEFAULT 'home',
  position INT NOT NULL DEFAULT 0,
  status TINYINT(1) NOT NULL DEFAULT 1,
  sort_order INT NOT NULL DEFAULT 0,
  start_date DATE DEFAULT NULL,
  end_date DATE DEFAULT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY banners_type_index (type),
  KEY banners_status_index (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 15. notifications
-- ============================================================
CREATE TABLE notifications (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id INT UNSIGNED DEFAULT NULL,
  title VARCHAR(255) NOT NULL,
  message TEXT NOT NULL,
  type ENUM('info','warning','success') NOT NULL DEFAULT 'info',
  is_read TINYINT(1) NOT NULL DEFAULT 0,
  link VARCHAR(255) DEFAULT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY notifications_user_id_index (user_id),
  KEY notifications_is_read_index (is_read),
  CONSTRAINT fk_notifications_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 16. payments
-- ============================================================
CREATE TABLE payments (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  order_id INT UNSIGNED NOT NULL,
  user_id INT UNSIGNED NOT NULL,
  method VARCHAR(50) NOT NULL,
  transaction_id VARCHAR(255) DEFAULT NULL,
  amount DECIMAL(12,2) NOT NULL,
  status VARCHAR(50) NOT NULL DEFAULT 'pending',
  gateway_response JSON DEFAULT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY payments_order_id_index (order_id),
  KEY payments_user_id_index (user_id),
  CONSTRAINT fk_payments_order FOREIGN KEY (order_id) REFERENCES orders (id) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_payments_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 17. settings
-- ============================================================
CREATE TABLE settings (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  setting_key VARCHAR(100) NOT NULL,
  setting_value TEXT NOT NULL,
  setting_group VARCHAR(100) DEFAULT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY settings_setting_key_unique (setting_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 18. addresses
-- ============================================================
CREATE TABLE addresses (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id INT UNSIGNED NOT NULL,
  name VARCHAR(100) NOT NULL,
  phone VARCHAR(20) NOT NULL,
  address_line1 VARCHAR(255) NOT NULL,
  address_line2 VARCHAR(255) DEFAULT NULL,
  city VARCHAR(100) NOT NULL,
  state VARCHAR(100) NOT NULL,
  pincode VARCHAR(10) NOT NULL,
  country VARCHAR(100) NOT NULL DEFAULT 'India',
  is_default TINYINT(1) NOT NULL DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY addresses_user_id_index (user_id),
  CONSTRAINT fk_addresses_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TRIGGERS
-- ============================================================

DELIMITER //

CREATE TRIGGER trg_reviews_after_insert AFTER INSERT ON reviews
FOR EACH ROW
BEGIN
    UPDATE products SET
        rating = (SELECT ROUND(AVG(rating), 2) FROM reviews WHERE product_id = NEW.product_id AND is_approved = 1),
        reviews_count = (SELECT COUNT(*) FROM reviews WHERE product_id = NEW.product_id AND is_approved = 1)
    WHERE id = NEW.product_id;
END//

CREATE TRIGGER trg_reviews_after_update AFTER UPDATE ON reviews
FOR EACH ROW
BEGIN
    UPDATE products SET
        rating = (SELECT ROUND(AVG(rating), 2) FROM reviews WHERE product_id = NEW.product_id AND is_approved = 1),
        reviews_count = (SELECT COUNT(*) FROM reviews WHERE product_id = NEW.product_id AND is_approved = 1)
    WHERE id = NEW.product_id;
END//

CREATE TRIGGER trg_reviews_after_delete AFTER DELETE ON reviews
FOR EACH ROW
BEGIN
    UPDATE products SET
        rating = (SELECT ROUND(AVG(rating), 2) FROM reviews WHERE product_id = OLD.product_id AND is_approved = 1),
        reviews_count = (SELECT COUNT(*) FROM reviews WHERE product_id = OLD.product_id AND is_approved = 1)
    WHERE id = OLD.product_id;
END//

CREATE TRIGGER trg_products_before_insert BEFORE INSERT ON products
FOR EACH ROW
BEGIN
    IF NEW.discount_price IS NOT NULL AND NEW.price > 0 THEN
        SET NEW.discount_percent = ROUND(((NEW.price - NEW.discount_price) / NEW.price) * 100);
        IF NEW.discount_percent < 0 THEN SET NEW.discount_percent = 0; END IF;
    END IF;
END//

CREATE TRIGGER trg_products_before_update BEFORE UPDATE ON products
FOR EACH ROW
BEGIN
    IF NEW.discount_price IS NOT NULL AND NEW.price > 0 THEN
        SET NEW.discount_percent = ROUND(((NEW.price - NEW.discount_price) / NEW.price) * 100);
        IF NEW.discount_percent < 0 THEN SET NEW.discount_percent = 0; END IF;
    ELSE
        SET NEW.discount_percent = NULL;
    END IF;
END//

CREATE TRIGGER trg_orders_before_insert BEFORE INSERT ON orders
FOR EACH ROW
BEGIN
    IF NEW.order_number IS NULL OR NEW.order_number = '' THEN
        SET NEW.order_number = CONCAT('ORD-', DATE_FORMAT(NOW(), '%Y%m%d'), '-', LPAD(FLOOR(RAND()*899999+100000), 6, '0'));
    END IF;
END//

CREATE TRIGGER trg_order_items_after_insert AFTER INSERT ON order_items
FOR EACH ROW
BEGIN
    UPDATE products SET quantity = quantity - NEW.quantity
    WHERE id = NEW.product_id AND quantity >= NEW.quantity;
END//

CREATE TRIGGER trg_orders_after_update_cancel AFTER UPDATE ON orders
FOR EACH ROW
BEGIN
    IF NEW.order_status = 'cancelled' AND OLD.order_status != 'cancelled' THEN
        UPDATE products p
        JOIN order_items oi ON p.id = oi.product_id
        SET p.quantity = p.quantity + oi.quantity
        WHERE oi.order_id = NEW.id;
    END IF;
END//

CREATE TRIGGER trg_users_before_insert BEFORE INSERT ON users
FOR EACH ROW
BEGIN
    IF NEW.verification_token IS NULL THEN
        SET NEW.verification_token = UPPER(CONCAT(
            SUBSTRING(MD5(RAND()),1,8), '-',
            SUBSTRING(MD5(RAND()),1,4), '-',
            SUBSTRING(MD5(RAND()),1,4), '-',
            SUBSTRING(MD5(RAND()),1,4), '-',
            SUBSTRING(MD5(RAND()),1,12)
        ));
    END IF;
END//

DELIMITER ;

-- ============================================================
-- DATA INSERTION
-- ============================================================

-- Default admin (password: admin123)
INSERT INTO admins (name, email, password, phone, role, status) VALUES
('Super Admin', 'admin@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+91-9876543210', 'super_admin', 1);

-- Default settings
INSERT INTO settings (setting_key, setting_value, setting_group) VALUES
('site_name', 'AI Shopping', 'general'),
('logo', 'assets/images/logo.png', 'general'),
('contact_email', 'support@aishoping.in', 'contact'),
('phone', '+91-1800-123-4567', 'contact'),
('address', '123, AI Tech Park, Electronic City, Bangalore - 560100', 'contact'),
('facebook_url', 'https://facebook.com/aishoping', 'social'),
('twitter_url', 'https://twitter.com/aishoping', 'social'),
('instagram_url', 'https://instagram.com/aishoping', 'social'),
('youtube_url', 'https://youtube.com/@aishoping', 'social'),
('currency', 'INR', 'general'),
('currency_symbol', '₹', 'general'),
('footer_text', '© 2025 AI Shopping. All rights reserved.', 'general');

-- Categories
INSERT INTO categories (name, slug, description, status, sort_order) VALUES
('Electronics', 'electronics', 'Latest gadgets and electronic devices', 1, 1),
('Fashion', 'fashion', 'Trendy clothing and accessories', 1, 2),
('Home & Garden', 'home-garden', 'Home improvement and garden essentials', 1, 3),
('Sports', 'sports', 'Sports equipment and fitness gear', 1, 4),
('Books', 'books', 'Books across all genres', 1, 5),
('Beauty', 'beauty', 'Beauty and personal care products', 1, 6);

-- Subcategories
INSERT INTO categories (name, slug, description, parent_id, status, sort_order) VALUES
('Mobile Phones', 'mobile-phones', 'Smartphones and accessories', 1, 1, 1),
('Laptops', 'laptops', 'Laptops and notebooks', 1, 1, 2),
('Headphones', 'headphones', 'Audio devices and headphones', 1, 1, 3),
('Men Clothing', 'men-clothing', 'Clothing for men', 2, 1, 1),
('Women Clothing', 'women-clothing', 'Clothing for women', 2, 1, 2),
('Footwear', 'footwear', 'Shoes and sandals', 2, 1, 3),
('Kitchen', 'kitchen', 'Kitchen appliances and essentials', 3, 1, 1),
('Fitness', 'fitness', 'Fitness and gym equipment', 4, 1, 1),
('Fiction', 'fiction', 'Fiction books', 5, 1, 1);

-- Brands
INSERT INTO brands (name, slug, description, status) VALUES
('Samsung', 'samsung', 'Global leader in electronics', 1),
('Apple', 'apple', 'Innovation and premium quality', 1),
('Nike', 'nike', 'Just Do It - World leader in sports', 1),
('Sony', 'sony', 'Electronics and entertainment', 1),
('HP', 'hp', 'Computing and printing solutions', 1),
('Lenovo', 'lenovo', 'Think different, build different', 1),
('OnePlus', 'oneplus', 'Never Settle - Flagship killer', 1),
('Adidas', 'adidas', 'Impossible is Nothing', 1);

-- Users (passwords are all 'password' hashed with bcrypt)
INSERT INTO users (name, email, password, phone, city, state, pincode, status, email_verified) VALUES
('Rahul Sharma', 'rahul@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+91-9876543211', 'Bangalore', 'Karnataka', '560038', 'active', 1),
('Priya Patel', 'priya@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+91-9876543212', 'Mumbai', 'Maharashtra', '400093', 'active', 1),
('Amit Singh', 'amit@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+91-9876543213', 'New Delhi', 'Delhi', '110001', 'active', 1),
('Neha Gupta', 'neha@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+91-9876543214', 'Kolkata', 'West Bengal', '700064', 'active', 1),
('Vikram Reddy', 'vikram@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+91-9876543215', 'Hyderabad', 'Telangana', '500033', 'active', 1);

-- Products
INSERT INTO products (name, slug, description, short_description, price, discount_price, category_id, brand_id, quantity, sku, rating, reviews_count, is_featured, is_trending, is_bestseller, is_flash_sale, flash_sale_price, flash_sale_end, status, image_url) VALUES
('Samsung Galaxy S25 Ultra', 'samsung-galaxy-s25-ultra', 'Latest Samsung flagship with AI-powered camera, S Pen support, and stunning display.', '12GB RAM, 256GB Storage, 200MP Camera', 124999.00, 109999.00, 7, 1, 50, 'SAM-S25U-256', 4.80, 245, 1, 1, 1, 1, 99999.00, '2026-08-15 23:59:59', 1, 'https://placehold.co/400x400?text=Product'),
('iPhone 16 Pro Max', 'iphone-16-pro-max', 'Apple iPhone 16 Pro Max with A18 Bionic chip, titanium design, and pro camera system.', '256GB, Titanium Design, A18 Chip', 159900.00, 149900.00, 7, 2, 35, 'APL-IP16PM-256', 4.90, 512, 1, 1, 1, 1, 139999.00, '2026-08-10 23:59:59', 1, 'https://placehold.co/400x400?text=Product'),
('OnePlus 13 Pro', 'oneplus-13-pro', 'Flagship killer with Snapdragon 8 Gen 4, Hasselblad cameras, and 100W charging.', '16GB RAM, 512GB, Hasselblad Camera', 89999.00, 79999.00, 7, 7, 60, 'OPL-13P-512', 4.50, 178, 1, 1, 0, 0, NULL, NULL, 1, 'https://placehold.co/400x400?text=Product'),
('Samsung Galaxy A55', 'samsung-galaxy-a55', 'Mid-range powerhouse with AMOLED display and 50MP camera.', '8GB RAM, 128GB, 5000mAh Battery', 34999.00, 29999.00, 7, 1, 80, 'SAM-A55-128', 4.30, 89, 0, 0, 1, 0, NULL, NULL, 1, 'https://placehold.co/400x400?text=Product'),
('HP Pavilion 15', 'hp-pavilion-15', 'Powerful laptop for work and play with Intel i7 and RTX 3050.', 'Intel i7, 16GB RAM, 512GB SSD, RTX 3050', 78999.00, 69999.00, 8, 5, 25, 'HP-PAV15-I7', 4.40, 134, 1, 1, 0, 0, NULL, NULL, 1, 'https://placehold.co/400x400?text=Product'),
('Lenovo ThinkPad X1 Carbon', 'lenovo-thinkpad-x1-carbon', 'Ultra-light business laptop with 14-inch 2K display and Intel i7.', 'Intel i7, 16GB, 512GB SSD, 2K Display', 129999.00, 114999.00, 8, 6, 20, 'LEN-TPX1C-16', 4.60, 98, 1, 0, 1, 0, NULL, NULL, 1, 'https://placehold.co/400x400?text=Product'),
('Apple MacBook Air M3', 'apple-macbook-air-m3', 'Ultra-thin laptop with Apple M3 chip, 15-inch Liquid Retina display.', 'M3, 8GB, 256GB SSD, 15-inch', 124900.00, 114900.00, 8, 2, 40, 'APL-MBA-M3-256', 4.70, 312, 1, 1, 1, 1, 109900.00, '2026-07-31 23:59:59', 1, 'https://placehold.co/400x400?text=Product'),
('HP Victus Gaming', 'hp-victus-gaming', 'Gaming laptop with AMD Ryzen 7 and RTX 4060.', 'Ryzen 7, 16GB, 1TB SSD, RTX 4060, 144Hz', 89999.00, 79999.00, 8, 5, 30, 'HP-VIC-R7-1T', 4.20, 67, 0, 1, 0, 0, NULL, NULL, 1, 'https://placehold.co/400x400?text=Product'),
('Sony WH-1000XM6', 'sony-wh-1000xm6', 'Industry-leading noise cancellation with 40-hour battery life.', 'Wireless ANC, 40hr Battery, Hi-Res Audio', 25999.00, 22999.00, 9, 4, 45, 'SNY-WH1000XM6', 4.70, 423, 1, 1, 1, 1, 19999.00, '2026-07-25 23:59:59', 1, 'https://placehold.co/400x400?text=Product'),
('Samsung Galaxy Buds Pro 3', 'samsung-galaxy-buds-pro-3', 'Premium TWS earbuds with 360 Audio and ANC.', 'ANC, 360 Audio, IP57, 29hr Battery', 19999.00, 15999.00, 9, 1, 65, 'SAM-BUDS-P3', 4.40, 156, 0, 1, 0, 0, NULL, NULL, 1, 'https://placehold.co/400x400?text=Product'),
('Apple AirPods Pro 3', 'apple-airpods-pro-3', 'Adaptive audio with USB-C, spatial audio.', 'USB-C, Spatial Audio, Adaptive ANC', 24900.00, 21900.00, 9, 2, 55, 'APL-AIRP-P3', 4.60, 389, 1, 0, 1, 1, 19900.00, '2026-08-05 23:59:59', 1, 'https://placehold.co/400x400?text=Product'),
('Nike Air Jordan Retro', 'nike-air-jordan-retro', 'Iconic basketball sneakers with premium leather and Air cushioning.', 'Leather, Air Sole, High-top Design', 18995.00, 15995.00, 12, 3, 40, 'NK-AJR-2025', 4.50, 287, 1, 1, 1, 0, NULL, NULL, 1, 'https://placehold.co/400x400?text=Product'),
('Adidas Ultraboost 25', 'adidas-ultraboost-25', 'Ultra-comfortable running shoes with Boost midsole.', 'Boost Midsole, Primeknit Upper', 15999.00, 12999.00, 12, 8, 55, 'ADI-UB25-BLK', 4.60, 198, 1, 1, 0, 1, 11999.00, '2026-07-30 23:59:59', 1, 'https://placehold.co/400x400?text=Product'),
('Nike Dri-FIT T-Shirt', 'nike-dri-fit-tshirt', 'Performance t-shirt with moisture-wicking technology.', 'Dri-FIT, Breathable, Regular Fit', 2995.00, 2395.00, 10, 3, 120, 'NK-DFT-L', 4.30, 78, 0, 0, 1, 0, NULL, NULL, 1, 'https://placehold.co/400x400?text=Product'),
('Nike Yoga Luxe Leggings', 'nike-yoga-luxe-leggings', 'Premium high-waist leggings with stretch fabric.', 'High-Waist, Stretch, Moisture-Wicking', 4995.00, 3995.00, 11, 3, 75, 'NK-YLL-M', 4.40, 92, 0, 1, 0, 0, NULL, NULL, 1, 'https://placehold.co/400x400?text=Product'),
('Adidas Campus 00s', 'adidas-campus-00s', 'Classic sneakers with suede upper and rubber cupsole.', 'Suede, Rubber Sole, Vintage Style', 8999.00, 7499.00, 12, 8, 90, 'ADI-CAMP-00S', 4.20, 145, 0, 0, 1, 0, NULL, NULL, 1, 'https://placehold.co/400x400?text=Product'),
('Samsung 55-inch OLED TV', 'samsung-55-oled-tv', 'Stunning 55-inch OLED TV with Quantum HDR and Dolby Atmos.', '55-inch OLED, 4K, Dolby Atmos, Smart TV', 109999.00, 89999.00, 1, 1, 20, 'SAM-55OLED-QD', 4.70, 312, 1, 1, 0, 1, 84999.00, '2026-08-20 23:59:59', 1, 'https://placehold.co/400x400?text=Product'),
('Sony Bravia XR 65-inch', 'sony-bravia-xr-65', 'Cognitive processor XR with stunning 4K HDR.', '65-inch, 4K, XR Processor, Dolby Vision', 149999.00, 129999.00, 1, 4, 15, 'SNY-BVXR-65', 4.60, 201, 1, 0, 1, 0, NULL, NULL, 1, 'https://placehold.co/400x400?text=Product'),
('Nike Pro Training Set', 'nike-pro-training-set', 'Complete training gear set with resistance bands and mat.', 'Resistance Bands, Mat, Dri-FIT Fabric', 3499.00, 2799.00, 8, 3, 100, 'NK-PRO-TRN', 4.10, 56, 0, 0, 0, 0, NULL, NULL, 1, 'https://placehold.co/400x400?text=Product'),
('Adidas Football', 'adidas-football', 'Official size 5 match ball with stitched PU cover.', 'Size 5, PU Stitched, FIFA Quality', 4499.00, 3799.00, 8, 8, 85, 'ADI-FTB-S5', 4.30, 34, 0, 1, 0, 0, NULL, NULL, 1, 'https://placehold.co/400x400?text=Product'),
('Wings of Fire - APJ Abdul Kalam', 'wings-of-fire', 'Autobiography of Dr. APJ Abdul Kalam.', 'Paperback, 180 Pages, Inspirational', 399.00, 299.00, 5, NULL, 200, 'BK-WOF-001', 4.80, 567, 1, 1, 1, 0, NULL, NULL, 1, 'https://placehold.co/400x400?text=Product'),
('The Alchemist - Paulo Coelho', 'the-alchemist', 'International bestselling novel about following your dreams.', 'Paperback, 208 Pages, Bestseller', 350.00, 259.00, 5, NULL, 180, 'BK-ALC-001', 4.60, 432, 1, 0, 1, 0, NULL, NULL, 1, 'https://placehold.co/400x400?text=Product'),
('Rich Dad Poor Dad', 'rich-dad-poor-dad', 'Personal finance classic by Robert Kiyosaki.', 'Paperback, 336 Pages, Finance', 499.00, 349.00, 5, NULL, 150, 'BK-RDPD-001', 4.50, 789, 0, 1, 0, 0, NULL, NULL, 1, 'https://placehold.co/400x400?text=Product'),
('Sony 4K Action Cam', 'sony-4k-action-cam', 'Compact 4K action camera with stabilization.', '4K, Stabilization, Waterproof, 32MP', 29999.00, 25999.00, 1, 4, 35, 'SNY-AC-4K', 4.20, 78, 0, 0, 0, 0, NULL, NULL, 1, 'https://placehold.co/400x400?text=Product');

-- Product Images
INSERT INTO product_images (product_id, image, is_primary, sort_order) VALUES
(1, 'products/samsung-s25-ultra.jpg', 1, 1),
(2, 'products/iphone-16pm.jpg', 1, 1),
(3, 'products/oneplus-13p.jpg', 1, 1),
(4, 'products/samsung-a55.jpg', 1, 1),
(5, 'products/hp-pavilion-15.jpg', 1, 1),
(6, 'products/lenovo-x1-carbon.jpg', 1, 1),
(7, 'products/macbook-air-m3.jpg', 1, 1),
(8, 'products/hp-victus.jpg', 1, 1),
(9, 'products/sony-wh1000xm6.jpg', 1, 1),
(10, 'products/galaxy-buds-pro3.jpg', 1, 1),
(11, 'products/airpods-pro3.jpg', 1, 1),
(12, 'products/air-jordan-retro.jpg', 1, 1),
(13, 'products/adidas-ultraboost-25.jpg', 1, 1),
(14, 'products/nike-drifit.jpg', 1, 1),
(15, 'products/nike-yoga-leggings.jpg', 1, 1),
(16, 'products/adidas-campus-00s.jpg', 1, 1),
(17, 'products/samsung-oled-tv.jpg', 1, 1),
(18, 'products/sony-bravia-xr.jpg', 1, 1),
(19, 'products/nike-training-set.jpg', 1, 1),
(20, 'products/adidas-football.jpg', 1, 1),
(21, 'products/wings-of-fire.jpg', 1, 1),
(22, 'products/the-alchemist.jpg', 1, 1),
(23, 'products/rich-dad-poor-dad.jpg', 1, 1),
(24, 'products/sony-action-cam.jpg', 1, 1);

-- Product Specifications
INSERT INTO product_specifications (product_id, spec_name, spec_value, sort_order) VALUES
(1, 'Display', '6.9-inch Dynamic AMOLED 2X, 120Hz', 1),
(1, 'Processor', 'Exynos 2500 / Snapdragon 8 Gen 4', 2),
(1, 'RAM', '12GB LPDDR5X', 3),
(1, 'Storage', '256GB UFS 4.0', 4),
(1, 'Camera', '200MP + 50MP + 12MP + 10MP', 5),
(1, 'Battery', '5000mAh, 45W Fast Charging', 6),
(2, 'Display', '6.9-inch Super Retina XDR OLED, 120Hz', 1),
(2, 'Processor', 'A18 Bionic', 2),
(2, 'RAM', '8GB', 3),
(2, 'Storage', '256GB', 4),
(2, 'Camera', '48MP Main + 12MP Ultra + 12MP Tele + LiDAR', 5),
(2, 'Battery', '4685mAh, 40W Fast Charging', 6),
(7, 'Display', '15.3-inch Liquid Retina, 2880x1864', 1),
(7, 'Processor', 'Apple M3 8-core', 2),
(7, 'RAM', '8GB Unified', 3),
(7, 'Storage', '256GB SSD', 4),
(7, 'Battery', 'Up to 18 hours', 5),
(9, 'Driver', '40mm Dynamic Driver', 1),
(9, 'Battery', '40 hours (ANC on)', 2),
(9, 'Connectivity', 'Bluetooth 5.3, 3.5mm, USB-C', 3),
(9, 'Weight', '250g', 4);

-- Reviews
INSERT INTO reviews (user_id, product_id, rating, title, comment, is_approved) VALUES
(1, 1, 5, 'Best phone ever!', 'Absolutely love the camera quality and battery life. The S Pen is a game changer.', 1),
(2, 1, 4, 'Great phone but pricey', 'Amazing performance but very expensive compared to competition.', 1),
(3, 2, 5, 'Worth every penny', 'The best iPhone yet. The camera is incredible and battery lasts all day.', 1),
(4, 2, 5, 'Apple at its best', 'Smooth, fast, and the battery lasts all day. Titanium design is gorgeous.', 1),
(5, 7, 4, 'Great laptop', 'Perfect for my daily workflow. Light and powerful. Battery is amazing.', 1),
(1, 9, 5, 'Best ANC headphones', 'The noise cancellation is phenomenal. Worth every rupee for daily commute.', 1),
(2, 12, 4, 'Stylish and comfortable', 'Great shoes for casual wear and light sports. True to size.', 1),
(3, 21, 5, 'Must read for everyone', 'Incredibly inspiring autobiography of a great leader.', 1),
(4, 22, 5, 'Life-changing book', 'This book changed my perspective on life and dreams.', 1),
(5, 23, 4, 'Good financial advice', 'Practical tips for financial independence. A must-read.', 1);

-- Coupons
INSERT INTO coupons (code, type, value, min_order, max_discount, usage_limit, used_count, expiry_date, status) VALUES
('WELCOME20', 'percentage', 20.00, 499.00, 500.00, 100, 25, '2026-12-31', 1),
('SAVE500', 'flat', 500.00, 2499.00, 500.00, 50, 12, '2026-09-30', 1),
('FREESHIP', 'flat', 49.00, 0.00, 49.00, 200, 88, '2026-12-31', 1),
('FESTIVE15', 'percentage', 15.00, 999.00, 1000.00, 75, 34, '2026-08-31', 1),
('SUMMER25', 'percentage', 25.00, 1499.00, 750.00, 100, 5, '2026-07-31', 1),
('FLASH50', 'flat', 50.00, 299.00, 50.00, 150, 67, '2026-08-15', 1);

-- Banners
INSERT INTO banners (title, subtitle, image, link, type, position, status, sort_order, start_date, end_date) VALUES
('Summer Sale 2026', 'Up to 50% off on electronics', 'banners/summer-sale.jpg', 'products.php?category=1', 'offer', 1, 1, 1, '2026-07-01', '2026-08-31'),
('New Arrivals', 'Check out the latest products', 'banners/new-arrivals.jpg', 'products.php?sort=newest', 'home', 2, 1, 2, '2026-07-01', '2026-12-31'),
('Festive Bonanza', 'Special discounts on everything', 'banners/festive-bonanza.jpg', 'deals.php', 'festival', 3, 1, 3, '2026-10-15', '2026-11-15'),
('Flash Sale', '24-hour deals you cannot miss', 'banners/flash-sale.jpg', 'deals.php', 'offer', 4, 1, 4, '2026-07-15', '2026-07-16'),
('Fashion Week', 'Trendy styles for every occasion', 'banners/fashion-week.jpg', 'products.php?category=2', 'home', 5, 1, 5, '2026-07-01', '2026-09-30');

-- Notifications
INSERT INTO notifications (user_id, title, message, type, link) VALUES
(NULL, 'Summer Sale Started', 'Our biggest summer sale is live with up to 50% off on all categories!', 'info', 'deals.php'),
(NULL, 'New iPhone Launched', 'iPhone 16 Pro Max is now available for pre-order.', 'success', 'product.php?id=2'),
(1, 'Order Confirmed', 'Your order has been confirmed successfully.', 'success', 'orders.php'),
(2, 'Flash Sale Alert', 'Flash sale on Sony WH-1000XM6 ends soon!', 'warning', 'product.php?id=9');

-- Addresses
INSERT INTO addresses (user_id, name, phone, address_line1, address_line2, city, state, pincode, country, is_default) VALUES
(1, 'Rahul Sharma', '+91-9876543211', '42, MG Road', 'Indiranagar', 'Bangalore', 'Karnataka', '560038', 'India', 1),
(1, 'Rahul Sharma', '+91-9876543211', '15, 3rd Cross', 'Koramangala', 'Bangalore', 'Karnataka', '560034', 'India', 0),
(2, 'Priya Patel', '+91-9876543212', '15, Andheri East', 'Near Station', 'Mumbai', 'Maharashtra', '400093', 'India', 1),
(3, 'Amit Singh', '+91-9876543213', '88, Connaught Place', 'Block C', 'New Delhi', 'Delhi', '110001', 'India', 1),
(4, 'Neha Gupta', '+91-9876543214', '7/2, Salt Lake Sector 1', 'Near Lake', 'Kolkata', 'West Bengal', '700064', 'India', 1),
(5, 'Vikram Reddy', '+91-9876543215', '201, Jubilee Hills', 'Road No 36', 'Hyderabad', 'Telangana', '500033', 'India', 1);
