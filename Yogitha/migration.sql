-- ============================================================================
-- Migration Script: Tourist Guide & Destination Information Portal
-- Adds 11 New Features, 9 New Tables, Region Categorization & Seed Data
-- Database Target: tourist_portal
-- Compatible with phpMyAdmin and standard MySQL CLI imports
-- ============================================================================

USE `tourist_portal`;

-- ----------------------------------------------------------------------------
-- 1. Region Type Column Migration for Destinations Table
-- ----------------------------------------------------------------------------
-- Safely add region_type ENUM column if not present
SET @dbname = DATABASE();
SET @tablename = "destinations";
SET @columnname = "region_type";
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      TABLE_SCHEMA = @dbname
      AND TABLE_NAME = @tablename
      AND COLUMN_NAME = @columnname
  ) > 0,
  "SELECT 1",
  "ALTER TABLE `destinations` ADD COLUMN `region_type` ENUM('National', 'International') NOT NULL DEFAULT 'National' AFTER `continent`;"
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Update existing destinations classification
UPDATE `destinations` SET `region_type` = 'International' WHERE `country` NOT IN ('India');
UPDATE `destinations` SET `region_type` = 'National' WHERE `country` = 'India';

-- ----------------------------------------------------------------------------
-- 2. Table Structure: `packages`
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `packages` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(150) NOT NULL,
  `description` TEXT NOT NULL,
  `duration_days` INT NOT NULL DEFAULT 1,
  `price` DECIMAL(10,2) NOT NULL,
  `destination_id` INT DEFAULT NULL,
  `image` VARCHAR(255) DEFAULT 'default_destination.jpg',
  `inclusions` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`destination_id`) REFERENCES `destinations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------------------------------------------------------
-- 3. Table Structure: `offers`
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `offers` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(150) NOT NULL,
  `description` TEXT NOT NULL,
  `discount_type` ENUM('percent', 'flat') NOT NULL DEFAULT 'percent',
  `discount_value` DECIMAL(10,2) NOT NULL,
  `valid_from` DATE NOT NULL,
  `valid_to` DATE NOT NULL,
  `applicable_to` VARCHAR(100) NOT NULL DEFAULT 'all',
  `code` VARCHAR(50) NOT NULL UNIQUE,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------------------------------------------------------
-- 4. Table Structure: `restaurants`
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `restaurants` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(150) NOT NULL,
  `destination_id` INT NOT NULL,
  `cuisine_type` VARCHAR(100) NOT NULL,
  `price_range` VARCHAR(50) NOT NULL DEFAULT 'Mid-Range',
  `rating` DECIMAL(3,2) DEFAULT 4.50,
  `image` VARCHAR(255) DEFAULT 'default_destination.jpg',
  `contact` VARCHAR(100) DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`destination_id`) REFERENCES `destinations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------------------------------------------------------
-- 5. Table Structure: `hotels`
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `hotels` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(150) NOT NULL,
  `destination_id` INT NOT NULL,
  `star_rating` TINYINT NOT NULL DEFAULT 3,
  `price_per_night` DECIMAL(10,2) NOT NULL,
  `amenities` TEXT DEFAULT NULL,
  `image` VARCHAR(255) DEFAULT 'default_destination.jpg',
  `contact` VARCHAR(100) DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`destination_id`) REFERENCES `destinations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------------------------------------------------------
-- 6. Table Structure: `nearby_places`
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `nearby_places` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `destination_id` INT NOT NULL,
  `name` VARCHAR(150) NOT NULL,
  `description` TEXT NOT NULL,
  `image` VARCHAR(255) DEFAULT 'default_destination.jpg',
  `distance_km` DECIMAL(5,2) DEFAULT 0.00,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`destination_id`) REFERENCES `destinations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------------------------------------------------------
