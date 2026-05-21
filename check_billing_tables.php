<?php
include 'config.php';
$tables = ['payment_transactions', 'payment_gateways', 'billing_invoices', 'customer_subscriptions'];
foreach ($tables as $table) {
    $res = $conn->query("SHOW TABLES LIKE '$table'");
    if ($res->num_rows == 0) {
        echo "Table $table missing.
";
    } else {
        echo "Table $table exists.
";
    }
}
?>
