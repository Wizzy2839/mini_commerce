<?php
// $adminUser and $adminName are set from admin_header.php
$currentPage = basename($_SERVER['PHP_SELF']);

function navLink(string $href, string $icon, string $label, string $current): string {
    $isActive = ($current === $href);
    // Determine which icons to use fill (if active)
    $iconClass = $isActive ? 'fill-1 text-primary-400' : 'text-slate-400 group-hover:text-primary-300';
    $textClass = $isActive ? 'text-white font-bold' : 'text-slate-300 font-medium group-hover:text-white';
    $bgClass   = $isActive ? 'nav-active bg-primary-900/40 shadow-inner' : 'hover:bg-white/5';
    
    return "<a class=\"group flex items-center gap-3.5 px-4 py-3 rounded-xl $bgClass transition-all duration-300 relative overflow-hidden\" href=\"$href\">
        " . ($isActive ? "<div class=\"absolute inset-0 bg-gradient-to-r from-primary-600/20 to-transparent\"></div>" : "") . "
        <span class=\"material-symbols-outlined text-[20px] relative z-10 $iconClass transition-colors\">$icon</span>
        <span class=\"text-sm relative z-10 $textClass transition-colors\">$label</span>
    </a>";
}
?>
<aside class="w-[280px] shrink-0 flex flex-col overflow-hidden relative border-r border-slate-800 shadow-2xl z-30" style="background: linear-gradient(180deg, #0f172a 0%, #020617 100%);">
    
    <!-- Abstract top glow -->
    <div class="absolute top-0 left-0 w-full h-48 bg-primary-900/20 blur-[60px] pointer-events-none"></div>

    <!-- Brand -->
    <div class="px-6 py-8 flex items-center gap-3 relative z-10">
        <div class="size-12 rounded-2xl bg-gradient-to-br from-primary-600 to-primary-900 flex items-center justify-center text-white shrink-0 shadow-[0_0_20px_rgba(159,18,57,0.4)] border border-white/10">
            <span class="material-symbols-outlined text-[24px]">shopping_bag</span>
        </div>
        <div>
            <h1 class="font-extrabold text-xl text-white tracking-tight">Shop<span class="text-primary-500">Ease</span></h1>
            <div class="flex items-center gap-1.5 mt-1">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 shadow-[0_0_8px_rgba(16,185,129,0.8)]"></span>
                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Admin Panel</p>
            </div>
        </div>
    </div>

    <!-- Nav Items -->
    <nav class="flex-1 px-4 py-4 space-y-1.5 sidebar-scrollbar overflow-y-auto relative z-10">
        <p class="text-[10px] font-extrabold text-slate-500 uppercase tracking-widest px-4 pb-2 pt-2">Utama</p>
        <?= navLink('index.php',      'dashboard',   'Dashboard',         $currentPage) ?>
        
        <p class="text-[10px] font-extrabold text-slate-500 uppercase tracking-widest px-4 pb-2 pt-6">Katalog</p>
        <?= navLink('products.php',   'inventory_2', 'Kelola Produk',     $currentPage) ?>
        <?= navLink('categories.php', 'category',    'Kategori',          $currentPage) ?>

        <p class="text-[10px] font-extrabold text-slate-500 uppercase tracking-widest px-4 pb-2 pt-6">Penjualan</p>
        <?= navLink('reports.php',    'receipt_long','Semua Transaksi',   $currentPage) ?>
    </nav>

    <!-- Admin Profile Footer -->
    <div class="p-4 relative z-10 mt-auto">
        <!-- Decoration -->
        <div class="absolute bottom-0 left-0 w-full h-32 bg-gradient-to-t from-primary-950/50 to-transparent pointer-events-none -z-10"></div>
        
        <div class="flex items-center gap-3 px-3 py-3 rounded-2xl bg-white/5 border border-white/10 hover:bg-white/10 hover:border-primary-500/30 transition-all group backdrop-blur-md">
            <div class="size-10 rounded-full bg-gradient-to-br from-slate-700 to-slate-900 border border-slate-600 flex items-center justify-center text-white font-bold text-sm shrink-0 shadow-inner group-hover:scale-105 transition-transform">
                <?= $adminInitial ?>
            </div>
            <div class="flex flex-col min-w-0 flex-1">
                <span class="text-sm font-bold truncate text-white"><?= htmlspecialchars($adminName) ?></span>
                <span class="text-[10px] font-bold text-primary-400">Super Admin</span>
            </div>
            <a href="/auth/logout.php" class="w-8 h-8 rounded-full bg-slate-800 flex items-center justify-center text-slate-400 hover:text-white hover:bg-rose-500 hover:shadow-[0_0_15px_rgba(244,63,94,0.5)] transition-all shrink-0" title="Logout">
                <span class="material-symbols-outlined text-[16px] translate-x-[1px]">logout</span>
            </a>
        </div>
    </div>
</aside>
