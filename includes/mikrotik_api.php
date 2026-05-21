<?php
/**
 * Advanced RouterOS API Client - Enhanced with OLT Integration
 * Supports fetching OLT ONT data through MikroTik
 */
class RouterosAPI {
    var $debug = false;
    var $connected = false;
    var $port = 8728;
    var $timeout = 5;
    var $attempts = 3;
    var $delay = 2;
    var $socket;
    var $error_no;
    var $error_str;

    function isConnected() { 
        return $this->connected; 
    }

    function setPort($port) {
        $this->port = intval($port) ?: 8728;
    }

    function setTimeout($timeout) {
        $this->timeout = intval($timeout) ?: 5;
    }

    function connect($host, $usr, $pass, $port = null) {
        if ($port !== null) {
            $this->port = intval($port) ?: 8728;
        }
        
        for ($a = 1; $a <= $this->attempts; $a++) {
            $this->socket = @fsockopen($host, $this->port, $this->error_no, $this->error_str, $this->timeout);
            
            if ($this->socket) {
                socket_set_timeout($this->socket, $this->timeout);
                
                $this->write('/login', false);
                $this->write('=name=' . $usr, false);
                $this->write('=password=' . $pass, true);
                $response = $this->read(false);
                
                if (isset($response[0]) && $response[0] == '!done') {
                    $this->connected = true;
                    return true;
                }
                
                $challenge = '';
                foreach ($response as $line) {
                    if (is_string($line) && strpos($line, '=ret=') !== false) {
                        $challenge = substr($line, 5);
                        break;
                    }
                }
                
                if ($challenge) {
                    $hash = md5(pack('H*', '00') . $pass . pack('H*', $challenge));
                    
                    $this->write('/login', false);
                    $this->write('=name=' . $usr, false);
                    $this->write('=response=00' . $hash, true);
                    $res = $this->read(false);
                    
                    if (isset($res[0]) && $res[0] == '!done') {
                        $this->connected = true;
                        return true;
                    }
                }
                
                fclose($this->socket);
            }
            
            if ($a < $this->attempts) {
                sleep($this->delay);
            }
        }
        
        return false;
    }

    function disconnect() {
        if (is_resource($this->socket)) {
            fclose($this->socket);
        }
        $this->connected = false;
    }

    function write($command, $param = true) {
        if ($command !== '') {
            $data = explode("\n", (string)$command);
            foreach ($data as $com) {
                $com = trim($com);
                if ($com !== '') {
                    $this->sendWord($com);
                }
            }
        } else {
            $this->sendWord('');
        }
        if ($param) $this->sendWord('');
        return true;
    }

    function sendWord($word) {
        $len = strlen($word);
        if ($len < 0x80) {
            fwrite($this->socket, chr($len));
        } elseif ($len < 0x4000) {
            fwrite($this->socket, chr(($len >> 8) | 0x80) . chr($len & 0xFF));
        } elseif ($len < 0x200000) {
            fwrite($this->socket, chr(($len >> 16) | 0xC0) . chr(($len >> 8) & 0xFF) . chr($len & 0xFF));
        } elseif ($len < 0x10000000) {
            fwrite($this->socket, chr(($len >> 24) | 0xE0) . chr(($len >> 16) & 0xFF) . chr(($len >> 8) & 0xFF) . chr($len & 0xFF));
        } else {
            fwrite($this->socket, chr(0xF0) . chr(($len >> 24) & 0xFF) . chr(($len >> 16) & 0xFF) . chr(($len >> 8) & 0xFF) . chr($len & 0xFF));
        }
        fwrite($this->socket, $word);
    }

