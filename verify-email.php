<?php
define('MOONHERITAGE_ACCESS', true);
require_once 'config.php';

$token = sanitize($_GET['token'] ?? '');
$message = '';
$messageType = '';

if (empty($token)) {
    redirect('login.php?error=invalid_token');
}

try {
    $db = getDB();
    
    
    $stmt = $db->prepare("
        SELECT id, email, first_name 
        FROM users 
        WHERE verification_token = ? 
        AND email_verified = 0
    ");
    $stmt->execute([$token]);
    $user = $stmt->fetch();
    
    if (!$user) {
        $message = 'Invalid or expired verification link';
        $messageType = 'error';
    } else {
        
        $updateStmt = $db->prepare("
            UPDATE users 
            SET email_verified = 1, 
                verification_token = NULL,
                updated_at = NOW()
            WHERE id = ?
        ");
        $updateStmt->execute([$user['id']]);
        
        
        logActivity($user['id'], 'email_verified', 'Email address verified');
        
        
        $welcomeSubject = "Welcome to MoonHeritage - {$user['first_name']}!";
        $welcomeMessage = "
            <html>
            <body style='font-family: Arial, sans-serif;'>
                <div style='max-width: 600px; margin: 0 auto; padding: 20px;'>
                    <h2 style='color: #3b82f6;'>Welcome to MoonHeritage!</h2>
                    <p>Hi {$user['first_name']},</p>
                    <p>Your email has been successfully verified. You can now:</p>
                    <ul>
                        <li>Book hotels and villas</li>
                        <li>Save favorites to your wishlist</li>
                        <li>Leave reviews for properties</li>
                        <li>Manage your bookings</li>
                    </ul>
                    <p style='text-align: center; margin: 30px 0;'>
                        <a href='" . SITE_URL . "hotels.php' 
                           style='background: #3b82f6; color: white; padding: 12px 30px; 
                                  text-decoration: none; border-radius: 5px; display: inline-block;'>
                            Start Exploring
                        </a>
                    </p>
                    <p>If you have any questions, feel free to contact our support team.</p>
                    <hr style='margin: 30px 0;'>
                    <p style='color: #6b7280; font-size: 12px;'>
                        © " . date('Y') . " MoonHeritage. All rights reserved.
                    </p>
                </div>
            </body>
            </html>
        ";
        
        sendEmail($user['email'], $welcomeSubject, $welcomeMessage);
        
        $message = 'Email verified successfully! You can now log in.';
        $messageType = 'success';
    }
    
} catch (PDOException $e) {
    error_log($e->getMessage());
    $message = 'An error occurred during verification';
    $messageType = 'error';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verification - MoonHeritage</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gradient-to-br from-blue-500 to-purple-600 min-h-screen flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-8">
        <div class="text-center mb-6">
            <?php if ($messageType === 'success'): ?>
                <div class="bg-green-100 rounded-full w-20 h-20 flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-check-circle text-green-600 text-4xl"></i>
                </div>
                <h1 class="text-2xl font-bold text-gray-800 mb-2">Email Verified!</h1>
            <?php else: ?>
                <div class="bg-red-100 rounded-full w-20 h-20 flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-times-circle text-red-600 text-4xl"></i>
                </div>
                <h1 class="text-2xl font-bold text-gray-800 mb-2">Verification Failed</h1>
            <?php endif; ?>
            
            <p class="text-gray-600"><?php echo escape($message); ?></p>
        </div>
        
        <div class="flex gap-4">
            <?php if ($messageType === 'success'): ?>
                <a href="login.php" class="flex-1 bg-blue-600 text-white text-center py-3 rounded-lg hover:bg-blue-700 transition font-semibold">
                    <i class="fas fa-sign-in-alt mr-2"></i>Login Now
                </a>
            <?php else: ?>
                <a href="signup.php" class="flex-1 bg-blue-600 text-white text-center py-3 rounded-lg hover:bg-blue-700 transition font-semibold">
                    <i class="fas fa-user-plus mr-2"></i>Sign Up Again
                </a>
            <?php endif; ?>
            <a href="index.php" class="flex-1 border border-gray-300 text-gray-700 text-center py-3 rounded-lg hover:bg-gray-50 transition font-semibold">
                <i class="fas fa-home mr-2"></i>Home
            </a>
        </div>
    </div>
</body>
</html>