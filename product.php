<?php include 'components/header.php'; ?>
<?php
require_once __DIR__ . '/config/functions.php';
$pdo = getDB();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = $pdo->prepare('SELECT p.*, c.name AS category_name FROM products p JOIN categories c ON c.id = p.category_id WHERE p.id = ?');
$stmt->execute([$id]);
$product = $stmt->fetch();

if (!$product) {
    echo '<div class="flex flex-col items-center justify-center py-32 bg-white/50 backdrop-blur-sm rounded-[3rem] border border-white/60 shadow-glass mt-10">
            <div class="size-24 bg-slate-100 rounded-full flex items-center justify-center text-slate-400 mb-6 shadow-inner">
                <span class="material-symbols-outlined text-[48px]">search_off</span>
            </div>
            <h3 class="text-2xl font-bold text-accent mb-2">Produk Tidak Ditemukan</h3>
            <p class="text-slate-500 font-medium max-w-md text-center">Maaf, produk yang Anda cari mungkin sudah dihapus atau tidak tersedia.</p>
            <a href="/index.php" class="mt-8 px-6 py-3 bg-accent text-white font-bold rounded-2xl hover:bg-black transition-colors shadow-lg">Kembali ke Beranda</a>
          </div>';
    include 'components/footer.php';
    exit;
}

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
?>

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
    <a href="/index.php" class="hover:text-primary-600 transition-colors flex items-center gap-1"><span class="material-symbols-outlined text-[16px]">home</span> Beranda</a>
    <span class="material-symbols-outlined text-[16px] mx-2 text-slate-300">chevron_right</span>
    <a href="/index.php?category_id=<?= $product['category_id'] ?>" class="hover:text-primary-600 transition-colors"><?= htmlspecialchars($product['category_name']) ?></a>
    <span class="material-symbols-outlined text-[16px] mx-2 text-slate-300">chevron_right</span>
    <span class="text-accent font-bold truncate max-w-[200px] md:max-w-md"><?= htmlspecialchars($product['name']) ?></span>
</div>

