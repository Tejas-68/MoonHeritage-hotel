<?php
define('MOONHERITAGE_ACCESS', true);
require_once 'config.php';


if (!isLoggedIn()) {
    redirect('login.php');
}

$user = getCurrentUser();
$db = getDB();
$uid = getUserId();


function bookingBadgeClasses(string $status): array {
    $status = strtolower($status);
    switch ($status) {
        case 'confirmed':
            return ['bg' => 'green', 'text' => 'green'];
        case 'completed':
            return ['bg' => 'blue', 'text' => 'blue'];
        case 'cancelled':
        case 'canceled':
            return ['bg' => 'red', 'text' => 'red'];
        default:
            return ['bg' => 'yellow', 'text' => 'yellow'];
    }
}

$message = '';
$messageType = '';


try {
    $bookingsStmt = $db->prepare("
        SELECT b.*, h.name as hotel_name, h.city, h.country, h.main_image
        FROM bookings b
        INNER JOIN hotels h ON b.hotel_id = h.id
        WHERE b.user_id = ?
        ORDER BY b.created_at DESC
        LIMIT 10
    ");
    $bookingsStmt->execute([$uid]);
    $bookings = $bookingsStmt->fetchAll();
} catch (PDOException $e) {
    error_log("Failed to fetch bookings: " . $e->getMessage());
    $bookings = [];
}


try {
    $wishlistStmt = $db->prepare("
        SELECT h.*, w.created_at as added_date
        FROM wishlist w
        INNER JOIN hotels h ON w.hotel_id = h.id
        WHERE w.user_id = ?
        ORDER BY w.created_at DESC
    ");
    $wishlistStmt->execute([$uid]);
    $wishlist = $wishlistStmt->fetchAll();
} catch (PDOException $e) {
    error_log("Failed to fetch wishlist: " . $e->getMessage());
    $wishlist = [];
}


try {
    $statsStmt = $db->prepare("
        SELECT 
            COUNT(*) as total_bookings,
            SUM(CASE WHEN booking_status = 'confirmed' THEN 1 ELSE 0 END) as confirmed_bookings,
            SUM(CASE WHEN booking_status = 'completed' THEN 1 ELSE 0 END) as completed_bookings,
            COALESCE(SUM(total_amount), 0) as total_spent
        FROM bookings
        WHERE user_id = ?
    ");
    $statsStmt->execute([$uid]);
    $stats = $statsStmt->fetch();
    if (!$stats) {
        $stats = [
            'total_bookings' => 0,
            'confirmed_bookings' => 0,
            'completed_bookings' => 0,
            'total_spent' => 0
        ];
    }
} catch (PDOException $e) {
    error_log("Failed to fetch stats: " . $e->getMessage());
    $stats = [
        'total_bookings' => 0,
        'confirmed_bookings' => 0,
        'completed_bookings' => 0,
        'total_spent' => 0
    ];
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $firstName = sanitize($_POST['first_name'] ?? '');
    $lastName = sanitize($_POST['last_name'] ?? '');
    $phone = sanitize($_POST['phone'] ?? '');
    $address = sanitize($_POST['address'] ?? '');
    $city = sanitize($_POST['city'] ?? '');
    $country = sanitize($_POST['country'] ?? '');
    $postalCode = sanitize($_POST['postal_code'] ?? '');

    try {
        $updateStmt = $db->prepare("
            UPDATE users 
            SET first_name = ?, last_name = ?, phone = ?, address = ?, city = ?, country = ?, postal_code = ?
            WHERE id = ?
        ");
        $updateStmt->execute([$firstName, $lastName, $phone, $address, $city, $country, $postalCode, $uid]);

        
        $_SESSION['first_name'] = $firstName;
        $_SESSION['last_name'] = $lastName;

        logActivity($uid, 'profile_updated', 'Profile information updated');

        $message = 'Profile updated successfully!';
        $messageType = 'success';

        
        $user = getCurrentUser();
    } catch (PDOException $e) {
        error_log("Profile update failed: " . $e->getMessage());
        $message = 'Failed to update profile';
        $messageType = 'error';
    }
}


$activeTab = sanitize($_GET['tab'] ?? 'dashboard');
$allowedTabs = ['dashboard', 'bookings', 'wishlist', 'settings'];
if (!in_array($activeTab, $allowedTabs, true)) {
    $activeTab = 'dashboard';
}


function messageBoxClasses(string $type): string {
    if ($type === 'success') return 'mb-6 bg-green-100 border border-green-400 text-green-700';
    if ($type === 'error') return 'mb-6 bg-red-100 border border-red-400 text-red-700';
    return 'mb-6 bg-gray-100 border border-gray-300 text-gray-700';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - MoonHeritage</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="bg-gray-50">
    
    <nav class="bg-gray-900 text-white sticky top-0 z-50 shadow-lg">
        <div class="container mx-auto px-6 py-4">
            <div class="flex items-center justify-between">
                <a href="index.php" class="flex items-center space-x-2">
                    <i class="fas fa-moon text-2xl"></i>
                    <span class="text-xl font-bold">MoonHeritage</span>
                </a>
                <div class="flex items-center space-x-4">
                    <span class="text-gray-300">
                        <i class="fas fa-user-circle text-xl mr-2"></i>
                        <?php echo escape($user['first_name'] . ' ' . $user['last_name']); ?>
                    </span>
                    <a href="logout.php" class="bg-red-600 px-4 py-2 rounded-lg hover:bg-red-700">Logout</a>
                </div>
            </div>
        </div>
    </nav>

    
    <section class="bg-gradient-to-r from-blue-600 to-purple-600 text-white py-12">
        <div class="container mx-auto px-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-4xl font-bold mb-2">Welcome back, <?php echo escape($user['first_name']); ?>!</h1>
                    <p class="text-blue-100">Manage your bookings and account settings</p>
                </div>
                <div class="hidden md:block">
                    <div class="bg-white bg-opacity-20 backdrop-blur-sm rounded-lg p-4">
                        <p class="text-sm mb-1">Member since</p>
                        <p class="font-bold"><?php echo formatDate($user['created_at'], 'M Y'); ?></p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    
    <section class="py-8">
        <div class="container mx-auto px-6">
            <?php if ($message): ?>
                <div class="<?php echo messageBoxClasses($messageType); ?> px-4 py-3 rounded-lg flex items-center">
                    <i class="fas fa-<?php echo $messageType === 'success' ? 'check-circle' : 'exclamation-circle'; ?> mr-3"></i>
                    <span><?php echo escape($message); ?></span>
                </div>
            <?php endif; ?>

            <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
                
                <aside class="lg:col-span-1">
                    <div class="bg-white rounded-lg shadow-md overflow-hidden">
                        
                        <div class="p-6 text-center bg-gradient-to-b from-blue-50 to-white">
                            <div class="w-24 h-24 bg-blue-600 rounded-full mx-auto mb-4 flex items-center justify-center text-white text-3xl font-bold">
                                <?php echo strtoupper(substr($user['first_name'], 0, 1) . substr($user['last_name'], 0, 1)); ?>
                            </div>
                            <h3 class="font-bold text-lg"><?php echo escape($user['first_name'] . ' ' . $user['last_name']); ?></h3>
                            <p class="text-sm text-gray-600"><?php echo escape($user['email']); ?></p>
                        </div>

                        
                        <nav class="p-4">
                            <a href="?tab=dashboard"
                               class="flex items-center px-4 py-3 mb-2 rounded-lg <?php echo $activeTab === 'dashboard' ? 'bg-blue-600 text-white' : 'hover:bg-gray-100 text-gray-700'; ?>">
                                <i class="fas fa-th-large mr-3"></i>
                                Dashboard
                            </a>
                            <a href="?tab=bookings"
                               class="flex items-center px-4 py-3 mb-2 rounded-lg <?php echo $activeTab === 'bookings' ? 'bg-blue-600 text-white' : 'hover:bg-gray-100 text-gray-700'; ?>">
                                <i class="fas fa-calendar-check mr-3"></i>
                                My Bookings
                            </a>
                            <a href="?tab=wishlist"
                               class="flex items-center px-4 py-3 mb-2 rounded-lg <?php echo $activeTab === 'wishlist' ? 'bg-blue-600 text-white' : 'hover:bg-gray-100 text-gray-700'; ?>">
                                <i class="fas fa-heart mr-3"></i>
                                Wishlist
                            </a>
                            <a href="?tab=settings"
                               class="flex items-center px-4 py-3 mb-2 rounded-lg <?php echo $activeTab === 'settings' ? 'bg-blue-600 text-white' : 'hover:bg-gray-100 text-gray-700'; ?>">
                                <i class="fas fa-cog mr-3"></i>
                                Settings
                            </a>
                        </nav>
                    </div>
                </aside>

                
                <div class="lg:col-span-3">
                    <?php if ($activeTab === 'dashboard'): ?>
                        
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
                            <div class="bg-white rounded-lg shadow-md p-6">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-gray-600 text-sm">Total Bookings</p>
                                        <p class="text-3xl font-bold text-blue-600"><?php echo (int)$stats['total_bookings']; ?></p>
                                    </div>
                                    <i class="fas fa-calendar-check text-4xl text-blue-200"></i>
                                </div>
                            </div>

                            <div class="bg-white rounded-lg shadow-md p-6">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-gray-600 text-sm">Confirmed</p>
                                        <p class="text-3xl font-bold text-green-600"><?php echo (int)$stats['confirmed_bookings']; ?></p>
                                    </div>
                                    <i class="fas fa-check-circle text-4xl text-green-200"></i>
                                </div>
                            </div>

                            <div class="bg-white rounded-lg shadow-md p-6">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-gray-600 text-sm">Completed</p>
                                        <p class="text-3xl font-bold text-purple-600"><?php echo (int)$stats['completed_bookings']; ?></p>
                                    </div>
                                    <i class="fas fa-flag-checkered text-4xl text-purple-200"></i>
                                </div>
                            </div>

                            <div class="bg-white rounded-lg shadow-md p-6">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-gray-600 text-sm">Total Spent</p>
                                        <p class="text-3xl font-bold text-orange-600"><?php echo formatPrice($stats['total_spent'] ?? 0); ?></p>
                                    </div>
                                    <i class="fas fa-dollar-sign text-4xl text-orange-200"></i>
                                </div>
                            </div>
                        </div>

                        
                        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                            <h2 class="text-2xl font-bold mb-4">Recent Bookings</h2>
                            <?php if (empty($bookings)): ?>
                                <div class="text-center py-12">
                                    <i class="fas fa-calendar-times text-6xl text-gray-300 mb-4"></i>
                                    <p class="text-gray-600 mb-4">No bookings yet</p>
                                    <a href="hotels.php" class="inline-block bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700">
                                        Browse Hotels
                                    </a>
                                </div>
                            <?php else: ?>
                                <div class="space-y-4">
                                    <?php foreach (array_slice($bookings, 0, 3) as $booking): 
                                        $badge = bookingBadgeClasses($booking['booking_status'] ?? '');
                                    ?>
                                        <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition">
                                            <div class="flex gap-4">
                                                <img src="<?php echo getImageUrl($booking['main_image']); ?>"
                                                     alt="<?php echo escape($booking['hotel_name']); ?>"
                                                     class="w-24 h-24 object-cover rounded-lg">
                                                <div class="flex-1">
                                                    <div class="flex justify-between items-start mb-2">
                                                        <div>
                                                            <h4 class="font-bold"><?php echo escape($booking['hotel_name']); ?></h4>
                                                            <p class="text-sm text-gray-600">
                                                                <i class="fas fa-map-marker-alt mr-1"></i>
                                                                <?php echo escape($booking['city'] . ', ' . $booking['country']); ?>
                                                            </p>
                                                        </div>
                                                        <span class="px-3 py-1 bg-<?php echo $badge['bg']; ?>-100 text-<?php echo $badge['text']; ?>-800 rounded-full text-sm font-semibold">
                                                            <?php echo ucfirst(escape($booking['booking_status'])); ?>
                                                        </span>
                                                    </div>
                                                    <div class="grid grid-cols-3 gap-4 text-sm">
                                                        <div>
                                                            <p class="text-gray-600">Check-in</p>
                                                            <p class="font-semibold"><?php echo formatDate($booking['check_in_date'], 'M d, Y'); ?></p>
                                                        </div>
                                                        <div>
                                                            <p class="text-gray-600">Check-out</p>
                                                            <p class="font-semibold"><?php echo formatDate($booking['check_out_date'], 'M d, Y'); ?></p>
                                                        </div>
                                                        <div>
                                                            <p class="text-gray-600">Total</p>
                                                            <p class="font-semibold text-blue-600"><?php echo formatPrice($booking['total_amount']); ?></p>
                                                        </div>
                                                    </div>
                                                    <div class="mt-3">
                                                        <a href="booking-confirmation.php?booking=<?php echo urlencode($booking['booking_number']); ?>"
                                                           class="text-blue-600 hover:underline text-sm">
                                                            View Details <i class="fas fa-arrow-right ml-1"></i>
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>

                    <?php elseif ($activeTab === 'bookings'): ?>
                        
                        <div class="bg-white rounded-lg shadow-md p-6">
                            <h2 class="text-2xl font-bold mb-6">My Bookings</h2>
                            <?php if (empty($bookings)): ?>
                                <div class="text-center py-12">
                                    <i class="fas fa-calendar-times text-6xl text-gray-300 mb-4"></i>
                                    <p class="text-gray-600 mb-4">No bookings found</p>
                                    <a href="hotels.php" class="inline-block bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700">
                                        Start Booking
                                    </a>
                                </div>
                            <?php else: ?>
                                <div class="space-y-6">
                                    <?php foreach ($bookings as $booking):
                                        $badge = bookingBadgeClasses($booking['booking_status'] ?? '');
                                    ?>
                                        <div class="border-2 border-gray-200 rounded-lg p-6 hover:border-blue-300 transition">
                                            <div class="flex justify-between items-start mb-4">
                                                <div>
                                                    <p class="text-sm text-gray-600">Booking Reference</p>
                                                    <p class="font-bold text-xl text-blue-600"><?php echo escape($booking['booking_number']); ?></p>
                                                </div>
                                                <span class="px-4 py-2 bg-<?php echo $badge['bg']; ?>-100 text-<?php echo $badge['text']; ?>-800 rounded-full text-sm font-semibold">
                                                    <?php echo ucfirst(escape($booking['booking_status'])); ?>
                                                </span>
                                            </div>

                                            <div class="flex gap-6">
                                                <img src="<?php echo getImageUrl($booking['main_image']); ?>"
                                                     alt="<?php echo escape($booking['hotel_name']); ?>"
                                                     class="w-32 h-32 object-cover rounded-lg">
                                                <div class="flex-1">
                                                    <h4 class="text-xl font-bold mb-2"><?php echo escape($booking['hotel_name']); ?></h4>
                                                    <p class="text-gray-600 mb-3">
                                                        <i class="fas fa-map-marker-alt mr-2"></i>
                                                        <?php echo escape($booking['city'] . ', ' . $booking['country']); ?>
                                                    </p>

                                                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
                                                        <div>
                                                            <p class="text-sm text-gray-600">Check-in</p>
                                                            <p class="font-semibold"><?php echo formatDate($booking['check_in_date'], 'M d, Y'); ?></p>
                                                        </div>
                                                        <div>
                                                            <p class="text-sm text-gray-600">Check-out</p>
                                                            <p class="font-semibold"><?php echo formatDate($booking['check_out_date'], 'M d, Y'); ?></p>
                                                        </div>
                                                        <div>
                                                            <p class="text-sm text-gray-600">Guests</p>
                                                            <p class="font-semibold"><?php echo (int)($booking['guests_adults'] + $booking['guests_children']); ?></p>
                                                        </div>
                                                        <div>
                                                            <p class="text-sm text-gray-600">Total</p>
                                                            <p class="font-semibold text-blue-600"><?php echo formatPrice($booking['total_amount']); ?></p>
                                                        </div>
                                                    </div>

                                                    <div class="flex gap-3">
                                                        <a href="booking-confirmation.php?booking=<?php echo urlencode($booking['booking_number']); ?>"
                                                           class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 text-sm">
                                                            View Details
                                                        </a>
                                                        <?php if (($booking['booking_status'] ?? '') === 'confirmed'): ?>
                                                            <button onclick="cancelBooking('<?php echo escape($booking['booking_number']); ?>')"
                                                                    class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 text-sm">
                                                                Cancel Booking
                                                            </button>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>

                    <?php elseif ($activeTab === 'wishlist'): ?>
                        
                        <div class="bg-white rounded-lg shadow-md p-6">
                            <h2 class="text-2xl font-bold mb-6">My Wishlist</h2>
                            <?php if (empty($wishlist)): ?>
                                <div class="text-center py-12">
                                    <i class="fas fa-heart text-6xl text-gray-300 mb-4"></i>
                                    <p class="text-gray-600 mb-4">Your wishlist is empty</p>
                                    <a href="hotels.php" class="inline-block bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700">
                                        Explore Hotels
                                    </a>
                                </div>
                            <?php else: ?>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <?php foreach ($wishlist as $hotel): ?>
                                        <div class="border border-gray-200 rounded-lg overflow-hidden hover:shadow-lg transition">
                                            <div class="relative">
                                                <img src="<?php echo getImageUrl($hotel['main_image']); ?>"
                                                     alt="<?php echo escape($hotel['name']); ?>"
                                                     class="w-full h-48 object-cover">
                                                <button onclick="removeFromWishlist(<?php echo (int)$hotel['id']; ?>)"
                                                        class="absolute top-3 right-3 bg-white rounded-full p-2 text-red-500 hover:bg-red-50">
                                                    <i class="fas fa-heart"></i>
                                                </button>
                                            </div>
                                            <div class="p-4">
                                                <h4 class="font-bold text-lg mb-1"><?php echo escape($hotel['name']); ?></h4>
                                                <p class="text-sm text-gray-600 mb-3">
                                                    <i class="fas fa-map-marker-alt mr-1"></i>
                                                    <?php echo escape($hotel['city'] . ', ' . $hotel['country']); ?>
                                                </p>
                                                <div class="flex justify-between items-center mb-3">
                                                    <?php echo getStarRating($hotel['star_rating']); ?>
                                                    <span class="font-bold text-blue-600"><?php echo formatPrice($hotel['price_per_night']); ?>/night</span>
                                                </div>
                                                <a href="hotel-details.php?slug=<?php echo urlencode($hotel['slug']); ?>"
                                                   class="block w-full bg-blue-600 text-white text-center py-2 rounded-lg hover:bg-blue-700">
                                                    View Details
                                                </a>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>

                    <?php elseif ($activeTab === 'settings'): ?>
                        
                        <div class="bg-white rounded-lg shadow-md p-6">
                            <h2 class="text-2xl font-bold mb-6">Account Settings</h2>

                            <form method="POST" action="" class="space-y-6">
                                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label class="block text-gray-700 font-semibold mb-2">First Name</label>
                                        <input type="text" name="first_name" value="<?php echo escape($user['first_name']); ?>" required
                                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                    </div>
                                    <div>
                                        <label class="block text-gray-700 font-semibold mb-2">Last Name</label>
                                        <input type="text" name="last_name" value="<?php echo escape($user['last_name']); ?>" required
                                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-gray-700 font-semibold mb-2">Email Address</label>
                                    <input type="email" value="<?php echo escape($user['email']); ?>" disabled
                                           class="w-full px-4 py-3 border border-gray-300 rounded-lg bg-gray-100">
                                    <p class="text-sm text-gray-500 mt-1">Email cannot be changed</p>
                                </div>

                                <div>
                                    <label class="block text-gray-700 font-semibold mb-2">Phone Number</label>
                                    <input type="tel" name="phone" value="<?php echo escape($user['phone']); ?>"
                                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                </div>

                                <div>
                                    <label class="block text-gray-700 font-semibold mb-2">Address</label>
                                    <textarea name="address" rows="3"
                                              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"><?php echo escape($user['address']); ?></textarea>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                    <div>
                                        <label class="block text-gray-700 font-semibold mb-2">City</label>
                                        <input type="text" name="city" value="<?php echo escape($user['city']); ?>"
                                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                    </div>
                                    <div>
                                        <label class="block text-gray-700 font-semibold mb-2">Country</label>
                                        <input type="text" name="country" value="<?php echo escape($user['country']); ?>"
                                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                    </div>
                                    <div>
                                        <label class="block text-gray-700 font-semibold mb-2">Postal Code</label>
                                        <input type="text" name="postal_code" value="<?php echo escape($user['postal_code']); ?>"
                                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                    </div>
                                </div>

                                <button type="submit" name="update_profile"
                                        class="bg-blue-600 text-white px-8 py-3 rounded-lg hover:bg-blue-700 font-semibold">
                                    <i class="fas fa-save mr-2"></i>Save Changes
                                </button>
                            </form>

                            
                            <div class="mt-8 pt-8 border-t">
                                <h3 class="text-xl font-bold mb-4">Change Password</h3>
                                <a href="change-password.php" class="inline-block bg-gray-600 text-white px-6 py-3 rounded-lg hover:bg-gray-700">
                                    <i class="fas fa-key mr-2"></i>Change Password
                                </a>
                            </div>

                            
                            <div class="mt-8 pt-8 border-t">
                                <h3 class="text-xl font-bold mb-4 text-red-600">Danger Zone</h3>
                                <p class="text-gray-600 mb-4">Once you delete your account, there is no going back. Please be certain.</p>
                                <button onclick="confirmDeleteAccount()"
                                        class="bg-red-600 text-white px-6 py-3 rounded-lg hover:bg-red-700">
                                    <i class="fas fa-trash mr-2"></i>Delete Account
                                </button>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <script src="js/main.js"></script>
    <script>
        function removeFromWishlist(hotelId) {
            if (!confirm('Remove this hotel from your wishlist?')) return;
            fetch('api/wishlist.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ hotel_id: hotelId, action: 'remove' })
            })
            .then(response => response.json())
            .then(data => {
                if (data && data.success) {
                    location.reload();
                } else {
                    alert(data.message || 'Failed to remove from wishlist');
                }
            })
            .catch(() => alert('Network error'));
        }

        function cancelBooking(bookingNumber) {
            if (!confirm('Are you sure you want to cancel this booking?')) return;
            // Replace with your cancellation endpoint; currently shows placeholder
            fetch('api/cancel-booking.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ booking_number: bookingNumber })
            })
            .then(res => res.json())
            .then(data => {
                if (data && data.success) {
                    location.reload();
                } else {
                    alert(data.message || 'Cancellation failed');
                }
            })
            .catch(() => alert('Network error'));
        }

        function confirmDeleteAccount() {
            if (!confirm('Are you absolutely sure? This action cannot be undone!')) return;
            if (!confirm('Final confirmation: Delete your account permanently?')) return;
            // Call your account deletion endpoint
            fetch('api/delete-account.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ confirm: true })
            })
            .then(res => res.json())
            .then(data => {
                if (data && data.success) {
                    window.location = 'goodbye.php';
                } else {
                    alert(data.message || 'Account deletion failed');
                }
            })
            .catch(() => alert('Network error'));
        }
    </script>
</body>
</html>