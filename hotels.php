<?php
define('MOONHERITAGE_ACCESS', true);
require_once 'config.php';

// Get search parameters
$location = sanitize($_GET['location'] ?? '');
$checkIn = sanitize($_GET['check_in'] ?? '');
$checkOut = sanitize($_GET['check_out'] ?? '');
$guests = (int)($_GET['guests'] ?? 1);
$category = sanitize($_GET['category'] ?? '');
$minPrice = (int)($_GET['min_price'] ?? 0);
$maxPrice = (int)($_GET['max_price'] ?? 10000);
$rating = (float)($_GET['rating'] ?? 0);
$sortBy = sanitize($_GET['sort'] ?? 'featured');
$page = max(1, (int)($_GET['page'] ?? 1));

// Build query
$db = getDB();
$where = ["h.status = 'active'"];
$params = [];

if ($location) {
    $where[] = "(h.city LIKE ? OR h.country LIKE ? OR h.name LIKE ?)";
    $searchTerm = "%$location%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
}

if ($category) {
    $where[] = "h.category = ?";
    $params[] = $category;
}

if ($minPrice > 0) {
    $where[] = "h.price_per_night >= ?";
    $params[] = $minPrice;
}

if ($maxPrice < 10000) {
    $where[] = "h.price_per_night <= ?";
    $params[] = $maxPrice;
}

if ($rating > 0) {
    $where[] = "h.star_rating >= ?";
    $params[] = $rating;
}

// Sorting
$orderBy = "h.featured DESC, h.created_at DESC";
switch ($sortBy) {
    case 'price_low':
        $orderBy = "h.price_per_night ASC";
        break;
    case 'price_high':
        $orderBy = "h.price_per_night DESC";
        break;
    case 'rating':
        $orderBy = "h.star_rating DESC";
        break;
    case 'popular':
        $orderBy = "h.view_count DESC";
        break;
}

// Count total hotels
$whereClause = implode(' AND ', $where);
$countQuery = "SELECT COUNT(*) as total FROM hotels h WHERE $whereClause";
$countStmt = $db->prepare($countQuery);
$countStmt->execute($params);
$totalHotels = $countStmt->fetch()['total'];

// Pagination
$pagination = paginate($totalHotels, $page, HOTELS_PER_PAGE);
$offset = $pagination['offset'];

// Fetch hotels
$query = "SELECT h.*, 
          (SELECT COUNT(*) FROM reviews WHERE hotel_id = h.id AND status = 'approved') as review_count,
          (SELECT AVG(rating) FROM reviews WHERE hotel_id = h.id AND status = 'approved') as avg_rating
          FROM hotels h 
          WHERE $whereClause 
          ORDER BY $orderBy 
          LIMIT " . HOTELS_PER_PAGE . " OFFSET $offset";

$stmt = $db->prepare($query);
$stmt->execute($params);
$hotels = $stmt->fetchAll();

