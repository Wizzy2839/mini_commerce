<?php include 'components/admin_header.php'; ?>
<?php
require_once __DIR__ . '/../config/functions.php';
$pdo = getDB();

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

// Fetch products with category name
$search   = trim($_GET['q'] ?? '');
$filterCat = (int)($_GET['cat'] ?? 0);
$sql    = 'SELECT p.*, c.name AS category_name FROM products p JOIN categories c ON c.id = p.category_id WHERE 1=1';
$params = [];
if ($search !== '') { $sql .= ' AND p.name LIKE ?'; $params[] = '%'.$search.'%'; }
if ($filterCat > 0) { $sql .= ' AND p.category_id = ?'; $params[] = $filterCat; }
$sql .= ' ORDER BY p.id DESC';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

$categories = $pdo->query('SELECT * FROM categories WHERE is_active = 1 ORDER BY name')->fetchAll();

// Edit mode?
$editProduct = null;
if (isset($_GET['edit'])) {
    $editStmt = $pdo->prepare('SELECT * FROM products WHERE id = ?');
    $editStmt->execute([(int)$_GET['edit']]);
    $editProduct = $editStmt->fetch();
}
?>

<?php if ($flash): ?>
<div class="flex items-start gap-3 px-4 py-3 rounded-2xl mb-6 text-sm font-bold shadow-sm <?= $flash['type'] === 'success' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-rose-50 text-rose-700 border border-rose-200' ?>">
    <span class="material-symbols-outlined shrink-0"><?= $flash['type'] === 'success' ? 'check_circle' : 'error' ?></span>
    <span class="mt-0.5"><?= htmlspecialchars($flash['message']) ?></span>
</div>
<?php endif; ?>

<div class="mb-6 flex flex-col sm:flex-row sm:items-end justify-between gap-4">
    <div>
        <h2 class="text-3xl font-extrabold text-accent tracking-tight">Katalog Produk</h2>
        <p class="text-slate-500 font-medium mt-2">Kelola inventaris, sesuaikan harga, dan pantau stok barang Anda.</p>
    </div>
    <button onclick="openModal('addModal')" class="flex items-center gap-2 bg-primary-800 hover:bg-primary-900 text-white px-5 py-2.5 rounded-xl font-bold shadow-lg shadow-primary-900/20 shrink-0 transition-transform active:scale-95">
        <span class="material-symbols-outlined text-[20px]">add</span> Produk Baru
    </button>
</div>

<!-- Search & filter -->
<div class="bg-white p-4 rounded-t-[2rem] border border-b-0 border-slate-100 flex flex-col md:flex-row items-center gap-4 mt-4 shadow-[0_8px_30px_rgba(0,0,0,0.02)] relative z-10">
    <form method="GET" class="flex flex-col sm:flex-row items-center gap-3 flex-1 w-full">
        <div class="relative flex-1 w-full">
            <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-slate-400">search</span>
            <input name="q" value="<?= htmlspecialchars($search) ?>" class="w-full h-12 pl-12 pr-4 bg-slate-50/50 border-slate-200 rounded-xl text-sm font-medium focus:bg-white focus:ring-4 focus:ring-primary-50 focus:border-primary-300 transition-all placeholder:text-slate-400" placeholder="Cari nama produk...">
        </div>
        <div class="flex items-center gap-3 w-full sm:w-auto">
            <select name="cat" class="h-12 border-slate-200 bg-slate-50/50 rounded-xl px-4 py-2 text-sm font-medium focus:bg-white focus:ring-4 focus:ring-primary-50 focus:border-primary-300 cursor-pointer transition-all w-full sm:w-48">
                <option value="0">Semua Kategori</option>
                <?php foreach ($categories as $cat): ?>
                <option value="<?= $cat['id'] ?>" <?= $filterCat === (int)$cat['id'] ? 'selected' : '' ?>><?= htmlspecialchars($cat['name']) ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="h-12 px-6 bg-slate-800 text-white rounded-xl text-sm font-bold hover:bg-slate-900 transition-colors shadow-md shadow-slate-800/20 whitespace-nowrap">Terapkan</button>
        </div>
    </form>