-- 7. Table Structure: `guides`
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `guides` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(150) NOT NULL,
  `destination_id` INT NOT NULL,
  `photo` VARCHAR(255) DEFAULT 'default_destination.jpg',
  `languages` VARCHAR(150) NOT NULL,
  `experience_years` INT NOT NULL DEFAULT 1,
  `contact_number` VARCHAR(50) NOT NULL,
  `email` VARCHAR(100) NOT NULL,
  `rating` DECIMAL(3,2) DEFAULT 4.80,
  `price_per_day` DECIMAL(10,2) DEFAULT 1500.00,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`destination_id`) REFERENCES `destinations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------------------------------------------------------
-- 8. Table Structure: `bookings`
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `bookings` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `booking_number` VARCHAR(50) NOT NULL UNIQUE,
  `user_id` INT NOT NULL,
  `booking_type` ENUM('package', 'hotel', 'guide') NOT NULL,
  `reference_id` INT NOT NULL,
  `check_in` DATE NOT NULL,
  `check_out` DATE DEFAULT NULL,
  `guests` INT DEFAULT 1,
  `rooms` INT DEFAULT 1,
  `total_price` DECIMAL(10,2) NOT NULL,
  `offer_id` INT DEFAULT NULL,
  `status` ENUM('pending', 'confirmed', 'cancelled') DEFAULT 'pending',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`offer_id`) REFERENCES `offers` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------------------------------------------------------
-- 9. Table Structure: `notifications`
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `notifications` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `message` TEXT NOT NULL,
  `type` VARCHAR(50) DEFAULT 'info',
  `is_read` TINYINT(1) DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------------------------------------------------------
-- 10. Table Structure: `faqs`
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `faqs` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `question` TEXT NOT NULL,
  `answer` TEXT NOT NULL,
  `display_order` INT DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================================
-- SEED DATA INSERTIONS (INSERT IGNORE / ON DUPLICATE KEY to avoid duplication)
-- ============================================================================

-- ----------------------------------------------------------------------------
-- Seed Data: 22 Additional Indian Destinations (National)
-- ----------------------------------------------------------------------------
INSERT INTO `destinations` (`id`, `name`, `country`, `continent`, `region_type`, `category_id`, `description`, `best_time_to_visit`, `image`, `rating`, `price_range`, `latitude`, `longitude`, `nearby_attractions`) VALUES
(16, 'Shimla', 'India', 'Asia', 'National', 1, 'Shimla is the capital of Himachal Pradesh, famous for its colonial architecture, Mall Road, snow-capped mountains, and historic Kalka-Shimla toy train ride.', 'March to June & Dec to Feb', 'https://images.unsplash.com/photo-1597074866923-dc0589150358?auto=format&fit=crop&w=800&q=80', 4.60, 'Budget', 31.10480000, 77.17340000, 'The Ridge, Mall Road, Jakhoo Temple, Kufri'),
(17, 'Munnar', 'India', 'Asia', 'National', 1, 'Munnar is a scenic hill station in Kerala famous for its endless tea plantations, misty hills, exotic wildlife in Eravikulam National Park, and pleasant climate.', 'September to March', 'https://images.unsplash.com/photo-1593693397690-362cb9666fc2?auto=format&fit=crop&w=800&q=80', 4.75, 'Mid-Range', 10.08890000, 77.05950000, 'Tea Museum, Mattupetty Dam, Anamudi Peak, Eravikulam National Park'),
(18, 'Ooty', 'India', 'Asia', 'National', 1, 'Known as the Queen of Hill Stations in Tamil Nadu, Ooty features lush Nilgiri mountains, botanical gardens, serene lakes, and colonial charm.', 'October to June', 'https://images.unsplash.com/photo-1589182373726-e4f658ab50f0?auto=format&fit=crop&w=800&q=80', 4.50, 'Budget', 11.41020000, 76.69500000, 'Ooty Lake, Botanical Gardens, Doddabetta Peak, Rose Garden'),
(19, 'Darjeeling', 'India', 'Asia', 'National', 1, 'Darjeeling is renowned worldwide for its aromatic black tea, views of Mount Kanchenjunga, and the UNESCO Heritage Himalayan Railway toy train.', 'April to June & Oct to Dec', 'https://images.unsplash.com/photo-1544735716-392fe2489ffa?auto=format&fit=crop&w=800&q=80', 4.65, 'Mid-Range', 27.04100000, 88.26630000, 'Tiger Hill, Batasia Loop, Peace Pagoda, Rock Garden'),
(20, 'Goa', 'India', 'Asia', 'National', 2, 'Goa is India’s beach paradise featuring golden sands, lively nightlife, Portuguese heritage churches, water sports, and delicious seafood.', 'November to February', 'https://images.unsplash.com/photo-1512343879784-a960bf40e7f2?auto=format&fit=crop&w=800&q=80', 4.85, 'Mid-Range', 15.29930000, 74.12400000, 'Baga Beach, Calangute, Aguada Fort, Basilica of Bom Jesus'),
(21, 'Varkala', 'India', 'Asia', 'National', 2, 'Varkala in Kerala is a coastal town known for its unique red sandstone cliffs overlooking the Arabian Sea, mineral springs, and peaceful beaches.', 'October to March', 'https://images.unsplash.com/photo-1590050752117-238cb0fb12b1?auto=format&fit=crop&w=800&q=80', 4.60, 'Budget', 8.73790000, 76.71630000, 'Varkala Cliff, Papanasam Beach, Janardhanaswamy Temple, Kappil Lake'),
(22, 'Gokarna', 'India', 'Asia', 'National', 2, 'Gokarna in Karnataka is a pristine coastal town famous for its crescent beaches, laid-back vibe, and sacred Mahabaleshwar Temple.', 'October to March', 'https://images.unsplash.com/photo-1600100397608-f010e423b971?auto=format&fit=crop&w=800&q=80', 4.55, 'Budget', 14.54790000, 74.31880000, 'Om Beach, Kudle Beach, Half Moon Beach, Mahabaleshwar Temple'),
(23, 'Jaipur', 'India', 'Asia', 'National', 3, 'Jaipur, the Pink City of Rajasthan, boasts magnificent royal palaces, hilltop forts, vibrant bazaars, and rich Rajputana heritage.', 'October to March', 'https://images.unsplash.com/photo-1599661046289-e31897846e41?auto=format&fit=crop&w=800&q=80', 4.80, 'Mid-Range', 26.91240000, 75.78730000, 'Amber Palace, Hawa Mahal, City Palace, Jal Mahal'),
(24, 'Agra', 'India', 'Asia', 'National', 3, 'Agra is home to the iconic Taj Mahal, one of the Seven Wonders of the World, along with Agra Fort and Fatehpur Sikri.', 'October to March', 'https://images.unsplash.com/photo-1564507592333-c60657eea523?auto=format&fit=crop&w=800&q=80', 4.90, 'Luxury', 27.17670000, 78.00810000, 'Taj Mahal, Agra Fort, Tomb of I’timād-ud-Daulah, Mehtab Bagh'),
(25, 'Varanasi', 'India', 'Asia', 'National', 3, 'Varanasi is one of the world’s oldest continuously inhabited cities, sacred to Hindus with ancient riverbank Ghats and spiritual evening Aarti.', 'October to March', 'https://images.unsplash.com/photo-1561361513-2d000a50f0dc?auto=format&fit=crop&w=800&q=80', 4.70, 'Budget', 25.31760000, 82.97390000, 'Dashashwamedh Ghat, Kashi Vishwanath Temple, Assi Ghat, Sarnath'),
(26, 'Amritsar', 'India', 'Asia', 'National', 3, 'Amritsar in Punjab is the spiritual center of Sikhism, world-famous for the Golden Temple, Wagah Border ceremony, and delicious Punjabi cuisine.', 'October to March', 'https://images.unsplash.com/photo-1609949907687-34c82c23f2f0?auto=format&fit=crop&w=800&q=80', 4.85, 'Budget', 31.63400000, 74.87230000, 'Golden Temple, Jallianwala Bagh, Wagah Border, Partition Museum'),
(27, 'Udaipur', 'India', 'Asia', 'National', 3, 'Known as the City of Lakes, Udaipur in Rajasthan features romantic marble palaces on Lake Pichola, royal gardens, and royal heritage.', 'October to March', 'https://images.unsplash.com/photo-1615836245337-f5b9b2303f10?auto=format&fit=crop&w=800&q=80', 4.85, 'Luxury', 24.58540000, 73.71250000, 'City Palace, Lake Palace, Jagmandir, Saheliyon-ki-Bari'),
(28, 'Mysore', 'India', 'Asia', 'National', 3, 'Mysore in Karnataka is famed for its grand Mysore Palace, silk saris, sandalwood crafts, and rich cultural royal heritage.', 'October to March', 'https://images.unsplash.com/photo-1600100395164-88484a0d9b4b?auto=format&fit=crop&w=800&q=80', 4.60, 'Mid-Range', 12.29580000, 76.63940000, 'Mysore Palace, Chamundi Hill, Brindavan Gardens, Mysore Zoo'),
(29, 'Ranthambore', 'India', 'Asia', 'National', 4, 'Ranthambore National Park in Rajasthan is one of India’s premier tiger reserves, featuring wild Bengal tigers roaming amid ancient fort ruins.', 'October to June', 'https://images.unsplash.com/photo-1575550959106-5a7defe28b56?auto=format&fit=crop&w=800&q=80', 4.75, 'Luxury', 26.01730000, 76.50260000, 'Ranthambore Fort, Padam Talao, Trinetra Ganesha Temple, Jogi Mahal'),
(30, 'Jim Corbett', 'India', 'Asia', 'National', 4, 'India’s oldest national park located in Uttarakhand, famous for Royal Bengal tigers, wild elephants, river rafting, and forest safaris.', 'November to June', 'https://images.unsplash.com/photo-1549366021-9f761d450615?auto=format&fit=crop&w=800&q=80', 4.65, 'Mid-Range', 29.53000000, 78.77470000, 'Dhikala Zone, Corbett Waterfall, Garjiya Devi Temple, Kosi River'),
(31, 'Kaziranga', 'India', 'Asia', 'National', 4, 'A UNESCO World Heritage Site in Assam, Kaziranga is home to two-thirds of the world’s great one-horned rhinoceroses and dense elephant grass marshes.', 'November to April', 'https://images.unsplash.com/photo-1534567153574-2b12153a87f0?auto=format&fit=crop&w=800&q=80', 4.80, 'Mid-Range', 26.57750000, 93.17110000, 'Kaziranga Elephant Safari, Orchid Park, Kakochang Waterfalls'),
(32, 'Rishikesh', 'India', 'Asia', 'National', 5, 'Rishikesh in Uttarakhand is the Yoga Capital of the World, nestled along the holy Ganges River, offering white-water rafting, bungee jumping, and spiritual ashrams.', 'September to June', 'https://images.unsplash.com/photo-1590766940554-634a7ed41450?auto=format&fit=crop&w=800&q=80', 4.75, 'Budget', 30.08690000, 78.26760000, 'Laxman Jhula, Ram Jhula, Triveni Ghat, Beatles Ashram'),
(33, 'Leh Ladakh', 'India', 'Asia', 'National', 5, 'Ladakh is a high-altitude cold desert offering surreal landscapes, crystal-clear Pangong Lake, high mountain passes, and Buddhist monasteries.', 'May to September', 'https://images.unsplash.com/photo-1581793745862-99fde7fa73d2?auto=format&fit=crop&w=800&q=80', 4.90, 'Luxury', 34.15260000, 77.57710000, 'Pangong Tso Lake, Nubra Valley, Khardung La Pass, Thiksey Monastery'),
(34, 'Spiti Valley', 'India', 'Asia', 'National', 5, 'Spiti Valley is a remote Himalayan cold desert valley in Himachal Pradesh, famous for ancient monasteries, rugged roads, and starlit night skies.', 'June to September', 'https://images.unsplash.com/photo-1626621341517-bbf3d9990a23?auto=format&fit=crop&w=800&q=80', 4.80, 'Mid-Range', 32.24610000, 78.03490000, 'Key Monastery, Chandratal Lake, Kaza, Hikkim Post Office'),
(35, 'Mumbai', 'India', 'Asia', 'National', 6, 'Mumbai, India’s financial capital and home of Bollywood, features colonial architecture, Marine Drive skyline, bustling markets, and street food.', 'October to March', 'https://images.unsplash.com/photo-1570168007204-dfb528c6958f?auto=format&fit=crop&w=800&q=80', 4.65, 'Luxury', 18.92200000, 72.83470000, 'Gateway of India, Marine Drive, Elephanta Caves, Colaba Causeway'),
(36, 'New Delhi', 'India', 'Asia', 'National', 6, 'India’s bustling capital city blending historic monuments like Qutub Minar and Red Fort with modern shopping, government avenues, and rich food markets.', 'October to March', 'https://images.unsplash.com/photo-1587474260584-136574528ed5?auto=format&fit=crop&w=800&q=80', 4.60, 'Mid-Range', 28.61390000, 77.20900000, 'India Gate, Qutub Minar, Red Fort, Humayun’s Tomb, Lotus Temple'),
(37, 'Alleppey', 'India', 'Asia', 'National', 2, 'Alleppey (Alappuzha) in Kerala is famous for its serene backwaters, traditional luxury houseboats, palm-fringed canals, and Ayurvedic retreats.', 'September to March', 'https://images.unsplash.com/photo-1602216056096-3b40cc0c9944?auto=format&fit=crop&w=800&q=80', 4.80, 'Mid-Range', 9.49810000, 76.33880000, 'Alleppey Backwaters, Houseboat Cruise, Marari Beach, Punnamada Lake')
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`);

