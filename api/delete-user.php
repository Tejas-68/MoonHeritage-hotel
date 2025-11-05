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
    $userId = (int)($data['user_id'] ?? 0);
    
    if (!$userId) {
        jsonResponse(['success' => false, 'message' => 'Invalid user ID'], 400);
    }
    
    
    if ($userId == getUserId()) {
        jsonResponse(['success' => false, 'message' => 'You cannot delete your own account'], 400);
    }
    
    $db = getDB();
    
    
    $userStmt = $db->prepare("SELECT email, first_name, last_name, role FROM users WHERE id = ?");
    $userStmt->execute([$userId]);
    $user = $userStmt->fetch();
    
    if (!$user) {
        jsonResponse(['success' => false, 'message' => 'User not found'], 404);
    }
    
    
    if ($user['role'] === 'admin') {
        jsonResponse(['success' => false, 'message' => 'Cannot delete admin accounts'], 400);
    }
    
    
    $bookingCheck = $db->prepare("
        SELECT COUNT(*) as count 
        FROM bookings 
        WHERE user_id = ? 
        AND booking_status IN ('confirmed', 'pending')
    ");
    $bookingCheck->execute([$userId]);
    $activeBookings = $bookingCheck->fetch()['count'];
    
    if ($activeBookings > 0) {
        jsonResponse([
            'success' => false, 
            'message' => 'Cannot delete user with active bookings. Please cancel or complete all bookings first.'
        ], 400);
    }
    
    
    $deleteStmt = $db->prepare("DELETE FROM users WHERE id = ?");
    $deleteStmt->execute([$userId]);
    
    
    $subject = "Account Deletion - MoonHeritage";
    $message = "
        <html>
        <body style='font-family: Arial, sans-serif;'>
            <h2>Account Deleted</h2>
            <p>Dear {$user['first_name']} {$user['last_name']},</p>
            <p>Your MoonHeritage account has been permanently deleted by an administrator.</p>
            <p>All your data has been removed from our system.</p>
            <p>If you believe this is an error, please contact our support team.</p>
            <p>Best regards,<br>MoonHeritage Team</p>
        </body>
        </html>
    ";
    
    sendEmail($user['email'], $subject, $message);
    
    logActivity(
        getUserId(), 
        'delete_user', 
        "Deleted user: {$user['email']} (ID: $userId)"
    );
    
    jsonResponse([
        'success' => true, 
        'message' => 'User deleted successfully'
    ]);
    
} catch (PDOException $e) {
    error_log($e->getMessage());
    jsonResponse(['success' => false, 'message' => 'Database error occurred'], 500);
} catch (Exception $e) {
    error_log($e->getMessage());
    jsonResponse(['success' => false, 'message' => 'An error occurred'], 500);
}
?>