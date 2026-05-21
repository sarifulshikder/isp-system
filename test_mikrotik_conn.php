<?php
$host = '10.5.50.1';
$port = 8728;
$timeout = 5;

echo "Testing connection to $host:$port...
";

$socket = @fsockopen($host, $port, $errno, $errstr, $timeout);

if ($socket) {
    echo "Successfully connected to $host:$port!
";
    fclose($socket);
} else {
    echo "Failed to connect to $host:$port. Error: $errno - $errstr
";
}
?>
