<?php include 'components/header.php'; ?>
<?php
require_once __DIR__ . '/config/functions.php';
$pdo = getDB();

// Get all active categories
$cats = $pdo->query('SELECT * FROM categories WHERE is_active = 1 ORDER BY name')->fetchAll();

// Get selected category
$catId = isset($_GET['category_id']) ? (int)$_GET['category_id'] : 0;
$search = trim($_GET['q'] ?? '');

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$perPage = 12; // Adjusted to 12 for better grid symmetry (3x4 or 4x3)
$offset = ($page - 1) * $perPage;

// Build shared params for filters
$params = [];
if ($catId > 0) $params[] = $catId;
if ($search !== '') $params[] = '%' . $search . '%';

// Build product query for COUNT
$countSql = 'SELECT COUNT(*) FROM products p JOIN categories c ON c.id = p.category_id WHERE p.stock >= 0';
if ($catId > 0) $countSql .= ' AND p.category_id = ?';
if ($search !== '') $countSql .= ' AND p.name LIKE ?';
$countStmt = $pdo->prepare($countSql);
$countStmt->execute($params);
$totalProducts = $countStmt->fetchColumn();
$totalPages = ceil($totalProducts / $perPage);

// Build product query for DATA (same WHERE filters, adds LIMIT/OFFSET)
$sql = 'SELECT p.*, c.name AS category_name FROM products p JOIN categories c ON c.id = p.category_id WHERE p.stock >= 0';
if ($catId > 0) $sql .= ' AND p.category_id = ?';
if ($search !== '') $sql .= ' AND p.name LIKE ?';
$sql .= ' ORDER BY p.id ASC LIMIT ' . $perPage . ' OFFSET ' . $offset;
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

// Get success/error flash
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
?>

<?php if ($flash): ?>
<div class="mb-0 animate-[fade-in-down_0.5s_ease-out]" id="flashMsg">
    <div class="flex items-center gap-3 px-5 py-4 rounded-2xl <?= $flash['type'] === 'success' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200 shadow-[0_8px_30px_rgba(16,185,129,0.12)]' : 'bg-red-50 text-red-700 border border-red-200 shadow-[0_8px_30px_rgba(239,68,68,0.12)]' ?> text-sm font-semibold mb-6">
        <span class="material-symbols-outlined text-[24px]"><?= $flash['type'] === 'success' ? 'check_circle' : 'error' ?></span>
        <?= htmlspecialchars($flash['message']) ?>
    </div>
</div>
<?php endif; ?>

