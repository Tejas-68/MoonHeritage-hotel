CREATE DATABASE IF NOT EXISTS moonheritage
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE moonheritage;

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

-- USERS
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

-- HOTELS
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

CREATE FULLTEXT INDEX IF NOT EXISTS ft_hotels_search ON hotels (name, description, city, country);

-- HOTEL IMAGES
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

-- AMENITIES + HOTEL_AMENITIES
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

-- ROOMS
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

-- BOOKINGS
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

-- REVIEWS
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

-- WISHLIST
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

-- PROMOTIONS & PROMOTION_HOTELS
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

-- NEWSLETTER SUBSCRIBERS
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

-- CONTACT MESSAGES
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

-- ACTIVITY LOGS
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

-- SETTINGS
CREATE TABLE IF NOT EXISTS settings (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    value TEXT,
    description VARCHAR(255),
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_settings_key (setting_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- DESTINATIONS
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

-- ═══════════════════════════════════════════════════════════════
-- SEED DATA
-- ═══════════════════════════════════════════════════════════════

-- Admin user (password: admin123 — change via admin-reset-password.html)
INSERT INTO users (username, email, password, first_name, last_name, role, email_verified, status)
VALUES ('admin','admin@moonheritage.com','$2a$12$2CgEbAfGYQKnxm8qfckJIOeK0FJ7dMTiNQzxXhCUjUx.dS3uuxiAW','Admin','User','admin',1,'active');

-- Sample users
INSERT INTO users (username, email, password, first_name, last_name, role, email_verified, status) VALUES
('teajs',   'tejas@gmail.com',  '$2y$12$V9qoP6XGZpV4b2uQpt9vyeEue5hEAtDPpOG7DNMAmQ8Ki7lgf5Obi', 'tejas',   'yalvar',    'user', 1, 'active'),
('manju',   'manju@example.com',  '$2y$12$V9qoP6XGZpV4b2uQpt9vyeEue5hEAtDPpOG7DNMAmQ8Ki7lgf5Obi', ',manju',   'nath',    'user', 1, 'active'),
('adarsh', 'adi@example.com','$2y$12$V9qoP6XGZpV4b2uQpt9vyeEue5hEAtDPpOG7DNMAmQ8Ki7lgf5Obi', 'adi',   'ganig', 'user', 1, 'active');

-- ── Amenities ──────────────────────────────────────────────────────
INSERT INTO amenities (name, icon, category) VALUES
('Free WiFi',         'fa-wifi',             'connectivity'),
('Swimming Pool',     'fa-swimming-pool',    'recreation'),
('Fitness Center',    'fa-dumbbell',         'recreation'),
('Restaurant',        'fa-utensils',         'dining'),
('Bar & Lounge',      'fa-cocktail',         'dining'),
('Room Service',      'fa-concierge-bell',   'service'),
('Spa & Wellness',    'fa-spa',              'wellness'),
('Free Parking',      'fa-parking',          'facilities'),
('Airport Shuttle',   'fa-shuttle-van',      'transport'),
('Pet Friendly',      'fa-paw',              'policy'),
('Air Conditioning',  'fa-snowflake',        'comfort'),
('Beach Access',      'fa-umbrella-beach',   'recreation'),
('Private Pool',      'fa-water',            'recreation'),
('Butler Service',    'fa-user-tie',         'service'),
('Rooftop Terrace',   'fa-city',             'recreation'),
('Business Center',   'fa-briefcase',        'business'),
('Kids Club',         'fa-child',            'family'),
('Laundry Service',   'fa-tshirt',           'service'),
('24/7 Front Desk',   'fa-clock',            'service'),
('Bicycle Rental',    'fa-bicycle',          'transport');

-- ── Hotels (12 properties) ─────────────────────────────────────────
INSERT INTO hotels (name, slug, description, short_description, address, city, state, country,
  phone, email, category, star_rating, price_per_night, original_price, discount_percentage,
  featured, total_rooms, available_rooms, main_image, status)
VALUES

-- 1. Mykonos, Greece — Hotel
('Aegean Pearl Hotel',
 'aegean-pearl-hotel',
 'Perched on a clifftop above the Aegean Sea, Aegean Pearl Hotel delivers an extraordinary blend of Cycladic architecture and modern luxury. Whitewashed walls, cobalt-blue domes, and infinity pools merge seamlessly with the horizon. Each suite offers a private terrace where the endless blue of the sea meets the sky. Our award-winning restaurant serves fresh-caught seafood and curated wines from across the Greek islands.',
 'Clifftop luxury hotel with infinity pools and Aegean sea views in Mykonos.',
 '12 Agios Stefanos Road', 'Mykonos', NULL, 'Greece',
 '+30 22890 12345', 'stay@aegeanpearl.com',
 'hotel', 5.0, 380.00, 480.00, 21, 1, 48, 42,
 'https://images.unsplash.com/photo-1613395877344-13d4a8e0d49e?w=1200&q=80', 'active'),

-- 2. Paris, France — Hotel
('Maison Lumière',
 'maison-lumiere-paris',
 'Nestled steps from the Champs-Élysées, Maison Lumière is an intimate Haussmann-era boutique hotel celebrating Parisian elegance. Original parquet floors, Venetian plaster walls, and bespoke French furnishings create an atmosphere of refined romance. Our rooftop terrace offers an unobstructed view of the Eiffel Tower. The in-house bistro, helmed by a Michelin-trained chef, redefines modern French cuisine.',
 'Intimate Parisian boutique hotel with Eiffel Tower rooftop views and Michelin bistro.',
 '18 Avenue George V', 'Paris', 'Île-de-France', 'France',
 '+33 1 47 23 55 00', 'bonjour@maisonlumiere.fr',
 'hotel', 5.0, 420.00, 420.00, 0, 1, 32, 28,
 'https://images.unsplash.com/photo-1551882547-ff40c63fe5fa?w=1200&q=80', 'active'),

-- 3. Bali, Indonesia — Villa
('Pura Vida Villa Ubud',
 'pura-vida-villa-ubud',
 'Hidden within Ubud\'s sacred jungle, Pura Vida Villa offers an authentic Balinese retreat unlike any other. Each private villa features its own plunge pool, open-air bathroom, and a bamboo garden pavilion for meditation. Traditional Balinese architecture — carved teak doors, lotus-filled courtyards, alang-alang thatched roofs — blends with modern comforts. Daily yoga, rice-field trekking, and traditional cooking classes are included in every stay.',
 'Lush private villas with plunge pools and traditional Balinese experiences in Ubud.',
 '99 Jalan Bisma, Ubud', 'Ubud', 'Bali', 'Indonesia',
 '+62 361 975 080', 'villa@puravida-ubud.com',
 'villa', 4.5, 275.00, 350.00, 21, 1, 16, 14,
 'https://images.unsplash.com/photo-1537996194471-e657df975ab4?w=1200&q=80', 'active'),

-- 4. Maldives — Resort
('Turquoise Horizon Resort',
 'turquoise-horizon-resort',
 'Turquoise Horizon Resort is the pinnacle of barefoot luxury in the Maldives. Overwater bungalows rise above crystal-clear lagoons where manta rays glide beneath your glass floor. An all-inclusive concept covers world-class dining at five restaurants, unlimited diving and snorkelling, sunset dolphin cruises, and a comprehensive spa menu. Seaplane transfers ensure your arrival is as breathtaking as your stay.',
 'All-inclusive overwater bungalow resort with private reefs and seaplane transfers.',
 'North Malé Atoll', 'Malé Atoll', NULL, 'Maldives',
 '+960 664 2000', 'reservations@turquoisehorizon.mv',
 'resort', 5.0, 850.00, 1200.00, 29, 1, 65, 58,
 'https://images.unsplash.com/photo-1514282401047-d79a71a590e8?w=1200&q=80', 'active'),

-- 5. Santorini, Greece — Villa
('Caldera Sunset Villas',
 'caldera-sunset-villas',
 'Caldera Sunset Villas clings to the ancient volcanic cliffs of Santorini, offering some of the world\'s most celebrated views. Each villa is carved into the caldera face, featuring private hot tubs jutting over the cliff edge, cave bedrooms with vaulted ceilings, and personal concierge service. As the sun sinks into the Aegean, the sky ignites — a sight best enjoyed with a glass of Assyrtiko from our private wine cellar.',
 'Iconic cliff-carved villas with private hot tubs overlooking the Santorini caldera.',
 '7 Imerovigli Cliff Walk', 'Santorini', NULL, 'Greece',
 '+30 22860 28900', 'hello@calderasunset.gr',
 'villa', 5.0, 620.00, 620.00, 0, 1, 12, 10,
 'https://images.unsplash.com/photo-1570077188670-e3a8d69ac5ff?w=1200&q=80', 'active'),

-- 6. Tuscany, Italy — Villa
('Villa Toscana Estates',
 'villa-toscana-estates',
 'Villa Toscana Estates is a 15th-century Medici manor lovingly restored into a private luxury retreat. Set amid 40 hectares of rolling vineyards, olive groves, and cypress alleys, the estate offers exclusive buyout for families and groups seeking total privacy. Guests enjoy private wine tastings, truffle hunting with local experts, clay tennis courts, a heated outdoor pool, and farm-to-table dinners under the Tuscan stars.',
 'Restored 15th-century Medici manor with vineyards, private pool, and farm-to-table dining.',
 'Via del Chianti 7, Greve in Chianti', 'Florence', 'Tuscany', 'Italy',
 '+39 055 854 3210', 'info@villatoscana.it',
 'villa', 5.0, 520.00, 650.00, 20, 0, 20, 18,
 'https://images.unsplash.com/photo-1567636788276-40a47795ba4d?w=1200&q=80', 'active'),

-- 7. Dubai, UAE — Hotel
('Skyline Tower Hotel Dubai',
 'skyline-tower-hotel-dubai',
 'Rising 52 floors above the Dubai Marina, Skyline Tower Hotel is a testament to architectural ambition and hospitality excellence. The sky lobby on the 40th floor looks directly at the Burj Khalifa while our rooftop infinity pool appears suspended in the clouds. Guests enjoy direct access to The Walk promenade, three acclaimed restaurants, and a world-class spa offering Arabic tradition and international wellness.',
 '52-floor Dubai Marina hotel with rooftop infinity pool facing the Burj Khalifa.',
 'Dubai Marina Walk, JBR', 'Dubai', NULL, 'UAE',
 '+971 4 555 7890', 'stay@skylinetowerdubai.com',
 'hotel', 5.0, 495.00, 495.00, 0, 1, 120, 105,
 'https://images.unsplash.com/photo-1582719508461-905c673771fd?w=1200&q=80', 'active'),

-- 8. Amalfi Coast, Italy — Hotel
('Positano Grand Coastal Hotel',
 'positano-grand-coastal-hotel',
 'Carved into the Amalfi cliffs overlooking the Tyrrhenian Sea, Positano Grand Coastal Hotel is a mosaic of pastel terraces, bougainvillea, and breathtaking beauty. Private boat excursions to Capri depart from our pier daily. The infinity pool hovers over the cobalt water, and every suite boasts its own sea-view terrace. Our La Sponda-inspired restaurant celebrates the richness of Southern Italian cuisine.',
 'Iconic cliff-side hotel on the Amalfi Coast with private pier and sea-view suites.',
 '5 Via Pasitea', 'Positano', 'Campania', 'Italy',
 '+39 089 812 181', 'reservations@positanogrand.it',
 'hotel', 5.0, 560.00, 700.00, 20, 0, 38, 33,
 'https://images.unsplash.com/photo-1516483638261-f4dbaf036963?w=1200&q=80', 'active'),

-- 9. Swiss Alps, Switzerland — Resort
('Alpine Summit Lodge',
 'alpine-summit-lodge',
 'Alpine Summit Lodge sits at 1,900 metres in the heart of the Swiss Alps, offering ski-in ski-out access to 300km of pristine pistes in winter and wildflower meadow hiking in summer. The Great Hall features a 12-metre stone fireplace, fur throws, and mountain views through floor-to-ceiling glazing. Après-ski in our whisky bar, followed by a four-course fondue dinner, defines the Lodge experience.',
 'Ski-in ski-out alpine lodge at 1,900m with stone fireplaces and 300km of pistes.',
 'Kirchplatz 3, Grindelwald', 'Grindelwald', 'Bern', 'Switzerland',
 '+41 33 854 5400', 'stay@alpinesummit.ch',
 'resort', 4.5, 480.00, 600.00, 20, 1, 55, 48,
 'https://images.unsplash.com/photo-1476514525535-07fb3b4ae5f1?w=1200&q=80', 'active'),

-- 10. Kerala, India — Resort
('Backwater Palace Kerala',
 'backwater-palace-kerala',
 'Backwater Palace Kerala is an eco-luxury heritage resort floating on the legendary Kerala backwaters. Private kettuvallam houseboats, guided through palm-fringed canals by traditional oarsmen, form part of the resort\'s unique accommodation. Ayurvedic wellness programmes curated by doctors, Kathakali dance performances at sunset, and authentic Keralan feasts served on banana leaves define the cultural immersion here.',
 'Eco-luxury heritage resort on the Kerala backwaters with private houseboats and Ayurveda.',
 'Muhamma, Alappuzha District', 'Alleppey', 'Kerala', 'India',
 '+91 477 223 3456', 'palace@backwaterpalace.in',
 'resort', 4.5, 310.00, 310.00, 0, 0, 42, 38,
 'https://images.unsplash.com/photo-1602002418816-5c0aeef426aa?w=1200&q=80', 'active'),

-- 11. Cotswolds, UK — Cottage
('Cotswolds Stone Cottage Retreat',
 'cotswolds-stone-cottage-retreat',
 'The Cotswolds Stone Cottage Retreat is a cluster of beautifully restored 17th-century honey-stone cottages nestled in England\'s most picturesque countryside. Each cottage features exposed beam ceilings, inglenook log fireplaces, roll-top copper baths, and private walled gardens. Village pubs, antique markets, and walking trails begin at your doorstep. Full English breakfast delivered to your cottage door each morning.',
 'Restored 17th-century honey-stone cottages with log fires and private walled gardens.',
 'The Green, Bourton-on-the-Water', 'Bourton-on-the-Water', 'Gloucestershire', 'United Kingdom',
 '+44 1451 820900', 'hello@cotswoldscottage.co.uk',
 'cottage', 4.0, 240.00, 300.00, 20, 0, 8, 7,
 'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?w=1200&q=80', 'active'),

-- 12. New York, USA — Hotel
('Manhattan Heights Hotel',
 'manhattan-heights-hotel-nyc',
 'Manhattan Heights Hotel puts the pulse of New York City at your fingertips. A bold fusion of Art Deco heritage and contemporary design, the hotel occupies a landmark 1930s tower in Midtown. The Living Room lobby bar buzzes with creatives and financiers from dawn to well past midnight. Our executive suites on the upper floors face Central Park, and the rooftop Altitude Restaurant rivals any table in the five boroughs.',
 'Art Deco Midtown Manhattan hotel with Central Park suites and a rooftop restaurant.',
 '345 Park Avenue', 'New York City', 'New York', 'USA',
 '+1 212 555 8900', 'nyc@manhattanheights.com',
 'hotel', 4.5, 345.00, 420.00, 18, 0, 200, 175,
 'https://images.unsplash.com/photo-1534430480872-3498386e7856?w=1200&q=80', 'active');

-- ── Gallery images (hotel_images) ──────────────────────────────────
INSERT INTO hotel_images (hotel_id, image_path, caption, is_primary, display_order) VALUES
-- Aegean Pearl (1)
(1,'https://images.unsplash.com/photo-1613395877344-13d4a8e0d49e?w=1200&q=80','Infinity pool sunset',1,0),
(1,'https://images.unsplash.com/photo-1602632188808-f21f5a25f50f?w=1200&q=80','Suite terrace',0,1),
(1,'https://images.unsplash.com/photo-1575783970733-1aaedde1db74?w=1200&q=80','Aegean sea view',0,2),
-- Maison Lumière (2)
(2,'https://images.unsplash.com/photo-1551882547-ff40c63fe5fa?w=1200&q=80','Hotel façade',1,0),
(2,'https://images.unsplash.com/photo-1582719508461-905c673771fd?w=1200&q=80','Lobby',0,1),
(2,'https://images.unsplash.com/photo-1590490360182-c33d57733427?w=1200&q=80','Rooftop Eiffel view',0,2),
-- Pura Vida Villa (3)
(3,'https://images.unsplash.com/photo-1537996194471-e657df975ab4?w=1200&q=80','Jungle pool villa',1,0),
(3,'https://images.unsplash.com/photo-1540541338287-41700207dee6?w=1200&q=80','Rice field view',0,1),
(3,'https://images.unsplash.com/photo-1535913562611-58f5e9e1e5e2?w=1200&q=80','Garden pavilion',0,2),
-- Turquoise Horizon (4)
(4,'https://images.unsplash.com/photo-1514282401047-d79a71a590e8?w=1200&q=80','Overwater bungalow',1,0),
(4,'https://images.unsplash.com/photo-1580541832626-2a7131ee809f?w=1200&q=80','Lagoon view',0,1),
(4,'https://images.unsplash.com/photo-1504450874982-f7cd6af7ec76?w=1200&q=80','Sunset over water',0,2),
-- Caldera Sunset Villas (5)
(5,'https://images.unsplash.com/photo-1570077188670-e3a8d69ac5ff?w=1200&q=80','Caldera panorama',1,0),
(5,'https://images.unsplash.com/photo-1555881400-74d7acaacd8b?w=1200&q=80','Cliff-top hot tub',0,1),
(5,'https://images.unsplash.com/photo-1534430480872-3498386e7856?w=1200&q=80','Cave suite',0,2),
-- Villa Toscana (6)
(6,'https://images.unsplash.com/photo-1567636788276-40a47795ba4d?w=1200&q=80','Vineyard estate',1,0),
(6,'https://images.unsplash.com/photo-1499678329028-101435f03a35?w=1200&q=80','Olive grove pool',0,1),
-- Skyline Dubai (7)
(7,'https://images.unsplash.com/photo-1582719508461-905c673771fd?w=1200&q=80','Skyline pool',1,0),
(7,'https://images.unsplash.com/photo-1542314831-068cd1dbfeeb?w=1200&q=80','Marina view room',0,1),
-- Positano (8)
(8,'https://images.unsplash.com/photo-1516483638261-f4dbaf036963?w=1200&q=80','Cliff terrace',1,0),
(8,'https://images.unsplash.com/photo-1533105079780-92b9be482077?w=1200&q=80','Sea view pool',0,1),
-- Alpine Summit (9)
(9,'https://images.unsplash.com/photo-1476514525535-07fb3b4ae5f1?w=1200&q=80','Mountain lodge',1,0),
(9,'https://images.unsplash.com/photo-1502003148287-a82ef80a6abc?w=1200&q=80','Ski slopes',0,1),
-- Backwater Palace (10)
(10,'https://images.unsplash.com/photo-1602002418816-5c0aeef426aa?w=1200&q=80','Houseboat',1,0),
(10,'https://images.unsplash.com/photo-1544551763-46a013bb70d5?w=1200&q=80','Backwater canal',0,1),
-- Cotswolds (11)
(11,'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?w=1200&q=80','Stone cottage',1,0),
(11,'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=1200&q=80','Garden',0,1),
-- Manhattan Heights (12)
(12,'https://images.unsplash.com/photo-1534430480872-3498386e7856?w=1200&q=80','NYC skyline view',1,0),
(12,'https://images.unsplash.com/photo-1499856871958-5b9627545d1a?w=1200&q=80','Central Park suite',0,1);

-- ── Rooms ─────────────────────────────────────────────────────────
INSERT INTO rooms (hotel_id, room_type, description, price_per_night, max_occupancy, size_sqm, bed_type) VALUES
-- Aegean Pearl (1)
(1,'Cycladic Suite','Open-plan suite with whitewashed walls, private terrace, and panoramic sea view.',380.00,2,65,'King'),
(1,'Infinity Pool Villa','Private villa with a personal infinity pool that appears to flow into the Aegean.',620.00,4,120,'King + Twin'),
-- Maison Lumière (2)
(2,'Classic Parisian Room','Elegant room with parquet floors, French tall windows and city views.',280.00,2,28,'Queen'),
(2,'Eiffel View Suite','Corner suite with direct Eiffel Tower sightline from both bedroom and bathroom.',520.00,2,55,'King'),
-- Pura Vida Villa (3)
(3,'Garden Pool Villa','Private villa with plunge pool, open-air bathroom and tropical garden.',275.00,2,80,'King'),
(3,'Jungle Retreat Villa','Two-bedroom villa deep in the jungle, with yoga pavilion and personal butler.',420.00,4,140,'King + Twin'),
-- Turquoise Horizon (4)
(4,'Lagoon Bungalow','Overwater bungalow above a shallow lagoon with glass floor and sun deck.',850.00,2,90,'King'),
(4,'Ocean Retreat Villa','Two-bedroom overwater villa with private pool, butler, and reef access.',1400.00,4,200,'King + Twin'),
-- Caldera Sunset Villas (5)
(5,'Caldera Cave Suite','Cave-hewn suite with vaulted ceiling, hot tub on private terrace.',620.00,2,75,'King'),
(5,'Honeymoon Cave Villa','Ultimate privacy — two levels built into the caldera with a private plunge pool.',980.00,2,120,'King'),
-- Villa Toscana (6)
(6,'Medici Deluxe Room','High-ceilinged room with frescoed walls and vineyard views.',520.00,2,45,'King'),
(6,'Estate Suite','Sprawling suite with sitting room, private terrace over the olive grove.',780.00,3,110,'King'),
-- Skyline Dubai (7)
(7,'City View King','Floor-to-ceiling glass overlooking the Marina skyline.',405.00,2,38,'King'),
(7,'Marina Penthouse Suite','Double-height living space with wraparound terrace and Burj view.',1100.00,4,210,'King + Sofa'),
-- Positano (8)
(8,'Sea View Deluxe','Terrace suite with unobstructed Tyrrhenian Sea panorama.',560.00,2,42,'King'),
(8,'Premier Cliff Suite','Three-level suite carved into the cliff with private pool and butler.',950.00,4,160,'King + Twin'),
-- Alpine Summit (9)
(9,'Cosy Alpine Room','Pine-panelled room with feather duvet, mountain view, and ski locker.',350.00,2,32,'Queen'),
(9,'Panorama Suite','Bi-level suite with sauna, fireplace lounge, and 180° peak views.',680.00,4,95,'King + Twin'),
-- Backwater Palace (10)
(10,'Heritage Room','Colonial-era room with antique furnishings and backwater views.',310.00,2,36,'King'),
(10,'Premium Houseboat','Private kettuvallam houseboat for two with personal oarsman and chef.',560.00,2,70,'King'),
-- Cotswolds Cottage (11)
(11,'Classic Cottage','Honey-stone cottage with inglenook fireplace and walled garden.',240.00,2,48,'King'),
(11,'Grand Cottage','Larger cottage sleeping four with two bathrooms and private orchard.',360.00,4,80,'King + Twin'),
-- Manhattan Heights (12)
(12,'Executive City Room','Art Deco-furnished room with floor 25+ view over Midtown Manhattan.',345.00,2,35,'Queen'),
(12,'Central Park Suite','Corner suite on floor 40 with sweeping Central Park dual-aspect views.',790.00,3,95,'King');

-- ── Amenity mappings ───────────────────────────────────────────────
INSERT INTO hotel_amenities (hotel_id, amenity_id) VALUES
-- Amenity IDs: 1=WiFi, 2=Pool, 3=Gym, 4=Restaurant, 5=Bar, 6=Room service,
--              7=Spa, 8=Parking, 9=Shuttle, 10=Pets, 11=AC, 12=Beach, 13=Private pool,
--              14=Butler, 15=Rooftop, 16=Business, 17=Kids, 18=Laundry, 19=24/7 desk, 20=Bikes
-- Aegean Pearl
(1,1),(1,2),(1,4),(1,5),(1,6),(1,7),(1,11),(1,15),(1,18),(1,19),
-- Maison Lumière
(2,1),(2,4),(2,5),(2,6),(2,7),(2,11),(2,15),(2,16),(2,18),(2,19),
-- Pura Vida Villa
(3,1),(3,6),(3,7),(3,10),(3,11),(3,13),(3,14),(3,18),(3,19),(3,20),
-- Turquoise Horizon
(4,1),(4,2),(4,4),(4,5),(4,6),(4,7),(4,9),(4,12),(4,13),(4,14),(4,18),(4,19),
-- Caldera Sunset Villas
(5,1),(5,6),(5,7),(5,11),(5,13),(5,14),(5,18),(5,19),
-- Villa Toscana
(6,1),(6,2),(6,4),(6,7),(6,8),(6,10),(6,13),(6,18),(6,19),(6,20),
-- Skyline Dubai
(7,1),(7,2),(7,3),(7,4),(7,5),(7,6),(7,7),(7,8),(7,9),(7,11),(7,15),(7,16),(7,18),(7,19),
-- Positano
(8,1),(8,2),(8,4),(8,5),(8,6),(8,7),(8,11),(8,12),(8,18),(8,19),
-- Alpine Summit
(9,1),(9,3),(9,4),(9,5),(9,6),(9,7),(9,8),(9,11),(9,18),(9,19),
-- Backwater Palace
(10,1),(10,4),(10,6),(10,7),(10,9),(10,10),(10,18),(10,19),(10,20),
-- Cotswolds Cottage
(11,1),(11,8),(11,10),(11,18),(11,19),(11,20),
-- Manhattan Heights
(12,1),(12,2),(12,3),(12,4),(12,5),(12,6),(12,7),(12,8),(12,9),(12,11),(12,15),(12,16),(12,17),(12,18),(12,19);

-- ── Reviews ────────────────────────────────────────────────────────
INSERT INTO reviews (user_id, hotel_id, rating, title, comment, service_rating, cleanliness_rating, location_rating, value_rating, verified_booking, status) VALUES
(2, 1, 5.0, 'A dream in the Aegean',      'Woke up to a sunrise over the sea from our private terrace. The infinity pool felt like swimming in the sky. Service was impeccable.', 5.0, 5.0, 5.0, 4.5, 1, 'approved'),
(3, 1, 4.5, 'Stunning but pricey',        'Perfect setting and faultless rooms. Worth every cent for a special occasion.', 4.5, 5.0, 5.0, 4.0, 1, 'approved'),
(2, 2, 5.0, 'Paris like in the movies',   'The Eiffel Tower glittered from our suite window at night. Breakfast was extraordinary. Will return every year.', 5.0, 5.0, 5.0, 4.5, 1, 'approved'),
(4, 2, 4.5, 'Exceptional boutique stay',  'Small enough to feel personal, luxurious enough to feel indulged. The rooftop croissants alone deserve five stars.', 5.0, 4.5, 5.0, 4.0, 1, 'approved'),
(3, 3, 5.0, 'Bali at its best',           'The jungle pool villa was pure magic. Waking to birdsong and mist over the rice terraces was unforgettable. Staff were warm and attentive.', 5.0, 5.0, 4.5, 4.5, 1, 'approved'),
(2, 4, 5.0, 'Heaven on water',            'Staying in an overwater bungalow with manta rays beneath your feet is surreal. The reef snorkelling was world-class. Totally worth it.', 5.0, 5.0, 5.0, 4.0, 1, 'approved'),
(4, 5, 5.0, 'Most romantic place on Earth','Hot tub on the caldera edge at sunset with my partner. Words honestly fail. Just go.', 5.0, 5.0, 5.0, 4.5, 1, 'approved'),
(3, 7, 4.5, 'Dubai in style',             'The rooftop pool at night with the Burj Khalifa lit up is jawdropping. Room service was prompt and hotel staff went far beyond expectations.', 4.5, 5.0, 5.0, 4.0, 1, 'approved'),
(2, 9, 5.0, 'Alpine perfection',          'Ski-in ski-out access is a game-changer. After a long day on the slopes the stone fireplace suite and Swiss cheese fondue erased all fatigue.', 5.0, 5.0, 5.0, 4.5, 1, 'approved'),
(4,12, 4.5, 'NYC in comfort',             'Woke up to Central Park both mornings. The bar downstairs was buzzing every night. Location is unbeatable for exploring the city.', 4.5, 4.5, 5.0, 4.0, 1, 'approved');

-- ── Settings ───────────────────────────────────────────────────────
INSERT INTO settings (setting_key, value, description) VALUES
('site_name',              'MoonHeritage',         'Website name'),
('site_email',             'info@moonheritage.com', 'Contact email'),
('booking_tax_percentage', '10',                   'Tax percentage on bookings'),
('cancellation_hours',     '24',                   'Free cancellation hours before check-in'),
('featured_hotels_limit',  '8',                    'Number of featured hotels on homepage'),
('reviews_per_page',       '10',                   'Reviews displayed per page');

-- ── Destinations ───────────────────────────────────────────────────
INSERT INTO destinations (name, country, description, hotel_count, featured, display_order) VALUES
('Mykonos',     'Greece',         'Dazzling Cycladic island of whitewashed villages and turquoise seas.', 24, 1, 1),
('Paris',       'France',         'The eternal city of light, love, cuisine, and culture.',              156, 1, 2),
('Bali',        'Indonesia',      'Tropical island retreat of sacred temples, rice terraces, and surf.',  89, 1, 3),
('Maldives',    'Maldives',       'The world\'s most sought-after overwater bungalow destination.',       72, 1, 4),
('Santorini',   'Greece',         'Dramatic caldera sunsets and cave-hewn luxury above the Aegean.',      38, 1, 5),
('Dubai',       'UAE',            'Desert city of architectural wonders and limitless luxury.',          312, 1, 6),
('Tuscany',     'Italy',          'Rolling vineyards, Medici villas, and timeless Italian beauty.',       47, 0, 7),
('Switzerland', 'Switzerland',    'Alpine grandeur — skiing in winter, wildflowers in summer.',           63, 0, 8),
('Kerala',      'India',          'God\'s own country — backwaters, spice gardens, and Ayurveda.',        55, 0, 9),
('New York',    'USA',            'The city that never sleeps, from Midtown to Brooklyn.',               201, 0, 10);

-- ── Promotions ─────────────────────────────────────────────────────
INSERT INTO promotions (title, description, discount_type, discount_value, promo_code, valid_from, valid_until, min_booking_amount, status) VALUES
('Summer Escape',   'Enjoy 20% off on all featured properties this summer.',    'percentage', 20.00, 'SUMMER20', '2025-06-01', '2025-08-31', 200.00, 'active'),
('Honeymoon Deal',  '15% off on villas and overwater bungalows.',               'percentage', 15.00, 'HONEY15',  '2025-01-01', '2025-12-31', 500.00, 'active'),
('Last Minute',     '$50 off bookings made less than 72 hours before check-in.','fixed',       50.00, 'LAST50',   '2025-01-01', '2025-12-31', 300.00, 'active');

INSERT INTO promotion_hotels (promotion_id, hotel_id) VALUES
(1,1),(1,2),(1,7),(1,9),
(2,3),(2,4),(2,5),
(3,8),(3,11),(3,12);
