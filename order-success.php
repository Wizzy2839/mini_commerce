<?php include 'components/header.php'; ?>
<?php
require_once __DIR__ . '/config/functions.php';
$pdo = getDB();

$txId = (int)($_GET['id'] ?? 0);
if ($txId <= 0) { header('Location: /index.php'); exit; }

// Fetch transaction (only show if it belongs to current user)
$stmt = $pdo->prepare('SELECT * FROM transactions WHERE id = ? AND user_id = ?');
$stmt->execute([$txId, $_SESSION['user_id']]);
$tx = $stmt->fetch();
if (!$tx) { header('Location: /index.php'); exit; }

// Fetch items
$stmt = $pdo->prepare(
    'SELECT td.*, p.name, p.image_url
     FROM transaction_details td
     JOIN products p ON p.id = td.product_id
     WHERE td.transaction_id = ?'
);
$stmt->execute([$txId]);
$details = $stmt->fetchAll();

$paymentLabels = [
    'transfer' => 'Transfer Bank (Virtual Account)',
    'ewallet'  => 'E-Wallet (GoPay, OVO, Dana)',
    'card'     => 'Kartu Kredit / Debit',
    'cod'      => 'Bayar di Tempat (COD)',
];
?>

<div class="max-w-2xl mx-auto py-8">
    <!-- Success Icon -->
    <div class="text-center mb-8">
        <div class="relative inline-flex items-center justify-center size-24 bg-green-100 rounded-full mb-4">
            <span class="material-symbols-outlined text-green-600 text-[56px] fill-1">check_circle</span>
            <span class="absolute inset-0 rounded-full border-4 border-green-300 animate-ping opacity-30"></span>
        </div>
        <h2 class="text-3xl font-bold text-slate-900 dark:text-white">Pesanan Berhasil!</h2>
        <p class="text-slate-500 mt-2">Terima kasih! Pesananmu sedang kami proses dan akan segera dikirim.</p>
    </div>

    <!-- Order Summary Card -->
    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 overflow-hidden shadow-sm mb-6">
        <!-- Order Info Header -->
        <div class="p-6 bg-gradient-to-r from-primary/5 to-blue-50 dark:from-primary/10 dark:to-slate-900 border-b border-slate-200 dark:border-slate-700">
        <div class="grid grid-cols-2 gap-4 text-sm">
                <div>
                    <p class="text-slate-500 font-medium">ID Pesanan</p>
                    <p class="font-bold text-slate-900 dark:text-white mt-1 font-mono">ORD-<?= str_pad($tx['id'], 8, '0', STR_PAD_LEFT) ?></p>
                </div>
                <div>
                    <p class="text-slate-500 font-medium">Tanggal Pesanan</p>
                    <p class="font-bold text-slate-900 dark:text-white mt-1"><?= date('d M Y, H:i', strtotime($tx['created_at'])) ?> WIB</p>
                </div>
                <div>
                    <p class="text-slate-500 font-medium">Metode Pembayaran</p>
                    <p class="font-bold text-slate-900 dark:text-white mt-1"><?= $paymentLabels[$tx['payment_method']] ?? $tx['payment_method'] ?></p>
                </div>
                <div>
                    <p class="text-slate-500 font-medium">Status</p>
                    <?php if ($tx['status'] === 'pending'): ?>
                    <span class="inline-flex items-center gap-1 mt-1 px-2.5 py-1 rounded-full text-xs font-bold bg-yellow-100 text-yellow-700 border border-yellow-200">
                        <span class="size-1.5 rounded-full bg-yellow-500 animate-pulse"></span>
                        Menunggu Pembayaran
                    </span>
                    <?php elseif ($tx['status'] === 'processing'): ?>
                    <span class="inline-flex items-center gap-1 mt-1 px-2.5 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-700 border border-blue-200">
                        <span class="size-1.5 rounded-full bg-blue-500 animate-pulse"></span>
                        Pembayaran Dikonfirmasi
                    </span>
                    <?php elseif ($tx['status'] === 'delivered'): ?>
                    <span class="inline-flex items-center gap-1 mt-1 px-2.5 py-1 rounded-full text-xs font-bold bg-purple-100 text-purple-700 border border-purple-200">
                        <span class="size-1.5 rounded-full bg-purple-500"></span>
                        Sedang Dikirim
                    </span>
                    <?php elseif ($tx['status'] === 'completed'): ?>
                    <span class="inline-flex items-center gap-1 mt-1 px-2.5 py-1 rounded-full text-xs font-bold bg-green-100 text-green-700 border border-green-200">
                        <span class="size-1.5 rounded-full bg-green-500"></span>
                        Selesai
                    </span>
                    <?php else: ?>
                    <span class="inline-flex items-center gap-1 mt-1 px-2.5 py-1 rounded-full text-xs font-bold bg-red-100 text-red-700 border border-red-200">
                        Dibatalkan
                    </span>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Alamat -->
        <div class="p-6 border-b border-slate-200 dark:border-slate-700">
            <h4 class="font-bold text-slate-700 dark:text-slate-300 mb-2 flex items-center gap-2 text-sm">
                <span class="material-symbols-outlined text-[18px] text-primary">location_on</span> Alamat Pengiriman
            </h4>
            <p class="font-bold text-slate-900 dark:text-white"><?= htmlspecialchars($tx['recipient_name']) ?></p>
            <p class="text-slate-600 dark:text-slate-300 text-sm mt-1"><?= nl2br(htmlspecialchars($tx['address'])) ?></p>
            <p class="text-slate-600 dark:text-slate-300 text-sm mt-1">📞 <?= htmlspecialchars($tx['phone'] ?? '') ?></p>
        </div>

        <!-- Items -->
        <div class="p-6 border-b border-slate-200 dark:border-slate-700 divide-y divide-slate-100 dark:divide-slate-700">
            <?php foreach ($details as $d): ?>
            <div class="flex items-center gap-4 py-3 first:pt-0 last:pb-0">
                <div class="size-14 rounded-lg bg-slate-200 dark:bg-slate-700 animate-pulse border border-slate-200 dark:border-slate-700 shrink-0 overflow-hidden">
                    <img src="<?= htmlspecialchars($d['image_url'] ?? '') ?>" alt="<?= htmlspecialchars($d['name']) ?>"
                        class="w-full h-full object-cover opacity-0 transition-opacity duration-500 ease-out"
                        loading="lazy"
                        onload="this.classList.remove('opacity-0'); this.parentElement.classList.remove('animate-pulse');"
                        onerror="this.src='https://placehold.co/56x56/f1f5f9/94a3b8?text=?'; this.classList.remove('opacity-0'); this.parentElement.classList.remove('animate-pulse');">
                </div>
                <div class="flex-1 min-w-0">
                    <h5 class="font-bold truncate"><?= htmlspecialchars($d['name']) ?></h5>
                    <p class="text-xs text-slate-500"><?= $d['quantity'] ?> x <?= formatRupiah($d['price_at_purchase']) ?></p>
                </div>
                <div class="font-bold text-slate-900 dark:text-white shrink-0"><?= formatRupiah($d['price_at_purchase'] * $d['quantity']) ?></div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Total -->
        <div class="p-6 flex justify-between items-center">
            <span class="font-bold text-lg">Total Pembayaran</span>
            <span class="text-2xl font-bold text-primary"><?= formatRupiah($tx['grand_total']) ?></span>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="flex gap-4">
        <a href="/history.php" class="flex-1 py-3 font-bold border-2 border-primary text-primary rounded-xl text-center hover:bg-primary hover:text-white transition-colors">
            Lihat Riwayat
        </a>
        <a href="/index.php" class="flex-1 py-3 font-bold bg-primary text-white rounded-xl text-center hover:bg-primary/90 transition-colors shadow-lg shadow-primary/30">
            Belanja Lagi
        </a>
    </div>
</div>

<?php include 'components/footer.php'; ?>
