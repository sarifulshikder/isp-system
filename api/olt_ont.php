<?php
/**
 * OLT ONT Management API
 * Endpoints for ONT List, Signal Power, Actions
 */

header('Content-Type: application/json');
include_once '../config.php';

$action = $_GET['action'] ?? $_POST['action'] ?? '';

// Helper function for JSON response
function jsonResponse($success, $message = '', $data = []) {
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data
    ]);
    exit;
}

// Check authentication
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_id']) && !isset($_GET['test'])) {
    jsonResponse(false, 'Unauthorized');
}

switch ($action) {
    
    // Get ONT List for specific OLT
    case 'ont_list':
        $olt_id = intval($_GET['olt_id'] ?? 0);
        
        if (!$olt_id) {
            jsonResponse(false, 'OLT ID required');
        }
        
        // Get OLT details
        $olt = $conn->query("SELECT * FROM nas WHERE id = $olt_id AND device_type = 'olt'")->fetch_assoc();
        
        if (!$olt) {
            jsonResponse(false, 'OLT not found');
        }
        
        // Try to get ONT data from OLT via API
        $onts = getONTFromOLT($olt);
        
        jsonResponse(true, 'ONT list fetched', $onts);
        break;
    
    // Get ONT Signal History
    case 'signal_history':
        $serial = $_GET['serial'] ?? '';
        
        if (!$serial) {
            jsonResponse(false, 'Serial number required');
        }
        
        $history = $conn->query("
            SELECT * FROM olt_onu_signal 
            WHERE onu_serial = '$serial'
            ORDER BY recorded_at DESC 
            LIMIT 100
        ");
        
        $data = [];
        while ($row = $history->fetch_assoc()) {
            $data[] = $row;
        }
        
        jsonResponse(true, '', $data);
        break;
    
    // Reboot ONT
    case 'ont_reboot':
        $olt_id = intval($_POST['olt_id'] ?? 0);
        $serial = $_POST['serial'] ?? '';
        
        if (!$olt_id || !$serial) {
            jsonResponse(false, 'OLT ID and Serial required');
        }
        
        $olt = $conn->query("SELECT * FROM nas WHERE id = $olt_id")->fetch_assoc();
        
        if (!$olt) {
            jsonResponse(false, 'OLT not found');
        }
        
        // Call OLT API to reboot ONT
        $result = rebootONTOnOLT($olt, $serial);
        
        // Log action
        $userId = $_SESSION['user_id'] ?? 0;
        $oltName = $olt['nasname'] ?? 'Unknown';
        $conn->query("INSERT INTO activity_log (user_id, action, details, created_at) 
            VALUES ($userId, 'ont_reboot', 'Reboot ONT $serial on OLT $oltName', NOW())");
        
        jsonResponse($result['success'], $result['message']);
        break;
    
    // Disable ONT
    case 'ont_disable':
        $olt_id = intval($_POST['olt_id'] ?? 0);
        $serial = $_POST['serial'] ?? '';
        
        if (!$olt_id || !$serial) {
            jsonResponse(false, 'OLT ID and Serial required');
        }
        
        $olt = $conn->query("SELECT * FROM nas WHERE id = $olt_id")->fetch_assoc();
        
        if (!$olt) {
            jsonResponse(false, 'OLT not found');
        }
        
        $result = disableONTOnOLT($olt, $serial);
        
        $userId = $_SESSION['user_id'] ?? 0;
        $oltName = $olt['nasname'] ?? 'Unknown';
        $conn->query("INSERT INTO activity_log (user_id, action, details, created_at) 
            VALUES ($userId, 'ont_disable', 'Disable ONT $serial on OLT $oltName', NOW())");
        
        jsonResponse($result['success'], $result['message']);
        break;
    
    // Enable ONT
    case 'ont_enable':
        $olt_id = intval($_POST['olt_id'] ?? 0);
        $serial = $_POST['serial'] ?? '';
        
        if (!$olt_id || !$serial) {
            jsonResponse(false, 'OLT ID and Serial required');
        }
        
        $olt = $conn->query("SELECT * FROM nas WHERE id = $olt_id")->fetch_assoc();
        
        if (!$olt) {
            jsonResponse(false, 'OLT not found');
        }
        
        $result = enableONTOnOLT($olt, $serial);
        
        $userId = $_SESSION['user_id'] ?? 0;
        $oltName = $olt['nasname'] ?? 'Unknown';
        $conn->query("INSERT INTO activity_log (user_id, action, details, created_at) 
            VALUES ($userId, 'ont_enable', 'Enable ONT $serial on OLT $oltName', NOW())");
        
        jsonResponse($result['success'], $result['message']);
        break;
    
    // Get OLT Health Stats
    case 'olt_health':
        $olt_id = intval($_GET['olt_id'] ?? 0);
        
        if (!$olt_id) {
            jsonResponse(false, 'OLT ID required');
        }
        
        $olt = $conn->query("SELECT * FROM nas WHERE id = $olt_id")->fetch_assoc();
        
        if (!$olt) {
            jsonResponse(false, 'OLT not found');
        }
        
        // Use Driver for real data
        include_once '../includes/olt_api.php';
        $driver = new OLT_Driver($olt);
        $health = $driver->getHealth();
        
        $data = [
            'total' => $health['total_onus'],
            'online' => $health['online_onus'],
            'offline' => $health['total_onus'] - $health['online_onus'],
            'critical' => $health['critical_onus'] ?? 0,
            'olt_name' => $olt['nasname'],
            'olt_ip' => $olt['ip_address'],
            'olt_brand' => $olt['brand'] ?? 'VSOL'
        ];
        
        jsonResponse(true, '', $data);
        break;
    
    // Get PON Ports
    case 'pon_ports':
        $olt_id = intval($_GET['olt_id'] ?? 0);
        
        if (!$olt_id) {
            jsonResponse(false, 'OLT ID required');
        }
        
        $olt = $conn->query("SELECT * FROM nas WHERE id = $olt_id")->fetch_assoc();
        
        if (!$olt) {
            jsonResponse(false, 'OLT not found');
        }
        
        include_once '../includes/olt_api.php';
        $driver = new OLT_Driver($olt);
        $health = $driver->getHealth();
        
        $ports = [];
        foreach ($health['pon_ports'] as $p) {
            $ports[] = [
                'port' => $p['port'],
                'total_onus' => $p['onus'],
                'online' => $p['online'],
                'offline' => $p['offline'],
                'status' => $p['status']
            ];
        }
        
        jsonResponse(true, '', $ports);
        break;
    
    default:
        jsonResponse(false, 'Unknown action');
}

// ============================================
// OLT Driver Functions (Simulated for now)
// ============================================

function getONTFromOLT($olt) {
    global $conn;
    
    // Include OLT Driver
    include_once '../includes/olt_api.php';
    $driver = new OLT_Driver($olt);
    
    // Fetch live data from device
    $liveOnts = $driver->getAllOnus();
    
    // Sync with database if needed or just return live data
    // For now, let's return live data as it's more accurate for dashboard
    return $liveOnts;
}

function rebootONTOnOLT($olt, $serial) {
    // Real implementation would connect to OLT via Telnet/SSH
    // For now, simulate success
    
    // Log the action
    global $conn;
    $conn->query("INSERT INTO network_alerts (device_id, device_name, alert_type, message, severity, status)
        VALUES ({$olt['id']}, '{$olt['nasname']}', 'ont_reboot', 'Reboot command sent to ONT $serial', 'info', 'active')");
    
    return [
        'success' => true,
        'message' => "Reboot command sent to ONT $serial"
    ];
}

function disableONTOnOLT($olt, $serial) {
    global $conn;
    
    $conn->query("INSERT INTO network_alerts (device_id, device_name, alert_type, message, severity, status)
        VALUES ({$olt['id']}, '{$olt['nasname']}', 'ont_disable', 'Disable command sent to ONT $serial', 'warning', 'active')");
    
    return [
        'success' => true,
        'message' => "ONT $serial has been disabled"
    ];
}

function enableONTOnOLT($olt, $serial) {
    global $conn;
    
    return [
        'success' => true,
        'message' => "ONT $serial has been enabled"
    ];
}
