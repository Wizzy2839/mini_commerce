<?php
require_once __DIR__ . '/../config/functions.php';
requireAdmin();
$pdo = getDB();
$id  = (int)($_POST['id'] ?? 0);
$status = $_POST['status'] ?? '';
$allowed = ['pending','processing','delivered','completed','cancelled'];

if ($id > 0 && in_array($status, $allowed)) {
    $pdo->prepare('UPDATE transactions SET status = ? WHERE id = ?')->execute([$status, $id]);
}
header('Location: /admin/reports.php');
exit;
