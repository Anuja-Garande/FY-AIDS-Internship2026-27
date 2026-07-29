-- ============================================================
-- INCREDIBLE INDIA TOURISM PORTAL - DATABASE
-- Import this file in phpMyAdmin (or run via MySQL CLI)
-- ============================================================

CREATE DATABASE IF NOT EXISTS tourism_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE tourism_db;

-- ------------------------------------------------------------
-- USERS
-- ------------------------------------------------------------
CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    contact_number VARCHAR(20) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- ADMIN
-- ------------------------------------------------------------
CREATE TABLE admin (
    admin_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(150) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Default admin login -> username: admin | password: admin123
INSERT INTO admin (username, password, email) VALUES
('admin', '$2y$10$92aQ0nD3f7A9tX1lYV0R7uH8s1z4b7hV1QW8m8L1z1Q1r1w1e1x1u', 'admin@incredibleindia.local');
-- NOTE: the hash above is a placeholder. Run reset_admin_password.php (included)
-- once to set a proper bcrypt hash for 'admin123'.

-- ------------------------------------------------------------
-- STATES
-- ------------------------------------------------------------
CREATE TABLE states (
    state_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    tagline VARCHAR(200) DEFAULT NULL,
    description TEXT,
    image VARCHAR(255) DEFAULT NULL
) ENGINE=InnoDB;

INSERT INTO states (name, tagline, description, image) VALUES
('Rajasthan', 'Land of Kings', 'A royal state of forts, palaces, deserts and vibrant culture.', 'rajasthan.jpg'),
('Kerala', "God's Own Country", 'Backwaters, tea gardens and lush tropical greenery.', 'kerala.jpg'),
('Goa', 'Sun, Sand & Sea', 'Golden beaches, Portuguese heritage and a lively nightlife.', 'goa.jpg'),
('Himachal Pradesh', 'The Land of Snow', 'Snow-capped mountains, valleys and adventure sports.', 'himachal.jpg'),
('Uttarakhand', 'Land of the Gods', 'Himalayan peaks, sacred rivers and yoga retreats.', 'uttarakhand.jpg'),
('Tamil Nadu', 'Temple Land', 'Ancient temples, hill stations and coastal heritage.', 'tamilnadu.jpg'),
('Maharashtra', 'The Gateway State', 'Historic caves, hill forts and the buzz of Mumbai.', 'maharashtra.jpg'),
('Karnataka', 'One State, Many Worlds', 'Royal heritage, coffee hills and ancient ruins.', 'karnataka.jpg'),
('Uttar Pradesh', 'Heart of Incredible India', 'Home to the Taj Mahal and the ghats of Varanasi.', 'uttarpradesh.jpg'),
('Jammu and Kashmir', 'Paradise on Earth', 'Alpine lakes, meadows and the majestic Himalayas.', 'jammukashmir.jpg');

-- ------------------------------------------------------------
-- DESTINATIONS
-- ------------------------------------------------------------
CREATE TABLE destinations (
    destination_id INT AUTO_INCREMENT PRIMARY KEY,
    state_id INT NOT NULL,
    name VARCHAR(150) NOT NULL,
    category VARCHAR(50) NOT NULL,
    description TEXT,
    location VARCHAR(150) DEFAULT NULL,
    image VARCHAR(255) DEFAULT NULL,
    popularity INT DEFAULT 0,
    views INT NOT NULL DEFAULT 0,
    FOREIGN KEY (state_id) REFERENCES states(state_id) ON DELETE CASCADE
) ENGINE=InnoDB;

INSERT INTO destinations (state_id, name, category, description, location, image, popularity) VALUES
-- Rajasthan (1)
(1, 'Jaipur', 'Heritage', 'The Pink City, famed for Amber Fort, Hawa Mahal and City Palace.', 'Jaipur, Rajasthan', 'jaipur.jpg', 98),
(1, 'Udaipur', 'Heritage', 'The City of Lakes, known for its romantic palaces and lake views.', 'Udaipur, Rajasthan', 'udaipur.jpg', 93),
(1, 'Jodhpur', 'Heritage', 'The Blue City, home to the imposing Mehrangarh Fort.', 'Jodhpur, Rajasthan', 'jodhpur.jpg', 88),
(1, 'Jaisalmer', 'Desert', 'The Golden City rising from the Thar Desert sands.', 'Jaisalmer, Rajasthan', 'jaisalmer.jpg', 85),
-- Kerala (2)
(2, 'Munnar', 'Hill Station', 'Rolling tea estates and misty mountains.', 'Munnar, Kerala', 'munnar.jpg', 92),
(2, 'Alleppey', 'Backwaters', 'Houseboat cruises through tranquil backwaters.', 'Alleppey, Kerala', 'alleppey.jpg', 94),
(2, 'Kochi', 'Heritage', 'A coastal blend of colonial history and modern culture.', 'Kochi, Kerala', 'kochi.jpg', 87),
(2, 'Wayanad', 'Wildlife', 'Dense forests, waterfalls and wildlife sanctuaries.', 'Wayanad, Kerala', 'wayanad.jpg', 82),
-- Goa (3)
(3, 'Baga Beach', 'Beach', 'Golden sands and buzzing beach shacks.', 'North Goa', 'baga.jpg', 90),
(3, 'Old Goa', 'Heritage', 'Historic churches and Portuguese-era architecture.', 'Old Goa', 'oldgoa.jpg', 78),
(3, 'Anjuna', 'Beach', 'Famous for its flea markets and sunset parties.', 'North Goa', 'anjuna.jpg', 83),
(3, 'Palolem', 'Beach', 'A quiet crescent beach lined with palm trees.', 'South Goa', 'palolem.jpg', 80),
-- Himachal Pradesh (4)
(4, 'Manali', 'Hill Station', 'Snow peaks, adventure sports and riverside charm.', 'Manali, Himachal Pradesh', 'manali.jpg', 95),
(4, 'Shimla', 'Hill Station', 'Colonial charm amid pine-covered hills.', 'Shimla, Himachal Pradesh', 'shimla.jpg', 90),
(4, 'Dharamshala', 'Hill Station', 'Home to the Dalai Lama and Tibetan culture.', 'Dharamshala, Himachal Pradesh', 'dharamshala.jpg', 84),
(4, 'Spiti Valley', 'Mountain', 'A cold desert valley with monasteries and stark beauty.', 'Spiti, Himachal Pradesh', 'spiti.jpg', 86),
-- Uttarakhand (5)
(5, 'Rishikesh', 'Spiritual', 'The Yoga Capital, on the banks of the Ganges.', 'Rishikesh, Uttarakhand', 'rishikesh.jpg', 91),
(5, 'Nainital', 'Hill Station', 'A charming lake town surrounded by hills.', 'Nainital, Uttarakhand', 'nainital.jpg', 85),
(5, 'Mussoorie', 'Hill Station', 'The Queen of Hills with sweeping valley views.', 'Mussoorie, Uttarakhand', 'mussoorie.jpg', 83),
(5, 'Valley of Flowers', 'Nature', 'A vibrant national park blooming with alpine flowers.', 'Chamoli, Uttarakhand', 'valleyofflowers.jpg', 79),
-- Tamil Nadu (6)
(6, 'Ooty', 'Hill Station', 'Tea gardens and toy train rides in the Nilgiris.', 'Ooty, Tamil Nadu', 'ooty.jpg', 88),
(6, 'Kodaikanal', 'Hill Station', 'A serene lake town amid pine forests.', 'Kodaikanal, Tamil Nadu', 'kodaikanal.jpg', 82),
(6, 'Mahabalipuram', 'Heritage', 'Ancient shore temples and rock-cut monuments.', 'Mahabalipuram, Tamil Nadu', 'mahabalipuram.jpg', 80),
(6, 'Rameswaram', 'Spiritual', 'A sacred pilgrimage island town.', 'Rameswaram, Tamil Nadu', 'rameswaram.jpg', 77),
-- Maharashtra (7)
(7, 'Mumbai', 'Urban', 'The City of Dreams, Gateway of India and Marine Drive.', 'Mumbai, Maharashtra', 'mumbai.jpg', 96),
(7, 'Lonavala', 'Hill Station', 'Misty ghats, waterfalls and old forts.', 'Lonavala, Maharashtra', 'lonavala.jpg', 84),
(7, 'Ajanta-Ellora', 'Heritage', 'World-famous rock-cut cave temples and carvings.', 'Aurangabad, Maharashtra', 'ajantaellora.jpg', 86),
(7, 'Mahabaleshwar', 'Hill Station', 'Strawberry farms and scenic viewpoints.', 'Mahabaleshwar, Maharashtra', 'mahabaleshwar.jpg', 81),
-- Karnataka (8)
(8, 'Coorg', 'Hill Station', 'Coffee plantations and misty hills, the Scotland of India.', 'Coorg, Karnataka', 'coorg.jpg', 89),
(8, 'Hampi', 'Heritage', 'Ruins of the Vijayanagara Empire amid boulder landscapes.', 'Hampi, Karnataka', 'hampi.jpg', 87),
(8, 'Mysore', 'Heritage', 'Royal palaces and the grand Mysore Dasara festival.', 'Mysore, Karnataka', 'mysore.jpg', 85),
(8, 'Gokarna', 'Beach', 'Laid-back beaches and pilgrim temples.', 'Gokarna, Karnataka', 'gokarna.jpg', 78),
-- Uttar Pradesh (9)
(9, 'Agra', 'Heritage', 'Home to the iconic Taj Mahal.', 'Agra, Uttar Pradesh', 'agra.jpg', 99),
(9, 'Varanasi', 'Spiritual', 'The oldest living city, famous for its sacred ghats.', 'Varanasi, Uttar Pradesh', 'varanasi.jpg', 93),
(9, 'Lucknow', 'Heritage', 'The City of Nawabs, known for its cuisine and architecture.', 'Lucknow, Uttar Pradesh', 'lucknow.jpg', 82),
(9, 'Ayodhya', 'Spiritual', 'A significant pilgrimage town on the banks of the Sarayu.', 'Ayodhya, Uttar Pradesh', 'ayodhya.jpg', 84),
-- Jammu and Kashmir (10)
(10, 'Srinagar', 'Nature', 'Dal Lake, houseboats and Mughal gardens.', 'Srinagar, J&K', 'srinagar.jpg', 92),
(10, 'Gulmarg', 'Hill Station', 'Snow slopes and one of the highest cable cars in the world.', 'Gulmarg, J&K', 'gulmarg.jpg', 88),
(10, 'Pahalgam', 'Hill Station', 'Meadows and pine forests along the Lidder river.', 'Pahalgam, J&K', 'pahalgam.jpg', 85),
(10, 'Leh-Ladakh', 'Mountain', 'High-altitude desert landscapes and Buddhist monasteries.', 'Leh, Ladakh', 'ladakh.jpg', 90);

-- ------------------------------------------------------------
-- HOTELS
-- ------------------------------------------------------------
CREATE TABLE hotels (
    hotel_id INT AUTO_INCREMENT PRIMARY KEY,
    destination_id INT NOT NULL,
    name VARCHAR(150) NOT NULL,
    price_range VARCHAR(50) DEFAULT NULL,
    contact VARCHAR(50) DEFAULT NULL,
    amenities VARCHAR(255) DEFAULT NULL,
    image VARCHAR(255) DEFAULT NULL,
    FOREIGN KEY (destination_id) REFERENCES destinations(destination_id) ON DELETE CASCADE
) ENGINE=InnoDB;

INSERT INTO hotels (destination_id, name, price_range, contact, amenities, image) VALUES
(1, 'Rambagh Heritage Stay', '₹6,000 - ₹15,000', '+91-9000000001', 'Pool, Spa, Free WiFi', 'hotel1.jpg'),
(2, 'Lake Palace View Resort', '₹7,500 - ₹20,000', '+91-9000000002', 'Lake View, Restaurant, WiFi', 'hotel2.jpg'),
(3, 'Blue City Haveli', '₹3,500 - ₹9,000', '+91-9000000003', 'Rooftop Cafe, AC, WiFi', 'hotel3.jpg'),
(4, 'Desert Dune Camp', '₹4,000 - ₹11,000', '+91-9000000004', 'Desert Safari, Bonfire, Meals', 'hotel4.jpg'),
(5, 'Tea Valley Resort', '₹3,000 - ₹8,000', '+91-9000000005', 'Mountain View, WiFi, Breakfast', 'hotel5.jpg'),
(6, 'Backwater Houseboat Stay', '₹5,000 - ₹12,000', '+91-9000000006', 'Houseboat, Meals, Ayurveda', 'hotel6.jpg'),
(7, 'Fort Kochi Residency', '₹2,500 - ₹7,000', '+91-9000000007', 'Free WiFi, AC, Breakfast', 'hotel7.jpg'),
(9, 'Beach Shack Resort Goa', '₹2,000 - ₹6,000', '+91-9000000008', 'Beachfront, Pool, Bar', 'hotel8.jpg'),
(13, 'Snow Peak Cottages', '₹3,500 - ₹9,500', '+91-9000000009', 'Bonfire, Mountain View, WiFi', 'hotel9.jpg'),
(17, 'Ganga View Ashram Stay', '₹1,500 - ₹4,000', '+91-9000000010', 'Yoga Hall, Meals, River View', 'hotel10.jpg'),
(25, 'Marine Drive Suites', '₹6,000 - ₹18,000', '+91-9000000011', 'Sea View, Gym, WiFi', 'hotel11.jpg'),
(29, 'Coffee Estate Homestay', '₹2,800 - ₹7,500', '+91-9000000012', 'Plantation Tour, Meals, WiFi', 'hotel12.jpg'),
(33, 'Taj View Grand', '₹4,500 - ₹14,000', '+91-9000000013', 'Taj View, Pool, Restaurant', 'hotel13.jpg'),
(37, 'Dal Lake Houseboat', '₹4,000 - ₹10,000', '+91-9000000014', 'Lake View, Meals, Shikara Ride', 'hotel14.jpg');

-- ------------------------------------------------------------
-- RESTAURANTS
-- ------------------------------------------------------------
CREATE TABLE restaurants (
    restaurant_id INT AUTO_INCREMENT PRIMARY KEY,
    destination_id INT NOT NULL,
    name VARCHAR(150) NOT NULL,
    cuisine_type VARCHAR(100) DEFAULT NULL,
    price_range VARCHAR(50) DEFAULT NULL,
    contact VARCHAR(50) DEFAULT NULL,
    image VARCHAR(255) DEFAULT NULL,
    FOREIGN KEY (destination_id) REFERENCES destinations(destination_id) ON DELETE CASCADE
) ENGINE=InnoDB;

INSERT INTO restaurants (destination_id, name, cuisine_type, price_range, contact, image) VALUES
(1, 'Chokhi Dhani Thali House', 'Rajasthani', '₹500 - ₹1,200', '+91-9100000001', 'restaurant1.jpg'),
(2, 'Lake View Dining', 'Multi-cuisine', '₹700 - ₹1,800', '+91-9100000002', 'restaurant2.jpg'),
(5, 'Spice Garden Kerala Kitchen', 'Kerala', '₹300 - ₹800', '+91-9100000003', 'restaurant3.jpg'),
(6, 'Backwater Bites', 'Seafood', '₹400 - ₹1,000', '+91-9100000004', 'restaurant4.jpg'),
(9, 'Beach Shack Grill', 'Goan Seafood', '₹400 - ₹1,200', '+91-9100000005', 'restaurant5.jpg'),
(13, 'Himalayan Cafe', 'North Indian, Tibetan', '₹300 - ₹900', '+91-9100000006', 'restaurant6.jpg'),
(17, 'Ganga Ghat Cafe', 'Vegetarian', '₹200 - ₹600', '+91-9100000007', 'restaurant7.jpg'),
(25, 'Mumbai Street Food Co.', 'Street Food', '₹150 - ₹500', '+91-9100000008', 'restaurant8.jpg'),
(33, 'Petha & Paratha House', 'Mughlai', '₹300 - ₹900', '+91-9100000009', 'restaurant9.jpg'),
(37, 'Kashmiri Wazwan House', 'Kashmiri', '₹600 - ₹1,500', '+91-9100000010', 'restaurant10.jpg');

-- ------------------------------------------------------------
-- TOUR PACKAGES
-- ------------------------------------------------------------
CREATE TABLE packages (
    package_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    state_id INT DEFAULT NULL,
    duration VARCHAR(50) DEFAULT NULL,
    price DECIMAL(10,2) NOT NULL,
    itinerary TEXT,
    image VARCHAR(255) DEFAULT NULL,
    FOREIGN KEY (state_id) REFERENCES states(state_id) ON DELETE SET NULL
) ENGINE=InnoDB;

INSERT INTO packages (name, state_id, duration, price, itinerary, image) VALUES
('Royal Rajasthan Trail', 1, '6 Days / 5 Nights', 24999.00, 'Day 1: Arrive Jaipur, City Palace | Day 2: Amber Fort, Hawa Mahal | Day 3: Travel to Jodhpur | Day 4: Mehrangarh Fort | Day 5: Travel to Udaipur | Day 6: Lake Pichola & departure', 'pkg1.jpg'),
('Kerala Backwater Bliss', 2, '5 Days / 4 Nights', 21999.00, 'Day 1: Arrive Kochi | Day 2: Munnar tea gardens | Day 3: Thekkady wildlife | Day 4: Alleppey houseboat stay | Day 5: Departure', 'pkg2.jpg'),
('Goa Beach Getaway', 3, '4 Days / 3 Nights', 15999.00, 'Day 1: Arrive, Baga Beach | Day 2: Old Goa churches, Anjuna market | Day 3: Palolem beach day | Day 4: Departure', 'pkg3.jpg'),
('Himalayan Adventure', 4, '6 Days / 5 Nights', 26999.00, 'Day 1: Arrive Manali | Day 2: Solang Valley | Day 3: Travel to Shimla | Day 4: Mall Road, Jakhu Temple | Day 5: Dharamshala | Day 6: Departure', 'pkg4.jpg'),
('Rishikesh Yoga & Nature', 5, '4 Days / 3 Nights', 13999.00, 'Day 1: Arrive Rishikesh, Ganga Aarti | Day 2: Yoga & rafting | Day 3: Nainital day trip | Day 4: Departure', 'pkg5.jpg'),
('Tamil Nadu Hills & Heritage', 6, '5 Days / 4 Nights', 19999.00, 'Day 1: Arrive Ooty | Day 2: Toy train, tea estates | Day 3: Kodaikanal | Day 4: Mahabalipuram temples | Day 5: Departure', 'pkg6.jpg'),
('Mumbai City Explorer', 7, '3 Days / 2 Nights', 12999.00, 'Day 1: Gateway of India, Marine Drive | Day 2: Elephanta Caves, Lonavala day trip | Day 3: Departure', 'pkg7.jpg'),
('Karnataka Heritage Circuit', 8, '5 Days / 4 Nights', 20999.00, 'Day 1: Arrive Mysore, Palace | Day 2: Coorg coffee estates | Day 3: Hampi ruins | Day 4: Gokarna beach | Day 5: Departure', 'pkg8.jpg'),
('Golden Triangle: Agra & Lucknow', 9, '4 Days / 3 Nights', 17999.00, 'Day 1: Arrive Agra, Taj Mahal sunrise | Day 2: Agra Fort, travel to Lucknow | Day 3: Lucknow heritage tour | Day 4: Departure', 'pkg9.jpg'),
('Kashmir Paradise Tour', 10, '6 Days / 5 Nights', 32999.00, 'Day 1: Arrive Srinagar, Dal Lake shikara | Day 2: Mughal gardens | Day 3: Gulmarg gondola | Day 4: Pahalgam valley | Day 5: Leh flight & monastery visit | Day 6: Departure', 'pkg10.jpg');

-- ------------------------------------------------------------
-- BOOKINGS
-- ------------------------------------------------------------
CREATE TABLE bookings (
    booking_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    package_id INT NOT NULL,
    travel_date DATE NOT NULL,
    travellers INT NOT NULL DEFAULT 1,
    total_cost DECIMAL(10,2) NOT NULL,
    status ENUM('Pending','Confirmed','Cancelled') DEFAULT 'Pending',
    booking_reference VARCHAR(20) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (package_id) REFERENCES packages(package_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- REVIEWS  (item_type: destination / hotel / restaurant)
-- ------------------------------------------------------------
CREATE TABLE reviews (
    review_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    item_type ENUM('destination','hotel','restaurant') NOT NULL,
    item_id INT NOT NULL,
    rating TINYINT NOT NULL CHECK (rating BETWEEN 1 AND 5),
    review_text TEXT,
    status ENUM('Pending','Approved','Rejected') DEFAULT 'Approved',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- WISHLIST  (item_type: destination / package)
-- ------------------------------------------------------------
CREATE TABLE wishlist (
    wishlist_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    item_type ENUM('destination','package') NOT NULL,
    item_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_wish (user_id, item_type, item_id),
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- DESTINATION IMAGE GALLERY
-- ------------------------------------------------------------
CREATE TABLE destination_images (
    image_id INT AUTO_INCREMENT PRIMARY KEY,
    destination_id INT NOT NULL,
    image VARCHAR(255) NOT NULL,
    caption VARCHAR(150) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (destination_id) REFERENCES destinations(destination_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- LOGIN ATTEMPT TRACKING (brute-force lockout)
-- ------------------------------------------------------------
CREATE TABLE login_attempts (
    attempt_id INT AUTO_INCREMENT PRIMARY KEY,
    identifier VARCHAR(150) NOT NULL,
    attempts INT NOT NULL DEFAULT 1,
    last_attempt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    locked_until TIMESTAMP NULL DEFAULT NULL,
    UNIQUE KEY unique_identifier (identifier)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- CONTACT / FEEDBACK
-- ------------------------------------------------------------
CREATE TABLE contact_messages (
    message_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL,
    subject VARCHAR(200) DEFAULT NULL,
    message TEXT NOT NULL,
    status ENUM('New','Read','Responded') DEFAULT 'New',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;
