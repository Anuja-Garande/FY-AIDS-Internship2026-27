-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 29, 2026 at 12:33 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `ai_shopping`
--

-- --------------------------------------------------------

--
-- Table structure for table `addresses`
--

CREATE TABLE `addresses` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `address_line1` varchar(255) NOT NULL,
  `address_line2` varchar(255) DEFAULT NULL,
  `city` varchar(100) NOT NULL,
  `state` varchar(100) NOT NULL,
  `pincode` varchar(10) NOT NULL,
  `country` varchar(100) NOT NULL DEFAULT 'India',
  `is_default` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `addresses`
--

INSERT INTO `addresses` (`id`, `user_id`, `name`, `phone`, `address_line1`, `address_line2`, `city`, `state`, `pincode`, `country`, `is_default`, `created_at`) VALUES
(7, 8, 'rahul veer', '9822396767', 'tarwadi', 'phursungi', 'pune', 'Maharashtra', '412308', 'India', 1, '2026-07-19 11:21:06');

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `role` enum('super_admin','admin','manager') NOT NULL DEFAULT 'admin',
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `last_login` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `name`, `email`, `password`, `image`, `phone`, `role`, `status`, `created_at`, `updated_at`, `last_login`) VALUES
(2, 'Admin', 'admin@aishopping.com', '$2y$10$GDekUtOG.FT.cr8YgKx.beXJzCaMUtcLJqsSX62yu5efBOzAX1Zb.', NULL, '+91-9999999999', 'super_admin', 1, '2026-07-19 08:55:29', '2026-07-29 09:57:15', '2026-07-29 09:57:15');

-- --------------------------------------------------------

--
-- Table structure for table `banners`
--

CREATE TABLE `banners` (
  `id` int(10) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `subtitle` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `badge` varchar(100) DEFAULT NULL,
  `badge_icon` varchar(100) DEFAULT 'fas fa-tag',
  `button_text` varchar(100) DEFAULT NULL,
  `button_icon` varchar(100) DEFAULT NULL,
  `gradient` varchar(255) DEFAULT NULL,
  `image` varchar(255) NOT NULL,
  `link` varchar(255) DEFAULT NULL,
  `type` enum('home','offer','festival') NOT NULL DEFAULT 'home',
  `position` varchar(50) DEFAULT 'home',
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `banners`
--

INSERT INTO `banners` (`id`, `title`, `subtitle`, `description`, `badge`, `badge_icon`, `button_text`, `button_icon`, `gradient`, `image`, `link`, `type`, `position`, `status`, `sort_order`, `start_date`, `end_date`, `created_at`) VALUES
(1, 'Summer Sale 2026', 'Up to 50% off on electronics', NULL, NULL, 'fas fa-tag', NULL, NULL, NULL, 'shoes-sale.jpg', 'products.php?category=1', 'offer', 'carousel', 1, 1, '2026-07-01', '2026-08-31', '2026-07-17 14:29:29'),
(2, 'New Arrivals', 'Check out the latest products', NULL, NULL, 'fas fa-tag', NULL, NULL, NULL, 'banner-home.jpeg', 'products.php?sort=newest', 'home', '2', 1, 2, '2026-07-01', '2026-12-31', '2026-07-17 14:29:29'),
(3, 'Festive Bonanza', 'Special discounts on everything', NULL, NULL, 'fas fa-tag', NULL, NULL, NULL, 'monsoon-offer.jpg', 'deals.php', 'festival', '3', 1, 3, '2026-10-15', '2026-11-15', '2026-07-17 14:29:29'),
(4, 'Flash Sale', '24-hour deals you cannot miss', NULL, NULL, 'fas fa-tag', NULL, NULL, NULL, 'mobile-deals.jpg', 'deals.php', 'offer', 'carousel', 1, 4, '2026-07-15', '2026-07-16', '2026-07-17 14:29:29'),
(5, 'Fashion Week', 'Trendy styles for every occasion', '', '', 'fas fa-tag', '', 'fas fa-arrow-right', 'linear-gradient(135deg, #F472B6 0%, #DB2777 50%, #FB7185 100%)', 'shoes-sale.jpg', 'products.php?category=2', 'home', 'home', 1, 5, '2026-07-01', '2026-09-30', '2026-07-17 14:29:29'),
(6, 'Up to 50% OFF', 'Premium Shoes', 'Step into style with massive discounts on top brands. Limited time only!', 'Shoes Sale', 'fas fa-tag', 'Shop Shoes', 'fas fa-shoe-prints', 'linear-gradient(135deg, #F472B6 0%, #DB2777 50%, #FB7185 100%)', 'shoes-sale.jpg', 'products.php?category=shoes', 'offer', 'carousel', 1, 1, NULL, NULL, '2026-07-21 18:10:06'),
(7, '30% OFF on Mobiles', 'Smartphones Festival', 'iPhone, Samsung, OnePlus and more at unbeatable prices. Exchange offers available!', 'Mobile Festival', 'fas fa-tag', 'Shop Mobiles', 'fas fa-mobile-alt', 'linear-gradient(135deg, #E879F9 0%, #7C3AED 50%, #A855F7 100%)', 'mobile-deals.jpg', 'products.php?category=electronics', 'offer', 'carousel', 1, 2, NULL, NULL, '2026-07-21 18:10:06'),
(8, 'Flat 20% OFF', 'Laptops and Accessories', 'MacBook, Dell, HP and Lenovo laptops with extra bank discounts.', 'Laptop Deals', 'fas fa-tag', 'Shop Laptops', 'fas fa-laptop', 'linear-gradient(135deg, #FB7185 0%, #FBBF24 50%, #F97316 100%)', 'laptop-deals.jpg', 'products.php?category=electronics', 'offer', 'carousel', 1, 3, NULL, NULL, '2026-07-21 18:10:06'),
(9, 'Buy 1 Get 1', 'FREE Festival', 'Mix and match across categories. The more you shop, the more you save!', 'Buy 1 Get 1', 'fas fa-tag', 'Explore Deals', 'fas fa-gift', 'linear-gradient(135deg, #34D399 0%, #10B981 50%, #059669 100%)', 'buy1get1-free.jpg', 'products.php', 'offer', 'carousel', 1, 4, NULL, NULL, '2026-07-21 18:10:06'),
(10, 'Free Shipping', 'Orders Above 999', 'No minimum order required for premium members. Fast 2-day delivery!', 'Free Shipping', 'fas fa-tag', 'Start Shopping', 'fas fa-shopping-bag', 'linear-gradient(135deg, #60A5FA 0%, #3B82F6 50%, #2563EB 100%)', 'free-shipping.jpg', 'products.php', 'offer', 'carousel', 1, 5, NULL, NULL, '2026-07-21 18:10:06'),
(11, 'Monsoon Mega Sale', 'Up to 60% OFF', 'Stay dry in style! Umbrellas, raincoats, boots and more at unbeatable monsoon prices.', 'Monsoon Offer', 'fas fa-tag', 'Shop Monsoon', 'fas fa-cloud-rain', 'linear-gradient(135deg, #0EA5E9 0%, #6366F1 50%, #8B5CF6 100%)', 'img_6a5fbed2635af3.64299110.jpg', 'products.php', 'offer', 'carousel', 1, 6, '2026-07-22', '2026-07-31', '2026-07-21 18:10:06');

-- --------------------------------------------------------

--
-- Table structure for table `brands`
--

CREATE TABLE `brands` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(150) NOT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `brands`
--

INSERT INTO `brands` (`id`, `name`, `slug`, `logo`, `description`, `status`, `created_at`) VALUES
(1, 'Samsung', 'samsung', NULL, 'Global leader in electronics', 1, '2026-07-17 14:29:28'),
(2, 'Apple', 'apple', NULL, 'Innovation and premium quality', 1, '2026-07-17 14:29:28'),
(3, 'Nike', 'nike', NULL, 'Just Do It - World leader in sports', 1, '2026-07-17 14:29:28'),
(4, 'Sony', 'sony', NULL, 'Electronics and entertainment', 1, '2026-07-17 14:29:28'),
(5, 'HP', 'hp', NULL, 'Computing and printing solutions', 1, '2026-07-17 14:29:28'),
(6, 'Lenovo', 'lenovo', NULL, 'Think different, build different', 1, '2026-07-17 14:29:28'),
(7, 'OnePlus', 'oneplus', NULL, 'Never Settle - Flagship killer', 1, '2026-07-17 14:29:28'),
(8, 'Adidas', 'adidas', NULL, 'Impossible is Nothing', 1, '2026-07-17 14:29:28'),
(9, 'Google', 'google', NULL, NULL, 1, '2026-07-25 19:04:32'),
(10, 'Xiaomi', 'xiaomi', NULL, NULL, 1, '2026-07-25 19:04:32'),
(11, 'Vivo', 'vivo', NULL, NULL, 1, '2026-07-25 19:04:32'),
(12, 'Motorola', 'motorola', NULL, NULL, 1, '2026-07-25 19:04:32'),
(13, 'Nothing', 'nothing', NULL, NULL, 1, '2026-07-25 19:04:32'),
(14, 'Dell', 'dell', NULL, NULL, 1, '2026-07-25 19:04:32'),
(15, 'ASUS', 'asus', NULL, NULL, 1, '2026-07-25 19:04:32'),
(16, 'Acer', 'acer', NULL, NULL, 1, '2026-07-25 19:04:32'),
(17, 'MSI', 'msi', NULL, NULL, 1, '2026-07-25 19:04:32'),
(18, 'JBL', 'jbl', NULL, NULL, 1, '2026-07-25 19:04:32'),
(19, 'boAt', 'boat', NULL, NULL, 1, '2026-07-25 19:04:32'),
(20, 'Jabra', 'jabra', NULL, NULL, 1, '2026-07-25 19:04:32'),
(21, 'Sennheiser', 'sennheiser', NULL, NULL, 1, '2026-07-25 19:04:32'),
(22, 'Realme', 'realme', NULL, NULL, 1, '2026-07-25 19:04:32'),
(23, 'Skullcandy', 'skullcandy', NULL, NULL, 1, '2026-07-25 19:04:32'),
(24, 'Marshall', 'marshall', NULL, NULL, 1, '2026-07-25 19:04:32'),
(25, 'Levi\'s', 'levi-s', NULL, NULL, 1, '2026-07-25 19:04:32'),
(26, 'Allen Solly', 'allen-solly', NULL, NULL, 1, '2026-07-25 19:04:32'),
(27, 'Puma', 'puma', NULL, NULL, 1, '2026-07-25 19:04:32'),
(28, 'Jack & Jones', 'jack-jones', NULL, NULL, 1, '2026-07-25 19:04:32'),
(29, 'Van Heusen', 'van-heusen', NULL, NULL, 1, '2026-07-25 19:04:32'),
(30, 'US Polo Assn.', 'us-polo-assn-', NULL, NULL, 1, '2026-07-25 19:04:32'),
(31, 'Wrangler', 'wrangler', NULL, NULL, 1, '2026-07-25 19:04:32'),
(32, 'Roadster', 'roadster', NULL, NULL, 1, '2026-07-25 19:04:32'),
(33, 'Pepe Jeans', 'pepe-jeans', NULL, NULL, 1, '2026-07-25 19:04:32'),
(34, 'H&M', 'h-m', NULL, NULL, 1, '2026-07-25 19:04:32'),
(35, 'Zara', 'zara', NULL, NULL, 1, '2026-07-25 19:04:32'),
(36, 'Saree Mall', 'saree-mall', NULL, NULL, 1, '2026-07-25 19:04:32'),
(37, 'W', 'w', NULL, NULL, 1, '2026-07-25 19:04:32'),
(38, 'Forever 21', 'forever-21', NULL, NULL, 1, '2026-07-25 19:04:32'),
(39, 'Mango', 'mango', NULL, NULL, 1, '2026-07-25 19:04:32'),
(40, 'Biba', 'biba', NULL, NULL, 1, '2026-07-25 19:04:32'),
(41, 'Marks & Spencer', 'marks-spencer', NULL, NULL, 1, '2026-07-25 19:04:32'),
(42, 'Accessorize', 'accessorize', NULL, NULL, 1, '2026-07-25 19:04:32'),
(43, 'Woodland', 'woodland', NULL, NULL, 1, '2026-07-25 19:04:32'),
(44, 'Bata', 'bata', NULL, NULL, 1, '2026-07-25 19:04:32'),
(45, 'Skechers', 'skechers', NULL, NULL, 1, '2026-07-25 19:04:32'),
(46, 'New Balance', 'new-balance', NULL, NULL, 1, '2026-07-25 19:04:32'),
(47, 'Crocs', 'crocs', NULL, NULL, 1, '2026-07-25 19:04:32'),
(48, 'Clarks', 'clarks', NULL, NULL, 1, '2026-07-25 19:04:32'),
(49, 'Reebok', 'reebok', NULL, NULL, 1, '2026-07-25 19:04:32'),
(50, 'ASICS', 'asics', NULL, NULL, 1, '2026-07-25 19:04:32'),
(51, 'Prestige', 'prestige', NULL, NULL, 1, '2026-07-25 19:04:32'),
(52, 'Butterfly', 'butterfly', NULL, NULL, 1, '2026-07-25 19:04:32'),
(53, 'Bajaj', 'bajaj', NULL, NULL, 1, '2026-07-25 19:04:32'),
(54, 'Hawkins', 'hawkins', NULL, NULL, 1, '2026-07-25 19:04:32'),
(55, 'Milton', 'milton', NULL, NULL, 1, '2026-07-25 19:04:32'),
(56, 'Borosil', 'borosil', NULL, NULL, 1, '2026-07-25 19:04:32'),
(57, 'Havells', 'havells', NULL, NULL, 1, '2026-07-25 19:04:32'),
(58, 'Philips', 'philips', NULL, NULL, 1, '2026-07-25 19:04:32'),
(60, 'Decathlon', 'decathlon', NULL, NULL, 1, '2026-07-25 19:04:32'),
(61, 'Kobo', 'kobo', NULL, NULL, 1, '2026-07-25 19:04:32'),
(62, 'Boldfit', 'boldfit', NULL, NULL, 1, '2026-07-25 19:04:32'),
(63, 'Lifelong', 'lifelong', NULL, NULL, 1, '2026-07-25 19:04:32'),
(64, 'Strauss', 'strauss', NULL, NULL, 1, '2026-07-25 19:04:32'),
(65, 'Nivia', 'nivia', NULL, NULL, 1, '2026-07-25 19:04:32'),
(66, 'Yonex', 'yonex', NULL, NULL, 1, '2026-07-25 19:04:32'),
(67, 'Cosco', 'cosco', NULL, NULL, 1, '2026-07-25 19:04:32'),
(68, 'PowerNet', 'powernet', NULL, NULL, 1, '2026-07-25 19:04:32'),
(69, 'Lakme', 'lakme', NULL, NULL, 1, '2026-07-25 19:04:32'),
(70, 'Maybelline', 'maybelline', NULL, NULL, 1, '2026-07-25 19:04:32'),
(71, 'Forest Essentials', 'forest-essentials', NULL, NULL, 1, '2026-07-25 19:04:32'),
(72, 'Mamaearth', 'mamaearth', NULL, NULL, 1, '2026-07-25 19:04:32'),
(73, 'The Man Company', 'the-man-company', NULL, NULL, 1, '2026-07-25 19:04:32'),
(74, 'L\'Oreal Paris', 'l-oreal-paris', NULL, NULL, 1, '2026-07-25 19:04:32'),
(75, 'Nykaa', 'nykaa', NULL, NULL, 1, '2026-07-25 19:04:32'),
(76, 'Biotique', 'biotique', NULL, NULL, 1, '2026-07-25 19:04:32'),
(77, 'WOW Skin Science', 'wow-skin-science', NULL, NULL, 1, '2026-07-25 19:04:32');

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED DEFAULT NULL,
  `session_id` varchar(255) DEFAULT NULL,
  `product_id` int(10) UNSIGNED NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cart`
--

INSERT INTO `cart` (`id`, `user_id`, `session_id`, `product_id`, `quantity`, `created_at`, `updated_at`) VALUES
(98, 8, NULL, 2, 1, '2026-07-29 09:56:35', '2026-07-29 09:56:35');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `parent_id` int(10) UNSIGNED DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `slug`, `description`, `image`, `parent_id`, `status`, `sort_order`, `created_at`) VALUES
(1, 'Electronics', 'electronics', 'Latest gadgets and electronic devices', NULL, NULL, 1, 1, '2026-07-17 14:29:28'),
(2, 'Fashion', 'fashion', 'Trendy clothing and accessories', NULL, NULL, 1, 2, '2026-07-17 14:29:28'),
(3, 'Home & Garden', 'home-garden', 'Home improvement and garden essentials', NULL, NULL, 1, 3, '2026-07-17 14:29:28'),
(4, 'Sports', 'sports', 'Sports equipment and fitness gear', NULL, NULL, 1, 4, '2026-07-17 14:29:28'),
(5, 'Books', 'books', 'Books across all genres', NULL, NULL, 1, 5, '2026-07-17 14:29:28'),
(6, 'Beauty', 'beauty', 'Beauty and personal care products', NULL, NULL, 1, 6, '2026-07-17 14:29:28'),
(7, 'Mobile Phones', 'mobile-phones', 'Smartphones and accessories', NULL, 1, 1, 1, '2026-07-17 14:29:28'),
(8, 'Laptops', 'laptops', 'Laptops and notebooks', NULL, 1, 1, 2, '2026-07-17 14:29:28'),
(9, 'Headphones', 'headphones', 'Audio devices and headphones', NULL, 1, 1, 3, '2026-07-17 14:29:28'),
(10, 'Men Clothing', 'men-clothing', 'Clothing for men', NULL, 2, 1, 1, '2026-07-17 14:29:28'),
(11, 'Women Clothing', 'women-clothing', 'Clothing for women', NULL, 2, 1, 2, '2026-07-17 14:29:28'),
(12, 'Footwear', 'footwear', 'Shoes and sandals', NULL, 2, 1, 3, '2026-07-17 14:29:28'),
(13, 'Kitchen', 'kitchen', 'Kitchen appliances and essentials', NULL, 3, 1, 1, '2026-07-17 14:29:28'),
(14, 'Fitness', 'fitness', 'Fitness and gym equipment', NULL, 4, 1, 1, '2026-07-17 14:29:28'),
(15, 'Fiction', 'fiction', 'Fiction books', NULL, 5, 1, 1, '2026-07-17 14:29:28');

-- --------------------------------------------------------

--
-- Table structure for table `coupons`
--

CREATE TABLE `coupons` (
  `id` int(10) UNSIGNED NOT NULL,
  `code` varchar(50) NOT NULL,
  `type` enum('percentage','flat') NOT NULL DEFAULT 'percentage',
  `value` decimal(12,2) NOT NULL DEFAULT 0.00,
  `min_order` decimal(12,2) NOT NULL DEFAULT 0.00,
  `max_discount` decimal(12,2) DEFAULT NULL,
  `usage_limit` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `used_count` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `expiry_date` date DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `coupons`
--

INSERT INTO `coupons` (`id`, `code`, `type`, `value`, `min_order`, `max_discount`, `usage_limit`, `used_count`, `expiry_date`, `status`, `created_at`) VALUES
(1, 'WELCOME20', 'percentage', 20.00, 499.00, 500.00, 100, 25, '2026-12-31', 1, '2026-07-17 14:29:29'),
(2, 'SAVE500', 'flat', 500.00, 2499.00, 500.00, 50, 12, '2026-09-30', 1, '2026-07-17 14:29:29'),
(3, 'FREESHIP', 'flat', 49.00, 0.00, 49.00, 200, 88, '2026-12-31', 1, '2026-07-17 14:29:29'),
(4, 'FESTIVE15', 'percentage', 15.00, 999.00, 1000.00, 75, 34, '2026-08-31', 1, '2026-07-17 14:29:29'),
(5, 'SUMMER25', 'percentage', 25.00, 1499.00, 750.00, 100, 5, '2026-07-31', 1, '2026-07-17 14:29:29'),
(6, 'FLASH50', 'flat', 50.00, 299.00, 50.00, 150, 67, '2026-08-15', 1, '2026-07-17 14:29:29');

-- --------------------------------------------------------

--
-- Table structure for table `flight_bookings`
--

CREATE TABLE `flight_bookings` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `booking_ref` varchar(20) NOT NULL,
  `from_city` varchar(100) NOT NULL,
  `to_city` varchar(100) NOT NULL,
  `departure_date` date NOT NULL,
  `return_date` date DEFAULT NULL,
  `trip_type` enum('oneway','roundtrip') NOT NULL DEFAULT 'oneway',
  `travelers` int(10) UNSIGNED NOT NULL DEFAULT 1,
  `travel_class` enum('economy','premium_economy','business','first') NOT NULL DEFAULT 'economy',
  `passenger_name` varchar(150) NOT NULL,
  `passenger_email` varchar(150) NOT NULL,
  `passenger_phone` varchar(20) DEFAULT NULL,
  `total_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `payment_method` varchar(50) DEFAULT NULL,
  `payment_status` enum('pending','paid','failed','refunded') NOT NULL DEFAULT 'pending',
  `razorpay_order_id` varchar(100) DEFAULT NULL,
  `razorpay_payment_id` varchar(100) DEFAULT NULL,
  `ticket_emailed` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `type` enum('info','warning','success') NOT NULL DEFAULT 'info',
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `link` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `title`, `message`, `type`, `is_read`, `link`, `created_at`) VALUES
(1, NULL, 'Summer Sale Started', 'Our biggest summer sale is live with up to 50% off on all categories!', 'info', 0, 'deals.php', '2026-07-17 14:29:29'),
(2, NULL, 'New iPhone Launched', 'iPhone 16 Pro Max is now available for pre-order.', 'success', 0, 'product.php?id=2', '2026-07-17 14:29:29'),
(13, 8, 'Order Placed', 'Your order has been placed successfully!', 'success', 0, NULL, '2026-07-25 14:57:43'),
(14, 8, 'Order Placed', 'Your order has been placed successfully!', 'success', 0, NULL, '2026-07-28 16:18:39');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `order_number` varchar(50) NOT NULL,
  `subtotal` decimal(12,2) NOT NULL DEFAULT 0.00,
  `discount_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `coupon_code` varchar(50) DEFAULT NULL,
  `shipping_charge` decimal(12,2) NOT NULL DEFAULT 0.00,
  `total_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `payment_method` varchar(50) DEFAULT NULL,
  `payment_status` enum('pending','paid','failed','refunded') NOT NULL DEFAULT 'pending',
  `order_status` enum('pending','confirmed','processing','shipped','delivered','cancelled') NOT NULL DEFAULT 'pending',
  `shipping_name` varchar(100) NOT NULL,
  `shipping_email` varchar(100) DEFAULT NULL,
  `shipping_phone` varchar(20) NOT NULL,
  `shipping_address` text NOT NULL,
  `shipping_city` varchar(100) NOT NULL,
  `shipping_state` varchar(100) NOT NULL,
  `shipping_pincode` varchar(10) NOT NULL,
  `tracking_number` varchar(100) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `order_number`, `subtotal`, `discount_amount`, `coupon_code`, `shipping_charge`, `total_amount`, `payment_method`, `payment_status`, `order_status`, `shipping_name`, `shipping_email`, `shipping_phone`, `shipping_address`, `shipping_city`, `shipping_state`, `shipping_pincode`, `tracking_number`, `notes`, `created_at`, `updated_at`) VALUES
