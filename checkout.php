<?php include 'components/header.php'; ?>
<?php
require_once __DIR__ . '/config/functions.php';
$pdo    = getDB();
$userId = $_SESSION['user_id'];
$user   = getCurrentUser();

// Generate Anti-Double Order Token
if (empty($_SESSION['checkout_token'])) {
    $_SESSION['checkout_token'] = bin2hex(random_bytes(32));
}
$checkoutToken = $_SESSION['checkout_token'];

// Fetch cart items
$stmt = $pdo->prepare(
    'SELECT ci.*, p.name, p.image_url, p.price, p.stock
     FROM cart_items ci
     JOIN products p ON p.id = ci.product_id
     WHERE ci.user_id = ?'
);
$stmt->execute([$userId]);
$items = $stmt->fetchAll();

if (empty($items)) {
    header('Location: /cart.php');
    exit;
}

$subtotal = 0;
foreach ($items as $i) $subtotal += $i['price'] * $i['quantity'];
$shipping = 25000;
$fee      = 2500;
$ppn      = $subtotal * 0.11; // PPN 11% dari Total Barang
$total    = $subtotal + $shipping + $fee + $ppn;
?>

<!-- Breadcrumb -->
<div class="mb-8 flex items-center text-sm text-slate-500 font-medium relative z-10">
    <a href="/index.php" class="hover:text-primary-600 transition-colors flex items-center gap-1"><span class="material-symbols-outlined text-[16px]">home</span> Beranda</a>
    <span class="material-symbols-outlined text-[16px] mx-2 text-slate-300">chevron_right</span>
    <a href="/cart.php" class="hover:text-primary-600 transition-colors">Keranjang</a>
    <span class="material-symbols-outlined text-[16px] mx-2 text-slate-300">chevron_right</span>
    <span class="text-accent font-bold">Checkout</span>
</div>

<div class="mb-8 relative z-10">
    <h1 class="text-3xl md:text-4xl font-extrabold text-accent tracking-tight">Checkout Pesanan</h1>
    <p class="text-slate-500 font-medium mt-2">Selesaikan pembayaran Anda untuk memproses pesanan.</p>
</div>