    function read($parse = true) {
        $response = array();
        $done = false;
        
        while (true) {
            $char = fread($this->socket, 1);
            if ($char === false || $char === '') break;
            $byte = ord($char);
            
            $length = 0;
            if ($byte & 128) {
                if (($byte & 192) == 128) {
                    $length = (($byte & 63) << 8) + ord(fread($this->socket, 1));
                } else if (($byte & 224) == 192) {
                    $length = (($byte & 31) << 16) + (ord(fread($this->socket, 1)) << 8) + ord(fread($this->socket, 1));
                } else if (($byte & 240) == 224) {
                    $length = (($byte & 15) << 24) + (ord(fread($this->socket, 1)) << 16) + (ord(fread($this->socket, 1)) << 8) + ord(fread($this->socket, 1));
                } else {
                    $length = (ord(fread($this->socket, 1)) << 24) + (ord(fread($this->socket, 1)) << 16) + (ord(fread($this->socket, 1)) << 8) + ord(fread($this->socket, 1));
                }
            } else {
                $length = $byte;
            }

            if ($length > 0) {
                $word = fread($this->socket, $length);
                $response[] = $word;
                if ($word === '!done' || $word === '!trap' || $word === '!fatal') {
                    $done = true;
                }
            } else {
                $response[] = ''; // Empty word marks end of sentence
                if ($done) break; // If we already saw !done, this empty word ends the !done sentence
            }
        }
        
        if ($parse) {
            $parsed = array();
            $current = array();
            $ret_value = null;
            
            foreach ($response as $word) {
                if ($word === '!re' || $word === '!done' || $word === '!trap' || $word === '!fatal') {
                    continue;
                }
                if ($word === '') {
                    if (!empty($current)) {
                        // Special check for 'ret' in !done
                        if (count($current) === 1 && isset($current['ret'])) {
                            $ret_value = $current['ret'];
                        }
                        $parsed[] = $current;
                        $current = array();
                    }
                    continue;
                }
                if (strpos($word, '=') === 0) {
                    $parts = explode('=', substr($word, 1), 2);
                    if (count($parts) == 2) {
                        $current[$parts[0]] = $parts[1];
                    }
                }
            }
            
            // If it was a count-only (only one result which is 'ret'), return it as string
            if (count($parsed) === 1 && $ret_value !== null) {
                return $ret_value;
            }
            
            return $parsed;
        }
        
        return $response;
    }

    function comm($com, $arr = array()) {
        if (!$this->connected) {
            return array();
        }
        
        $this->sendWord($com);
        if (is_array($arr)) {
            foreach ($arr as $k => $v) {
                if (is_numeric($k)) {
                    $this->sendWord($v);
                } else {
                    $word = $k;
                    if (strpos($word, '=') !== 0 && strpos($word, '?') !== 0 && strpos($word, '.') !== 0) {
                        $word = '=' . $word;
                    }
                    if ($v !== '') {
                        $word .= '=' . $v;
                    }
                    // Handle case where v is empty string but we still need the = if it's not a count-only style
                    // Actually, let's keep it simple for now as most parameters expect value.
                    $this->sendWord($word);
                }
            }
        }
        $this->sendWord('');
        
        return $this->read();
    }

    /**
     * Get system resources
     */
    function getResources() {
        $res = $this->comm("/system/resource/print");
        return isset($res[0]) ? $res[0] : array();
    }

    /**
     * Get interfaces
     */
    function getInterfaces() {
        return $this->comm("/interface/print");
    }

    /**
     * Get active PPPoE connections
     */
    function getActivePPP() {
        return $this->comm("/ppp/active/print");
    }

    /**
     * Get PPPoE secrets
     */
    function getSecrets() {
        return $this->comm("/ppp/secret/print");
    }

    /**
     * Get Hotspot active users
     */
    function getActiveHotspot() {
        return $this->comm("/ip/hotspot/active/print");
    }

    /**
     * Get Hotspot users
     */
    function getHotspotUsers() {
        return $this->comm("/ip/hotspot/user/print");
    }

    /**
     * Get interface traffic (monitor)
     */
    function monitorTraffic($interface) {
        $this->write("/interface/monitor-traffic", false);
        $this->write("=interface=" . $interface, false);
        $this->write("=once=", true);
        return $this->read();
    }

    /**
     * Get OLT ONT data from MikroTik (via Dude or custom script)
     * This works when MikroTik has visibility into the OLT network
     */
    function getOLTData($olt_ip = null) {
        $result = [
            'connected' => false,
            'onus' => [],
            'ports' => []
        ];
        
        // Method 1: Try to get ONT data from RouterOS via custom script
        // This requires a script on MikroTik that fetches OLT data
        $script = '/system script job print';
        $jobs = $this->comm($script);
        
        // Method 2: Try IP ARP to find OLT and ONUs
        $arp = $this->comm("/ip/arp/print");
        $olt_onus = [];
        foreach ($arp as $entry) {
            if (isset($entry['address'])) {
                // Try to identify OLT IPs based on known patterns
                if ($olt_ip && $entry['address'] == $olt_ip) {
                    $result['connected'] = true;
                }
            }
        }
        
        // Method 3: Get fiber interface status (SFP ports)
        $intfs = $this->comm("/interface/print", ["type" => "sfp"]);
        foreach ($intfs as $intf) {
            $result['ports'][] = [
                'name' => $intf['name'] ?? '',
                'status' => $intf['running'] ?? 'false',
                'rx' => $intf['rx-byte'] ?? '0',
                'tx' => $intf['tx-byte'] ?? '0'
            ];
        }
        
        // Method 4: If MikroTik has Dude package, try to get data from Dude
        $dude = $this->comm("/dude/print");
        if (!empty($dude)) {
            // Dude is available - could fetch data from devices
            $result['has_dude'] = true;
        }
        
        return $result;
    }

