<?php
// config.php - System Configuration & Database Connection (Cloud & Local Support)
ob_start();
session_start();

// Database credentials - Supports Environment Variables (for Cloud/Server) or XAMPP defaults
$db_host = getenv('DB_HOST') ?: (getenv('MYSQLHOST') ?: 'localhost');
$db_user = getenv('DB_USER') ?: (getenv('MYSQLUSER') ?: 'root');
$db_pass = getenv('DB_PASS') !== false ? getenv('DB_PASS') : (getenv('MYSQLPASSWORD') ?: '');
$db_name = getenv('DB_NAME') ?: (getenv('MYSQLDATABASE') ?: 'kruta_shop');
$db_port = getenv('DB_PORT') ?: (getenv('MYSQLPORT') ?: '3306');

try {
    $conn = new PDO("mysql:host=$db_host;port=$db_port;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
} catch (PDOException $e) {
    // If running on local XAMPP without database installed
    die("<div style='font-family:sans-serif; text-align:center; padding:50px;'>
            <h2 style='color:#e74c3c;'>⚠️ ยังไม่ได้เชื่อมต่อ MySQL หรือยังไม่ได้ติดตั้งฐานข้อมูล kruta_shop</h2>
            <p>กรุณาเปิด <a href='install.php' style='color:#3498db; font-weight:bold;'>คลิกที่นี่เพื่อติดตั้งฐานข้อมูลอัตโนมัติ (install.php)</a></p>
            <p style='color:#888; font-size:12px;'>Error: " . htmlspecialchars($e->getMessage()) . "</p>
         </div>");
}

// Ensure tables exist
try {
    $conn->exec("CREATE TABLE IF NOT EXISTS `settings` (
        `setting_key` VARCHAR(50) PRIMARY KEY,
        `setting_value` TEXT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    $conn->exec("CREATE TABLE IF NOT EXISTS `user_addresses` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `user_id` INT NOT NULL,
        `recipient_name` VARCHAR(150) NOT NULL,
        `phone` VARCHAR(30) NOT NULL,
        `address` TEXT NOT NULL,
        `is_default` TINYINT(1) DEFAULT 0,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    $conn->exec("CREATE TABLE IF NOT EXISTS `verified_slips` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `slip_hash` VARCHAR(64) NOT NULL UNIQUE,
        `order_id` INT NULL,
        `amount` DECIMAL(10,2) NOT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    // Default PromptPay settings
    $checkPromptPay = $conn->query("SELECT setting_value FROM settings WHERE setting_key = 'promptpay_number'")->fetch();
    if (!$checkPromptPay) {
        $conn->exec("INSERT INTO settings (setting_key, setting_value) VALUES 
            ('promptpay_number', '0899999999'),
            ('promptpay_name', 'ครูต๊ะ เครื่องเขียนเพื่อการเรียนรู้'),
            ('promptpay_type', 'mobile')
            ON DUPLICATE KEY UPDATE setting_key=setting_key;");
    }
} catch (Exception $e) {}

// Function to get dynamic setting
function get_setting($key, $default = '') {
    global $conn;
    try {
        $stmt = $conn->prepare("SELECT setting_value FROM settings WHERE setting_key = ?");
        $stmt->execute([$key]);
        $row = $stmt->fetch();
        return $row ? $row['setting_value'] : $default;
    } catch (Exception $e) {
        return $default;
    }
}

// App Settings
define('SITE_NAME', 'Kruta Shop (ครูต๊ะช็อป)');
define('SITE_TAGLINE', 'อาณาจักรเครื่องเขียนสุดคิ้วท์ เด้ง ดึ๋ง ปังสุดในสามโลก! ✨');

// Helper Functions
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

function is_admin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

function current_user() {
    if (!is_logged_in()) return null;
    return [
        'id' => $_SESSION['user_id'],
        'fullname' => $_SESSION['user_name'],
        'email' => $_SESSION['user_email'],
        'role' => $_SESSION['user_role'],
        'avatar' => $_SESSION['user_avatar'] ?? 'https://api.dicebear.com/7.x/bottts/svg?seed=Kruta'
    ];
}

function cart_count() {
    if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) return 0;
    return array_sum($_SESSION['cart']);
}

function format_price($price) {
    return number_format($price, 2) . ' ฿';
}

// Load PromptPay Helper & AI Slip Verifier
require_once __DIR__ . '/promptpay_helper.php';
require_once __DIR__ . '/slip_verifier.php';
