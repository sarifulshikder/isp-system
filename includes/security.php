<?php
class Security {
    private $conn;
    private $maxAttempts = 5;
    private $lockoutDuration = 15; // minutes
    
    public function __construct($db) {
        $this->conn = $db;
        $this->loadSettings();
    }
    
    private function loadSettings() {
        $result = $this->conn->query("SELECT setting_key, setting_value FROM system_settings");
        while ($row = $result->fetch_assoc()) {
            switch ($row['setting_key']) {
                case 'max_login_attempts':
                    $this->maxAttempts = (int)$row['setting_value'];
                    break;
                case 'lockout_duration':
                    $this->lockoutDuration = (int)$row['setting_value'];
                    break;
            }
        }
    }
    
    public function getClientIP() {
        // In Docker environment without a trusted proxy, REMOTE_ADDR is most reliable.
        // We only trust X-Forwarded-For if we know we are behind a trusted proxy.
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }
    
    public function getUserAgent() {
        return $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
    }
    
    public function isLockedOut($username) {
        $stmt = $this->conn->prepare("
            SELECT COUNT(*) as attempts 
            FROM login_attempts 
            WHERE username = ? 
            AND attempted_at > DATE_SUB(NOW(), INTERVAL ? MINUTE)
            AND status = 'blocked'
        ");
        $stmt->bind_param("si", $username, $this->lockoutDuration);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        
        return $result['attempts'] > 0;
    }
    
    public function getFailedAttempts($username) {
        $stmt = $this->conn->prepare("
            SELECT COUNT(*) as attempts 
            FROM login_attempts 
            WHERE username = ? 
            AND attempted_at > DATE_SUB(NOW(), INTERVAL ? MINUTE)
            AND status = 'failed'
        ");
        $stmt->bind_param("si", $username, $this->lockoutDuration);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        
        return $result['attempts'];
    }
    
    public function recordLoginAttempt($username, $status) {
        $ip = $this->getClientIP();
        $userAgent = $this->getUserAgent();
        
        $statusValue = ($status === true) ? 'success' : 'failed';
        
        // Check if we need to block
        if ($status === false) {
            $attempts = $this->getFailedAttempts($username);
            if ($attempts >= $this->maxAttempts - 1) {
                $statusValue = 'blocked';
            }
        }
        
        $stmt = $this->conn->prepare("
            INSERT INTO login_attempts (username, ip, user_agent, status)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->bind_param("ssss", $username, $ip, $userAgent, $statusValue);
        $stmt->execute();
        
        return $statusValue === 'blocked';
    }
    
    public function logActivity($adminId, $username, $action, $description = '') {
        $ip = $this->getClientIP();
        
        $stmt = $this->conn->prepare("
            INSERT INTO activity_log (admin_id, action, description, ip)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->bind_param("isss", $adminId, $action, $description, $ip);
        $stmt->execute();
    }
    
    public function getLoginHistory($username, $limit = 10) {
        $stmt = $this->conn->prepare("
            SELECT * FROM login_attempts 
            WHERE username = ?
            ORDER BY attempted_at DESC
            LIMIT ?
        ");
        $stmt->bind_param("si", $username, $limit);
        $stmt->execute();
        return $stmt->get_result();
    }
    
    public function getActivityLog($adminId = null, $limit = 50) {
        if ($adminId) {
            $stmt = $this->conn->prepare("
                SELECT * FROM activity_log 
                WHERE admin_id = ?
                ORDER BY created_at DESC
                LIMIT ?
            ");
            $stmt->bind_param("ii", $adminId, $limit);
            $stmt->execute();
            return $stmt->get_result();
        } else {
            return $this->conn->query("
                SELECT * FROM activity_log 
                ORDER BY created_at DESC
                LIMIT $limit
            ");
        }
    }
    
    public function clearOldAttempts($days = 30) {
        $this->conn->query("
            DELETE FROM login_attempts 
            WHERE attempted_at < DATE_SUB(NOW(), INTERVAL $days DAY)
        ");
    }
    
    public function getSetting($key, $default = '') {
        $result = $this->conn->query("
            SELECT setting_value FROM system_settings 
            WHERE setting_key = '$key'
        ");
        $row = $result->fetch_assoc();
        return $row ? $row['setting_value'] : $default;
    }
    
    public function updateSetting($key, $value) {
        $stmt = $this->conn->prepare("
            INSERT INTO system_settings (setting_key, setting_value)
            VALUES (?, ?)
            ON DUPLICATE KEY UPDATE setting_value = ?
        ");
        $stmt->bind_param("sss", $key, $value, $value);
        return $stmt->execute();
    }
}
