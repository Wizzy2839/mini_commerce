<?php include 'components/admin_header.php'; ?>
<?php
require_once __DIR__ . '/../config/functions.php';
$pdo = getDB();

// Stats
$totalSales    = $pdo->query("SELECT COALESCE(SUM(grand_total),0) FROM transactions WHERE status != 'cancelled'")->fetchColumn();
$totalOrders   = $pdo->query("SELECT COUNT(*) FROM transactions")->fetchColumn();
$totalProducts = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
$newCustomers  = $pdo->query("SELECT COUNT(*) FROM users WHERE role='user' AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)")->fetchColumn();

// Recent 10 transactions
$recentTx = $pdo->query("SELECT t.*, u.name AS user_name FROM transactions t JOIN users u ON u.id = t.user_id ORDER BY t.created_at DESC LIMIT 10")->fetchAll();

// Top categories
$topCats = $pdo->query(
    "SELECT c.name, SUM(td.price_at_purchase * td.quantity) AS total_sales, SUM(td.quantity) AS units_sold
     FROM transaction_details td
     JOIN products p ON p.id = td.product_id
     JOIN categories c ON c.id = p.category_id
     GROUP BY c.id, c.name
     ORDER BY total_sales DESC LIMIT 5"
)->fetchAll();
?>

<div class="mb-8 flex flex-col md:flex-row md:items-end justify-between gap-4">
    <div>
        <h2 class="text-3xl font-extrabold text-accent tracking-tight">Dashboard Overview</h2>
        <p class="text-slate-500 font-medium mt-2">Selamat datang, <strong class="text-primary-800"><?= htmlspecialchars($adminName) ?></strong>! Berikut ringkasan performa toko Anda hari ini.</p>
    </div>
    <div class="flex gap-3">
        <button class="flex items-center gap-2 px-4 py-2 bg-white border border-slate-200 text-slate-700 font-bold rounded-xl hover:bg-slate-50 transition-colors shadow-sm text-sm">
            <span class="material-symbols-outlined text-[18px]">calendar_month</span> 30 Hari Terakhir
        </button>
        <button class="flex items-center gap-2 px-4 py-2 bg-primary-800 text-white font-bold rounded-xl hover:bg-primary-900 transition-colors shadow-sm shadow-primary-900/20 text-sm">
            <span class="material-symbols-outlined text-[18px]">download</span> Export
        </button>
    </div>
</div>

