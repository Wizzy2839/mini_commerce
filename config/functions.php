<?php
require_once __DIR__ . '/../config/database.php';

/**
 * Require the user to be logged in. Redirects to login page if not.
 * @param string $redirect Path to redirect to if not logged in
 */
function requireLogin(string $redirect = '/auth/login.php'): void {
    if (session_status() === PHP_SESSION_NONE) session_start();
    if (empty($_SESSION['user_id'])) {
        header('Location: ' . $redirect);
        exit;
    }
}

/**
 * Require the user to have admin role. Redirects to login if not.
 */
function requireAdmin(): void {
    if (session_status() === PHP_SESSION_NONE) session_start();
    if (empty($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
        header('Location: /auth/login.php');
        exit;
    }
}

/**
 * Get the current logged-in user's data from the DB.
 */
function getCurrentUser(): ?array {
    if (session_status() === PHP_SESSION_NONE) session_start();
    if (empty($_SESSION['user_id'])) return null;
    $pdo = getDB();
    $stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch() ?: null;
}

/**
 * Get the total number of items in the current user's cart.
 */
function getCartCount(): int {
    if (session_status() === PHP_SESSION_NONE) session_start();
    if (empty($_SESSION['user_id'])) return 0;
    $pdo = getDB();
    $stmt = $pdo->prepare('SELECT COALESCE(SUM(quantity), 0) FROM cart_items WHERE user_id = ?');
    $stmt->execute([$_SESSION['user_id']]);
    return (int) $stmt->fetchColumn();
}

/**
 * Format a number as Indonesian Rupiah.
 */
function formatRupiah(float $amount): string {
    return 'Rp ' . number_format($amount, 0, ',', '.');
}
