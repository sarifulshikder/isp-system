<?php
/**
 * VSOL OLT SNMP Driver
 * Specifically for V1600D and similar EPON OLTs
 */

class VSOL_SNMP {
    private $host;
    private $community;
    private $port;
    private $timeout = 2;
    private $retries = 1;

    public function __construct($host, $community = 'public', $port = 161) {
        $this->host = $host;
        $this->community = $community;
        $this->port = $port ?: 161;
    }

    private function snmpwalk($oid) {
        $target = $this->host . ':' . $this->port;
        $cmd = sprintf("snmpwalk -v 2c -c %s -t %d -r %d -Os %s %s 2>/dev/null", 
            escapeshellarg($this->community),
            $this->timeout,
            $this->retries,
            $target,
            $oid
        );
        $output = shell_exec($cmd);
        $results = [];
        if ($output) {
            $lines = explode("\n", $output);
            foreach ($lines as $line) {
                $line = trim($line);
                if (empty($line)) continue;
                if (preg_match('/^(\S+)\s*=\s*(.*)$/', $line, $matches)) {
                    $val = trim($matches[2]);
                    $val = trim($val, '"');
                    $val = str_replace(['STRING: ', 'INTEGER: ', 'Gauge32: '], '', $val);
                    $results[$matches[1]] = $val;
                }
            }
        }
        return $results;
    }

    public function getONTs() {
        // Get Interface Descriptions
        $ifDescrs = $this->snmpwalk('1.3.6.1.2.1.2.2.1.2');
        $ifStatuses = $this->snmpwalk('1.3.6.1.2.1.2.2.1.8');
        
        // Get Optical Power (VSOL Specific)
        $rxPowers = $this->snmpwalk('1.3.6.1.4.1.37950.1.1.5.12.2.1.8.1.7');
        
        $onts = [];
        foreach ($ifDescrs as $oid => $descr) {
            if (preg_match('/EPON(\d+)ONU(\d+)\s*(.*)/i', $descr, $m)) {
                $pon = intval($m[1]);
                $onu = intval($m[2]);
                $alias = trim($m[3]);
                $ifIndex = str_replace('iso.3.6.1.2.1.2.2.1.2.', '', $oid);
                
                $status = $ifStatuses['iso.3.6.1.2.1.2.2.1.8.' . $ifIndex] ?? '2';
                
                // Map to VSOL Optical Power index
                $vsolIndex = "iso.3.6.1.4.1.37950.1.1.5.12.2.1.8.1.7.$pon.$onu";
                $rawRx = $rxPowers[$vsolIndex] ?? 'N/A';
                
                // Parse RX power from string like "0.02 mW (-16.20 dBm)"
                $rx = 'N/A';
                if (preg_match('/\((.*)\s*dBm\)/i', $rawRx, $rm)) {
                    $rx = $rm[1];
                }

                $onts[] = [
                    'port' => "0/$pon",
                    'onu_serial' => "EPON0{$pon}ONU{$onu}",
                    'alias' => $alias,
                    'rx_power' => $rx,
                    'status' => ($status == '1') ? 'online' : 'offline',
                    'onu_type' => 'VSOL EPON'
                ];
            }
        }
        return $onts;
    }

    public function getStats() {
        $onts = $this->getONTs();
        $total = count($onts);
        $online = 0;
        $critical = 0;
        
        foreach ($onts as $ont) {
            if ($ont['status'] == 'online') {
                $online++;
                if ($ont['rx_power'] != 'N/A' && floatval($ont['rx_power']) < -27) {
                    $critical++;
                }
            }
        }
        
        return [
            'total' => $total,
            'online' => $online,
            'offline' => $total - $online,
            'critical' => $critical
        ];
    }
}
