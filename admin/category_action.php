<?php
require_once __DIR__ . '/../config/functions.php';
requireAdmin();

$pdo    = getDB();
$action = $_POST['action'] ?? '';
$id     = (int)($_POST['id'] ?? 0);

switch ($action) {
    case 'create':
        $name = trim($_POST['name'] ?? '');
        $icon = trim($_POST['icon'] ?? 'category');
        if ($name) {
            $pdo->prepare('INSERT INTO categories (name, icon, is_active) VALUES (?, ?, 1)')->execute([$name, $icon]);
            $_SESSION['flash'] = ['type' => 'success', 'message' => 'Kategori berhasil ditambahkan.'];
        }
        break;

    case 'update':
        $name = trim($_POST['name'] ?? '');
        $icon = trim($_POST['icon'] ?? 'category');
        if ($name && $id) {
            $pdo->prepare('UPDATE categories SET name = ?, icon = ? WHERE id = ?')->execute([$name, $icon, $id]);
            $_SESSION['flash'] = ['type' => 'success', 'message' => 'Kategori berhasil diperbarui.'];
        }
        break;

    case 'delete':
        // Check if products in category
        $count = $pdo->prepare('SELECT COUNT(*) FROM products WHERE category_id = ?');
        $count->execute([$id]);
        if ($count->fetchColumn() > 0) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Tidak bisa menghapus kategori yang masih memiliki produk.'];
        } else {
            $pdo->prepare('DELETE FROM categories WHERE id = ?')->execute([$id]);
            $_SESSION['flash'] = ['type' => 'success', 'message' => 'Kategori berhasil dihapus.'];
        }
        break;

    case 'toggle':
        $pdo->prepare('UPDATE categories SET is_active = NOT is_active WHERE id = ?')->execute([$id]);
        break;
}

header('Location: /admin/categories.php');
exit;
