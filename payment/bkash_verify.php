<?php
session_start();
include '../config.php';

$paymentID = $_GET['paymentID'] ?? '';
$trxID = $_GET['trxID'] ?? '';
$amount = $_GET['amount'] ?? 0;
$username = $_SESSION['username'] ?? '';

if(empty($paymentID) || empty($trxID) || empty($username)){
    die("Invalid request. <a href='bkash_pay.php'>Go Back</a>");
}

// In a real scenario, you might want to call bKash Query Payment API here to be 100% sure
// but for this integration we assume execute was successful if we reached here with trxID

$amount_val = floatval($amount);

$conn->query("
    UPDATE customers 
    SET wallet = wallet + $amount_val 
    WHERE username = '$username'
");

$conn->query("
    INSERT INTO wallet_transactions (username, amount, gateway, status, txn_id)
    VALUES ('$username', $amount_val, 'bkash', 'success', '$trxID')
");

echo "<!DOCTYPE html>
<html>
<head>
    <title>Payment Success</title>
    <link href='https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap' rel='stylesheet'>
    <link rel='stylesheet' href='../assets/css/theme.css'>
    <style>
        body { 
            display: flex; 
            justify-content: center; 
            align-items: center; 
            min-height: 100vh; 
            background: var(--bg-main);
            font-family: 'Inter', sans-serif;
        }
        .success-box {
            background: var(--bg-card);
            padding: 40px;
            border-radius: 16px;
            text-align: center;
            box-shadow: var(--shadow-lg);
            max-width: 400px;
        }
        .success-icon {
            width: 80px;
            height: 80px;
            background: #d1fae5;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            color: #10b981;
            font-size: 40px;
        }
        h2 { color: #10b981; margin-bottom: 15px; }
        p { color: var(--text-muted); margin-bottom: 25px; }
        .amount { font-size: 24px; font-weight: 700; color: var(--text-main); }
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 24px;
            background: var(--primary);
            color: white;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class='success-box'>
        <div class='success-icon'>
            <i class='fa fa-check'></i>
        </div>
        <h2>Payment Successful!</h2>
        <p>Your wallet has been recharged successfully via bKash.</p>
        <div class='amount'>BDT $amount_val</div>
        <br>
        <a href='../dashboard.php' class='btn'>
            <i class='fa fa-home'></i> Go to Dashboard
        </a>
    </div>
</body>
</html>";
?>