<div class="flex flex-col lg:flex-row gap-10 lg:gap-16 bg-white/80 backdrop-blur-3xl rounded-[3rem] p-6 lg:p-10 shadow-glass border border-white/60 relative overflow-hidden">
    <!-- Subtle Background Blob -->
    <div class="absolute -top-40 -right-40 w-96 h-96 bg-primary-100/50 rounded-full blur-[100px] pointer-events-none"></div>

    <!-- Product Image Section -->
    <div class="w-full lg:w-5/12 shrink-0 relative z-10">
        <div class="relative aspect-[4/5] md:aspect-square rounded-[2rem] overflow-hidden bg-slate-50 border border-slate-100/80 shadow-inner group">
            
            <!-- Blurred background copy for premium depth -->
            <div class="absolute inset-0 blur-2xl scale-110 opacity-40 mix-blend-multiply" style="background-image: url('<?= htmlspecialchars($product['image_url'] ?? '') ?>'); background-size: cover; background-position: center;"></div>
            
            <img class="relative z-10 w-full h-full object-contain p-8 mix-blend-multiply transition-transform duration-700 ease-out group-hover:scale-110"
                src="<?= htmlspecialchars($product['image_url'] ?? '') ?>"
                alt="<?= htmlspecialchars($product['name']) ?>"
                loading="lazy"
                onerror="this.src='https://placehold.co/800x800/f8f9fa/94a3b8?text=Produk+ShopEase'">
                
            <?php if ($product['stock'] <= 0): ?>
            <div class="absolute inset-0 z-20 bg-white/50 backdrop-blur-sm flex items-center justify-center transition-all duration-500 delay-300">
                <span class="bg-accent text-white text-lg font-bold px-8 py-3 rounded-full shadow-[0_10px_30px_rgba(0,0,0,0.2)] uppercase tracking-wider">Stok Habis</span>
            </div>
            <?php elseif ($product['stock'] <= 3): ?>
            <span class="absolute top-6 left-6 bg-rose-500/90 backdrop-blur-md text-white text-xs font-extrabold px-4 py-2 rounded-full uppercase tracking-widest shadow-lg z-20 animate-pulse">Sisa <?= $product['stock'] ?></span>
            <?php endif; ?>
        </div>
    </div>

    <!-- Product Info Section -->
    <div class="w-full lg:w-7/12 flex flex-col relative z-10">
        <div class="mb-4">
            <span class="inline-block px-4 py-1.5 bg-primary-50 text-primary-700 text-xs font-bold uppercase tracking-widest rounded-full mb-3 shadow-[0_4px_10px_rgba(128,0,32,0.05)] border border-primary-100/50">
                <?= htmlspecialchars($product['category_name']) ?>
            </span>
            <h1 class="text-3xl md:text-5xl font-extrabold text-accent leading-[1.1] tracking-tight mb-6"><?= htmlspecialchars($product['name']) ?></h1>
            
            <div class="flex flex-wrap items-center gap-4 mb-8">
                <div class="flex items-center gap-2 bg-amber-50 text-amber-700 px-4 py-2 rounded-2xl font-bold text-sm border border-amber-100">
                    <span class="material-symbols-outlined text-[20px] fill-1 text-amber-500">star</span>
                    <?= number_format($product['rating'], 1) ?> <span class="text-amber-700/60 font-medium ml-1">Rating</span>
                </div>
                <div class="flex items-center gap-2 px-4 py-2 rounded-2xl font-semibold text-sm border <?= $product['stock'] > 0 ? 'bg-emerald-50 text-emerald-700 border-emerald-100' : 'bg-red-50 text-red-700 border-red-100' ?>">
                    <span class="material-symbols-outlined text-[20px]"><?= $product['stock'] > 0 ? 'inventory_2' : 'inventory' ?></span>
                    <?= $product['stock'] > 0 ? 'Tersedia ' . $product['stock'] . ' item' : 'Stok Habis' ?>
                </div>
            </div>

            <div class="text-4xl md:text-5xl font-extrabold text-primary-800 tracking-tight mb-8">
                <?= formatRupiah($product['price']) ?>
            </div>
        </div>

        <div class="prose max-w-none text-slate-500 leading-relaxed text-base mb-10 pb-8 border-b border-slate-100/80">
            <h3 class="text-lg font-bold text-accent mb-4">Deskripsi Produk</h3>
            <div class="whitespace-pre-line"><?= htmlspecialchars($product['description'] ?: 'Tidak ada deskripsi tersedia untuk produk ini. Hubungi layanan pelanggan kami untuk informasi lebih lanjut.') ?></div>
        </div>

        <!-- Action Buttons Section -->
        <div class="mt-auto">
            <?php if ($product['stock'] > 0): ?>
            <div class="flex flex-col gap-4">
                
                <form method="POST" action="/cart_action.php" class="flex flex-col sm:flex-row gap-4">
                    <input type="hidden" name="action" value="add">
                    <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                    <input type="hidden" name="redirect" value="/product.php?id=<?= $product['id'] ?>">
                    
                    <!-- Quantity Control -->
                    <div class="flex items-center justify-between border-2 border-slate-100/80 rounded-2xl overflow-hidden bg-white h-14 w-full sm:w-40 shrink-0 shadow-sm focus-within:border-primary-400 focus-within:ring-4 focus-within:ring-primary-50 transition-all">
                        <button type="button" onclick="this.nextElementSibling.stepDown()" class="px-5 text-slate-400 hover:text-primary-600 hover:bg-primary-50 h-full transition-colors flex items-center justify-center"><span class="material-symbols-outlined">remove</span></button>
                        <input type="number" name="quantity" value="1" min="1" max="<?= $product['stock'] ?>" class="w-12 text-center border-none bg-transparent text-lg font-extrabold text-accent focus:ring-0 p-0" required>
                        <button type="button" onclick="this.previousElementSibling.stepUp()" class="px-5 text-slate-400 hover:text-primary-600 hover:bg-primary-50 h-full transition-colors flex items-center justify-center"><span class="material-symbols-outlined">add</span></button>
                    </div>

                    <button type="submit" class="flex-1 bg-white border-2 border-primary-600 text-primary-700 hover:bg-primary-50 font-bold text-lg h-14 rounded-2xl flex items-center justify-center gap-2 transition-all active:scale-[0.98] shadow-sm hover:shadow-glow">
                        <span class="material-symbols-outlined">add_shopping_cart</span>
                        Tambah ke Keranjang
                    </button>
                </form>

                <!-- Beli Langsung (Adds to cart & redirects to checkout) -->
                <form method="POST" action="/cart_action.php" class="w-full">
                    <input type="hidden" name="action" value="add">
                    <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                    <input type="hidden" name="quantity" value="1" id="directBuyQty">
                    <input type="hidden" name="redirect" value="/checkout.php">
                    <button type="submit" class="w-full bg-primary-700 hover:bg-primary-800 text-white font-bold text-lg h-14 rounded-2xl flex items-center justify-center gap-2 transition-all shadow-[0_8px_25px_rgba(128,0,32,0.25)] hover:shadow-[0_12px_30px_rgba(128,0,32,0.35)] hover:-translate-y-1 active:translate-y-0 active:scale-[0.98]" onclick="document.getElementById('directBuyQty').value = document.querySelector('input[name=quantity]').value">
                        Beli Langsung Sekarang <span class="material-symbols-outlined text-[20px]">arrow_forward</span>
                    </button>
                </form>
            </div>
            
            <div class="mt-8 grid grid-cols-2 md:grid-cols-4 gap-4 pt-6 border-t border-slate-100/80 text-center">
                <div class="flex flex-col items-center justify-center text-slate-400">
                    <span class="material-symbols-outlined text-[24px] mb-2 opacity-70">local_shipping</span>
                    <span class="text-[10px] font-bold uppercase tracking-wider">Gratis Ongkir</span>
                </div>
                <div class="flex flex-col items-center justify-center text-slate-400">
                    <span class="material-symbols-outlined text-[24px] mb-2 opacity-70">workspace_premium</span>
                    <span class="text-[10px] font-bold uppercase tracking-wider">Original 100%</span>
                </div>
                <div class="flex flex-col items-center justify-center text-slate-400">
                    <span class="material-symbols-outlined text-[24px] mb-2 opacity-70">verified</span>
                    <span class="text-[10px] font-bold uppercase tracking-wider">Garansi Resmi</span>
                </div>
                <div class="flex flex-col items-center justify-center text-slate-400">
                    <span class="material-symbols-outlined text-[24px] mb-2 opacity-70">currency_exchange</span>
                    <span class="text-[10px] font-bold uppercase tracking-wider">Retur 7 Hari</span>
                </div>
            </div>

            <?php else: ?>
            <div class="bg-slate-50 border-2 border-dashed border-slate-200 rounded-3xl p-8 text-center">
                <div class="size-16 bg-slate-100 rounded-full flex items-center justify-center text-slate-400 mx-auto mb-4">
                    <span class="material-symbols-outlined text-[32px]">production_quantity_limits</span>
                </div>
                <h4 class="text-lg font-bold text-accent mb-2">Mohon maaf, stok sedang habis</h4>
                <p class="text-sm text-slate-500 font-medium mb-6">Tambahkan ke wishlist untuk mendapat notifikasi saat barang kembali tersedia.</p>
                <button type="button" class="px-8 py-3 bg-white border border-slate-200 text-slate-600 font-bold rounded-xl hover:bg-slate-50 hover:text-accent transition-all flex items-center justify-center gap-2 shadow-sm mx-auto group">
                    <span class="material-symbols-outlined text-[20px] group-hover:text-rose-500 transition-colors">favorite</span>
                    Tambah ke Wishlist
                </button>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'components/footer.php'; ?>
