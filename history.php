<?php include 'components/header.php'; ?>
<?php
require_once __DIR__ . '/config/functions.php';
$pdo    = getDB();
$userId = $_SESSION['user_id'];

$stmt = $pdo->prepare(
    'SELECT t.*, COUNT(td.id) AS item_count FROM transactions t
     LEFT JOIN transaction_details td ON td.transaction_id = t.id
     WHERE t.user_id = ?
     GROUP BY t.id
     ORDER BY t.created_at DESC'
);
$stmt->execute([$userId]);
$orders = $stmt->fetchAll();

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

$statusColors = [
    'pending'    => ['bg-amber-100/50', 'text-amber-700', 'border-amber-200', 'schedule', 'bg-amber-500'],
    'processing' => ['bg-blue-100/50', 'text-blue-700', 'border-blue-200', 'package_2', 'bg-blue-500'],
    'delivered'  => ['bg-purple-100/50', 'text-purple-700', 'border-purple-200', 'local_shipping', 'bg-purple-500'],
    'completed'  => ['bg-emerald-100/50', 'text-emerald-700', 'border-emerald-200', 'task_alt', 'bg-emerald-500'],
    'cancelled'  => ['bg-rose-100/50', 'text-rose-700', 'border-rose-200', 'cancel', 'bg-rose-500'],
];
$statusLabels = ['pending' => 'Menunggu Pembayaran', 'processing' => 'Diproses', 'delivered' => 'Dikirim', 'completed' => 'Selesai', 'cancelled' => 'Dibatalkan'];

// Helper for Timeline
$timelineSteps = [
    'pending' => 1,
    'processing' => 2,
    'delivered' => 3,
    'completed' => 4,
    'cancelled' => -1 // special case
];
?>

<!-- Breadcrumb -->
<div class="mb-8 flex items-center text-sm text-slate-500 font-medium relative z-10">
    <a href="/index.php" class="hover:text-primary-600 transition-colors flex items-center gap-1"><span class="material-symbols-outlined text-[16px]">home</span> Beranda</a>
    <span class="material-symbols-outlined text-[16px] mx-2 text-slate-300">chevron_right</span>
    <span class="text-accent font-bold">Riwayat Pesanan</span>
</div>

<div class="mb-8 relative z-10 flex flex-col md:flex-row md:items-end justify-between gap-4">
    <div>
        <h1 class="text-3xl md:text-4xl font-extrabold text-accent tracking-tight">Riwayat Pesanan</h1>
        <p class="text-slate-500 font-medium mt-2">Pantau status pengiriman dan riwayat transaksi belanja Anda.</p>
    </div>
</div>

<?php if (empty($orders)): ?>
<div class="flex flex-col items-center justify-center py-32 bg-white/50 backdrop-blur-sm rounded-[3rem] border border-white/60 shadow-glass relative z-10">
    <div class="size-24 bg-slate-100 rounded-full flex items-center justify-center text-slate-400 mb-6 shadow-inner">
        <span class="material-symbols-outlined text-[48px]">receipt_long</span>
    </div>
    <h3 class="mt-4 text-2xl font-bold text-accent mb-2">Belum ada pesanan</h3>
    <p class="text-slate-500 font-medium max-w-md text-center">Mulai eksplorasi produk premium kami dan pesanan Anda akan muncul di sini.</p>
    <a href="/index.php" class="mt-8 relative inline-flex items-center gap-2 group px-8 py-3.5 bg-primary-700 text-white font-bold rounded-2xl hover:bg-primary-800 transition-all shadow-[0_8px_25px_rgba(128,0,32,0.25)] hover:shadow-[0_12px_30px_rgba(128,0,32,0.35)] hover:-translate-y-1 overflow-hidden">
        <div class="absolute inset-0 bg-white/20 translate-y-full group-hover:translate-y-0 transition-transform duration-300 ease-out"></div>
        <span class="relative z-10 material-symbols-outlined group-hover:-rotate-12 transition-transform">store</span>
        <span class="relative z-10">Mulai Belanja</span>
    </a>
