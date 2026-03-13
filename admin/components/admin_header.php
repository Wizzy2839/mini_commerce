<?php
require_once __DIR__ . '/../../config/functions.php';
requireAdmin();
$adminUser = getCurrentUser();
$adminName = htmlspecialchars($adminUser['name'] ?? 'Admin');
$adminInitial = strtoupper(substr($adminUser['name'] ?? 'A', 0, 1));
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>Admin Panel — ShopEase Premium</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    <script id="tailwind-config">
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#fff1f2',
                            100: '#ffe4e6',
                            200: '#fecdd3',
                            300: '#fda4af',
                            400: '#fb7185',
                            500: '#f43f5e',
                            600: '#e11d48',
                            700: '#be123c',
                            800: '#9f1239', // Maroon base
                            900: '#881337',
                            950: '#4c0519',
                        },
                        accent: '#1e293b',
                        sidebar: '#0f172a',
                    },
                    fontFamily: { display: ['Inter', 'sans-serif'] },
                },
            },
        }
    </script>
    <style>
        .fill-1 { font-variation-settings: 'FILL' 1; }
        .sidebar-scrollbar::-webkit-scrollbar { width: 4px; }
        .sidebar-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .sidebar-scrollbar::-webkit-scrollbar-thumb { background: rgba(159,18,57,0.3); border-radius: 9999px; }
        .nav-active { position: relative; }
        .nav-active::before { content:''; position:absolute; left:0; top:50%; transform:translateY(-50%); width:4px; height:100%; background:#e11d48; border-radius:0 4px 4px 0; }
    </style>
</head>
<body class="font-display text-slate-800 bg-slate-50 antialiased selection:bg-primary-200 selection:text-primary-900">
<div class="flex h-screen overflow-hidden">

    <!-- Sidebar Navigation -->
    <?php include 'admin_sidebar.php'; ?>

    <!-- Main Content Area -->
    <div class="flex-1 flex flex-col overflow-hidden relative">
        
        <!-- Subtle background pattern for main content area -->
        <div class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAiIGhlaWdodD0iMjAiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PGNpcmNsZSBjeD0iMiIgY3k9IjIiIHI9IjEiIGZpbGw9InJnYmEoMTQ4LDE2MywxODQsMC4wNSkiLz48L3N2Zz4=')] pointer-events-none z-0"></div>

        <!-- Top Bar -->
        <header class="h-20 bg-white/80 backdrop-blur-xl border-b border-slate-200/60 flex items-center justify-between px-6 lg:px-10 z-20 shrink-0 shadow-sm sticky top-0">
            <div class="flex items-center gap-6 flex-1">
                <div class="relative w-80 max-w-full group">
                    <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-primary-600 transition-colors">search</span>
                    <input class="w-full h-11 pl-12 pr-4 bg-slate-100/50 border-transparent rounded-2xl text-sm font-medium focus:bg-white focus:ring-4 focus:ring-primary-50 focus:border-primary-300 transition-all placeholder:text-slate-400 text-slate-700" placeholder="Cari pesanan, pelanggan, produk..." type="text"/>
                    <div class="absolute right-3 top-1/2 -translate-y-1/2 hidden sm:flex items-center gap-1">
                        <kbd class="px-2 py-1 bg-white border border-slate-200 rounded-lg text-[10px] font-bold text-slate-400">Ctrl</kbd>
                        <kbd class="px-2 py-1 bg-white border border-slate-200 rounded-lg text-[10px] font-bold text-slate-400">K</kbd>
                    </div>
                </div>
            </div>
            
            <div class="flex items-center gap-4">
                <a href="/index.php" class="hidden sm:flex items-center gap-2 text-slate-600 hover:text-primary-700 hover:bg-primary-50 px-4 py-2.5 rounded-xl text-sm font-bold transition-all" target="_blank" title="Lihat Toko Publik">
                    <span class="material-symbols-outlined text-[20px]">storefront</span>
                    Toko
                </a>
                
                <div class="relative">
                    <button class="relative p-2.5 text-slate-400 hover:text-primary-600 hover:bg-primary-50 rounded-xl transition-all">
                        <span class="material-symbols-outlined text-[20px]">notifications</span>
                        <span class="absolute top-2 right-2 w-2 h-2 bg-rose-500 rounded-full border-2 border-white"></span>
                    </button>
                </div>

                <div class="h-8 w-px bg-slate-200 mx-1"></div>
                
                <!-- Admin Info Pill Dropdown -->
                <div class="relative" id="adminMenu">
                    <button onclick="toggleAdminMenu()" class="flex items-center gap-3 pl-2 pr-4 py-1.5 rounded-full bg-slate-50 border border-slate-200 hover:bg-white hover:shadow-md hover:border-slate-300 transition-all focus:outline-none focus:ring-2 focus:ring-primary-500/50">
                        <div class="size-9 rounded-full bg-gradient-to-br from-primary-700 to-primary-900 flex items-center justify-center text-white font-bold text-sm shrink-0 shadow-inner">
                            <?= $adminInitial ?>
                        </div>
                        <div class="hidden sm:flex flex-col text-left">
                            <span class="text-sm font-bold text-accent leading-none"><?= $adminName ?></span>
                            <span class="text-[10px] font-bold text-primary-600 uppercase tracking-wider mt-1">Administrator</span>
                        </div>
                        <span class="material-symbols-outlined text-slate-400 text-[18px] ml-1 transition-transform duration-200" id="adminMenuCaret">expand_more</span>
                    </button>
                    
                    <!-- Dropdown Panel -->
                    <div id="adminMenuDropdown" class="hidden opacity-0 translate-y-2 absolute right-0 top-full mt-3 w-56 bg-white/95 backdrop-blur-xl border border-slate-100 rounded-3xl shadow-glass overflow-hidden z-[60] transition-all duration-200 origin-top-right">
                        <div class="p-4 bg-gradient-to-b from-primary-50/50 to-transparent border-b border-primary-100/50">
                            <p class="font-bold text-accent text-sm truncate"><?= $adminName ?></p>
                            <p class="text-xs text-slate-500 truncate mt-1"><?= htmlspecialchars($adminUser['email'] ?? '') ?></p>
                        </div>
                        <nav class="p-2 space-y-0.5">
                            <a href="/profile.php" class="flex items-center gap-3 px-4 py-2.5 rounded-2xl hover:bg-slate-50 text-slate-600 hover:text-primary-800 text-sm font-medium transition-colors group">
                                <span class="material-symbols-outlined text-[20px] text-slate-400 group-hover:text-primary-500 transition-colors">manage_accounts</span> Pengaturan
                            </a>
                            <div class="h-px bg-slate-100 my-2 mx-4"></div>
                            <a href="/auth/logout.php" class="flex items-center gap-3 px-4 py-2.5 rounded-2xl hover:bg-red-50 text-slate-600 hover:text-red-600 text-sm font-medium transition-colors group">
                                <span class="material-symbols-outlined text-[20px] text-slate-400 group-hover:text-red-500 transition-colors">logout</span> Keluar
                            </a>
                        </nav>
                    </div>
                </div>
            </div>
        </header>

        <script>
        function toggleAdminMenu() {
            const dropdown = document.getElementById('adminMenuDropdown');
            const caret = document.getElementById('adminMenuCaret');
            
            if (dropdown.classList.contains('hidden')) {
                dropdown.classList.remove('hidden');
                setTimeout(() => {
                    dropdown.classList.remove('opacity-0', 'translate-y-2');
                    caret?.classList.add('rotate-180');
                }, 10);
            } else {
                dropdown.classList.add('opacity-0', 'translate-y-2');
                caret?.classList.remove('rotate-180');
                setTimeout(() => {
                    dropdown.classList.add('hidden');
                }, 200);
            }
        }

        // Close dropdown when clicking outside
        document.addEventListener('click', (e) => {
            const menuMenu = document.getElementById('adminMenu');
            const dropdown = document.getElementById('adminMenuDropdown');
            const caret = document.getElementById('adminMenuCaret');
            if (menuMenu && !menuMenu.contains(e.target) && !dropdown.classList.contains('hidden')) {
                dropdown.classList.add('opacity-0', 'translate-y-2');
                caret?.classList.remove('rotate-180');
                setTimeout(() => {
                    dropdown.classList.add('hidden');
                }, 200);
            }
        });
        </script>

        <main class="flex-1 overflow-y-auto p-6 md:p-8 lg:p-10 relative z-10 scroll-smooth">
            <div class="max-w-screen-2xl mx-auto">