</div>

<!-- Products Table -->
<div class="bg-white rounded-b-[2rem] border border-slate-100 shadow-[0_8px_30px_rgba(0,0,0,0.04)] overflow-hidden mb-8">
    <div class="overflow-x-auto p-2">
        <table class="w-full text-left border-collapse whitespace-nowrap">
            <thead>
                <tr>
                    <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100">Info Produk</th>
                    <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100">Harga</th>
                    <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100">Kategori</th>
                    <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100">Stok</th>
                    <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                <?php foreach ($products as $p): ?>
                <tr class="hover:bg-slate-50/80 transition-colors group">
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-4">
                            <div class="size-14 rounded-2xl bg-slate-100 border border-slate-200 shrink-0 overflow-hidden relative group-hover:shadow-md transition-shadow">
                                <img src="<?= htmlspecialchars($p['image_url'] ?? '') ?>"
                                    class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110"
                                    loading="lazy"
                                    onerror="this.src='https://placehold.co/100x100/f8fafc/94a3b8?text=No+Img';">
                            </div>
                            <div>
                                <p class="font-bold text-slate-800 text-base"><?= htmlspecialchars($p['name']) ?></p>
                                <p class="text-[11px] font-bold text-slate-400 mt-1 tracking-widest uppercase">ID: <?= str_pad($p['id'],4,'0',STR_PAD_LEFT) ?></p>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 font-extrabold text-primary-800"><?= formatRupiah($p['price']) ?></td>
                    <td class="px-6 py-4">
                        <span class="px-3 py-1.5 bg-slate-100 text-slate-600 rounded-lg text-xs font-bold border border-slate-200"><?= htmlspecialchars($p['category_name']) ?></span>
                    </td>
                    <td class="px-6 py-4">
                        <?php if ($p['stock'] <= 0): ?>
                            <span class="inline-flex items-center gap-1 text-rose-700 font-bold bg-rose-50 px-3 py-1.5 rounded-lg text-xs border border-rose-100">
                                <span class="w-1.5 h-1.5 rounded-full bg-rose-500 animate-pulse"></span> Habis
                            </span>
                        <?php elseif ($p['stock'] <= 5): ?>
                            <span class="inline-flex items-center gap-1 text-orange-700 font-bold bg-orange-50 px-3 py-1.5 rounded-lg text-xs border border-orange-100">
                                <span class="w-1.5 h-1.5 rounded-full bg-orange-500"></span> <?= $p['stock'] ?> Sisa
                            </span>
                        <?php else: ?>
                            <span class="inline-flex items-center gap-1 text-emerald-700 font-bold bg-emerald-50 px-3 py-1.5 rounded-lg text-xs border border-emerald-100">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> <?= $p['stock'] ?> Unit
                            </span>
                        <?php endif; ?>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                            <a href="?edit=<?= $p['id'] ?>" class="p-2 text-slate-400 hover:text-primary-600 hover:bg-primary-50 rounded-xl transition-colors" title="Edit">
                                <span class="material-symbols-outlined text-[20px]">edit</span>
                            </a>
                            <form method="POST" action="product_action.php" onsubmit="return confirm('Hapus produk secara permanen?')">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= $p['id'] ?>">
                                <button type="submit" class="p-2 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-xl transition-colors" title="Hapus">
                                    <span class="material-symbols-outlined text-[20px]">delete</span>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($products)): ?>
                <tr>
                    <td colspan="5" class="py-20 text-center">
                        <div class="inline-flex flex-col items-center justify-center text-slate-400">
                            <span class="material-symbols-outlined text-[48px] mb-4 opacity-50">inventory_2</span>
                            <p class="font-medium text-lg">Tidak ada produk ditemukan.</p>
                            <p class="text-sm mt-1 opacity-75">Coba gunakan kata kunci pencarian yang lain.</p>
                        </div>
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add Product Modal -->
<div id="addModal" class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-[100] items-center justify-center hidden opacity-0 transition-opacity duration-300">
    <div class="bg-white rounded-[2rem] shadow-2xl border border-slate-100 p-8 w-full max-w-xl mx-4 transform scale-95 transition-transform duration-300 relative overflow-hidden">
        <!-- Decoration -->
        <div class="absolute top-0 right-0 w-32 h-32 bg-primary-50 rounded-full blur-3xl -z-10 translate-x-1/2 -translate-y-1/2"></div>
        
        <div class="flex justify-between items-center mb-6 relative z-10">
            <h3 class="text-2xl font-extrabold text-accent">Tambah Produk Baru</h3>
            <button onclick="closeModal('addModal')" class="w-8 h-8 flex items-center justify-center bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-full transition-colors">
                <span class="material-symbols-outlined text-[20px]">close</span>
            </button>
        </div>
        
        <form method="POST" action="product_action.php" class="space-y-5 relative z-10">
            <input type="hidden" name="action" value="create">
            
            <div>
                <label class="block text-sm font-bold text-slate-700 mb-2">Nama Produk</label>
                <input type="text" name="name" class="w-full h-12 px-4 rounded-xl border-slate-200 bg-slate-50/50 focus:bg-white focus:ring-4 focus:ring-primary-50 focus:border-primary-300 transition-all text-sm font-medium" placeholder="Contoh: Jam Tangan Elegan" required>
            </div>
            
            <div class="grid grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Harga (Rp)</label>
                    <input type="number" name="price" min="0" class="w-full h-12 px-4 rounded-xl border-slate-200 bg-slate-50/50 focus:bg-white focus:ring-4 focus:ring-primary-50 focus:border-primary-300 transition-all text-sm font-medium" placeholder="0" required>
                </div>
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Stok Awal</label>
                    <input type="number" name="stock" min="0" class="w-full h-12 px-4 rounded-xl border-slate-200 bg-slate-50/50 focus:bg-white focus:ring-4 focus:ring-primary-50 focus:border-primary-300 transition-all text-sm font-medium" placeholder="0" required>
                </div>
            </div>
            
            <div class="grid grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Kategori</label>
                    <select name="category_id" class="w-full h-12 px-4 rounded-xl border-slate-200 bg-slate-50/50 focus:bg-white focus:ring-4 focus:ring-primary-50 focus:border-primary-300 transition-all text-sm font-medium" required>
                        <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">URL Gambar Eksternal</label>
                    <input type="text" name="image_url" class="w-full h-12 px-4 rounded-xl border-slate-200 bg-slate-50/50 focus:bg-white focus:ring-4 focus:ring-primary-50 focus:border-primary-300 transition-all text-sm font-medium" placeholder="https://...">
                </div>
            </div>
            
            <div>
                <label class="block text-sm font-bold text-slate-700 mb-2">Deskripsi Produk <span class="text-xs font-medium text-slate-400 font-normal">(Opsional)</span></label>
                <textarea name="description" rows="3" class="w-full p-4 rounded-xl border-slate-200 bg-slate-50/50 focus:bg-white focus:ring-4 focus:ring-primary-50 focus:border-primary-300 resize-none transition-all text-sm font-medium" placeholder="Tuliskan deskripsi lengkap..."></textarea>
            </div>
            
            <div class="flex gap-3 pt-4 border-t border-slate-100">
                <button type="button" onclick="closeModal('addModal')" class="flex-1 h-12 border border-slate-200 text-slate-600 rounded-xl font-bold hover:bg-slate-50 transition-colors">Batal</button>
                <button type="submit" class="flex-1 h-12 bg-primary-800 text-white rounded-xl font-bold hover:bg-primary-900 shadow-lg shadow-primary-900/20 transition-all">Simpan Produk</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Product Modal -->
