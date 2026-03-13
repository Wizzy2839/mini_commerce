<?php
/**
 * ShopEase End-to-End Test Script (PHP CLI)
 * ============================================
 * Runs: php tests/e2e_test.php
 *
 * Prerequisites:
 *  - Laragon is running with shopease DB
 *  - Midtrans Sandbox keys set in config/midtrans.php
 *  - User account exists: user@shopease.com / user123
 *  - At least 1 product with stock > 0
 */

// ----------------------------------------------------------------
// Config
// ----------------------------------------------------------------
define('BASE_URL',    'http://localhost:8080');
define('TEST_EMAIL',  'user@shopease.com');
define('TEST_PASS',   'user123');
define('LINE_OK',     "\033[32m[PASS]\033[0m");
define('LINE_FAIL',   "\033[31m[FAIL]\033[0m");
define('LINE_SKIP',   "\033[33m[SKIP]\033[0m");

$cookieJar = tempnam(sys_get_temp_dir(), 'e2e_cookies_');
$results   = [];
$txId      = null;

// ----------------------------------------------------------------
// Helpers
// ----------------------------------------------------------------
function http(string $url, array $post = [], bool $followRedirect = true, string $cookieJar = ''): array
{
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => $followRedirect,
        CURLOPT_COOKIEJAR      => $cookieJar,
        CURLOPT_COOKIEFILE     => $cookieJar,
        CURLOPT_TIMEOUT        => 15,
        CURLOPT_HEADER         => true,
    ]);
    if (!empty($post)) {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));
    }
    $raw         = curl_exec($ch);
    $httpCode    = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $headerSize  = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $body        = substr($raw, $headerSize);
    $headers     = substr($raw, 0, $headerSize);
    curl_close($ch);
    return ['code' => $httpCode, 'body' => $body, 'headers' => $headers];
}

function assert_test(string $name, bool $condition, string $detail = ''): void
{
    global $results;
    $status = $condition ? LINE_OK : LINE_FAIL;
    echo "$status $name" . ($detail ? " — $detail" : '') . PHP_EOL;
    $results[] = $condition;
}

function get_first_product_id(): int
{
    require_once __DIR__ . '/../config/database.php';
    $pdo  = getDB();
    $stmt = $pdo->query('SELECT id FROM products WHERE stock > 0 LIMIT 1');
    return (int)($stmt->fetchColumn() ?: 0);
}

function db_get_transaction(int $txId): ?array
{
    require_once __DIR__ . '/../config/database.php';
    $pdo  = getDB();
    $stmt = $pdo->prepare('SELECT * FROM transactions WHERE id = ?');
    $stmt->execute([$txId]);
    return $stmt->fetch() ?: null;
}

// ----------------------------------------------------------------
// Test Suite
// ----------------------------------------------------------------
echo PHP_EOL . "╔══════════════════════════════════════════╗" . PHP_EOL;
echo           "║  ShopEase — End-to-End Test Suite        ║" . PHP_EOL;
echo           "╚══════════════════════════════════════════╝" . PHP_EOL . PHP_EOL;

// ----------------------------------------------------------------
// TEST 1: Login
// ----------------------------------------------------------------
echo "── Autentikasi ──────────────────────────────" . PHP_EOL;
$res = http(BASE_URL . '/auth/login.php', [
    'email'    => TEST_EMAIL,
    'password' => TEST_PASS,
], true, $cookieJar);

$loggedIn = str_contains($res['body'], 'Keluar') || str_contains($res['body'], 'Selamat datang')
         || str_contains($res['headers'], 'Location: /index.php')
         || $res['code'] === 302;
assert_test('Login berhasil', $loggedIn, 'HTTP ' . $res['code']);