</div>
<?php else: ?>
<div class="space-y-6 relative z-10">
    <?php foreach ($orders as $order): ?>
    <?php 
    $status = $order['status']; 
    $st = $statusColors[$status] ?? $statusColors['pending'];
    $currentStep = $timelineSteps[$status] ?? 0;
    ?>
    <div class="bg-white/80 backdrop-blur-xl rounded-[2rem] border border-white/60 overflow-hidden shadow-glass hover:shadow-[0_15px_40px_rgba(0,0,0,0.08)] transition-all group">
        
        <!-- Header Pesanan -->
        <div class="p-6 md:p-8 flex flex-col md:flex-row justify-between gap-6 border-b border-slate-100 bg-white/40">
            <div class="flex flex-col gap-3">
                <div class="flex flex-wrap items-center gap-3">
                    <span class="px-4 py-1.5 rounded-xl text-xs font-bold border flex items-center gap-1.5 <?= $st[0] ?> <?= $st[1] ?> <?= $st[2] ?>">
                        <span class="material-symbols-outlined text-[16px]"><?= $st[3] ?></span>
                        <?= $statusLabels[$status] ?? ucfirst($status) ?>
                    </span>
                    <span class="font-mono font-bold text-slate-400">#ORD-<?= str_pad($order['id'], 8, '0', STR_PAD_LEFT) ?></span>
                </div>
                <div class="text-sm font-medium text-slate-500 flex items-center gap-2">
                    <span class="material-symbols-outlined text-[18px]">calendar_today</span>
                    <?= date('d M Y, H:i', strtotime($order['created_at'])) ?>
                </div>
            </div>
            
            <div class="text-left md:text-right flex flex-col justify-center">
                <p class="text-xs text-slate-400 font-bold uppercase tracking-widest mb-1">Total Tagihan</p>
                <p class="text-2xl font-extrabold text-primary-800"><?= formatRupiah($order['grand_total']) ?></p>
                <p class="text-xs text-slate-500 font-medium mt-1"><?= $order['item_count'] ?> macam produk</p>
            </div>
        </div>
        
        <!-- Timeline Status (Visual) -->
        <div class="px-6 md:px-12 py-8 bg-slate-50/30">
            <?php if ($status === 'cancelled'): ?>
                <div class="flex items-center justify-center gap-3 text-rose-500 bg-rose-50 border border-rose-100 rounded-2xl p-4">
                    <span class="material-symbols-outlined text-[24px]">cancel</span>
                    <p class="font-bold">Pesanan ini telah dibatalkan.</p>
                </div>
            <?php else: ?>
                <div class="relative w-full max-w-2xl mx-auto">
                    <!-- Garis Background -->
                    <div class="absolute top-1/2 left-0 w-full h-1 bg-slate-200 -translate-y-1/2 rounded-full z-0"></div>
                    
                    <!-- Garis Progress -->
                    <div class="absolute top-1/2 left-0 h-1 bg-emerald-500 -translate-y-1/2 rounded-full z-0 transition-all duration-1000" style="width: <?= ($currentStep - 1) * 33.33 ?>%"></div>
                    
                    <div class="relative z-10 flex justify-between">
                        <!-- Step 1: Pending -->
                        <div class="flex flex-col items-center gap-2">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-white transition-colors duration-500 <?= $currentStep >= 1 ? 'bg-emerald-500 shadow-[0_0_15px_rgba(16,185,129,0.5)]' : 'bg-slate-200 text-slate-400' ?>">
                                <span class="material-symbols-outlined text-[16px]"><?= $currentStep > 1 ? 'check' : 'schedule' ?></span>
                            </div>
                            <span class="text-[10px] md:text-xs font-bold <?= $currentStep >= 1 ? 'text-emerald-700' : 'text-slate-400' ?>">Dibayar</span>
                        </div>
                        
                        <!-- Step 2: Processing -->
                        <div class="flex flex-col items-center gap-2">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-white transition-colors duration-500 <?= $currentStep >= 2 ? 'bg-emerald-500 shadow-[0_0_15px_rgba(16,185,129,0.5)]' : 'bg-slate-200 text-slate-400' ?>">
                                <span class="material-symbols-outlined text-[16px]"><?= $currentStep > 2 ? 'check' : 'package_2' ?></span>
                            </div>
                            <span class="text-[10px] md:text-xs font-bold <?= $currentStep >= 2 ? 'text-emerald-700' : 'text-slate-400' ?>">Diproses</span>
                        </div>
                        
                        <!-- Step 3: Delivered -->
                        <div class="flex flex-col items-center gap-2">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-white transition-colors duration-500 <?= $currentStep >= 3 ? 'bg-emerald-500 shadow-[0_0_15px_rgba(16,185,129,0.5)]' : 'bg-slate-200 text-slate-400' ?>">
                                <span class="material-symbols-outlined text-[16px]"><?= $currentStep > 3 ? 'check' : 'local_shipping' ?></span>
                            </div>
                            <span class="text-[10px] md:text-xs font-bold <?= $currentStep >= 3 ? 'text-emerald-700' : 'text-slate-400' ?>">Dikirim</span>
                        </div>
                        
                        <!-- Step 4: Completed -->
                        <div class="flex flex-col items-center gap-2">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-white transition-colors duration-500 <?= $currentStep >= 4 ? 'bg-emerald-500 shadow-[0_0_15px_rgba(16,185,129,0.5)]' : 'bg-slate-200 text-slate-400' ?>">
                                <span class="material-symbols-outlined text-[16px]">task_alt</span>
                            </div>
                            <span class="text-[10px] md:text-xs font-bold <?= $currentStep >= 4 ? 'text-emerald-700' : 'text-slate-400' ?>">Selesai</span>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Aksi Pesanan -->
        <div class="p-6 border-t border-slate-100 flex flex-col sm:flex-row justify-between items-center gap-4 bg-white/40">
            <a href="/order-success.php?id=<?= $order['id'] ?>" class="text-primary-600 hover:text-primary-800 text-sm font-bold flex items-center gap-1 hover:underline transition-colors w-full sm:w-auto justify-center">
                <span class="material-symbols-outlined text-[20px]">receipt_long</span> Rincian Pesanan
            </a>
            
            <div class="flex flex-wrap gap-3 w-full sm:w-auto justify-end">
            <?php if ($status === 'pending'): ?>
                <!-- Tombol Batal -->
                <form action="/cancel_order.php" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan pesanan ini? Stok akan dikembalikan.');" class="flex-1 sm:flex-none">
                    <input type="hidden" name="id" value="<?= $order['id'] ?>">
                    <button type="submit" class="w-full flex justify-center items-center gap-2 text-sm font-bold px-6 py-3 bg-red-50 text-red-600 border border-red-100 rounded-xl hover:bg-red-500 hover:text-white transition-colors shadow-sm">
                        <span class="material-symbols-outlined text-[18px]">cancel</span> Batalkan
                    </button>
                </form>

                <?php if (!empty($order['snap_token'])): ?>
                <a href="/payment.php?id=<?= $order['id'] ?>" class="flex-1 sm:flex-none flex justify-center items-center gap-2 text-sm font-bold px-6 py-3 bg-primary-700 text-white rounded-xl hover:bg-primary-800 transition-colors shadow-[0_8px_20px_rgba(128,0,32,0.2)] hover:-translate-y-0.5">
                    <span class="material-symbols-outlined text-[18px]">payments</span> Bayar Sekarang
                </a>
                <?php endif; ?>

            <?php elseif ($status === 'completed' || $status === 'cancelled'): ?>
                <a href="/index.php" class="flex-1 sm:flex-none flex justify-center items-center gap-2 text-sm font-bold px-6 py-3 border-2 border-primary-200 text-primary-700 bg-white rounded-xl hover:border-primary-600 hover:bg-primary-50 transition-colors shadow-sm">
                    <span class="material-symbols-outlined text-[18px]">shopping_bag</span> Belanja Lagi
                </a>
            <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Decorative Background Blobs -->
<div class="fixed top-1/4 right-0 w-[500px] h-[500px] bg-primary-100/30 rounded-full blur-[120px] pointer-events-none -z-10 mix-blend-multiply"></div>
<div class="fixed bottom-0 left-0 w-[600px] h-[400px] bg-indigo-100/20 rounded-full blur-[120px] pointer-events-none -z-10 mix-blend-multiply"></div>
<?php endif; ?>

<?php include 'components/footer.php'; ?>
