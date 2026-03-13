<?php
session_start();
require_once __DIR__ . '/../config/database.php';

if (!empty($_SESSION['user_id'])) { header('Location: /index.php'); exit; }

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = trim($_POST['name'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';

    if (empty($name) || empty($email) || empty($password)) {
        $error = 'Semua kolom wajib diisi.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Format email tidak valid.';
    } elseif (strlen($password) < 6) {
        $error = 'Password minimal 6 karakter.';
    } elseif ($password !== $confirm) {
        $error = 'Konfirmasi password tidak cocok.';
    } else {
        $pdo  = getDB();
        $check = $pdo->prepare('SELECT id FROM users WHERE email = ?');
        $check->execute([$email]);
        if ($check->fetch()) {
            $error = 'Email sudah terdaftar. Gunakan email lain atau login.';
        } else {
            $hashed = password_hash($password, PASSWORD_BCRYPT);
            $insert = $pdo->prepare('INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, "user")');
            $insert->execute([$name, $email, $hashed]);
            $userId = $pdo->lastInsertId();
            $_SESSION['user_id'] = $userId;
            $_SESSION['name']    = $name;
            $_SESSION['role']    = 'user';
            header('Location: /index.php');
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>Daftar Akun — ShopEase Premium</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    <script>
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
                        accent: '#1e293b'
                    },
                    fontFamily: { display: ['Inter', 'sans-serif'] },
                    boxShadow: {
                        'glass': '0 8px 32px 0 rgba(16, 25, 40, 0.08)',
                    }
                }
            }
        }
    </script>
</head>
<body class="font-display bg-slate-50 text-slate-800 min-h-screen flex selection:bg-primary-200 selection:text-primary-900 overflow-x-hidden">

