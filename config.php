<?php
// ============================================================
//  ISP Management System - Docker Config
// ============================================================

$db_host = getenv('DB_HOST') ?: 'db';
$db_user = getenv('DB_USER') ?: 'isp_user';
$db_pass = getenv('DB_PASS');

if (!$db_pass) {
    die("Error: Environment variable DB_PASS is not set.");
}

$db_name = getenv('DB_NAME') ?: 'isp_db';

$base_path = './';
$site_url  = 'http://localhost';

// Connection
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
if ($conn->connect_error) {
    die("<div style='font-family:sans-serif;padding:30px;background:#fff3cd;border:1px solid #ffc107;border-radius:8px;'>
         <h3 style='color:#856404;'>&#9888; Database Connection Failed</h3>
         <p><strong>Error:</strong> " . htmlspecialchars($conn->connect_error) . "</p>
         <p>DB Host: <code>$db_host</code> | DB Name: <code>$db_name</code></p>
         </div>");
}
$conn->set_charset('utf8mb4');

// Debugging Configuration
if (!defined('DEBUG_MODE')) {
    define('DEBUG_MODE', true); // SET TO FALSE IN PRODUCTION
}
if (DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}

if (!defined('APP_NAME')) {
    define('APP_NAME',    'ISP Management System');
    define('APP_VERSION', '2.0.0');
    define('SITE_URL',    $site_url);
    define('BASE_PATH',   $base_path);
}
?>
