<?php
include 'config.php';
include 'config/genieacs.php';
header('Content-Type: application/json');

// 1. DB Stats
$total   = $conn->query("SELECT COUNT(*) as c FROM customers")->fetch_assoc()['c'];
$active  = $conn->query("SELECT COUNT(*) as c FROM customers WHERE status='active'")->fetch_assoc()['c'];
$expired = $conn->query("SELECT COUNT(*) as c FROM customers WHERE status='expired'")->fetch_assoc()['c'];
$tickets = $conn->query("SELECT COUNT(*) as c FROM tickets WHERE status='open'")->fetch_assoc()['c'];
$revenue = $conn->query("SELECT COALESCE(SUM(amount),0) as r FROM recharge WHERE MONTH(created_at)=MONTH(NOW()) AND YEAR(created_at)=YEAR(NOW())")->fetch_assoc()['r'];

// 2. DB Status
$db_version  = $conn->query("SELECT VERSION() as v")->fetch_assoc()['v'];
$db_uptime_s = $conn->query("SHOW STATUS LIKE 'Uptime'")->fetch_assoc()['Value'];
$db_uptime   = round($db_uptime_s / 3600, 1);

// 3. RADIUS — Docker DNS check only (no TCP block)
$radius_ip     = gethostbyname('isp_radius');
$radius_online = ($radius_ip !== 'isp_radius'); // resolve হলে container আছে
$rad_auth = $conn->query("SELECT COUNT(*) as c FROM radpostauth WHERE reply='Access-Accept' AND authdate >= CURDATE()")->fetch_assoc()['c'] ?? 0;
$rad_fail = $conn->query("SELECT COUNT(*) as c FROM radpostauth WHERE reply='Access-Reject' AND authdate >= CURDATE()")->fetch_assoc()['c'] ?? 0;

// 4. GenieACS — 1 second timeout only
$genieacs_online  = false;
$genieacs_latency = 'N/A';
$ch = curl_init(GENIEACS_URL . '/devices/?limit=1');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT        => 1,
    CURLOPT_CONNECTTIMEOUT => 1,
    CURLOPT_NOBODY         => true,
]);
$t1      = microtime(true);
curl_exec($ch);
$http    = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$elapsed = round((microtime(true) - $t1) * 1000);
curl_close($ch);
if ($http >= 200 && $http < 400) {
    $genieacs_online  = true;
    $genieacs_latency = $elapsed . 'ms';
}

// 5. System Health
$load = sys_getloadavg();
$cpu  = round($load[0], 2);
$mem  = 0;
if (file_exists('/proc/meminfo')) {
    $meminfo = file_get_contents('/proc/meminfo');
    preg_match('/MemTotal:\s+(\d+)/', $meminfo, $mt);
    preg_match('/MemAvailable:\s+(\d+)/', $meminfo, $ma);
    if ($mt && $ma) $mem = round((1 - $ma[1] / $mt[1]) * 100, 1);
}

echo json_encode([
    'customers' => ['total' => $total, 'active' => $active, 'expired' => $expired],
    'tickets'   => ['open' => $tickets],
    'revenue'   => ['monthly' => $revenue],
    'radius'    => [
        'process'      => $radius_online ? 'Running' : 'Stopped',
        'auth_success' => $rad_auth,
        'auth_fail'    => $rad_fail,
    ],
    'database'  => [
        'status'  => 'Online',
        'uptime'  => $db_uptime . ' hours',
        'version' => $db_version,
    ],
    'genieacs'  => [
        'status'  => $genieacs_online ? 'Online' : 'Offline',
        'tasks'   => 0,
        'latency' => $genieacs_latency,
    ],
    'system'    => ['cpu' => $cpu, 'ram' => $mem],
]);