<!-- Stats Cards -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
    <?php $cards = [
        ['icon'=>'account_balance_wallet', 'label'=>'Total Penjualan', 'value'=>formatRupiah($totalSales), 'bg'=>'bg-gradient-to-br from-emerald-500 to-emerald-600', 'color'=>'text-emerald-50'],
        ['icon'=>'shopping_cart_checkout', 'label'=>'Total Pesanan', 'value'=>number_format($totalOrders), 'bg'=>'bg-gradient-to-br from-blue-500 to-blue-600', 'color'=>'text-blue-50'],
        ['icon'=>'inventory_2', 'label'=>'Total Produk', 'value'=>number_format($totalProducts), 'bg'=>'bg-gradient-to-br from-purple-500 to-purple-600', 'color'=>'text-purple-50'],
        ['icon'=>'group_add', 'label'=>'Pelanggan Baru', 'value'=>number_format($newCustomers) . ' <span class="text-xs font-normal opacity-70">/ 30 hr</span>', 'bg'=>'bg-gradient-to-br from-orange-500 to-orange-600', 'color'=>'text-orange-50']
    ]; ?>
    <?php foreach ($cards as $i => $c): ?>
    <div class="bg-white p-6 rounded-[1.5rem] border border-slate-100 shadow-[0_8px_30px_rgba(0,0,0,0.04)] hover:shadow-[0_8px_30px_rgba(0,0,0,0.08)] transition-all transform hover:-translate-y-1 relative overflow-hidden group">
        <!-- Decoration -->
        <div class="absolute -right-6 -top-6 w-24 h-24 rounded-full <?= $c['bg'] ?> opacity-10 group-hover:scale-150 transition-transform duration-500"></div>
        
        <div class="flex items-start justify-between mb-4 relative z-10">
            <div class="<?= $c['bg'] ?> <?= $c['color'] ?> w-12 h-12 rounded-xl flex items-center justify-center shadow-lg">
                <span class="material-symbols-outlined text-[24px]"><?= $c['icon'] ?></span>
            </div>
            <span class="flex items-center gap-1 text-xs font-bold text-emerald-600 bg-emerald-50 px-2 py-1 rounded-lg">
                <span class="material-symbols-outlined text-[14px]">trending_up</span> +<?= rand(2,15) ?>%
            </span>
        </div>
        <div class="relative z-10">
            <p class="text-slate-500 text-sm font-bold uppercase tracking-wider mb-1"><?= $c['label'] ?></p>
            <h3 class="text-2xl font-black text-accent tracking-tight"><?= $c['value'] ?></h3>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    
    <!-- Recent Transactions (Spans 2 columns on large screens) -->
    <div class="lg:col-span-2 space-y-6">
        <div class="bg-white rounded-[2rem] border border-slate-100 shadow-[0_8px_30px_rgba(0,0,0,0.04)] overflow-hidden">
            <div class="p-6 md:p-8 border-b border-slate-50 flex justify-between items-center bg-slate-50/50">
                <h4 class="font-extrabold text-xl text-accent flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary-600">receipt_long</span>
                    Transaksi Terbaru
                </h4>
                <a href="orders.php" class="text-sm text-primary-700 font-bold hover:underline flex items-center gap-1">
                    Lihat Semua <span class="material-symbols-outlined text-[16px]">arrow_forward</span>
                </a>
            </div>
            <div class="overflow-x-auto p-2">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr>
                            <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100">Order ID</th>
                            <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100">Pelanggan</th>
                            <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100">Total</th>
                            <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100">Status</th>
                            <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100 text-right">Waktu</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        <?php foreach ($recentTx as $tx):
                            $statusColors = [
                                'pending'=>['bg-amber-100/50','text-amber-700'],
                                'processing'=>['bg-blue-100/50','text-blue-700'],
                                'completed'=>['bg-emerald-100/50','text-emerald-700'],
                                'cancelled'=>['bg-rose-100/50','text-rose-700'],
                                'delivered'=>['bg-purple-100/50','text-purple-700']
                            ];
                            $statusLabels = ['pending'=>'Menunggu','processing'=>'Diproses','completed'=>'Selesai','cancelled'=>'Batal','delivered'=>'Dikirim'];
                            $sts = $tx['status'];
                            $stClass = $statusColors[$sts] ?? ['bg-slate-100', 'text-slate-600'];
                        ?>
                        <tr class="hover:bg-slate-50/80 transition-colors group cursor-pointer" onclick="window.location='order_detail.php?id=<?= $tx['id'] ?>'">
                            <td class="px-6 py-4">
                                <span class="font-mono font-bold text-accent group-hover:text-primary-600 transition-colors">#ORD-<?= str_pad($tx['id'],5,'0',STR_PAD_LEFT) ?></span>
                            </td>
                            <td class="px-6 py-4 font-bold text-slate-700 flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center text-slate-400 font-bold text-xs uppercase"><?= substr($tx['user_name'],0,2) ?></div>
                                <?= htmlspecialchars($tx['user_name']) ?>
                            </td>
                            <td class="px-6 py-4 font-extrabold text-primary-800"><?= formatRupiah($tx['grand_total']) ?></td>
                            <td class="px-6 py-4">
                                <span class="px-3 py-1 rounded-lg text-xs font-bold <?= $stClass[0] ?> <?= $stClass[1] ?>">
                                    <?= $statusLabels[$sts] ?? $sts ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-slate-400 text-sm font-medium text-right"><?= date('d M, H:i', strtotime($tx['created_at'])) ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($recentTx)): ?>
                        <tr><td colspan="5" class="px-6 py-12 text-center text-slate-400 font-medium">Belum ada transaksi</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Right Column -->
    <div class="space-y-8">
        <!-- Top categories -->
        <?php if (!empty($topCats)): ?>
        <div class="bg-white rounded-[2rem] border border-slate-100 shadow-[0_8px_30px_rgba(0,0,0,0.04)] overflow-hidden">
            <div class="p-6 border-b border-slate-50 flex justify-between items-center bg-slate-50/50">
                <h4 class="font-extrabold text-lg text-accent flex items-center gap-2">
                    <span class="material-symbols-outlined text-orange-500">local_fire_department</span>
                    Kategori Terlaris
                </h4>
            </div>
            <div class="p-2">
                <table class="w-full text-left border-collapse">
                    <tbody class="divide-y divide-slate-50">
                        <?php foreach ($topCats as $i => $cat): ?>
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <span class="text-xs font-black text-slate-300 w-4"><?= $i+1 ?></span>
                                    <div>
                                        <p class="font-bold text-slate-800"><?= htmlspecialchars($cat['name']) ?></p>
                                        <p class="text-xs font-medium text-slate-500 mt-0.5"><?= number_format($cat['units_sold']) ?> produk terjual</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 font-bold text-primary-700 text-right"><?= formatRupiah($cat['total_sales']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Quick Actions Dummy -->
        <div class="bg-gradient-to-br from-primary-900 to-primary-950 rounded-[2rem] p-8 text-white shadow-lg shadow-primary-900/20 relative overflow-hidden">
            <div class="absolute -right-10 -bottom-10 w-40 h-40 bg-white/5 rounded-full blur-2xl"></div>
            <h4 class="font-bold text-lg mb-4">Aksi Cepat</h4>
            <div class="grid grid-cols-2 gap-3">
                <a href="product_add.php" class="bg-white/10 hover:bg-white/20 border border-white/10 rounded-xl p-4 flex flex-col items-center justify-center gap-2 transition-colors text-center">
                    <span class="material-symbols-outlined">add_circle</span>
                    <span class="text-xs font-bold">Produk Baru</span>
                </a>
                <a href="reports.php" class="bg-white/10 hover:bg-white/20 border border-white/10 rounded-xl p-4 flex flex-col items-center justify-center gap-2 transition-colors text-center">
                    <span class="material-symbols-outlined">analytics</span>
                    <span class="text-xs font-bold">Laporan</span>
                </a>
            </div>
        </div>
    </div>
</div>

<?php include 'components/admin_footer.php'; ?>
