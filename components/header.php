<?php
require_once __DIR__ . '/../config/functions.php';
requireLogin('/auth/login.php');
$cartCount = getCartCount();
$user      = getCurrentUser();
$userName  = htmlspecialchars($user['name'] ?? 'User');
$userInitial = strtoupper(substr($user['name'] ?? 'U', 0, 1));
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>ShopEase - Premium Experience</title>
    <!-- Tailwind CSS (via CDN with Forms & Container Queries) -->
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <!-- Google Fonts: Inter & Material Symbols -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0..1,0&display=swap" rel="stylesheet"/>
    
    <!-- Design System Configuration -->
    <script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#fdf3f4',
                            100: '#f9e4e6',
                            200: '#f3cdd2',
                            300: '#eaabb4',
                            400: '#de7b8a',
                            500: '#cd4f63',
                            600: '#b43249',
                            700: '#972439',
                            800: '#800020', // Core Maroon
                            900: '#6f1e2f',
                            950: '#3e0c16',
                        },
                        accent: '#0f0f13',
                        surface: '#fcfcfc',
                    },
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                        display: ['Inter', 'sans-serif'],
                    },
                    boxShadow: {
                        'glass': '0 8px 32px 0 rgba(128, 0, 32, 0.05)',
                        'glass-hover': '0 12px 40px 0 rgba(128, 0, 32, 0.12)',
                        'glow': '0 0 20px rgba(128,0,32,0.3)',
                        'glow-lg': '0 0 30px rgba(128,0,32,0.5)',
                    },
                    animation: {
                        'blob': 'blob 7s infinite',
                        'float': 'float 3s ease-in-out infinite',
                        'pulse-slow': 'pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite',
                    },
                    keyframes: {
                        blob: {
                            '0%': { transform: 'translate(0px, 0px) scale(1)' },
                            '33%': { transform: 'translate(30px, -50px) scale(1.1)' },
                            '66%': { transform: 'translate(-20px, 20px) scale(0.9)' },
                            '100%': { transform: 'translate(0px, 0px) scale(1)' },
                        },
                        float: {
                            '0%, 100%': { transform: 'translateY(0)' },
                            '50%': { transform: 'translateY(-5px)' },
                        }
                    }
                },
            },
        }
    </script>
    <style>
        body { background-color: #f8f8fb; color: #1e1e24; }
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
        .fill-1 { font-variation-settings: 'FILL' 1; }
        .glass-panel { background: rgba(255, 255, 255, 0.75); backdrop-filter: blur(16px); -webkit-backdrop-filter: blur(16px); border-bottom: 1px solid rgba(255, 255, 255, 0.5); }
        .hero-gradient { background: linear-gradient(135deg, #800020 0%, #4a0012 100%); }
        .text-gradient { background: linear-gradient(to right, #800020, #de7b8a); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
    </style>
</head>
<body class="font-sans min-h-screen antialiased flex flex-col relative overflow-x-hidden selection:bg-primary-200 selection:text-primary-900">

    <!-- Ambient Background Orbs -->
    <div class="fixed inset-0 min-h-screen w-full -z-10 overflow-hidden pointer-events-none opacity-40">
        <div class="absolute top-[-10%] left-[-10%] w-96 h-96 bg-primary-200 rounded-full mix-blend-multiply filter blur-3xl opacity-70 animate-blob"></div>
        <div class="absolute top-[-20%] right-[-10%] w-96 h-96 bg-primary-300 rounded-full mix-blend-multiply filter blur-3xl opacity-50 animate-blob animation-delay-2000"></div>
        <div class="absolute bottom-[-20%] left-[20%] w-[40rem] h-[40rem] bg-rose-100 rounded-full mix-blend-multiply filter blur-3xl opacity-40 animate-blob animation-delay-4000"></div>
    </div>

    <!-- Full-Width Sticky Navigation Bar -->
    <div class="sticky top-0 z-50 w-full glass-panel shadow-sm transition-all duration-300" id="navbarWrapper">
        <header class="max-w-7xl mx-auto px-4 md:px-8">
            <div class="py-3.5 flex items-center justify-between gap-4">
                
                <!-- Brand / Logo -->
                <div class="flex items-center gap-3 shrink-0 group">
                    <a href="/index.php" class="flex items-center gap-2.5 focus:outline-none rounded-xl focus:ring-2 focus:ring-primary-500/50">
                        <div class="size-10 bg-gradient-to-br from-primary-800 to-primary-600 rounded-2xl flex items-center justify-center text-white shadow-glow group-hover:shadow-glow-lg group-hover:rotate-3 transition-all duration-300">
                            <span class="material-symbols-outlined text-[20px] fill-1">shopping_bag</span>
                        </div>
                        <h2 class="text-xl font-extrabold tracking-tight text-accent hidden sm:block">
                            Shop<span class="text-primary-700">Ease</span>
                        </h2>
                    </a>
                </div>

                <!-- Desktop Search (Centered, Modern) -->
                <form method="GET" action="/index.php" class="hidden md:flex flex-1 max-w-xl mx-8 group">
                    <div class="relative w-full">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none text-slate-400 group-focus-within:text-primary-600 transition-colors">
                            <span class="material-symbols-outlined text-[20px]">search</span>
                        </div>
                        <input name="q" value="<?= htmlspecialchars($_GET['q'] ?? '') ?>"
                            class="block w-full pl-11 pr-4 py-2.5 bg-slate-100/50 hover:bg-slate-100 border-transparent text-accent placeholder:text-slate-400 rounded-2xl focus:bg-white focus:ring-2 focus:ring-primary-500/30 focus:border-primary-500 font-medium text-sm transition-all shadow-inner"
                            placeholder="Temukan produk, kategori..." type="text" autocomplete="off"/>
                    </div>
                </form>

                <!-- User Actions -->
                <div class="flex items-center gap-1.5 sm:gap-3 shrink-0">
                    
                    <!-- Cart Indicator -->
                    <a href="/cart.php" class="relative p-2.5 text-slate-600 hover:text-primary-700 hover:bg-primary-50 rounded-2xl transition-all group focus:outline-none focus:ring-2 focus:ring-primary-500/50">
                        <span class="material-symbols-outlined transition-transform duration-300 group-hover:-translate-y-0.5">shopping_cart</span>
                        <?php if ($cartCount > 0): ?>
                        <span class="absolute top-1.5 right-1.5 flex h-[18px] min-w-[18px] items-center justify-center rounded-full bg-primary-600 px-1 text-[10px] font-bold text-white shadow-md ring-2 ring-white animate-[pulse_2s_cubic-bezier(0.4,0,0.6,1)_infinite] group-hover:animate-none">
                            <?= $cartCount > 9 ? '9+' : $cartCount ?>
                        </span>
                        <?php endif; ?>
                    </a>

                    <div class="w-[1px] h-6 bg-slate-200 hidden sm:block mx-1"></div>

                    <!-- User Profile Dropdown -->
                    <div class="relative" id="userMenu">
                        <button onclick="toggleUserMenu()" class="flex items-center gap-2.5 p-1 pr-3 rounded-full hover:bg-slate-100/80 transition-all focus:outline-none focus:ring-2 focus:ring-primary-500/50 border border-transparent hover:border-slate-200">
                            <div class="h-9 w-9 rounded-full bg-gradient-to-tr from-primary-800 to-primary-500 flex items-center justify-center text-white font-bold text-sm shadow-sm ring-2 ring-white">
                                <?= $userInitial ?>
                            </div>
                            <div class="hidden md:flex flex-col items-start leading-none">
                                <span class="text-xs text-slate-500 font-medium mb-0.5">Halo,</span>
                                <span class="text-sm font-bold text-accent truncate max-w-[100px]"><?= $userName ?></span>
                            </div>
                            <span class="material-symbols-outlined text-sm text-slate-400 hidden md:block transition-transform duration-200" id="userMenuCaret">expand_more</span>
                        </button>

                        <!-- Dropdown Panel -->
                        <div id="userMenuDropdown" class="hidden opacity-0 translate-y-2 absolute right-0 top-full mt-3 w-64 bg-white/95 backdrop-blur-xl border border-slate-100 rounded-3xl shadow-glass overflow-hidden z-[60] transition-all duration-200 origin-top-right">
                            <div class="p-5 bg-gradient-to-b from-primary-50/50 to-transparent border-b border-primary-100/50">
                                <p class="font-bold text-accent text-base truncate"><?= $userName ?></p>
                                <p class="text-xs text-slate-500 truncate mt-1"><?= htmlspecialchars($user['email'] ?? '') ?></p>
                                <?php if (($_SESSION['role'] ?? '') === 'admin'): ?>
                                <span class="inline-flex mt-2 px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-widest bg-primary-100 text-primary-800">Admin</span>
                                <?php endif; ?>
                            </div>
                            <nav class="p-2 space-y-0.5">
                                <a href="/index.php" class="flex items-center gap-3 px-4 py-2.5 rounded-2xl hover:bg-primary-50 text-slate-600 hover:text-primary-800 text-sm font-medium transition-colors group">
                                    <span class="material-symbols-outlined text-[20px] text-slate-400 group-hover:text-primary-500 transition-colors">storefront</span> Beranda Utama
                                </a>
                                <a href="/profile.php" class="flex items-center gap-3 px-4 py-2.5 rounded-2xl hover:bg-primary-50 text-slate-600 hover:text-primary-800 text-sm font-medium transition-colors group">
                                    <span class="material-symbols-outlined text-[20px] text-slate-400 group-hover:text-primary-500 transition-colors">manage_accounts</span> Pengaturan Profil
                                </a>
                                <a href="/history.php" class="flex items-center gap-3 px-4 py-2.5 rounded-2xl hover:bg-primary-50 text-slate-600 hover:text-primary-800 text-sm font-medium transition-colors group">
                                    <span class="material-symbols-outlined text-[20px] text-slate-400 group-hover:text-primary-500 transition-colors">receipt_long</span> Riwayat Pesanan
                                </a>
                                
                                <?php if (($_SESSION['role'] ?? '') === 'admin'): ?>
                                <div class="h-px bg-slate-100 my-2 mx-4"></div>
                                <a href="/admin/index.php" class="flex items-center justify-between px-4 py-2.5 rounded-2xl bg-accent text-white hover:bg-black text-sm font-bold transition-all shadow-md">
                                    <div class="flex items-center gap-3">
                                        <span class="material-symbols-outlined text-[20px] text-primary-400">admin_panel_settings</span> Dashboard
                                    </div>
                                    <span class="material-symbols-outlined text-[16px]">arrow_forward</span>
                                </a>
                                <?php endif; ?>
                                
                                <div class="h-px bg-slate-100 my-2 mx-4"></div>
                                <a href="/auth/logout.php" class="flex items-center gap-3 px-4 py-2.5 rounded-2xl hover:bg-red-50 text-slate-600 hover:text-red-600 text-sm font-medium transition-colors group">
                                    <span class="material-symbols-outlined text-[20px] text-slate-400 group-hover:text-red-500 transition-colors">logout</span> Keluar Akun
                                </a>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Mobile Search (Expands below on small screens) -->
            <form method="GET" action="/index.php" class="md:hidden flex w-full border-t border-slate-100 px-4 py-3 bg-white/50 rounded-b-3xl">
                <div class="relative w-full">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none text-slate-400">
                        <span class="material-symbols-outlined text-[20px]">search</span>
                    </div>
                    <input name="q" value="<?= htmlspecialchars($_GET['q'] ?? '') ?>"
                        class="block w-full pl-11 pr-4 py-2.5 bg-white border border-slate-200 text-accent placeholder:text-slate-400 rounded-2xl focus:ring-2 focus:ring-primary-500/50 focus:border-primary-500 font-medium text-sm transition-all shadow-sm"
                        placeholder="Cari produk impianmu..." type="text"/>
                </div>
            </form>
        </header>
    </div>

    <!-- Main Content Canvas -->
    <main class="w-full max-w-7xl mx-auto px-4 md:px-8 flex-grow mt-6 pb-20">

<script>
function toggleUserMenu() {
    const dropdown = document.getElementById('userMenuDropdown');
    const caret = document.getElementById('userMenuCaret');
    
    if (dropdown.classList.contains('hidden')) {
        dropdown.classList.remove('hidden');
        // Slight delay for animation to trigger after display:block
        setTimeout(() => {
            dropdown.classList.remove('opacity-0', 'translate-y-2');
            caret?.classList.add('rotate-180');
        }, 10);
    } else {
        dropdown.classList.add('opacity-0', 'translate-y-2');
        caret?.classList.remove('rotate-180');
        setTimeout(() => {
            dropdown.classList.add('hidden');
        }, 200); // Wait for transition
    }
}

// Close dropdown when clicking outside
document.addEventListener('click', (e) => {
    const menuMenu = document.getElementById('userMenu');
    const dropdown = document.getElementById('userMenuDropdown');
    const caret = document.getElementById('userMenuCaret');
    if (!menuMenu.contains(e.target) && !dropdown.classList.contains('hidden')) {
        dropdown.classList.add('opacity-0', 'translate-y-2');
        caret?.classList.remove('rotate-180');
        setTimeout(() => {
            dropdown.classList.add('hidden');
        }, 200);
    }
});
</script>
