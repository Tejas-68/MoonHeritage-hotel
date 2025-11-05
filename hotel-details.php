<?php
define('MOONHERITAGE_ACCESS', true);
require_once 'config.php';


$slug = sanitize($_GET['slug'] ?? '');

if (empty($slug)) {
    redirect('hotels.php');
}


$db = getDB();
$stmt = $db->prepare("
    SELECT h.*, 
    (SELECT COUNT(*) FROM reviews WHERE hotel_id = h.id AND status = 'approved') as review_count,
    (SELECT AVG(rating) FROM reviews WHERE hotel_id = h.id AND status = 'approved') as avg_rating
    FROM hotels h 
    WHERE h.slug = ? AND h.status = 'active'
");
$stmt->execute([$slug]);
$hotel = $stmt->fetch();

if (!$hotel) {
    redirect('hotels.php');
}


$db->prepare("UPDATE hotels SET view_count = view_count + 1 WHERE id = ?")->execute([$hotel['id']]);


$imagesStmt = $db->prepare("SELECT * FROM hotel_images WHERE hotel_id = ? ORDER BY is_primary DESC, display_order ASC");
$imagesStmt->execute([$hotel['id']]);
$images = $imagesStmt->fetchAll();


$amenitiesStmt = $db->prepare("
    SELECT a.* FROM amenities a
    INNER JOIN hotel_amenities ha ON a.id = ha.amenity_id
    WHERE ha.hotel_id = ?
");
$amenitiesStmt->execute([$hotel['id']]);
$amenities = $amenitiesStmt->fetchAll();


$reviewsStmt = $db->prepare("
    SELECT r.*, u.first_name, u.last_name, u.profile_image
    FROM reviews r
    INNER JOIN users u ON r.user_id = u.id
    WHERE r.hotel_id = ? AND r.status = 'approved'
    ORDER BY r.created_at DESC
    LIMIT 10
");
$reviewsStmt->execute([$hotel['id']]);
$reviews = $reviewsStmt->fetchAll();


$inWishlist = false;
if (isLoggedIn()) {
    $wishlistStmt = $db->prepare("SELECT id FROM wishlist WHERE user_id = ? AND hotel_id = ?");
    $wishlistStmt->execute([getUserId(), $hotel['id']]);
    $inWishlist = $wishlistStmt->fetch() !== false;
}


$similarStmt = $db->prepare("
    SELECT * FROM hotels 
    WHERE category = ? AND id != ? AND status = 'active'
    ORDER BY RAND()
    LIMIT 4
");
$similarStmt->execute([$hotel['category'], $hotel['id']]);
$similarHotels = $similarStmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo escape($hotel['name']); ?> - MoonHeritage</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .gallery-img { cursor: pointer; transition: transform 0.3s; }
        .gallery-img:hover { transform: scale(1.05); }
        .modal { display: none; }
        .modal.active { display: flex; }
    </style>
</head>
<body class="bg-gray-50">
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

    <section class="bg-white">
        <div class="container mx-auto px-6 py-6">
            <div class="grid grid-cols-4 gap-2 h-96">
                <div class="col-span-2 row-span-2">
                    <img src="<?php echo getImageUrl($hotel['main_image']); ?>" 
                         alt="<?php echo escape($hotel['name']); ?>" 
                         class="w-full h-full object-cover rounded-l-lg gallery-img"
                         onclick="openLightbox(0)">
                </div>
                <?php 
                $displayImages = array_slice($images, 0, 4);
                foreach ($displayImages as $index => $image): 
                ?>
                    <div class="<?php echo $index === 3 ? 'relative' : ''; ?>">
                        <img src="<?php echo getImageUrl($image['image_path']); ?>" 
                             alt="<?php echo escape($image['caption'] ?? 'Hotel Image'); ?>" 
                             class="w-full h-full object-cover gallery-img <?php echo $index === 1 ? 'rounded-tr-lg' : ($index === 3 ? 'rounded-br-lg' : ''); ?>"
                             onclick="openLightbox(<?php echo $index + 1; ?>)">
                        <?php if ($index === 3 && count($images) > 5): ?>
                            <div class="absolute inset-0 bg-black bg-opacity-50 flex items-center justify-center rounded-br-lg cursor-pointer"
                                 onclick="openLightbox(0)">
                                <span class="text-white text-xl font-bold">
                                    <i class="fas fa-images mr-2"></i>+<?php echo count($images) - 5; ?> Photos
                                </span>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="py-8">
        <div class="container mx-auto px-6">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <div class="lg:col-span-2 space-y-6">
                    
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <div class="flex justify-between items-start mb-4">
                            <div>
                                <h1 class="text-4xl font-bold text-gray-800 mb-2"><?php echo escape($hotel['name']); ?></h1>
                                <p class="text-gray-600 flex items-center">
                                    <i class="fas fa-map-marker-alt mr-2"></i>
                                    <?php echo escape($hotel['address'] . ', ' . $hotel['city'] . ', ' . $hotel['country']); ?>
                                </p>
                            </div>
                            <button onclick="toggleWishlist()" class="text-3xl <?php echo $inWishlist ? 'text-red-500' : 'text-gray-400'; ?> hover:text-red-500">
                                <i class="<?php echo $inWishlist ? 'fas' : 'far'; ?> fa-heart"></i>
                            </button>
                        </div>
                        
                        <div class="flex items-center gap-4 mb-4">
                            <?php echo getStarRating($hotel['star_rating']); ?>
                            <?php if ($hotel['review_count'] > 0): ?>
                                <span class="text-gray-600">
                                    <?php echo number_format($hotel['avg_rating'], 1); ?>/5.0
                                    (<?php echo $hotel['review_count']; ?> reviews)
                                </span>
                            <?php endif; ?>
                            <span class="px-3 py-1 bg-blue-100 text-blue-600 rounded-full text-sm font-semibold">
                                <?php echo ucfirst($hotel['category']); ?>
                            </span>
                        </div>

                        <div class="flex gap-4 text-sm text-gray-600">
                            <span><i class="fas fa-door-open mr-2"></i><?php echo $hotel['total_rooms']; ?> Rooms</span>
                            <span><i class="fas fa-clock mr-2"></i>Check-in: <?php echo date('g:i A', strtotime($hotel['check_in_time'])); ?></span>
                            <span><i class="fas fa-clock mr-2"></i>Check-out: <?php echo date('g:i A', strtotime($hotel['check_out_time'])); ?></span>
                        </div>
                    </div>

                    
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <h2 class="text-2xl font-bold mb-4">About This Property</h2>
                        <p class="text-gray-700 leading-relaxed"><?php echo nl2br(escape($hotel['description'])); ?></p>
                    </div>

                    <?php if (!empty($amenities)): ?>
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <h2 class="text-2xl font-bold mb-4">Amenities</h2>
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                            <?php foreach ($amenities as $amenity): ?>
                                <div class="flex items-center space-x-3 p-3 bg-gray-50 rounded-lg">
                                    <i class="fas <?php echo $amenity['icon']; ?> text-blue-600 text-xl"></i>
                                    <span class="text-gray-700"><?php echo escape($amenity['name']); ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    
                    
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <div class="flex justify-between items-center mb-6">
                            <h2 class="text-2xl font-bold">Guest Reviews</h2>
                            <?php if (isLoggedIn()): ?>
                                <button onclick="openReviewModal()" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                                    <i class="fas fa-plus mr-2"></i>Write a Review
                                </button>
                            <?php endif; ?>
                        </div>

                        <?php if (!empty($reviews)): ?>
                            <div class="space-y-6">
                                <?php foreach ($reviews as $review): ?>
                                    <div class="border-b border-gray-200 pb-6 last:border-0">
                                        <div class="flex items-start gap-4">
                                            <div class="w-12 h-12 bg-blue-600 rounded-full flex items-center justify-center text-white font-bold">
                                                <?php echo strtoupper(substr($review['first_name'], 0, 1)); ?>
                                            </div>
                                            <div class="flex-1">
                                                <div class="flex justify-between items-start mb-2">
                                                    <div>
                                                        <h4 class="font-semibold"><?php echo escape($review['first_name'] . ' ' . $review['last_name']); ?></h4>
                                                        <p class="text-sm text-gray-500"><?php echo formatDate($review['created_at'], 'M d, Y'); ?></p>
                                                    </div>
                                                    <?php echo getStarRating($review['rating']); ?>
                                                </div>
                                                <?php if ($review['title']): ?>
                                                    <h5 class="font-semibold mb-2"><?php echo escape($review['title']); ?></h5>
                                                <?php endif; ?>
                                                <p class="text-gray-700"><?php echo nl2br(escape($review['comment'])); ?></p>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p class="text-gray-500 text-center py-8">No reviews yet. Be the first to review!</p>
                        <?php endif; ?>
                    </div>

                    <?php if (!empty($similarHotels)): ?>
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <h2 class="text-2xl font-bold mb-6">Similar Properties</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <?php foreach ($similarHotels as $similar): ?>
                                <a href="hotel-details.php?slug=<?php echo $similar['slug']; ?>" 
                                   class="border border-gray-200 rounded-lg overflow-hidden hover:shadow-lg transition">
                                    <img src="<?php echo getImageUrl($similar['main_image']); ?>" 
                                         alt="<?php echo escape($similar['name']); ?>" 
                                         class="w-full h-40 object-cover">
                                    <div class="p-4">
                                        <h4 class="font-bold mb-1"><?php echo escape($similar['name']); ?></h4>
                                        <p class="text-sm text-gray-600 mb-2">
                                            <i class="fas fa-map-marker-alt mr-1"></i><?php echo escape($similar['city']); ?>
                                        </p>
                                        <div class="flex justify-between items-center">
                                            <?php echo getStarRating($similar['star_rating']); ?>
                                            <span class="font-bold text-blue-600"><?php echo formatPrice($similar['price_per_night']); ?>/night</span>
                                        </div>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

                <div class="lg:col-span-1">
                    <div class="bg-white rounded-lg shadow-md p-6 sticky top-24">
                        <div class="mb-6">
                            <?php if ($hotel['discount_percentage'] > 0): ?>
                                <div class="flex items-center gap-2 mb-2">
                                    <span class="text-gray-500 line-through text-lg">
                                        <?php echo formatPrice($hotel['original_price']); ?>
                                    </span>
                                    <span class="bg-red-500 text-white px-2 py-1 rounded text-sm font-semibold">
                                        -<?php echo $hotel['discount_percentage']; ?>%
                                    </span>
                                </div>
                            <?php endif; ?>
                            <div class="flex items-baseline gap-2">
                                <span class="text-4xl font-bold text-blue-600">
                                    <?php echo formatPrice($hotel['price_per_night']); ?>
                                </span>
                                <span class="text-gray-600">/night</span>
                            </div>
                        </div>

                        <form method="POST" action="booking.php" class="space-y-4">
                            <input type="hidden" name="hotel_id" value="<?php echo $hotel['id']; ?>">
                            
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Check-in Date</label>
                                <input type="date" name="check_in" required
                                       min="<?php echo date('Y-m-d'); ?>"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Check-out Date</label>
                                <input type="date" name="check_out" required
                                       min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">Adults</label>
                                    <select name="adults" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                        <?php for ($i = 1; $i <= 10; $i++): ?>
                                            <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                                        <?php endfor; ?>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">Children</label>
                                    <select name="children" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                        <?php for ($i = 0; $i <= 5; $i++): ?>
                                            <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                                        <?php endfor; ?>
                                    </select>
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Number of Rooms</label>
                                <select name="rooms" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                    <?php for ($i = 1; $i <= min(5, $hotel['available_rooms']); $i++): ?>
                                        <option value="<?php echo $i; ?>"><?php echo $i; ?> Room<?php echo $i > 1 ? 's' : ''; ?></option>
                                    <?php endfor; ?>
                                </select>
                            </div>

                            <button type="submit" class="w-full bg-blue-600 text-white py-4 rounded-lg hover:bg-blue-700 font-semibold text-lg transition">
                                <i class="fas fa-calendar-check mr-2"></i>Book Now
                            </button>
                        </form>

                        <div class="mt-6 pt-6 border-t border-gray-200">
                            <div class="flex items-center text-sm text-gray-600 mb-2">
                                <i class="fas fa-check-circle text-green-500 mr-2"></i>
                                Free cancellation
                            </div>
                            <div class="flex items-center text-sm text-gray-600 mb-2">
                                <i class="fas fa-check-circle text-green-500 mr-2"></i>
                                No prepayment needed
                            </div>
                            <div class="flex items-center text-sm text-gray-600">
                                <i class="fas fa-check-circle text-green-500 mr-2"></i>
                                Instant confirmation
                            </div>
                        </div>
                        <div class="mt-6">
                            <a href="tel:<?php echo $hotel['phone']; ?>" class="block text-center text-blue-600 hover:underline">
                                <i class="fas fa-phone mr-2"></i>Contact Property
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <div id="lightbox" class="modal fixed inset-0 bg-black bg-opacity-90 z-50 items-center justify-center">
        <button onclick="closeLightbox()" class="absolute top-4 right-4 text-white text-4xl hover:text-gray-300">
            <i class="fas fa-times"></i>
        </button>
        <button onclick="prevImage()" class="absolute left-4 text-white text-4xl hover:text-gray-300">
            <i class="fas fa-chevron-left"></i>
        </button>
        <button onclick="nextImage()" class="absolute right-4 text-white text-4xl hover:text-gray-300">
            <i class="fas fa-chevron-right"></i>
        </button>
        <img id="lightboxImg" src="" alt="Hotel Image" class="max-w-5xl max-h-screen object-contain">
    </div>

    <script>
        const images = <?php echo json_encode(array_map(function($img) { return getImageUrl($img['image_path']); }, $images)); ?>;
        let currentIndex = 0;

        function openLightbox(index) {
            currentIndex = index;
            document.getElementById('lightboxImg').src = images[currentIndex];
            document.getElementById('lightbox').classList.add('active');
        }

        function closeLightbox() {
            document.getElementById('lightbox').classList.remove('active');
        }

        function prevImage() {
            currentIndex = (currentIndex - 1 + images.length) % images.length;
            document.getElementById('lightboxImg').src = images[currentIndex];
        }

        function nextImage() {
            currentIndex = (currentIndex + 1) % images.length;
            document.getElementById('lightboxImg').src = images[currentIndex];
        }

        function toggleWishlist() {
            <?php if (!isLoggedIn()): ?>
                window.location.href = 'login.php';
                return;
            <?php endif; ?>

            fetch('api/wishlist.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ hotel_id: <?php echo $hotel['id']; ?> })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                }
            });
        }

        
        document.addEventListener('keydown', function(e) {
            if (document.getElementById('lightbox').classList.contains('active')) {
                if (e.key === 'ArrowLeft') prevImage();
                if (e.key === 'ArrowRight') nextImage();
                if (e.key === 'Escape') closeLightbox();
            }
        });
    </script>
</body>
</html>