<!-- Premium Interactive Hero Section -->
<section class="mb-16 mt-2 relative rounded-[3rem] overflow-hidden bg-primary-950 shadow-2xl isolate">
    <!-- Deep premium mesh grid background -->
    <div class="absolute inset-0 z-0 opacity-10 bg-[url('data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIyNCIgaGVpZ2h0PSIyNCI+PGNpcmNsZSBjeD0iMSIgY3k9IjEiIHI9IjEiIGZpbGw9IiNmZmYiLz48L3N2Zz4=')]"></div>
    <!-- Rich Orbs & Gradients -->
    <div class="absolute -top-1/2 -right-1/4 w-full h-[150%] bg-gradient-to-b from-primary-600/50 to-transparent rotate-12 blur-3xl rounded-full mix-blend-screen pointer-events-none"></div>
    <div class="absolute bottom-0 left-0 w-full h-1/2 bg-gradient-to-t from-black/80 to-transparent pointer-events-none z-0"></div>

    <div class="relative z-10 flex flex-col md:flex-row items-center px-8 sm:px-12 md:px-20 py-24 min-h-[500px] gap-12">
        <!-- Hero Copy -->
        <div class="flex-1 text-white max-w-xl">
            <div class="inline-flex items-center gap-2 px-4 py-2 bg-white/10 backdrop-blur-md border border-white/20 rounded-full text-[11px] font-bold mb-8 uppercase tracking-[0.2em] shadow-lg">
                <span class="relative flex h-2 w-2">
                  <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-400 opacity-75"></span>
                  <span class="relative inline-flex rounded-full h-2 w-2 bg-rose-500"></span>
                </span>
                Koleksi Baru 2026
            </div>
            <h1 class="text-5xl lg:text-[4rem] font-black mb-6 leading-[1.05] tracking-tight">
                Gaya Premium,<br>
                <span class="text-transparent bg-clip-text bg-gradient-to-r from-rose-200 to-rose-400">Harga Terbaik.</span>
            </h1>
            <p class="text-rose-100/80 text-lg mb-10 max-w-md font-medium leading-relaxed">
                Jelajahi kurasi produk eksklusif kami. Dirancang khusus untuk memenuhi standar gaya hidup modern Anda.
            </p>
            <div class="flex flex-wrap items-center gap-4">
                <a href="#productGrid" class="h-14 px-8 bg-white text-primary-950 hover:text-primary-800 rounded-2xl font-bold flex items-center justify-center gap-3 hover:bg-rose-50 hover:scale-105 active:scale-95 transition-all shadow-[0_0_40px_rgba(255,255,255,0.2)]">
                    Mulai Belanja <span class="material-symbols-outlined text-[20px]">arrow_right_alt</span>
                </a>
                <a href="#categoryFilter" class="h-14 px-8 bg-white/10 backdrop-blur-md text-white border border-white/20 rounded-2xl font-bold flex items-center justify-center hover:bg-white/20 hover:border-white/30 transition-all">
                    Lihat Kategori
                </a>
            </div>
        </div>

        <!-- Abstract visual composition -->
        <div class="flex-1 hidden md:flex items-center justify-center relative w-full h-full">
            <div class="relative w-[300px] h-[400px]">
                <!-- Floating Glass Panes -->
                <div class="absolute top-0 right-0 w-64 h-80 bg-gradient-to-tr from-white/10 to-white/5 backdrop-blur-2xl border border-white/20 rounded-[2.5rem] shadow-2xl transform rotate-6 animate-[float_6s_ease-in-out_infinite]"></div>
                <div class="absolute bottom-0 left-0 w-56 h-72 bg-gradient-to-bl from-rose-500/20 to-primary-600/40 backdrop-blur-2xl border border-white/20 rounded-[2.5rem] shadow-2xl transform -rotate-12 animate-[float_5s_ease-in-out_infinite_reverse]"></div>
                <!-- Mini product card mock -->
                <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-52 bg-white/95 backdrop-blur-xl rounded-[2rem] p-4 shadow-2xl transform rotate-3 hover:rotate-0 hover:scale-105 transition-all duration-500 cursor-default">
                    <div class="w-full h-36 bg-slate-100 rounded-2xl mb-4 overflow-hidden relative">
                         <img src="https://placehold.co/400x400/f8f9fa/94a3b8?text=Produk+ShopEase" alt="Mock" class="w-full h-full object-cover mix-blend-multiply">
                         <div class="absolute top-2 left-2 size-6 rounded-full bg-primary-600 border-2 border-white shadow-sm flex items-center justify-center">
                             <span class="material-symbols-outlined text-white text-[12px]">favorite</span>
                         </div>
                    </div>
                    <div class="h-2.5 w-3/4 bg-slate-200 rounded-full mb-2.5"></div>
                    <div class="h-2.5 w-1/2 bg-slate-200 rounded-full mb-4"></div>
                    <div class="h-8 w-full bg-primary-50 rounded-xl flex items-center justify-center border border-primary-100">
                        <span class="text-[10px] font-black tracking-widest text-primary-700 uppercase">Tambah</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Modern Interactive Filter Pills -->
<section class="mb-12 w-full" id="categoryFilter">
    <div class="flex items-center justify-between mb-6">
        <h3 class="text-2xl font-extrabold text-accent flex items-center gap-2 tracking-tight">
            <span class="material-symbols-outlined text-primary-600">category</span>
            <?= $search ? 'Menampilkan: "' . htmlspecialchars($search) . '"' : 'Kategori Pilihan' ?>
        </h3>
        <?php if ($search): ?>
        <a href="/index.php" class="text-sm font-bold text-slate-500 hover:text-rose-600 transition-colors flex items-center gap-1 bg-slate-100 hover:bg-rose-50 px-3 py-1.5 rounded-xl">
            <span class="material-symbols-outlined text-[18px]">close</span> Bersihkan
        </a>
        <?php endif; ?>
    </div>
    
    <div class="flex items-center gap-3 overflow-x-auto no-scrollbar pb-4 -mx-4 px-4 sm:mx-0 sm:px-0">
        <a href="/index.php" class="flex h-12 shrink-0 items-center justify-center rounded-2xl px-7 text-sm font-bold transition-all duration-300 <?= $catId === 0 && empty($search) ? 'bg-primary-800 text-white shadow-lg shadow-primary-900/20' : 'bg-white border border-slate-200 text-slate-600 hover:border-slate-300 hover:bg-slate-50 hover:text-accent' ?>">
            Semua Koleksi
        </a>
        <?php foreach ($cats as $cat): ?>
        <a href="/index.php?category_id=<?= $cat['id'] ?>" class="flex h-12 shrink-0 items-center justify-center rounded-2xl px-7 text-sm font-bold transition-all duration-300 <?= $catId === (int)$cat['id'] ? 'bg-primary-800 text-white shadow-lg shadow-primary-900/20' : 'bg-white border border-slate-200 text-slate-600 hover:border-slate-300 hover:bg-slate-50 hover:text-accent' ?>">
            <span class="material-symbols-outlined text-[18px] mr-2 opacity-70"><?= htmlspecialchars($cat['icon'] ?? 'checkroom') ?></span>
            <?= htmlspecialchars($cat['name']) ?>
        </a>
        <?php endforeach; ?>
    </div>
