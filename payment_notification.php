<?php
/**
 * Midtrans Payment Notification Webhook Handler
 * -----------------------------------------------
 * Midtrans POSTs a JSON payload here after every payment event.
 * Set this URL in Midtrans Dashboard → Settings → Payment Notification URL.
 * URL: https://your-domain.com/payment_notification.php
 *
 * Docs: https://docs.midtrans.com/reference/handling-notifications
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/midtrans.php';

// Only accept POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method Not Allowed');
}

// Read raw JSON body
$rawBody = file_get_contents('php://input');
$payload = json_decode($rawBody, true);

if (empty($payload) || !is_array($payload)) {
    http_response_code(400);
    exit('Invalid payload');
}

// Verify Midtrans signature to prevent spoofing
if (!verifyMidtransSignature($payload)) {
    http_response_code(403);
    exit('Invalid signature');
}

$midtransOrderId  = $payload['order_id']          ?? '';
$transactionStatus = $payload['transaction_status'] ?? '';
$fraudStatus      = $payload['fraud_status']       ?? '';

// Determine new internal status
$newStatus = null;
$paidAt    = null;

switch ($transactionStatus) {
    case 'capture':
        // capture = credit card authorized - check fraud
        if ($fraudStatus === 'accept') {
            $newStatus = 'processing';
            $paidAt    = date('Y-m-d H:i:s');
        }
        break;

    case 'settlement':
        // settlement = payment confirmed (VA, e-wallet, etc.)
        $newStatus = 'processing';
        $paidAt    = date('Y-m-d H:i:s');
        break;

    case 'pending':
        // Customer received instructions but hasn't paid yet — no change
        break;

    case 'deny':
    case 'cancel':
    case 'expire':
        $newStatus = 'cancelled';
        break;

    case 'refund':
    case 'partial_refund':
        $newStatus = 'cancelled';
        break;
}

if ($newStatus !== null) {
    $pdo = getDB();
    $stmt = $pdo->prepare(
        'UPDATE transactions
            SET status = ?,
                paid_at = ?
          WHERE midtrans_order_id = ?'
    );
    $stmt->execute([$newStatus, $paidAt, $midtransOrderId]);
}

// Always return 200 so Midtrans doesn't retry
http_response_code(200);
echo json_encode(['status' => 'OK']);
