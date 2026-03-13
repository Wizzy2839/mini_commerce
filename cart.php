<?php include 'components/header.php'; ?>
<?php
require_once __DIR__ . '/config/functions.php';
$pdo    = getDB();
$userId = $_SESSION['user_id'];

// Flash message
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

// Fetch cart items joined with products
$stmt = $pdo->prepare(
    'SELECT ci.*, p.name, p.image_url, p.price, p.stock, c.name AS category_name
     FROM cart_items ci
     JOIN products p ON p.id = ci.product_id
     JOIN categories c ON c.id = p.category_id
     WHERE ci.user_id = ?
     ORDER BY ci.added_at ASC'
);
$stmt->execute([$userId]);
$items = $stmt->fetchAll();

$subtotal = 0;
foreach ($items as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}
$ppn = $subtotal * 0.11; // Estimasi PPN 11%
$grandTotal = $subtotal + $ppn;
$itemCount = count($items);
?>

<!-- Flash Messages -->
<?php if ($flash): ?>
<div class="mb-6 animate-[fade-in-down_0.5s_ease-out]">
    <div class="flex items-center gap-3 px-5 py-4 rounded-2xl <?= $flash['type'] === 'success' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200 shadow-[0_8px_30px_rgba(16,185,129,0.12)]' : 'bg-red-50 text-red-700 border border-red-200 shadow-[0_8px_30px_rgba(239,68,68,0.12)]' ?> text-sm font-semibold">
        <span class="material-symbols-outlined text-[24px]"><?= $flash['type'] === 'success' ? 'check_circle' : 'error' ?></span>
        <?= htmlspecialchars($flash['message']) ?>
    </div>
</div>
<?php endif; ?>

<!-- Breadcrumb -->
<div class="mb-8 flex items-center text-sm text-slate-500 font-medium">
    <a href="index.php" class="hover:text-primary-600 transition-colors flex items-center gap-1"><span class="material-symbols-outlined text-[16px]">home</span> Beranda</a>
    <span class="material-symbols-outlined text-[16px] mx-2 text-slate-300">chevron_right</span>
    <span class="text-accent font-bold">Keranjang Belanja</span>
</div>

<?php if (empty($items)): ?>
<div class="flex flex-col items-center justify-center py-32 bg-white/50 backdrop-blur-sm rounded-[3rem] border border-white/60 shadow-glass">
    <div class="size-24 bg-slate-100 rounded-full flex items-center justify-center text-slate-400 mb-6 shadow-inner relative">
        <span class="material-symbols-outlined text-[48px]">shopping_cart</span>
        <div class="absolute -top-1 -right-1 size-6 bg-rose-500 rounded-full text-white text-xs font-bold border-4 border-slate-100 flex items-center justify-center shadow-lg">0</div>
    </div>
    <h3 class="mt-4 text-2xl font-bold text-accent mb-2">Keranjang Anda Kosong</h3>
    <p class="text-slate-500 font-medium max-w-md text-center">Yuk, mulai tambahkan produk favoritmu dan nikmati penawaran spesial!</p>
    <a href="/index.php" class="mt-8 relative inline-flex items-center gap-2 group px-8 py-3.5 bg-primary-700 text-white font-bold rounded-2xl hover:bg-primary-800 transition-all shadow-[0_8px_25px_rgba(128,0,32,0.25)] hover:shadow-[0_12px_30px_rgba(128,0,32,0.35)] hover:-translate-y-1 overflow-hidden">
        <div class="absolute inset-0 bg-white/20 translate-y-full group-hover:translate-y-0 transition-transform duration-300 ease-out"></div>
        <span class="relative z-10 material-symbols-outlined group-hover:-rotate-12 transition-transform">store</span>
        <span class="relative z-10">Mulai Belanja</span>
    </a>
