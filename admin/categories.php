<?php include 'components/admin_header.php'; ?>
<?php
require_once __DIR__ . '/../config/functions.php';
$pdo = getDB();

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

// Fetch categories + product count
$categories = $pdo->query(
    'SELECT c.*, COUNT(p.id) AS product_count
     FROM categories c
     LEFT JOIN products p ON p.category_id = c.id
     GROUP BY c.id
     ORDER BY c.id ASC'
)->fetchAll();

$editCat = isset($_GET['edit']) ? $pdo->prepare('SELECT * FROM categories WHERE id = ?') : null;
if ($editCat) { $editCat->execute([(int)$_GET['edit']]); $editCat = $editCat->fetch(); }
?>

<?php if ($flash): ?>
<div class="flex items-start gap-3 px-4 py-3 rounded-2xl mb-6 text-sm font-bold shadow-sm <?= $flash['type'] === 'success' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-rose-50 text-rose-700 border border-rose-200' ?>">
    <span class="material-symbols-outlined shrink-0"><?= $flash['type'] === 'success' ? 'check_circle' : 'error' ?></span>
    <span class="mt-0.5"><?= htmlspecialchars($flash['message']) ?></span>
</div>
<?php endif; ?>

<div class="mb-6 flex flex-col sm:flex-row sm:items-end justify-between gap-4">
    <div>
        <h2 class="text-3xl font-extrabold text-accent tracking-tight">Kategori Produk</h2>
        <p class="text-slate-500 font-medium mt-2">Atur struktur kategori untuk mempermudah navigasi pelanggan.</p>
    </div>
    <button onclick="openModal('addCatModal')" class="flex items-center gap-2 bg-primary-800 hover:bg-primary-900 text-white px-5 py-2.5 rounded-xl font-bold shadow-lg shadow-primary-900/20 shrink-0 transition-transform active:scale-95">
        <span class="material-symbols-outlined text-[20px]">add</span> Kategori Baru
    </button>
</div>

<!-- Categories Table -->
<div class="bg-white rounded-[2rem] border border-slate-100 shadow-[0_8px_30px_rgba(0,0,0,0.04)] overflow-hidden mb-8 mt-4">
    <div class="overflow-x-auto p-2">
        <table class="w-full text-left border-collapse whitespace-nowrap">
            <thead>
                <tr>
                    <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100">Info Kategori</th>
                    <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100 text-center">Jumlah Produk</th>
                    <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100 text-center">Status Tampil</th>
                    <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100 text-right">Tindakan</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                <?php foreach ($categories as $cat): ?>
                <tr class="hover:bg-slate-50/80 group transition-all duration-300 <?= $cat['is_active'] ? '' : 'bg-slate-50/50 grayscale-[20%]' ?>">
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-4">
                            <div class="size-12 rounded-2xl flex items-center justify-center shrink-0 transition-all duration-300 <?= $cat['is_active'] ? 'bg-primary-50 text-primary-600 shadow-inner border border-primary-100 group-hover:bg-primary-600 group-hover:text-white' : 'bg-slate-100 text-slate-400 border border-slate-200' ?>">
                                <span class="material-symbols-outlined text-[24px]"><?= htmlspecialchars($cat['icon'] ?? 'category') ?></span>
                            </div>
                            <div>
                                <span class="font-extrabold text-base transition-colors <?= $cat['is_active'] ? 'text-slate-800 group-hover:text-primary-800' : 'text-slate-500' ?>"><?= htmlspecialchars($cat['name']) ?></span>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <span class="inline-flex items-center justify-center px-4 py-1.5 bg-slate-100 text-slate-600 rounded-lg text-xs font-bold border border-slate-200 shadow-sm">
                            <span class="material-symbols-outlined text-[14px] mr-1 opacity-50">inventory_2</span>
                            <?= $cat['product_count'] ?> Item
                        </span>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <form method="POST" action="category_action.php" class="inline-block">
                            <input type="hidden" name="action" value="toggle">
                            <input type="hidden" name="id" value="<?= $cat['id'] ?>">
                            <button type="submit" class="group/toggle flex items-center justify-center gap-2 text-sm mx-auto" title="Klik untuk mengubah status">
                                <div class="relative w-12 h-6 rounded-full transition-all duration-300 shadow-inner border <?= $cat['is_active'] ? 'bg-emerald-500 border-emerald-600' : 'bg-slate-300 border-slate-400' ?>">
                                    <span class="absolute top-0.5 size-5 rounded-full bg-white shadow-sm transition-all duration-300 <?= $cat['is_active'] ? 'left-[22px]' : 'left-0.5' ?>"></span>
                                </div>
                                <span class="font-bold text-[11px] uppercase tracking-wider w-14 text-left transition-colors <?= $cat['is_active'] ? 'text-emerald-600' : 'text-slate-400' ?>">
                                    <?= $cat['is_active'] ? 'Aktif' : 'Nonaktif' ?>
                                </span>
                            </button>
                        </form>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                            <a href="?edit=<?= $cat['id'] ?>" class="p-2 text-slate-400 hover:text-primary-600 hover:bg-primary-50 rounded-xl transition-colors" title="Edit Kategori">
                                <span class="material-symbols-outlined text-[20px]">edit</span>
                            </a>
                            <?php if ($cat['product_count'] == 0): ?>
                            <form method="POST" action="category_action.php" class="inline-block" onsubmit="return confirm('Hapus kategori ini secara permanen?')">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= $cat['id'] ?>">
                                <button type="submit" class="p-2 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-xl transition-colors" title="Hapus Kategori">
                                    <span class="material-symbols-outlined text-[20px]">delete</span>
                                </button>
                            </form>
                            <?php else: ?>
                            <span class="p-2 text-slate-300 cursor-not-allowed" title="Tidak dapat dihapus: masih memiliki produk terkait">
                                <span class="material-symbols-outlined text-[20px]">delete</span>
                            </span>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($categories)): ?>
                <tr>
                    <td colspan="4" class="py-16 text-center">
                        <div class="inline-flex flex-col items-center justify-center text-slate-400">
                            <span class="material-symbols-outlined text-[48px] mb-3 opacity-50">category</span>
                            <p class="font-medium text-lg">Belum ada kategori terdaftar.</p>
                        </div>
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add Modal -->
<div id="addCatModal" class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-[100] items-center justify-center hidden opacity-0 transition-opacity duration-300">
    <div class="bg-white rounded-[2rem] shadow-2xl border border-slate-100 p-8 w-full max-w-md mx-4 transform scale-95 transition-transform duration-300 relative overflow-hidden">
        <div class="absolute top-0 right-0 w-32 h-32 bg-primary-50 rounded-full blur-3xl -z-10 translate-x-1/2 -translate-y-1/2"></div>
        
        <div class="flex justify-between items-center mb-6 relative z-10">
            <h3 class="text-2xl font-extrabold text-accent">Kategori Baru</h3>
            <button onclick="closeModal('addCatModal')" class="w-8 h-8 flex items-center justify-center bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-full transition-colors">
                <span class="material-symbols-outlined text-[20px]">close</span>
            </button>
        </div>
        
        <form method="POST" action="category_action.php" class="space-y-5 relative z-10">
            <input type="hidden" name="action" value="create">
            
            <div>
                <label class="block text-sm font-bold text-slate-700 mb-2">Nama Kategori</label>
                <input type="text" name="name" class="w-full h-12 px-4 rounded-xl border-slate-200 bg-slate-50/50 focus:bg-white focus:ring-4 focus:ring-primary-50 focus:border-primary-300 transition-all text-sm font-medium" placeholder="Contoh: Elektronik Pria" required>
            </div>
            
            <div>
                <label class="block text-sm font-bold text-slate-700 mb-2">Ikon (Material Symbol)</label>
                <div class="relative">
                    <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 select-none">info</span>
                    <input type="text" name="icon" value="category" class="w-full h-12 pl-12 pr-4 rounded-xl border-slate-200 bg-slate-50/50 focus:bg-white focus:ring-4 focus:ring-primary-50 focus:border-primary-300 transition-all text-sm font-medium" placeholder="Contoh: checkroom">
                </div>
                <p class="text-[11px] font-medium text-slate-500 mt-2 px-1">Gunakan kode ikon dari <a href="https://fonts.google.com/icons?selected=Material+Symbols+Outlined" target="_blank" class="text-primary-600 hover:underline hover:text-primary-800 transition-colors">Google Material Symbols</a>.</p>
            </div>
            
            <div class="flex gap-3 pt-4 border-t border-slate-100">
                <button type="button" onclick="closeModal('addCatModal')" class="flex-1 h-12 border border-slate-200 text-slate-600 rounded-xl font-bold hover:bg-slate-50 transition-colors">Batal</button>
                <button type="submit" class="flex-1 h-12 bg-primary-800 text-white rounded-xl font-bold hover:bg-primary-900 shadow-lg shadow-primary-900/20 transition-all">Simpan Kategori</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Modal -->