(1, 8, 'ORD-20260719-8D6F', 109999.00, 500.00, 'WELCOME20', 0.00, 109499.00, 'cod', 'paid', 'delivered', 'rahul veer', 'rahulveer12@gmail.com', '9822396767', 'tarwadi, phursungi', 'pune', 'Maharashtra', '412308', '123456789098', '', '2026-07-19 11:25:04', '2026-07-19 11:44:31'),
(2, 8, 'ORD-20260719-2E00', 109999.00, 0.00, NULL, 0.00, 109999.00, 'cod', 'pending', 'delivered', 'rahul veer', 'rahulveer12@gmail.com', '9822396767', 'tarwadi, phursungi', 'pune', 'Maharashtra', '412308', NULL, '', '2026-07-19 12:36:52', '2026-07-22 08:21:49'),
(3, 8, 'ORD-20260719-C241', 109999.00, 500.00, 'WELCOME20', 0.00, 109499.00, 'cod', 'paid', 'delivered', 'rahul veer', 'rahulveer12@gmail.com', '9822396767', 'tarwadi, phursungi', 'pune', 'Maharashtra', '412308', '1223546087965949', '', '2026-07-19 14:42:47', '2026-07-19 14:44:49'),
(4, 8, 'ORD-20260720-1794', 79999.00, 500.00, 'SAVE500', 0.00, 79499.00, 'cod', 'failed', 'processing', 'rahul veer', 'rahulveer12@gmail.com', '9822396767', 'tarwadi, phursungi', 'pune', 'Maharashtra', '412308', '4454424464', '', '2026-07-20 10:26:19', '2026-07-20 10:27:05'),
(5, 8, 'ORD-20260720-ECF6', 2395.00, 479.00, 'WELCOME20', 0.00, 1916.00, 'cod', 'paid', 'delivered', 'rahul veer', 'rahulveer12@gmail.com', '9822396767', 'tarwadi, phursungi', 'pune', 'Maharashtra', '412308', '87553212457', '', '2026-07-20 10:51:34', '2026-07-20 10:52:38'),
(6, 8, 'ORD-20260720-3AC6', 22999.00, 500.00, 'WELCOME20', 0.00, 22499.00, 'cod', 'paid', 'delivered', 'rahul veer', 'rahulveer12@gmail.com', '9822396767', 'tarwadi, phursungi', 'pune', 'Maharashtra', '412308', '355676846576707', '', '2026-07-20 11:03:25', '2026-07-20 11:04:16'),
(7, 8, 'ORD-20260722-FCE7', 259899.00, 0.00, NULL, 0.00, 259899.00, 'cod', 'pending', 'pending', 'rahul veer', 'rahulveer12@gmail.com', '9822396767', 'tarwadi, phursungi', 'pune', 'Maharashtra', '412308', NULL, '', '2026-07-22 08:54:58', '2026-07-22 08:54:58'),
(8, 8, 'ORD-20260723-13D5', 89999.00, 0.00, NULL, 0.00, 89999.00, 'cod', 'pending', 'pending', 'rahul veer', 'rahulveer12@gmail.com', '9822396767', 'tarwadi, phursungi', 'pune', 'Maharashtra', '412308', NULL, '', '2026-07-23 09:22:10', '2026-07-23 09:22:10'),
(9, 8, 'ORD-20260725-F37B', 22999.00, 750.00, 'SUMMER25', 0.00, 22249.00, 'cod', 'pending', 'processing', 'rahul veer', 'rahulveer12@gmail.com', '9822396767', 'tarwadi, phursungi', 'pune', 'Maharashtra', '412308', NULL, '', '2026-07-25 14:57:43', '2026-07-25 14:58:16'),
(10, 8, 'ORD-20260728-1157', 109999.00, 0.00, NULL, 0.00, 109999.00, 'cod', 'pending', 'pending', 'rahul veer', 'rahulveer12@gmail.com', '9822396767', 'tarwadi, phursungi', 'pune', 'Maharashtra', '412308', NULL, '', '2026-07-28 16:18:39', '2026-07-28 16:18:39');