</section>

<!-- Product Grid: Apple-style large cards with glass effects -->
<section id="productGrid" style="view-transition-name: product-grid;">
    <?php if (empty($products)): ?>
    <div class="flex flex-col items-center justify-center py-32 bg-white/50 backdrop-blur-sm rounded-[3rem] border border-white/60 shadow-glass">
        <div class="size-24 bg-slate-100 rounded-full flex items-center justify-center text-slate-400 mb-6 shadow-inner">
            <span class="material-symbols-outlined text-[48px]">search_off</span>
        </div>
        <h3 class="text-2xl font-bold text-accent mb-2">Tidak Ditemukan</h3>
        <p class="text-slate-500 font-medium max-w-md text-center">Maaf, kami tidak menemukan produk yang cocok dengan pencarian atau filter Anda saat ini.</p>
        <a href="/index.php" class="mt-8 px-6 py-3 bg-accent text-white font-bold rounded-2xl hover:bg-black transition-colors shadow-lg">Kembali ke Semua Produk</a>
    </div>
    <?php else: ?>
    
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6 md:gap-8">
        <?php foreach ($products as $p): ?>
        <div class="group flex flex-col bg-white rounded-3xl overflow-hidden hover:shadow-[0_20px_40px_-15px_rgba(128,0,32,0.12)] transition-all duration-500 hover:-translate-y-2 border border-slate-100/60 p-2">
            
            <!-- Image Container -->
            <a href="/product.php?id=<?= $p['id'] ?>" class="relative aspect-[4/5] w-full rounded-2xl overflow-hidden bg-slate-50 mb-4 block isolate">
                <img class="w-full h-full object-cover transition-transform duration-700 ease-out group-hover:scale-105"
                    src="<?= htmlspecialchars($p['image_url'] ?? '') ?>"
                    alt="<?= htmlspecialchars($p['name']) ?>"
                    loading="lazy"
                    onerror="this.src='https://placehold.co/600x750/f8f9fa/94a3b8?text=Produk+ShopEase'">
                
                <!-- Overlay shadow for add button visibility -->
                <div class="absolute inset-0 bg-gradient-to-t from-black/40 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500 rounded-2xl pointer-events-none"></div>

                <!-- Hover Add to Cart Button -->
                <div class="absolute bottom-4 right-4 z-20 translate-y-4 opacity-0 group-hover:translate-y-0 group-hover:opacity-100 transition-all duration-500 ease-out">
                    <form action="/cart_action.php" method="POST" class="inline">
                        <input type="hidden" name="action" value="add">
                        <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
                        <input type="hidden" name="quantity" value="1">
                        <button type="submit" class="size-12 bg-white/95 backdrop-blur-md text-primary-800 rounded-full flex items-center justify-center shadow-[0_8px_20px_rgba(0,0,0,0.2)] hover:bg-primary-800 hover:text-white hover:scale-110 active:scale-95 transition-all duration-300" title="Tambah ke Keranjang">
                            <span class="material-symbols-outlined font-bold text-[22px]">add_shopping_cart</span>
                        </button>
                    </form>
                </div>

                <!-- Stock Badges -->
                <?php if ($p['stock'] <= 0): ?>
                <div class="absolute inset-0 z-20 bg-slate-900/40 backdrop-blur-[2px] flex items-center justify-center rounded-2xl">
                    <span class="bg-white text-slate-900 text-xs font-black px-5 py-2.5 rounded-full uppercase tracking-widest shadow-2xl">Habis Terjual</span>
                </div>
                <?php elseif ($p['stock'] <= 3): ?>
                <div class="absolute top-3 left-3 z-20">
                    <span class="bg-rose-500 text-white text-[10px] font-black px-3 py-1.5 rounded-full uppercase tracking-widest shadow-md">Sisa <?= $p['stock'] ?></span>
                </div>
                <?php endif; ?>
            </a>

            <!-- Content Container -->
            <a href="/product.php?id=<?= $p['id'] ?>" class="flex flex-col grow px-3 pb-3">
                <div class="flex items-center gap-2 mb-2.5">
                    <span class="text-[10px] font-extrabold text-primary-600 bg-primary-50 px-2.5 py-1 rounded-md uppercase tracking-widest"><?= htmlspecialchars($p['category_name']) ?></span>
                    <div class="flex items-center gap-0.5 ml-auto text-amber-500">
                        <span class="material-symbols-outlined text-[14px] fill-1">star</span>
                        <span class="text-[11px] font-bold text-slate-500 pt-0.5"><?= number_format($p['rating'], 1) ?></span>
                    </div>
                </div>
                
                <h4 class="text-accent font-extrabold text-[17px] leading-snug mb-3 line-clamp-2 group-hover:text-primary-800 transition-colors"><?= htmlspecialchars($p['name']) ?></h4>
                
                <div class="mt-auto pt-1">
                    <p class="text-slate-400 font-bold text-[10px] uppercase tracking-wider mb-0.5">Harga Pembelian</p>
                    <p class="text-primary-900 font-black text-xl tracking-tight"><?= formatRupiah($p['price']) ?></p>
                </div>
            </a>
        </div>
        <?php endforeach; ?>
    </div>
    
    <!-- Premium Pagination UI -->
    <?php if ($totalPages > 1): ?>
    <div class="mt-16 flex justify-center">
        <nav class="inline-flex items-center p-1.5 bg-white/80 backdrop-blur-xl rounded-[2rem] border border-white shadow-glass" aria-label="Pagination">
            <?php 
                $queryStr = $_GET;
                unset($queryStr['page']); 
                $baseUrl = '/index.php?' . http_build_query($queryStr);
                $baseUrl .= (empty($queryStr) ? '' : '&') . 'page=';
            ?>
            
            <a href="<?= $page > 1 ? $baseUrl . ($page - 1) : '#' ?>" class="size-10 flex items-center justify-center rounded-full transition-all <?= $page > 1 ? 'text-slate-600 hover:bg-slate-100' : 'text-slate-300 cursor-not-allowed pointer-events-none' ?>">
                <span class="material-symbols-outlined text-[20px]">arrow_back</span>
            </a>
            
            <div class="flex items-center gap-1 mx-2">
                <?php for($i = 1; $i <= $totalPages; $i++): ?>
                    <?php if ($i == 1 || $i == $totalPages || ($i >= $page - 1 && $i <= $page + 1)): ?>
                        <a href="<?= $baseUrl . $i ?>" class="min-w-[40px] h-10 flex items-center justify-center rounded-full text-sm font-bold transition-all <?= $i == $page ? 'bg-accent text-white shadow-md' : 'text-slate-600 hover:bg-slate-100 hover:text-accent' ?>">
                            <?= $i ?>
                        </a>
                    <?php elseif ($i == $page - 2 || $i == $page + 2): ?>
                        <span class="size-10 flex items-center justify-center text-slate-400 font-bold">...</span>
                    <?php endif; ?>
                <?php endfor; ?>
            </div>
            
            <a href="<?= $page < $totalPages ? $baseUrl . ($page + 1) : '#' ?>" class="size-10 flex items-center justify-center rounded-full transition-all <?= $page < $totalPages ? 'text-slate-600 hover:bg-slate-100' : 'text-slate-300 cursor-not-allowed pointer-events-none' ?>">
                <span class="material-symbols-outlined text-[20px]">arrow_forward</span>
            </a>
        </nav>
    </div>
    <?php endif; ?>
    
    <?php endif; ?>
