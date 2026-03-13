<?php include 'components/admin_header.php'; ?>
<?php
require_once __DIR__ . '/../config/functions.php';
$pdo = getDB();

$statusFilter = $_GET['status'] ?? '';
$dateFrom     = $_GET['date_from'] ?? '';
$dateTo       = $_GET['date_to'] ?? '';

$sql    = 'SELECT t.*, u.name AS user_name, u.email AS user_email FROM transactions t JOIN users u ON u.id = t.user_id WHERE 1=1';
$params = [];

if ($statusFilter) { $sql .= ' AND t.status = ?'; $params[] = $statusFilter; }
if ($dateFrom)     { $sql .= ' AND DATE(t.created_at) >= ?'; $params[] = $dateFrom; }
if ($dateTo)       { $sql .= ' AND DATE(t.created_at) <= ?'; $params[] = $dateTo; }

$sql .= ' ORDER BY t.created_at DESC LIMIT 100';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$transactions = $stmt->fetchAll();

// Summary stats for filtered results
$totalFiltered = array_sum(array_column($transactions, 'grand_total'));

// Updated premium status colors
$statusColors = [
    'pending'    => 'bg-amber-50 text-amber-700 border-amber-200',
    'processing' => 'bg-blue-50 text-blue-700 border-blue-200',
    'completed'  => 'bg-emerald-50 text-emerald-700 border-emerald-200',
    'cancelled'  => 'bg-rose-50 text-rose-700 border-rose-200',
    'delivered'  => 'bg-purple-50 text-purple-700 border-purple-200'
];
$statusLabels = [
    'pending'    => 'Menunggu Bayar',
    'processing' => 'Diproses',
    'completed'  => 'Selesai',
    'cancelled'  => 'Dibatalkan',
    'delivered'  => 'Dikirim'
];
?>

<div class="mb-6 flex flex-col lg:flex-row lg:items-end justify-between gap-6">
    <div>
        <h2 class="text-3xl font-extrabold text-accent tracking-tight">Semua Transaksi</h2>
        <p class="text-slate-500 font-medium mt-2">Pantau riwayat pesanan, pendapatan, dan ubah status pengiriman.</p>
    </div>
    
    <!-- Summary Widget -->
    <div class="flex items-center gap-4 bg-white border border-slate-100 rounded-2xl p-4 shadow-[0_8px_30px_rgba(0,0,0,0.04)] shrink-0 min-w-[320px]">
        <div class="size-12 rounded-xl bg-primary-50 text-primary-600 flex items-center justify-center shrink-0 border border-primary-100">
            <span class="material-symbols-outlined text-[24px]">account_balance_wallet</span>
        </div>
        <div class="flex-1">
            <p class="text-[11px] font-bold text-slate-400 uppercase tracking-widest mb-0.5">Total Pendapatan (Filter)</p>
            <div class="flex items-end gap-2">
                <span class="font-extrabold text-slate-800 text-xl leading-none"><?= formatRupiah($totalFiltered) ?></span>
                <span class="text-xs font-bold text-primary-600 bg-primary-50 px-2 py-0.5 rounded-md self-center">
                    <?= count($transactions) ?> Pesanan
                </span>
            </div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="bg-white p-5 rounded-t-[2rem] border border-b-0 border-slate-100 shadow-[0_8px_30px_rgba(0,0,0,0.02)] mt-4 relative z-10">
    <form method="GET" class="flex flex-col md:flex-row items-end gap-5">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-5 flex-1 w-full">
            <div>
                <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-widest mb-2 pl-1">Status Pesanan</label>
                <div class="relative">
                    <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none">swap_vert</span>
                    <select name="status" class="w-full h-12 pl-12 pr-4 rounded-xl border-slate-200 bg-slate-50/50 text-sm font-medium focus:bg-white focus:ring-4 focus:ring-primary-50 focus:border-primary-300 transition-all cursor-pointer appearance-none">
                        <option value="">Semua Status Transaksi</option>
                        <?php foreach (['pending','processing','delivered','completed','cancelled'] as $s): ?>
                        <option value="<?= $s ?>" <?= $statusFilter === $s ? 'selected' : '' ?>><?= $statusLabels[$s] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div>
                <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-widest mb-2 pl-1">Periode Awal</label>
                <div class="relative">
                    <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none">calendar_today</span>
                    <input type="date" name="date_from" value="<?= htmlspecialchars($dateFrom) ?>" class="w-full h-12 pl-12 pr-4 rounded-xl border-slate-200 bg-slate-50/50 text-sm font-medium focus:bg-white focus:ring-4 focus:ring-primary-50 focus:border-primary-300 transition-all text-slate-600">
                </div>
            </div>
            <div>
                <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-widest mb-2 pl-1">Periode Akhir</label>
                <div class="relative">
                    <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none">event</span>
                    <input type="date" name="date_to" value="<?= htmlspecialchars($dateTo) ?>" class="w-full h-12 pl-12 pr-4 rounded-xl border-slate-200 bg-slate-50/50 text-sm font-medium focus:bg-white focus:ring-4 focus:ring-primary-50 focus:border-primary-300 transition-all text-slate-600">
                </div>
            </div>
        </div>
        <div class="flex gap-3 w-full md:w-auto shrink-0 mt-2 md:mt-0">
            <a href="reports.php" class="flex-1 md:flex-none h-12 px-6 border border-slate-200 rounded-xl text-sm font-bold text-slate-600 hover:bg-slate-50 flex items-center justify-center transition-colors">Reset Filter</a>
            <button type="submit" class="flex-1 md:flex-none h-12 px-8 bg-slate-800 text-white rounded-xl text-sm font-bold hover:bg-slate-900 shadow-lg shadow-slate-800/20 transition-all flex items-center justify-center gap-2">
                <span class="material-symbols-outlined text-[18px]">filter_list</span> Terapkan
            </button>
        </div>
    </form>