-- ----------------------------------------------------------------------------
-- Seed Data: Packages
-- ----------------------------------------------------------------------------
INSERT INTO `packages` (`id`, `name`, `description`, `duration_days`, `price`, `destination_id`, `image`, `inclusions`) VALUES
(1, 'Himalayan Snow Adventure & Solang Trek', 'Experience the best of Manali with snow activities at Solang Valley, hot water springs at Vashisht, and guided trekking in Parvati Valley.', 5, 14999.00, 2, 'https://images.unsplash.com/photo-1596760407111-9017f83ad7bd?auto=format&fit=crop&w=800&q=80', '3-Star Hotel Stay, Breakfast & Dinner, Airport/Station Transfer, Solang Valley Pass, Private Cab for Sightseeing'),
(2, 'Romantic Bali Beach & Culture Escape', '6 Days of paradise in Ubud and Seminyak. Includes private pool villa, floating breakfast, Nusa Penida island boat tour, and Uluwatu sunset dance.', 6, 45999.00, 3, 'https://images.unsplash.com/photo-1537996194471-e657df975ab4?auto=format&fit=crop&w=800&q=80', '5-Star Resort Villa, Breakfast, Fast Boat to Nusa Penida, Spa Massage, English Speaking Guide'),
(3, 'Parisian Lights & Louvre Museum Experience', 'Explore Paris in style! Includes Skip-the-line Eiffel Tower tickets, Seine River Dinner Cruise, and guided walking tour through Montmartre.', 4, 89999.00, 1, 'https://images.unsplash.com/photo-1502602898657-3e91760cbb34?auto=format&fit=crop&w=800&q=80', 'Luxury Boutique Hotel, Daily Breakfast, Eiffel Tower Priority Access, Seine Cruise Ticket, Museum Pass'),
(4, 'Goa Sun, Sand & Water Sports Package', '4 Days of thrilling water sports at Baga Beach, sunset catamaran cruise, heritage tour of Old Goa churches, and casino night experience.', 4, 11999.00, 20, 'https://images.unsplash.com/photo-1512343879784-a960bf40e7f2?auto=format&fit=crop&w=800&q=80', 'Beachfront Resort, Breakfast, Jet Ski & Parasailing Combo, South Goa Tour, Scooter Rental Included'),
(5, 'Royal Rajasthan Jaipur & Udaipur Tour', 'Discover royal fortresses, lake palaces, and vibrant bazaars across Jaipur and Udaipur with luxury palace hotel stays.', 6, 28999.00, 23, 'https://images.unsplash.com/photo-1599661046289-e31897846e41?auto=format&fit=crop&w=800&q=80', 'Heritage Hotel Stays, Daily Buffet Breakfast, Private Chauffeur, Amber Fort Elephant Ride, Lake Pichola Boat Ride'),
(6, 'Kerala Backwaters & Houseboat Magic', 'Glide through tranquil canals of Alleppey on a private luxury houseboat with freshly prepared Keralan cuisine.', 3, 16500.00, 37, 'https://images.unsplash.com/photo-1602216056096-3b40cc0c9944?auto=format&fit=crop&w=800&q=80', 'Private Houseboat Stay, All Meals (Breakfast/Lunch/Dinner), Ayurveda Welcome Drink, Canoe Ride'),
(7, 'Leh Ladakh High Pass & Pangong Expedition', 'An epic mountain road trip across Khardung La Pass, Nubra Valley sand dunes, camel rides, and Pangong Lake camping.', 7, 34999.00, 33, 'https://images.unsplash.com/photo-1581793745862-99fde7fa73d2?auto=format&fit=crop&w=800&q=80', '3-Star Hotel & Luxury Tents, Inner Line Permits, Oxygen Cylinder Support, Breakfast & Dinner, 4x4 SUV Transfer')
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`);

-- ----------------------------------------------------------------------------
-- Seed Data: Offers
-- ----------------------------------------------------------------------------
INSERT INTO `offers` (`id`, `title`, `description`, `discount_type`, `discount_value`, `valid_from`, `valid_to`, `applicable_to`, `code`) VALUES
(1, 'Welcome New Traveler', 'Get flat Rs. 1,500 off on your first booking across any package or hotel stay!', 'flat', 1500.00, '2026-01-01', '2026-12-31', 'all', 'WELCOME1500'),
(2, 'Summer Vacation Sale', 'Enjoy 15% instant discount on all hill station and beach packages.', 'percent', 15.00, '2026-03-01', '2026-08-31', 'package', 'SUMMER15'),
(3, 'Luxury Hotel Discount', 'Save flat Rs. 3,000 on luxury 4-star and 5-star hotel bookings.', 'flat', 3000.00, '2026-01-01', '2026-12-31', 'hotel', 'LUXURY3000'),
(4, 'Local Guide Booking Offer', 'Get 20% off certified local guide bookings when paired with any trip.', 'percent', 20.00, '2026-01-01', '2026-12-31', 'guide', 'GUIDE20')
ON DUPLICATE KEY UPDATE `code` = VALUES(`code`);

-- ----------------------------------------------------------------------------
-- Seed Data: Hotels
-- ----------------------------------------------------------------------------
INSERT INTO `hotels` (`id`, `name`, `destination_id`, `star_rating`, `price_per_night`, `amenities`, `image`, `contact`) VALUES
(1, 'The Himalayan Resort & Spa', 2, 5, 8500.00, 'Free WiFi, Heated Swimming Pool, Spa & Wellness, Mountain View Rooms, Restaurant', 'https://images.unsplash.com/photo-1566073771259-6a8506099945?auto=format&fit=crop&w=800&q=80', '+91 98765 11223'),
(2, 'Taj Fort Aguada Beach Resort', 20, 5, 14500.00, 'Private Beach, Infinity Pool, Sea View Villas, Water Sports Center, Bar', 'https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?auto=format&fit=crop&w=800&q=80', '+91 832 664 5800'),
(3, 'Rambagh Palace Jaipur', 23, 5, 24000.00, 'Heritage Suites, Royal Dining, Peacock Gardens, Butler Service, Spa', 'https://images.unsplash.com/photo-1542314831-068cd1dbfeeb?auto=format&fit=crop&w=800&q=80', '+91 141 238 5700'),
(4, 'Le Grand Paris Hotel', 1, 4, 18500.00, 'Eiffel Tower View, French Bistro, Free WiFi, Air Conditioning, Concierge', 'https://images.unsplash.com/photo-1551882547-ff40c63fe5fa?auto=format&fit=crop&w=800&q=80', '+33 1 42 68 30 00'),
(5, 'Ubud Eco Jungle Luxury Retreat', 3, 4, 9200.00, 'Infinity Jungle Pool, Organic Breakfast, Yoga Shala, Free Shuttle, Spa', 'https://images.unsplash.com/photo-1571896349842-33c89424de2d?auto=format&fit=crop&w=800&q=80', '+62 361 975 888'),
(6, 'Alleppey Backwater Lake Resort', 37, 4, 6500.00, 'Ayurvedic Massage, Lake View Rooms, Boat Pier, Seafood Restaurant, Free WiFi', 'https://images.unsplash.com/photo-1566073771259-6a8506099945?auto=format&fit=crop&w=800&q=80', '+91 477 224 3500')
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`);

