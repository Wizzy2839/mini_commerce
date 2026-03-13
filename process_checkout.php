<?php
require_once __DIR__ . '/config/functions.php';
require_once __DIR__ . '/config/midtrans.php';
requireLogin('/auth/login.php');

$pdo    = getDB();
$userId = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /checkout.php'); exit;
}

$recipientName  = trim($_POST['recipient_name'] ?? '');
$phone          = trim($_POST['phone'] ?? '');
$address        = trim($_POST['address'] ?? '');
$paymentMethod  = $_POST['payment_method'] ?? 'transfer';
$grandTotal     = (float)$_POST['grand_total'];
$token          = $_POST['checkout_token'] ?? '';

// Anti Double Order
if (empty($token) || $token !== ($_SESSION['checkout_token'] ?? '')) {
    $_SESSION['flash'] = ['type' => 'error', 'message' => 'Pesanan sudah diproses atau sesi kadaluarsa.'];
    header('Location: /cart.php'); exit;
}

// Backend Validation
if (empty($recipientName) || empty($phone) || empty($address)) {
    $_SESSION['flash'] = ['type' => 'error', 'message' => 'Semua data pengiriman wajib diisi.'];
    header('Location: /checkout.php'); exit;
}
if (strlen($recipientName) < 3 || !preg_match('/^[a-zA-Z\s]+$/', $recipientName)) {
    $_SESSION['flash'] = ['type' => 'error', 'message' => 'Format nama penerima tidak valid.'];
    header('Location: /checkout.php'); exit;
}
if (!preg_match('/^08[0-9]{8,11}$/', $phone)) {
    $_SESSION['flash'] = ['type' => 'error', 'message' => 'Format nomor telepon tidak valid.'];
    header('Location: /checkout.php'); exit;
}
if (strlen($address) < 10) {
    $_SESSION['flash'] = ['type' => 'error', 'message' => 'Alamat minimal 10 karakter.'];
    header('Location: /checkout.php'); exit;
}

// Fetch current cart items
$stmt = $pdo->prepare('SELECT ci.*, p.price, p.name, p.stock FROM cart_items ci JOIN products p ON p.id = ci.product_id WHERE ci.user_id = ?');
$stmt->execute([$userId]);
$cartItems = $stmt->fetchAll();

if (empty($cartItems)) {
    $_SESSION['flash'] = ['type' => 'error', 'message' => 'Keranjang kamu kosong!'];
    header('Location: /cart.php'); exit;
}

// Fetch user info for Midtrans
$user = getCurrentUser();
$nameParts = explode(' ', $user['name'] ?? 'User', 2);

try {
    $pdo->beginTransaction();

    // 1. Insert transaction header
    $insertTx = $pdo->prepare(
        'INSERT INTO transactions (user_id, grand_total, recipient_name, phone, address, payment_method, status)
         VALUES (?, ?, ?, ?, ?, ?, "pending")'
    );
    $insertTx->execute([$userId, $grandTotal, $recipientName, $phone, $address, $paymentMethod]);
    $transactionId = $pdo->lastInsertId();

    // 2. Insert detail rows + reduce stock
    $insertDetail = $pdo->prepare(
        'INSERT INTO transaction_details (transaction_id, product_id, quantity, price_at_purchase) VALUES (?, ?, ?, ?)'
    );
    $reduceStock = $pdo->prepare('UPDATE products SET stock = stock - ? WHERE id = ? AND stock >= ?');

    foreach ($cartItems as $item) {
        if ($item['stock'] < $item['quantity']) {
            throw new Exception('Stok produk "' . $item['name'] . '" tidak mencukupi. Silakan periksa keranjang Anda.');
        }
        $insertDetail->execute([$transactionId, $item['product_id'], $item['quantity'], $item['price']]);
        $reduceStock->execute([$item['quantity'], $item['product_id'], $item['quantity']]);
    }

    // 3. Generate Midtrans Snap token
    $midtransOrderId = 'ORDER-' . $transactionId . '-' . time();

    // Build item details for Midtrans
    $itemDetails = [];
    foreach ($cartItems as $item) {
        $itemDetails[] = [
            'id'       => 'PROD-' . $item['product_id'],
            'price'    => (int) round($item['price']),
            'quantity' => (int) $item['quantity'],
            'name'     => mb_substr($item['name'], 0, 50),
        ];
    }
    // Add shipping, fee, PPN as line items (required to match gross_amount)
    $subtotal = array_sum(array_map(fn($i) => $i['price'] * $i['quantity'], $cartItems));
    $shipping  = 25000;
    $fee       = 2500;
    $ppn       = (int) round($subtotal * 0.11);

    $itemDetails[] = ['id' => 'SHIPPING', 'price' => $shipping, 'quantity' => 1, 'name' => 'Ongkos Kirim'];
    $itemDetails[] = ['id' => 'FEE',      'price' => $fee,      'quantity' => 1, 'name' => 'Biaya Layanan'];
    $itemDetails[] = ['id' => 'PPN',      'price' => $ppn,      'quantity' => 1, 'name' => 'PPN (11%)'];

    $snapParams = [
        'transaction_details' => [
            'order_id'    => $midtransOrderId,
            'gross_amount'=> (int) round($grandTotal),
        ],
        'item_details'     => $itemDetails,
        'customer_details' => [
            'first_name' => $nameParts[0],
            'last_name'  => $nameParts[1] ?? '',
            'email'      => $user['email'] ?? '',
            'phone'      => $phone,
            'shipping_address' => [
                'first_name' => $nameParts[0],
                'last_name'  => $nameParts[1] ?? '',
                'phone'      => $phone,
                'address'    => $address,
                'country_code' => 'IDN',
            ],
        ],
        // Dihapus 'enabled_payments' agar Midtrans menampilkan semua metode
        // yang aktif di pengaturan Dashboard Midtrans Anda.
        'callbacks' => [
            'finish' => (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/order-success.php?id=' . $transactionId,
        ],
    ];

    $snapResult      = getMidtransSnapToken($snapParams);
    $snapToken       = $snapResult['snap_token'];

    // 4. Save Midtrans data to transaction
    $pdo->prepare('UPDATE transactions SET midtrans_order_id = ?, snap_token = ? WHERE id = ?')
        ->execute([$midtransOrderId, $snapToken, $transactionId]);

    // 5. Clear cart & anti-double-order token
    $pdo->prepare('DELETE FROM cart_items WHERE user_id = ?')->execute([$userId]);
    unset($_SESSION['checkout_token']);

    $pdo->commit();

    // Redirect to payment page
    header('Location: /payment.php?id=' . $transactionId);
    exit;

} catch (Exception $e) {
    $pdo->rollBack();
    $_SESSION['flash'] = ['type' => 'error', 'message' => 'Terjadi kesalahan: ' . $e->getMessage()];
    header('Location: /checkout.php');
    exit;
}
