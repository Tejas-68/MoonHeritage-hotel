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
    $db = getDB();
    
    $id = $_POST['id'] ?? null;
    $name = sanitize($_POST['name']);
    $slug = generateSlug($name);
    $description = sanitize($_POST['description']);
    $short_description = sanitize($_POST['short_description']);
    $address = sanitize($_POST['address']);
    $city = sanitize($_POST['city']);
    $country = sanitize($_POST['country']);
    $phone = sanitize($_POST['phone'] ?? '');
    $category = sanitize($_POST['category'] ?? 'hotel');
    $star_rating = (float)($_POST['star_rating'] ?? 5.0);
    $price_per_night = (float)$_POST['price_per_night'];
    $original_price = !empty($_POST['original_price']) ? (float)$_POST['original_price'] : null;
    $total_rooms = (int)$_POST['total_rooms'];
    $featured = (int)($_POST['featured'] ?? 0);
    $status = sanitize($_POST['status'] ?? 'active');
    
    
    $discount_percentage = 0;
    if ($original_price && $original_price > $price_per_night) {
        $discount_percentage = calculateDiscount($original_price, $price_per_night);
    }
    
    
    $main_image = null;
    if (isset($_FILES['main_image']) && $_FILES['main_image']['error'] === UPLOAD_ERR_OK) {
        $uploadResult = uploadImage($_FILES['main_image'], 'hotels');
        if ($uploadResult['success']) {
            $main_image = $uploadResult['path'];
        } else {
            jsonResponse(['success' => false, 'message' => $uploadResult['message']], 400);
        }
    }
    
    if ($id) {
        
        $sql = "UPDATE hotels SET 
                name = ?, slug = ?, description = ?, short_description = ?,
                address = ?, city = ?, country = ?, phone = ?, category = ?,
                star_rating = ?, price_per_night = ?, original_price = ?,
                discount_percentage = ?, total_rooms = ?, available_rooms = ?,
                featured = ?, status = ?, updated_at = NOW()";
        
        $params = [
            $name, $slug, $description, $short_description,
            $address, $city, $country, $phone, $category,
            $star_rating, $price_per_night, $original_price,
            $discount_percentage, $total_rooms, $total_rooms,
            $featured, $status
        ];
        
        if ($main_image) {
            $sql .= ", main_image = ?";
            $params[] = $main_image;
        }
        
        $sql .= " WHERE id = ?";
        $params[] = $id;
        
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        
        logActivity(getUserId(), 'update_hotel', "Updated hotel: $name (ID: $id)");
        
        jsonResponse(['success' => true, 'message' => 'Hotel updated successfully', 'id' => $id]);
        
    } else {
        
        if (!$main_image) {
            $main_image = 'hotels/default.jpg'; 
        }
        
        $sql = "INSERT INTO hotels (
                    name, slug, description, short_description,
                    address, city, country, phone, category,
                    star_rating, price_per_night, original_price,
                    discount_percentage, total_rooms, available_rooms,
                    featured, status, main_image, created_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
        
        $stmt = $db->prepare($sql);
        $stmt->execute([
            $name, $slug, $description, $short_description,
            $address, $city, $country, $phone, $category,
            $star_rating, $price_per_night, $original_price,
            $discount_percentage, $total_rooms, $total_rooms,
            $featured, $status, $main_image
        ]);
        
        $newId = $db->lastInsertId();
        
        logActivity(getUserId(), 'add_hotel', "Added new hotel: $name (ID: $newId)");
        
        jsonResponse(['success' => true, 'message' => 'Hotel added successfully', 'id' => $newId]);
    }
    
} catch (PDOException $e) {
    error_log($e->getMessage());
    jsonResponse(['success' => false, 'message' => 'Database error occurred'], 500);
} catch (Exception $e) {
    error_log($e->getMessage());
    jsonResponse(['success' => false, 'message' => 'An error occurred'], 500);
}
?>