</div>

<!-- Transactions Table -->
<div class="bg-white rounded-b-[2rem] border border-slate-100 shadow-[0_8px_30px_rgba(0,0,0,0.04)] overflow-hidden mb-8">
    <div class="overflow-x-auto p-2">
        <table class="w-full text-left border-collapse whitespace-nowrap">
            <thead>
                <tr>
                    <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100">Order ID</th>
                    <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100">Pelanggan</th>
                    <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100">Total Nominal</th>
                    <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100">Pembayaran</th>
                    <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100">Status</th>
                    <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100 text-right">Tindakan Cepat</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                <?php foreach ($transactions as $tx): $sts = $tx['status']; ?>
                <tr class="hover:bg-slate-50/80 transition-colors group">
                    <td class="px-6 py-4">
                        <div class="flex flex-col">
                            <span class="font-bold text-slate-800 text-sm">ORD-<?= str_pad($tx['id'],6,'0',STR_PAD_LEFT) ?></span>
                            <span class="text-[10px] font-bold text-slate-400 mt-0.5"><?= date('d M Y, H:i', strtotime($tx['created_at'])) ?></span>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            <div class="size-8 rounded-full bg-slate-100 flex items-center justify-center text-slate-500 font-bold text-xs shrink-0 border border-slate-200">
                                <?= strtoupper(substr($tx['user_name'], 0, 1)) ?>
                            </div>
                            <div class="flex flex-col">
                                <span class="font-bold text-sm text-slate-700"><?= htmlspecialchars($tx['user_name']) ?></span>
                                <span class="text-[11px] font-medium text-slate-400"><?= htmlspecialchars($tx['user_email']) ?></span>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <span class="font-extrabold text-primary-800 text-base"><?= formatRupiah($tx['grand_total']) ?></span>
                    </td>
                    <td class="px-6 py-4">
                        <div class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-slate-50 border border-slate-200 shadow-sm">
                            <span class="material-symbols-outlined text-[16px] text-slate-400">payments</span>
                            <span class="text-xs font-bold text-slate-600 uppercase tracking-wide">
                                <?= $tx['payment_method'] === 'midtrans' ? 'Online' : htmlspecialchars($tx['payment_method']) ?>
                            </span>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <span class="inline-flex items-center justify-center px-3 py-1.5 rounded-lg text-xs font-bold border shadow-sm min-w-[110px] <?= $statusColors[$sts] ?? 'bg-slate-50 text-slate-600 border-slate-200' ?>">
                            <?= $statusLabels[$sts] ?? ucfirst($sts) ?>
                        </span>
                    </td>
                    <td class="px-6 py-4 text-right">
                        <?php if ($sts !== 'completed' && $sts !== 'cancelled'): ?>
                        <form method="POST" action="report_action.php" class="inline-flex relative group/select">
                            <input type="hidden" name="id" value="<?= $tx['id'] ?>">
                            <span class="material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 text-primary-600 pointer-events-none text-[16px]">edit_note</span>
                            <select name="status" onchange="this.form.submit()" class="h-9 pl-4 pr-10 text-xs font-bold rounded-xl border border-primary-200 bg-primary-50 text-primary-800 focus:ring-2 focus:ring-primary-100 focus:border-primary-400 cursor-pointer transition-all appearance-none shadow-sm hover:shadow-md">
                                <option value="" disabled>Ubah Status</option>
                                <?php foreach (array_slice($statusLabels, 1, 4) as $v => $l): // Skip pending ?>
                                <option value="<?= $v ?>" <?= $sts === $v ? 'selected' : '' ?>><?= $l ?></option>
                                <?php endforeach; ?>
                            </select>
                        </form>
                        <?php else: ?>
                        <div class="inline-flex items-center gap-1 text-[11px] font-bold text-slate-400 bg-slate-50 px-3 py-2 rounded-xl border border-slate-100">
                            <span class="material-symbols-outlined text-[14px]">lock</span> Final
                        </div>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                
                <?php if (empty($transactions)): ?>
                <tr>
                    <td colspan="6" class="py-20 text-center text-slate-400">
                        <div class="inline-flex flex-col items-center justify-center">
                            <span class="material-symbols-outlined text-[48px] mb-4 opacity-50">receipt_long</span>
                            <p class="font-medium text-lg text-slate-600">Tidak ada transaksi ditemukan.</p>
                            <p class="text-sm mt-1 opacity-75">Coba ubah filter rentang tanggal atau status pesanan.</p>
                            <a href="reports.php" class="mt-4 px-6 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-sm font-bold transition-colors">Hapus Semua Filter</a>
                        </div>
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'components/admin_footer.php'; ?>
