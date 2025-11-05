<?php
define('MOONHERITAGE_ACCESS', true);
require_once 'config.php';


if (isLoggedIn()) {
    redirect('index.php');
}


$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid request. Please try again.';
    } else {
        $username = sanitize($_POST['username'] ?? '');
        $email = sanitize($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        $firstName = sanitize($_POST['first_name'] ?? '');
        $lastName = sanitize($_POST['last_name'] ?? '');
        $phone = sanitize($_POST['phone'] ?? '');
        $agreeTerms = isset($_POST['agree_terms']);
        
        
        if (empty($username) || empty($email) || empty($password) || empty($confirmPassword)) {
            $error = 'Please fill in all required fields';
        } elseif (!validateEmail($email)) {
            $error = 'Please enter a valid email address';
        } elseif (strlen($username) < 3) {
            $error = 'Username must be at least 3 characters long';
        } elseif (strlen($password) < PASSWORD_MIN_LENGTH) {
            $error = 'Password must be at least ' . PASSWORD_MIN_LENGTH . ' characters long';
        } elseif ($password !== $confirmPassword) {
            $error = 'Passwords do not match';
        } elseif (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)/', $password)) {
            $error = 'Password must contain at least one uppercase letter, one lowercase letter, and one number';
        } elseif (!$agreeTerms) {
            $error = 'You must agree to the Terms and Conditions';
        } else {
            try {
                $db = getDB();
                
                
                $stmt = $db->prepare("SELECT id FROM users WHERE username = ?");
                $stmt->execute([$username]);
                if ($stmt->fetch()) {
                    $error = 'Username already taken';
                } else {
                    
                    $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
                    $stmt->execute([$email]);
                    if ($stmt->fetch()) {
                        $error = 'Email already registered';
                    } else {
                        
                        $hashedPassword = hashPassword($password);
                        $verificationToken = generateRandomString(64);
                        
                        $stmt = $db->prepare("INSERT INTO users (username, email, password, first_name, last_name, phone, verification_token, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
                        
                        if ($stmt->execute([$username, $email, $hashedPassword, $firstName, $lastName, $phone, $verificationToken])) {
                            $userId = $db->lastInsertId();
                            
                            
                            logActivity($userId, 'registration', 'New user registered');
                            
                            
                            $verificationLink = SITE_URL . "verify-email.php?token=" . $verificationToken;
                            $emailSubject = "Welcome to MoonHeritage - Verify Your Email";
                            $emailMessage = "
                                <html>
                                <body style='font-family: Arial, sans-serif;'>
                                    <div style='max-width: 600px; margin: 0 auto; padding: 20px;'>
                                        <h2 style='color: #3b82f6;'>Welcome to MoonHeritage!</h2>
                                        <p>Hi $firstName,</p>
                                        <p>Thank you for registering with MoonHeritage. To complete your registration, please verify your email address by clicking the button below:</p>
                                        <p style='text-align: center; margin: 30px 0;'>
                                            <a href='$verificationLink' style='background: #3b82f6; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block;'>Verify Email</a>
                                        </p>
                                        <p>Or copy and paste this link into your browser:</p>
                                        <p style='background: #f3f4f6; padding: 10px; border-radius: 5px; word-break: break-all;'>$verificationLink</p>
                                        <p>If you didn't create this account, please ignore this email.</p>
                                        <hr style='margin: 30px 0;'>
                                        <p style='color: #6b7280; font-size: 12px;'>© 2025 MoonHeritage. All rights reserved.</p>
                                    </div>
                                </body>
                                </html>
                            ";
                            
                            sendEmail($email, $emailSubject, $emailMessage);
                            
                            $success = 'Registration successful! Please check your email to verify your account.';
                            
                            
                            $_SESSION['user_id'] = $userId;
                            $_SESSION['username'] = $username;
                            $_SESSION['email'] = $email;
                            $_SESSION['role'] = 'user';
                            $_SESSION['first_name'] = $firstName;
                            $_SESSION['last_name'] = $lastName;
                            
                            
                            header("Refresh: 2; url=index.php");
                        } else {
                            $error = 'Registration failed. Please try again.';
                        }
                    }
                }
            } catch (PDOException $e) {
                $error = 'An error occurred. Please try again.';
                error_log($e->getMessage());
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - MoonHeritage</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .signup-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .glass-effect {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
        }
        .password-strength {
            height: 4px;
            border-radius: 2px;
            transition: all 0.3s ease;
        }
    </style>
</head>
<body class="signup-bg min-h-screen flex items-center justify-center p-4">
    <div class="container mx-auto max-w-2xl">
        
        <div class="text-center mb-8">
            <a href="index.php" class="inline-flex items-center space-x-2 text-white">
                <i class="fas fa-moon text-4xl"></i>
                <span class="text-3xl font-bold">MoonHeritage</span>
            </a>
        </div>

        
        <div class="glass-effect rounded-2xl shadow-2xl p-8">
            <div class="text-center mb-8">
                <h2 class="text-3xl font-bold text-gray-800 mb-2">Create Account</h2>
                <p class="text-gray-600">Join us and discover your perfect staycation</p>
            </div>

            <?php if ($error): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-6 flex items-center">
                    <i class="fas fa-exclamation-circle mr-3"></i>
                    <span><?php echo escape($error); ?></span>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-6 flex items-center">
                    <i class="fas fa-check-circle mr-3"></i>
                    <span><?php echo escape($success); ?></span>
                </div>
            <?php endif; ?>

            
            <form method="POST" action="" id="signupForm">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    
                    <div>
                        <label for="first_name" class="block text-gray-700 font-semibold mb-2">
                            <i class="fas fa-user mr-2"></i>First Name
                        </label>
                        <input type="text" id="first_name" name="first_name"
                               value="<?php echo isset($_POST['first_name']) ? escape($_POST['first_name']) : ''; ?>"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition"
                               placeholder="John">
                    </div>

                    
                    <div>
                        <label for="last_name" class="block text-gray-700 font-semibold mb-2">
                            <i class="fas fa-user mr-2"></i>Last Name
                        </label>
                        <input type="text" id="last_name" name="last_name"
                               value="<?php echo isset($_POST['last_name']) ? escape($_POST['last_name']) : ''; ?>"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition"
                               placeholder="Doe">
                    </div>
                </div>

                
                <div class="mb-6">
                    <label for="username" class="block text-gray-700 font-semibold mb-2">
                        <i class="fas fa-at mr-2"></i>Username <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="username" name="username" required
                           value="<?php echo isset($_POST['username']) ? escape($_POST['username']) : ''; ?>"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition"
                           placeholder="johndoe">
                    <p class="text-gray-500 text-sm mt-1">Must be at least 3 characters</p>
                </div>

                
                <div class="mb-6">
                    <label for="email" class="block text-gray-700 font-semibold mb-2">
                        <i class="fas fa-envelope mr-2"></i>Email Address <span class="text-red-500">*</span>
                    </label>
                    <input type="email" id="email" name="email" required
                           value="<?php echo isset($_POST['email']) ? escape($_POST['email']) : ''; ?>"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition"
                           placeholder="john@example.com">
                </div>

                
                <div class="mb-6">
                    <label for="phone" class="block text-gray-700 font-semibold mb-2">
                        <i class="fas fa-phone mr-2"></i>Phone Number
                    </label>
                    <input type="tel" id="phone" name="phone"
                           value="<?php echo isset($_POST['phone']) ? escape($_POST['phone']) : ''; ?>"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition"
                           placeholder="+1 234 567 8900">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    
                    <div>
                        <label for="password" class="block text-gray-700 font-semibold mb-2">
                            <i class="fas fa-lock mr-2"></i>Password <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input type="password" id="password" name="password" required
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition pr-12"
                                   placeholder="Enter password">
                            <button type="button" onclick="togglePassword('password', 'toggleIcon1')" 
                                    class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700">
                                <i class="fas fa-eye" id="toggleIcon1"></i>
                            </button>
                        </div>
                        <div class="mt-2">
                            <div class="password-strength bg-gray-200" id="passwordStrength"></div>
                            <p class="text-sm text-gray-500 mt-1" id="strengthText">Password strength</p>
                        </div>
                    </div>

                    
                    <div>
                        <label for="confirm_password" class="block text-gray-700 font-semibold mb-2">
                            <i class="fas fa-lock mr-2"></i>Confirm Password <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input type="password" id="confirm_password" name="confirm_password" required
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition pr-12"
                                   placeholder="Confirm password">
                            <button type="button" onclick="togglePassword('confirm_password', 'toggleIcon2')" 
                                    class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700">
                                <i class="fas fa-eye" id="toggleIcon2"></i>
                            </button>
                        </div>
                    </div>
                </div>

                
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                    <p class="text-sm text-gray-700 font-semibold mb-2">Password must contain:</p>
                    <ul class="text-sm text-gray-600 space-y-1">
                        <li id="req-length" class="flex items-center">
                            <i class="fas fa-circle text-gray-400 text-xs mr-2"></i>
                            At least 8 characters
                        </li>
                        <li id="req-uppercase" class="flex items-center">
                            <i class="fas fa-circle text-gray-400 text-xs mr-2"></i>
                            One uppercase letter
                        </li>
                        <li id="req-lowercase" class="flex items-center">
                            <i class="fas fa-circle text-gray-400 text-xs mr-2"></i>
                            One lowercase letter
                        </li>
                        <li id="req-number" class="flex items-center">
                            <i class="fas fa-circle text-gray-400 text-xs mr-2"></i>
                            One number
                        </li>
                    </ul>
                </div>

                
                <div class="mb-6">
                    <label class="flex items-start cursor-pointer">
                        <input type="checkbox" name="agree_terms" required class="mt-1 w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-2 focus:ring-blue-500">
                        <span class="ml-2 text-gray-700 text-sm">
                            I agree to the <a href="terms.php" class="text-blue-600 hover:underline">Terms and Conditions</a> 
                            and <a href="privacy.php" class="text-blue-600 hover:underline">Privacy Policy</a>
                        </span>
                    </label>
                </div>

                
                <button type="submit" 
                        class="w-full bg-gradient-to-r from-blue-600 to-purple-600 text-white font-semibold py-3 rounded-lg hover:from-blue-700 hover:to-purple-700 transition transform hover:scale-105 shadow-lg">
                    <i class="fas fa-user-plus mr-2"></i>Create Account
                </button>
            </form>

            
            <div class="text-center">
                <p class="text-gray-600">
                    Already have an account? 
                    <a href="login.php" class="text-blue-600 hover:text-blue-800 font-semibold hover:underline">
                        Sign In
                    </a>
                </p>
            </div>
        </div>

        
        <div class="text-center mt-6">
            <a href="index.php" class="text-white hover:text-gray-200 flex items-center justify-center space-x-2">
                <i class="fas fa-arrow-left"></i>
                <span>Back to Home</span>
            </a>
        </div>
    </div>

    <script>
        // Toggle password visibility
        function togglePassword(inputId, iconId) {
            const input = document.getElementById(inputId);
            const icon = document.getElementById(iconId);
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }

        // Password strength checker
        const passwordInput = document.getElementById('password');
        const strengthBar = document.getElementById('passwordStrength');
        const strengthText = document.getElementById('strengthText');

        passwordInput.addEventListener('input', function() {
            const password = this.value;
            let strength = 0;
            
            // Check requirements
            const hasLength = password.length >= 8;
            const hasUpper = /[A-Z]/.test(password);
            const hasLower = /[a-z]/.test(password);
            const hasNumber = /\d/.test(password);
            const hasSpecial = /[!@#$%^&*(),.?":{}|<>]/.test(password);
            
            // Update requirement indicators
            updateRequirement('req-length', hasLength);
            updateRequirement('req-uppercase', hasUpper);
            updateRequirement('req-lowercase', hasLower);
            updateRequirement('req-number', hasNumber);
            
            // Calculate strength
            if (hasLength) strength++;
            if (hasUpper) strength++;
            if (hasLower) strength++;
            if (hasNumber) strength++;
            if (hasSpecial) strength++;
            
            // Update strength bar
            const colors = ['#ef4444', '#f59e0b', '#eab308', '#84cc16', '#22c55e'];
            const labels = ['Very Weak', 'Weak', 'Fair', 'Good', 'Strong'];
            
            if (password.length === 0) {
                strengthBar.style.width = '0%';
                strengthBar.style.backgroundColor = '#e5e7eb';
                strengthText.textContent = 'Password strength';
                strengthText.className = 'text-sm text-gray-500 mt-1';
            } else {
                const percentage = (strength / 5) * 100;
                strengthBar.style.width = percentage + '%';
                strengthBar.style.backgroundColor = colors[strength - 1];
                strengthText.textContent = labels[strength - 1];
                strengthText.className = 'text-sm mt-1';
                strengthText.style.color = colors[strength - 1];
            }
        });

        function updateRequirement(id, met) {
            const element = document.getElementById(id);
            const icon = element.querySelector('i');
            
            if (met) {
                icon.classList.remove('fa-circle', 'text-gray-400');
                icon.classList.add('fa-check-circle', 'text-green-500');
                element.classList.add('text-green-600');
            } else {
                icon.classList.remove('fa-check-circle', 'text-green-500');
                icon.classList.add('fa-circle', 'text-gray-400');
                element.classList.remove('text-green-600');
            }
        }

        // Form validation
        document.getElementById('signupForm').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Passwords do not match!');
                return false;
            }
            
            // Show loading state
            const submitBtn = this.querySelector('button[type="submit"]');
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Creating account...';
            submitBtn.disabled = true;
        });

        // Add animation on load
        window.addEventListener('load', function() {
            document.querySelector('.glass-effect').classList.add('fade-in');
        });
    </script>
</body>
</html>