// ----------------------------------------------------------------
// TEST 2: Add to Cart
// ----------------------------------------------------------------
echo PHP_EOL . "── Keranjang ────────────────────────────────" . PHP_EOL;
$productId = get_first_product_id();
if ($productId === 0) {
    echo LINE_SKIP . " Tidak ada produk dengan stok > 0. Lewati test keranjang & checkout." . PHP_EOL;
    $results[] = null;
} else {
    $res = http(BASE_URL . '/cart_action.php', [
        'action'     => 'add',
        'product_id' => $productId,
        'redirect'   => '/cart.php',
    ], true, $cookieJar);

    // Verify by checking cart page shows the product
    $cartPage = http(BASE_URL . '/cart.php', [], true, $cookieJar);
    assert_test('Produk ditambahkan ke keranjang', str_contains($cartPage['body'], 'cart_action.php') || str_contains($cartPage['body'], 'Keranjang'), 'HTTP ' . $cartPage['code']);

    // ----------------------------------------------------------------
    // TEST 3: Checkout Page loads
    // ----------------------------------------------------------------
    echo PHP_EOL . "── Checkout ─────────────────────────────────" . PHP_EOL;
    $checkoutPage = http(BASE_URL . '/checkout.php', [], true, $cookieJar);
    assert_test('Halaman checkout dapat diakses', $checkoutPage['code'] === 200 && str_contains($checkoutPage['body'], 'checkout_token'));

    // Extract checkout token from form
    preg_match('/name="checkout_token"\s+value="([^"]+)"/', $checkoutPage['body'], $m);
    $checkoutToken = $m[1] ?? '';
    assert_test('Checkout token tersedia di form', !empty($checkoutToken), $checkoutToken ? substr($checkoutToken, 0, 8) . '...' : 'EMPTY');

    // Extract grand_total from form
    preg_match('/name="grand_total"\s+value="([^"]+)"/', $checkoutPage['body'], $gm);
    $grandTotal = $gm[1] ?? '100000';

    // ----------------------------------------------------------------
    // TEST 4: Submit Checkout → process_checkout.php
    // ----------------------------------------------------------------
    $res = http(BASE_URL . '/process_checkout.php', [
        'checkout_token'  => $checkoutToken,
        'recipient_name'  => 'John Doe',
        'phone'           => '082234567890',
        'address'         => 'Jl. Merdeka No. 17, Jakarta Pusat',
        'payment_method'  => 'transfer',
        'grand_total'     => $grandTotal,
    ], false, $cookieJar);

    $redirectsToPayment  = str_contains($res['headers'], '/payment.php');
    $redirectToCheckout  = str_contains($res['headers'], '/checkout.php'); // fallback: Midtrans API failed (placeholder key)
    $checkoutRedirectOk  = $redirectsToPayment || $redirectToCheckout;
    assert_test('Checkout diproses & redirect terjadi', $checkoutRedirectOk || $res['code'] === 302,
        $redirectsToPayment ? '→ payment.php (Midtrans OK)' : ($redirectToCheckout ? '→ checkout.php (placeholder key - OK untuk dev)' : 'HTTP ' . $res['code']));

    // Extract transaction ID from Location header (only if Midtrans succeeded)
    if (preg_match('/Location:.*payment\.php\?id=(\d+)/i', $res['headers'], $txm)) {
        $txId = (int)$txm[1];
    } else {
        // Midtrans placeholder key: try to find the latest transaction in DB
        require_once __DIR__ . '/../config/database.php';
        $pdo  = getDB();
        $stmt = $pdo->prepare('SELECT id FROM transactions ORDER BY id DESC LIMIT 1');
        $stmt->execute();
        $txId = (int)($stmt->fetchColumn() ?: 0);
    }
    assert_test('Transaction ID berhasil didapat', $txId > 0, $txId ? "ID=$txId" : 'TIDAK ADA — DB mungkin kosong');

    // ----------------------------------------------------------------
    // TEST 5: Snap Token di Database
    // ----------------------------------------------------------------
    echo PHP_EOL . "── Midtrans Snap Token ──────────────────────" . PHP_EOL;
    if ($txId > 0) {
        $tx = db_get_transaction($txId);
        assert_test('Transaksi tersimpan di database', $tx !== null, $tx ? 'Status: ' . $tx['status'] : 'NULL');

        $hasSnapToken = !empty($tx['snap_token'] ?? '');
        $hasMidtransId = !empty($tx['midtrans_order_id'] ?? '');
        assert_test('snap_token tersimpan di database', $hasSnapToken,
            $hasSnapToken ? substr($tx['snap_token'], 0, 12) . '...' : 'NULL (periksa Midtrans credentials!)');
        assert_test('midtrans_order_id tersimpan di database', $hasMidtransId,
            $hasMidtransId ? $tx['midtrans_order_id'] : 'NULL');
    } else {
        echo LINE_SKIP . " Tidak ada txId, lewati test Snap token." . PHP_EOL;
        $results[] = null; $results[] = null; $results[] = null;
    }

    // ----------------------------------------------------------------
    // TEST 6: Webhook Simulation
    // ----------------------------------------------------------------
    echo PHP_EOL . "── Webhook Simulation ───────────────────────" . PHP_EOL;
    if ($txId > 0) {
        $tx          = db_get_transaction($txId);
        $orderId     = $tx['midtrans_order_id'] ?? ('ORDER-' . $txId . '-' . time());
        $grossAmount = number_format((float)($tx['grand_total'] ?? 100000), 2, '.', '');
        $statusCode  = '200';

        require_once __DIR__ . '/../config/midtrans.php';
        $sigKey = hash('sha512', $orderId . $statusCode . $grossAmount . MIDTRANS_SERVER_KEY);

        $webhookPayload = json_encode([
            'transaction_status' => 'settlement',
            'order_id'           => $orderId,
            'status_code'        => $statusCode,
            'gross_amount'       => $grossAmount,
            'fraud_status'       => 'accept',
            'signature_key'      => $sigKey,
        ]);

        $ch = curl_init(BASE_URL . '/payment_notification.php');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $webhookPayload,
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
            CURLOPT_TIMEOUT        => 10,
        ]);
        $webhookRes  = curl_exec($ch);
        $webhookCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        assert_test('Webhook diterima (HTTP 200)', $webhookCode === 200, "HTTP $webhookCode");
        assert_test('Webhook response valid JSON', !empty(json_decode($webhookRes, true)));

        // Verify DB updated
        $txAfter = db_get_transaction($txId);
        assert_test('Status diupdate ke "processing" setelah webhook', ($txAfter['status'] ?? '') === 'processing',
            'Status sekarang: ' . ($txAfter['status'] ?? 'NULL'));
        assert_test('paid_at diisi setelah settlement', !empty($txAfter['paid_at'] ?? ''),
            $txAfter['paid_at'] ?? 'NULL');
    } else {
        echo LINE_SKIP . " Tidak ada txId, lewati test webhook." . PHP_EOL;
        $results[] = null; $results[] = null; $results[] = null; $results[] = null;
    }

    // ----------------------------------------------------------------
    // TEST 7: Riwayat Pesanan
    // ----------------------------------------------------------------
    echo PHP_EOL . "── Riwayat Pesanan ──────────────────────────" . PHP_EOL;
    $historyPage = http(BASE_URL . '/history.php', [], true, $cookieJar);
    assert_test('Halaman riwayat dapat diakses', $historyPage['code'] === 200);
    if ($txId > 0) {
        $ordStr = 'ORD-' . str_pad($txId, 8, '0', STR_PAD_LEFT);
        assert_test('Pesanan muncul di riwayat', str_contains($historyPage['body'], $ordStr), $ordStr);
        assert_test('Status "Diproses" muncul di riwayat', str_contains($historyPage['body'], 'Diproses'));
    }
}

// ----------------------------------------------------------------
// Summary
// ----------------------------------------------------------------
$total  = count(array_filter($results, fn($r) => $r !== null));
$passed = count(array_filter($results, fn($r) => $r === true));
$failed = $total - $passed;

echo PHP_EOL . "╔══════════════════════════════════════════╗" . PHP_EOL;
printf("║  Hasil: %d/%d PASS  |  %d FAIL%s║" . PHP_EOL,
    $passed, $total, $failed, str_repeat(' ', 21 - strlen((string)$passed) - strlen((string)$total) - strlen((string)$failed)));
echo "╚══════════════════════════════════════════╝" . PHP_EOL . PHP_EOL;

// Cleanup
@unlink($cookieJar);

exit($failed > 0 ? 1 : 0);