</section>

<?php include 'components/footer.php'; ?>

<!-- View Transitions Script -->
<style>
::view-transition-old(product-grid) { animation: fade-out-down 0.2s cubic-bezier(0.4, 0, 1, 1) both; }
::view-transition-new(product-grid) { animation: fade-in-up 0.4s cubic-bezier(0, 0, 0.2, 1) both; }
@keyframes fade-out-down { to { opacity: 0; transform: translateY(10px); } }
@keyframes fade-in-up { from { opacity: 0; transform: translateY(20px); } }
@keyframes fade-in-down { from { opacity: 0; transform: translateY(-20px); } to { opacity: 1; transform: translateY(0); } }
</style>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const filterContainer = document.getElementById('categoryFilter');
    if (!filterContainer) return;

    filterContainer.addEventListener('click', async (e) => {
        const link = e.target.closest('a');
        if (!link) return;
        
        e.preventDefault();
        const url = link.href;
        window.history.pushState({}, '', url);

        try {
            const response = await fetch(url);
            const html = await response.text();
            const doc = new DOMParser().parseFromString(html, 'text/html');
            
            const newFilter = doc.getElementById('categoryFilter').innerHTML;
            const newGrid = doc.getElementById('productGrid').innerHTML;

            // Optional view transition API if supported by browser
            if (document.startViewTransition) {
                document.startViewTransition(() => {
                    document.getElementById('categoryFilter').innerHTML = newFilter;
                    document.getElementById('productGrid').innerHTML = newGrid;
                });
            } else {
                document.getElementById('categoryFilter').innerHTML = newFilter;
                document.getElementById('productGrid').innerHTML = newGrid;
            }
        } catch (err) {
            console.error('Fetch error:', err);
            window.location.href = url; // Hard fallback
        }
    });
});
</script>
