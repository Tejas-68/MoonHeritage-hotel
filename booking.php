<?php
define('MOONHERITAGE_ACCESS', true);
require_once 'config.php';




error_reporting(E_ALL);
ini_set('display_errors', 1);


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    error_log("POST DATA RECEIVED: " . print_r($_POST, true));
    error_log("confirm_booking isset: " . (isset($_POST['confirm_booking']) ? 'YES' : 'NO'));
}


if (!isLoggedIn()) {
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
    redirect('login.php');
}


$hotelId = (int)($_POST['hotel_id'] ?? $_GET['hotel_id'] ?? 0);
$checkIn = sanitize($_POST['check_in'] ?? $_GET['check_in'] ?? '');
$checkOut = sanitize($_POST['check_out'] ?? $_GET['check_out'] ?? '');
$adults = (int)($_POST['adults'] ?? $_GET['adults'] ?? 1);
$children = (int)($_POST['children'] ?? $_GET['children'] ?? 0);
$rooms = (int)($_POST['rooms'] ?? $_GET['rooms'] ?? 1);


if (!$hotelId || !$checkIn || !$checkOut) {
    redirect('hotels.php');
}


$db = getDB();
$stmt = $db->prepare("SELECT * FROM hotels WHERE id = ? AND status = 'active'");
$stmt->execute([$hotelId]);
$hotel = $stmt->fetch();

if (!$hotel) {
    redirect('hotels.php');
}


$checkInDate = new DateTime($checkIn);
$checkOutDate = new DateTime($checkOut);
$nights = $checkInDate->diff($checkOutDate)->days;

if ($nights <= 0) {
    redirect('hotel-details.php?slug=' . $hotel['slug']);
}


$subtotal = $hotel['price_per_night'] * $nights * $rooms;
$taxPercentage = (float)getSetting('booking_tax_percentage', 10);
$taxAmount = $subtotal * ($taxPercentage / 100);
$totalAmount = $subtotal + $taxAmount;




