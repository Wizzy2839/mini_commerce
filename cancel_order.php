<?php
require_once __DIR__ . '/config/functions.php';
requireLogin('/auth/login.php');

$pdo = getDB();
$txId = (int)($_POST['id'] ?? 0);

if ($txId <= 0 || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /history.php');
    exit;
}

try {
    $pdo->beginTransaction();

    // Pastikan transaksi milik user ini dan masih berstatus pending
    $stmt = $pdo->prepare('SELECT id, status FROM transactions WHERE id = ? AND user_id = ? FOR UPDATE');
    $stmt->execute([$txId, $_SESSION['user_id']]);
    $tx = $stmt->fetch();

    if (!$tx) {
        throw new Exception('Pesanan tidak ditemukan.');
    }

    if ($tx['status'] !== 'pending') {
        throw new Exception('Pesanan tidak bisa dibatalkan karena statusnya sudah ' . $tx['status'] . '.');
    }

    // Ubah status jadi cancelled
    $updateStmt = $pdo->prepare('UPDATE transactions SET status = "cancelled" WHERE id = ?');
    $updateStmt->execute([$txId]);

    // Kembalikan stok produk
    $detailsStmt = $pdo->prepare('SELECT product_id, quantity FROM transaction_details WHERE transaction_id = ?');
    $detailsStmt->execute([$txId]);
    $details = $detailsStmt->fetchAll();

    $restoreStockStmt = $pdo->prepare('UPDATE products SET stock = stock + ? WHERE id = ?');
    foreach ($details as $detail) {
        $restoreStockStmt->execute([$detail['quantity'], $detail['product_id']]);
    }

    $pdo->commit();
    $_SESSION['flash'] = ['type' => 'success', 'message' => 'Pesanan berhasil dibatalkan.'];

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    $_SESSION['flash'] = ['type' => 'error', 'message' => $e->getMessage()];
}

header('Location: /history.php');
exit;
