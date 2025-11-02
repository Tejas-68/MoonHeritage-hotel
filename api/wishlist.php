<?php
define('MOONHERITAGE_ACCESS', true);
require_once '../config.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isLoggedIn()) {
    jsonResponse(['success' => false, 'message' => 'Please login first'], 401);
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);
$hotelId = (int)($input['hotel_id'] ?? 0);

if (!$hotelId) {
    jsonResponse(['success' => false, 'message' => 'Invalid hotel ID'], 400);
}

try {
    $db = getDB();
    $userId = getUserId();
    
    // Check if already in wishlist
    $checkStmt = $db->prepare("SELECT id FROM wishlist WHERE user_id = ? AND hotel_id = ?");
    $checkStmt->execute([$userId, $hotelId]);
    $exists = $checkStmt->fetch();
    
    if ($exists) {
        // Remove from wishlist
        $deleteStmt = $db->prepare("DELETE FROM wishlist WHERE user_id = ? AND hotel_id = ?");
        $deleteStmt->execute([$userId, $hotelId]);
        
        logActivity($userId, 'wishlist_removed', "Removed hotel ID: $hotelId from wishlist");
        
        jsonResponse([
            'success' => true,
            'action' => 'removed',
            'message' => 'Removed from wishlist'
        ]);
    } else {
        // Add to wishlist
        $insertStmt = $db->prepare("INSERT INTO wishlist (user_id, hotel_id, created_at) VALUES (?, ?, NOW())");
        $insertStmt->execute([$userId, $hotelId]);
        
        logActivity($userId, 'wishlist_added', "Added hotel ID: $hotelId to wishlist");
        
        jsonResponse([
            'success' => true,
            'action' => 'added',
            'message' => 'Added to wishlist'
        ]);
    }
    
} catch (PDOException $e) {
    error_log($e->getMessage());
    jsonResponse(['success' => false, 'message' => 'An error occurred'], 500);
}
?>