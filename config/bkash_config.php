<?php
// bKash Payment Gateway Configuration

function get_bkash_config() {
    global $conn;
    $res = $conn->query("SELECT config FROM payment_gateways WHERE name = 'bkash' LIMIT 1");
    if ($res && $row = $res->fetch_assoc()) {
        $config = json_decode($row['config'] ?? '{}', true);
        return [
            'app_key' => $config['api_key'] ?? '',
            'app_secret' => $config['api_secret'] ?? '',
            'username' => $config['merchant_id'] ?? '',
            'password' => $config['password'] ?? '',
            'is_test_mode' => $config['is_test_mode'] ?? 0
        ];
    }
    return [
        'app_key' => '',
        'app_secret' => '',
        'username' => '',
        'password' => '',
        'is_test_mode' => 1
    ];
}

$bkash_config = get_bkash_config();

define('BKASH_APP_KEY', $bkash_config['app_key']);
define('BKASH_APP_SECRET', $bkash_config['app_secret']);
define('BKASH_USERNAME', $bkash_config['username']);
define('BKASH_PASSWORD', $bkash_config['password']);

if ($bkash_config['is_test_mode']) {
    define('BKASH_BASE_URL', 'https://tokenized.sandbox.bka.sh/v1.2.0-beta/tokenized/checkout');
} else {
    define('BKASH_BASE_URL', 'https://tokenized.pay.bka.sh/v1.2.0-beta/tokenized/checkout');
}
?>