$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    
    error_log("=== BOOKING FORM SUBMITTED ===");
    error_log("POST keys: " . implode(", ", array_keys($_POST)));
    
    
    if (isset($_POST['confirm_booking']) || isset($_POST['first_name'])) {
        
        error_log("Processing booking...");
        
        $firstName = sanitize($_POST['first_name'] ?? '');
        $lastName = sanitize($_POST['last_name'] ?? '');
        $email = sanitize($_POST['email'] ?? '');
        $phone = sanitize($_POST['phone'] ?? '');
        $specialRequests = sanitize($_POST['special_requests'] ?? '');
        $paymentMethod = sanitize($_POST['payment_method'] ?? 'credit_card');
        
        
        if (empty($firstName) || empty($lastName) || empty($email) || empty($phone)) {
            $error = 'Please fill in all required fields';
            error_log("Validation failed: Missing required fields");
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Please enter a valid email address';
            error_log("Validation failed: Invalid email");
        } else {
            
            error_log("Validation passed, creating booking...");
            
            try {
                $db->beginTransaction();
                
                
                $bookingNumber = 'MH' . date('Ymd') . rand(1000, 9999);
                
                error_log("Generated booking number: " . $bookingNumber);
                
                
                $insertStmt = $db->prepare("
                    INSERT INTO bookings (
                        booking_number, user_id, hotel_id, 
                        check_in_date, check_out_date, total_nights,
                        guests_adults, guests_children, rooms_count,
                        subtotal, tax_amount, total_amount,
                        payment_status, payment_method, booking_status,
                        special_requests, created_at
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', ?, 'confirmed', ?, NOW())
                ");
                
                $result = $insertStmt->execute([
                    $bookingNumber,
                    getUserId(),
                    $hotelId,
                    $checkIn,
                    $checkOut,
                    $nights,
                    $adults,
                    $children,
                    $rooms,
                    $subtotal,
                    $taxAmount,
                    $totalAmount,
                    $paymentMethod,
                    $specialRequests
                ]);
                
                if (!$result) {
                    throw new Exception("Failed to insert booking");
                }
                
                $bookingId = $db->lastInsertId();
                error_log("Booking created with ID: " . $bookingId);
                
                
                $updateStmt = $db->prepare("UPDATE hotels SET available_rooms = available_rooms - ? WHERE id = ?");
                $updateStmt->execute([$rooms, $hotelId]);
                
                
                logActivity(getUserId(), 'booking_created', "Booking #$bookingNumber created");
                
                $db->commit();
                
                error_log("=== BOOKING SUCCESS - REDIRECTING ===");
                error_log("Redirect to: booking-confirmation.php?booking=" . $bookingNumber);
                
                
                header("Location: booking-confirmation.php?booking=" . urlencode($bookingNumber), true, 302);
                exit();
                
            } catch (Exception $e) {
                if ($db->inTransaction()) {
                    $db->rollBack();
                }
                $error = 'Booking failed: ' . $e->getMessage();
                error_log("BOOKING ERROR: " . $e->getMessage());
                error_log($e->getTraceAsString());
            }
        }
    }
}


$user = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Complete Your Booking - MoonHeritage</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50">
    
    
    <nav class="bg-gray-900 text-white sticky top-0 z-50 shadow-lg">
        <div class="container mx-auto px-6 py-4">
            <a href="index.php" class="text-xl font-bold">🌙 MoonHeritage</a>
        </div>
    </nav>

    <section class="py-8">
        <div class="container mx-auto px-6">
            
            <?php if ($error): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-6">
                    <i class="fas fa-exclamation-circle mr-3"></i>
                    <span><?php echo htmlspecialchars($error); ?></span>
                </div>
            <?php endif; ?>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                
                
                <div class="lg:col-span-2">
                    <form method="POST" action="booking.php" id="bookingForm">
                        
                        
                        <input type="hidden" name="hotel_id" value="<?php echo $hotelId; ?>">
                        <input type="hidden" name="check_in" value="<?php echo $checkIn; ?>">
                        <input type="hidden" name="check_out" value="<?php echo $checkOut; ?>">
                        <input type="hidden" name="adults" value="<?php echo $adults; ?>">
                        <input type="hidden" name="children" value="<?php echo $children; ?>">
                        <input type="hidden" name="rooms" value="<?php echo $rooms; ?>">
                        
                        
                        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                            <h2 class="text-2xl font-bold mb-6">Guest Information</h2>
                            
                            <div class="grid grid-cols-2 gap-4 mb-4">
                                <div>
                                    <label class="block text-gray-700 font-semibold mb-2">First Name *</label>
                                    <input type="text" name="first_name" required
                                           value="<?php echo htmlspecialchars($user['first_name']); ?>"
                                           class="w-full px-4 py-3 border rounded-lg">
                                </div>
                                <div>
                                    <label class="block text-gray-700 font-semibold mb-2">Last Name *</label>
                                    <input type="text" name="last_name" required
                                           value="<?php echo htmlspecialchars($user['last_name']); ?>"
                                           class="w-full px-4 py-3 border rounded-lg">
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-4 mb-4">
                                <div>
                                    <label class="block text-gray-700 font-semibold mb-2">Email *</label>
                                    <input type="email" name="email" required
                                           value="<?php echo htmlspecialchars($user['email']); ?>"
                                           class="w-full px-4 py-3 border rounded-lg">
                                </div>
                                <div>
                                    <label class="block text-gray-700 font-semibold mb-2">Phone *</label>
                                    <input type="tel" name="phone" required
                                           value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>"
                                           class="w-full px-4 py-3 border rounded-lg">
                                </div>
                            </div>

                            <div>
                                <label class="block text-gray-700 font-semibold mb-2">Special Requests</label>
                                <textarea name="special_requests" rows="3"
                                          class="w-full px-4 py-3 border rounded-lg"></textarea>
                            </div>
                        </div>

                        
                        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                            <h2 class="text-2xl font-bold mb-6">Payment Method</h2>
                            <label class="flex items-center p-4 border-2 rounded-lg mb-2 cursor-pointer">
                                <input type="radio" name="payment_method" value="credit_card" checked class="mr-4">
                                <span>Credit Card</span>
                            </label>
                            <label class="flex items-center p-4 border-2 rounded-lg mb-2 cursor-pointer">
                                <input type="radio" name="payment_method" value="paypal" class="mr-4">
                                <span>PayPal</span>
                            </label>
                            <label class="flex items-center p-4 border-2 rounded-lg cursor-pointer">
                                <input type="radio" name="payment_method" value="pay_at_hotel" class="mr-4">
                                <span>Pay at Hotel</span>
                            </label>
                        </div>

                        
                        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                            <label class="flex items-start cursor-pointer">
                                <input type="checkbox" id="terms" required class="mt-1 mr-3">
                                <span class="text-sm">I agree to the Terms and Conditions *</span>
                            </label>
                        </div>

                        
                        <button type="submit" name="confirm_booking" value="1"
                                class="w-full bg-blue-600 text-white py-4 rounded-lg hover:bg-blue-700 font-semibold text-lg">
                            <i class="fas fa-check-circle mr-2"></i>Confirm Booking
                        </button>
                    </form>
                </div>

                
                <div class="lg:col-span-1">
                    <div class="bg-white rounded-lg shadow-md p-6 sticky top-24">
                        <h3 class="text-xl font-bold mb-4">Booking Summary</h3>
                        <div class="mb-4">
                            <h4 class="font-bold"><?php echo htmlspecialchars($hotel['name']); ?></h4>
                            <p class="text-sm text-gray-600"><?php echo htmlspecialchars($hotel['city']); ?></p>
                        </div>
                        <div class="border-t pt-4">
                            <div class="flex justify-between mb-2">
                                <span>Check-in:</span>
                                <span><?php echo $checkIn; ?></span>
                            </div>
                            <div class="flex justify-between mb-2">
                                <span>Check-out:</span>
                                <span><?php echo $checkOut; ?></span>
                            </div>
                            <div class="flex justify-between mb-2">
                                <span>Nights:</span>
                                <span><?php echo $nights; ?></span>
                            </div>
                            <div class="flex justify-between mb-2">
                                <span>Subtotal:</span>
                                <span>$<?php echo number_format($subtotal, 2); ?></span>
                            </div>
                            <div class="flex justify-between mb-2">
                                <span>Tax:</span>
                                <span>$<?php echo number_format($taxAmount, 2); ?></span>
                            </div>
                            <div class="border-t pt-2 flex justify-between font-bold text-lg">
                                <span>Total:</span>
                                <span class="text-blue-600">$<?php echo number_format($totalAmount, 2); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    
    <script>
        console.log('✅ Booking page loaded');
        
        const form = document.getElementById('bookingForm');
        
        form.addEventListener('submit', function(e) {
            console.log('🔄 Form submitting...');
            
            const terms = document.getElementById('terms');
            if (!terms.checked) {
                e.preventDefault();
                alert('Please accept the terms and conditions');
                return false;
            }
            
            // Show loading
            const btn = form.querySelector('button[type="submit"]');
            btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Processing...';
            btn.disabled = true;
            
            console.log('✅ Form validation passed');
            // Form will now submit normally to PHP
        });
    </script>
</body>
</html>