--
-- Triggers `orders`
--
DELIMITER $$
CREATE TRIGGER `trg_orders_after_update_cancel` AFTER UPDATE ON `orders` FOR EACH ROW BEGIN
    IF NEW.order_status = 'cancelled' AND OLD.order_status != 'cancelled' THEN
        UPDATE products p
        JOIN order_items oi ON p.id = oi.product_id
        SET p.quantity = p.quantity + oi.quantity
        WHERE oi.order_id = NEW.id;
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_orders_before_insert` BEFORE INSERT ON `orders` FOR EACH ROW BEGIN
    IF NEW.order_number IS NULL OR NEW.order_number = '' THEN
        SET NEW.order_number = CONCAT('ORD-', DATE_FORMAT(NOW(), '%Y%m%d'), '-', LPAD(FLOOR(RAND()*899999+100000), 6, '0'));
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(10) UNSIGNED NOT NULL,
  `order_id` int(10) UNSIGNED NOT NULL,
  `product_id` int(10) UNSIGNED DEFAULT NULL,
  `product_name` varchar(255) NOT NULL,
  `product_price` decimal(12,2) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `total` decimal(12,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `product_name`, `product_price`, `quantity`, `total`, `created_at`) VALUES
(1, 1, 1, 'Samsung Galaxy S25 Ultra', 109999.00, 1, 109999.00, '2026-07-19 11:25:04'),
(2, 2, 1, 'Samsung Galaxy S25 Ultra', 109999.00, 1, 109999.00, '2026-07-19 12:36:52'),
(3, 3, 1, 'Samsung Galaxy S25 Ultra', 109999.00, 1, 109999.00, '2026-07-19 14:42:47'),
(4, 4, 3, 'OnePlus 13 Pro', 79999.00, 1, 79999.00, '2026-07-20 10:26:19'),
(5, 5, 14, 'Nike Dri-FIT T-Shirt', 2395.00, 1, 2395.00, '2026-07-20 10:51:34'),
(6, 6, 9, 'Sony WH-1000XM6', 22999.00, 1, 22999.00, '2026-07-20 11:03:25'),
(7, 7, 1, 'Samsung Galaxy S25 Ultra', 109999.00, 1, 109999.00, '2026-07-22 08:54:58'),
(8, 7, 2, 'iPhone 16 Pro Max', 149900.00, 1, 149900.00, '2026-07-22 08:54:58'),
(9, 8, 17, 'Samsung 55-inch OLED TV', 89999.00, 1, 89999.00, '2026-07-23 09:22:10'),
(10, 9, 9, 'Sony WH-1000XM6', 22999.00, 1, 22999.00, '2026-07-25 14:57:43'),
(11, 10, 1, 'Samsung Galaxy S25 Ultra', 109999.00, 1, 109999.00, '2026-07-28 16:18:39');

--
-- Triggers `order_items`
--
DELIMITER $$
CREATE TRIGGER `trg_order_items_after_insert` AFTER INSERT ON `order_items` FOR EACH ROW BEGIN
    UPDATE products SET quantity = quantity - NEW.quantity
    WHERE id = NEW.product_id AND quantity >= NEW.quantity;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` int(10) UNSIGNED NOT NULL,
  `order_id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `method` varchar(50) NOT NULL,
  `transaction_id` varchar(255) DEFAULT NULL,
  `amount` decimal(12,2) NOT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'pending',
  `gateway_response` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`gateway_response`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `short_description` varchar(500) DEFAULT NULL,
  `price` decimal(12,2) NOT NULL DEFAULT 0.00,
  `discount_price` decimal(12,2) DEFAULT NULL,
  `discount_percent` int(11) DEFAULT NULL,
  `category_id` int(10) UNSIGNED DEFAULT NULL,
  `brand_id` int(10) UNSIGNED DEFAULT NULL,
  `quantity` int(11) NOT NULL DEFAULT 0,
  `sku` varchar(100) DEFAULT NULL,
  `rating` decimal(3,2) NOT NULL DEFAULT 0.00,
  `reviews_count` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `is_featured` tinyint(1) NOT NULL DEFAULT 0,
  `is_trending` tinyint(1) NOT NULL DEFAULT 0,
  `is_bestseller` tinyint(1) NOT NULL DEFAULT 0,
  `is_flash_sale` tinyint(1) NOT NULL DEFAULT 0,
  `flash_sale_price` decimal(12,2) DEFAULT NULL,
  `flash_sale_end` datetime DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `meta_title` varchar(255) DEFAULT NULL,
  `meta_description` varchar(500) DEFAULT NULL,
  `image_url` varchar(500) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `slug`, `description`, `short_description`, `price`, `discount_price`, `discount_percent`, `category_id`, `brand_id`, `quantity`, `sku`, `rating`, `reviews_count`, `is_featured`, `is_trending`, `is_bestseller`, `is_flash_sale`, `flash_sale_price`, `flash_sale_end`, `status`, `meta_title`, `meta_description`, `image_url`, `created_at`, `updated_at`) VALUES
(1, 'Samsung Galaxy S25 Ultra', 'samsung-galaxy-s25-ultra', 'Latest Samsung flagship with AI-powered camera, S Pen support, and stunning display.', '12GB RAM, 256GB Storage, 200MP Camera', 124999.00, 109999.00, 12, 7, 1, 45, 'SAM-S25U-256', 4.50, 2, 1, 1, 1, 1, 99999.00, '2026-08-15 23:59:59', 1, NULL, NULL, 'https://placehold.co/400x400?text=Product', '2026-07-17 14:29:28', '2026-07-28 16:18:39'),
(2, 'iPhone 16 Pro Max', 'iphone-16-pro-max', 'Apple iPhone 16 Pro Max with A18 Bionic chip, titanium design, and pro camera system.', '256GB, Titanium Design, A18 Chip', 159900.00, 149900.00, 6, 7, 2, 34, 'APL-IP16PM-256', 5.00, 2, 1, 1, 1, 1, 139999.00, '2026-08-10 23:59:59', 1, NULL, NULL, 'https://placehold.co/400x400?text=Product', '2026-07-17 14:29:28', '2026-07-27 16:35:02'),
(3, 'OnePlus 13 Pro', 'oneplus-13-pro', 'Flagship killer with Snapdragon 8 Gen 4, Hasselblad cameras, and 100W charging.', '16GB RAM, 512GB, Hasselblad Camera', 89999.00, 79999.00, 11, 7, 7, 59, 'OPL-13P-512', 4.50, 178, 1, 1, 0, 0, NULL, NULL, 1, NULL, NULL, 'https://placehold.co/400x400?text=Product', '2026-07-17 14:29:28', '2026-07-27 16:35:02'),
(4, 'Samsung Galaxy A55', 'samsung-galaxy-a55', 'Mid-range powerhouse with AMOLED display and 50MP camera.', '8GB RAM, 128GB, 5000mAh Battery', 34999.00, 29999.00, 14, 7, 1, 80, 'SAM-A55-128', 4.30, 89, 0, 0, 1, 0, NULL, NULL, 1, NULL, NULL, 'https://placehold.co/400x400?text=Product', '2026-07-17 14:29:28', '2026-07-27 16:35:02'),
(5, 'HP Pavilion 15', 'hp-pavilion-15', 'Powerful laptop for work and play with Intel i7 and RTX 3050.', 'Intel i7, 16GB RAM, 512GB SSD, RTX 3050', 78999.00, 69999.00, 11, 8, 5, 25, 'HP-PAV15-I7', 4.40, 134, 1, 1, 0, 0, NULL, NULL, 1, NULL, NULL, 'https://placehold.co/400x400?text=Product', '2026-07-17 14:29:28', '2026-07-27 16:35:02'),
(6, 'Lenovo ThinkPad X1 Carbon', 'lenovo-thinkpad-x1-carbon', 'Ultra-light business laptop with 14-inch 2K display and Intel i7.', 'Intel i7, 16GB, 512GB SSD, 2K Display', 129999.00, 114999.00, 12, 8, 6, 20, 'LEN-TPX1C-16', 4.60, 98, 1, 0, 1, 0, NULL, NULL, 1, NULL, NULL, 'https://placehold.co/400x400?text=Product', '2026-07-17 14:29:28', '2026-07-27 16:35:02'),
(7, 'Apple MacBook Air M3', 'apple-macbook-air-m3', 'Ultra-thin laptop with Apple M3 chip, 15-inch Liquid Retina display.', 'M3, 8GB, 256GB SSD, 15-inch', 124900.00, 114900.00, 8, 8, 2, 40, 'APL-MBA-M3-256', 4.00, 1, 1, 1, 1, 1, 109900.00, '2026-07-31 23:59:59', 1, NULL, NULL, 'https://placehold.co/400x400?text=Product', '2026-07-17 14:29:28', '2026-07-27 16:35:02'),
(8, 'HP Victus Gaming', 'hp-victus-gaming', 'Gaming laptop with AMD Ryzen 7 and RTX 4060.', 'Ryzen 7, 16GB, 1TB SSD, RTX 4060, 144Hz', 89999.00, 79999.00, 11, 8, 5, 30, 'HP-VIC-R7-1T', 4.20, 67, 0, 1, 0, 0, NULL, NULL, 1, NULL, NULL, 'https://placehold.co/400x400?text=Product', '2026-07-17 14:29:28', '2026-07-27 16:35:02'),
(9, 'Sony WH-1000XM6', 'sony-wh-1000xm6', 'Industry-leading noise cancellation with 40-hour battery life.', 'Wireless ANC, 40hr Battery, Hi-Res Audio', 25999.00, 22999.00, 12, 9, 4, 43, 'SNY-WH1000XM6', 5.00, 1, 1, 1, 1, 1, 19999.00, '2026-07-25 23:59:59', 1, NULL, NULL, 'https://placehold.co/400x400?text=Product', '2026-07-17 14:29:28', '2026-07-27 16:35:02'),
(10, 'Samsung Galaxy Buds Pro 3', 'samsung-galaxy-buds-pro-3', 'Premium TWS earbuds with 360 Audio and ANC.', 'ANC, 360 Audio, IP57, 29hr Battery', 19999.00, 15999.00, 20, 9, 1, 65, 'SAM-BUDS-P3', 4.40, 156, 0, 1, 0, 0, NULL, NULL, 1, NULL, NULL, 'https://placehold.co/400x400?text=Product', '2026-07-17 14:29:28', '2026-07-27 16:35:02'),
(11, 'Apple AirPods Pro 3', 'apple-airpods-pro-3', 'Adaptive audio with USB-C, spatial audio.', 'USB-C, Spatial Audio, Adaptive ANC', 24900.00, 21900.00, 12, 9, 2, 55, 'APL-AIRP-P3', 4.60, 389, 1, 0, 1, 1, 19900.00, '2026-08-05 23:59:59', 1, NULL, NULL, 'https://placehold.co/400x400?text=Product', '2026-07-17 14:29:28', '2026-07-27 16:35:02'),
(12, 'Nike Air Jordan Retro', 'nike-air-jordan-retro', 'Iconic basketball sneakers with premium leather and Air cushioning.', 'Leather, Air Sole, High-top Design', 18995.00, 15995.00, 16, 12, 3, 40, 'NK-AJR-2025', 4.00, 1, 1, 1, 1, 0, NULL, NULL, 1, NULL, NULL, 'https://placehold.co/400x400?text=Product', '2026-07-17 14:29:28', '2026-07-27 16:35:02'),
(13, 'Adidas Ultraboost 25', 'adidas-ultraboost-25', 'Ultra-comfortable running shoes with Boost midsole.', 'Boost Midsole, Primeknit Upper', 15999.00, 12999.00, 19, 12, 8, 55, 'ADI-UB25-BLK', 4.60, 198, 1, 1, 0, 1, 11999.00, '2026-07-30 23:59:59', 1, NULL, NULL, 'https://placehold.co/400x400?text=Product', '2026-07-17 14:29:28', '2026-07-27 16:35:02'),
(14, 'Nike Dri-FIT T-Shirt', 'nike-dri-fit-tshirt', 'Performance t-shirt with moisture-wicking technology.', 'Dri-FIT, Breathable, Regular Fit', 2995.00, 2395.00, 20, 10, 3, 119, 'NK-DFT-L', 4.30, 78, 0, 0, 1, 0, NULL, NULL, 1, NULL, NULL, 'https://placehold.co/400x400?text=Product', '2026-07-17 14:29:28', '2026-07-27 16:35:02'),
(15, 'Nike Yoga Luxe Leggings', 'nike-yoga-luxe-leggings', 'Premium high-waist leggings with stretch fabric.', 'High-Waist, Stretch, Moisture-Wicking', 4995.00, 3995.00, 20, 11, 3, 75, 'NK-YLL-M', 4.40, 92, 0, 1, 0, 0, NULL, NULL, 1, NULL, NULL, 'https://placehold.co/400x400?text=Product', '2026-07-17 14:29:28', '2026-07-27 16:35:02'),
(16, 'Adidas Campus 00s', 'adidas-campus-00s', 'Classic sneakers with suede upper and rubber cupsole.', 'Suede, Rubber Sole, Vintage Style', 8999.00, 7499.00, 17, 12, 8, 90, 'ADI-CAMP-00S', 4.20, 145, 0, 0, 1, 0, NULL, NULL, 1, NULL, NULL, 'https://placehold.co/400x400?text=Product', '2026-07-17 14:29:28', '2026-07-27 16:35:02'),
(17, 'Samsung 55-inch OLED TV', 'samsung-55-oled-tv', 'Stunning 55-inch OLED TV with Quantum HDR and Dolby Atmos.', '55-inch OLED, 4K, Dolby Atmos, Smart TV', 109999.00, 89999.00, 18, 1, 1, 19, 'SAM-55OLED-QD', 4.70, 312, 1, 1, 0, 1, 84999.00, '2026-08-20 23:59:59', 1, NULL, NULL, 'https://placehold.co/400x400?text=Product', '2026-07-17 14:29:28', '2026-07-27 16:35:02'),
(18, 'Sony Bravia XR 65-inch', 'sony-bravia-xr-65', 'Cognitive processor XR with stunning 4K HDR.', '65-inch, 4K, XR Processor, Dolby Vision', 149999.00, 129999.00, 13, 1, 4, 15, 'SNY-BVXR-65', 4.60, 201, 1, 0, 1, 0, NULL, NULL, 1, NULL, NULL, 'https://placehold.co/400x400?text=Product', '2026-07-17 14:29:28', '2026-07-27 16:35:02'),
(19, 'Nike Pro Training Set', 'nike-pro-training-set', 'Complete training gear set with resistance bands and mat.', 'Resistance Bands, Mat, Dri-FIT Fabric', 3499.00, 2799.00, 20, 14, 3, 100, 'NK-PRO-TRN', 4.10, 56, 1, 0, 0, 0, NULL, NULL, 1, NULL, NULL, 'https://placehold.co/400x400?text=Product', '2026-07-17 14:29:28', '2026-07-27 16:35:02'),
(20, 'Adidas Football', 'adidas-football', 'Official size 5 match ball with stitched PU cover.', 'Size 5, PU Stitched, FIFA Quality', 4499.00, 3799.00, 16, 4, 8, 85, 'ADI-FTB-S5', 4.30, 34, 0, 1, 0, 0, NULL, NULL, 1, NULL, NULL, 'https://placehold.co/400x400?text=Product', '2026-07-17 14:29:28', '2026-07-27 16:35:02'),
(21, 'Wings of Fire - APJ Abdul Kalam', 'wings-of-fire', 'Autobiography of Dr. APJ Abdul Kalam.', 'Paperback, 180 Pages, Inspirational', 399.00, 299.00, 25, 5, NULL, 200, 'BK-WOF-001', 5.00, 1, 1, 1, 1, 0, NULL, NULL, 1, NULL, NULL, 'https://placehold.co/400x400?text=Product', '2026-07-17 14:29:28', '2026-07-27 16:35:02'),
(22, 'The Alchemist - Paulo Coelho', 'the-alchemist', 'International bestselling novel about following your dreams.', 'Paperback, 208 Pages, Bestseller', 350.00, 259.00, 26, 5, NULL, 180, 'BK-ALC-001', 5.00, 1, 1, 0, 1, 0, NULL, NULL, 1, NULL, NULL, 'https://placehold.co/400x400?text=Product', '2026-07-17 14:29:28', '2026-07-27 16:35:02'),
(23, 'Rich Dad Poor Dad', 'rich-dad-poor-dad', 'Personal finance classic by Robert Kiyosaki.', 'Paperback, 336 Pages, Finance', 499.00, 349.00, 30, 5, NULL, 150, 'BK-RDPD-001', 4.00, 1, 0, 1, 0, 0, NULL, NULL, 1, NULL, NULL, 'https://placehold.co/400x400?text=Product', '2026-07-17 14:29:28', '2026-07-27 16:35:02'),
(24, 'Sony 4K Action Cam', 'sony-4k-action-cam', 'Compact 4K action camera with stabilization.', '4K, Stabilization, Waterproof, 32MP', 29999.00, 25999.00, 13, 1, 4, 35, 'SNY-AC-4K', 4.20, 78, 0, 0, 0, 0, NULL, NULL, 1, NULL, NULL, 'https://placehold.co/400x400?text=Product', '2026-07-17 14:29:28', '2026-07-27 16:35:02'),
(25, 'Samsung Galaxy S24 FE', 'samsung-galaxy-s24-fe', 'Flagship features at an affordable price with Exynos 2400e processor and AMOLED display.', '6.7\" AMOLED, 50MP Camera, 4700mAh', 49999.00, 44999.00, 10, 7, 1, 60, 'SAM-S24FE', 4.50, 189, 1, 1, 0, 0, NULL, NULL, 1, NULL, NULL, 'https://placehold.co/400x400?text=Product', '2026-07-25 19:01:40', '2026-07-27 16:35:02'),
(26, 'OnePlus 12R', 'oneplus-12r', 'Powerful mid-range flagship with Snapdragon 8 Gen 2 and 100W SUPERVOOC charging.', 'Snapdragon 8 Gen 2, 16GB RAM, 100W', 39999.00, 34999.00, 13, 7, 7, 70, 'OPL-12R', 4.40, 234, 1, 0, 0, 0, NULL, NULL, 1, NULL, NULL, 'https://placehold.co/400x400?text=Product', '2026-07-25 19:01:41', '2026-07-27 16:35:02'),
(28, 'Samsung Galaxy Z Flip 6', 'samsung-galaxy-z-flip-6', 'Foldable flip phone with Flex Mode, FlexCam and Snapdragon 8 Gen 3.', '6.7\" Foldable, Flex Mode, 50MP', 109999.00, 99999.00, 9, 7, 1, 25, 'SAM-ZF6', 4.30, 87, 1, 1, 0, 0, NULL, NULL, 1, NULL, NULL, 'https://placehold.co/400x400?text=Product', '2026-07-25 19:01:42', '2026-07-27 16:35:02'),
(32, 'iPhone 15', 'iphone-15', 'Dynamic Island, 48MP camera system, USB-C, and durable Ceramic Shield front.', '48MP Camera, Dynamic Island, USB-C', 79900.00, 69900.00, 13, 7, 2, 45, 'APL-IP15', 4.70, 892, 1, 1, 0, 0, NULL, NULL, 1, NULL, NULL, 'https://placehold.co/400x400?text=Product', '2026-07-25 19:01:46', '2026-07-27 16:35:02'),
(33, 'Samsung Galaxy A35', 'samsung-galaxy-a35', 'Affordable 5G phone with IP67 rating, 50MP camera, and Super AMOLED display.', '5G, IP67, 50MP, Super AMOLED', 23999.00, 21999.00, 8, 7, 1, 100, 'SAM-A35', 4.10, 234, 0, 0, 0, 0, NULL, NULL, 1, NULL, NULL, 'https://placehold.co/400x400?text=Product', '2026-07-25 19:01:47', '2026-07-27 16:35:02'),
(37, 'Apple MacBook Pro 14 M3', 'apple-macbook-pro-14-m3', 'Professional laptop with M3 chip, Liquid Retina XDR display, and up to 18hr battery.', 'M3, 18GB, 512GB SSD, 14\" Liquid Retina XDR', 199900.00, 184900.00, 8, 8, 2, 30, 'APL-MBP14-M3', 4.80, 567, 1, 1, 0, 0, NULL, NULL, 1, NULL, NULL, 'https://placehold.co/400x400?text=Product', '2026-07-25 19:01:48', '2026-07-27 16:35:02'),
(38, 'Lenovo IdeaPad Slim 5', 'lenovo-ideapad-slim-5', 'Affordable slim laptop with Intel i5-1335U and 15.6\" FHD IPS display.', 'i5-1335U, 16GB, 512GB SSD, 15.6\"', 54999.00, 47999.00, 13, 8, 6, 60, 'LEN-IS5-15', 4.20, 289, 0, 1, 0, 0, NULL, NULL, 1, NULL, NULL, 'https://placehold.co/400x400?text=Product', '2026-07-25 19:01:49', '2026-07-27 16:35:02'),
(40, 'HP Envy x360 14', 'hp-envy-x360-14', '2-in-1 convertible laptop with OLED touchscreen and Intel Core Ultra 5.', 'Ultra 5, 16GB, 512GB, 14\" OLED Touch', 89999.00, 79999.00, 11, 8, 5, 30, 'HP-ENVY-X360', 4.40, 178, 1, 0, 0, 0, NULL, NULL, 1, NULL, NULL, 'https://placehold.co/400x400?text=Product', '2026-07-25 19:01:50', '2026-07-27 16:35:02'),
(49, 'OnePlus Buds Pro 2', 'oneplus-buds-pro-2', 'Flagship TWS earbuds with dual drivers, LHDC 5.0 and Smart ANC.', 'Dual Drivers, LHDC 5.0, Smart ANC', 9999.00, 8499.00, 15, 9, 7, 45, 'OPL-BP2', 4.50, 267, 1, 1, 0, 0, NULL, NULL, 1, NULL, NULL, 'https://placehold.co/400x400?text=Product', '2026-07-25 19:01:56', '2026-07-27 16:35:02'),
(71, 'Nike Revolution 7', 'nike-revolution-7', 'Lightweight running shoes with soft foam cushioning for everyday comfort.', 'Soft Foam, Lightweight, Mesh Upper', 3995.00, 3295.00, 18, 12, 3, 80, 'NK-REV7-BLK', 4.30, 567, 1, 1, 0, 0, NULL, NULL, 1, NULL, NULL, 'https://placehold.co/400x400?text=Product', '2026-07-25 19:02:09', '2026-07-27 16:35:02'),
(105, 'Atomic Habits by James Clear', 'atomic-habits-by-james-clear', 'An easy and proven way to build good habits and break bad ones.', 'Paperback, 320 Pages, Self-Help', 599.00, 449.00, 25, 15, NULL, 200, 'BK-ATHAB-001', 4.80, 2345, 1, 1, 0, 0, NULL, NULL, 1, NULL, NULL, 'https://placehold.co/400x400?text=Product', '2026-07-25 19:02:26', '2026-07-27 16:35:02'),
(106, 'The Psychology of Money by Morgan Housel', 'the-psychology-of-money-by-morgan-housel', 'Timeless lessons on wealth, greed, and happiness through fascinating stories.', 'Paperback, 256 Pages, Finance', 399.00, 299.00, 25, 15, NULL, 180, 'BK-PSYMNY-001', 4.70, 1892, 1, 1, 0, 0, NULL, NULL, 1, NULL, NULL, 'https://placehold.co/400x400?text=Product', '2026-07-25 19:02:27', '2026-07-27 16:35:02'),
(107, 'Ikigai by Héctor García', 'ikigai-by-h-ctor-garc-a', 'The Japanese secret to a long and happy life, discovered in the village of Okinawa.', 'Paperback, 208 Pages, Philosophy', 350.00, 249.00, 29, 15, NULL, 150, 'BK-IKIGAI-001', 4.60, 1567, 1, 1, 0, 0, NULL, NULL, 1, NULL, NULL, 'https://placehold.co/400x400?text=Product', '2026-07-25 19:02:28', '2026-07-27 16:35:02'),
(108, 'Sapiens by Yuval Noah Harari', 'sapiens-by-yuval-noah-harari', 'A brief history of humankind exploring how Homo sapiens came to dominate Earth.', 'Paperback, 498 Pages, History', 599.00, 449.00, 25, 15, NULL, 120, 'BK-SAPI-001', 4.70, 1234, 1, 0, 0, 0, NULL, NULL, 1, NULL, NULL, 'https://placehold.co/400x400?text=Product', '2026-07-25 19:02:29', '2026-07-27 16:35:02'),
(109, 'The Power of Your Subconscious Mind by Joseph Murphy', 'the-power-of-your-subconscious-mind-by-joseph-murphy', 'Unlock the dormant power within you to transform your life.', 'Paperback, 304 Pages, Self-Help', 299.00, 199.00, 33, 15, NULL, 200, 'BK-PWRSM-001', 4.50, 987, 0, 1, 0, 0, NULL, NULL, 1, NULL, NULL, 'https://placehold.co/400x400?text=Product', '2026-07-25 19:02:29', '2026-07-27 16:35:02'),
(110, 'Think and Grow Rich by Napoleon Hill', 'think-and-grow-rich-by-napoleon-hill', 'The landmark bestseller on success principles that has changed millions of lives.', 'Paperback, 320 Pages, Business', 350.00, 249.00, 29, 15, NULL, 150, 'BK-TGR-001', 4.50, 876, 0, 0, 0, 0, NULL, NULL, 1, NULL, NULL, 'https://placehold.co/400x400?text=Product', '2026-07-25 19:02:30', '2026-07-27 16:35:02'),
(111, 'Becoming by Michelle Obama', 'becoming-by-michelle-obama', 'An intimate, powerful, and inspiring memoir by the former First Lady of the US.', 'Hardcover, 448 Pages, Memoir', 899.00, 699.00, 22, 15, NULL, 100, 'BK-BECOM-001', 4.70, 1456, 1, 1, 0, 0, NULL, NULL, 1, NULL, NULL, 'https://placehold.co/400x400?text=Product', '2026-07-25 19:02:31', '2026-07-27 16:35:02'),
(112, '1984 by George Orwell', '1984-by-george-orwell', 'The dystopian masterpiece about totalitarianism, surveillance, and the power of truth.', 'Paperback, 328 Pages, Dystopian Fiction', 299.00, 199.00, 33, 15, NULL, 130, 'BK-1984-001', 4.60, 2345, 0, 0, 0, 0, NULL, NULL, 1, NULL, NULL, 'https://placehold.co/400x400?text=Product', '2026-07-25 19:02:31', '2026-07-27 16:35:02'),
(113, 'To Kill a Mockingbird by Harper Lee', 'to-kill-a-mockingbird-by-harper-lee', 'A Pulitzer Prize-winning novel about justice, compassion, and growing up in the South.', 'Paperback, 336 Pages, Classic Fiction', 350.00, 249.00, 29, 15, NULL, 110, 'BK-TKAM-001', 4.70, 1890, 0, 0, 0, 0, NULL, NULL, 1, NULL, NULL, 'https://placehold.co/400x400?text=Product', '2026-07-25 19:02:32', '2026-07-27 16:35:02'),
(114, 'The Subtle Art of Not Giving a F*ck by Mark Manson', 'the-subtle-art-of-not-giving-a-f-ck-by-mark-manson', 'A counterintuitive approach to living a good life by embracing your limitations.', 'Paperback, 224 Pages, Self-Help', 499.00, 349.00, 30, 15, NULL, 140, 'BK-SUBTL-001', 4.40, 1678, 0, 1, 0, 0, NULL, NULL, 1, NULL, NULL, 'https://placehold.co/400x400?text=Product', '2026-07-25 19:02:33', '2026-07-27 16:35:02'),
(125, 'Google Pixel 8', 'google-pixel-8', 'Pure Android experience with Google Tensor G3 and best-in-class camera processing.', 'Tensor G3, 50MP Camera, 7 Years Updates', 75999.00, 64999.00, 14, 7, 9, 35, 'GGL-PX8', 4.60, 312, 1, 1, 0, 0, NULL, NULL, 1, NULL, NULL, 'https://placehold.co/400x400?text=Product', '2026-07-25 19:04:48', '2026-07-27 16:35:02'),
(126, 'Redmi Note 13 Pro+', 'redmi-note-13-pro', 'Best mid-range phone with 200MP camera, 120W HyperCharge, and IP68 rating.', '200MP Camera, 120W Charge, IP68', 32999.00, 29999.00, 9, 7, 10, 90, 'RMI-N13P', 4.30, 456, 0, 1, 0, 0, NULL, NULL, 1, NULL, NULL, 'http://localhost/AI_%20Shopping/assets/uploads/products/prod_6a69d4a0958b6.png', '2026-07-25 19:04:48', '2026-07-29 10:23:30'),
(127, 'Vivo X100', 'vivo-x100', 'Flagship camera phone with ZEISS optics and MediaTek Dimensity 9300.', 'ZEISS Camera, Dimensity 9300, 120W', 69999.00, 59999.00, 14, 7, 11, 40, 'VVO-X100', 4.50, 167, 1, 0, 0, 0, NULL, NULL, 1, NULL, NULL, 'https://placehold.co/400x400?text=Product', '2026-07-25 19:04:48', '2026-07-27 16:35:02'),
(128, 'Motorola Edge 50 Pro', 'motorola-edge-50-pro', 'Slim and stylish with 144Hz pOLED display and 125W TurboPower charging.', '144Hz pOLED, 125W Charge, 50MP', 35999.00, 31999.00, 11, 7, 12, 55, 'MOT-E50P', 4.20, 123, 0, 0, 0, 0, NULL, NULL, 1, NULL, NULL, 'http://localhost/AI_%20Shopping/assets/uploads/products/prod_6a69d49a29b67.png', '2026-07-25 19:04:48', '2026-07-29 10:23:24'),
(129, 'Nothing Phone 2a Plus', 'nothing-phone-2a-plus', 'Unique Glyph interface with MediaTek Dimensity 7350 Pro and clean Nothing OS.', 'Glyph Interface, Dimensity 7350, 50MP', 26999.00, 24999.00, 7, 7, 13, 50, 'NOT-2AP', 4.30, 189, 0, 1, 0, 0, NULL, NULL, 1, NULL, NULL, 'https://placehold.co/400x400?text=Product', '2026-07-25 19:04:48', '2026-07-27 16:35:02'),
(130, 'Dell XPS 15', 'dell-xps-15', 'Premium ultrabook with 15.6\" OLED InfinityEdge display and Intel Core Ultra 7.', 'Intel Core Ultra 7, 16GB, 512GB OLED', 149999.00, 129999.00, 13, 8, 14, 20, 'DEL-XPS15', 4.60, 156, 1, 1, 0, 0, NULL, NULL, 1, NULL, NULL, 'https://placehold.co/400x400?text=Product', '2026-07-25 19:04:48', '2026-07-27 16:35:02'),
(131, 'ASUS ROG Strix G16', 'asus-rog-strix-g16', 'Gaming powerhouse with Intel i9-14900HX and RTX 4070, 240Hz display.', 'i9-14900HX, RTX 4070, 240Hz, 16GB', 159999.00, 139999.00, 13, 8, 15, 25, 'ASU-ROG-G16', 4.70, 234, 1, 1, 0, 0, NULL, NULL, 1, NULL, NULL, 'https://placehold.co/400x400?text=Product', '2026-07-25 19:04:48', '2026-07-27 16:35:02'),
(132, 'Acer Nitro V 15', 'acer-nitro-v-15', 'Budget gaming laptop with i5-13420H and RTX 4050 for entry-level gamers.', 'i5-13420H, RTX 4050, 144Hz, 16GB', 74999.00, 64999.00, 13, 8, 16, 35, 'ACR-NV15', 4.30, 145, 0, 0, 0, 0, NULL, NULL, 1, NULL, NULL, 'https://placehold.co/400x400?text=Product', '2026-07-25 19:04:48', '2026-07-27 16:35:02'),
(133, 'ASUS Vivobook 16', 'asus-vivobook-16', 'Everyday laptop with large 16\" display, AMD Ryzen 7, and NanoEdge bezels.', 'Ryzen 7, 16GB, 512GB, 16\" FHD', 49999.00, 44999.00, 10, 8, 15, 50, 'ASU-VIV-16', 4.10, 213, 0, 0, 0, 0, NULL, NULL, 1, NULL, NULL, 'https://placehold.co/400x400?text=Product', '2026-07-25 19:04:48', '2026-07-27 16:35:02'),
(134, 'MSI Thin 15', 'msi-thin-15', 'Slim gaming laptop with i7-13620H and RTX 4060 at an affordable price.', 'i7-13620H, RTX 4060, 144Hz, 16GB', 84999.00, 74999.00, 12, 8, 17, 30, 'MSI-THN15', 4.30, 112, 0, 1, 0, 0, NULL, NULL, 1, NULL, NULL, 'https://placehold.co/400x400?text=Product', '2026-07-25 19:04:48', '2026-07-27 16:35:02'),
(135, 'JBL Tune 770NC', 'jbl-tune-770nc', 'Over-ear ANC headphones with JBL Pure Bass and 44-hour battery life.', 'ANC, 44hr Battery, JBL Pure Bass', 5999.00, 4999.00, 17, 9, 18, 80, 'JBL-T770', 4.30, 345, 1, 1, 0, 0, NULL, NULL, 1, NULL, NULL, 'https://placehold.co/400x400?text=Product', '2026-07-25 19:04:48', '2026-07-27 16:35:02'),
(136, 'boAt Rockerz 551ANC', 'boat-rockerz-551anc', 'Wireless ANC headphones with ASAP Charge and 50mm drivers for immersive sound.', 'ANC, 50mm Drivers, 50hr Battery', 3999.00, 2999.00, 25, 9, 19, 100, 'BOAT-R551', 4.10, 567, 0, 1, 0, 0, NULL, NULL, 1, NULL, NULL, 'https://placehold.co/400x400?text=Product', '2026-07-25 19:04:48', '2026-07-27 16:35:02'),
(137, 'Jabra Elite 45h', 'jabra-elite-45h', 'Compact on-ear headphones with dual Bluetooth connectivity and 50hr battery.', 'Dual Connect, 50hr Battery, Foldable', 7999.00, 6999.00, 13, 9, 20, 40, 'JAB-E45H', 4.40, 234, 1, 0, 0, 0, NULL, NULL, 1, NULL, NULL, 'https://placehold.co/400x400?text=Product', '2026-07-25 19:04:48', '2026-07-27 16:35:02'),
(138, 'Sennheiser HD 450BT', 'sennheiser-hd-450bt', 'Premium wireless ANC headphones with Sennheiser sound and 30hr battery.', 'Sennheiser Sound, ANC, 30hr Battery', 9999.00, 8499.00, 15, 9, 21, 35, 'SEN-HD450', 4.50, 189, 1, 0, 0, 0, NULL, NULL, 1, NULL, NULL, 'https://placehold.co/400x400?text=Product', '2026-07-25 19:04:48', '2026-07-27 16:35:02'),
(139, 'Realme Buds T300', 'realme-buds-t300', 'True wireless earbuds with 360 Spatial Sound and 40dB ANC at an incredible price.', '360 Spatial Sound, 40dB ANC, 40hr', 2299.00, 1999.00, 13, 9, 22, 120, 'RME-BT300', 4.20, 678, 0, 1, 0, 0, NULL, NULL, 1, NULL, NULL, 'https://placehold.co/400x400?text=Product', '2026-07-25 19:04:48', '2026-07-27 16:35:02'),
(140, 'Skullcandy Crusher ANC 2', 'skullcandy-crusher-anc-2', 'Over-ear headphones with adjustable sensory bass and Personal Sound.', 'Adjustable Bass, ANC, Tile Finding', 12999.00, 10999.00, 15, 9, 23, 25, 'SKC-CR2', 4.30, 112, 0, 0, 0, 0, NULL, NULL, 1, NULL, NULL, 'https://placehold.co/400x400?text=Product', '2026-07-25 19:04:48', '2026-07-27 16:35:02'),
(141, 'Marshall Major IV', 'marshall-major-iv', 'Iconic on-aural wireless headphones with 80+ hours battery and Marshall sound.', '80hr Battery, Wireless Charging, Iconic Design', 14999.00, 12999.00, 13, 9, 24, 30, 'MSHL-MA4', 4.40, 198, 1, 0, 0, 0, NULL, NULL, 1, NULL, NULL, 'https://placehold.co/400x400?text=Product', '2026-07-25 19:04:48', '2026-07-27 16:35:02'),
(142, 'Levi\'s 511 Slim Fit Jeans', 'levi-s-511-slim-fit-jeans', 'Classic slim-fit jeans with stretch for all-day comfort and modern style.', 'Slim Fit, Stretch, 100% Cotton', 3999.00, 2999.00, 25, 10, 25, 100, 'LEV-511-BLU', 4.40, 456, 1, 1, 0, 0, NULL, NULL, 1, NULL, NULL, 'https://placehold.co/400x400?text=Product', '2026-07-25 19:04:48', '2026-07-27 16:35:02'),
(143, 'Allen Solly Men Regular Fit Shirt', 'allen-solly-men-regular-fit-shirt', 'Premium cotton formal shirt with wrinkle-free finish for office and casual wear.', 'Cotton, Wrinkle-Free, Regular Fit', 2499.00, 1799.00, 28, 10, 26, 80, 'ALS-RFS-GRY', 4.20, 312, 0, 1, 0, 0, NULL, NULL, 1, NULL, NULL, 'https://placehold.co/400x400?text=Product', '2026-07-25 19:04:48', '2026-07-27 16:35:02'),
(144, 'Puma Men Training Shorts', 'puma-men-training-shorts', 'DryCELL moisture-wicking training shorts with zip pocket and comfortable fit.', 'DryCELL, Zip Pocket, Lightweight', 1999.00, 1499.00, 25, 10, 27, 90, 'PUMA-TRN-SH', 4.10, 234, 0, 0, 0, 0, NULL, NULL, 1, NULL, NULL, 'https://placehold.co/400x400?text=Product', '2026-07-25 19:04:48', '2026-07-27 16:35:02'),
(145, 'Jack & Jones Denim Jacket', 'jack-jones-denim-jacket', 'Classic denim trucker jacket with vintage wash and premium quality denim.', 'Trucker Style, Vintage Wash, 100% Cotton', 4999.00, 3999.00, 20, 10, 28, 45, 'JJ-DNM-JKT', 4.30, 189, 1, 0, 0, 0, NULL, NULL, 1, NULL, NULL, 'https://placehold.co/400x400?text=Product', '2026-07-25 19:04:48', '2026-07-27 16:35:02'),
(146, 'Van Heusen Men Blazer', 'van-heusen-men-blazer', 'Slim-fit blazer with stretch fabric, perfect for formal events and smart casual look.', 'Slim Fit, Stretch, Formal', 5999.00, 4499.00, 25, 10, 29, 30, 'VH-BLZ-SLM', 4.40, 145, 1, 1, 0, 0, NULL, NULL, 1, NULL, NULL, 'https://placehold.co/400x400?text=Product', '2026-07-25 19:04:48', '2026-07-27 16:35:02'),
(147, 'US Polo Assn. Polo T-Shirt', 'us-polo-assn-polo-t-shirt', 'Classic polo shirt with embroidered double horseman logo and pique knit fabric.', 'Pique Knit, Embroidered Logo, Regular Fit', 2999.00, 2299.00, 23, 10, 30, 75, 'USPA-POLO-WHT', 4.20, 378, 0, 1, 0, 0, NULL, NULL, 1, NULL, NULL, 'https://placehold.co/400x400?text=Product', '2026-07-25 19:04:48', '2026-07-27 16:35:02'),
(148, 'Wrangler Men Cargo Pants', 'wrangler-men-cargo-pants', 'Durable cargo pants with multiple pockets and comfortable relaxed fit.', 'Cargo Style, Multiple Pockets, Relaxed', 3499.00, 2799.00, 20, 10, 31, 60, 'WRG-CRG-KHK', 4.10, 234, 0, 0, 0, 0, NULL, NULL, 1, NULL, NULL, 'https://placehold.co/400x400?text=Product', '2026-07-25 19:04:48', '2026-07-27 16:35:02'),
(149, 'Roadster Men Hoodie', 'roadster-men-hoodie', 'Warm fleece-lined hoodie with kangaroo pocket and adjustable drawstring hood.', 'Fleece-Lined, Kangaroo Pocket, Pullover', 1999.00, 1299.00, 35, 10, 32, 100, 'RDS-HDY-GRY', 4.00, 567, 0, 1, 0, 0, NULL, NULL, 1, NULL, NULL, 'https://placehold.co/400x400?text=Product', '2026-07-25 19:04:48', '2026-07-27 16:35:02'),
(150, 'Pepe Jeans Slim Chinos', 'pepe-jeans-slim-chinos', 'Premium slim-fit chinos with stretch comfort and tapered leg design.', 'Slim Fit, Stretch, Tapered Leg', 3299.00, 2499.00, 24, 10, 33, 55, 'PEP-CHN-SLM', 4.30, 189, 0, 0, 0, 0, NULL, NULL, 1, NULL, NULL, 'https://placehold.co/400x400?text=Product', '2026-07-25 19:04:48', '2026-07-27 16:35:02'),
(151, 'H&M Men Quilted Jacket', 'h-m-men-quilted-jacket', 'Lightweight quilted jacket with water-repellent finish, perfect for layering.', 'Quilted, Water-Repellent, Lightweight', 4999.00, 3499.00, 30, 10, 34, 40, 'HM-QJT-BLK', 4.20, 156, 1, 1, 0, 0, NULL, NULL, 1, NULL, NULL, 'https://placehold.co/400x400?text=Product', '2026-07-25 19:04:48', '2026-07-27 16:35:02'),
(152, 'Zara Women Midi Dress', 'zara-women-midi-dress', 'Elegant midi wrap dress with V-neckline and adjustable belt for a flattering silhouette.', 'Wrap Style, V-Neck, Midi Length', 4999.00, 3999.00, 20, 11, 35, 50, 'ZRA-MIDI-WRP', 4.50, 267, 1, 1, 0, 0, NULL, NULL, 1, NULL, NULL, 'https://placehold.co/400x400?text=Product', '2026-07-25 19:04:48', '2026-07-27 16:35:02'),
(153, 'H&M Oversized Blazer', 'h-m-oversized-blazer', 'Trendy oversized blazer with shoulder pads and single-button closure.', 'Oversized, Shoulder Pads, Single Button', 3999.00, 2999.00, 25, 11, 34, 40, 'HM-OVR-BLZ', 4.30, 198, 1, 1, 0, 0, NULL, NULL, 1, NULL, NULL, 'https://placehold.co/400x400?text=Product', '2026-07-25 19:04:48', '2026-07-27 16:35:02'),
(154, 'Saree Mall Silk Saree', 'saree-mall-silk-saree', 'Beautiful Kanjivaram-style silk saree with gold zari border and blouse piece.', 'Silk, Zari Border, Includes Blouse', 3999.00, 2999.00, 25, 11, 36, 30, 'SM-SLK-KJV', 4.40, 345, 1, 0, 0, 0, NULL, NULL, 1, NULL, NULL, 'https://placehold.co/400x400?text=Product', '2026-07-25 19:04:48', '2026-07-27 16:35:02'),
(155, 'W Women Palazzo Pants', 'w-women-palazzo-pants', 'Flared palazzo pants with elastic waistband and side pockets for effortless style.', 'Palazzo, Elastic Waist, Side Pockets', 2499.00, 1999.00, 20, 11, 37, 65, 'W-PLZ-STR', 4.20, 234, 0, 1, 0, 0, NULL, NULL, 1, NULL, NULL, 'https://placehold.co/400x400?text=Product', '2026-07-25 19:04:48', '2026-07-27 16:35:02'),
(156, 'Forever 21 Crop Top', 'forever-21-crop-top', 'Trendy ribbed crop top with square neckline and adjustable spaghetti straps.', 'Ribbed, Square Neck, Spaghetti Straps', 999.00, 799.00, 20, 11, 38, 100, 'F21-CRP-WHT', 4.10, 456, 0, 1, 0, 0, NULL, NULL, 1, NULL, NULL, 'https://placehold.co/400x400?text=Product', '2026-07-25 19:04:48', '2026-07-27 16:35:02'),
(157, 'Mango Leather Handbag', 'mango-leather-handbag', 'Premium faux leather handbag with gold hardware and multiple compartments.', 'Faux Leather, Gold Hardware, Multi-Compartment', 5999.00, 4499.00, 25, 11, 39, 25, 'MNG-HBG-TAN', 4.50, 189, 1, 0, 0, 0, NULL, NULL, 1, NULL, NULL, 'https://placehold.co/400x400?text=Product', '2026-07-25 19:04:48', '2026-07-27 16:35:02'),
(158, 'Biba Anarkali Suit Set', 'biba-anarkali-suit-set', 'Stunning printed Anarkali suit set with mirror work dupatta and matching bottom.', 'Anarkali, Mirror Work, 3-Piece Set', 3999.00, 2999.00, 25, 11, 40, 35, 'BIB-ANK-MRW', 4.30, 278, 1, 1, 0, 0, NULL, NULL, 1, NULL, NULL, 'https://placehold.co/400x400?text=Product', '2026-07-25 19:04:48', '2026-07-27 16:35:02'),
(159, 'Marks & Spencer Cashmere Scarf', 'marks-spencer-cashmere-scarf', 'Luxuriously soft 100% cashmere scarf in classic plaid pattern for winter styling.', '100% Cashmere, Plaid Pattern, Winter', 4999.00, 3999.00, 20, 11, 41, 30, 'MS-SCF-CSH', 4.60, 112, 0, 0, 0, 0, NULL, NULL, 1, NULL, NULL, 'https://placehold.co/400x400?text=Product', '2026-07-25 19:04:48', '2026-07-27 16:35:02'),
(160, 'Accessorize Crystal Earrings', 'accessorize-crystal-earrings', 'Elegant drop earrings with Austrian crystal and rhodium plating for lasting shine.', 'Austrian Crystal, Rhodium Plated, Drop Style', 1999.00, 1499.00, 25, 11, 42, 50, 'ACC-EAR-CRY', 4.40, 189, 0, 1, 0, 0, NULL, NULL, 1, NULL, NULL, 'https://placehold.co/400x400?text=Product', '2026-07-25 19:04:48', '2026-07-27 16:35:02'),
(161, 'Zara Printed Maxi Skirt', 'zara-printed-maxi-skirt', 'Flowing maxi skirt with bold print, elastic waistband, and side slit.', 'Bold Print, Elastic Waist, Side Slit', 3499.00, 2799.00, 20, 11, 35, 40, 'ZRA-MSK-PRN', 4.20, 167, 0, 0, 0, 0, NULL, NULL, 1, NULL, NULL, 'https://placehold.co/400x400?text=Product', '2026-07-25 19:04:48', '2026-07-27 16:35:02'),
(162, 'Puma RS-X Reinvention', 'puma-rs-x-reinvention', 'Chunky retro sneakers with RS foam technology and bold colorway.', 'RS Foam, Chunky Design, Bold Colors', 8999.00, 6999.00, 22, 12, 27, 45, 'PUMA-RSX', 4.20, 234, 1, 1, 0, 0, NULL, NULL, 1, NULL, NULL, 'https://placehold.co/400x400?text=Product', '2026-07-25 19:04:48', '2026-07-27 16:35:02'),
(163, 'Woodland Classic Boots', 'woodland-classic-boots', 'Rugged leather boots withGoodyear welt construction and anti-slip sole.', 'Leather, Goodyear Welt, Anti-Slip', 5999.00, 4999.00, 17, 12, 43, 50, 'WLD-CLB-BRN', 4.40, 345, 1, 0, 0, 0, NULL, NULL, 1, NULL, NULL, 'https://placehold.co/400x400?text=Product', '2026-07-25 19:04:48', '2026-07-27 16:35:02'),
(164, 'Bata Comfit Slip-Ons', 'bata-comfit-slip-ons', 'Ultra-comfortable slip-on formal shoes with Memory Foam insole.', 'Slip-On, Memory Foam, Formal', 2999.00, 2499.00, 17, 12, 44, 70, 'BTA-CMF-SLP', 4.10, 456, 0, 1, 0, 0, NULL, NULL, 1, NULL, NULL, 'https://placehold.co/400x400?text=Product', '2026-07-25 19:04:48', '2026-07-27 16:35:02'),
(165, 'Skechers Go Walk 7', 'skechers-go-walk-7', 'Lightweight walking shoes with Air-Cooled Goga Mat and ULTRA GO midsole.', 'Air-Cooled, Goga Mat, ULTRA GO', 6999.00, 5499.00, 21, 12, 45, 55, 'SKC-GW7-GRY', 4.50, 312, 1, 1, 0, 0, NULL, NULL, 1, NULL, NULL, 'https://placehold.co/400x400?text=Product', '2026-07-25 19:04:48', '2026-07-27 16:35:02'),
(166, 'New Balance 574 Classic', 'new-balance-574-classic', 'Iconic New Balance silhouette with ENCAP midsole cushioning and suede/mesh upper.', 'ENCAP, Suede + Mesh, Classic', 9995.00, 8495.00, 15, 12, 46, 40, 'NB-574-GRY', 4.40, 289, 1, 0, 0, 0, NULL, NULL, 1, NULL, NULL, 'https://placehold.co/400x400?text=Product', '2026-07-25 19:04:48', '2026-07-27 16:35:02'),
(167, 'Crocs Classic Clog', 'crocs-classic-clog', 'Iconic lightweight clog with Croslite foam and ventilation ports for breathability.', 'Croslite Foam, Ventilation, Lightweight', 3995.00, 3295.00, 18, 12, 47, 100, 'CRC-CLC-BLK', 4.20, 789, 0, 1, 0, 0, NULL, NULL, 1, NULL, NULL, 'https://placehold.co/400x400?text=Product', '2026-07-25 19:04:48', '2026-07-27 16:35:02'),
(168, 'Clarks Un Adorn Lace', 'clarks-un-adorn-lace', 'Premium leather lace-up shoes with Cushion Plus technology for all-day comfort.', 'Leather, Cushion Plus, Lace-Up', 7999.00, 6499.00, 19, 12, 48, 30, 'CLK-UAD-BRN', 4.50, 178, 1, 0, 0, 0, NULL, NULL, 1, NULL, NULL, 'https://placehold.co/400x400?text=Product', '2026-07-25 19:04:48', '2026-07-27 16:35:02'),
(169, 'Reebok Classic Leather', 'reebok-classic-leather', 'Timeless leather sneakers with soft leather upper and EVA midsole for cushioning.', 'Leather Upper, EVA Midsole, Timeless', 7999.00, 6499.00, 19, 12, 49, 45, 'RBK-CLC-WHT', 4.30, 345, 1, 1, 0, 0, NULL, NULL, 1, NULL, NULL, 'https://placehold.co/400x400?text=Product', '2026-07-25 19:04:48', '2026-07-27 16:35:02'),
(170, 'ASICS Gel-Kayano 31', 'asics-gel-kayano-31', 'Premium stability running shoes with 4D Guidance System and GEL cushioning.', '4D Guidance, GEL, Stability, 270g', 16990.00, 14490.00, 15, 12, 50, 25, 'ASICS-GK31', 4.60, 189, 1, 0, 0, 0, NULL, NULL, 1, NULL, NULL, 'https://placehold.co/400x400?text=Product', '2026-07-25 19:04:48', '2026-07-27 16:35:02'),
(171, 'Prestige Iris 750W Mixer Grinder', 'prestige-iris-750w-mixer-grinder', 'Powerful 750W mixer grinder with 3 stainless steel jars and motor overload protection.', '750W, 3 Jars, Motor Protection', 3499.00, 2799.00, 20, 13, 51, 60, 'PRE-IRIS-750', 4.30, 567, 1, 1, 0, 0, NULL, NULL, 1, NULL, NULL, 'https://placehold.co/400x400?text=Product', '2026-07-25 19:04:48', '2026-07-27 16:35:02'),
(172, 'Butterfly Jet Elite 750W', 'butterfly-jet-elite-750w', 'Stainless steel mixer grinder with 4 jars, vacuum suction feet and powerful motor.', '750W, 4 Jars, SS Body, Vacuum Feet', 2999.00, 2499.00, 17, 13, 52, 50, 'BFL-JET-EL', 4.10, 345, 0, 1, 0, 0, NULL, NULL, 1, NULL, NULL, 'https://placehold.co/400x400?text=Product', '2026-07-25 19:04:48', '2026-07-27 16:35:02'),
(173, 'Bajaj Rex Mixer Grinder 500W', 'bajaj-rex-mixer-grinder-500w', 'Compact 500W mixer grinder with 3 jars for everyday grinding and blending needs.', '500W, 3 Jars, Compact Design', 2199.00, 1799.00, 18, 13, 53, 70, 'BAJ-REX-500', 4.00, 423, 0, 0, 0, 0, NULL, NULL, 1, NULL, NULL, 'https://placehold.co/400x400?text=Product', '2026-07-25 19:04:49', '2026-07-27 16:35:02'),
(174, 'Prestige Popular Plus Pressure Cooker 5L', 'prestige-popular-plus-pressure-cooker-5l', 'Aluminium pressure cooker with induction base and safety valve for fast cooking.', '5L, Aluminium, Induction Base', 2999.00, 2499.00, 17, 13, 51, 80, 'PRE-POP-5L', 4.40, 678, 1, 1, 0, 0, NULL, NULL, 1, NULL, NULL, 'https://placehold.co/400x400?text=Product', '2026-07-25 19:04:49', '2026-07-27 16:35:02'),
(175, 'Hawkins Futura 3L Saucepan', 'hawkins-futura-3l-saucepan', 'Hard-anodized saucepan with stay-cool handle and even heat distribution.', '3L, Hard-Anodized, Even Heating', 1999.00, 1599.00, 20, 13, 54, 45, 'HAW-FUT-3L', 4.30, 234, 0, 0, 0, 0, NULL, NULL, 1, NULL, NULL, 'https://placehold.co/400x400?text=Product', '2026-07-25 19:04:49', '2026-07-27 16:35:02'),
(176, 'Milton Thermosteel Flask 1L', 'milton-thermosteel-flask-1l', 'Double-wall vacuum insulated flask keeping beverages hot/cold for 24 hours.', '1L, 24hr Hot/Cold, SS 304', 1299.00, 999.00, 23, 13, 55, 100, 'MLT-THR-1L', 4.50, 892, 1, 1, 0, 0, NULL, NULL, 1, NULL, NULL, 'https://placehold.co/400x400?text=Product', '2026-07-25 19:04:49', '2026-07-27 16:35:02'),
(177, 'Borosil Klip N Store Glass Set', 'borosil-klip-n-store-glass-set', 'Set of 4 borosilicate glass containers with airtight silicone lid clips.', '4pc Set, Borosilicate, Airtight', 1499.00, 1199.00, 20, 13, 56, 55, 'BOR-KNS-4PC', 4.40, 345, 1, 0, 0, 0, NULL, NULL, 1, NULL, NULL, 'https://placehold.co/400x400?text=Product', '2026-07-25 19:04:49', '2026-07-27 16:35:02'),
(178, 'Prestige Induction Cooktop PIC 16.0+', 'prestige-induction-cooktop-pic-16-0', 'Smart induction cooktop with Indian menu presets and automatic voltage regulator.', '1600W, Indian Menus, Push Buttons', 3499.00, 2999.00, 14, 13, 51, 40, 'PRE-PIC16', 4.20, 456, 0, 1, 0, 0, NULL, NULL, 1, NULL, NULL, 'https://placehold.co/400x400?text=Product', '2026-07-25 19:04:49', '2026-07-27 16:35:02'),
(179, 'Havells Captanio SX 2L Water Heater', 'havells-captanio-sx-2l-water-heater', 'Compact storage water heater with Incoloy heating element and 5-star rating.', '2L, Incoloy Element, 5-Star, WHB', 5499.00, 4499.00, 18, 13, 57, 25, 'HAV-CPT-2L', 4.30, 234, 0, 0, 0, 0, NULL, NULL, 1, NULL, NULL, 'https://placehold.co/400x400?text=Product', '2026-07-25 19:04:49', '2026-07-27 16:35:02'),
(180, 'Philips Viva Collection Airfryer', 'philips-viva-collection-airfryer', 'Rapid Air technology for guilt-free frying with up to 90% less fat.', '4.1L, Rapid Air, 90% Less Fat', 9995.00, 7995.00, 20, 13, 58, 35, 'PHS-AF-VVA', 4.50, 567, 1, 1, 0, 0, NULL, NULL, 1, NULL, NULL, 'https://placehold.co/400x400?text=Product', '2026-07-25 19:04:49', '2026-07-27 16:35:02'),
(181, 'Bajaj Majesty New SWX 3 Sandwich Toaster', 'bajaj-majesty-new-swx-3-sandwich-toaster', 'Non-stick coated plates with cool-touch handle for perfect golden sandwiches.', 'Non-Stick, Cool Touch, 700W', 1499.00, 1199.00, 20, 13, 53, 50, 'BAJ-SWX3', 4.10, 312, 0, 0, 0, 0, NULL, NULL, 1, NULL, NULL, 'https://placehold.co/400x400?text=Product', '2026-07-25 19:04:49', '2026-07-27 16:35:02'),
(182, 'Butterfly Matchless Gas Stove 3 Burner', 'butterfly-matchless-gas-stove-3-burner', 'Powder-coated gas stove with 3 high-efficiency burners and brass burners.', '3 Burner, Brass, Powder Coated', 4499.00, 3499.00, 22, 13, 52, 30, 'BFL-MCH-3B', 4.20, 289, 0, 0, 0, 0, NULL, NULL, 1, NULL, NULL, 'https://placehold.co/400x400?text=Product', '2026-07-25 19:04:49', '2026-07-27 16:35:02'),
(183, 'Milton Crystalware Dinner Set 30pc', 'milton-crystalware-dinner-set-30pc', 'Premium opalware dinner set with floral print, microwave safe and durable.', '30pc, Opalware, Floral, Microwave Safe', 3999.00, 2999.00, 25, 13, 55, 25, 'MLT-CRS-30', 4.40, 234, 1, 1, 0, 0, NULL, NULL, 1, NULL, NULL, 'https://placehold.co/400x400?text=Product', '2026-07-25 19:04:49', '2026-07-27 16:35:02'),
(184, 'Prestige Iris Non-Stick Cookware Set', 'prestige-iris-non-stick-cookware-set', '5-piece non-stick cookware set with granite finish and soft-touch handles.', '5pc, Granite Finish, Non-Stick', 3499.00, 2799.00, 20, 13, 51, 35, 'PRE-IRIS-CKW', 4.30, 345, 1, 0, 0, 0, NULL, NULL, 1, NULL, NULL, 'https://placehold.co/400x400?text=Product', '2026-07-25 19:04:49', '2026-07-27 16:35:02'),
(185, 'Decathlon Domyos Yoga Mat 8mm', 'decathlon-domyos-yoga-mat-8mm', 'Non-slip fitness yoga mat with alignment lines and carrying strap.', '8mm, Non-Slip, Alignment Lines', 1299.00, 999.00, 23, 14, 60, 100, 'DEC-YM-8MM', 4.30, 567, 1, 1, 0, 0, NULL, NULL, 1, NULL, NULL, 'https://placehold.co/400x400?text=Product', '2026-07-25 19:04:49', '2026-07-27 16:35:02'),
(186, 'Kobo Adjustable Dumbbell Set 20kg', 'kobo-adjustable-dumbbell-set-20kg', 'Adjustable cast iron dumbbell set with chrome handles and multiple weight plates.', '20kg Set, Cast Iron, Adjustable', 4999.00, 3999.00, 20, 14, 61, 40, 'KOB-DB-20K', 4.20, 234, 1, 1, 0, 0, NULL, NULL, 1, NULL, NULL, 'https://placehold.co/400x400?text=Product', '2026-07-25 19:04:49', '2026-07-27 16:35:02'),
(187, 'Boldfit Resistance Bands Set', 'boldfit-resistance-bands-set', 'Set of 5 resistance loop bands with different strengths for full body workout.', '5 Bands, Progressive Resistance, Latex', 799.00, 599.00, 25, 14, 62, 120, 'BLD-RB-5SET', 4.10, 678, 0, 1, 0, 0, NULL, NULL, 1, NULL, NULL, 'https://placehold.co/400x400?text=Product', '2026-07-25 19:04:49', '2026-07-27 16:35:02'),
(188, 'Lifelong LLHM114 Treadmill', 'lifelong-llhm114-treadmill', 'Motorized home treadmill with 4HP peak, 12km/h speed, and LCD display.', '4HP Peak, 12km/h, LCD, Foldable', 34999.00, 27999.00, 20, 14, 63, 15, 'LL-HM114', 4.00, 123, 1, 1, 0, 0, NULL, NULL, 1, NULL, NULL, 'https://placehold.co/400x400?text=Product', '2026-07-25 19:04:49', '2026-07-27 16:35:02'),
(189, 'Strauss Adjustable Weight Bench', 'strauss-adjustable-weight-bench', 'Multi-purpose adjustable weight bench with 6 positions for flat and incline workouts.', '6-Position, Multi-Purpose, Foldable', 8999.00, 6999.00, 22, 14, 64, 25, 'STR-WB-6P', 4.30, 189, 1, 0, 0, 0, NULL, NULL, 1, NULL, NULL, 'https://placehold.co/400x400?text=Product', '2026-07-25 19:04:49', '2026-07-27 16:35:02'),
(190, 'Nivia Storm Football Size 5', 'nivia-storm-football-size-5', 'Machine-stitched football with 32-panel design for excellent grip and flight.', 'Size 5, Machine-Stitched, 32-Panel', 1299.00, 999.00, 23, 14, 65, 80, 'NIV-STORM-S5', 4.10, 345, 0, 1, 0, 0, NULL, NULL, 1, NULL, NULL, 'https://placehold.co/400x400?text=Product', '2026-07-25 19:04:49', '2026-07-27 16:35:02'),
(191, 'Yonex Nanoray Light 18i Badminton Racket', 'yonex-nanoray-light-18i-badminton-racket', 'Lightweight badminton racket with isometric head shape for larger sweet spot.', 'Lightweight, Isometric, Nanomesh', 2499.00, 1999.00, 20, 14, 66, 45, 'YNX-NRL18I', 4.40, 289, 1, 1, 0, 0, NULL, NULL, 1, NULL, NULL, 'https://placehold.co/400x400?text=Product', '2026-07-25 19:04:49', '2026-07-27 16:35:02'),
(192, 'Cosco Cricket Tennis Ball (Pack of 6)', 'cosco-cricket-tennis-ball-pack-of-6', 'Hard tennis balls for practice and tennis cricket with consistent bounce.', '6 Pack, Hard, Consistent Bounce', 599.00, 449.00, 25, 14, 67, 100, 'CSC-TB-6PK', 4.00, 567, 0, 0, 0, 0, NULL, NULL, 1, NULL, NULL, 'https://placehold.co/400x400?text=Product', '2026-07-25 19:04:49', '2026-07-27 16:35:02'),
(193, 'PowerNet Zigzag Agility Ladder', 'powernet-zigzag-agility-ladder', 'Speed and agility training ladder with 12 rungs and carrying bag.', '12 Rungs, Adjustable, Portable', 999.00, 799.00, 20, 14, 68, 60, 'PWR-AL-12', 4.20, 178, 0, 1, 0, 0, NULL, NULL, 1, NULL, NULL, 'https://placehold.co/400x400?text=Product', '2026-07-25 19:04:49', '2026-07-27 16:35:02'),
(194, 'Boldfit Skipping Rope', 'boldfit-skipping-rope', 'Adjustable speed skipping rope with ball bearing system and anti-slip handles.', 'Adjustable, Ball Bearing, Anti-Slip', 499.00, 349.00, 30, 14, 62, 150, 'BLD-SR-SPD', 4.10, 892, 0, 1, 0, 0, NULL, NULL, 1, NULL, NULL, 'https://placehold.co/400x400?text=Product', '2026-07-25 19:04:49', '2026-07-27 16:35:02'),
(195, 'Lakme Absolute Skin Dew Serum Foundation', 'lakme-absolute-skin-dew-serum-foundation', 'Lightweight serum foundation with SPF 20 for a dewy, natural finish.', 'SPF 20, Serum, Dewy Finish', 899.00, 719.00, 20, 6, 69, 60, 'LAK-ASDS-FND', 4.30, 345, 1, 1, 0, 0, NULL, NULL, 1, NULL, NULL, 'https://placehold.co/400x400?text=Product', '2026-07-25 19:04:49', '2026-07-27 16:35:02'),
(196, 'Maybelline Fit Me Compact Powder', 'maybelline-fit-me-compact-powder', 'Matte + Poreless compact powder with micro-powders for shine control.', 'Matte, Oil-Free, Micro-Powders', 599.00, 479.00, 20, 6, 70, 80, 'MAY-FM-CMP', 4.20, 567, 0, 1, 0, 0, NULL, NULL, 1, NULL, NULL, 'https://placehold.co/400x400?text=Product', '2026-07-25 19:04:49', '2026-07-27 16:35:02'),
(197, 'Forest Essentials Soundarya Serum', 'forest-essentials-soundarya-serum', '24K Gold + Ayurvedic herbal serum for anti-aging and skin brightening.', '24K Gold, Ayurvedic, Anti-Aging', 3950.00, 3160.00, 20, 6, 71, 25, 'FE-SRND-SRM', 4.60, 234, 1, 0, 0, 0, NULL, NULL, 1, NULL, NULL, 'https://placehold.co/400x400?text=Product', '2026-07-25 19:04:49', '2026-07-27 16:35:02'),
(198, 'Mamaearth Vitamin C Face Wash', 'mamaearth-vitamin-c-face-wash', 'Refreshing face wash with Vitamin C and turmeric for bright, glowing skin.', 'Vitamin C, Turmeric, Sulfate-Free', 349.00, 279.00, 20, 6, 72, 120, 'MM-VTC-FW', 4.10, 892, 0, 1, 0, 0, NULL, NULL, 1, NULL, NULL, 'https://placehold.co/400x400?text=Product', '2026-07-25 19:04:49', '2026-07-27 16:35:02'),
(199, 'The Man Company Charcoal Face Mask', 'the-man-company-charcoal-face-mask', 'Deep cleansing charcoal peel-off mask for blackhead removal and detox.', 'Charcoal, Peel-Off, Deep Cleansing', 449.00, 359.00, 20, 6, 73, 70, 'TMC-CHR-FM', 4.00, 345, 0, 0, 0, 0, NULL, NULL, 1, NULL, NULL, 'https://placehold.co/400x400?text=Product', '2026-07-25 19:04:49', '2026-07-27 16:35:02'),
(200, 'L\'Oreal Paris Hyaluron Moisture Serum', 'l-oreal-paris-hyaluron-moisture-serum', 'Lightweight serum with Hyaluronic Acid for intense hydration and plump skin.', 'Hyaluronic Acid, Lightweight, 72hr Hydration', 799.00, 639.00, 20, 6, 74, 55, 'LOP-HM-SRM', 4.40, 456, 1, 1, 0, 0, NULL, NULL, 1, NULL, NULL, 'https://placehold.co/400x400?text=Product', '2026-07-25 19:04:49', '2026-07-27 16:35:02'),
(201, 'Nykaa So Matte Lipstick', 'nykaa-so-matte-lipstick', 'Long-lasting matte lipstick with creamy texture and intense color payoff.', 'Matte, Long-Lasting, Creamy', 349.00, 269.00, 23, 6, 75, 100, 'NYK-SM-LIP', 4.30, 678, 0, 1, 0, 0, NULL, NULL, 1, NULL, NULL, 'https://placehold.co/400x400?text=Product', '2026-07-25 19:04:49', '2026-07-27 16:35:02'),
(202, 'Biotique Bio Almond Overnight Therapy', 'biotique-bio-almond-overnight-therapy', 'Nourishing overnight eye cream with almond, pure turmeric, and herbs.', 'Overnight, Almond, Under-Eye', 349.00, 279.00, 20, 6, 76, 50, 'BIO-BA-OT', 4.20, 234, 0, 0, 0, 0, NULL, NULL, 1, NULL, NULL, 'https://placehold.co/400x400?text=Product', '2026-07-25 19:04:49', '2026-07-27 16:35:02'),
(203, 'Plum Green Tea Clear Face Mask', 'plum-green-tea-clear-face-mask', 'Clay face mask with green tea for acne-prone and oily skin detoxification.', 'Clay, Green Tea, Oil Control', 450.00, 360.00, 20, 6, 77, 60, 'PLM-GT-FM', 4.30, 345, 1, 1, 0, 0, NULL, NULL, 1, NULL, NULL, 'https://placehold.co/400x400?text=Product', '2026-07-25 19:04:49', '2026-07-27 16:35:02'),
(204, 'WOW Skin Science Apple Cider Vinegar Shampoo', 'wow-skin-science-apple-cider-vinegar-shampoo', 'Sulfate-free shampoo with apple cider vinegar for dandruff-free, shiny hair.', 'ACV, Sulfate-Free, Anti-Dandruff', 599.00, 449.00, 25, 6, NULL, 80, 'WOW-ACV-SH', 4.10, 1234, 0, 1, 0, 0, NULL, NULL, 1, NULL, NULL, 'https://placehold.co/400x400?text=Product', '2026-07-25 19:04:49', '2026-07-27 16:35:02');

--
-- Triggers `products`
--
DELIMITER $$
CREATE TRIGGER `trg_products_before_insert` BEFORE INSERT ON `products` FOR EACH ROW BEGIN
    IF NEW.discount_price IS NOT NULL AND NEW.price > 0 THEN
        SET NEW.discount_percent = ROUND(((NEW.price - NEW.discount_price) / NEW.price) * 100);
        IF NEW.discount_percent < 0 THEN SET NEW.discount_percent = 0; END IF;
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_products_before_update` BEFORE UPDATE ON `products` FOR EACH ROW BEGIN
    IF NEW.discount_price IS NOT NULL AND NEW.price > 0 THEN
        SET NEW.discount_percent = ROUND(((NEW.price - NEW.discount_price) / NEW.price) * 100);
        IF NEW.discount_percent < 0 THEN SET NEW.discount_percent = 0; END IF;
    ELSE
        SET NEW.discount_percent = NULL;
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `product_images`
--

