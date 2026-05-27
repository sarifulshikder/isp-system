<?php
session_start();
include '../config/bkash_config.php';

$request_data = array(
    'app_key' => BKASH_APP_KEY,
    'app_secret' => BKASH_APP_SECRET
);

$url = BKASH_BASE_URL . "/token/grant";
$request_data_json = json_encode($request_data);

$header = array(
    'Content-Type:application/json',
    "username:" . BKASH_USERNAME,
    "password:" . BKASH_PASSWORD
);

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $request_data_json);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
$resultdata = curl_exec($ch);
curl_close($ch);

$response = json_decode($resultdata, true);

if (isset($response['id_token'])) {
    $_SESSION['bkash_token'] = $response['id_token'];
}

echo $resultdata;
?>
