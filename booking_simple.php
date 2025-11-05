<?php
define('MOONHERITAGE_ACCESS', true);
require_once 'config.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

$hotelId = (int)($_GET['hotel_id'] ?? 1);
$checkIn = $_GET['check_in'] ?? '2025-10-30';
$checkOut = $_GET['check_out'] ?? '2025-10-31';


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_booking'])) {
    
    $db = getDB();
    $bookingNumber = 'MH' . date('Ymd') . rand(1000, 9999);
    
    try {
        $stmt = $db->prepare("
            INSERT INTO bookings (
                booking_number, user_id, hotel_id, 
                check_in_date, check_out_date, total_nights,
                guests_adults, guests_children, rooms_count,
                subtotal, tax_amount, total_amount,
                payment_status, payment_method, booking_status,
                special_requests, created_at
            ) VALUES (?, ?, ?, ?, ?, 1, 1, 0, 1, 
                      160, 16, 176, 'pending', ?, 'confirmed', ?, NOW())
        ");
        
        $stmt->execute([
            $bookingNumber,
            getUserId(),
            $hotelId,
            $_POST['check_in'],
            $_POST['check_out'],
            $_POST['payment_method'],
            $_POST['special_requests'] ?? ''
        ]);
        
        
        header("Location: booking-confirmation.php?booking=" . urlencode($bookingNumber));
        exit();
        
    } catch (Exception $e) {
        $error = "Booking failed: " . $e->getMessage();
    }
}

$user = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Simple Booking - MoonHeritage</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50">
    <div class="container mx-auto px-6 py-8 max-w-2xl">
        <h1 class="text-3xl font-bold mb-6">Complete Your Booking</h1>
        
        <?php if (isset($error)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <div class="bg-white rounded-lg shadow-md p-6">
            <form method="POST" action="">
                <input type="hidden" name="hotel_id" value="<?php echo $hotelId; ?>">
                <input type="hidden" name="check_in" value="<?php echo htmlspecialchars($checkIn); ?>">
                <input type="hidden" name="check_out" value="<?php echo htmlspecialchars($checkOut); ?>">
                
                <h2 class="text-xl font-bold mb-4">Guest Information</h2>
                
                <div class="mb-4">
                    <label class="block text-gray-700 font-semibold mb-2">First Name *</label>
                    <input type="text" name="first_name" required
                           value="<?php echo htmlspecialchars($user['first_name']); ?>"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg">
                </div>
                
                <div class="mb-4">
                    <label class="block text-gray-700 font-semibold mb-2">Last Name *</label>
                    <input type="text" name="last_name" required
                           value="<?php echo htmlspecialchars($user['last_name']); ?>"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg">
                </div>
                
                <div class="mb-4">
                    <label class="block text-gray-700 font-semibold mb-2">Email *</label>
                    <input type="email" name="email" required
                           value="<?php echo htmlspecialchars($user['email']); ?>"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg">
                </div>
                
                <div class="mb-4">
                    <label class="block text-gray-700 font-semibold mb-2">Phone *</label>
                    <input type="tel" name="phone" required
                           value="<?php echo htmlspecialchars($user['phone']); ?>"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg">
                </div>
                
                <div class="mb-4">
                    <label class="block text-gray-700 font-semibold mb-2">Special Requests</label>
                    <textarea name="special_requests" rows="3"
                              class="w-full px-4 py-3 border border-gray-300 rounded-lg"></textarea>
                </div>
                
                <h2 class="text-xl font-bold mb-4 mt-6">Payment Method</h2>
                
                <div class="mb-4">
                    <label class="flex items-center p-3 border-2 border-gray-300 rounded-lg cursor-pointer mb-2">
                        <input type="radio" name="payment_method" value="credit_card" checked class="mr-3">
                        <span>Credit Card</span>
                    </label>
                    <label class="flex items-center p-3 border-2 border-gray-300 rounded-lg cursor-pointer mb-2">
                        <input type="radio" name="payment_method" value="paypal" class="mr-3">
                        <span>PayPal</span>
                    </label>
                    <label class="flex items-center p-3 border-2 border-gray-300 rounded-lg cursor-pointer">
                        <input type="radio" name="payment_method" value="pay_at_hotel" class="mr-3">
                        <span>Pay at Hotel</span>
                    </label>
                </div>
                
                <div class="mb-6">
                    <label class="flex items-start cursor-pointer">
                        <input type="checkbox" required class="mt-1 mr-3">
                        <span class="text-sm text-gray-700">
                            I agree to the Terms and Conditions *
                        </span>
                    </label>
                </div>
                
                <button type="submit" name="confirm_booking" value="1"
                        class="w-full bg-blue-600 text-white py-4 rounded-lg hover:bg-blue-700 font-semibold text-lg">
                    <i class="fas fa-check-circle mr-2"></i>Confirm Booking
                </button>
                
                <div class="mt-4 text-center">
                    <a href="booking.php?hotel_id=<?php echo $hotelId; ?>&check_in=<?php echo $checkIn; ?>&check_out=<?php echo $checkOut; ?>" 
                       class="text-blue-600 hover:underline text-sm">
                        ← Back to Full Booking Form
                    </a>
                </div>
            </form>
        </div>
        
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mt-6">
            <h3 class="font-bold mb-2">Booking Summary</h3>
            <p><strong>Check-in:</strong> <?php echo $checkIn; ?></p>
            <p><strong>Check-out:</strong> <?php echo $checkOut; ?></p>
            <p><strong>Total:</strong> $176.00</p>
        </div>
    </div>
    
    <script>
        console.log('✅ Simple booking page loaded - NO JavaScript validation');
        document.querySelector('form').addEventListener('submit', function() {
            console.log('🟢 Form submitting directly to PHP...');
        });
    </script>
</body>
</html>