// Get categories for filter
$categoriesQuery = "SELECT DISTINCT category, COUNT(*) as count FROM hotels WHERE status = 'active' GROUP BY category";
$categories = $db->query($categoriesQuery)->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Find Hotels - MoonHeritage</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="bg-gray-50">
    <!-- Navigation -->
    <nav class="bg-gray-900 text-white sticky top-0 z-50 shadow-lg">
        <div class="container mx-auto px-6 py-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-8">
                    <a href="home.html" class="flex items-center space-x-2">
                        <i class="fas fa-moon text-2xl"></i>
                        <span class="text-xl font-bold">MoonHeritage</span>
                    </a>
                    <div class="hidden md:flex space-x-6">
                        <a href="home.html" class="hover:text-gray-300 transition">Home</a>
                        <a href="hotels.php" class="text-blue-400">Hotels</a>
                        <a href="flights.php" class="hover:text-gray-300 transition">Flights</a>
                        <a href="tours.php" class="hover:text-gray-300 transition">Tours</a>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <?php if (isLoggedIn()): ?>
                        <a href="profile.php" class="hover:text-gray-300">
                            <i class="fas fa-user-circle text-xl"></i>
                            <?php echo escape($_SESSION['first_name'] ?? 'Profile'); ?>
                        </a>
                        <a href="logout.php" class="bg-red-600 px-4 py-2 rounded-lg hover:bg-red-700">Logout</a>
                    <?php else: ?>
                        <a href="login.php" class="hover:text-gray-300">Log In</a>
                        <a href="signup.php" class="bg-blue-600 px-4 py-2 rounded-lg hover:bg-blue-700">Sign Up</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <!-- Search Bar -->
    <section class="bg-white shadow-md py-6">
        <div class="container mx-auto px-6">
            <form method="GET" action="" class="grid grid-cols-1 md:grid-cols-5 gap-4">
                <div>
                    <input type="text" name="location" value="<?php echo escape($location); ?>" 
                           placeholder="Where are you going?" 
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <input type="date" name="check_in" value="<?php echo escape($checkIn); ?>"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <input type="date" name="check_out" value="<?php echo escape($checkOut); ?>"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <select name="category" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <option value="">All Types</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat['category']; ?>" <?php echo $category === $cat['category'] ? 'selected' : ''; ?>>
                                <?php echo ucfirst($cat['category']); ?> (<?php echo $cat['count']; ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <button type="submit" class="w-full bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 font-semibold">
                        <i class="fas fa-search mr-2"></i>Search
                    </button>
                </div>
            </form>
        </div>
    </section>

    <!-- Main Content -->
    <section class="py-8">
        <div class="container mx-auto px-6">
            <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
                <!-- Filters Sidebar -->
                <aside class="lg:col-span-1">
                    <div class="bg-white rounded-lg shadow-md p-6 sticky top-24">
                        <h3 class="text-xl font-bold mb-4">Filters</h3>
                        
                        <form method="GET" action="" id="filterForm">
                            <input type="hidden" name="location" value="<?php echo escape($location); ?>">
                            
                            <!-- Price Range -->
                            <div class="mb-6">
                                <h4 class="font-semibold mb-3">Price Range</h4>
                                <div class="space-y-3">
                                    <div>
                                        <label class="text-sm text-gray-600">Min Price</label>
                                        <input type="number" name="min_price" value="<?php echo $minPrice; ?>" 
                                               class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                                    </div>
                                    <div>
                                        <label class="text-sm text-gray-600">Max Price</label>
                                        <input type="number" name="max_price" value="<?php echo $maxPrice; ?>" 
                                               class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                                    </div>
                                </div>
                            </div>

                            <!-- Star Rating -->
                            <div class="mb-6">
                                <h4 class="font-semibold mb-3">Star Rating</h4>
                                <div class="space-y-2">
                                    <?php for ($i = 5; $i >= 1; $i--): ?>
                                        <label class="flex items-center cursor-pointer hover:bg-gray-50 p-2 rounded">
                                            <input type="radio" name="rating" value="<?php echo $i; ?>" 
                                                   <?php echo $rating == $i ? 'checked' : ''; ?>
                                                   class="mr-3">
                                            <div class="flex text-yellow-400">
                                                <?php for ($j = 0; $j < $i; $j++): ?>
                                                    <i class="fas fa-star"></i>
                                                <?php endfor; ?>
                                            </div>
                                            <span class="ml-2 text-gray-600">& up</span>
                                        </label>
                                    <?php endfor; ?>
                                </div>
                            </div>

                            <!-- Property Type -->
                            <div class="mb-6">
                                <h4 class="font-semibold mb-3">Property Type</h4>
                                <div class="space-y-2">
                                    <?php foreach ($categories as $cat): ?>
                                        <label class="flex items-center cursor-pointer hover:bg-gray-50 p-2 rounded">
                                            <input type="radio" name="category" value="<?php echo $cat['category']; ?>"
                                                   <?php echo $category === $cat['category'] ? 'checked' : ''; ?>
                                                   class="mr-3">
                                            <span><?php echo ucfirst($cat['category']); ?></span>
                                            <span class="ml-auto text-gray-500 text-sm">(<?php echo $cat['count']; ?>)</span>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                            <button type="submit" class="w-full bg-blue-600 text-white py-3 rounded-lg hover:bg-blue-700 font-semibold">
                                Apply Filters
                            </button>
                            <a href="hotels.php" class="block text-center mt-3 text-blue-600 hover:underline">Clear All</a>
                        </form>
                    </div>
                </aside>

                <!-- Hotels Grid -->
                <div class="lg:col-span-3">
                    <!-- Results Header -->
                    <div class="flex justify-between items-center mb-6">
                        <div>
                            <h2 class="text-2xl font-bold">
                                <?php echo $location ? "Hotels in " . escape($location) : "All Hotels"; ?>
                            </h2>
                            <p class="text-gray-600"><?php echo $totalHotels; ?> properties found</p>
                        </div>
                        <div>
                            <select name="sort" onchange="this.form.submit()" form="filterForm"
                                    class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                <option value="featured" <?php echo $sortBy === 'featured' ? 'selected' : ''; ?>>Featured</option>
                                <option value="price_low" <?php echo $sortBy === 'price_low' ? 'selected' : ''; ?>>Price: Low to High</option>
                                <option value="price_high" <?php echo $sortBy === 'price_high' ? 'selected' : ''; ?>>Price: High to Low</option>
                                <option value="rating" <?php echo $sortBy === 'rating' ? 'selected' : ''; ?>>Highest Rated</option>
                                <option value="popular" <?php echo $sortBy === 'popular' ? 'selected' : ''; ?>>Most Popular</option>
                            </select>
                        </div>
                    </div>

                    <!-- Hotels List -->
                    <div class="space-y-6">
                        <?php if (empty($hotels)): ?>
                            <div class="bg-white rounded-lg shadow-md p-12 text-center">
                                <i class="fas fa-search text-6xl text-gray-300 mb-4"></i>
                                <h3 class="text-xl font-bold text-gray-800 mb-2">No hotels found</h3>
                                <p class="text-gray-600 mb-4">Try adjusting your filters or search criteria</p>
                                <a href="hotels.php" class="inline-block bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700">
                                    View All Hotels
                                </a>
                            </div>
                        <?php else: ?>
                            <?php foreach ($hotels as $hotel): ?>
                                <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-xl transition cursor-pointer">
                                    <div class="md:flex">
                                        <div class="md:w-1/3 relative">
                                            <img src="<?php echo getImageUrl($hotel['main_image']); ?>" 
                                                 alt="<?php echo escape($hotel['name']); ?>" 
                                                 class="w-full h-64 md:h-full object-cover">
                                            <?php if ($hotel['featured']): ?>
                                                <span class="absolute top-3 left-3 bg-yellow-500 text-white px-3 py-1 rounded-full text-sm font-semibold">
                                                    <i class="fas fa-star mr-1"></i>Featured
                                                </span>
                                            <?php endif; ?>
                                            <?php if ($hotel['discount_percentage'] > 0): ?>
                                                <span class="absolute top-3 right-3 bg-red-500 text-white px-3 py-1 rounded-full text-sm font-semibold">
                                                    -<?php echo $hotel['discount_percentage']; ?>%
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="md:w-2/3 p-6">
                                            <div class="flex justify-between items-start mb-3">
                                                <div>
                                                    <h3 class="text-2xl font-bold text-gray-800 mb-1">
                                                        <a href="hotel-details.php?slug=<?php echo $hotel['slug']; ?>" class="hover:text-blue-600">
                                                            <?php echo escape($hotel['name']); ?>
                                                        </a>
                                                    </h3>
                                                    <p class="text-gray-600">
                                                        <i class="fas fa-map-marker-alt mr-2"></i>
                                                        <?php echo escape($hotel['city'] . ', ' . $hotel['country']); ?>
                                                    </p>
                                                </div>
                                                <button onclick="toggleWishlist(<?php echo $hotel['id']; ?>, this)" 
                                                        class="text-gray-400 hover:text-red-500 text-2xl">
                                                    <i class="far fa-heart"></i>
                                                </button>
                                            </div>

                                            <div class="flex items-center mb-3">
                                                <?php echo getStarRating($hotel['star_rating']); ?>
                                                <?php if ($hotel['review_count'] > 0): ?>
                                                    <span class="ml-3 text-gray-600">
                                                        <?php echo number_format($hotel['avg_rating'], 1); ?>
                                                        (<?php echo $hotel['review_count']; ?> reviews)
                                                    </span>
                                                <?php endif; ?>
                                            </div>

                                            <p class="text-gray-700 mb-4 line-clamp-2">
                                                <?php echo escape($hotel['short_description']); ?>
                                            </p>

                                            <div class="flex items-center justify-between">
                                                <div>
                                                    <?php if ($hotel['discount_percentage'] > 0): ?>
                                                        <span class="text-gray-400 line-through text-sm">
                                                            <?php echo formatPrice($hotel['original_price']); ?>
                                                        </span>
                                                    <?php endif; ?>
                                                    <div>
                                                        <span class="text-3xl font-bold text-blue-600">
                                                            <?php echo formatPrice($hotel['price_per_night']); ?>
                                                        </span>
                                                        <span class="text-gray-600">/night</span>
                                                    </div>
                                                </div>
                                                <a href="hotel-details.php?slug=<?php echo $hotel['slug']; ?>" 
                                                   class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition font-semibold">
                                                    View Details
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <!-- Pagination -->
                    <?php if ($pagination['total_pages'] > 1): ?>
                        <div class="flex justify-center items-center mt-8 space-x-2">
                            <?php if ($pagination['has_previous']): ?>
                                <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>" 
                                   class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
                                    <i class="fas fa-chevron-left"></i>
                                </a>
                            <?php endif; ?>

                            <?php for ($i = 1; $i <= $pagination['total_pages']; $i++): ?>
                                <?php if ($i == $page): ?>
                                    <span class="px-4 py-2 bg-blue-600 text-white rounded-lg"><?php echo $i; ?></span>
                                <?php else: ?>
                                    <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>" 
                                       class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
                                        <?php echo $i; ?>
                                    </a>
                                <?php endif; ?>
                            <?php endfor; ?>

                            <?php if ($pagination['has_next']): ?>
                                <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>" 
                                   class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <?php include 'includes/footer.php'; ?>

    <script src="js/main.js"></script>
    <script>
        function toggleWishlist(hotelId, button) {
            <?php if (!isLoggedIn()): ?>
                window.location.href = 'login.php';
                return;
            <?php endif; ?>

            const icon = button.querySelector('i');
            
            fetch('api/wishlist.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ hotel_id: hotelId })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    if (data.action === 'added') {
                        icon.classList.remove('far');
                        icon.classList.add('fas');
                        button.classList.add('text-red-500');
                        showToast('Added to wishlist', 'success');
                    } else {
                        icon.classList.remove('fas');
                        icon.classList.add('far');
                        button.classList.remove('text-red-500');
                        showToast('Removed from wishlist', 'info');
                    }
                }
            });
        }

        // Auto-submit form when filters change
        document.querySelectorAll('input[type="radio"]').forEach(radio => {
            radio.addEventListener('change', function() {
                document.getElementById('filterForm').submit();
            });
        });
    </script>
</body>
</html>