<?php if ($editCat): ?>
<div id="editCatModal" class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-[100] flex items-center justify-center opacity-100 transition-opacity duration-300">
    <div class="bg-white rounded-[2rem] shadow-2xl border border-slate-100 p-8 w-full max-w-md mx-4 transform scale-100 transition-transform duration-300 relative overflow-hidden">
        <div class="absolute top-0 right-0 w-32 h-32 bg-amber-50 rounded-full blur-3xl -z-10 translate-x-1/2 -translate-y-1/2"></div>
        
        <div class="flex justify-between items-center mb-6 relative z-10">
            <h3 class="text-2xl font-extrabold text-accent">Edit Kategori</h3>
            <a href="categories.php" class="w-8 h-8 flex items-center justify-center bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-full transition-colors">
                <span class="material-symbols-outlined text-[20px]">close</span>
            </a>
        </div>
        
        <form method="POST" action="category_action.php" class="space-y-5 relative z-10">
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="id" value="<?= $editCat['id'] ?>">
            
            <div>
                <label class="block text-sm font-bold text-slate-700 mb-2">Nama Kategori</label>
                <input type="text" name="name" value="<?= htmlspecialchars($editCat['name']) ?>" class="w-full h-12 px-4 rounded-xl border-slate-200 bg-slate-50/50 focus:bg-white focus:ring-4 focus:ring-primary-50 focus:border-primary-300 transition-all text-sm font-medium" required>
            </div>
            
            <div>
                <label class="block text-sm font-bold text-slate-700 mb-2">Ikon</label>
                <div class="relative">
                    <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 select-none">info</span>
                    <input type="text" name="icon" value="<?= htmlspecialchars($editCat['icon'] ?? 'category') ?>" class="w-full h-12 pl-12 pr-4 rounded-xl border-slate-200 bg-slate-50/50 focus:bg-white focus:ring-4 focus:ring-primary-50 focus:border-primary-300 transition-all text-sm font-medium">
                </div>
            </div>
            
            <div class="flex gap-3 pt-4 border-t border-slate-100">
                <a href="categories.php" class="flex-1 h-12 border border-slate-200 text-slate-600 rounded-xl font-bold flex items-center justify-center hover:bg-slate-50 transition-colors">Batal</a>
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
