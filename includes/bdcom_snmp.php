<?php
/**
 * BDCOM OLT SNMP Driver
 * Specifically for P3310C and similar EPON OLTs
 */

class BDCOM_SNMP {
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
        // Build host:port string
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
                    $val = str_replace(['STRING: ', 'INTEGER: ', 'Gauge32: '], '', $val);
                    $val = trim($val, '"'); // Remove quotes after stripping prefix
                    $results[$matches[1]] = $val;
                }
            }
        }
        return $results;
    }

    public function getONTs() {
        // Get Interface Descriptions
        $ifDescrs = $this->snmpwalk('1.3.6.1.2.1.2.2.1.2');
        $ifAliases = $this->snmpwalk('1.3.6.1.2.1.31.1.1.1.18');
        $ifStatuses = $this->snmpwalk('1.3.6.1.2.1.2.2.1.8');
        
        // BDCOM P3310C RX Power OID (1/10th dBm)
        $rxPowers = $this->snmpwalk('1.3.6.1.4.1.3320.101.10.5.1.5');
        
        $onts = [];
        foreach ($ifDescrs as $oid => $descr) {
            // Match BDCOM Style: EPON0/1:3
            if (preg_match('/EPON(\d+)\/(\d+):(\d+)/i', $descr, $m)) {
                
                $ifIndex = str_replace('iso.3.6.1.2.1.2.2.1.2.', '', $oid);
                $status = $ifStatuses['iso.3.6.1.2.1.2.2.1.8.' . $ifIndex] ?? '2';
                $alias = $ifAliases['iso.3.6.1.2.1.31.1.1.1.18.' . $ifIndex] ?? '';
                
                $rawRx = $rxPowers['iso.3.6.1.4.1.3320.101.10.5.1.5.' . $ifIndex] ?? 'N/A';
                $rx = 'N/A';
                if ($rawRx !== 'N/A') {
                    $rx = number_format($rawRx / 10, 2);
                }

                $onts[] = [
                    'port' => "0/" . $m[2],
                    'onu_serial' => $descr,
                    'alias' => $alias ?: "ONU " . $m[3],
                    'rx_power' => $rx,
                    'status' => ($status == '1') ? 'online' : 'offline',
                    'onu_type' => 'BDCOM EPON'
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
