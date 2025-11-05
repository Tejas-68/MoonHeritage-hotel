<?php 

if (!defined('MOONHERITAGE_ACCESS')) {
    define('MOONHERITAGE_ACCESS', true);
}

error_reporting(E_ALL);
ini_set('display_errors', 1);

date_default_timezone_set('Asia/Kolkata');


define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'moonheritage');
define('DB_CHARSET', 'utf8mb4');


define('SITE_NAME', 'MoonHeritage');

define('SITE_URL', 'http://localhost:8080/moonheritage/');

define('SITE_EMAIL', 'info@moonheritage.com');
define('ADMIN_EMAIL', 'admin@moonheritage.com');


define('ENCRYPTION_KEY', 'your-secret-key-here-change-in-production');
define('SESSION_LIFETIME', 3600); 
define('PASSWORD_MIN_LENGTH', 8);


define('UPLOAD_DIR', __DIR__ . '/uploads/');
define('MAX_FILE_SIZE', 209715200); 
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/gif', 'image/webp']);


define('ITEMS_PER_PAGE', 12);
define('HOTELS_PER_PAGE', 12);
define('REVIEWS_PER_PAGE', 10);


define('DEFAULT_CURRENCY', 'USD');
define('CURRENCY_SYMBOL', '$');


class Database {
    private static $instance = null;
    private $connection;
    
    private function __construct() {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
            ];
            
            $this->connection = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            die("Database Connection Failed: " . $e->getMessage());
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->connection;
    }
}

function getDB() {
    return Database::getInstance()->getConnection();
}

function sanitize($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

function redirect($url) {
    header("Location: " . $url);
    exit();
}

function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function getUserId() {
    return $_SESSION['user_id'] ?? null;
}

function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    try {
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([getUserId()]);
        return $stmt->fetch();
    } catch (PDOException $e) {
        return null;
    }
}

function formatPrice($price) {
    return CURRENCY_SYMBOL . number_format($price, 2);
}

function formatDate($date, $format = 'F d, Y') {
    return date($format, strtotime($date));
}

function generateRandomString($length = 32) {
    return bin2hex(random_bytes($length / 2));
}


function getImageUrl($path) {
    if (empty($path)) {
        return SITE_URL . 'images/default-hotel.jpg';
    }
    
    if (strpos($path, 'http://') === 0 || strpos($path, 'https://') === 0) {
        return $path;
    }
    
    $path = ltrim($path, '/');
    
    $imageUrl = SITE_URL . 'uploads/' . $path;
    
    
    error_log("Image URL generated: " . $imageUrl);
    
    return $imageUrl;
}













function hashPassword($password) {
    return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
}

function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}


function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function sendEmail($to, $subject, $message, $headers = []) {
    $defaultHeaders = [
        'From' => SITE_EMAIL,
        'Reply-To' => SITE_EMAIL,
        'X-Mailer' => 'PHP/' . phpversion(),
        'MIME-Version' => '1.0',
        'Content-type' => 'text/html; charset=UTF-8'
    ];

    $headers = array_merge($defaultHeaders, $headers);
    $headerString = '';
    
    foreach ($headers as $key => $value) {
        $headerString .= "$key: $value\r\n";
    }
    
    return mail($to, $subject, $message, $headerString);
}

function uploadImage($file, $directory = 'hotels') {
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => 'No file uploaded or upload error'];
    }
    
    if ($file['size'] > MAX_FILE_SIZE) {
        return ['success' => false, 'message' => 'File size exceeds limit'];
    }
    
    if (!in_array($file['type'], ALLOWED_IMAGE_TYPES)) {
        return ['success' => false, 'message' => 'Invalid file type'];
    }
    
    $uploadPath = UPLOAD_DIR . $directory . '/';
    if (!file_exists($uploadPath)) {
        mkdir($uploadPath, 0777, true);
    }
    
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '_' . time() . '.' . $extension;
    $destination = $uploadPath . $filename;
    
    if (move_uploaded_file($file['tmp_name'], $destination)) {
        return [
            'success' => true,
            'filename' => $filename,
            'path' => $directory . '/' . $filename
        ];
    }
    
    return ['success' => false, 'message' => 'Failed to move uploaded file'];
}

