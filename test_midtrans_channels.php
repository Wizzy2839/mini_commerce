<?php
require 'd:/mini_commerce/config/midtrans.php';

echo "Using Server Key: " . substr(MIDTRANS_SERVER_KEY, 0, 10) . "...\n";
echo "Production Mode: " . (MIDTRANS_IS_PRODUCTION ? "TRUE" : "FALSE") . "\n";
echo "Snap URL: " . MIDTRANS_SNAP_URL . "\n\n";

$params = [
    'transaction_details' => [
        'order_id' => 'DEBUG-' . time(),
        'gross_amount' => 10000
    ]
];

$payload = json_encode($params);
$ch = curl_init(MIDTRANS_SNAP_URL);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => $payload,
    CURLOPT_HTTPHEADER     => [
        'Content-Type: application/json',
        'Accept: application/json',
        'Authorization: Basic ' . base64_encode(MIDTRANS_SERVER_KEY . ':'),
    ]
]);
$res = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Code: $httpCode\n";
echo "Response:\n";
print_r(json_decode($res, true) ?: $res);
