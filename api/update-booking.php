<?php
define('MOONHERITAGE_ACCESS', true);
require_once '../config.php';

header('Content-Type: application/json');

if (!isLoggedIn() || !isAdmin()) {
    jsonResponse(['success' => false, 'message' => 'Unauthorized'], 401);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'message' => 'Invalid request method'], 405);
}

try {
    $data = json_decode(file_get_contents('php://input'), true);
    $bookingId = (int)($data['booking_id'] ?? 0);
    $status = sanitize($data['status'] ?? '');
    
    if (!$bookingId) {
        jsonResponse(['success' => false, 'message' => 'Invalid booking ID'], 400);
    }
    
    $validStatuses = ['confirmed', 'cancelled', 'completed', 'pending'];
    if (!in_array($status, $validStatuses)) {
        jsonResponse(['success' => false, 'message' => 'Invalid status'], 400);
    }
    
    $db = getDB();
    
    
    $bookingStmt = $db->prepare("
        SELECT b.*, h.name as hotel_name, u.email as user_email, u.first_name, u.last_name
        FROM bookings b
        JOIN hotels h ON b.hotel_id = h.id
        JOIN users u ON b.user_id = u.id
        WHERE b.id = ?
    ");
    $bookingStmt->execute([$bookingId]);
    $booking = $bookingStmt->fetch();
    
    if (!$booking) {
        jsonResponse(['success' => false, 'message' => 'Booking not found'], 404);
    }
    
    
    $updateSql = "UPDATE bookings SET booking_status = ?, updated_at = NOW()";
    $params = [$status];
    
    if ($status === 'cancelled') {
        $updateSql .= ", cancelled_at = NOW()";
    }
    
    $updateSql .= " WHERE id = ?";
    $params[] = $bookingId;
    
    $stmt = $db->prepare($updateSql);
    $stmt->execute($params);
    
    
    $subject = "Booking " . ucfirst($status) . " - " . $booking['hotel_name'];
    $message = "
        <html>
        <body style='font-family: Arial, sans-serif;'>
            <h2>Booking Update</h2>
            <p>Dear {$booking['first_name']} {$booking['last_name']},</p>
            <p>Your booking #{$booking['booking_number']} at {$booking['hotel_name']} has been <strong>" . strtoupper($status) . "</strong>.</p>
            
            <div style='background: #f3f4f6; padding: 20px; border-radius: 8px; margin: 20px 0;'>
                <h3>Booking Details:</h3>
                <p><strong>Hotel:</strong> {$booking['hotel_name']}</p>
                <p><strong>Check-in:</strong> {$booking['check_in_date']}</p>
                <p><strong>Check-out:</strong> {$booking['check_out_date']}</p>
                <p><strong>Total Amount:</strong> " . formatPrice($booking['total_amount']) . "</p>
                <p><strong>Status:</strong> " . ucfirst($status) . "</p>
            </div>
            
            " . ($status === 'cancelled' ? 
                "<p style='color: #dc2626;'>If you have any questions about this cancellation, please contact us.</p>" : 
                "<p style='color: #16a34a;'>Thank you for choosing MoonHeritage!</p>") . "
            
            <p>Best regards,<br>MoonHeritage Team</p>
        </body>
        </html>
    ";
    
    sendEmail($booking['user_email'], $subject, $message);
    
    logActivity(
        getUserId(), 
        'update_booking_status', 
        "Updated booking #{$booking['booking_number']} status to $status"
    );
    
    jsonResponse([
        'success' => true, 
        'message' => "Booking $status successfully",
        'booking_id' => $bookingId,
        'status' => $status
    ]);
    
} catch (PDOException $e) {
    error_log($e->getMessage());
    jsonResponse(['success' => false, 'message' => 'Database error occurred'], 500);
} catch (Exception $e) {
    error_log($e->getMessage());
    jsonResponse(['success' => false, 'message' => 'An error occurred'], 500);
}
?>