<?php
$db_host = getenv('DB_HOST') ?: 'db';
$db_user = getenv('DB_USER') ?: 'isp_user';
$db_pass = getenv('DB_PASS') ?: 'isp_pass123';
$db_name = getenv('DB_NAME') ?: 'isp_db';

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
if ($conn->connect_error) {
    die("Database Connection Failed: " . $conn->connect_error);
}
$conn->set_charset('utf8mb4');
?>