<?php if ($editProduct): ?>
<div id="editModal" class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-[100] flex items-center justify-center opacity-100 transition-opacity duration-300">
    <div class="bg-white rounded-[2rem] shadow-2xl border border-slate-100 p-8 w-full max-w-xl mx-4 transform scale-100 transition-transform duration-300 max-h-[90vh] overflow-y-auto relative">
        <div class="absolute top-0 right-0 w-32 h-32 bg-amber-50 rounded-full blur-3xl -z-10 translate-x-1/2 -translate-y-1/2"></div>
        
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-2xl font-extrabold text-accent">Edit Data Produk</h3>
            <a href="products.php" class="w-8 h-8 flex items-center justify-center bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-full transition-colors"><span class="material-symbols-outlined text-[20px]">close</span></a>
        </div>
        
        <form method="POST" action="product_action.php" class="space-y-5">
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="id" value="<?= $editProduct['id'] ?>">
            
            <div>
                <label class="block text-sm font-bold text-slate-700 mb-2">Nama Produk</label>
                <input type="text" name="name" value="<?= htmlspecialchars($editProduct['name']) ?>" class="w-full h-12 px-4 rounded-xl border-slate-200 bg-slate-50/50 focus:bg-white focus:ring-4 focus:ring-primary-50 focus:border-primary-300 transition-all text-sm font-medium" required>
            </div>
            
            <div class="grid grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Harga (Rp)</label>
                    <input type="number" name="price" value="<?= $editProduct['price'] ?>" min="0" class="w-full h-12 px-4 rounded-xl border-slate-200 bg-slate-50/50 focus:bg-white focus:ring-4 focus:ring-primary-50 focus:border-primary-300 transition-all text-sm font-medium" required>
                </div>
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Stok Tersedia</label>
                    <input type="number" name="stock" value="<?= $editProduct['stock'] ?>" min="0" class="w-full h-12 px-4 rounded-xl border-slate-200 bg-slate-50/50 focus:bg-white focus:ring-4 focus:ring-primary-50 focus:border-primary-300 transition-all text-sm font-medium" required>
                </div>
            </div>
            
            <div class="grid grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Kategori</label>
                    <select name="category_id" class="w-full h-12 px-4 rounded-xl border-slate-200 bg-slate-50/50 focus:bg-white focus:ring-4 focus:ring-primary-50 focus:border-primary-300 transition-all text-sm font-medium" required>
                        <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>" <?= $cat['id'] == $editProduct['category_id'] ? 'selected' : '' ?>><?= htmlspecialchars($cat['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">URL Gambar</label>
                    <input type="text" name="image_url" value="<?= htmlspecialchars($editProduct['image_url'] ?? '') ?>" class="w-full h-12 px-4 rounded-xl border-slate-200 bg-slate-50/50 focus:bg-white focus:ring-4 focus:ring-primary-50 focus:border-primary-300 transition-all text-sm font-medium">
                </div>
            </div>
            
            <div>
                <label class="block text-sm font-bold text-slate-700 mb-2">Deskripsi Produk</label>
                <textarea name="description" rows="3" class="w-full p-4 rounded-xl border-slate-200 bg-slate-50/50 focus:bg-white focus:ring-4 focus:ring-primary-50 focus:border-primary-300 resize-none transition-all text-sm font-medium"><?= htmlspecialchars($editProduct['description'] ?? '') ?></textarea>
            </div>
            
            <div class="flex gap-3 pt-4 border-t border-slate-100">
                <a href="products.php" class="flex-1 h-12 border border-slate-200 text-slate-600 rounded-xl font-bold flex items-center justify-center hover:bg-slate-50 transition-colors">Batal</a>
                <button type="submit" class="flex-1 h-12 bg-primary-800 text-white rounded-xl font-bold hover:bg-primary-900 shadow-lg shadow-primary-900/20 transition-all">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<script>
function openModal(id) {
    const modal = document.getElementById(id);
    const content = modal.querySelector('div');
    modal.classList.replace('hidden', 'flex');
    // small delay for transition
    setTimeout(() => {
        modal.classList.remove('opacity-0');
        content.classList.remove('scale-95');
    }, 10);
}
function closeModal(id) {
    const modal = document.getElementById(id);
    const content = modal.querySelector('div');
    modal.classList.add('opacity-0');
    content.classList.add('scale-95');
    setTimeout(() => {
        modal.classList.replace('flex', 'hidden');
    }, 300);
}
</script>

<?php include 'components/admin_footer.php'; ?>