-- ----------------------------------------------------------------------------
-- Seed Data: Restaurants
-- ----------------------------------------------------------------------------
INSERT INTO `restaurants` (`id`, `name`, `destination_id`, `cuisine_type`, `price_range`, `rating`, `image`, `contact`) VALUES
(1, 'Johnson’s Cafe & Bakery', 2, 'Continental & Trout Fish', 'Mid-Range', 4.70, 'https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?auto=format&fit=crop&w=800&q=80', '+91 98160 32100'),
(2, 'Thalassa Greek Restaurant', 20, 'Greek & Mediterranean', 'Luxury', 4.80, 'https://images.unsplash.com/photo-1555396273-367ea4eb4db5?auto=format&fit=crop&w=800&q=80', '+91 98500 33537'),
(3, '1135 AD Amber Palace Restaurant', 23, 'Royal Rajasthani Thali', 'Luxury', 4.85, 'https://images.unsplash.com/photo-1544025162-d76694265947?auto=format&fit=crop&w=800&q=80', '+91 141 253 0110'),
(4, 'Le Jules Verne Eiffel Tower', 1, 'French Fine Dining', 'Luxury', 4.90, 'https://images.unsplash.com/photo-1550966871-3ed3cdb5ed0c?auto=format&fit=crop&w=800&q=80', '+33 1 45 55 61 44'),
(5, 'Bebek Bengil (Dirty Duck Diner)', 3, 'Balinese Crispy Duck', 'Mid-Range', 4.65, 'https://images.unsplash.com/photo-1504674900247-0877df9cc836?auto=format&fit=crop&w=800&q=80', '+62 361 975 489')
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`);

-- ----------------------------------------------------------------------------
-- Seed Data: Nearby Places
-- ----------------------------------------------------------------------------
INSERT INTO `nearby_places` (`id`, `destination_id`, `name`, `description`, `image`, `distance_km`) VALUES
(1, 2, 'Solang Valley', 'Famous for snow sports including skiing, paragliding, snowmobiling, and ropeway rides.', 'https://images.unsplash.com/photo-1596760407111-9017f83ad7bd?auto=format&fit=crop&w=800&q=80', 13.50),
(2, 2, 'Rohtang Pass', 'High mountain pass connecting Kullu Valley with Lahaul and Spiti Valleys, known for year-round snow.', 'https://images.unsplash.com/photo-1562979314-bee7453e911c?auto=format&fit=crop&w=800&q=80', 51.00),
(3, 20, 'Aguada Fort & Lighthouse', '17th-century Portuguese fort standing on Sinquerim Beach offering sweeping views of the Arabian Sea.', 'https://images.unsplash.com/photo-1512343879784-a960bf40e7f2?auto=format&fit=crop&w=800&q=80', 8.20),
(4, 20, 'Dudhsagar Waterfalls', 'Four-tiered spectacular waterfall located on the Mandovi River amidst lush Bhagwan Mahaveer Sanctuary.', 'https://images.unsplash.com/photo-1582510003544-4d00b7f74220?auto=format&fit=crop&w=800&q=80', 60.00),
(5, 23, 'Amber Fort & Palace', 'Majestic hilltop fort built from pink and yellow sandstone featuring the stunning Sheesh Mahal (Mirror Palace).', 'https://images.unsplash.com/photo-1599661046289-e31897846e41?auto=format&fit=crop&w=800&q=80', 11.00)
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`);

