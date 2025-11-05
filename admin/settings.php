<?php
define('MOONHERITAGE_ACCESS', true);
require_once '../config.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('../login.php');
}

$db = getDB();
$message = '';
$messageType = '';


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        
        if (!empty($_POST['current_password'])) {
            $currentPassword = $_POST['current_password'];
            $newPassword = $_POST['new_password'];
            $confirmPassword = $_POST['confirm_password'];
            
            
            $userStmt = $db->prepare("SELECT password FROM users WHERE id = ?");
            $userStmt->execute([getUserId()]);
            $user = $userStmt->fetch();
            
            if (!verifyPassword($currentPassword, $user['password'])) {
                throw new Exception('Current password is incorrect');
            }
            
            if (strlen($newPassword) < 8) {
                throw new Exception('New password must be at least 8 characters');
            }
            
            if ($newPassword !== $confirmPassword) {
                throw new Exception('New passwords do not match');
            }
            
            
            $hashedPassword = hashPassword($newPassword);
            $updatePwdStmt = $db->prepare("UPDATE users SET password = ?, updated_at = NOW() WHERE id = ?");
            $updatePwdStmt->execute([$hashedPassword, getUserId()]);
            
            logActivity(getUserId(), 'change_password', 'Admin changed their password');
            
            
            session_destroy();
            header('Location: ../login.php?message=Password changed successfully. Please login again.');
            exit();
        } else {
            
            foreach ($_POST as $key => $value) {
                if (!in_array($key, ['csrf_token', 'current_password', 'new_password', 'confirm_password'])) {
                    setSetting($key, sanitize($value));
                }
            }
            
            logActivity(getUserId(), 'update_settings', 'System settings updated');
            $message = 'Settings saved successfully!';
            $messageType = 'success';
        }
    } catch (Exception $e) {
        $message = 'Error: ' . $e->getMessage();
        $messageType = 'error';
    }
}


$settingsResult = $db->query("SELECT setting_key, value FROM settings")->fetchAll(PDO::FETCH_KEY_PAIR);
$settings = [];


$defaultSettings = [
    'site_name' => 'MoonHeritage',
    'site_email' => 'info@moonheritage.com',
    'site_phone' => '+1 234 567 8900',
    'booking_tax_percentage' => '10',
    'cancellation_hours' => '24',
    'featured_hotels_limit' => '8',
    'reviews_per_page' => '10',
    'min_booking_amount' => '50',
    'max_guests_per_booking' => '10',
    'currency' => 'USD',
    'currency_symbol' => '$',
    'enable_notifications' => '1',
    'enable_reviews' => '1',
    'require_email_verification' => '1'
];

$settings = array_merge($defaultSettings, $settingsResult);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @media (max-width: 768px) {
            body { display: none !important; }
        }
    </style>
