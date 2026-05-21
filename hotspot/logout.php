<?php
session_start();

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/includes/auth.php';

$auth = new HotspotAuth();
$auth->logout();

header('Location: index.php');
exit;
?>