CREATE TABLE `product_images` (
  `id` int(10) UNSIGNED NOT NULL,
  `product_id` int(10) UNSIGNED NOT NULL,
  `image` varchar(255) NOT NULL,
  `is_primary` tinyint(1) NOT NULL DEFAULT 0,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `product_images`
--

INSERT INTO `product_images` (`id`, `product_id`, `image`, `is_primary`, `sort_order`, `created_at`) VALUES
(125, 1, 'Samsung Galaxy S25 Ultra.jpeg', 1, 1, '2026-07-28 19:35:35'),
(126, 2, 'iphone 16 pro max.jpeg', 1, 1, '2026-07-28 19:35:35'),
(127, 3, 'oneplus 13 pro.jpeg', 1, 1, '2026-07-28 19:35:35'),
(128, 4, 'Samsung Galaxy A55.jpeg', 1, 1, '2026-07-28 19:35:35'),
(129, 5, 'HP Pavilian 15.jpeg', 1, 1, '2026-07-28 19:35:35'),
(130, 6, 'Lenovo Thinkpad x1 carbon.jpeg', 1, 1, '2026-07-28 19:35:35'),
(131, 7, 'Apple MacBook Air M3.jpeg', 1, 1, '2026-07-28 19:35:35'),
(132, 8, 'Hp victus Gaming.jpeg', 1, 1, '2026-07-28 19:35:35'),
(133, 9, 'sony WH-1000XM6.jpeg', 1, 1, '2026-07-28 19:35:35'),
(134, 10, 'Samsung Galaxy Buds Pro 3.jpeg', 1, 1, '2026-07-28 19:35:35'),
(135, 11, 'Apple AirPods Pro 3.jpeg', 1, 1, '2026-07-28 19:35:35'),
(136, 12, 'Nike Air Jordan Retro.jpeg', 1, 1, '2026-07-28 19:35:35'),
(137, 13, 'Adidas Ultraboost 25.jpeg', 1, 1, '2026-07-28 19:35:35'),
(138, 14, 'Nike Dri-FIT T-Shirt.jpeg', 1, 1, '2026-07-28 19:35:35'),
(139, 15, 'Nike Yoga Luxe Leggings.jpeg', 1, 1, '2026-07-28 19:35:35'),
(140, 16, 'Adidas Campus 00s.jpeg', 1, 1, '2026-07-28 19:35:35'),
(141, 17, 'Samsung 55-inch OLED TV.jpeg', 1, 1, '2026-07-28 19:35:35'),
(142, 18, 'Sony Bravia XR 65-inch.jpeg', 1, 1, '2026-07-28 19:35:35'),
(143, 19, 'Nike Pro Training Set.jpeg', 1, 1, '2026-07-28 19:35:35'),
(144, 20, 'Adidas Football.jpeg', 1, 1, '2026-07-28 19:35:35'),
(145, 21, 'Wings of Fire - APJ Abdul Kalam.jpeg', 1, 1, '2026-07-28 19:35:35'),
(146, 22, 'The Alchemist - Paulo Coelho.jpeg', 1, 1, '2026-07-28 19:35:35'),
(147, 23, 'Rich Dad Poor Dad.jpeg', 1, 1, '2026-07-28 19:35:35'),
(148, 24, 'Sony 4K Action Cam.jpeg', 1, 1, '2026-07-28 19:35:35'),
(149, 25, 'Samsung Galaxy S24 .jpeg', 1, 1, '2026-07-28 19:35:35'),
(150, 26, 'OnePlus 12R.jpeg', 1, 1, '2026-07-28 19:35:35'),
(151, 28, 'Samsung Galaxy Z Flip 6.jpeg', 1, 1, '2026-07-28 19:35:35'),
(152, 32, 'iPhone 15.jpeg', 1, 1, '2026-07-28 19:35:35'),
(153, 33, 'Samsung Galaxy A35.jpeg', 1, 1, '2026-07-28 19:35:35'),
(154, 37, 'Apple MacBook Pro 14 M3.jpeg', 1, 1, '2026-07-28 19:35:35'),
(155, 38, 'Lenovo IdeaPad Slim 5.jpeg', 1, 1, '2026-07-28 19:35:35'),
(156, 40, 'HP Envy x360 14.jpeg', 1, 1, '2026-07-28 19:35:35'),
(157, 49, 'OnePlus Buds Pro 2.jpeg', 1, 1, '2026-07-28 19:35:35'),
(158, 71, 'Nike Revolution 7.jpeg', 1, 1, '2026-07-28 19:35:35'),
(159, 105, 'Atomic Habits by James Clear.jpeg', 1, 1, '2026-07-28 19:35:35'),
(160, 106, 'The Psychology of Money by Morgan Housel.jpeg', 1, 1, '2026-07-28 19:35:35'),
(161, 107, 'Ikigai by H�ctor Garc�a.jpeg', 1, 1, '2026-07-28 19:35:35'),
(162, 108, 'Sapiens by Yuval Noah Harari.jpeg', 1, 1, '2026-07-28 19:35:35'),
(163, 109, 'The Power of Your Subconscious Mind by Joseph Murphy.jpeg', 1, 1, '2026-07-28 19:35:35'),
(164, 110, 'Think and Grow Rich by Napoleon Hill.jpeg', 1, 1, '2026-07-28 19:35:35'),
(165, 111, 'Becoming by Michelle Obama.jpeg', 1, 1, '2026-07-28 19:35:35'),
(166, 112, '1984 by George Orwell.jpeg', 1, 1, '2026-07-28 19:35:35'),
(167, 113, 'To Kill a Mockingbird by Harper Lee.jpeg', 1, 1, '2026-07-28 19:35:35'),
(168, 114, 'The Subtle Art of Not Giving a Fck by Mark Manson.jpeg', 1, 1, '2026-07-28 19:35:35'),
(169, 125, 'Google Pixel 8.jpeg', 1, 1, '2026-07-28 19:35:35'),
(170, 127, 'Vivo X100.jpeg', 1, 1, '2026-07-28 19:35:35'),
(171, 129, 'Nothing Phone 2a Plus.jpeg', 1, 1, '2026-07-28 19:35:35'),
(172, 130, 'Dell XPS 15.jpeg', 1, 1, '2026-07-28 19:35:35'),
(173, 131, 'ASUS ROG Strix G16.jpeg', 1, 1, '2026-07-28 19:35:35'),
(174, 132, 'Acer Nitro v 15.jpeg', 1, 1, '2026-07-28 19:35:35'),
(175, 133, 'ASUS Vivobook 16.jpeg', 1, 1, '2026-07-28 19:35:35'),
(176, 134, 'MSI Thin 15.jpeg', 1, 1, '2026-07-28 19:35:35'),
(177, 135, 'JBL Tune 770NC.jpeg', 1, 1, '2026-07-28 19:35:35'),
(178, 136, 'boAt Rockerz 551ANC.jpeg', 1, 1, '2026-07-28 19:35:35'),
(179, 137, 'Jabra Elite 45h.jpeg', 1, 1, '2026-07-28 19:35:35'),
(180, 138, 'Sennheiser HD 450BT.jpeg', 1, 1, '2026-07-28 19:35:35'),
(181, 139, 'Realme Buds T300.jpeg', 1, 1, '2026-07-28 19:35:35'),
(182, 140, 'Skullcandy Crusher ANC 2.jpeg', 1, 1, '2026-07-28 19:35:35'),
(183, 141, 'MarshalMajor.jpeg', 1, 1, '2026-07-28 19:35:35'),
(184, 142, 'Levi\'s 511 Slim Fit Jeans.jpeg', 1, 1, '2026-07-28 19:35:35'),
(185, 143, 'Allen Solly Men Regular Fit Shirt.jpeg', 1, 1, '2026-07-28 19:35:35'),
(186, 144, 'Puma Men Training Shorts.jpeg', 1, 1, '2026-07-28 19:35:35'),
(187, 145, 'Jack & Jones Denim Jacket.jpeg', 1, 1, '2026-07-28 19:35:35'),
(188, 146, 'Van Heusen Men Blazer.jpeg', 1, 1, '2026-07-28 19:35:35'),
(189, 148, 'Wrangler Men Cargo Pants.jpeg', 1, 1, '2026-07-28 19:35:35'),
(190, 149, 'Roadster Men Hoodie.jpeg', 1, 1, '2026-07-28 19:35:35'),
(191, 150, 'Pepe Jeans Slim Chinos.jpeg', 1, 1, '2026-07-28 19:35:35'),
(192, 151, 'H&M Men Quilted Jacket.jpeg', 1, 1, '2026-07-28 19:35:35'),
(193, 152, 'Zara Women Midi Dress.jpeg', 1, 1, '2026-07-28 19:35:35'),
(194, 153, 'H&M Oversized Blazer.jpeg', 1, 1, '2026-07-28 19:35:35'),
(195, 154, 'Saree Mall Silk Saree.jpeg', 1, 1, '2026-07-28 19:35:35'),
(196, 155, 'W Women Palazzo Pants.jpeg', 1, 1, '2026-07-28 19:35:35'),
(197, 156, 'Forever 21 Crop Top.jpeg', 1, 1, '2026-07-28 19:35:35'),
(198, 157, 'Mango Leather Handbag.jpeg', 1, 1, '2026-07-28 19:35:35'),
(199, 158, 'Biba Anarkali Suit Set.jpeg', 1, 1, '2026-07-28 19:35:35'),
(200, 159, 'Marks & Spencer Cashmere Scarf.jpeg', 1, 1, '2026-07-28 19:35:35'),
(201, 160, 'Accessorize Crystal Earrings.jpeg', 1, 1, '2026-07-28 19:35:35'),
(202, 161, 'Zara Printed Maxi Skirt.jpeg', 1, 1, '2026-07-28 19:35:35'),
(203, 162, 'Puma RS-X Reinvention.jpeg', 1, 1, '2026-07-28 19:35:35'),
(204, 163, 'Woodland Classic Boots.jpeg', 1, 1, '2026-07-28 19:35:35'),
(205, 164, 'Bata Comfit Slip-Ons.jpeg', 1, 1, '2026-07-28 19:35:35'),
(206, 165, 'Skechers Go Walk 7.jpeg', 1, 1, '2026-07-28 19:35:35'),
(207, 166, 'New Balance 574 Classic.jpeg', 1, 1, '2026-07-28 19:35:35'),
(208, 167, 'Crocs Classic Clog.jpeg', 1, 1, '2026-07-28 19:35:35'),
(209, 168, 'Clarks Un Adorn Lace.jpeg', 1, 1, '2026-07-28 19:35:35'),
(210, 169, 'Reebok Classic Leather.jpeg', 1, 1, '2026-07-28 19:35:35'),
(211, 170, 'ASICS Gel-Kayano 31.jpeg', 1, 1, '2026-07-28 19:35:35'),
(212, 171, 'Prestige Iris 750W Mixer Grinder.jpeg', 1, 1, '2026-07-28 19:35:35'),
(213, 172, 'Butterfly Jet Elite 750W.jpeg', 1, 1, '2026-07-28 19:35:35'),
(214, 173, 'Bajaj Rex Mixer Grinder 500W.jpeg', 1, 1, '2026-07-28 19:35:35'),
(215, 174, 'Prestige Popular Plus Pressure Cooker 5L.jpeg', 1, 1, '2026-07-28 19:35:35'),
(216, 175, 'Hawkins Futura 3L Saucepan.jpeg', 1, 1, '2026-07-28 19:35:35'),
(217, 176, 'Milton Thermosteel Flask 1L.jpeg', 1, 1, '2026-07-28 19:35:35'),
(218, 177, 'Borosil Klip N Store Glass Set.jpeg', 1, 1, '2026-07-28 19:35:35'),
(219, 178, 'Prestige Induction Cooktop PIC 16.0+.jpeg', 1, 1, '2026-07-28 19:35:35'),
(220, 179, 'Havells Captanio SX 2L Water Heater.jpeg', 1, 1, '2026-07-28 19:35:35'),
(221, 180, 'Philips Viva Collection Airfryer.jpeg', 1, 1, '2026-07-28 19:35:35'),
(222, 181, 'Bajaj Majesty New SWX 3 Sandwich Toaster.jpeg', 1, 1, '2026-07-28 19:35:35'),
(223, 182, 'Butterfly Matchless Gas Stove 3 Burner.jpeg', 1, 1, '2026-07-28 19:35:35'),
(224, 183, 'Milton Crystalware Dinner Set 30pc.jpeg', 1, 1, '2026-07-28 19:35:35'),
(225, 184, 'Prestige Iris Non-Stick Cookware Set.jpeg', 1, 1, '2026-07-28 19:35:35'),
(226, 185, 'Decathlon Domyos Yoga Mat 8mm.jpeg', 1, 1, '2026-07-28 19:35:35'),
(227, 186, 'Kobo Adjustable Dumbbell Set 20kg.jpeg', 1, 1, '2026-07-28 19:35:35'),
(228, 187, 'Boldfit Resistance Bands Set.jpeg', 1, 1, '2026-07-28 19:35:35'),
(229, 188, 'Lifelong LLHM114 Treadmill.jpeg', 1, 1, '2026-07-28 19:35:35'),
(230, 189, 'Strauss Adjustable Weight Bench.jpeg', 1, 1, '2026-07-28 19:35:35'),
(231, 190, 'Nivia Storm Football Size 5.jpeg', 1, 1, '2026-07-28 19:35:35'),
(232, 191, 'Yonex Nanoray Light 18i Badminton Racket.jpeg', 1, 1, '2026-07-28 19:35:35'),
(233, 192, 'Cosco Cricket Tennis Ball (Pack of 6).jpeg', 1, 1, '2026-07-28 19:35:35'),
(234, 193, 'PowerNet Zigzag Agility Ladder.jpeg', 1, 1, '2026-07-28 19:35:35'),
(235, 194, 'Boldfit Skipping Rope.jpeg', 1, 1, '2026-07-28 19:35:35'),
(236, 195, 'Lakme Absolute Skin Dew Serum Foundation.jpeg', 1, 1, '2026-07-28 19:35:35'),
(237, 196, 'Maybelline Fit Me Compact Powder.jpeg', 1, 1, '2026-07-28 19:35:35'),
(238, 197, 'Forest Essentials Soundarya Serum.jpeg', 1, 1, '2026-07-28 19:35:35'),
(239, 198, 'Mamaearth Vitamin C Face Wash.jpeg', 1, 1, '2026-07-28 19:35:35'),
(240, 199, 'The Man Company Charcoal Face Mask.jpeg', 1, 1, '2026-07-28 19:35:35'),
(241, 200, 'L\'Oreal Paris Hyaluron Moisture Serum.jpeg', 1, 1, '2026-07-28 19:35:35'),
(242, 201, 'Nykaa So Matte Lipstick.jpeg', 1, 1, '2026-07-28 19:35:35'),
(243, 202, 'Biotique Bio Almond Overnight Therapy.jpeg', 1, 1, '2026-07-28 19:35:35'),
(244, 203, 'Plum Green Tea Clear Face Mask.jpeg', 1, 1, '2026-07-28 19:35:35'),
(245, 204, 'WOW Skin Science Apple Cider Vinegar Shampoo.jpeg', 1, 1, '2026-07-28 19:35:35'),
(246, 147, 'Polo Assn. Polo T-Shirt.jpeg', 1, 1, '2026-07-28 19:35:51'),
(247, 128, 'prod_6a69d49a29b67.png', 1, 0, '2026-07-29 10:23:24'),
(248, 128, 'prod_6a69d49c0683b.png', 0, 0, '2026-07-29 10:23:26'),
(249, 128, 'prod_6a69d49e34832.png', 0, 0, '2026-07-29 10:23:28'),
(250, 126, 'prod_6a69d4a0958b6.png', 1, 0, '2026-07-29 10:23:30');

-- --------------------------------------------------------

--
-- Table structure for table `product_specifications`
--

CREATE TABLE `product_specifications` (
  `id` int(10) UNSIGNED NOT NULL,
  `product_id` int(10) UNSIGNED NOT NULL,
  `spec_name` varchar(150) NOT NULL,
  `spec_value` text NOT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `product_specifications`
--

INSERT INTO `product_specifications` (`id`, `product_id`, `spec_name`, `spec_value`, `sort_order`) VALUES
(1, 1, 'Display', '6.9-inch Dynamic AMOLED 2X, 120Hz', 1),
(2, 1, 'Processor', 'Exynos 2500 / Snapdragon 8 Gen 4', 2),
(3, 1, 'RAM', '12GB LPDDR5X', 3),
(4, 1, 'Storage', '256GB UFS 4.0', 4),
(5, 1, 'Camera', '200MP + 50MP + 12MP + 10MP', 5),
(6, 1, 'Battery', '5000mAh, 45W Fast Charging', 6),
(7, 2, 'Display', '6.9-inch Super Retina XDR OLED, 120Hz', 1),
(8, 2, 'Processor', 'A18 Bionic', 2),
(9, 2, 'RAM', '8GB', 3),
(10, 2, 'Storage', '256GB', 4),
(11, 2, 'Camera', '48MP Main + 12MP Ultra + 12MP Tele + LiDAR', 5),
(12, 2, 'Battery', '4685mAh, 40W Fast Charging', 6),
(13, 7, 'Display', '15.3-inch Liquid Retina, 2880x1864', 1),
(14, 7, 'Processor', 'Apple M3 8-core', 2),
(15, 7, 'RAM', '8GB Unified', 3),
(16, 7, 'Storage', '256GB SSD', 4),
(17, 7, 'Battery', 'Up to 18 hours', 5),
(18, 9, 'Driver', '40mm Dynamic Driver', 1),
(19, 9, 'Battery', '40 hours (ANC on)', 2),
(20, 9, 'Connectivity', 'Bluetooth 5.3, 3.5mm, USB-C', 3),
(21, 9, 'Weight', '250g', 4),
(22, 25, 'Display', '6.7\" FHD+ Dynamic AMOLED 2X', 1),
(23, 25, 'Processor', 'Exynos 2400e', 2),
(24, 25, 'RAM', '8GB', 3),
(25, 25, 'Storage', '128GB', 4),
(26, 25, 'Camera', '50MP + 12MP + 8MP', 5),
(27, 25, 'Battery', '4700mAh, 25W Charging', 6),
(28, 26, 'Display', '6.78\" LTPO AMOLED, 120Hz', 1),
(29, 26, 'Processor', 'Snapdragon 8 Gen 2', 2),
(30, 26, 'RAM', '16GB', 3),
(31, 26, 'Storage', '256GB', 4),
(32, 26, 'Camera', '50MP + 8MP + 2MP', 5),
(33, 26, 'Battery', '5500mAh, 100W SUPERVOOC', 6),
(34, 28, 'Display', '6.7\" FHD+ Foldable AMOLED + 3.4\" Cover', 1),
(35, 28, 'Processor', 'Snapdragon 8 Gen 3', 2),
(36, 28, 'RAM', '12GB', 3),
(37, 28, 'Storage', '256GB', 4),
(38, 28, 'Camera', '50MP + 12MP Ultra', 5),
(39, 28, 'Battery', '4000mAh, 25W', 6),
(40, 32, 'Display', '6.1\" Super Retina XDR OLED', 1),
(41, 32, 'Processor', 'A16 Bionic', 2),
(42, 32, 'RAM', '6GB', 3),
(43, 32, 'Storage', '128GB', 4),
(44, 32, 'Camera', '48MP + 12MP Ultra', 5),
(45, 32, 'Battery', '3349mAh, 20W Charging', 6),
(46, 33, 'Display', '6.6\" Super AMOLED, 120Hz', 1),
(47, 33, 'Processor', 'Exynos 1380', 2),
(48, 33, 'RAM', '8GB', 3),
(49, 33, 'Storage', '128GB', 4),
(50, 33, 'Camera', '50MP + 8MP + 5MP', 5),
(51, 33, 'Battery', '5000mAh, 25W Charging', 6),
(52, 37, 'Display', '14.2\" Liquid Retina XDR', 1),
(53, 37, 'Processor', 'Apple M3 Pro 11-core', 2),
(54, 37, 'RAM', '18GB Unified', 3),
(55, 37, 'Storage', '512GB SSD', 4),
(56, 37, 'Battery', 'Up to 18 hours', 5),
(57, 37, 'Ports', 'MagSafe, 3x Thunderbolt 4, HDMI, SD', 6),
(58, 38, 'Display', '15.6\" FHD IPS, 100% sRGB', 1),
(59, 38, 'Processor', 'Intel Core i5-1335U', 2),
(60, 38, 'RAM', '16GB LPDDR5', 3),
(61, 38, 'Storage', '512GB NVMe SSD', 4),
(62, 38, 'Battery', '57Whr, Up to 10hrs', 5),
(63, 38, 'Weight', '1.46kg', 6),
(64, 40, 'Display', '14\" 2.8K OLED, Touch, 360°', 1),
(65, 40, 'Processor', 'Intel Core Ultra 5 125H', 2),
(66, 40, 'RAM', '16GB LPDDR5x', 3),
(67, 40, 'Storage', '512GB NVMe SSD', 4),
(68, 40, 'Graphics', 'Intel Arc', 5),
(69, 40, 'Stylus', 'HP Pen included', 6),
(70, 49, 'Driver', '11mm Dynamic + 6mm Planar', 1),
(71, 49, 'ANC', 'Up to 48dB Smart ANC', 2),
(72, 49, 'Battery', '39hrs total (ANC off)', 3),
(73, 49, 'Connectivity', 'Bluetooth 5.3, LHDC 5.0', 4),
(74, 49, 'Spatial Audio', 'Dolby Atmos', 5),
(75, 49, 'Water Resistance', 'IP55', 6),
(76, 71, 'Upper', 'Mesh', 1),
(77, 71, 'Midsole', 'Soft Foam', 2),
(78, 71, 'Outsole', 'Rubber', 3),
(79, 71, 'Closure', 'Lace-Up', 4),
(80, 71, 'Weight', '250g', 5),
(81, 71, 'Use', 'Running, Walking', 6),
(82, 105, 'Author', 'James Clear', 1),
(83, 105, 'Pages', '320', 2),
(84, 105, 'Format', 'Paperback', 3),
(85, 105, 'Genre', 'Self-Help, Psychology', 4),
(86, 105, 'Publisher', 'Random House', 5),
(87, 105, 'Language', 'English', 6),
(88, 106, 'Author', 'Morgan Housel', 1),
(89, 106, 'Pages', '256', 2),
(90, 106, 'Format', 'Paperback', 3),
(91, 106, 'Genre', 'Finance, Psychology', 4),
(92, 106, 'Publisher', 'Jaico Publishing', 5),
(93, 106, 'Language', 'English', 6),
(94, 107, 'Author', 'Héctor García & Francesc Miralles', 1),
(95, 107, 'Pages', '208', 2),
(96, 107, 'Format', 'Paperback', 3),
(97, 107, 'Genre', 'Philosophy, Self-Help', 4),
(98, 107, 'Publisher', 'Penguin', 5),
(99, 107, 'Language', 'English', 6),
(100, 108, 'Author', 'Yuval Noah Harari', 1),
(101, 108, 'Pages', '498', 2),
(102, 108, 'Format', 'Paperback', 3),
(103, 108, 'Genre', 'History, Science', 4),
(104, 108, 'Publisher', 'Vintage', 5),
(105, 108, 'Language', 'English', 6),
(106, 109, 'Author', 'Joseph Murphy', 1),
(107, 109, 'Pages', '304', 2),
(108, 109, 'Format', 'Paperback', 3),
(109, 109, 'Genre', 'Self-Help, Mind Power', 4),
(110, 109, 'Publisher', 'Manjul Publishing', 5),
(111, 109, 'Language', 'English', 6),
(112, 110, 'Author', 'Napoleon Hill', 1),
(113, 110, 'Pages', '320', 2),
(114, 110, 'Format', 'Paperback', 3),
(115, 110, 'Genre', 'Business, Self-Help', 4),
(116, 110, 'Publisher', 'Jaico Publishing', 5),
(117, 110, 'Language', 'English', 6),
(118, 111, 'Author', 'Michelle Obama', 1),
(119, 111, 'Pages', '448', 2),
(120, 111, 'Format', 'Hardcover', 3),
(121, 111, 'Genre', 'Memoir, Biography', 4),
(122, 111, 'Publisher', 'Penguin', 5),
(123, 111, 'Language', 'English', 6),
(124, 112, 'Author', 'George Orwell', 1),
(125, 112, 'Pages', '328', 2),
(126, 112, 'Format', 'Paperback', 3),
(127, 112, 'Genre', 'Dystopian, Science Fiction', 4),
(128, 112, 'Publisher', 'Penguin', 5),
(129, 112, 'Language', 'English', 6),
(130, 113, 'Author', 'Harper Lee', 1),
(131, 113, 'Pages', '336', 2),
(132, 113, 'Format', 'Paperback', 3),
(133, 113, 'Genre', 'Classic Fiction, Drama', 4),
(134, 113, 'Publisher', 'Arrow Books', 5),
(135, 113, 'Language', 'English', 6),
(136, 114, 'Author', 'Mark Manson', 1),
(137, 114, 'Pages', '224', 2),
(138, 114, 'Format', 'Paperback', 3),
(139, 114, 'Genre', 'Self-Help, Philosophy', 4),
(140, 114, 'Publisher', 'HarperCollins', 5),
(141, 114, 'Language', 'English', 6),
(142, 125, 'Display', '6.2\" OLED, 120Hz', 1),
(143, 125, 'Processor', 'Google Tensor G3', 2),
(144, 125, 'RAM', '8GB', 3),
(145, 125, 'Storage', '128GB', 4),
(146, 125, 'Camera', '50MP + 12MP Ultra', 5),
(147, 125, 'Battery', '4575mAh, 27W Charging', 6),
(148, 126, 'Display', '6.67\" 1.5K AMOLED, 120Hz', 1),
(149, 126, 'Processor', 'MediaTek Dimensity 7200 Ultra', 2),
(150, 126, 'RAM', '12GB', 3),
(151, 126, 'Storage', '256GB', 4),
(152, 126, 'Camera', '200MP + 8MP + 2MP', 5),
(153, 126, 'Battery', '4610mAh, 120W HyperCharge', 6),
(154, 127, 'Display', '6.78\" LTPO AMOLED, 120Hz', 1),
(155, 127, 'Processor', 'MediaTek Dimensity 9300', 2),
(156, 127, 'RAM', '16GB', 3),
(157, 127, 'Storage', '256GB', 4),
(158, 127, 'Camera', '50MP ZEISS + 50MP Ultra + 64MP Tele', 5),
(159, 127, 'Battery', '5000mAh, 120W FlashCharge', 6),
(160, 128, 'Display', '6.7\" pOLED, 144Hz', 1),
(161, 128, 'Processor', 'Snapdragon 7 Gen 3', 2),
(162, 128, 'RAM', '12GB', 3),
(163, 128, 'Storage', '256GB', 4),
(164, 128, 'Camera', '50MP + 13MP Ultra + 10MP Tele', 5),
(165, 128, 'Battery', '4500mAh, 125W TurboPower', 6),
(166, 129, 'Display', '6.7\" AMOLED, 120Hz', 1),
(167, 129, 'Processor', 'MediaTek Dimensity 7350 Pro', 2),
(168, 129, 'RAM', '8GB', 3),
(169, 129, 'Storage', '256GB', 4),
(170, 129, 'Camera', '50MP + 50MP Ultra', 5),
(171, 129, 'Battery', '5000mAh, 50W Charging', 6),
(172, 130, 'Display', '15.6\" 3.5K OLED, Touch', 1),
(173, 130, 'Processor', 'Intel Core Ultra 7 155H', 2),
(174, 130, 'RAM', '16GB LPDDR5x', 3),
(175, 130, 'Storage', '512GB NVMe SSD', 4),
(176, 130, 'Graphics', 'Intel Arc', 5),
(177, 130, 'Battery', '86Whr, Up to 13hrs', 6),
(178, 131, 'Display', '16\" QHD 240Hz ROG Nebula', 1),
(179, 131, 'Processor', 'Intel i9-14900HX', 2),
(180, 131, 'RAM', '16GB DDR5', 3),
(181, 131, 'Storage', '1TB PCIe Gen4 SSD', 4),
(182, 131, 'Graphics', 'NVIDIA RTX 4070 8GB', 5),
(183, 131, 'Cooling', 'ROG Intelligent Cooling', 6),
(184, 132, 'Display', '15.6\" FHD IPS, 144Hz', 1),
(185, 132, 'Processor', 'Intel i5-13420H', 2),
(186, 132, 'RAM', '16GB DDR5', 3),
(187, 132, 'Storage', '512GB NVMe SSD', 4),
(188, 132, 'Graphics', 'NVIDIA RTX 4050 6GB', 5),
(189, 132, 'Weight', '2.1kg', 6),
(190, 133, 'Display', '16\" FHD IPS, NanoEdge', 1),
(191, 133, 'Processor', 'AMD Ryzen 7 7730U', 2),
(192, 133, 'RAM', '16GB DDR4', 3),
(193, 133, 'Storage', '512GB NVMe SSD', 4),
(194, 133, 'Battery', '50Whr, Up to 8hrs', 5),
(195, 133, 'Weight', '1.88kg', 6),
(196, 134, 'Display', '15.6\" FHD IPS, 144Hz', 1),
(197, 134, 'Processor', 'Intel i7-13620H', 2),
(198, 134, 'RAM', '16GB DDR5', 3),
(199, 134, 'Storage', '512GB NVMe SSD', 4),
(200, 134, 'Graphics', 'NVIDIA RTX 4060 8GB', 5),
(201, 134, 'Weight', '1.86kg', 6),
(202, 135, 'Driver', '40mm', 1),
(203, 135, 'ANC', 'Adaptive, 3 levels', 2),
(204, 135, 'Battery', '44 hours (ANC off), 33 hours (ANC on)', 3),
(205, 135, 'Connectivity', 'Bluetooth 5.3, 3.5mm', 4),
(206, 135, 'Weight', '252g', 5),
(207, 135, 'Foldable', 'Yes', 6),
(208, 136, 'Driver', '50mm', 1),
(209, 136, 'ANC', 'Hybrid ANC', 2),
(210, 136, 'Battery', '50 hours (ANC off), 35 hours (ANC on)', 3),
(211, 136, 'Connectivity', 'Bluetooth 5.3, AUX', 4),
(212, 136, 'ASAP Charge', '10min = 5hrs', 5),
(213, 136, 'Weight', '230g', 6),
(214, 137, 'Driver', '40mm', 1),
(215, 137, 'Battery', '50 hours', 2),
(216, 137, 'Connectivity', 'Bluetooth 5.0, Dual Connect', 3),
(217, 137, 'Weight', '174g', 4),
(218, 137, 'Microphones', '2-mic technology', 5),
(219, 137, 'Foldable', 'Yes', 6),
(220, 138, 'Driver', '32mm', 1),
(221, 138, 'ANC', 'Active Noise Cancellation', 2),
(222, 138, 'Battery', '30 hours', 3),
(223, 138, 'Connectivity', 'Bluetooth 5.0', 4),
(224, 138, 'Codec', 'AAC, aptX', 5),
(225, 138, 'Weight', '238g', 6),
(226, 139, 'Driver', '12.4mm', 1),
(227, 139, 'ANC', 'Up to 40dB', 2),
(228, 139, 'Battery', '40 hours total', 3),
(229, 139, 'Connectivity', 'Bluetooth 5.3', 4),
(230, 139, 'Spatial Audio', '360°', 5),
(231, 139, 'Water Resistance', 'IP55', 6),
(232, 140, 'Driver', '40mm', 1),
(233, 140, 'Bass', 'Adjustable Sensory Bass', 2),
(234, 140, 'ANC', 'Digital Active', 3),
(235, 140, 'Battery', '50 hours', 4),
(236, 140, 'Connectivity', 'Bluetooth 5.0, AUX', 5),
(237, 140, 'Weight', '310g', 6),
(238, 141, 'Driver', '40mm Custom', 1),
(239, 141, 'Battery', '80+ hours', 2),
(240, 141, 'Connectivity', 'Bluetooth 5.0', 3),
(241, 141, 'Charging', 'USB-C + Wireless', 4),
(242, 141, 'Foldable', 'Yes', 5),
(243, 141, 'Weight', '165g', 6),
(244, 142, 'Fit', 'Slim Fit', 1),
(245, 142, 'Material', '99% Cotton, 1% Elastane', 2),
(246, 142, 'Closure', 'Button Fly', 3),
(247, 142, 'Care', 'Machine Washable', 4),
(248, 142, 'Pocket', '5-pocket styling', 5),
(249, 142, 'Length', 'Regular', 6),
(250, 143, 'Fit', 'Regular Fit', 1),
(251, 143, 'Material', '100% Cotton', 2),
(252, 143, 'Collar', 'Spread Collar', 3),
(253, 143, 'Sleeve', 'Full Sleeve', 4),
(254, 143, 'Wrinkle-Free', 'Yes', 5),
(255, 143, 'Pattern', 'Solid', 6),
(256, 144, 'Fit', 'Regular Fit', 1),
(257, 144, 'Material', '100% Polyester', 2),
(258, 144, 'Technology', 'DryCELL', 3),
(259, 144, 'Pockets', 'Side zip, Back patch', 4),
(260, 144, 'Waistband', 'Elastic with drawcord', 5),
(261, 144, 'Length', 'Above Knee', 6),
(262, 145, 'Style', 'Trucker Jacket', 1),
(263, 145, 'Material', '100% Cotton Denim', 2),
(264, 145, 'Wash', 'Vintage Medium', 3),
(265, 145, 'Closure', 'Button Front', 4),
(266, 145, 'Pockets', 'Chest flap, Side hand', 5),
(267, 145, 'Lining', 'Cotton body lining', 6),
(268, 146, 'Fit', 'Slim Fit', 1),
(269, 146, 'Material', '98% Polyester, 2% Elastane', 2),
(270, 146, 'Closure', 'Two-Button', 3),
(271, 146, 'Lining', 'Full Lining', 4),
(272, 146, 'Pockets', '2 Flap, 1 Chest', 5),
(273, 146, 'Occasion', 'Formal, Business', 6),
(274, 147, 'Fit', 'Regular Fit', 1),
(275, 147, 'Material', '100% Cotton Pique', 2),
(276, 147, 'Collar', 'Ribbed Polo', 3),
(277, 147, 'Sleeve', 'Short Sleeve', 4),
(278, 147, 'Logo', 'Embroidered Double Horseman', 5),
(279, 147, 'Pattern', 'Solid', 6),
(280, 148, 'Fit', 'Relaxed Fit', 1),
(281, 148, 'Material', '100% Cotton Twill', 2),
(282, 148, 'Pockets', '6 total (2 cargo)', 3),
(283, 148, 'Closure', 'Button + Zip', 4),
(284, 148, 'Knee', 'Articulated', 5),
(285, 148, 'Color', 'Khaki', 6),
(286, 149, 'Fit', 'Regular Fit', 1),
(287, 149, 'Material', '60% Cotton, 40% Polyester', 2),
(288, 149, 'Hood', 'Drawstring', 3),
(289, 149, 'Pocket', 'Kangaroo', 4),
(290, 149, 'Care', 'Machine Washable', 5),
(291, 149, 'Warmth', 'Fleece-Lined', 6),
(292, 150, 'Fit', 'Slim Tapered', 1),
(293, 150, 'Material', '98% Cotton, 2% Elastane', 2),
(294, 150, 'Closure', 'Button + Zip', 3),
(295, 150, 'Pocket', '5-pocket styling', 4),
(296, 150, 'Rise', 'Mid Rise', 5),
(297, 150, 'Wrinkle-Resistant', 'Yes', 6),
(298, 151, 'Fit', 'Regular Fit', 1),
(299, 151, 'Material', '100% Recycled Polyester', 2),
(300, 151, 'Fill', 'Synthetic', 3),
(301, 151, 'Closure', 'Zip + Snap', 4),
(302, 151, 'Pockets', '2 Zip, 1 Inner', 5),
(303, 151, 'Water-Repellent', 'Yes', 6),
(304, 152, 'Style', 'Wrap Dress', 1),
(305, 152, 'Length', 'Midi', 2),
(306, 152, 'Neckline', 'V-Neck', 3),
(307, 152, 'Material', 'Viscose Blend', 4),
(308, 152, 'Closure', 'Wrap Tie', 5),
(309, 152, 'Occasion', 'Casual, Semi-Formal', 6),
(310, 153, 'Fit', 'Oversized', 1),
(311, 153, 'Material', 'Polyester Blend', 2),
(312, 153, 'Closure', 'Single Button', 3),
(313, 153, 'Shoulder', 'Padded', 4),
(314, 153, 'Pockets', '2 Flap', 5),
(315, 153, 'Lining', 'Full', 6),
(316, 154, 'Material', 'Art Silk', 1),
(317, 154, 'Length', '5.5 meters + 0.8m Blouse', 2),
(318, 154, 'Border', 'Gold Zari', 3),
(319, 154, 'Pattern', 'Traditional', 4),
(320, 154, 'Wash', 'Dry Clean Only', 5),
(321, 154, 'Occasion', 'Festive, Wedding', 6),
(322, 155, 'Fit', 'Relaxed Flare', 1),
(323, 155, 'Material', 'Viscose Crepe', 2),
(324, 155, 'Waist', 'Elasticated', 3),
(325, 155, 'Pockets', '2 Side', 4),
(326, 155, 'Length', 'Full Length', 5),
(327, 155, 'Pattern', 'Striped', 6),
(328, 156, 'Style', 'Crop Top', 1),
(329, 156, 'Material', '95% Rayon, 5% Spandex', 2),
(330, 156, 'Neckline', 'Square', 3),
(331, 156, 'Straps', 'Spaghetti, Adjustable', 4),
(332, 156, 'Fit', 'Cropped, Fitted', 5),
(333, 156, 'Pattern', 'Solid', 6),
(334, 157, 'Material', 'Vegan Leather', 1),
(335, 157, 'Compartments', '3 Main + 2 Inner', 2),
(336, 157, 'Closure', 'Magnetic Snap', 3),
(337, 157, 'Strap', 'Detachable Crossbody', 4),
(338, 157, 'Hardware', 'Gold-Tone', 5),
(339, 157, 'Dimensions', '30 x 25 x 12 cm', 6),
(340, 158, 'Set', 'Kurta + Bottom + Dupatta', 1),
(341, 158, 'Material', 'Cotton Blend', 2),
(342, 158, 'Work', 'Mirror Work', 3),
(343, 158, 'Length', 'Calf Length', 4),
(344, 158, 'Neckline', 'Round with Keyhole', 5),
(345, 158, 'Sleeve', '3/4 Sleeve', 6),
(346, 159, 'Material', '100% Cashmere', 1),
(347, 159, 'Pattern', 'Plaid', 2),
(348, 159, 'Dimensions', '180 x 70 cm', 3),
(349, 159, 'Weight', 'Lightweight', 4),
(350, 159, 'Care', 'Hand Wash', 5),
(351, 159, 'Season', 'Winter', 6),
(352, 160, 'Type', 'Drop Earrings', 1),
(353, 160, 'Material', 'Austrian Crystal', 2),
(354, 160, 'Plating', 'Rhodium', 3),
(355, 160, 'Closure', 'Push Back', 4),
(356, 160, 'Weight', '5g each', 5),
(357, 160, 'Occasion', 'Party, Gift', 6),
(358, 161, 'Style', 'Maxi Skirt', 1),
(359, 161, 'Material', 'Viscose', 2),
(360, 161, 'Waist', 'Elasticated', 3),
(361, 161, 'Slit', 'Side', 4),
(362, 161, 'Pattern', 'Bold Print', 5),
(363, 161, 'Length', 'Ankle Length', 6),
(364, 162, 'Upper', 'Mesh + Synthetic', 1),
(365, 162, 'Midsole', 'RS Foam', 2),
(366, 162, 'Outsole', 'Rubber', 3),
(367, 162, 'Closure', 'Lace-Up', 4),
(368, 162, 'Style', 'Retro Chunky', 5),
(369, 162, 'Weight', '340g', 6),
(370, 163, 'Upper', 'Full Grain Leather', 1),
(371, 163, 'Sole', 'Goodyear Welt, Anti-Slip', 2),
(372, 163, 'Closure', 'Lace-Up', 3),
(373, 163, 'Height', 'Ankle', 4),
(374, 163, 'Insole', 'Cushioned', 5),
(375, 163, 'Use', 'Trekking, Casual', 6),
(376, 164, 'Upper', 'Synthetic Leather', 1),
(377, 164, 'Insole', 'Memory Foam', 2),
(378, 164, 'Sole', 'TPR', 3),
(379, 164, 'Style', 'Slip-On, Formal', 4),
(380, 164, 'Closure', 'No Laces', 5),
(381, 164, 'Color', 'Black', 6),
(382, 165, 'Upper', 'Mesh', 1),
(383, 165, 'Midsole', 'ULTRA GO', 2),
(384, 165, 'Insole', 'Air-Cooled Goga Mat', 3),
(385, 165, 'Outsole', 'Rubber', 4),
(386, 165, 'Closure', 'Slip-On', 5),
(387, 165, 'Weight', '200g', 6),
(388, 166, 'Upper', 'Suede + Mesh', 1),
(389, 166, 'Midsole', 'ENCAP', 2),
(390, 166, 'Outsole', 'Rubber', 3),
(391, 166, 'Closure', 'Lace-Up', 4),
(392, 166, 'Style', 'Retro Running', 5),
(393, 166, 'Weight', '310g', 6),
(394, 167, 'Material', 'Croslite Foam', 1),
(395, 167, 'Ventilation', 'Yes, Upper Ports', 2),
(396, 167, 'Closure', 'Slip-On + Heel Strap', 3),
(397, 167, 'Weight', '170g', 4),
(398, 167, 'Use', 'Casual, Kitchen, Beach', 5),
(399, 167, 'Waterproof', 'Yes', 6),
(400, 168, 'Upper', 'Premium Leather', 1),
(401, 168, 'Insole', 'Cushion Plus', 2),
(402, 168, 'Sole', 'Rubber', 3),
(403, 168, 'Closure', 'Lace-Up', 4),
(404, 168, 'Style', 'Formal, Smart Casual', 5),
(405, 168, 'Width', 'Standard', 6),
(406, 169, 'Upper', 'Soft Leather', 1),
(407, 169, 'Midsole', 'EVA Foam', 2),
(408, 169, 'Outsole', 'Rubber', 3),
(409, 169, 'Closure', 'Lace-Up', 4),
(410, 169, 'Style', 'Classic Retro', 5),
(411, 169, 'Weight', '280g', 6),
(412, 170, 'Upper', 'Engineered Mesh', 1),
(413, 170, 'Midsole', 'FF Blast Plus Eco', 2),
(414, 170, 'Technology', '4D Guidance System, GEL', 3),
(415, 170, 'Outsole', 'AHAR+', 4),
(416, 170, 'Weight', '270g', 5),
(417, 170, 'Use', 'Stability Running', 6),
(418, 171, 'Power', '750W', 1),
(419, 171, 'Jars', '3 SS Jars (Liquid, Chutney, Dry Grinding)', 2),
(420, 171, 'Speed', '3 Speed + Pulse', 3),
(421, 171, 'Blade', 'SS Multi-purpose', 4),
(422, 171, 'Protection', 'Motor Overload', 5),
(423, 171, 'Warranty', '2 Years', 6),
(424, 172, 'Power', '750W', 1),
(425, 172, 'Body', 'Stainless Steel', 2),
(426, 172, 'Jars', '4 SS Jars', 3),
(427, 172, 'Speed', '3 Speed + Pulse', 4),
(428, 172, 'Feet', 'Vacuum Suction', 5),
(429, 172, 'Warranty', '2 Years', 6),
(430, 173, 'Power', '500W', 1),
(431, 173, 'Jars', '3 SS Jars', 2),
(432, 173, 'Speed', '3 Speed', 3),
(433, 173, 'Blade', 'SS Blade', 4),
(434, 173, 'Body', 'ABS Plastic', 5),
(435, 173, 'Warranty', '2 Years', 6),
(436, 174, 'Capacity', '5 Litres', 1),
(437, 174, 'Material', 'Aluminium', 2),
(438, 174, 'Base', 'Induction Compatible', 3),
(439, 174, 'Safety', 'Safety Valve, Gasket Release', 4),
(440, 174, 'Handle', 'Bakelite', 5),
(441, 174, 'Warranty', '5 Years', 6),
(442, 175, 'Capacity', '3 Litres', 1),
(443, 175, 'Material', 'Hard-Anodized Aluminium', 2),
(444, 175, 'Handle', 'Stay-Cool Nylon', 3),
(445, 175, 'Base', 'Flat, Induction Safe', 4),
(446, 175, 'Lid', 'Tight-fitting', 5),
(447, 175, 'Warranty', '5 Years', 6),
(448, 176, 'Capacity', '1 Litre', 1),
(449, 176, 'Material', 'SS 304 Stainless Steel', 2),
(450, 176, 'Insulation', 'Double Wall Vacuum', 3),
(451, 176, 'Keep Hot', '24 Hours', 4),
(452, 176, 'Keep Cold', '24 Hours', 5),
(453, 176, 'Lid', 'Cup Lid', 6),
(454, 177, 'Set', '4 Containers', 1),
(455, 177, 'Material', 'Borosilicate Glass', 2),
(456, 177, 'Lid', 'Airtight Silicone Clip', 3),
(457, 177, 'Microwave', 'Glass body only', 4),
(458, 177, 'Dishwasher', 'Yes', 5),
(459, 177, 'Capacity', 'Various sizes', 6),
(460, 178, 'Power', '1600W', 1),
(461, 178, 'Menu', 'Indian Preset Menus', 2),
(462, 178, 'Control', 'Push Button', 3),
(463, 178, 'Pan Sensor', 'Yes', 4),
(464, 178, 'Auto Voltage', 'AVR', 5),
(465, 178, 'Timer', 'Yes', 6),
(466, 179, 'Capacity', '2 Litres', 1),
(467, 179, 'Element', 'Incoloy 800', 2),
(468, 179, 'Rating', '5 Star BEE', 3),
(469, 179, 'Safety', 'Multi-function-valve', 4),
(470, 179, 'Tank', 'Rust Proof Inner', 5),
(471, 179, 'Warranty', '5 Years on Tank', 6),
(472, 180, 'Capacity', '4.1 Litres', 1),
(473, 180, 'Technology', 'Rapid Air', 2),
(474, 180, 'Fat Reduction', 'Up to 90%', 3),
(475, 180, 'Control', 'Twin TurboStar', 4),
(476, 180, 'Dishwasher', 'Parts', 5),
(477, 180, 'Warranty', '2 Years', 6),
(478, 181, 'Power', '700W', 1),
(479, 181, 'Plates', 'Non-Stick Coated', 2),
(480, 181, 'Handle', 'Cool-Touch', 3),
(481, 181, 'Indicator', 'Power ON Light', 4),
(482, 181, 'Make', '2 Sandwiches', 5),
(483, 181, 'Warranty', '2 Years', 6),
(484, 182, 'Burners', '3 Brass Burners', 1),
(485, 182, 'Body', 'Powder Coated MS', 2),
(486, 182, 'Pan Support', '3 SS Drip Trays', 3),
(487, 182, 'Ignition', 'Manual', 4),
(488, 182, 'Toughened Glass', 'No', 5),
(489, 182, 'Warranty', '2 Years', 6),
(490, 183, 'Pieces', '30', 1),
(491, 183, 'Material', 'Opalware', 2),
(492, 183, 'Pattern', 'Floral', 3),
(493, 183, 'Microwave', 'Safe', 4),
(494, 183, 'Dishwasher', 'Safe', 5),
(495, 183, 'Break Resistant', 'Yes', 6),
(496, 184, 'Pieces', '5 (Tawa, Fry Pan, Kadhai, Sauce Pan, Lid)', 1),
(497, 184, 'Coating', '3-Layer Non-Stick', 2),
(498, 184, 'Handle', 'Soft-Touch Bakelite', 3),
(499, 184, 'Base', 'Induction Compatible', 4),
(500, 184, 'Dishwasher', 'Hand Wash', 5),
(501, 184, 'Warranty', '2 Years', 6),
(502, 185, 'Thickness', '8mm', 1),
(503, 185, 'Material', 'NBR Foam', 2),
(504, 185, 'Size', '183 x 61 cm', 3),
(505, 185, 'Non-Slip', 'Yes, Both Sides', 4),
(506, 185, 'Weight', '800g', 5),
(507, 185, 'Includes', 'Carrying Strap', 6),
(508, 186, 'Total Weight', '20kg', 1),
(509, 186, 'Material', 'Cast Iron Plates, Chrome Handle', 2),
(510, 186, 'Increments', '1kg to 10kg per dumbbell', 3),
(511, 186, 'Grip', 'Knurled Chrome', 4),
(512, 186, 'Collar', 'Star Lock', 5),
(513, 186, 'Set', '2 Dumbbells + Plates', 6),
(514, 187, 'Bands', '5 Loop Bands', 1),
(515, 187, 'Resistance', 'X-Light to X-Heavy', 2),
(516, 187, 'Material', 'Natural Latex', 3),
(517, 187, 'Length', '60cm each', 4),
(518, 187, 'Use', 'Full Body, Rehab', 5),
(519, 187, 'Includes', 'Carry Bag, Guide', 6),
(520, 188, 'Motor', '4HP Peak', 1),
(521, 188, 'Speed', '0.8-12 km/h', 2),
(522, 188, 'Running Area', '120 x 42cm', 3),
(523, 188, 'Display', 'LCD (Speed, Time, Cal, Distance)', 4),
(524, 188, 'Foldable', 'Yes', 5),
(525, 188, 'Max User', '110kg', 6),
(526, 189, 'Positions', '6 (Decline to 90° Incline)', 1),
(527, 189, 'Padding', 'High-Density Foam', 2),
(528, 189, 'Frame', 'Heavy-Duty Steel', 3),
(529, 189, 'Max Load', '250kg', 4),
(530, 189, 'Foldable', 'Yes', 5),
(531, 189, 'Use', 'Chest, Shoulder, Arm', 6),
(532, 190, 'Size', '5 (Official)', 1),
(533, 190, 'Panels', '32', 2),
(534, 190, 'Stitching', 'Machine', 3),
(535, 190, 'Material', 'Rubber', 4),
(536, 190, 'Bladder', 'Butyl', 5),
(537, 190, 'Weight', '410-450g', 6),
(538, 191, 'Weight', '85g (U)', 1),
(539, 191, 'Head Shape', 'Isometric', 2),
(540, 191, 'Shaft', 'Medium Flex', 3),
(541, 191, 'Material', 'Graphite + Nanomesh', 4),
(542, 191, 'String tension', '20-28 lbs', 5),
(543, 191, 'Balance', 'Head Light', 6),
(544, 192, 'Pack', '6 Balls', 1),
(545, 192, 'Type', 'Hard Tennis Ball', 2),
(546, 192, 'Material', 'Rubber + Wool Felt', 3),
(547, 192, 'Bounce', 'Consistent', 4),
(548, 192, 'Use', 'Tennis Cricket, Practice', 5),
(549, 192, 'Size', 'Standard', 6),
(550, 193, 'Rungs', 'Heavy-Duty Plastic', 1),
(551, 193, 'Spacing', '15-46 inch adjustable', 2),
(552, 193, 'Strap', 'Nylon', 3),
(553, 193, 'Bag', 'Carrying Bag Included', 4),
(554, 193, 'Use', 'Speed, Agility, Footwork', 5),
(555, 194, 'Length', 'Adjustable up to 3m', 1),
(556, 194, 'Handle', 'Anti-Slip PVC + Steel', 2),
(557, 194, 'Bearing', 'Dual Ball Bearing', 3),
(558, 194, 'Wire', 'Steel Cable + PVC Coating', 4),
(559, 194, 'Weight', '300g', 5),
(560, 194, 'Use', 'Cardio, HIIT, Boxing', 6),
(561, 195, 'SPF', '20', 1),
(562, 195, 'Finish', 'Dewy, Natural', 2),
(563, 195, 'Coverage', 'Light to Medium', 3),
(564, 195, 'Type', 'Serum Foundation', 4),
(565, 195, 'Shades', 'Multiple', 5),
(566, 195, 'Size', '25ml', 6),
(567, 196, 'Finish', 'Matte', 1),
(568, 196, 'Oil-Free', 'Yes', 2),
(569, 196, 'SPF', '32', 3),
(570, 196, 'Coverage', 'Light', 4),
(571, 196, 'Shades', 'Multiple', 5),
(572, 196, 'Size', '6g', 6),
(573, 197, 'Key Ingredient', '24K Gold, Saffron, Vetiver', 1),
(574, 197, 'Type', 'Face Serum', 2),
(575, 197, 'Skin Type', 'All', 3),
(576, 197, 'Concern', 'Anti-Aging, Brightening', 4),
(577, 197, 'Size', '40ml', 5),
(578, 197, 'Ayurvedic', 'Yes', 6),
(579, 198, 'Key Ingredient', 'Vitamin C, Turmeric', 1),
(580, 198, 'Type', 'Face Wash', 2),
(581, 198, 'Skin Type', 'All, Dull Skin', 3),
(582, 198, 'Sulfate-Free', 'Yes', 4),
(583, 198, 'Paraben-Free', 'Yes', 5),
(584, 198, 'Size', '150ml', 6),
(585, 199, 'Key Ingredient', 'Activated Charcoal', 1),
(586, 199, 'Type', 'Peel-Off Mask', 2),
(587, 199, 'Skin Type', 'Oily, Combination', 3),
(588, 199, 'Concern', 'Blackheads, Detox', 4),
(589, 199, 'Size', '100ml', 5),
(590, 199, 'Gender', 'Unisex', 6),
(591, 200, 'Key Ingredient', 'Hyaluronic Acid', 1),
(592, 200, 'Type', 'Face Serum', 2),
(593, 200, 'Hydration', 'Up to 72 hours', 3),
(594, 200, 'Skin Type', 'All, Dry Skin', 4),
(595, 200, 'Texture', 'Lightweight, Non-Greasy', 5),
(596, 200, 'Size', '30ml', 6),
(597, 201, 'Finish', 'Matte', 1),
(598, 201, 'Texture', 'Creamy', 2),
(599, 201, 'Longevity', 'Up to 8 hours', 3),
(600, 201, 'Shades', 'Multiple', 4),
(601, 201, 'Vitamin E', 'Yes', 5),
(602, 201, 'Cruelty-Free', 'Yes', 6),
(603, 202, 'Key Ingredient', 'Almond, Turmeric, Saffron', 1),
(604, 202, 'Type', 'Under-Eye Cream', 2),
(605, 202, 'Use', 'Overnight', 3),
(606, 202, 'Concern', 'Dark Circles, Fine Lines', 4),
(607, 202, 'Ayurvedic', 'Yes', 5),
(608, 202, 'Size', '16g', 6),
(609, 203, 'Key Ingredient', 'Green Tea, Glycolic Acid', 1),
(610, 203, 'Type', 'Clay Mask', 2),
(611, 203, 'Skin Type', 'Oily, Acne-Prone', 3),
(612, 203, 'Concern', 'Acne, Oil Control', 4),
(613, 203, 'Paraben-Free', 'Yes', 5),
(614, 203, 'Size', '60ml', 6),
(615, 204, 'Key Ingredient', 'Apple Cider Vinegar', 1),
(616, 204, 'Type', 'Shampoo', 2),
(617, 204, 'Hair Type', 'All, Dandruff-Prone', 3),
(618, 204, 'Sulfate-Free', 'Yes', 4),
(619, 204, 'Paraben-Free', 'Yes', 5),
(620, 204, 'Size', '300ml', 6);

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `product_id` int(10) UNSIGNED NOT NULL,
  `rating` tinyint(3) UNSIGNED NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `comment` text DEFAULT NULL,
  `is_approved` tinyint(1) NOT NULL DEFAULT 0,
  `admin_reply` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ;

--
-- Triggers `reviews`
--
DELIMITER $$
CREATE TRIGGER `trg_reviews_after_delete` AFTER DELETE ON `reviews` FOR EACH ROW BEGIN
    UPDATE products SET
        rating = (SELECT ROUND(AVG(rating), 2) FROM reviews WHERE product_id = OLD.product_id AND is_approved = 1),
        reviews_count = (SELECT COUNT(*) FROM reviews WHERE product_id = OLD.product_id AND is_approved = 1)
    WHERE id = OLD.product_id;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_reviews_after_insert` AFTER INSERT ON `reviews` FOR EACH ROW BEGIN
    UPDATE products SET
        rating = (SELECT ROUND(AVG(rating), 2) FROM reviews WHERE product_id = NEW.product_id AND is_approved = 1),
        reviews_count = (SELECT COUNT(*) FROM reviews WHERE product_id = NEW.product_id AND is_approved = 1)
    WHERE id = NEW.product_id;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_reviews_after_update` AFTER UPDATE ON `reviews` FOR EACH ROW BEGIN
    UPDATE products SET
        rating = (SELECT ROUND(AVG(rating), 2) FROM reviews WHERE product_id = NEW.product_id AND is_approved = 1),
        reviews_count = (SELECT COUNT(*) FROM reviews WHERE product_id = NEW.product_id AND is_approved = 1)
    WHERE id = NEW.product_id;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` int(10) UNSIGNED NOT NULL,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text NOT NULL,
  `setting_group` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `setting_key`, `setting_value`, `setting_group`, `created_at`, `updated_at`) VALUES
(1, 'site_name', 'AI Shopping', 'general', '2026-07-17 14:29:28', '2026-07-17 14:29:28'),
(2, 'logo', 'assets/images/logo.png', 'general', '2026-07-17 14:29:28', '2026-07-17 14:29:28'),
(3, 'contact_email', 'support@aishoping.in', 'contact', '2026-07-17 14:29:28', '2026-07-17 14:29:28'),
(4, 'phone', '9922396767', 'contact', '2026-07-17 14:29:28', '2026-07-25 18:35:56'),
(5, 'address', 'zeal college narhi', 'contact', '2026-07-17 14:29:28', '2026-07-25 18:35:56'),
(6, 'facebook_url', 'https://facebook.com/aishoping', 'social', '2026-07-17 14:29:28', '2026-07-17 14:29:28'),
(7, 'twitter_url', 'https://twitter.com/aishoping', 'social', '2026-07-17 14:29:28', '2026-07-17 14:29:28'),
(8, 'instagram_url', 'https://instagram.com/aishoping', 'social', '2026-07-17 14:29:28', '2026-07-17 14:29:28'),
(9, 'youtube_url', 'https://youtube.com/@aishoping', 'social', '2026-07-17 14:29:28', '2026-07-17 14:29:28'),
(10, 'currency', 'INR', 'general', '2026-07-17 14:29:28', '2026-07-17 14:29:28'),
(11, 'currency_symbol', '₹', 'general', '2026-07-17 14:29:28', '2026-07-17 14:29:28'),
(12, 'footer_text', '© 2025 AI Shopping. All rights reserved.', 'general', '2026-07-17 14:29:28', '2026-07-17 14:29:28');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `state` varchar(100) DEFAULT NULL,
  `pincode` varchar(10) DEFAULT NULL,
  `status` enum('active','blocked') NOT NULL DEFAULT 'active',
  `email_verified` tinyint(1) NOT NULL DEFAULT 0,
  `verification_token` varchar(255) DEFAULT NULL,
  `reset_token` varchar(255) DEFAULT NULL,
  `remember_token` varchar(255) DEFAULT NULL,
  `reset_token_expires` datetime DEFAULT NULL,
  `reset_expires` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `last_login` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `phone`, `avatar`, `address`, `city`, `state`, `pincode`, `status`, `email_verified`, `verification_token`, `reset_token`, `remember_token`, `reset_token_expires`, `reset_expires`, `created_at`, `updated_at`, `last_login`) VALUES
(6, 'Rahul Veer', 'rahulveer@gmail.com', '$2y$10$bd47/fAZqc8S8nw9rehQEutCygt5k/Uq6mgelhbJwxJnvML2UgKY2', '9922396767', NULL, NULL, NULL, NULL, NULL, 'active', 0, '0838707E-68E4-7861-DEC4-A00003F6100C', NULL, NULL, NULL, NULL, '2026-07-18 09:22:56', '2026-07-18 09:22:56', NULL),
(7, 'Test User', 'user@example.com', '$2y$10$CRFSVHGWAE5M874ezFkJrewmuXt3TzVoS.iGWuaBbRbRK7aUd9e.C', '9876543210', NULL, NULL, NULL, NULL, NULL, 'active', 0, 'BEF86F53-7685-D33C-4226-C6D22F59D473', NULL, NULL, NULL, NULL, '2026-07-19 09:12:01', '2026-07-19 09:12:01', NULL),
(8, 'Rahul tatyasaheb Veer', 'rahulveer12@gmail.com', '$2y$10$AzXvG9WM1TptL3Zx1aDQour8xermanZxEpwoCbLGa2Wdg4LRWFl4S', '9922396767', NULL, NULL, NULL, NULL, NULL, 'active', 0, 'F28C6407-1E1A-233B-F588-0A967268AB79', NULL, NULL, NULL, NULL, '2026-07-19 09:25:28', '2026-07-29 09:31:16', '2026-07-29 15:01:16');

--
-- Triggers `users`
--
DELIMITER $$
CREATE TRIGGER `trg_users_before_insert` BEFORE INSERT ON `users` FOR EACH ROW BEGIN
    IF NEW.verification_token IS NULL THEN
        SET NEW.verification_token = UPPER(CONCAT(
            SUBSTRING(MD5(RAND()),1,8), '-',
            SUBSTRING(MD5(RAND()),1,4), '-',
            SUBSTRING(MD5(RAND()),1,4), '-',
            SUBSTRING(MD5(RAND()),1,4), '-',
            SUBSTRING(MD5(RAND()),1,12)
        ));
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `wishlist`
--

CREATE TABLE `wishlist` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `product_id` int(10) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `addresses`
--
ALTER TABLE `addresses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `addresses_user_id_index` (`user_id`);

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `admins_email_unique` (`email`),
  ADD KEY `admins_role_index` (`role`),
  ADD KEY `admins_status_index` (`status`);

--
-- Indexes for table `banners`
--
ALTER TABLE `banners`
  ADD PRIMARY KEY (`id`),
  ADD KEY `banners_type_index` (`type`),
  ADD KEY `banners_status_index` (`status`);

--
-- Indexes for table `brands`
--
ALTER TABLE `brands`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `brands_slug_unique` (`slug`),
  ADD KEY `brands_status_index` (`status`);

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cart_user_id_index` (`user_id`),
  ADD KEY `cart_session_id_index` (`session_id`),
  ADD KEY `cart_product_id_index` (`product_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `categories_slug_unique` (`slug`),
  ADD KEY `categories_parent_id_index` (`parent_id`),
  ADD KEY `categories_status_index` (`status`);

--
-- Indexes for table `coupons`
--
ALTER TABLE `coupons`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `coupons_code_unique` (`code`),
  ADD KEY `coupons_status_index` (`status`);

--
-- Indexes for table `flight_bookings`
--
ALTER TABLE `flight_bookings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `booking_ref_unique` (`booking_ref`),
  ADD KEY `flight_bookings_user_id_index` (`user_id`),
  ADD KEY `flight_bookings_payment_status_index` (`payment_status`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `notifications_user_id_index` (`user_id`),
  ADD KEY `notifications_is_read_index` (`is_read`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `orders_order_number_unique` (`order_number`),
  ADD KEY `orders_user_id_index` (`user_id`),
  ADD KEY `orders_order_status_index` (`order_status`),
  ADD KEY `orders_payment_status_index` (`payment_status`),
  ADD KEY `orders_created_at_index` (`created_at`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_items_order_id_index` (`order_id`),
  ADD KEY `order_items_product_id_index` (`product_id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `payments_order_id_index` (`order_id`),
  ADD KEY `payments_user_id_index` (`user_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `products_slug_unique` (`slug`),
  ADD UNIQUE KEY `products_sku_unique` (`sku`),
  ADD KEY `products_category_id_index` (`category_id`),
  ADD KEY `products_brand_id_index` (`brand_id`),
  ADD KEY `products_status_index` (`status`),
  ADD KEY `products_is_featured_index` (`is_featured`),
  ADD KEY `products_is_trending_index` (`is_trending`),
  ADD KEY `products_is_bestseller_index` (`is_bestseller`),
  ADD KEY `products_is_flash_sale_index` (`is_flash_sale`),
  ADD KEY `products_price_index` (`price`);
ALTER TABLE `products` ADD FULLTEXT KEY `products_search_fulltext` (`name`,`description`,`short_description`);

--
-- Indexes for table `product_images`
--
ALTER TABLE `product_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_images_product_id_index` (`product_id`);

--
-- Indexes for table `product_specifications`
--
ALTER TABLE `product_specifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_specifications_product_id_index` (`product_id`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `reviews_user_id_index` (`user_id`),
  ADD KEY `reviews_product_id_index` (`product_id`),
  ADD KEY `reviews_is_approved_index` (`is_approved`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `settings_setting_key_unique` (`setting_key`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`),
  ADD KEY `users_status_index` (`status`),
  ADD KEY `users_email_verified_index` (`email_verified`);

--
-- Indexes for table `wishlist`
--
ALTER TABLE `wishlist`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `wishlist_user_product_unique` (`user_id`,`product_id`),
  ADD KEY `wishlist_product_id_index` (`product_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `addresses`
--
ALTER TABLE `addresses`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `banners`
--
ALTER TABLE `banners`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `brands`
--
ALTER TABLE `brands`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=78;

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=99;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `coupons`
--
ALTER TABLE `coupons`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `flight_bookings`
--
ALTER TABLE `flight_bookings`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=205;

--
-- AUTO_INCREMENT for table `product_images`
--
ALTER TABLE `product_images`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=251;

--
-- AUTO_INCREMENT for table `product_specifications`
--
ALTER TABLE `product_specifications`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=621;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `wishlist`
--
ALTER TABLE `wishlist`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `addresses`
--
ALTER TABLE `addresses`
  ADD CONSTRAINT `fk_addresses_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `fk_cart_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_cart_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `categories`
--
ALTER TABLE `categories`
  ADD CONSTRAINT `fk_categories_parent` FOREIGN KEY (`parent_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `flight_bookings`
--
ALTER TABLE `flight_bookings`
  ADD CONSTRAINT `fk_flight_bookings_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `fk_notifications_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `fk_orders_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `fk_order_items_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_order_items_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `fk_payments_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_payments_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `fk_products_brand` FOREIGN KEY (`brand_id`) REFERENCES `brands` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_products_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `product_images`
--
ALTER TABLE `product_images`
  ADD CONSTRAINT `fk_product_images_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `product_specifications`
--
ALTER TABLE `product_specifications`
  ADD CONSTRAINT `fk_product_specifications_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `fk_reviews_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_reviews_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `wishlist`
--
ALTER TABLE `wishlist`
  ADD CONSTRAINT `fk_wishlist_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_wishlist_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