function deleteFile($path) {
    $fullPath = UPLOAD_DIR . $path;
    if (file_exists($fullPath)) {
        return unlink($fullPath);
    }
    return false;
}

function generateSlug($string) {
    $string = strtolower($string);
    $string = preg_replace('/[^a-z0-9\s-]/', '', $string);
    $string = preg_replace('/[\s-]+/', '-', $string);
    $string = trim($string, '-');
    return $string;
}

function paginate($totalItems, $currentPage = 1, $itemsPerPage = ITEMS_PER_PAGE) {
    $totalPages = ceil($totalItems / $itemsPerPage);
    $currentPage = max(1, min($currentPage, $totalPages));
    $offset = ($currentPage - 1) * $itemsPerPage;
    
    return [
        'total_items' => $totalItems,
        'total_pages' => $totalPages,
        'current_page' => $currentPage,
        'items_per_page' => $itemsPerPage,
        'offset' => $offset,
        'has_previous' => $currentPage > 1,
        'has_next' => $currentPage < $totalPages
    ];
}

function getStarRating($rating, $maxRating = 5) {
    $html = '<div class="flex text-yellow-400">';
    $fullStars = floor($rating);
    $halfStar = ($rating - $fullStars) >= 0.5;
    
    for ($i = 0; $i < $fullStars; $i++) {
        $html .= '<i class="fas fa-star"></i>';
    }
    
    if ($halfStar) {
        $html .= '<i class="fas fa-star-half-alt"></i>';
        $fullStars++;
    }
    
    for ($i = $fullStars; $i < $maxRating; $i++) {
        $html .= '<i class="far fa-star"></i>';
    }
    
    $html .= '</div>';
    return $html;
}

function calculateDiscount($originalPrice, $discountedPrice) {
    if ($originalPrice <= 0) return 0;
    return round((($originalPrice - $discountedPrice) / $originalPrice) * 100);
}

function logActivity($userId, $action, $details = '') {
    try {
        $db = getDB();
        $stmt = $db->prepare("INSERT INTO activity_logs (user_id, action, details, ip_address, created_at) VALUES (?, ?, ?, ?, NOW())");
        $stmt->execute([$userId, $action, $details, $_SERVER['REMOTE_ADDR']]);
        return true;
    } catch (PDOException $e) {
        return false;
    }
}

function getSetting($key, $default = null) {
    try {
        $db = getDB();
        $stmt = $db->prepare("SELECT value FROM settings WHERE setting_key = ?");
        $stmt->execute([$key]);
        $result = $stmt->fetch();
        return $result ? $result['value'] : $default;
    } catch (PDOException $e) {
        return $default;
    }
}

function setSetting($key, $value) {
    try {
        $db = getDB();
        $stmt = $db->prepare("INSERT INTO settings (setting_key, value, updated_at) VALUES (?, ?, NOW()) 
                             ON DUPLICATE KEY UPDATE value = ?, updated_at = NOW()");
        $stmt->execute([$key, $value, $value]);
        return true;
    } catch (PDOException $e) {
        return false;
    }
}

function jsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit();
}

function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function escape($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}




if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['initiated'])) {
    session_regenerate_id(true);
    $_SESSION['initiated'] = true;
    $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $_SESSION['ip_address'] = $_SERVER['REMOTE_ADDR'] ?? '';
}

if (isset($_SESSION['user_agent']) && $_SESSION['user_agent'] !== ($_SERVER['HTTP_USER_AGENT'] ?? '')) {
    session_unset();
    session_destroy();
    session_start();
}
?>