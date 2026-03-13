<?php
// =============================================================
// Midtrans Payment Gateway Configuration
// Docs: https://docs.midtrans.com/reference/snap-api
// =============================================================

define('MIDTRANS_SERVER_KEY', getenv('MIDTRANS_SERVER_KEY'));
define('MIDTRANS_CLIENT_KEY', getenv('MIDTRANS_CLIENT_KEY'));
define('MIDTRANS_IS_PRODUCTION', false); // Tetap false agar menembak URL Sandbox kuning itu

define('MIDTRANS_SNAP_URL', MIDTRANS_IS_PRODUCTION
    ? 'https://app.midtrans.com/snap/v1/transactions'
    : 'https://app.sandbox.midtrans.com/snap/v1/transactions'); // Paksa ke Sandbox URL

define('MIDTRANS_SNAP_JS', MIDTRANS_IS_PRODUCTION
    ? 'https://app.midtrans.com/snap/snap.js'
    : 'https://app.sandbox.midtrans.com/snap/snap.js');

/**
 * Create a Midtrans Snap Token by calling the Snap API.
 *
 * @param array $params  Must contain: order_id, gross_amount, customer_details, item_details
 * @return array         ['snap_token' => string, 'redirect_url' => string]
 * @throws RuntimeException on API error
 */
function getMidtransSnapToken(array $params): array
{
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
        ],
        CURLOPT_TIMEOUT        => 30,
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlErr  = curl_error($ch);
    curl_close($ch);

    if ($curlErr) {
        throw new RuntimeException('Midtrans cURL error: ' . $curlErr);
    }

    $data = json_decode($response, true);

    if ($httpCode !== 201 || empty($data['token'])) {
        $errMsg = $data['error_messages'][0] ?? $data['message'] ?? 'Unknown Midtrans error';
        throw new RuntimeException('Midtrans API error (' . $httpCode . '): ' . $errMsg);
    }

    return [
        'snap_token'   => $data['token'],
        'redirect_url' => $data['redirect_url'] ?? '',
    ];
}

/**
 * Verify Midtrans webhook notification signature.
 * Formula: SHA512(order_id + status_code + gross_amount + server_key)
 *
 * @param array $payload  Decoded JSON from Midtrans
 * @return bool
 */
function verifyMidtransSignature(array $payload): bool
{
    $expected = hash('sha512',
        ($payload['order_id']     ?? '') .
        ($payload['status_code']  ?? '') .
        ($payload['gross_amount'] ?? '') .
        MIDTRANS_SERVER_KEY
    );
    return hash_equals($expected, $payload['signature_key'] ?? '');
}
