<?php
define('MOONHERITAGE_ACCESS', true);
require_once '../config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'message' => 'Invalid request method'], 405);
}

try {
    $data = json_decode(file_get_contents('php://input'), true);
    $email = filter_var($data['email'] ?? '', FILTER_SANITIZE_EMAIL);
    
    if (!validateEmail($email)) {
        jsonResponse(['success' => false, 'message' => 'Invalid email address'], 400);
    }
    
    $db = getDB();
    
    // Check if email already subscribed
    $checkStmt = $db->prepare("SELECT id, status FROM newsletter_subscribers WHERE email = ?");
    $checkStmt->execute([$email]);
    $subscriber = $checkStmt->fetch();
    
    if ($subscriber) {
        if ($subscriber['status'] === 'subscribed') {
            jsonResponse(['success' => false, 'message' => 'Email already subscribed'], 400);
        } else {
            // Reactivate subscription
            $updateStmt = $db->prepare("UPDATE newsletter_subscribers SET status = 'subscribed', subscribed_at = NOW(), unsubscribed_at = NULL WHERE email = ?");
            $updateStmt->execute([$email]);
            
            jsonResponse([
                'success' => true,
                'message' => 'Successfully resubscribed to newsletter!'
            ]);
        }
    } else {
        // New subscription
        $verificationToken = generateRandomString(32);
        $insertStmt = $db->prepare("INSERT INTO newsletter_subscribers (email, verification_token, subscribed_at) VALUES (?, ?, NOW())");
        $insertStmt->execute([$email, $verificationToken]);
        
        // In a real application, you would send a verification email here
        // sendEmail($email, 'Confirm Newsletter Subscription', 'Please click the link to confirm...');
        
        jsonResponse([
            'success' => true,
            'message' => 'Successfully subscribed to newsletter!'
        ]);
    }
    
} catch (PDOException $e) {
    error_log('Newsletter subscription error: ' . $e->getMessage());
    jsonResponse(['success' => false, 'message' => 'Database error occurred'], 500);
} catch (Exception $e) {
    error_log('Newsletter subscription error: ' . $e->getMessage());
    jsonResponse(['success' => false, 'message' => 'An error occurred'], 500);
}