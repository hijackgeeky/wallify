<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'wallpaper_admin');

// Site configuration
$site_url = isset($_SERVER['HTTP_HOST']) ? 'http://' . $_SERVER['HTTP_HOST'] . '/admin' : 'http://localhost/admin';
define('SITE_URL', $site_url);
define('ADMIN_PATH', dirname(__DIR__) . DIRECTORY_SEPARATOR);
define('UPLOAD_DIR', ADMIN_PATH . 'uploads/');
define('THUMBNAIL_DIR', ADMIN_PATH . 'uploads/thumbnails/');

// Security configuration
define('MIN_PASSWORD_LENGTH', 8);
define('MAX_FILE_SIZE', 104857600); // 100MB

// Create database connection
try {
    $pdo = new PDO(
        "mysql:host=".DB_HOST.";dbname=".DB_NAME.";charset=utf8mb4", 
        DB_USER, 
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Create upload directories if they don't exist
if (!file_exists(UPLOAD_DIR)) {
    mkdir(UPLOAD_DIR, 0755, true);
}
if (!file_exists(THUMBNAIL_DIR)) {
    mkdir(THUMBNAIL_DIR, 0755, true);
}

// Start secure session
session_name('WALLPAPER_ADMIN_SESSION');
session_set_cookie_params([
    'lifetime' => 86400,
    'path' => '/',
    'domain' => parse_url(SITE_URL, PHP_URL_HOST),
    'secure' => (parse_url(SITE_URL, PHP_URL_SCHEME) === 'https'),
    'httponly' => true,
    'samesite' => 'Strict'
]);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// CSRF protection
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>