</div>
<?php else: ?>
<div class="flex flex-col lg:flex-row gap-8 items-start relative z-10">
    <!-- Daftar Item -->
    <div class="w-full lg:w-2/3">
        <div class="bg-white/80 backdrop-blur-xl rounded-[2.5rem] border border-white/60 shadow-glass overflow-hidden">
            <div class="p-6 md:p-8 border-b border-slate-100 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-white/50">
                <div>
                    <h2 class="text-2xl font-extrabold text-accent">Keranjang Belanja</h2>
                    <p class="text-sm font-medium text-slate-500 mt-1">Anda memiliki <?= $itemCount ?> produk dalam keranjang</p>
                </div>
                <form method="POST" action="/cart_action.php" onsubmit="event.preventDefault(); showConfirmModal(this, 'Semua Produk di Keranjang');">
                    <input type="hidden" name="action" value="clear">
                    <input type="hidden" name="product_id" value="0">
                    <input type="hidden" name="redirect" value="/cart.php">
                    <button type="submit" class="text-sm text-rose-500 font-bold hover:text-rose-700 bg-rose-50 px-4 py-2 rounded-xl transition-colors shrink-0">Hapus Semua</button>
                </form>
            </div>
            
            <div class="divide-y divide-slate-100">
                <?php foreach ($items as $item): ?>
                <div class="p-6 md:p-8 flex flex-col sm:flex-row gap-6 items-start sm:items-center hover:bg-white/40 transition-colors group">
                    
                    <!-- Product Image -->
                    <div class="relative size-28 rounded-2xl bg-slate-50 border border-slate-100 flex-shrink-0 overflow-hidden shadow-inner group-hover:shadow-[0_8px_20px_rgba(128,0,32,0.06)] transition-all">
                        <img src="<?= htmlspecialchars($item['image_url'] ?? '') ?>" alt="<?= htmlspecialchars($item['name']) ?>" 
                            class="w-full h-full object-cover mix-blend-multiply transition-transform duration-500 group-hover:scale-110"
                            loading="lazy"
                            onerror="this.src='https://placehold.co/96x96/f8f9fa/94a3b8?text=?';">
                    </div>
                    
                    <!-- Product Details -->
                    <div class="flex-1 min-w-0">
                        <p class="text-[10px] text-slate-400 uppercase tracking-widest font-bold mb-1.5"><?= htmlspecialchars($item['category_name']) ?></p>
                        <h4 class="text-lg md:text-xl font-bold text-accent mb-2 truncate group-hover:text-primary-700 transition-colors leading-tight">
                            <a href="/product.php?id=<?= $item['product_id'] ?>"><?= htmlspecialchars($item['name']) ?></a>
                        </h4>
                        <div class="flex items-baseline gap-2">
                            <p class="text-primary-800 font-extrabold text-xl md:text-2xl"><?= formatRupiah($item['price'] * $item['quantity']) ?></p>
                            <?php if($item['quantity'] > 1): ?>
                            <p class="text-xs text-slate-400 font-medium"><?= formatRupiah($item['price']) ?> / produk</p>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Actions -->
                    <div class="flex flex-row sm:flex-col items-center gap-4 w-full sm:w-auto justify-between sm:justify-center mt-2 sm:mt-0">
                        
                        <!-- Premium Quantity Updater -->
                        <div class="flex items-center justify-between border-2 border-slate-100 rounded-2xl overflow-hidden bg-white h-11 w-32 shadow-sm focus-within:border-primary-400 focus-within:ring-4 focus-within:ring-primary-50 transition-all">
                            <form method="POST" action="/cart_action.php" class="h-full">
                                <input type="hidden" name="action" value="update">
                                <input type="hidden" name="product_id" value="<?= $item['product_id'] ?>">
                                <input type="hidden" name="quantity" value="<?= $item['quantity'] - 1 ?>">
                                <input type="hidden" name="redirect" value="/cart.php">
                                <button class="w-10 h-full flex items-center justify-center text-slate-400 hover:text-primary-600 hover:bg-primary-50 transition-colors">
                                    <span class="material-symbols-outlined text-[18px]">remove</span>
                                </button>
                            </form>
                            
                            <span class="w-8 text-center font-bold text-accent text-sm"><?= $item['quantity'] ?></span>
                            
                            <form method="POST" action="/cart_action.php" class="h-full">
                                <input type="hidden" name="action" value="update">
                                <input type="hidden" name="product_id" value="<?= $item['product_id'] ?>">
                                <input type="hidden" name="quantity" value="<?= $item['quantity'] + 1 ?>">
                                <input type="hidden" name="redirect" value="/cart.php">
                                <button class="w-10 h-full flex items-center justify-center text-slate-400 hover:text-primary-600 hover:bg-primary-50 transition-colors" <?= $item['quantity'] >= $item['stock'] ? 'disabled title="Stok maksimal"' : '' ?>>
                                    <span class="material-symbols-outlined text-[18px]">add</span>
                                </button>
                            </form>
                        </div>
                        
                        <!-- Remove Button -->
                        <form method="POST" action="/cart_action.php" onsubmit="event.preventDefault(); showConfirmModal(this, '<?= htmlspecialchars(addslashes($item['name'])) ?>');">
                            <input type="hidden" name="action" value="remove">
                            <input type="hidden" name="product_id" value="<?= $item['product_id'] ?>">
                            <input type="hidden" name="redirect" value="/cart.php">
                            <button type="submit" class="text-slate-400 hover:text-rose-500 hover:bg-rose-50 transition-colors p-2 rounded-xl" title="Hapus Produk dari Keranjang">
                                <span class="material-symbols-outlined text-[20px]">delete_outline</span>
                            </button>
                        </form>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <div class="p-4 bg-slate-50/50 border-t border-slate-100 flex justify-center">
                 <a href="index.php" class="inline-flex items-center gap-2 text-sm text-primary-600 font-bold hover:text-primary-800 transition-colors px-4 py-2 hover:bg-primary-50 rounded-lg">
                    <span class="material-symbols-outlined text-[18px]">arrow_back</span>
                    Tambah Produk Lain
                </a>
            </div>
        </div>
    </div>

    <!-- Summary / Ringkasan -->
    <div class="w-full lg:w-1/3">
        <div class="bg-white/80 backdrop-blur-xl rounded-[2.5rem] border border-white/60 p-6 md:p-8 shadow-glass sticky top-28">
            <h3 class="text-xl font-bold text-accent mb-6 flex items-center gap-2">
                <span class="material-symbols-outlined text-primary-600">receipt_long</span>
                Ringkasan Belanja
            </h3>
            
            <div class="space-y-4 mb-8 text-sm font-medium">
                <div class="flex justify-between items-center text-slate-500">
                    <span>Subtotal (<?= $itemCount ?> Barang)</span>
                    <span class="text-accent"><?= formatRupiah($subtotal) ?></span>
                </div>
                
                <div class="flex justify-between items-center text-slate-500">
                    <span class="flex items-center gap-1">Pajak (PPN 11%) <span class="material-symbols-outlined text-[14px] cursor-help" title="Pajak Pertambahan Nilai">info</span></span>
                    <span class="text-accent">+<?= formatRupiah($ppn) ?></span>
                </div>
                
                <div class="flex justify-between items-center text-emerald-600 bg-emerald-50 px-3 py-2 rounded-lg border border-emerald-100">
                    <span class="flex items-center gap-1.5"><span class="material-symbols-outlined text-[16px]">local_activity</span> Diskon</span>
                    <span>-Rp 0</span>
                </div>
                
                <div class="w-full h-px bg-slate-200/60 my-4 border-dashed border-b border-slate-300"></div>
                
                <div class="flex justify-between items-end">
                    <span class="text-base font-bold text-slate-500">Total Tagihan</span>
                    <span class="text-3xl font-extrabold text-primary-800 tracking-tight"><?= formatRupiah($grandTotal) ?></span>
                </div>
            </div>
            
            <a href="/checkout.php" class="w-full relative inline-flex items-center justify-center gap-2 group px-8 py-4 bg-primary-700 text-white font-bold rounded-2xl hover:bg-primary-800 transition-all shadow-[0_8px_25px_rgba(128,0,32,0.25)] hover:shadow-[0_12px_30px_rgba(128,0,32,0.35)] hover:-translate-y-1 overflow-hidden">
                <div class="absolute inset-0 bg-white/20 translate-y-full group-hover:translate-y-0 transition-transform duration-300 ease-out"></div>
                <span class="relative z-10 text-lg">Lanjut Pembayaran</span>
                <span class="relative z-10 material-symbols-outlined group-hover:translate-x-1 transition-transform">arrow_forward</span>
            </a>
            
            <div class="mt-6 flex flex-col gap-3">
                <div class="flex items-center justify-center gap-2 text-xs text-slate-500 font-medium">
                    <span class="material-symbols-outlined text-emerald-500 text-[16px]">verified_user</span>
                    Transaksi 100% Aman & Terenkripsi
                </div>
                <div class="flex items-center justify-center gap-1">
                    <span class="px-2 py-1 bg-slate-50 border border-slate-100 rounded text-[10px] font-bold text-slate-400">MIDTRANS</span>
                    <span class="px-2 py-1 bg-slate-50 border border-slate-100 rounded text-[10px] font-bold text-slate-400">QRIS</span>
                    <span class="px-2 py-1 bg-slate-50 border border-slate-100 rounded text-[10px] font-bold text-slate-400">VA</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Decorative right background blob -->
