<?php
session_start();
include '../config/bkash_config.php';

$amount = $_GET['amount'];
$callbackURL = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]/payment/bkash_execute.php";

$requestbody = array(
    'initial_amount' => $amount,
    'amount' => $amount,
    'currency' => 'BDT',
    'intent' => 'sale',
    'merchantInvoiceNumber' => 'Inv' . date('YmdHis'),
    'callbackURL' => $callbackURL
);

$url = BKASH_BASE_URL . "/create";
$requestbodyjson = json_encode($requestbody);

$header = array(
    'Content-Type:application/json',
    'Authorization:' . $_SESSION['bkash_token'],
    "X-APP-Key:" . BKASH_APP_KEY
);

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $requestbodyjson);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
$resultdata = curl_exec($ch);
curl_close($ch);

echo $resultdata;
?>
