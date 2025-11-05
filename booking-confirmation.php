<?php
define('MOONHERITAGE_ACCESS', true);
require_once 'config.php';


if (!isLoggedIn()) {
    redirect('login.php');
}


$bookingNumber = sanitize($_GET['booking'] ?? '');

if (empty($bookingNumber)) {
    redirect('index.php');
}


$db = getDB();
$stmt = $db->prepare("
    SELECT b.*, h.name as hotel_name, h.address, h.city, h.country, 
           h.phone as hotel_phone, h.email as hotel_email, h.main_image,
           u.first_name, u.last_name, u.email as user_email
    FROM bookings b
    INNER JOIN hotels h ON b.hotel_id = h.id
    INNER JOIN users u ON b.user_id = u.id
    WHERE b.booking_number = ? AND b.user_id = ?
");
$stmt->execute([$bookingNumber, getUserId()]);
$booking = $stmt->fetch();

if (!$booking) {
    redirect('index.php');
}


$checkIn = new DateTime($booking['check_in_date']);
$checkOut = new DateTime($booking['check_out_date']);
$nights = $checkIn->diff($checkOut)->days;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Confirmed - MoonHeritage</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <style>
        @keyframes checkmark {
            0% { transform: scale(0) rotate(45deg); }
            50% { transform: scale(1.2) rotate(45deg); }
            100% { transform: scale(1) rotate(45deg); }
        }
        .checkmark-animation {
            animation: checkmark 0.5s ease-in-out;
        }
        @media print {
            .no-print { display: none; }
        }
    </style>
</head>
<body class="bg-gray-50">
    
    <nav class="bg-gray-900 text-white sticky top-0 z-50 shadow-lg no-print">
        <div class="container mx-auto px-6 py-4">
            <div class="flex items-center justify-between">
                <a href="index.php" class="flex items-center space-x-2">
                    <i class="fas fa-moon text-2xl"></i>
                    <span class="text-xl font-bold">MoonHeritage</span>
                </a>
                <div class="flex items-center space-x-4">
                    <a href="profile.php" class="hover:text-gray-300">
                        <i class="fas fa-user-circle text-xl"></i>
                        My Bookings
                    </a>
                </div>
            </div>
        </div>
    </nav>

    
    <section class="py-12">
        <div class="container mx-auto px-6 max-w-4xl">
            
            <div class="bg-white rounded-2xl shadow-2xl overflow-hidden mb-8 fade-in">
                <div class="bg-gradient-to-r from-green-500 to-green-600 text-white p-8 text-center">
                    <div class="inline-block bg-white rounded-full p-4 mb-4 checkmark-animation">
                        <i class="fas fa-check text-green-600 text-5xl"></i>
                    </div>
                    <h1 class="text-4xl font-bold mb-2">Booking Confirmed!</h1>
                    <p class="text-xl opacity-90">Your reservation has been successfully completed</p>
                </div>

                
                <div class="p-8">
                    
                    <div class="text-center mb-8 pb-8 border-b">
                        <p class="text-gray-600 mb-2">Booking Reference</p>
                        <h2 class="text-3xl font-bold text-blue-600"><?php echo escape($booking['booking_number']); ?></h2>
                        <p class="text-sm text-gray-500 mt-2">
                            <i class="far fa-clock mr-2"></i>
                            Booked on <?php echo formatDate($booking['created_at'], 'F d, Y'); ?>
                        </p>
                    </div>

                    
                    <div class="mb-8">
                        <h3 class="text-xl font-bold mb-4">Hotel Information</h3>
                        <div class="flex gap-4 bg-gray-50 p-4 rounded-lg">
                            <img src="<?php echo getImageUrl($booking['main_image']); ?>" 
                                 alt="<?php echo escape($booking['hotel_name']); ?>" 
                                 class="w-32 h-32 object-cover rounded-lg">
                            <div class="flex-1">
                                <h4 class="text-2xl font-bold mb-2"><?php echo escape($booking['hotel_name']); ?></h4>
                                <p class="text-gray-600 mb-2">
                                    <i class="fas fa-map-marker-alt mr-2"></i>
                                    <?php echo escape($booking['address'] . ', ' . $booking['city'] . ', ' . $booking['country']); ?>
                                </p>
                                <p class="text-gray-600">
                                    <i class="fas fa-phone mr-2"></i>
                                    <?php echo escape($booking['hotel_phone']); ?>
                                </p>
                            </div>
                        </div>
                    </div>

                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                        <div class="bg-blue-50 p-4 rounded-lg">
                            <div class="flex items-center mb-2">
                                <i class="fas fa-calendar-check text-blue-600 text-2xl mr-3"></i>
                                <div>
                                    <p class="text-sm text-gray-600">Check-in</p>
                                    <p class="font-bold text-lg"><?php echo formatDate($booking['check_in_date'], 'D, M d, Y'); ?></p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-red-50 p-4 rounded-lg">
                            <div class="flex items-center mb-2">
                                <i class="fas fa-calendar-times text-red-600 text-2xl mr-3"></i>
                                <div>
                                    <p class="text-sm text-gray-600">Check-out</p>
                                    <p class="font-bold text-lg"><?php echo formatDate($booking['check_out_date'], 'D, M d, Y'); ?></p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-purple-50 p-4 rounded-lg">
                            <div class="flex items-center mb-2">
                                <i class="fas fa-moon text-purple-600 text-2xl mr-3"></i>
                                <div>
                                    <p class="text-sm text-gray-600">Total Nights</p>
                                    <p class="font-bold text-lg"><?php echo $nights; ?> night<?php echo $nights > 1 ? 's' : ''; ?></p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-green-50 p-4 rounded-lg">
                            <div class="flex items-center mb-2">
                                <i class="fas fa-users text-green-600 text-2xl mr-3"></i>
                                <div>
                                    <p class="text-sm text-gray-600">Guests</p>
                                    <p class="font-bold text-lg">
                                        <?php echo $booking['guests_adults']; ?> Adult<?php echo $booking['guests_adults'] > 1 ? 's' : ''; ?>
                                        <?php if ($booking['guests_children'] > 0): ?>
                                            , <?php echo $booking['guests_children']; ?> Child<?php echo $booking['guests_children'] > 1 ? 'ren' : ''; ?>
                                        <?php endif; ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    
                    <div class="mb-8 pb-8 border-b">
                        <h3 class="text-xl font-bold mb-4">Guest Information</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <p class="text-sm text-gray-600">Guest Name</p>
                                <p class="font-semibold"><?php echo escape($booking['first_name'] . ' ' . $booking['last_name']); ?></p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Email</p>
                                <p class="font-semibold"><?php echo escape($booking['user_email']); ?></p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Rooms</p>
                                <p class="font-semibold"><?php echo $booking['rooms_count']; ?> Room<?php echo $booking['rooms_count'] > 1 ? 's' : ''; ?></p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Booking Status</p>
                                <span class="inline-block px-3 py-1 bg-green-100 text-green-800 rounded-full text-sm font-semibold">
                                    <?php echo ucfirst($booking['booking_status']); ?>
                                </span>
                            </div>
                        </div>

                        <?php if ($booking['special_requests']): ?>
                            <div class="mt-4">
                                <p class="text-sm text-gray-600 mb-1">Special Requests</p>
                                <p class="text-gray-700 bg-gray-50 p-3 rounded"><?php echo nl2br(escape($booking['special_requests'])); ?></p>
                            </div>
                        <?php endif; ?>
                    </div>

                    
                    <div class="mb-8">
                        <h3 class="text-xl font-bold mb-4">Payment Summary</h3>
                        <div class="bg-gray-50 p-6 rounded-lg">
                            <div class="flex justify-between mb-3">
                                <span class="text-gray-700">Subtotal</span>
                                <span class="font-semibold"><?php echo formatPrice($booking['subtotal']); ?></span>
                            </div>
                            <div class="flex justify-between mb-3">
                                <span class="text-gray-700">Taxes & Fees</span>
                                <span class="font-semibold"><?php echo formatPrice($booking['tax_amount']); ?></span>
                            </div>
                            <?php if ($booking['discount_amount'] > 0): ?>
                                <div class="flex justify-between mb-3 text-green-600">
                                    <span>Discount</span>
                                    <span class="font-semibold">-<?php echo formatPrice($booking['discount_amount']); ?></span>
                                </div>
                            <?php endif; ?>
                            <div class="border-t border-gray-300 pt-3 mt-3">
                                <div class="flex justify-between items-center">
                                    <span class="text-xl font-bold">Total Amount</span>
                                    <span class="text-2xl font-bold text-blue-600"><?php echo formatPrice($booking['total_amount']); ?></span>
                                </div>
                            </div>
                            <div class="mt-4">
                                <p class="text-sm text-gray-600">
                                    <i class="fas fa-credit-card mr-2"></i>
                                    Payment Method: <?php echo ucfirst(str_replace('_', ' ', $booking['payment_method'])); ?>
                                </p>
                                <p class="text-sm text-gray-600 mt-1">
                                    <i class="fas fa-info-circle mr-2"></i>
                                    Payment Status: 
                                    <span class="font-semibold <?php echo $booking['payment_status'] === 'paid' ? 'text-green-600' : 'text-yellow-600'; ?>">
                                        <?php echo ucfirst($booking['payment_status']); ?>
                                    </span>
                                </p>
                            </div>
                        </div>
                    </div>

                    
                    <div class="bg-yellow-50 border-l-4 border-yellow-500 p-4 mb-8">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <i class="fas fa-exclamation-triangle text-yellow-500 text-xl"></i>
                            </div>
                            <div class="ml-3">
                                <h4 class="text-sm font-semibold text-yellow-800 mb-2">Important Information</h4>
                                <ul class="text-sm text-yellow-700 space-y-1">
                                    <li>• Please present your booking confirmation at check-in</li>
                                    <li>• Valid photo ID required at check-in</li>
                                    <li>• Free cancellation up to 24 hours before check-in</li>
                                    <li>• Check-in time: 2:00 PM | Check-out time: 11:00 AM</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 no-print">
                        <button onclick="window.print()" 
                                class="bg-gray-600 text-white px-6 py-3 rounded-lg hover:bg-gray-700 transition font-semibold">
                            <i class="fas fa-print mr-2"></i>Print Confirmation
                        </button>
                        <a href="mailto:<?php echo escape($booking['hotel_email']); ?>" 
                           class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition font-semibold text-center">
                            <i class="fas fa-envelope mr-2"></i>Email Hotel
                        </a>
                        <a href="profile.php" 
                           class="bg-green-600 text-white px-6 py-3 rounded-lg hover:bg-green-700 transition font-semibold text-center">
                            <i class="fas fa-th-list mr-2"></i>View All Bookings
                        </a>
                    </div>
                </div>
            </div>

            
            <div class="bg-white rounded-lg shadow-md p-6 no-print">
                <h3 class="text-xl font-bold mb-4">What's Next?</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="text-center p-4">
                        <div class="bg-blue-100 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-3">
                            <i class="fas fa-envelope text-blue-600 text-2xl"></i>
                        </div>
                        <h4 class="font-semibold mb-2">Confirmation Email</h4>
                        <p class="text-sm text-gray-600">Check your email for booking details and receipt</p>
                    </div>
                    <div class="text-center p-4">
                        <div class="bg-green-100 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-3">
                            <i class="fas fa-calendar-alt text-green-600 text-2xl"></i>
                        </div>
                        <h4 class="font-semibold mb-2">Add to Calendar</h4>
                        <p class="text-sm text-gray-600">Set reminders for your upcoming trip</p>
                    </div>
                    <div class="text-center p-4">
                        <div class="bg-purple-100 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-3">
                            <i class="fas fa-route text-purple-600 text-2xl"></i>
                        </div>
                        <h4 class="font-semibold mb-2">Plan Activities</h4>
                        <p class="text-sm text-gray-600">Explore things to do at your destination</p>
                    </div>
                </div>
            </div>

            
            <div class="text-center mt-8 no-print">
                <a href="index.php" class="inline-block bg-blue-600 text-white px-8 py-3 rounded-lg hover:bg-blue-700 transition font-semibold">
                    <i class="fas fa-home mr-2"></i>Back to Home
                </a>
            </div>
        </div>
    </section>

    <script src="js/main.js"></script>
    <script>
        // Auto-print functionality (optional)
        // window.onload = function() {
        //     setTimeout(() => {
        //         window.print();
        //     }, 1000);
        // };

        // Confetti animation (optional)
        console.log('🎉 Booking Confirmed! Reference: <?php echo $booking['booking_number']; ?>');
    </script>
</body>
</html>