</head>
<body class="bg-gray-100">
    <?php include 'includes/sidebar.php'; ?>

    <div class="ml-64 p-8">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-800">System Settings</h1>
            <p class="text-gray-600">Configure your website settings</p>
        </div>

        <?php if ($message): ?>
        <div class="mb-6 p-4 rounded-lg <?php echo $messageType === 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
            <div class="flex items-center">
                <i class="fas <?php echo $messageType === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'; ?> mr-3"></i>
                <span><?php echo escape($message); ?></span>
            </div>
        </div>
        <?php endif; ?>

        <form method="POST" class="space-y-6">
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">

            
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-bold text-gray-800 mb-6 flex items-center">
                    <i class="fas fa-cog text-blue-600 mr-3"></i>
                    General Settings
                </h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">Site Name</label>
                        <input type="text" name="site_name" value="<?php echo escape($settings['site_name']); ?>"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>

                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">Site Email</label>
                        <input type="email" name="site_email" value="<?php echo escape($settings['site_email']); ?>"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>

                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">Site Phone</label>
                        <input type="text" name="site_phone" value="<?php echo escape($settings['site_phone']); ?>"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>

                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">Currency</label>
                        <select name="currency" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            <option value="USD" <?php echo $settings['currency'] === 'USD' ? 'selected' : ''; ?>>USD - US Dollar</option>
                            <option value="EUR" <?php echo $settings['currency'] === 'EUR' ? 'selected' : ''; ?>>EUR - Euro</option>
                            <option value="GBP" <?php echo $settings['currency'] === 'GBP' ? 'selected' : ''; ?>>GBP - British Pound</option>
                            <option value="INR" <?php echo $settings['currency'] === 'INR' ? 'selected' : ''; ?>>INR - Indian Rupee</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">Currency Symbol</label>
                        <input type="text" name="currency_symbol" value="<?php echo escape($settings['currency_symbol']); ?>"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
            </div>

            
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-bold text-gray-800 mb-6 flex items-center">
                    <i class="fas fa-calendar-check text-green-600 mr-3"></i>
                    Booking Settings
                </h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">Tax Percentage (%)</label>
                        <input type="number" name="booking_tax_percentage" value="<?php echo escape($settings['booking_tax_percentage']); ?>"
                               min="0" max="100" step="0.1"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <p class="text-sm text-gray-500 mt-1">Tax applied to all bookings</p>
                    </div>

                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">Free Cancellation Hours</label>
                        <input type="number" name="cancellation_hours" value="<?php echo escape($settings['cancellation_hours']); ?>"
                               min="0" step="1"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <p class="text-sm text-gray-500 mt-1">Hours before check-in for free cancellation</p>
                    </div>

                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">Minimum Booking Amount ($)</label>
                        <input type="number" name="min_booking_amount" value="<?php echo escape($settings['min_booking_amount']); ?>"
                               min="0" step="0.01"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>

                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">Max Guests Per Booking</label>
                        <input type="number" name="max_guests_per_booking" value="<?php echo escape($settings['max_guests_per_booking']); ?>"
                               min="1" step="1"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
            </div>

            
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-bold text-gray-800 mb-6 flex items-center">
                    <i class="fas fa-desktop text-purple-600 mr-3"></i>
                    Display Settings
                </h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">Featured Hotels Limit</label>
                        <input type="number" name="featured_hotels_limit" value="<?php echo escape($settings['featured_hotels_limit']); ?>"
                               min="1" step="1"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <p class="text-sm text-gray-500 mt-1">Number of featured hotels on homepage</p>
                    </div>

                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">Reviews Per Page</label>
                        <input type="number" name="reviews_per_page" value="<?php echo escape($settings['reviews_per_page']); ?>"
                               min="5" step="1"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
            </div>

            
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-bold text-gray-800 mb-6 flex items-center">
                    <i class="fas fa-toggle-on text-blue-600 mr-3"></i>
                    Feature Settings
                </h2>
                
                <div class="space-y-4">
                    <label class="flex items-center justify-between p-4 bg-gray-50 rounded-lg cursor-pointer hover:bg-gray-100">
                        <div>
                            <div class="font-semibold text-gray-800">Email Notifications</div>
                            <div class="text-sm text-gray-600">Send email notifications to users</div>
                        </div>
                        <div class="relative">
                            <input type="checkbox" name="enable_notifications" value="1" 
                                   <?php echo $settings['enable_notifications'] ? 'checked' : ''; ?>
                                   class="sr-only peer">
                            <div class="w-14 h-7 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:left-[4px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-6 after:w-6 after:transition-all peer-checked:bg-blue-600"></div>
                        </div>
                    </label>

                    <label class="flex items-center justify-between p-4 bg-gray-50 rounded-lg cursor-pointer hover:bg-gray-100">
                        <div>
                            <div class="font-semibold text-gray-800">User Reviews</div>
                            <div class="text-sm text-gray-600">Allow users to leave reviews</div>
                        </div>
                        <div class="relative">
                            <input type="checkbox" name="enable_reviews" value="1" 
                                   <?php echo $settings['enable_reviews'] ? 'checked' : ''; ?>
                                   class="sr-only peer">
                            <div class="w-14 h-7 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:left-[4px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-6 after:w-6 after:transition-all peer-checked:bg-blue-600"></div>
                        </div>
                    </label>

                    <label class="flex items-center justify-between p-4 bg-gray-50 rounded-lg cursor-pointer hover:bg-gray-100">
                        <div>
                            <div class="font-semibold text-gray-800">Email Verification Required</div>
                            <div class="text-sm text-gray-600">Users must verify email before booking</div>
                        </div>
                        <div class="relative">
                            <input type="checkbox" name="require_email_verification" value="1" 
                                   <?php echo $settings['require_email_verification'] ? 'checked' : ''; ?>
                                   class="sr-only peer">
                            <div class="w-14 h-7 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:left-[4px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-6 after:w-6 after:transition-all peer-checked:bg-blue-600"></div>
                        </div>
                    </label>
                </div>
            </div>

            
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-bold text-gray-800 mb-6 flex items-center">
                    <i class="fas fa-lock text-red-600 mr-3"></i>
                    Change Admin Password
                </h2>
                
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
                    <div class="flex items-start">
                        <i class="fas fa-exclamation-triangle text-yellow-600 mt-1 mr-3"></i>
                        <div class="text-sm text-yellow-800">
                            <strong>Important:</strong> If you change your password, you will be logged out and need to log in again with your new password.
                        </div>
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">Current Password</label>
                        <input type="password" name="current_password" id="current_password"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>

                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">New Password</label>
                        <input type="password" name="new_password" id="new_password"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <p class="text-sm text-gray-500 mt-1">Min 8 characters</p>
                    </div>

                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">Confirm New Password</label>
                        <input type="password" name="confirm_password" id="confirm_password"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
            </div>

            
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-bold text-gray-800 mb-6 flex items-center">
                    <i class="fas fa-toggle-on text-blue-600 mr-3"></i>
                    Feature Settings
                </h2>
                
                <div class="space-y-4">
                    <label class="flex items-center justify-between p-4 bg-gray-50 rounded-lg cursor-pointer hover:bg-gray-100">
                        <div>
                            <div class="font-semibold text-gray-800">Email Notifications</div>
                            <div class="text-sm text-gray-600">Send email notifications to users</div>
                        </div>
                        <div class="relative">
                            <input type="checkbox" name="enable_notifications" value="1" 
                                   <?php echo $settings['enable_notifications'] ? 'checked' : ''; ?>
                                   class="sr-only peer">
                            <div class="w-14 h-7 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:left-[4px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-6 after:w-6 after:transition-all peer-checked:bg-blue-600"></div>
                        </div>
                    </label>

                    <label class="flex items-center justify-between p-4 bg-gray-50 rounded-lg cursor-pointer hover:bg-gray-100">
                        <div>
                            <div class="font-semibold text-gray-800">User Reviews</div>
                            <div class="text-sm text-gray-600">Allow users to leave reviews</div>
                        </div>
                        <div class="relative">
                            <input type="checkbox" name="enable_reviews" value="1" 
                                   <?php echo $settings['enable_reviews'] ? 'checked' : ''; ?>
                                   class="sr-only peer">
                            <div class="w-14 h-7 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:left-[4px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-6 after:w-6 after:transition-all peer-checked:bg-blue-600"></div>
                        </div>
                    </label>

                    <label class="flex items-center justify-between p-4 bg-gray-50 rounded-lg cursor-pointer hover:bg-gray-100">
                        <div>
                            <div class="font-semibold text-gray-800">Email Verification Required</div>
                            <div class="text-sm text-gray-600">Users must verify email before booking</div>
                        </div>
                        <div class="relative">
                            <input type="checkbox" name="require_email_verification" value="1" 
                                   <?php echo $settings['require_email_verification'] ? 'checked' : ''; ?>
                                   class="sr-only peer">
                            <div class="w-14 h-7 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:left-[4px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-6 after:w-6 after:transition-all peer-checked:bg-blue-600"></div>
                        </div>
                    </label>
                </div>
            </div>

            
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-bold text-gray-800 mb-6 flex items-center">
                    <i class="fas fa-info-circle text-gray-600 mr-3"></i>
                    System Information
                </h2>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <div class="text-sm text-gray-600 mb-1">PHP Version</div>
                        <div class="font-semibold text-gray-800"><?php echo phpversion(); ?></div>
                    </div>

                    <div class="bg-gray-50 p-4 rounded-lg">
                        <div class="text-sm text-gray-600 mb-1">Database</div>
                        <div class="font-semibold text-gray-800">MySQL <?php echo $db->query('SELECT VERSION()')->fetchColumn(); ?></div>
                    </div>

                    <div class="bg-gray-50 p-4 rounded-lg">
                        <div class="text-sm text-gray-600 mb-1">Server</div>
                        <div class="font-semibold text-gray-800"><?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown'; ?></div>
                    </div>
                </div>
            </div>

            
            <div class="flex justify-end space-x-4">
                <a href="dashboard.php" class="px-6 py-3 border border-gray-300 rounded-lg hover:bg-gray-50 font-semibold">
                    Cancel
                </a>
                <button type="submit" class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-semibold">
                    <i class="fas fa-save mr-2"></i>Save Settings
                </button>
            </div>
        </form>
    </div>

    <script>
        // Convert checkboxes to proper values
        document.querySelector('form').addEventListener('submit', function(e) {
            const checkboxes = this.querySelectorAll('input[type="checkbox"]');
            checkboxes.forEach(checkbox => {
                if (!checkbox.checked) {
                    const hidden = document.createElement('input');
                    hidden.type = 'hidden';
                    hidden.name = checkbox.name;
                    hidden.value = '0';
                    this.appendChild(hidden);
                }
            });
            
            // Validate password change if any password field is filled
            const currentPwd = document.getElementById('current_password').value;
            const newPwd = document.getElementById('new_password').value;
            const confirmPwd = document.getElementById('confirm_password').value;
            
            if (currentPwd || newPwd || confirmPwd) {
                if (!currentPwd) {
                    e.preventDefault();
                    alert('Please enter your current password');
                    return false;
                }
                if (!newPwd) {
                    e.preventDefault();
                    alert('Please enter a new password');
                    return false;
                }
                if (newPwd.length < 8) {
                    e.preventDefault();
                    alert('New password must be at least 8 characters');
                    return false;
                }
                if (newPwd !== confirmPwd) {
                    e.preventDefault();
                    alert('New passwords do not match');
                    return false;
                }
                
                if (!confirm('You will be logged out after changing your password. Continue?')) {
                    e.preventDefault();
                    return false;
                }
            }
        });
    </script>
</body>
</html>