<div class="fixed top-1/4 right-0 w-[500px] h-[500px] bg-primary-100/40 rounded-full blur-[120px] pointer-events-none -z-10 mix-blend-multiply"></div>
<?php endif; ?>

<?php include 'components/footer.php'; ?>

<!-- Custom Confirm Modal (Premium Edition) -->
<div id="confirmModal" class="fixed inset-0 z-[100] hidden items-center justify-center">
    <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity opacity-0" id="confirmOverlay" onclick="closeConfirmModal()"></div>
    <!-- Premium Card -->
    <div class="bg-white/90 backdrop-blur-xl border border-white/60 rounded-[2rem] p-8 w-full max-w-sm mx-4 relative z-10 shadow-[0_20px_60px_-15px_rgba(0,0,0,0.3)] transform scale-95 opacity-0 transition-all duration-300" id="confirmDialog">
        
        <!-- Floating badge -->
        <div class="absolute -top-10 left-1/2 -translate-x-1/2 w-20 h-20 bg-white rounded-2xl shadow-lg border border-slate-50 flex items-center justify-center rotate-12 transition-transform">
            <div class="w-14 h-14 bg-rose-50 rounded-xl flex items-center justify-center text-rose-500 -rotate-12">
                <span class="material-symbols-outlined text-[32px]">delete_sweep</span>
            </div>
        </div>
        
        <div class="pt-10 text-center">
            <h3 class="text-2xl font-extrabold text-accent mb-3 tracking-tight">Hapus Produk?</h3>
            <p class="text-sm text-slate-500 mb-8 leading-relaxed font-medium">Apakah Anda yakin ingin menghapus <strong id="confirmItemName" class="text-primary-700 font-bold block mt-1"></strong> dari keranjang belanja?</p>
            
            <div class="flex flex-col-reverse sm:flex-row gap-3">
                <button type="button" onclick="closeConfirmModal()" class="flex-1 bg-slate-50 hover:bg-slate-100 text-slate-500 hover:text-slate-700 font-bold py-3.5 rounded-2xl transition-all shadow-sm">Batal</button>
                <button type="button" onclick="executeDelete()" class="flex-1 bg-rose-500 hover:bg-rose-600 text-white font-bold py-3.5 rounded-2xl transition-all shadow-[0_8px_20px_rgba(244,63,94,0.3)] hover:shadow-[0_12px_25px_rgba(244,63,94,0.4)]">Ya, Hapus</button>
            </div>
        </div>
    </div>
