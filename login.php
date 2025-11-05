<?php
define('MOONHERITAGE_ACCESS', true);
require_once 'config.php';


if (isLoggedIn()) {
    redirect('index.php');
}


$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);
    
    if (empty($email) || empty($password)) {
        $error = 'Please enter both email and password';
    } else {
        try {
            $db = getDB();
            $stmt = $db->prepare("SELECT * FROM users WHERE email = ? AND status = 'active'");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if ($user && verifyPassword($password, $user['password'])) {
                
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['first_name'] = $user['first_name'];
                $_SESSION['last_name'] = $user['last_name'];
                
                
                $updateStmt = $db->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
                $updateStmt->execute([$user['id']]);
                
                
                logActivity($user['id'], 'login', 'User logged in');
                
                
                if ($remember) {
                    $token = generateRandomString(64);
                    setcookie('remember_token', $token, time() + (86400 * 30), '/'); 
                    
                    
                }
                
                
                if ($user['role'] === 'admin') {
                    header('Location: /MoonHeritage/admin/dashboard.php');
                } else {
                    redirect('index.php');
                }
            } else {
                $error = 'Invalid email or password';
                logActivity(null, 'failed_login', "Failed login attempt for email: $email");
            }
        } catch (PDOException $e) {
            $error = 'An error occurred. Please try again.';
            error_log($e->getMessage());
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - MoonHeritage</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .login-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .glass-effect {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
        }
    </style>
</head>
<body class="login-bg min-h-screen flex items-center justify-center p-4">
    <div class="container mx-auto max-w-md">
        
        <div class="text-center mb-8">
            <a href="index.php" class="inline-flex items-center space-x-2 text-white">
                <i class="fas fa-moon text-4xl"></i>
                <span class="text-3xl font-bold">MoonHeritage</span>
            </a>
        </div>

        
        <div class="glass-effect rounded-2xl shadow-2xl p-8">
            <div class="text-center mb-8">
                <h2 class="text-3xl font-bold text-gray-800 mb-2">Welcome Back</h2>
                <p class="text-gray-600">Sign in to your account to continue</p>
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

            
            <form method="POST" action="" id="loginForm">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                
                
                <div class="mb-6">
                    <label for="email" class="block text-gray-700 font-semibold mb-2">
                        <i class="fas fa-envelope mr-2"></i>Email Address
                    </label>
                    <input type="email" id="email" name="email" required
                           value="<?php echo isset($_POST['email']) ? escape($_POST['email']) : ''; ?>"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                           placeholder="Enter your email">
                </div>

                
                <div class="mb-6">
                    <label for="password" class="block text-gray-700 font-semibold mb-2">
                        <i class="fas fa-lock mr-2"></i>Password
                    </label>
                    <div class="relative">
                        <input type="password" id="password" name="password" required
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition pr-12"
                               placeholder="Enter your password">
                        <button type="button" onclick="togglePassword()" 
                                class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700">
                            <i class="fas fa-eye" id="toggleIcon"></i>
                        </button>
                    </div>
                </div>

                
                <div class="flex items-center justify-between mb-6">
                    <label class="flex items-center cursor-pointer">
                        <input type="checkbox" name="remember" class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-2 focus:ring-blue-500">
                        <span class="ml-2 text-gray-700">Remember me</span>
                    </label>
                    <a href="forgot-password.php" class="text-blue-600 hover:text-blue-800 hover:underline text-sm">
                        Forgot Password?
                    </a>
                </div>

                
                <button type="submit" 
                        class="w-full bg-gradient-to-r from-blue-600 to-purple-600 text-white font-semibold py-3 rounded-lg hover:from-blue-700 hover:to-purple-700 transition transform hover:scale-105 shadow-lg">
                    <i class="fas fa-sign-in-alt mr-2"></i>Sign In
                </button>
            </form>

            
            <div class="relative my-8">
                <div class="absolute inset-0 flex items-center">
                    <div class="w-full border-t border-gray-300"></div>
                </div>
                <div class="relative flex justify-center text-sm">
                    <span class="px-4 bg-white text-gray-500">Or continue with</span>
                </div>
            </div>

            
            <div class="grid grid-cols-3 gap-3 mb-6">
                <button type="button" 
                        class="flex items-center justify-center px-4 py-3 border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                    <i class="fab fa-google text-red-500 text-xl"></i>
                </button>
                <button type="button" 
                        class="flex items-center justify-center px-4 py-3 border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                    <i class="fab fa-facebook text-blue-600 text-xl"></i>
                </button>
                <button type="button" 
                        class="flex items-center justify-center px-4 py-3 border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                    <i class="fab fa-twitter text-blue-400 text-xl"></i>
                </button>
            </div>

            
            <div class="text-center">
                <p class="text-gray-600">
                    Don't have an account? 
                    <a href="signup.php" class="text-blue-600 hover:text-blue-800 font-semibold hover:underline">
                        Sign Up
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
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.getElementById('toggleIcon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }

        // Form validation
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            
            if (!email || !password) {
                e.preventDefault();
                alert('Please fill in all fields');
                return false;
            }
            
            // Email validation
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                e.preventDefault();
                alert('Please enter a valid email address');
                return false;
            }
            
            // Show loading state
            const submitBtn = this.querySelector('button[type="submit"]');
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Signing in...';
            submitBtn.disabled = true;
        });

        // Add animation on load
        window.addEventListener('load', function() {
            document.querySelector('.glass-effect').classList.add('fade-in');
        });

        // Demo credentials info
        console.log('Demo Login Credentials:');
        console.log('Email: admin@moonheritage.com');
        console.log('Password: admin123');
    </script>
</body>
</html>