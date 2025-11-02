-- ======================================================
-- MoonHeritage — Complete database.sql (COPY & PASTE)
-- Creates database `moonheritage`, tables, constraints, indexes,
-- and sample data. Compatible with XAMPP/MySQL (InnoDB, utf8mb4).
-- Login supports BOTH username AND email.
-- ======================================================

/* Create database and select it */
CREATE DATABASE IF NOT EXISTS moonheritage
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE moonheritage;

-- Temporarily disable foreign key checks for clean drops
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS promotion_hotels;
DROP TABLE IF EXISTS wishlist;
DROP TABLE IF EXISTS reviews;
DROP TABLE IF EXISTS bookings;
DROP TABLE IF EXISTS rooms;
DROP TABLE IF EXISTS hotel_amenities;
DROP TABLE IF EXISTS amenities;
DROP TABLE IF EXISTS hotel_images;
DROP TABLE IF EXISTS hotels;
DROP TABLE IF EXISTS newsletter_subscribers;
DROP TABLE IF EXISTS promotions;
DROP TABLE IF EXISTS destinations;
DROP TABLE IF EXISTS contact_messages;
DROP TABLE IF EXISTS activity_logs;
DROP TABLE IF EXISTS settings;
DROP TABLE IF EXISTS users;

SET FOREIGN_KEY_CHECKS = 1;

-- ======================================================
-- USERS
-- ======================================================
CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    first_name VARCHAR(50),
    last_name VARCHAR(50),
    phone VARCHAR(20),
    address TEXT,
    city VARCHAR(100),
    country VARCHAR(100),
    postal_code VARCHAR(20),
    profile_image VARCHAR(255),
    role ENUM('user','admin','hotel_owner') DEFAULT 'user',
    email_verified TINYINT(1) DEFAULT 0,
    verification_token VARCHAR(100),
    reset_token VARCHAR(100),
    reset_token_expires DATETIME,
    status ENUM('active','suspended','deleted') DEFAULT 'active',
    last_login DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_users_email (email),
    INDEX idx_users_username (username),
    INDEX idx_users_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ======================================================