<div class="flex w-full min-h-screen">
    
    <!-- Left Panel: Form Area -->
    <div class="w-full lg:w-1/2 flex flex-col justify-center px-6 sm:px-12 md:px-20 lg:px-24 py-12 relative z-10 bg-white">
        <!-- Abstract Decoration -->
        <div class="absolute top-0 right-0 w-64 h-64 bg-primary-100 rounded-full blur-[80px] -z-10 opacity-50 translate-x-1/2 -translate-y-1/2 pointer-events-none"></div>
        <div class="absolute bottom-0 left-0 w-80 h-80 bg-rose-100 rounded-full blur-[100px] -z-10 opacity-50 -translate-x-1/3 translate-y-1/3 pointer-events-none"></div>

        <!-- Logo -->
        <a href="/index.php" class="inline-flex items-center gap-3 group w-fit mb-10">
            <div class="w-12 h-12 bg-primary-800 rounded-xl flex items-center justify-center text-white shadow-lg shadow-primary-800/30 group-hover:scale-105 group-hover:-rotate-3 transition-transform">
                <span class="material-symbols-outlined text-2xl">shopping_bag</span>
            </div>
            <h1 class="text-3xl font-extrabold text-accent tracking-tight">Shop<span class="text-primary-600">Ease</span></h1>
        </a>

        <div class="max-w-md w-full mx-auto lg:mx-0">
            <h2 class="text-3xl md:text-4xl font-extrabold text-accent tracking-tight mb-2">Buat Akun Baru</h2>
            <p class="text-slate-500 font-medium mb-8 text-base">Bergabunglah dengan kami untuk mengubah cara Anda berbelanja.</p>

            <?php if ($error): ?>
                <div class="flex items-start gap-3 bg-red-50 border border-red-100 text-red-700 rounded-2xl p-4 mb-8 text-sm font-medium animate-pulse">
                    <span class="material-symbols-outlined text-[20px] shrink-0 mt-0.5">error</span>
                    <span class="leading-relaxed"><?= htmlspecialchars($error) ?></span>
                </div>
            <?php endif; ?>

            <form method="POST" class="space-y-5 relative">
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Nama Lengkap</label>
                    <div class="relative group/input">
                        <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within/input:text-primary-600 transition-colors">person</span>
                        <input type="text" name="name" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>"
                            class="w-full h-14 pl-12 pr-4 rounded-xl border-slate-200 bg-slate-50/50 focus:bg-white focus:ring-4 focus:ring-primary-50 focus:border-primary-400 transition-all font-medium text-slate-900"
                            placeholder="Nama Lengkap Anda" required autofocus>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Alamat Email</label>
                    <div class="relative group/input">
                        <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within/input:text-primary-600 transition-colors">mail</span>
                        <input type="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                            class="w-full h-14 pl-12 pr-4 rounded-xl border-slate-200 bg-slate-50/50 focus:bg-white focus:ring-4 focus:ring-primary-50 focus:border-primary-400 transition-all font-medium text-slate-900"
                            placeholder="nama@email.com" required>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Kata Sandi</label>
                    <div class="relative group/input">
                        <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within/input:text-primary-600 transition-colors">lock</span>
                        <input type="password" name="password" id="passField"
                            class="w-full h-14 pl-12 pr-12 rounded-xl border-slate-200 bg-slate-50/50 focus:bg-white focus:ring-4 focus:ring-primary-50 focus:border-primary-400 transition-all font-medium text-slate-900"
                            placeholder="Minimal 6 karakter" required>
                        <button type="button" onclick="togglePassword('passField', 'eyeIcon1')" class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-primary-600 transition-colors py-2" aria-label="Toggle password visibility">
                            <span class="material-symbols-outlined text-[20px]" id="eyeIcon1">visibility</span>
                        </button>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Konfirmasi Kata Sandi</label>
                    <div class="relative group/input">
                        <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within/input:text-primary-600 transition-colors">lock_reset</span>
                        <input type="password" name="confirm_password" id="confirmField"
                            class="w-full h-14 pl-12 pr-12 rounded-xl border-slate-200 bg-slate-50/50 focus:bg-white focus:ring-4 focus:ring-primary-50 focus:border-primary-400 transition-all font-medium text-slate-900"
                            placeholder="Ulangi kata sandi Anda" required>
                        <button type="button" onclick="togglePassword('confirmField', 'eyeIcon2')" class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-primary-600 transition-colors py-2" aria-label="Toggle password visibility">
                            <span class="material-symbols-outlined text-[20px]" id="eyeIcon2">visibility</span>
                        </button>
                    </div>
                </div>

                <div class="pt-2">
                    <p class="text-xs text-slate-500 font-medium mb-6">Dengan mendaftar, Anda menyetujui <a href="#" class="text-primary-600 hover:underline">Syarat & Ketentuan</a> serta <a href="#" class="text-primary-600 hover:underline">Kebijakan Privasi</a> kami.</p>
                </div>

                <button type="submit"
                    class="w-full relative inline-flex items-center justify-center gap-2 group px-8 py-4 bg-primary-800 text-white font-bold rounded-xl hover:bg-primary-900 transition-all shadow-[0_8px_25px_rgba(159,18,57,0.25)] hover:shadow-[0_12px_30px_rgba(159,18,57,0.35)] hover:-translate-y-1 overflow-hidden mt-4">
                    <div class="absolute inset-0 bg-white/10 translate-y-full group-hover:translate-y-0 transition-transform duration-300 ease-out"></div>
                    <span class="relative z-10 material-symbols-outlined text-[20px] group-hover:scale-110 transition-transform">person_add</span>
                    <span class="relative z-10 text-lg">Buat Akun Sekarang</span>
                </button>
            </form>

            <div class="mt-8 text-center sm:text-left text-slate-500 font-medium pb-8 border-b border-slate-100">
                Sudah punya akun? 
                <a href="/auth/login.php" class="text-primary-700 font-bold hover:underline ml-1">Masuk di sini</a>
            </div>

            <div class="mt-8 text-xs text-slate-400 font-medium pb-4">
                &copy; <?= date('Y') ?> ShopEase. Semua Hak Dilindungi.
            </div>
        </div>
    </div>

    <!-- Right Panel: Image/Graphic Area -->
    <div class="hidden lg:flex w-1/2 bg-slate-900 relative overflow-hidden items-center justify-center">
        <!-- Abstract gradient background representing luxury/premium maroon -->
        <div class="absolute inset-0 bg-gradient-to-tl from-primary-950 via-primary-900 to-accent opacity-90"></div>
        
        <!-- Large decorative elements -->
        <div class="absolute -bottom-[20%] -right-[10%] w-[80%] h-[80%] rounded-full bg-primary-600/30 blur-[120px] mix-blend-screen"></div>
        <div class="absolute -top-[20%] -left-[10%] w-[60%] h-[60%] rounded-full bg-rose-500/20 blur-[100px] mix-blend-screen"></div>
        
        <!-- Premium glass card overlay showing a feature or quote -->
        <div class="relative z-10 bg-white/10 backdrop-blur-2xl border border-white/20 p-12 rounded-[2.5rem] max-w-lg shadow-[0_20px_50px_rgba(0,0,0,0.3)] transform hover:scale-[1.02] transition-transform duration-500 flex flex-col justify-center h-auto items-start">
            <div class="w-16 h-16 bg-gradient-to-br from-primary-400 to-primary-600 rounded-2xl flex items-center justify-center shadow-lg shadow-primary-500/30 mb-8 border border-white/20">
                <span class="material-symbols-outlined text-white text-[32px]">credit_card</span>
            </div>
            <h3 class="text-3xl font-bold text-white mb-4 leading-tight">Transaksi aman dan mulus setiap saat.</h3>
            <p class="text-primary-100/80 text-lg leading-relaxed mb-10">Bergabung sekarang untuk menikmati promo spesial member baru, pembayaran instan, dan layanan prioritas.</p>
            
            <div class="flex items-center gap-6">
                <div class="flex flex-col gap-1">
                    <span class="text-2xl font-black text-white">99%</span>
                    <span class="text-xs font-medium text-primary-200 uppercase tracking-widest">Transaksi Suskes</span>
                </div>
                <!-- Divider -->
                <div class="w-px h-10 bg-white/20"></div>
                <div class="flex flex-col gap-1">
                    <span class="text-2xl font-black text-white">24/7</span>
                    <span class="text-xs font-medium text-primary-200 uppercase tracking-widest">Bantuan Cepat</span>
                </div>
            </div>
        </div>
        
        <!-- Overlay pattern -->
        <div class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAiIGhlaWdodD0iMjAiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PGNpcmNsZSBjeD0iMiIgY3k9IjIiIHI9IjEiIGZpbGw9InJnYmEoMjU1LDI1NSwyNTUsMC4wNykiLz48L3N2Zz4=')] opacity-50 point-events-none"></div>
    </div>
</div>

<script>
function togglePassword(fieldId, iconId) {
    const field = document.getElementById(fieldId);
    const icon = document.getElementById(iconId);
    
    if (field.type === 'password') { 
        field.type = 'text'; 
        icon.textContent = 'visibility_off';
    } else { 
        field.type = 'password'; 
        icon.textContent = 'visibility';
    }
}
</script>
</body>
</html>