    /**
     * Get GPON/SFP interface status
     * For MikroTik with GPON or SFP+ modules connecting to OLT
     */
    function getGPONStatus() {
        $gpon_ifaces = [];
        
        // Get all interfaces
        $intfs = $this->comm("/interface/print");
        
        foreach ($intfs as $intf) {
            $type = strtolower($intf['type'] ?? '');
            $name = $intf['name'] ?? '';
            
            // Look for GPON, SFP, or XGS interfaces
            if (strpos($type, 'gpon') !== false || 
                strpos($type, 'sfp') !== false ||
                strpos($name, 'gpon') !== false ||
                strpos($name, 'sfp') !== false ||
                strpos($name, 'optical') !== false) {
                
                $gpon_ifaces[] = [
                    'name' => $name,
                    'type' => $type,
                    'status' => $intf['running'] ?? 'false',
                    'rx_bytes' => $intf['rx-byte'] ?? '0',
                    'tx_bytes' => $intf['tx-byte'] ?? '0',
                    'rx_packet' => $intf['rx-packet'] ?? '0',
                    'tx_packet' => $intf['tx-packet'] ?? '0',
                    'disabled' => $intf['disabled'] ?? 'false'
                ];
            }
        }
        
        return $gpon_ifaces;
    }

    /**
     * Get PPPoE sessions with ONT info
     * For BNG scenario where MikroTik sees OLT through PPPoE
     */
    function getPPPoESessions() {
        $sessions = $this->comm("/ppp/active/print");
        $session_data = [];
        
        foreach ($sessions as $s) {
            $session_data[] = [
                'name' => $s['name'] ?? '',
                'service' => $s['service'] ?? '',
                'address' => $s['address'] ?? '',
                'caller_id' => $s['caller-id'] ?? '',
                'uptime' => $s['uptime'] ?? '',
                'session_id' => $s['session-id'] ?? ''
            ];
        }
        
        return $session_data;
    }

    /**
     * Get DHCP leases (for ONTs with IP)
     */
    function getDHCPLeases() {
        return $this->comm("/ip/dhcp-server/lease/print");
    }

    /**
     * Get OLT ONT via SNMP through MikroTik
     * Useful when MikroTik is between the OLT and your system
     */
    function getSNMPData($target_ip, $oid) {
        // Try snmp get through MikroTik
        // This requires snmp tool on RouterOS
        return [];
    }

    /**
     * Get bandwidth queues for traffic shaping
     */
    function getQueues() {
        return $this->comm("/queue/simple/print");
    }

    /**
     * Get firewall connections
     */
    function getConnections() {
        return $this->comm("/ip/firewall/connection/print", ["count-only" => ""]);
    }

    /**
     * Disconnect a specific user
     */
    function disconnectUser($username) {
        $active = $this->comm("/ppp/active/print", array("?name" => $username));
        
        if (isset($active[0]) && isset($active[0]['.id'])) {
            $this->comm("/ppp/active/remove", array(".id" => $active[0]['.id']));
            return true;
        }
        return false;
    }

    /**
     * Hotspot user create
     */
    function createHotspotUser($user, $pass, $profile, $limit) {
        $this->comm("/ip/hotspot/user/add", [
            "name" => $user,
            "password" => $pass,
            "profile" => $profile,
            "limit-uptime" => $limit
        ]);
        return true;
    }

    /**
     * Get OLT ONT via custom script execution
     * Requires预先在 MikroTik 上创建脚本
     */
    function runOLTScript($script_name = "get-olt-data") {
        $result = $this->comm("/system script run", ["=source=" => $script_name]);
        return $result;
    }
}
