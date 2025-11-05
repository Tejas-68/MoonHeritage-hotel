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
        jsonResponse(['success' => false, 'message' => 'Invalid amenity ID'], 400);
    }
    
    $db = getDB();
    
    
    $amenityStmt = $db->prepare("SELECT name FROM amenities WHERE id = ?");
    $amenityStmt->execute([$id]);
    $amenity = $amenityStmt->fetch();
    
    if (!$amenity) {
        jsonResponse(['success' => false, 'message' => 'Amenity not found'], 404);
    }
    
    
    $deleteStmt = $db->prepare("DELETE FROM amenities WHERE id = ?");
    $deleteStmt->execute([$id]);
    
    logActivity(getUserId(), 'delete_amenity', "Deleted amenity: {$amenity['name']} (ID: $id)");
    
    jsonResponse([
        'success' => true, 
        'message' => 'Facility deleted successfully'
    ]);
    
} catch (PDOException $e) {
    error_log($e->getMessage());
    jsonResponse(['success' => false, 'message' => 'Database error occurred'], 500);
} catch (Exception $e) {
    error_log($e->getMessage());
    jsonResponse(['success' => false, 'message' => 'An error occurred'], 500);
}
?>