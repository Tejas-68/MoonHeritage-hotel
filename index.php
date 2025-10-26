<?php
define('MOONHERITAGE_ACCESS', true);
require_once 'config.php';

// Fetch featured hotels
$db = getDB();
$featuredHotelsStmt = $db->query("
    SELECT h.*, 
    (SELECT AVG(rating) FROM reviews WHERE hotel_id = h.id AND status = 'approved') as avg_rating,
    (SELECT COUNT(*) FROM reviews WHERE hotel_id = h.id AND status = 'approved') as review_count
    FROM hotels h 
    WHERE h.status = 'active' AND h.category = 'hotel' AND h.featured = 1
    ORDER BY h.view_count DESC
    LIMIT 8
");
$featuredHotels = $featuredHotelsStmt->fetchAll();

// Fetch featured villas
$featuredVillasStmt = $db->query("
    SELECT h.*, 
    (SELECT AVG(rating) FROM reviews WHERE hotel_id = h.id AND status = 'approved') as avg_rating,
    (SELECT COUNT(*) FROM reviews WHERE hotel_id = h.id AND status = 'approved') as review_count
    FROM hotels h 
    WHERE h.status = 'active' AND h.category = 'villa' AND h.featured = 1
    ORDER BY h.view_count DESC
    LIMIT 8
");
$featuredVillas = $featuredVillasStmt->fetchAll();

// Fetch all amenities for filter
$amenitiesStmt = $db->query("SELECT * FROM amenities ORDER BY name");
$amenities = $amenitiesStmt->fetchAll();

// Fetch active promotions
$promotionsStmt = $db->query("
    SELECT * FROM promotions 
    WHERE status = 'active' 
    AND valid_from <= CURDATE() 
    AND valid_until >= CURDATE()
    LIMIT 2
");
$promotions = $promotionsStmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MoonHeritage - Find Your Best Staycation</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="bg-gray-50">
    <?php if (isLoggedIn() && isAdmin() && !isset($_GET['user_view'])): ?>
    <!-- Admin Notice -->
    <div class="bg-yellow-500 text-white py-2 px-6 text-center">
        <i class="fas fa-exclamation-triangle mr-2"></i>
        You're viewing as Admin. 
        <a href="admin/dashboard.php" class="underline font-semibold ml-2">Go to Admin Panel</a>
        <span class="mx-2">|</span>
        <a href="logout.php" class="underline font-semibold">Logout</a>
    </div>
    <?php endif; ?>
    
    <!-- Navigation -->
    <nav class="bg-black text-white sticky top-0 z-50 shadow-lg">
        <div class="container mx-auto px-6 py-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-8">
                    <a href="index.php" class="flex items-center space-x-2">
                        <i class="fas fa-moon text-2xl"></i>
                        <span class="text-xl font-bold">MoonHeritage</span>
                    </a>
                    <div class="hidden md:flex space-x-6">
                        <a href="index.php" class="hover:text-gray-300 transition">Home</a>
                        <a href="hotels.php" class="hover:text-gray-300 transition">Hotels</a>
                        <a href="hotels.php?category=villa" class="hover:text-gray-300 transition">Villas</a>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <?php if (isLoggedIn()): ?>
                        <?php if (isAdmin()): ?>
                            <a href="admin/dashboard.php" class="bg-purple-600 px-4 py-2 rounded-lg hover:bg-purple-700">
                                <i class="fas fa-chart-line mr-2"></i>Admin Panel
                            </a>
                        <?php endif; ?>
                        <a href="profile.php" class="hover:text-gray-300">
                            <i class="fas fa-user-circle text-xl"></i>
                        </a>
                        <a href="logout.php" class="bg-red-600 px-4 py-2 rounded-lg hover:bg-red-700">Logout</a>
                    <?php else: ?>
                        <a href="login.php" class="hover:text-gray-300">Log In</a>
                        <a href="signup.php" class="bg-blue-600 px-4 py-2 rounded-lg hover:bg-blue-700 transition">Sign Up</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="relative h-screen bg-cover bg-center" style="background-image:linear-gradient(rgba(0,0,0,0.4), rgba(0,0,0,0.4)),url('images/hero-bg.jpg');">
        <div class="container mx-auto px-6 h-full flex items-center">
            <div class="text-white max-w-4xl">
                <h1 class="text-6xl font-bold mb-6">Find Your Best Staycation</h1>
                
                <!-- Search Box -->
                <div class="bg-white rounded-lg shadow-2xl p-6 mt-8">
                    <form action="hotels.php" method="GET">
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
                            <div class="relative">
                                <label class="text-gray-700 text-sm font-semibold mb-2 block">Location</label>
                                <input type="text" name="location" placeholder="Add destination" 
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-gray-800">
                                <i class="fas fa-map-marker-alt absolute right-3 top-11 text-gray-400"></i>
                            </div>
                            <div class="relative">
                                <label class="text-gray-700 text-sm font-semibold mb-2 block">Check in</label>
                                <input type="date" name="check_in"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-gray-800">
                            </div>
                            <div class="relative">
                                <label class="text-gray-700 text-sm font-semibold mb-2 block">Check out</label>
                                <input type="date" name="check_out"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-gray-800">
                            </div>
                            <div class="flex items-end">
                                <button type="submit" class="w-full bg-black text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition font-semibold">
                                    Search <i class="fas fa-arrow-right ml-2"></i>
                                </button>
                            </div>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <span class="text-gray-700 text-sm font-semibold">Filter:</span>
                            <button type="button" onclick="filterByCategory('hotel')" class="px-4 py-1 bg-blue-600 text-white text-sm rounded-full hover:bg-blue-700">Hotel</button>
                            <button type="button" onclick="filterByCategory('villa')" class="px-4 py-1 bg-gray-200 text-gray-700 text-sm rounded-full hover:bg-gray-300">Villa</button>
                            <button type="button" onclick="filterByCategory('resort')" class="px-4 py-1 bg-gray-200 text-gray-700 text-sm rounded-full hover:bg-gray-300">Resorts</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- Featured Hotels Section -->
    <section class="py-16 bg-gray-50">
        <div class="container mx-auto px-6">
            <div class="flex justify-between items-center mb-8">
                <div>
                    <h2 class="text-4xl font-bold mb-2">Top Trending Hotels</h2>
                    <p class="text-gray-600">Discover the most trending hotels worldwide for an unforgettable experience.</p>
                </div>
                <a href="hotels.php?category=hotel" class="text-blue-600 hover:underline flex items-center">
                    See All <i class="fas fa-arrow-right ml-2"></i>
                </a>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <?php foreach ($featuredHotels as $hotel): ?>
                <div class="bg-white rounded-lg shadow-lg overflow-hidden transform hover:scale-105 transition">
                    <div class="relative">
                        <img src="<?php echo getImageUrl($hotel['main_image']); ?>" alt="<?php echo escape($hotel['name']); ?>" class="w-full h-48 object-cover">
                        <button class="absolute top-3 right-3 bg-white rounded-full p-2 hover:bg-gray-100">
                            <i class="far fa-heart text-gray-600"></i>
                        </button>
                        <?php if ($hotel['discount_percentage'] > 0): ?>
                        <span class="absolute top-3 left-3 bg-red-500 text-white px-3 py-1 rounded-full text-sm font-semibold">
                            -<?php echo $hotel['discount_percentage']; ?>%
                        </span>
                        <?php endif; ?>
                    </div>
                    <div class="p-4">
                        <h3 class="font-bold text-lg mb-1"><?php echo escape($hotel['name']); ?></h3>
                        <p class="text-gray-600 text-sm mb-2">
                            <i class="fas fa-map-marker-alt"></i> <?php echo escape($hotel['city'] . ', ' . $hotel['country']); ?>
                        </p>
                        <div class="flex items-center mb-3">
                            <?php echo getStarRating($hotel['star_rating']); ?>
                            <?php if ($hotel['review_count'] > 0): ?>
                            <span class="text-gray-600 text-sm ml-2">(<?php echo $hotel['review_count']; ?> Reviews)</span>
                            <?php endif; ?>
                        </div>
                        <div class="flex justify-between items-center">
                            <div>
                                <span class="text-2xl font-bold text-blue-600"><?php echo formatPrice($hotel['price_per_night']); ?></span>
                                <span class="text-gray-500 text-sm">/night</span>
                                <?php if ($hotel['original_price'] > $hotel['price_per_night']): ?>
                                <span class="text-gray-400 line-through text-sm ml-1"><?php echo formatPrice($hotel['original_price']); ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <a href="hotel-details.php?slug=<?php echo $hotel['slug']; ?>" class="mt-4 block text-center bg-blue-600 text-white py-2 rounded-lg hover:bg-blue-700">
                            View Details
                        </a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Featured Villas Section -->
    <section class="py-16 bg-white">
        <div class="container mx-auto px-6">
            <div class="flex justify-between items-center mb-8">
                <div>
                    <h2 class="text-4xl font-bold mb-2">Luxury Villas</h2>
                    <p class="text-gray-600">Experience privacy and luxury in our exclusive villa collection.</p>
                </div>
                <a href="hotels.php?category=villa" class="text-blue-600 hover:underline flex items-center">
                    See All <i class="fas fa-arrow-right ml-2"></i>
                </a>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <?php foreach ($featuredVillas as $villa): ?>
                <div class="bg-white rounded-lg shadow-lg overflow-hidden transform hover:scale-105 transition">
                    <div class="relative">
                        <img src="<?php echo getImageUrl($villa['main_image']); ?>" alt="<?php echo escape($villa['name']); ?>" class="w-full h-48 object-cover">
                        <button class="absolute top-3 right-3 bg-white rounded-full p-2 hover:bg-gray-100">
                            <i class="far fa-heart text-gray-600"></i>
                        </button>
                    </div>
                    <div class="p-4">
                        <h3 class="font-bold text-lg mb-1"><?php echo escape($villa['name']); ?></h3>
                        <p class="text-gray-600 text-sm mb-2">
                            <i class="fas fa-map-marker-alt"></i> <?php echo escape($villa['city'] . ', ' . $villa['country']); ?>
                        </p>
                        <div class="flex items-center mb-3">
                            <?php echo getStarRating($villa['star_rating']); ?>
                            <?php if ($villa['review_count'] > 0): ?>
                            <span class="text-gray-600 text-sm ml-2">(<?php echo $villa['review_count']; ?> Reviews)</span>
                            <?php endif; ?>
                        </div>
                        <div class="flex justify-between items-center">
                            <div>
                                <span class="text-2xl font-bold text-blue-600"><?php echo formatPrice($villa['price_per_night']); ?></span>
                                <span class="text-gray-500 text-sm">/night</span>
                            </div>
                        </div>
                        <a href="hotel-details.php?slug=<?php echo $villa['slug']; ?>" class="mt-4 block text-center bg-blue-600 text-white py-2 rounded-lg hover:bg-blue-700">
                            View Details
                        </a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Facilities Filter Section -->
    <section class="py-16 bg-gray-50">
        <div class="container mx-auto px-6">
            <h2 class="text-4xl font-bold mb-4 text-center">Find Properties by Amenities</h2>
            <p class="text-gray-600 mb-8 text-center">Select facilities that matter most to you</p>
            
            <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
                <?php foreach ($amenities as $amenity): ?>
                <a href="hotels.php?amenity=<?php echo $amenity['id']; ?>" 
                   class="bg-white p-6 rounded-lg shadow-md hover:shadow-xl transition text-center group cursor-pointer">
                    <i class="fas <?php echo $amenity['icon']; ?> text-4xl text-blue-600 mb-3 group-hover:scale-110 transition"></i>
                    <h3 class="font-semibold text-gray-800"><?php echo escape($amenity['name']); ?></h3>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Promotions Section -->
    <?php if (!empty($promotions)): ?>
    <section class="py-16 bg-white">
        <div class="container mx-auto px-6">
            <div class="flex justify-between items-center mb-8">
                <h2 class="text-4xl font-bold">Get promo for a cheaper price</h2>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <?php foreach ($promotions as $promo): ?>
                <div class="relative rounded-lg overflow-hidden shadow-lg h-64" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                    <div class="absolute inset-0 flex items-center p-8">
                        <div class="text-white">
                            <div class="flex items-center mb-2">
                                <div class="bg-yellow-400 rounded-full p-3 mr-4">
                                    <i class="fas fa-percent text-2xl text-gray-900"></i>
                                </div>
                                <span class="text-sm">Valid: <?php echo formatDate($promo['valid_from']); ?> - <?php echo formatDate($promo['valid_until']); ?></span>
                            </div>
                            <h3 class="text-2xl font-bold mb-2"><?php echo escape($promo['title']); ?></h3>
                            <p class="text-lg mb-4"><?php echo escape($promo['description']); ?></p>
                            <div class="text-6xl font-bold"><?php echo $promo['discount_value']; ?>%</div>
                            <?php if ($promo['promo_code']): ?>
                            <div class="mt-4 bg-white text-gray-900 inline-block px-4 py-2 rounded-lg font-bold">
                                CODE: <?php echo $promo['promo_code']; ?>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white pt-16 pb-8">
        <div class="container mx-auto px-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8 mb-12">
                <div>
                    <div class="flex items-center space-x-2 mb-4">
                        <i class="fas fa-moon text-2xl"></i>
                        <span class="text-xl font-bold">MoonHeritage</span>
                    </div>
                    <p class="text-gray-400 mb-4">Making travel more accessible to everyone.</p>
                    <div class="flex space-x-4">
                        <a href="#" class="bg-gray-800 p-2 rounded-full hover:bg-gray-700 transition">
                            <i class="fab fa-instagram"></i>
                        </a>
                        <a href="#" class="bg-gray-800 p-2 rounded-full hover:bg-gray-700 transition">
                            <i class="fab fa-facebook"></i>
                        </a>
                        <a href="#" class="bg-gray-800 p-2 rounded-full hover:bg-gray-700 transition">
                            <i class="fab fa-twitter"></i>
                        </a>
                    </div>
                </div>

                <div>
                    <h4 class="font-bold text-lg mb-4">About</h4>
                    <ul class="space-y-2 text-gray-400">
                        <li><a href="#" class="hover:text-white transition">About Us</a></li>
                        <li><a href="#" class="hover:text-white transition">Features</a></li>
                        <li><a href="#" class="hover:text-white transition">Careers</a></li>
                    </ul>
                </div>

                <div>
                    <h4 class="font-bold text-lg mb-4">Support</h4>
                    <ul class="space-y-2 text-gray-400">
                        <li><a href="#" class="hover:text-white transition">Contact Us</a></li>
                        <li><a href="#" class="hover:text-white transition">FAQ</a></li>
                    </ul>
                </div>

                <div>
                    <h4 class="font-bold text-lg mb-4">Newsletter</h4>
                    <form action="api/newsletter.php" method="POST">
                        <input type="email" name="email" placeholder="Enter your email" required
                               class="w-full px-4 py-2 rounded-lg bg-gray-800 border border-gray-700 mb-2">
                        <button type="submit" class="w-full bg-blue-600 px-4 py-2 rounded-lg hover:bg-blue-700">
                            Subscribe
                        </button>
                    </form>
                </div>
            </div>

            <div class="border-t border-gray-800 pt-8 text-center text-gray-400 text-sm">
                © 2025 MoonHeritage. All rights reserved.
            </div>
        </div>
    </footer>

    <script>
        function filterByCategory(category) {
            window.location.href = 'hotels.php?category=' + category;
        }
    </script>
</body>
</html>