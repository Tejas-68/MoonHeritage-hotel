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
    $id = (int)($data['id'] ?? 0);
    
    if (!$id) {
        jsonResponse(['success' => false, 'message' => 'Invalid hotel ID'], 400);
    }
    
    $db = getDB();
    
    // Check if hotel has active bookings
    $bookingCheck = $db->prepare("
        SELECT COUNT(*) as count 
        FROM bookings 
        WHERE hotel_id = ? 
        AND booking_status IN ('confirmed', 'pending')
    ");
    $bookingCheck->execute([$id]);
    $activeBookings = $bookingCheck->fetch()['count'];
    
    if ($activeBookings > 0) {
        jsonResponse([
            'success' => false, 
            'message' => 'Cannot delete hotel with active bookings. Please cancel or complete all bookings first.'
        ], 400);
    }
    
    // Get hotel details for logging
    $hotelStmt = $db->prepare("SELECT name, main_image FROM hotels WHERE id = ?");
    $hotelStmt->execute([$id]);
    $hotel = $hotelStmt->fetch();
    
    if (!$hotel) {
        jsonResponse(['success' => false, 'message' => 'Hotel not found'], 404);
    }
    
    // Delete hotel image if exists
    if ($hotel['main_image'] && $hotel['main_image'] !== 'hotels/default.jpg') {
        deleteFile($hotel['main_image']);
    }
    
    // Delete hotel (cascades will handle related records)
    $deleteStmt = $db->prepare("DELETE FROM hotels WHERE id = ?");
    $deleteStmt->execute([$id]);
    
    logActivity(getUserId(), 'delete_hotel', "Deleted hotel: {$hotel['name']} (ID: $id)");
    
    jsonResponse([
        'success' => true, 
        'message' => 'Hotel deleted successfully'
    ]);
    
} catch (PDOException $e) {
    error_log($e->getMessage());
    jsonResponse(['success' => false, 'message' => 'Database error occurred'], 500);
} catch (Exception $e) {
    error_log($e->getMessage());
    jsonResponse(['success' => false, 'message' => 'An error occurred'], 500);
}
?>