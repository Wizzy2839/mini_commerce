<?php
require_once __DIR__ . '/../config/functions.php';
requireAdmin();

$pdo    = getDB();
$action = $_POST['action'] ?? '';
$id     = (int)($_POST['id'] ?? 0);

switch ($action) {
    case 'create':
        $name       = trim($_POST['name']       ?? '');
        $catId      = (int)$_POST['category_id'];
        $price      = (float)$_POST['price'];
        $stock      = (int)$_POST['stock'];
        $desc       = trim($_POST['description'] ?? '');
        $imageUrl   = trim($_POST['image_url']   ?? '');
        $stmt = $pdo->prepare('INSERT INTO products (name, category_id, price, stock, description, image_url, rating) VALUES (?, ?, ?, ?, ?, ?, 0)');
        $stmt->execute([$name, $catId, $price, $stock, $desc, $imageUrl]);
        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Produk berhasil ditambahkan.'];
        break;

    case 'update':
        $name     = trim($_POST['name']       ?? '');
        $catId    = (int)$_POST['category_id'];
        $price    = (float)$_POST['price'];
        $stock    = (int)$_POST['stock'];
        $desc     = trim($_POST['description'] ?? '');
        $imageUrl = trim($_POST['image_url']   ?? '');
        $stmt = $pdo->prepare('UPDATE products SET name=?, category_id=?, price=?, stock=?, description=?, image_url=? WHERE id=?');
        $stmt->execute([$name, $catId, $price, $stock, $desc, $imageUrl, $id]);
        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Produk berhasil diperbarui.'];
        break;

    case 'delete':
        $pdo->prepare('DELETE FROM products WHERE id = ?')->execute([$id]);
        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Produk berhasil dihapus.'];
        break;
}

header('Location: /admin/products.php');
exit;