<form method="POST" action="/process_checkout.php" id="checkoutForm" onsubmit="return handleCheckoutSubmit(this)" class="relative z-10">
    <input type="hidden" name="checkout_token" value="<?= htmlspecialchars($checkoutToken) ?>">
    
    <div class="flex flex-col lg:flex-row gap-8 items-start">
        <!-- Form Kiri -->
        <div class="w-full lg:w-2/3 space-y-6">
            
            <!-- Alamat Pengiriman -->
            <div class="bg-white/80 backdrop-blur-xl rounded-[2rem] border border-white/60 p-6 md:p-8 shadow-glass group">
                <h3 class="text-xl font-bold mb-6 flex items-center gap-3 text-accent border-b border-slate-100 pb-4">
                    <div class="w-10 h-10 rounded-xl bg-primary-50 text-primary-600 flex items-center justify-center shadow-inner group-hover:scale-110 transition-transform">
                        <span class="material-symbols-outlined">location_on</span>
                    </div>
                    Informasi Pengiriman
                </h3>
                
                <div class="space-y-5">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2">Nama Penerima</label>
                            <input type="text" name="recipient_name" value="<?= htmlspecialchars($user['name'] ?? '') ?>"
                                class="w-full h-12 rounded-xl border-slate-200 bg-slate-50/50 focus:bg-white focus:ring-4 focus:ring-primary-50 focus:border-primary-400 transition-all shadow-sm px-4 font-medium"
                                minlength="3" maxlength="100" pattern="^[a-zA-Z\s]+$" title="Nama hanya boleh berisi huruf dan spasi" required>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2">Nomor Telepon</label>
                            <input type="tel" name="phone" value="<?= htmlspecialchars($user['phone'] ?? '') ?>"
                                class="w-full h-12 rounded-xl border-slate-200 bg-slate-50/50 focus:bg-white focus:ring-4 focus:ring-primary-50 focus:border-primary-400 transition-all shadow-sm px-4 font-medium"
                                placeholder="08..." pattern="^08[0-9]{8,11}$" title="Nomor telepon harus diawali 08 dan berisi 10-13 digit angka" required>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">Alamat Lengkap</label>
                        <textarea name="address" rows="3" required
                            class="w-full rounded-xl border-slate-200 bg-slate-50/50 focus:bg-white focus:ring-4 focus:ring-primary-50 focus:border-primary-400 transition-all shadow-sm p-4 font-medium resize-none"
                            minlength="10" title="Alamat harus diisi minimal 10 karakter untuk kejelasan rute"
                            placeholder="Jl. Nama Jalan No. 123, Rt/Rw, Kelurahan, Kecamatan, Kota, Provinsi, Kode Pos..."></textarea>
                    </div>
                </div>
            </div>

            <!-- Metode Pembayaran -->
            <div class="bg-white/80 backdrop-blur-xl rounded-[2rem] border border-white/60 p-6 md:p-8 shadow-glass group">
                <h3 class="text-xl font-bold mb-6 flex items-center gap-3 text-accent border-b border-slate-100 pb-4">
                    <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shadow-inner group-hover:scale-110 transition-transform">
                        <span class="material-symbols-outlined">payments</span>
                    </div>
                    Metode Pembayaran
                </h3>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <?php 
                    $paymentMethods = [
                        'transfer' => ['Transfer Bank (VA)', 'account_balance', 'bg-blue-50 text-blue-600'], 
                        'ewallet' => ['E-Wallet (QRIS/GoPay)', 'qr_code_scanner', 'bg-emerald-50 text-emerald-600'], 
                        'card' => ['Kartu Kredit/Debit', 'credit_card', 'bg-indigo-50 text-indigo-600'], 
                        'cod' => ['Bayar di Tempat (COD)', 'local_shipping', 'bg-amber-50 text-amber-600']
                    ];
                    foreach ($paymentMethods as $val => [$label, $icon, $iconBg]): 
                    ?>
                    <label class="relative flex items-center p-4 border-2 border-slate-100 rounded-2xl cursor-pointer hover:border-primary-300 hover:bg-primary-50/30 transition-all group/radio has-[:checked]:border-primary-600 has-[:checked]:bg-primary-50">
                        <input type="radio" name="payment_method" value="<?= $val ?>" class="shrink-0 w-5 h-5 text-primary-600 focus:ring-primary-600 border-slate-300 focus:ring-offset-0" <?= $val === 'transfer' ? 'checked' : '' ?>>
                        <div class="ml-4 flex-1 flex items-center justify-between">
                            <span class="font-bold text-slate-700 group-hover/radio:text-primary-800 transition-colors"><?= $label ?></span>
                            <div class="w-8 h-8 rounded-lg <?= $iconBg ?> shrink-0 flex items-center justify-center">
                                <span class="material-symbols-outlined text-[18px]"><?= $icon ?></span>
                            </div>
                        </div>
                    </label>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <div class="bg-primary-50/50 border border-primary-100 rounded-2xl p-4 flex gap-3 text-sm text-primary-800 font-medium">
                <span class="material-symbols-outlined text-primary-600 shrink-0">info</span>
                <p>Klik tombol <strong>Pesan Sekarang</strong> di bawah untuk melanjutkan ke halaman pembayaran yang aman.</p>
            </div>
        </div>

        <!-- Ringkasan Kanan -->
        <div class="w-full lg:w-1/3">
            <div class="bg-white/80 backdrop-blur-xl rounded-[2.5rem] border border-white/60 p-6 md:p-8 shadow-glass sticky top-28">
                <h3 class="text-xl font-bold text-accent mb-6 flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary-600">receipt_long</span>
                    Ringkasan Pesanan
                </h3>
                
                <!-- Items list (scrollable if too many) -->
                <div class="space-y-4 mb-6 max-h-[300px] overflow-y-auto pr-2 custom-scrollbar">
                    <?php foreach ($items as $item): ?>
                    <div class="flex gap-4 items-center p-3 rounded-2xl hover:bg-slate-50 transition-colors">
                        <div class="size-16 rounded-xl bg-slate-100 border border-slate-200 overflow-hidden shrink-0">
                            <img src="<?= htmlspecialchars($item['image_url'] ?? '') ?>" alt="<?= htmlspecialchars($item['name']) ?>"
                                class="w-full h-full object-cover mix-blend-multiply" loading="lazy"
                                onerror="this.src='https://placehold.co/64x64/f8f9fa/94a3b8?text=?';">
                        </div>
                        <div class="flex-1 min-w-0">
                            <h4 class="text-sm font-bold text-accent truncate"><?= htmlspecialchars($item['name']) ?></h4>
                            <p class="text-xs text-slate-500 font-medium mt-1 text-primary-600"><?= $item['quantity'] ?> x <?= formatRupiah($item['price']) ?></p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="h-px w-full bg-slate-200 border-dashed border-b border-slate-300 my-6"></div>
                
                <div class="space-y-3 text-sm font-medium mb-8">
                    <div class="flex justify-between items-center text-slate-500">
                        <span>Subtotal (<?= count($items) ?> Barang)</span>
                        <span class="text-accent"><?= formatRupiah($subtotal) ?></span>
                    </div>
                    <div class="flex justify-between items-center text-slate-500">
                        <span>Ongkos Kirim</span>
                        <span class="text-accent"><?= formatRupiah($shipping) ?></span>
                    </div>
                    <div class="flex justify-between items-center text-slate-500">
                        <span>Pajak (PPN 11%)</span>
                        <span class="text-accent">+<?= formatRupiah($ppn) ?></span>
                    </div>
                    <div class="flex justify-between items-center text-slate-500">
                        <span>Biaya Layanan</span>
                        <span class="text-accent">+<?= formatRupiah($fee) ?></span>
                    </div>
                </div>
                
                <div class="flex justify-between items-end mb-8 pt-4 border-t border-slate-200">
                    <span class="text-base font-bold text-slate-500">Total Tagihan</span>
                    <span class="text-3xl font-extrabold text-primary-800 tracking-tight"><?= formatRupiah($total) ?></span>
                </div>
                
                <input type="hidden" name="grand_total" value="<?= $total ?>">
                <button type="submit" id="submitBtn" class="w-full relative inline-flex items-center justify-center gap-2 group px-8 py-4 bg-primary-700 text-white font-bold rounded-2xl hover:bg-primary-800 transition-all shadow-[0_8px_25px_rgba(128,0,32,0.25)] hover:shadow-[0_12px_30px_rgba(128,0,32,0.35)] hover:-translate-y-1 overflow-hidden">
                    <div class="absolute inset-0 bg-white/20 translate-y-full group-hover:translate-y-0 transition-transform duration-300 ease-out"></div>
                    <span id="submitText" class="relative z-10 text-lg">Pesan Sekarang</span>
                    <span id="submitIcon" class="relative z-10 material-symbols-outlined text-[20px] group-hover:scale-110 transition-transform">lock</span>
                    <span id="loadingIcon" class="relative z-10 material-symbols-outlined text-[20px] hidden animate-spin">refresh</span>
                </button>
            </div>
        </div>
    </div>
</form>

<!-- Decorative Background Blob -->
<div class="fixed top-1/4 left-0 w-[600px] h-[600px] bg-primary-100/30 rounded-full blur-[120px] pointer-events-none -z-10 mix-blend-multiply"></div>

<style>
/* Custom Scrollbar for items list */
.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background-color: #e2e8f0;
    border-radius: 20px;
}
.custom-scrollbar:hover::-webkit-scrollbar-thumb {
    background-color: #cbd5e1;
}
</style>

<script>
function handleCheckoutSubmit(form) {
    if (!form.checkValidity()) return false;
    
    // Disable button to prevent double-click
    const btn = document.getElementById('submitBtn');
    btn.disabled = true;
    btn.classList.add('opacity-80', 'cursor-not-allowed', 'scale-[0.98]');
    btn.classList.remove('hover:-translate-y-1', 'hover:shadow-[0_12px_30px_rgba(128,0,32,0.35)]');
    
    // Change text & icon
    document.getElementById('submitText').innerText = 'Memproses Pesanan...';
    document.getElementById('submitIcon').classList.add('hidden');
    document.getElementById('loadingIcon').classList.remove('hidden');
    
    return true; // continue submit
}
</script>

<?php include 'components/footer.php'; ?>
