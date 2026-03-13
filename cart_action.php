<?php
require_once __DIR__ . '/config/functions.php';
requireLogin('/auth/login.php');

$pdo       = getDB();
$userId    = $_SESSION['user_id'];
$action    = $_POST['action'] ?? '';
$productId = (int)($_POST['product_id'] ?? 0);
$qty       = max(1, (int)($_POST['quantity'] ?? 1));
$redirect  = $_POST['redirect'] ?? '/cart.php';

if ($productId <= 0) {
    header('Location: ' . $redirect);
    exit;
}

switch ($action) {
    case 'add':
        // Check stock
        $product = $pdo->prepare('SELECT stock FROM products WHERE id = ?');
        $product->execute([$productId]);
        $p = $product->fetch();
        if (!$p || $p['stock'] <= 0) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Stok produk tidak tersedia.'];
            break;
        }
        // Upsert: if exists, increment; else insert
        $stmt = $pdo->prepare(
            'INSERT INTO cart_items (user_id, product_id, quantity)
             VALUES (?, ?, 1)
             ON DUPLICATE KEY UPDATE quantity = quantity + 1'
        );
        $stmt->execute([$userId, $productId]);
        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Produk berhasil ditambahkan ke keranjang!'];
        break;

    case 'update':
        if ($qty <= 0) {
            // Remove item if qty set to 0
            $stmt = $pdo->prepare('DELETE FROM cart_items WHERE user_id = ? AND product_id = ?');
            $stmt->execute([$userId, $productId]);
        } else {
            // Check stock limit
            $product = $pdo->prepare('SELECT stock FROM products WHERE id = ?');
            $product->execute([$productId]);
            $p = $product->fetch();
            $qty = min($qty, $p['stock'] ?? $qty);
            $stmt = $pdo->prepare('UPDATE cart_items SET quantity = ? WHERE user_id = ? AND product_id = ?');
            $stmt->execute([$qty, $userId, $productId]);
        }
        break;

    case 'remove':
        $stmt = $pdo->prepare('DELETE FROM cart_items WHERE user_id = ? AND product_id = ?');
        $stmt->execute([$userId, $productId]);
        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Produk dihapus dari keranjang.'];
        break;

    case 'clear':
        $stmt = $pdo->prepare('DELETE FROM cart_items WHERE user_id = ?');
        $stmt->execute([$userId]);
        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Keranjang dikosongkan.'];
        break;
}

header('Location: ' . $redirect);
exit;