-- HOTELS
-- ======================================================
CREATE TABLE IF NOT EXISTS hotels (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    slug VARCHAR(250) UNIQUE NOT NULL,
    description TEXT,
    short_description VARCHAR(500),
    address TEXT NOT NULL,
    city VARCHAR(100) NOT NULL,
    state VARCHAR(100),
    country VARCHAR(100) NOT NULL,
    postal_code VARCHAR(20),
    latitude DECIMAL(10,8),
    longitude DECIMAL(11,8),
    phone VARCHAR(20),
    email VARCHAR(100),
    website VARCHAR(255),
    category ENUM('hotel','villa','apartment','resort','cottage') DEFAULT 'hotel',
    star_rating DECIMAL(2,1) DEFAULT 0.0,
    price_per_night DECIMAL(10,2) NOT NULL,
    original_price DECIMAL(10,2),
    discount_percentage INT DEFAULT 0,
    featured TINYINT(1) DEFAULT 0,
    total_rooms INT DEFAULT 0,
    available_rooms INT DEFAULT 0,
    check_in_time TIME DEFAULT '14:00:00',
    check_out_time TIME DEFAULT '11:00:00',
    main_image VARCHAR(255),
    status ENUM('active','inactive','pending') DEFAULT 'active',
    owner_id INT UNSIGNED DEFAULT NULL,
    view_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_hotels_city (city),
    INDEX idx_hotels_category (category),
    INDEX idx_hotels_featured (featured),
    INDEX idx_hotels_price (price_per_night),
    INDEX idx_hotels_status (status),
    INDEX idx_hotels_owner (owner_id),
    CONSTRAINT fk_hotels_owner FOREIGN KEY (owner_id) REFERENCES users(id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/* Fulltext index for search (MySQL 5.6+, InnoDB supported) */
CREATE FULLTEXT INDEX IF NOT EXISTS ft_hotels_search ON hotels (name, description, city, country);

-- ======================================================
-- HOTEL IMAGES
-- ======================================================
CREATE TABLE IF NOT EXISTS hotel_images (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    hotel_id INT UNSIGNED NOT NULL,
    image_path VARCHAR(255) NOT NULL,
    caption VARCHAR(255),
    is_primary TINYINT(1) DEFAULT 0,
    display_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_hotel_images_hotel (hotel_id),
    CONSTRAINT fk_hotel_images_hotel FOREIGN KEY (hotel_id) REFERENCES hotels(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ======================================================
-- AMENITIES + HOTEL_AMENITIES
-- ======================================================
CREATE TABLE IF NOT EXISTS amenities (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    icon VARCHAR(50),
    category VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS hotel_amenities (
    hotel_id INT UNSIGNED NOT NULL,
    amenity_id INT UNSIGNED NOT NULL,
    PRIMARY KEY (hotel_id, amenity_id),
    INDEX idx_hotel_amenities_hotel (hotel_id),
    INDEX idx_hotel_amenities_amenity (amenity_id),
    CONSTRAINT fk_hotel_amenities_hotel FOREIGN KEY (hotel_id) REFERENCES hotels(id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_hotel_amenities_amenity FOREIGN KEY (amenity_id) REFERENCES amenities(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ======================================================
-- ROOMS
-- ======================================================
CREATE TABLE IF NOT EXISTS rooms (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    hotel_id INT UNSIGNED NOT NULL,
    room_type VARCHAR(100) NOT NULL,
    description TEXT,
    price_per_night DECIMAL(10,2) NOT NULL,
    max_occupancy INT DEFAULT 2,
    size_sqm DECIMAL(10,2),
    bed_type VARCHAR(50),
    total_rooms INT DEFAULT 1,
    available_rooms INT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_rooms_hotel (hotel_id),
    CONSTRAINT fk_rooms_hotel FOREIGN KEY (hotel_id) REFERENCES hotels(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ======================================================
-- BOOKINGS
-- ======================================================
CREATE TABLE IF NOT EXISTS bookings (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    booking_number VARCHAR(30) UNIQUE NOT NULL,
    user_id INT UNSIGNED NOT NULL,
    hotel_id INT UNSIGNED NOT NULL,
    room_id INT UNSIGNED DEFAULT NULL,
    check_in_date DATE NOT NULL,
    check_out_date DATE NOT NULL,
    total_nights INT NOT NULL,
    guests_adults INT DEFAULT 1,
    guests_children INT DEFAULT 0,
    rooms_count INT DEFAULT 1,
    subtotal DECIMAL(10,2) NOT NULL,
    tax_amount DECIMAL(10,2) DEFAULT 0.00,
    discount_amount DECIMAL(10,2) DEFAULT 0.00,
    total_amount DECIMAL(10,2) NOT NULL,
    payment_status ENUM('pending','paid','failed','refunded') DEFAULT 'pending',
    payment_method VARCHAR(50),
    payment_id VARCHAR(100),
    booking_status ENUM('confirmed','pending','cancelled','completed') DEFAULT 'pending',
    special_requests TEXT,
    cancellation_reason TEXT,
    cancelled_at DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_bookings_user (user_id),
    INDEX idx_bookings_hotel (hotel_id),
    INDEX idx_bookings_dates (check_in_date, check_out_date),
    INDEX idx_bookings_status (booking_status),
    CONSTRAINT fk_bookings_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_bookings_hotel FOREIGN KEY (hotel_id) REFERENCES hotels(id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_bookings_room FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ======================================================
-- REVIEWS
-- ======================================================
CREATE TABLE IF NOT EXISTS reviews (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    hotel_id INT UNSIGNED NOT NULL,
    booking_id INT UNSIGNED DEFAULT NULL,
    rating DECIMAL(2,1) NOT NULL,
    title VARCHAR(200),
    comment TEXT NOT NULL,
    cleanliness_rating DECIMAL(2,1),
    service_rating DECIMAL(2,1),
    location_rating DECIMAL(2,1),
    value_rating DECIMAL(2,1),
    verified_booking TINYINT(1) DEFAULT 0,
    helpful_count INT DEFAULT 0,
    status ENUM('approved','pending','rejected') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_reviews_hotel (hotel_id),
    INDEX idx_reviews_user (user_id),
    INDEX idx_reviews_rating (rating),
    INDEX idx_reviews_status (status),
    CONSTRAINT fk_reviews_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_reviews_hotel FOREIGN KEY (hotel_id) REFERENCES hotels(id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_reviews_booking FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ======================================================
-- WISHLIST
-- ======================================================
CREATE TABLE IF NOT EXISTS wishlist (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    hotel_id INT UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_wishlist (user_id, hotel_id),
    INDEX idx_wishlist_user (user_id),
    CONSTRAINT fk_wishlist_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_wishlist_hotel FOREIGN KEY (hotel_id) REFERENCES hotels(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ======================================================
-- PROMOTIONS & PROMOTION_HOTELS
-- ======================================================
CREATE TABLE IF NOT EXISTS promotions (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    discount_type ENUM('percentage','fixed') DEFAULT 'percentage',
    discount_value DECIMAL(10,2) NOT NULL,
    promo_code VARCHAR(50) UNIQUE,
    valid_from DATE NOT NULL,
    valid_until DATE NOT NULL,
    min_booking_amount DECIMAL(10,2) DEFAULT 0.00,
    max_discount_amount DECIMAL(10,2),
    usage_limit INT,
    used_count INT DEFAULT 0,
    image VARCHAR(255),
    status ENUM('active','inactive','expired') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_promotions_code (promo_code),
    INDEX idx_promotions_dates (valid_from, valid_until),
    INDEX idx_promotions_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS promotion_hotels (
    promotion_id INT UNSIGNED NOT NULL,
    hotel_id INT UNSIGNED NOT NULL,
    PRIMARY KEY (promotion_id, hotel_id),
    INDEX idx_promotion_hotels_promo (promotion_id),
    INDEX idx_promotion_hotels_hotel (hotel_id),
    CONSTRAINT fk_promotion_hotels_promo FOREIGN KEY (promotion_id) REFERENCES promotions(id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_promotion_hotels_hotel FOREIGN KEY (hotel_id) REFERENCES hotels(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ======================================================
-- NEWSLETTER SUBSCRIBERS
-- ======================================================
CREATE TABLE IF NOT EXISTS newsletter_subscribers (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) UNIQUE NOT NULL,
    name VARCHAR(100),
    status ENUM('subscribed','unsubscribed') DEFAULT 'subscribed',
    verification_token VARCHAR(100),
    verified TINYINT(1) DEFAULT 0,
    subscribed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    unsubscribed_at DATETIME,
    INDEX idx_newsletter_email (email),
    INDEX idx_newsletter_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ======================================================
-- CONTACT MESSAGES
-- ======================================================
CREATE TABLE IF NOT EXISTS contact_messages (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    subject VARCHAR(200),
    message TEXT NOT NULL,
    status ENUM('new','read','replied','archived') DEFAULT 'new',
    replied_at DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_contact_status (status),
    INDEX idx_contact_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ======================================================
-- ACTIVITY LOGS
-- ======================================================
CREATE TABLE IF NOT EXISTS activity_logs (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED,
    action VARCHAR(100) NOT NULL,
    details TEXT,
    ip_address VARCHAR(45),
    user_agent VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_activity_user (user_id),
    INDEX idx_activity_action (action),
    INDEX idx_activity_created (created_at),
    CONSTRAINT fk_activity_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ======================================================
-- SETTINGS
-- ======================================================
CREATE TABLE IF NOT EXISTS settings (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    value TEXT,
    description VARCHAR(255),
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_settings_key (setting_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ======================================================
-- DESTINATIONS
-- ======================================================
CREATE TABLE IF NOT EXISTS destinations (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    country VARCHAR(100) NOT NULL,
    description TEXT,
    image VARCHAR(255),
    hotel_count INT DEFAULT 0,
    featured TINYINT(1) DEFAULT 0,
    display_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_destinations_featured (featured)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ======================================================
-- DEFAULT SEED DATA (admin, amenities, hotels, promotions, destinations)
-- ======================================================

-- Admin user (password hashed). The hash in this SQL was produced for 'admin123' using bcrypt ($2y$).
-- Replace with your own hash if you use a different password.
INSERT INTO users (username, email, password, first_name, last_name, role, email_verified, status)
VALUES
('admin','admin@moonheritage.com','$2y$12$V9qoP6XGZpV4b2uQpt9vyeEue5hEAtDPpOG7DNMAmQ8Ki7lgf5Obi','Admin','User','admin',1,'active');

-- Amenities (sample)
INSERT INTO amenities (name, icon, category) VALUES
('Free WiFi', 'fa-wifi', 'connectivity'),
('Swimming Pool', 'fa-swimming-pool', 'recreation'),
('Fitness Center', 'fa-dumbbell', 'recreation'),
('Restaurant', 'fa-utensils', 'dining'),
('Bar', 'fa-cocktail', 'dining'),
('Room Service', 'fa-concierge-bell', 'service'),
('Spa', 'fa-spa', 'wellness'),
('Parking', 'fa-parking', 'facilities'),
('Airport Shuttle', 'fa-shuttle-van', 'transport'),
('Pet Friendly', 'fa-paw', 'policy'),
('Air Conditioning', 'fa-snowflake', 'comfort'),
('Heating', 'fa-temperature-high', 'comfort'),
('TV', 'fa-tv', 'entertainment'),
('Mini Bar', 'fa-wine-bottle', 'room'),
('Safe', 'fa-lock', 'security'),
('Laundry Service', 'fa-tshirt', 'service'),
('Business Center', 'fa-briefcase', 'business'),
('Meeting Rooms', 'fa-users', 'business'),
('Kid-Friendly', 'fa-child', 'family'),
('24/7 Front Desk', 'fa-clock', 'service');

-- Sample Hotels (minimal required columns provided)
INSERT INTO hotels (name, slug, description, short_description, address, city, country, category, star_rating, price_per_night, original_price, discount_percentage, featured, total_rooms, available_rooms, main_image, status)
VALUES
('Moonlight Majestic Hotel', 'moonlight-majestic-hotel', 'Experience luxury and comfort in the heart of Mykonos. Our hotel offers stunning views, world-class amenities, and exceptional service.', 'Luxury hotel with stunning ocean views', '123 Beach Road', 'Mykonos', 'Greece', 'hotel', 5.0, 160.00, 200.00, 20, 1, 50, 45, 'hotels/moonlight-majestic.jpg', 'active'),
('Elysee Retreat', 'elysee-retreat', 'A charming boutique hotel in the heart of Paris, offering elegant rooms and personalized service.', 'Boutique hotel in Paris city center', '45 Rue de Rivoli', 'Paris', 'France', 'hotel', 4.5, 190.00, 190.00, 0, 1, 30, 28, 'hotels/elysee-retreat.jpg', 'active'),
('Bamboo Villa Inn', 'bamboo-villa-inn', 'Immerse yourself in Balinese culture at our tropical villa resort with private pools and spa services.', 'Tropical villa with private pools', '789 Ubud Road', 'Ubud', 'Indonesia', 'villa', 4.0, 220.00, 220.00, 0, 1, 20, 18, 'hotels/bamboo-villa.jpg', 'active'),
('Sea Shore Lodge', 'sea-shore-lodge', 'Beachfront resort offering all-inclusive packages with water sports and fine dining.', 'All-inclusive beachfront resort', '321 Coastal Avenue', 'Centura', 'Maldives', 'resort', 5.0, 550.00, 785.00, 30, 1, 80, 72, 'hotels/sea-shore.jpg', 'active'),
('Azure Oasis', 'azure-oasis', 'Overwater bungalows with private infinity pools and world-class diving experiences.', 'Luxury overwater bungalows', '555 Ocean Drive', 'Male', 'Maldives', 'resort', 5.0, 600.00, 600.00, 0, 0, 40, 35, 'hotels/azure-oasis.jpg', 'active'),
('Descartt Heights', 'descartt-heights', 'Mountain retreat with panoramic views and hiking trails right at your doorstep.', 'Mountain resort with hiking trails', '777 Mountain Pass', 'Monte Liba', 'Switzerland', 'resort', 4.0, 700.00, 700.00, 0, 0, 35, 30, 'hotels/descartt-heights.jpg', 'active'),
('Majestic Serenity Palace', 'majestic-serenity-palace', 'Heritage palace hotel offering royal treatment in the backwaters of Kerala.', 'Heritage palace on Kerala backwaters', '888 Palace Road', 'Kerala', 'India', 'hotel', 4.5, 420.00, 420.00, 0, 0, 60, 55, 'hotels/serenity-palace.jpg', 'active'),
('Bella Vista', 'bella-vista', 'Coastal luxury hotel with Mediterranean cuisine and sunset views.', 'Luxury coastal hotel with sea views', '999 Coastal Way', 'Puglia', 'Italy', 'hotel', 5.0, 450.00, 450.00, 0, 0, 45, 40, 'hotels/bella-vista.jpg', 'active');

-- Sample Settings
INSERT INTO settings (setting_key, value, description) VALUES
('site_name', 'MoonHeritage', 'Website name'),
('site_email', 'info@moonheritage.com', 'Contact email'),
('booking_tax_percentage', '10', 'Tax percentage on bookings'),
('cancellation_hours', '24', 'Free cancellation hours before check-in'),
('featured_hotels_limit', '8', 'Number of featured hotels to display'),
('reviews_per_page', '10', 'Reviews per page');

-- Sample Promotions
INSERT INTO promotions (title, description, discount_type, discount_value, promo_code, valid_from, valid_until, min_booking_amount, image, status)
VALUES
('Summer Special', 'Get 50% off on selected hotels this summer!', 'percentage', 50.00, 'SUMMER50', '2025-06-01', '2025-08-31', 100.00, 'promos/summer-special.jpg', 'active'),
('Christmas Deals', 'Celebrate the holidays with 75% discount on luxury stays!', 'percentage', 75.00, 'XMAS75', '2025-12-01', '2025-12-31', 200.00, 'promos/christmas-deals.jpg', 'active');

-- Map promotions to hotels (adjust IDs if you later reseed differently)
INSERT INTO promotion_hotels (promotion_id, hotel_id) VALUES
(1, 1), (1, 2), (2, 4);

-- Sample Destinations
INSERT INTO destinations (name, country, description, hotel_count, featured, display_order) VALUES
('Paris', 'France', 'The city of love and lights', 156, 1, 1),
('Mykonos', 'Greece', 'Beautiful Greek island paradise', 89, 1, 2),
('Bali', 'Indonesia', 'Tropical island getaway', 234, 1, 3),
('Maldives', 'Maldives', 'Luxury island resorts', 178, 1, 4),
('Dubai', 'UAE', 'Modern luxury destination', 312, 0, 5),
('Tokyo', 'Japan', 'Blend of tradition and modernity', 267, 0, 6);

-- ======================================================
-- Optional: example mapping of a few amenities to a hotel (hotel_id 1 exists from above inserts)
-- ======================================================
INSERT IGNORE INTO hotel_amenities (hotel_id, amenity_id)
SELECT 1, id FROM amenities WHERE name IN ('Free WiFi','Swimming Pool','Parking') LIMIT 3;

-- ======================================================
-- Final notes:
-- 1) If you import via phpMyAdmin: upload this full file and run it. All tables/constraints will be created.
-- 2) If you change column names in your PHP code, update the SQL accordingly (especially users.username/email).
-- 3) Admin password hash above is bcrypt for 'admin123'. To change password, replace the hash with your own bcrypt hash.
-- 4) If you need demo bookings / rooms seed data added, tell me "add demo bookings" and I'll append them.
-- ======================================================

