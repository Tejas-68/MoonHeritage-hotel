<?php
define('MOONHERITAGE_ACCESS', true);
require_once '../config.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isLoggedIn()) {
    jsonResponse(['success' => false, 'message' => 'Please login first'], 401);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'message' => 'Invalid request method'], 405);
}

try {
    $data = json_decode(file_get_contents('php://input'), true);
    $hotelId = (int)($data['hotel_id'] ?? 0);
    
    if ($hotelId <= 0) {
        jsonResponse(['success' => false, 'message' => 'Invalid hotel ID'], 400);
    }
    
    $db = getDB();
    $userId = getUserId();
    
    // Check if hotel exists
    $hotelStmt = $db->prepare("SELECT id FROM hotels WHERE id = ? AND status = 'active'");
    $hotelStmt->execute([$hotelId]);
    if (!$hotelStmt->fetch()) {
        jsonResponse(['success' => false, 'message' => 'Hotel not found'], 404);
    }
    
    // Check if already in wishlist
    $checkStmt = $db->prepare("SELECT id FROM wishlist WHERE user_id = ? AND hotel_id = ?");
    $checkStmt->execute([$userId, $hotelId]);
    $exists = $checkStmt->fetch();
    
    if ($exists) {
        // Remove from wishlist
        $deleteStmt = $db->prepare("DELETE FROM wishlist WHERE user_id = ? AND hotel_id = ?");
        $deleteStmt->execute([$userId, $hotelId]);
        
        jsonResponse([
            'success' => true,
            'action' => 'removed',
            'message' => 'Removed from wishlist'
        ]);
    } else {
        // Add to wishlist
        $insertStmt = $db->prepare("INSERT INTO wishlist (user_id, hotel_id, created_at) VALUES (?, ?, NOW())");
        $insertStmt->execute([$userId, $hotelId]);
        
        jsonResponse([
            'success' => true,
            'action' => 'added',
            'message' => 'Added to wishlist'
        ]);
    }
    
} catch (PDOException $e) {
    error_log('Wishlist error: ' . $e->getMessage());
    jsonResponse(['success' => false, 'message' => 'Database error occurred'], 500);
} catch (Exception $e) {
    error_log('Wishlist error: ' . $e->getMessage());
    jsonResponse(['success' => false, 'message' => 'An error occurred'], 500);
}