</div>

<script>
let formToSubmit = null;

function showConfirmModal(form, itemName) {
    formToSubmit = form;
    document.getElementById('confirmItemName').innerText = itemName;
    
    document.body.style.overflow = 'hidden'; // lock scroll
    
    const modal = document.getElementById('confirmModal');
    const overlay = document.getElementById('confirmOverlay');
    const dialog = document.getElementById('confirmDialog');
    
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    
    // Trigger animations
    requestAnimationFrame(() => {
        overlay.classList.remove('opacity-0');
        dialog.classList.remove('scale-95', 'opacity-0');
        dialog.classList.add('scale-100', 'opacity-100');
    });
}

function closeConfirmModal() {
    const modal = document.getElementById('confirmModal');
    const overlay = document.getElementById('confirmOverlay');
    const dialog = document.getElementById('confirmDialog');
    
    document.body.style.overflow = ''; // unlock scroll
    
    overlay.classList.add('opacity-0');
    dialog.classList.remove('scale-100', 'opacity-100');
    dialog.classList.add('scale-95', 'opacity-0');
    
    // wait for animation to finish
    setTimeout(() => {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        formToSubmit = null;
    }, 300);
}

function executeDelete() {
    if (formToSubmit) {
        formToSubmit.submit();
    }
}
</script>