-- ----------------------------------------------------------------------------
-- Seed Data: Tour Guides
-- ----------------------------------------------------------------------------
INSERT INTO `guides` (`id`, `name`, `destination_id`, `photo`, `languages`, `experience_years`, `contact_number`, `email`, `rating`, `price_per_day`) VALUES
(1, 'Vikram Sharma', 2, 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=400&q=80', 'English, Hindi, Pahari', 8, '+91 98161 22334', 'vikram.manali@gmail.com', 4.90, 1500.00),
(2, 'Maria D’Souza', 20, 'https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?auto=format&fit=crop&w=400&q=80', 'English, Hindi, Portuguese, Konkani', 6, '+91 98221 44556', 'maria.goaguide@gmail.com', 4.85, 1800.00),
(3, 'Rajesh Rathore', 23, 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&w=400&q=80', 'English, Hindi, Rajasthani, French', 12, '+91 94140 77889', 'rajesh.jaipurheritage@gmail.com', 4.95, 2000.00),
(4, 'Pierre Laurent', 1, 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&w=400&q=80', 'English, French, Spanish', 10, '+33 6 12 34 56 78', 'pierre.parisguide@gmail.com', 4.90, 4500.00)
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`);

-- ----------------------------------------------------------------------------
-- Seed Data: FAQs
-- ----------------------------------------------------------------------------
INSERT INTO `faqs` (`id`, `question`, `answer`, `display_order`) VALUES
(1, 'How do I book a tour package, hotel, or guide on TravelPortal?', 'Simply browse to your desired Package, Hotel, or Guide page, select your preferred dates and guest count, click "Book Now", apply any discount offer codes on the checkout page, and confirm your booking instantly.', 1),
(2, 'Are all tour guides verified on TravelPortal?', 'Yes! All local tour guides listed on TravelPortal undergo background checks, identity verification, and license verification before being registered.', 2),
(3, 'What is the refund and cancellation policy?', 'You can cancel any pending or confirmed booking directly from your user profile under "My Bookings". Cancellations made at least 48 hours prior to check-in/start date receive a 100% full refund.', 3),
(4, 'How do I apply a promo code for discounts?', 'During the checkout process on `checkout.php`, enter your promo code in the "Offer / Coupon Code" field and click "Apply". The total bill will automatically update with your discount.', 4),
(5, 'Do I get instant confirmation for my hotel and package bookings?', 'Yes. Upon completing checkout, your booking is placed in "Confirmed" or "Pending" status and an immediate in-app notification + receipt is generated in your user profile.', 5)
ON DUPLICATE KEY UPDATE `question` = VALUES(`question`);

-- ----------------------------------------------------------------------------
-- Additional Seed Data Expansion (Destinations, Nearby Places, Hotels, Restaurants, Guides, Reviews)
-- ----------------------------------------------------------------------------
INSERT INTO `destinations` (`id`, `name`, `country`, `continent`, `region_type`, `category_id`, `description`, `best_time_to_visit`, `image`, `rating`, `price_range`, `latitude`, `longitude`, `nearby_attractions`) VALUES
(38, 'Kyoto', 'Japan', 'Asia', 'International', 3, 'Kyoto, once the capital of Japan, is a city on the island of Honshu. It is famous for its numerous classical Buddhist temples, gardens, imperial palaces, Shinto shrines and traditional wooden houses.', 'March to May & Oct to Nov', 'https://images.unsplash.com/photo-1493976040374-85c8e12f0c0e?auto=format&fit=crop&w=800&q=80', 4.90, 'Luxury', 35.01160000, 135.76810000, 'Fushimi Inari Taisha, Arashiyama Bamboo Grove, Kinkaku-ji (Golden Pavilion), Gion District'),
(39, 'Dubai', 'UAE', 'Asia', 'International', 6, 'Dubai is a city and emirate in the United Arab Emirates known for luxury shopping, ultramodern architecture and a lively nightlife scene. Burj Khalifa, an 830m-tall tower, dominates the skyscraper-filled skyline.', 'November to April', 'https://images.unsplash.com/photo-1512453979798-5ea266f8880c?auto=format&fit=crop&w=800&q=80', 4.85, 'Luxury', 25.20480000, 55.27080000, 'Burj Khalifa, Dubai Mall, Palm Jumeirah, Desert Safari, Miracle Garden'),
(40, 'Cape Town', 'South Africa', 'Africa', 'International', 5, 'Cape Town is a port city on South Africa’s southwest coast, on a peninsula beneath the imposing Table Mountain. Slowly rotating cable cars climb to the mountain’s flat top, offering sweeping views of the city.', 'November to March', 'https://images.unsplash.com/photo-1580618672591-eb180b1a973f?auto=format&fit=crop&w=800&q=80', 4.75, 'Mid-Range', -33.92490000, 18.42410000, 'Table Mountain, Cape of Good Hope, Robben Island, Boulders Penguin Beach'),
(41, 'Coorg', 'India', 'Asia', 'National', 1, 'Coorg (Kodagu), known as the Scotland of India, is an enchanting hill station in Karnataka renowned for lush coffee plantations, misty valleys, cascades, and rich Kodava culture.', 'October to March', 'https://images.unsplash.com/photo-1593693397690-362cb9666fc2?auto=format&fit=crop&w=800&q=80', 4.70, 'Mid-Range', 12.42440000, 75.73820000, 'Abbey Falls, Raja’s Seat, Namdroling Monastery (Golden Temple), Dubare Elephant Camp'),
(42, 'Puducherry', 'India', 'Asia', 'National', 3, 'Puducherry (Pondicherry) is a French colonial settlement in India with vibrant mustard-yellow heritage villas, quiet tree-lined avenues, serene beaches, and the experimental township of Auroville.', 'October to March', 'https://images.unsplash.com/photo-1582510003544-4d00b7f74220?auto=format&fit=crop&w=800&q=80', 4.65, 'Budget', 11.94160000, 79.80830000, 'Promenade Beach, French Quarter (White Town), Auroville Matrimandir, Paradise Beach'),
(43, 'Gulmarg', 'India', 'Asia', 'National', 1, 'Gulmarg in Jammu & Kashmir is a premier ski resort town set in the Pir Panjal Range, famous for the world’s highest gondola cable car ride and pristine snow-clad alpine slopes.', 'December to March & April to June', 'https://images.unsplash.com/photo-1549366021-9f761d450615?auto=format&fit=crop&w=800&q=80', 4.88, 'Luxury', 34.04840000, 74.38050000, 'Gulmarg Gondola, Apharwat Peak, Alpather Lake, Strawberry Valley, St. Mary Church')
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`);

INSERT INTO `hotels` (`name`, `destination_id`, `star_rating`, `price_per_night`, `amenities`, `image`, `contact`) VALUES
('The Oberoi Cecil Shimla', 16, 5, 16500.00, 'Heritage Suites, Heated Indoor Pool, Spa, Valley View Dining, Kids Club', 'https://images.unsplash.com/photo-1566073771259-6a8506099945?auto=format&fit=crop&w=800&q=80', '+91 177 280 4848'),
('Cliffs & Palms Ayurvedic Beach Resort', 21, 4, 5200.00, 'Ocean View Balcony, Ayurvedic Wellness Spa, Yoga Deck, Seafood Bistro, Swimming Pool', 'https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?auto=format&fit=crop&w=800&q=80', '+91 470 260 1200'),
('Kyoto Ryokan Onsen & Spa Resort', 38, 5, 22000.00, 'Private Hot Spring Onsen, Kaiseki Dinner, Zen Garden, Tatami Suites, Free WiFi', 'https://images.unsplash.com/photo-1542314831-068cd1dbfeeb?auto=format&fit=crop&w=800&q=80', '+81 75 561 1111'),
('Atlantis The Royal Palm', 39, 5, 35000.00, 'Private Beach, Aquaventure Waterpark, Cloud 22 Sky Pool, Celebrity Chef Restaurants', 'https://images.unsplash.com/photo-1571896349842-33c89424de2d?auto=format&fit=crop&w=800&q=80', '+971 4 426 0000'),
('Evolve Back Chikka Narayana Coffee Estate Resort', 41, 5, 18500.00, 'Private Pool Villa, Plantation Walk, Reading Lounge, Ayurvedic Spa, Infinity Pool', 'https://images.unsplash.com/photo-1566073771259-6a8506099945?auto=format&fit=crop&w=800&q=80', '+91 8274 258 400'),
('La Villa Heritage Hotel French Quarter', 42, 4, 8800.00, 'Colonial Courtyard, Swimming Pool, French Fine Dining, Free Bikes, Library', 'https://images.unsplash.com/photo-1551882547-ff40c63fe5fa?auto=format&fit=crop&w=800&q=80', '+91 413 222 8555'),
('The Khyber Himalayan Resort & Spa', 43, 5, 24500.00, 'Heated Pool with Snow View, Ski Storage, L’Occitane Spa, Gondola Shuttle, Fine Dining', 'https://images.unsplash.com/photo-1542314831-068cd1dbfeeb?auto=format&fit=crop&w=800&q=80', '+91 1951 254 999');

INSERT INTO `restaurants` (`name`, `destination_id`, `cuisine_type`, `price_range`, `rating`, `image`, `contact`) VALUES
('The Devicos Restaurant & Bar', 16, 'North Indian, Chinese & Continental', 'Mid-Range', 4.60, 'https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?auto=format&fit=crop&w=800&q=80', '+91 177 280 6000'),
('Abhiba Cliffside Seafood Shack', 21, 'Kerala Seafood & Fresh Juices', 'Budget', 4.75, 'https://images.unsplash.com/photo-1555396273-367ea4eb4db5?auto=format&fit=crop&w=800&q=80', '+91 98470 55112'),
('Gion Karyo Traditional Kaiseki', 38, 'Authentic Japanese Multi-Course Kaiseki', 'Luxury', 4.95, 'https://images.unsplash.com/photo-1504674900247-0877df9cc836?auto=format&fit=crop&w=800&q=80', '+81 75 532 0025'),
('At.mosphere Burj Khalifa Level 122', 39, 'International Fine Dining', 'Luxury', 4.90, 'https://images.unsplash.com/photo-1550966871-3ed3cdb5ed0c?auto=format&fit=crop&w=800&q=80', '+971 4 888 3828'),
('Coorg Cuisine Authentic Pandi Curry', 41, 'Traditional Kodava & South Indian', 'Budget', 4.80, 'https://images.unsplash.com/photo-1544025162-d76694265947?auto=format&fit=crop&w=800&q=80', '+91 94483 12345'),
('Café des Arts French Bistro', 42, 'French Crepes, Croissants & Espresso', 'Mid-Range', 4.70, 'https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?auto=format&fit=crop&w=800&q=80', '+91 413 233 7422'),
('Nedous Dining Hall Kashmiri Wazwan', 43, 'Authentic Kashmiri Rista & Gustaba', 'Luxury', 4.85, 'https://images.unsplash.com/photo-1504674900247-0877df9cc836?auto=format&fit=crop&w=800&q=80', '+91 1951 254 222');

INSERT INTO `guides` (`name`, `destination_id`, `photo`, `languages`, `experience_years`, `contact_number`, `email`, `rating`, `price_per_day`) VALUES
('Sunil Verma', 16, 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&w=400&q=80', 'English, Hindi, Punjabi', 9, '+91 98170 33445', 'sunil.shimla@gmail.com', 4.88, 1600.00),
('Ananya Nair', 21, 'https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?auto=format&fit=crop&w=400&q=80', 'English, Malayalam, Hindi, Tamil', 5, '+91 94470 66778', 'ananya.kerala@gmail.com', 4.92, 1400.00),
('Kenji Takahashi', 38, 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&w=400&q=80', 'English, Japanese, French', 11, '+81 90 1234 5678', 'kenji.kyotoguide@gmail.com', 4.98, 4200.00),
('Zaid Al-Maktoum', 39, 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=400&q=80', 'English, Arabic, Hindi', 7, '+971 50 987 6543', 'zaid.dubaiexplorer@gmail.com', 4.85, 3800.00),
('Jean-Luc Dupont', 42, 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&w=400&q=80', 'English, French, Tamil', 10, '+91 94130 88990', 'jeanluc.pondy@gmail.com', 4.90, 1800.00),
('Tashi Namgyal', 43, 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&w=400&q=80', 'English, Ladakhi, Hindi', 8, '+91 94191 55443', 'tashi.snowguide@gmail.com', 4.95, 2000.00);

-- ============================================================================
-- End of Migration Script
-- ============================================================================
