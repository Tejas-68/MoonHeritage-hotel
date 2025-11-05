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
    
    $id = $data['id'] ?? null;
    $name = sanitize($data['name'] ?? '');
    $icon = sanitize($data['icon'] ?? '');
    $category = sanitize($data['category'] ?? '');
    
    if (empty($name) || empty($icon) || empty($category)) {
        jsonResponse(['success' => false, 'message' => 'All fields are required'], 400);
    }
    
    $db = getDB();
    
    if ($id) {
        
        $stmt = $db->prepare("
            UPDATE amenities 
            SET name = ?, icon = ?, category = ?
            WHERE id = ?
        ");
        $stmt->execute([$name, $icon, $category, $id]);
        
        logActivity(getUserId(), 'update_amenity', "Updated amenity: $name (ID: $id)");
        
        jsonResponse([
            'success' => true, 
            'message' => 'Facility updated successfully',
            'id' => $id
        ]);
        
    } else {
        
        $checkStmt = $db->prepare("SELECT id FROM amenities WHERE name = ?");
        $checkStmt->execute([$name]);
        
        if ($checkStmt->fetch()) {
            jsonResponse(['success' => false, 'message' => 'Facility with this name already exists'], 400);
        }
        
        
        $stmt = $db->prepare("
            INSERT INTO amenities (name, icon, category, created_at) 
            VALUES (?, ?, ?, NOW())
        ");
        $stmt->execute([$name, $icon, $category]);
        
        $newId = $db->lastInsertId();
        
        logActivity(getUserId(), 'add_amenity', "Added new amenity: $name (ID: $newId)");
        
        jsonResponse([
            'success' => true, 
            'message' => 'Facility added successfully',
            'id' => $newId
        ]);
    }
    
} catch (PDOException $e) {
    error_log($e->getMessage());
    jsonResponse(['success' => false, 'message' => 'Database error occurred'], 500);
} catch (Exception $e) {
    error_log($e->getMessage());
    jsonResponse(['success' => false, 'message' => 'An error occurred'], 500);
}
?>