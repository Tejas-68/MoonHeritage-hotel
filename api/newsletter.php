<?php
define('MOONHERITAGE_ACCESS', true);
require_once '../config.php';

header('Content-Type: application/json');


$input = json_decode(file_get_contents('php://input'), true);
$email = sanitize($input['email'] ?? '');


if (empty($email)) {
    jsonResponse(['success' => false, 'message' => 'Email is required'], 400);
}

if (!validateEmail($email)) {
    jsonResponse(['success' => false, 'message' => 'Invalid email address'], 400);
}

try {
    $db = getDB();
    
    
    $checkStmt = $db->prepare("SELECT id, status FROM newsletter_subscribers WHERE email = ?");
    $checkStmt->execute([$email]);
    $existing = $checkStmt->fetch();
    
    if ($existing) {
        if ($existing['status'] === 'subscribed') {
            jsonResponse([
                'success' => false,
                'message' => 'This email is already subscribed'
            ], 400);
        } else {
            
            $updateStmt = $db->prepare("UPDATE newsletter_subscribers SET status = 'subscribed', subscribed_at = NOW() WHERE email = ?");
            $updateStmt->execute([$email]);
            
            jsonResponse([
                'success' => true,
                'message' => 'Successfully resubscribed to newsletter'
            ]);
        }
    } else {
        
        $verificationToken = generateRandomString(64);
        
        
        $insertStmt = $db->prepare("
            INSERT INTO newsletter_subscribers (email, verification_token, subscribed_at) 
            VALUES (?, ?, NOW())
        ");
        $insertStmt->execute([$email, $verificationToken]);
        
        
        $verificationLink = SITE_URL . "verify-newsletter.php?token=" . $verificationToken;
        $emailSubject = "Welcome to MoonHeritage Newsletter";
        $emailMessage = "
            <html>
            <body style='font-family: Arial, sans-serif;'>
                <div style='max-width: 600px; margin: 0 auto; padding: 20px;'>
                    <h2 style='color: #3b82f6;'>Welcome to MoonHeritage!</h2>
                    <p>Thank you for subscribing to our newsletter.</p>
                    <p>You'll now receive exclusive deals, travel tips, and special offers directly to your inbox.</p>
                    <p style='text-align: center; margin: 30px 0;'>
                        <a href='$verificationLink' style='background: #3b82f6; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block;'>Verify Email</a>
                    </p>
                    <p style='font-size: 12px; color: #6b7280;'>
                        If you didn't subscribe, you can safely ignore this email.
                    </p>
                    <hr style='margin: 30px 0;'>
                    <p style='color: #6b7280; font-size: 12px;'>© 2025 MoonHeritage. All rights reserved.</p>
                </div>
            </body>
            </html>
        ";
        
        sendEmail($email, $emailSubject, $emailMessage);
        
        
        if (isLoggedIn()) {
            logActivity(getUserId(), 'newsletter_subscribed', "Subscribed: $email");
        }
        
        jsonResponse([
            'success' => true,
            'message' => 'Successfully subscribed! Please check your email to verify.'
        ]);
    }
    
} catch (PDOException $e) {
    error_log($e->getMessage());
    jsonResponse(['success' => false, 'message' => 'An error occurred'], 500);
}
?>