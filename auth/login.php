<?php
session_start();
require_once __DIR__ . '/../config/database.php';

// Redirect to home if already logged in
if (!empty($_SESSION['user_id'])) {
    header('Location: /index.php');
    exit;
}

$error = '';
$dbError = false;

// Test DB connection first
try {
    getDB();
} catch (Exception $e) {
    $dbError = true;
    $error = '⚠️ Koneksi database gagal. Pastikan MySQL sudah aktif di Laragon dan database "shopease" sudah di-import.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$dbError) {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = 'Email dan password wajib diisi.';
    } else {
        try {
            $pdo  = getDB();
            $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ?');
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['name']    = $user['name'];
                $_SESSION['role']    = $user['role'];
                if ($user['role'] === 'admin') {
                    header('Location: /admin/index.php');
                } else {
                    header('Location: /index.php');
                }
                exit;
            } else if ($user) {
                $error = 'Password salah. Coba lagi, atau gunakan tombol demo di bawah. Jika baru import DB, jalankan <a href="/setup.php?key=setup2024" class="underline font-bold">setup.php</a> terlebih dahulu.';
            } else {
                $error = 'Email tidak terdaftar.';
            }
        } catch (Exception $e) {
            $error = 'Terjadi kesalahan database: ' . htmlspecialchars($e->getMessage());
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>Masuk — ShopEase Premium</title>
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
                        accent: '#1e293b' // Deep slate for contrast
                    },
                    fontFamily: { display: ['Inter', 'sans-serif'] },
                    boxShadow: {
                        'glass': '0 8px 32px 0 rgba(16, 25, 40, 0.08)',
                    }
                }
            }
        }
    </script>
    <style>
        .split-bg {
            background-color: #f8fafc;
            background-image: radial-gradient(#cbd5e1 1px, transparent 1px);
            background-size: 24px 24px;
        }
    </style>
</head>
<body class="font-display bg-slate-50 text-slate-800 min-h-screen flex selection:bg-primary-200 selection:text-primary-900 overflow-x-hidden">

<div class="flex w-full min-h-screen">
    
    <!-- Left Panel: Form Area -->
    <div class="w-full lg:w-1/2 flex flex-col justify-center px-6 sm:px-12 md:px-20 lg:px-24 py-12 relative z-10 bg-white">
        <!-- Abstract Decoration -->
        <div class="absolute top-0 right-0 w-64 h-64 bg-primary-100 rounded-full blur-[80px] -z-10 opacity-50 translate-x-1/2 -translate-y-1/2 pointer-events-none"></div>
        <div class="absolute bottom-0 left-0 w-80 h-80 bg-rose-100 rounded-full blur-[100px] -z-10 opacity-50 -translate-x-1/3 translate-y-1/3 pointer-events-none"></div>

        <!-- Logo -->
        <a href="/index.php" class="inline-flex items-center gap-3 group w-fit mb-12">
            <div class="w-12 h-12 bg-primary-800 rounded-xl flex items-center justify-center text-white shadow-lg shadow-primary-800/30 group-hover:scale-105 group-hover:-rotate-3 transition-transform">
                <span class="material-symbols-outlined text-2xl">shopping_bag</span>
            </div>
            <h1 class="text-3xl font-extrabold text-accent tracking-tight">Shop<span class="text-primary-600">Ease</span></h1>
        </a>

        <div class="max-w-md w-full mx-auto lg:mx-0">
            <h2 class="text-3xl md:text-4xl font-extrabold text-accent tracking-tight mb-2">Selamat Datang Kembali</h2>
            <p class="text-slate-500 font-medium mb-8 text-base">Silakan masuk untuk melanjutkan pengalaman belanja premium Anda bersama kami.</p>

            <?php if ($error): ?>
                <div class="flex items-start gap-3 bg-red-50 border border-red-100 text-red-700 rounded-2xl p-4 mb-8 text-sm font-medium animate-pulse">
                    <span class="material-symbols-outlined text-[20px] shrink-0 mt-0.5">error</span>
                    <span class="leading-relaxed"><?= $error ?></span>
                </div>
            <?php endif; ?>

            <form method="POST" class="space-y-6 relative">
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Alamat Email</label>
                    <div class="relative group/input">
                        <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within/input:text-primary-600 transition-colors">mail</span>
                        <input type="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                            class="w-full h-14 pl-12 pr-4 rounded-xl border-slate-200 bg-slate-50/50 focus:bg-white focus:ring-4 focus:ring-primary-50 focus:border-primary-400 transition-all font-medium text-slate-900"
                            placeholder="nama@email.com" required autofocus>
                    </div>
                </div>

                <div>
                    <div class="flex justify-between items-center mb-2">
                        <label class="block text-sm font-bold text-slate-700">Kata Sandi</label>
                        <a href="#" class="text-sm font-semibold text-primary-600 hover:text-primary-800 hover:underline transition-colors" onclick="alert('Fitur pemulihan kata sandi (Lupa Password) belum tersedia pada demo ini. Silakan gunakan akun Demo saja.')">Lupa sandi?</a>
                    </div>
                    <div class="relative group/input">
                        <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within/input:text-primary-600 transition-colors">lock</span>
                        <input type="password" name="password" id="passwordField"
                            class="w-full h-14 pl-12 pr-12 rounded-xl border-slate-200 bg-slate-50/50 focus:bg-white focus:ring-4 focus:ring-primary-50 focus:border-primary-400 transition-all font-medium text-slate-900"
                            placeholder="••••••••" required>
                        <button type="button" onclick="togglePassword()" class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-primary-600 transition-colors py-2" aria-label="Toggle password visibility">
                            <span class="material-symbols-outlined text-[20px]" id="eyeIcon">visibility</span>
                        </button>
                    </div>
                </div>

                <button type="submit"
                    class="w-full relative inline-flex items-center justify-center gap-2 group px-8 py-4 bg-primary-800 text-white font-bold rounded-xl hover:bg-primary-900 transition-all shadow-[0_8px_25px_rgba(159,18,57,0.25)] hover:shadow-[0_12px_30px_rgba(159,18,57,0.35)] hover:-translate-y-1 overflow-hidden mt-4">
                    <div class="absolute inset-0 bg-white/10 translate-y-full group-hover:translate-y-0 transition-transform duration-300 ease-out"></div>
                    <span class="relative z-10 material-symbols-outlined text-[20px] group-hover:translate-x-1 transition-transform">login</span>
                    <span class="relative z-10 text-lg">Masuk ke Akun</span>
                </button>
            </form>

            <div class="mt-8 text-center sm:text-left text-slate-500 font-medium">
                Pengguna baru? 
                <a href="/auth/register.php" class="text-primary-700 font-bold hover:underline ml-1">Buat akun sekarang</a>
            </div>

            <!-- Demo Credentials -->
            <div class="mt-12 pt-8 border-t border-slate-100">
                <p class="text-xs text-slate-400 font-extrabold uppercase tracking-widest mb-4">Akses Cepat (Mode Demo)</p>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <button type="button" onclick="fillCredentials('user@shopease.com','user123')"
                        class="p-4 border-2 border-slate-100 rounded-2xl hover:border-primary-300 hover:bg-primary-50/50 transition-all text-left group/demo flex items-start gap-3">
                        <div class="w-10 h-10 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center shrink-0 group-hover/demo:scale-110 transition-transform">
                            <span class="material-symbols-outlined text-[20px]">person</span>
                        </div>
                        <div>
                            <p class="font-bold text-accent text-sm">Customer</p>
                            <p class="text-slate-500 text-xs mt-1">user@shopease.com</p>
                        </div>
                    </button>
                    <button type="button" onclick="fillCredentials('admin@shopease.com','admin123')"
                        class="p-4 border-2 border-slate-100 rounded-2xl hover:border-primary-300 hover:bg-primary-50/50 transition-all text-left group/demo flex items-start gap-3">
                        <div class="w-10 h-10 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0 group-hover/demo:scale-110 transition-transform">
                            <span class="material-symbols-outlined text-[20px]">admin_panel_settings</span>
                        </div>
                        <div>
                            <p class="font-bold text-accent text-sm">Administrator</p>
                            <p class="text-slate-500 text-xs mt-1">admin@shopease.com</p>
                        </div>
                    </button>
                </div>
            </div>

            <div class="mt-12 text-xs text-slate-400 font-medium">
                &copy; <?= date('Y') ?> ShopEase. Semua Hak Dilindungi.
            </div>
        </div>
    </div>

    <!-- Right Panel: Image/Graphic Area -->
    <div class="hidden lg:flex w-1/2 bg-slate-900 relative overflow-hidden items-center justify-center">
        <!-- Abstract gradient background representing luxury/premium maroon -->
        <div class="absolute inset-0 bg-gradient-to-br from-primary-950 via-primary-900 to-accent opacity-90"></div>
        
        <!-- Large decorative elements -->
        <div class="absolute -top-[20%] -right-[10%] w-[80%] h-[80%] rounded-full bg-primary-600/30 blur-[120px] mix-blend-screen"></div>
        <div class="absolute -bottom-[20%] -left-[10%] w-[60%] h-[60%] rounded-full bg-rose-500/20 blur-[100px] mix-blend-screen"></div>
        
        <!-- Premium glass card overlay showing a feature or quote -->
        <div class="relative z-10 bg-white/10 backdrop-blur-2xl border border-white/20 p-12 rounded-[2.5rem] max-w-lg shadow-[0_20px_50px_rgba(0,0,0,0.3)] transform hover:scale-[1.02] transition-transform duration-500">
            <span class="material-symbols-outlined text-[48px] text-white/80 mb-6 drop-shadow-md">local_mall</span>
            <h3 class="text-3xl font-bold text-white mb-4 leading-tight">Temukan koleksi eksklusif dengan penawaran terbaik.</h3>
            <p class="text-primary-100/80 text-lg leading-relaxed mb-8">ShopEase menghadirkan pengalaman belanja modern, aman, dan tanpa hambatan langsung ke genggaman Anda.</p>
            
            <div class="flex items-center gap-4">
                <div class="flex -space-x-3">
                    <div class="w-10 h-10 rounded-full bg-slate-300 border-2 border-primary-900 shadow-sm"><img src="https://ui-avatars.com/api/?name=A+B&background=random" class="rounded-full w-full h-full object-cover"></div>
                    <div class="w-10 h-10 rounded-full bg-slate-300 border-2 border-primary-900 shadow-sm"><img src="https://ui-avatars.com/api/?name=C+D&background=random" class="rounded-full w-full h-full object-cover"></div>
                    <div class="w-10 h-10 rounded-full bg-slate-300 border-2 border-primary-900 shadow-sm"><img src="https://ui-avatars.com/api/?name=E+F&background=random" class="rounded-full w-full h-full object-cover"></div>
                </div>
                <div class="text-sm text-primary-100 font-medium">Bergabung dengan ribuan<br>pelanggan setia lainnya.</div>
            </div>
        </div>
        
        <!-- Overlay pattern -->
        <div class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAiIGhlaWdodD0iMjAiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PGNpcmNsZSBjeD0iMiIgY3k9IjIiIHI9IjEiIGZpbGw9InJnYmEoMjU1LDI1NSwyNTUsMC4wNykiLz48L3N2Zz4=')] opacity-50 point-events-none"></div>
    </div>
</div>

<script>
function togglePassword() {
    const field = document.getElementById('passwordField');
    const icon = document.getElementById('eyeIcon');
    
    if (field.type === 'password') { 
        field.type = 'text'; 
        icon.textContent = 'visibility_off';
    } else { 
        field.type = 'password'; 
        icon.textContent = 'visibility';
    }
}

function fillCredentials(email, pass) {
    const emailField = document.querySelector('[name="email"]');
    const passField = document.getElementById('passwordField');
    
    // Add subtle flash effect
    emailField.classList.add('bg-primary-50');
    passField.classList.add('bg-primary-50');
    
    emailField.value = email;
    passField.value = pass;
    
    setTimeout(() => {
        emailField.classList.remove('bg-primary-50');
        passField.classList.remove('bg-primary-50');
    }, 300);
}
</script>

</body>
</html>
