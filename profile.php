<?php include 'components/header.php'; ?>
<?php
require_once __DIR__ . '/config/functions.php';
$pdo  = getDB();
$user = getCurrentUser();

$success = $error = '';

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name      = trim($_POST['name'] ?? '');
    $phone     = trim($_POST['phone'] ?? '');
    $gender    = $_POST['gender'] ?? null;
    $birthDate = $_POST['birth_date'] ?? null;

    if (empty($name)) {
        $error = 'Nama tidak boleh kosong.';
    } else {
        $stmt = $pdo->prepare('UPDATE users SET name = ?, phone = ?, gender = ?, birth_date = ? WHERE id = ?');
        $stmt->execute([$name, $phone ?: null, $gender ?: null, $birthDate ?: null, $user['id']]);
        $_SESSION['name'] = $name;
        $user = getCurrentUser();
        $success = 'Profil berhasil diperbarui!';
    }
}
?>

<!-- Breadcrumb -->
<div class="mb-6 flex items-center text-sm text-slate-500">
    <a href="/index.php" class="hover:text-primary hover:underline">Beranda</a>
    <span class="material-symbols-outlined text-[16px] mx-2">chevron_right</span>
    <span class="text-slate-900 dark:text-white font-medium">Profil Akun</span>
</div>
<h2 class="text-2xl font-bold mb-8">Pengaturan Akun</h2>

<div class="flex flex-col lg:flex-row gap-8 items-start">
    <!-- Sidebar -->
    <div class="w-full lg:w-1/4">
        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-6 shadow-sm sticky top-24">
            <div class="flex flex-col items-center pb-6 border-b border-slate-200 dark:border-slate-700 mb-6">
                <div class="size-24 rounded-full bg-primary flex items-center justify-center text-white font-bold text-3xl mb-4 shadow-lg">
                    <?= strtoupper(substr($user['name'] ?? 'U', 0, 1)) ?>
                </div>
                <h3 class="font-bold text-lg"><?= htmlspecialchars($user['name']) ?></h3>
                <p class="text-sm text-slate-500">Member sejak <?= date('M Y', strtotime($user['created_at'])) ?></p>
            </div>
            <nav class="space-y-2">
                <a href="/profile.php" class="flex items-center gap-3 px-4 py-3 rounded-xl bg-primary/10 text-primary font-bold">
                    <span class="material-symbols-outlined">person</span> Informasi Pribadi
                </a>
                <a href="/history.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors">
                    <span class="material-symbols-outlined">receipt_long</span> Riwayat Pembelian
                </a>
                <div class="h-px bg-slate-200 dark:bg-slate-700 my-2"></div>
                <a href="/auth/logout.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 font-medium transition-colors">
                    <span class="material-symbols-outlined">logout</span> Keluar Akun
                </a>
            </nav>
        </div>
    </div>

    <!-- Main Content -->
    <div class="w-full lg:w-3/4">
        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-8 shadow-sm">
            <h3 class="text-xl font-bold mb-6 pb-4 border-b border-slate-200 dark:border-slate-700">Informasi Pribadi</h3>

            <?php if ($success): ?>
            <div class="flex items-center gap-3 bg-green-50 border border-green-200 text-green-700 rounded-xl p-4 mb-6 text-sm">
                <span class="material-symbols-outlined text-[20px]">check_circle</span> <?= htmlspecialchars($success) ?>
            </div>
            <?php endif; ?>
            <?php if ($error): ?>
            <div class="flex items-center gap-3 bg-red-50 border border-red-200 text-red-700 rounded-xl p-4 mb-6 text-sm">
                <span class="material-symbols-outlined text-[20px]">error</span> <?= htmlspecialchars($error) ?>
            </div>
            <?php endif; ?>

            <form method="POST" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Nama Lengkap</label>
                        <input type="text" name="name" value="<?= htmlspecialchars($user['name'] ?? '') ?>"
                            class="w-full rounded-xl border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 focus:ring-primary focus:border-primary shadow-sm" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Email</label>
                        <input type="email" value="<?= htmlspecialchars($user['email'] ?? '') ?>" readonly
                            class="w-full rounded-xl border-slate-300 dark:border-slate-600 bg-slate-100 dark:bg-slate-900/50 text-slate-500 cursor-not-allowed shadow-sm">
                        <p class="text-[10px] text-slate-400 mt-1">Email tidak dapat diubah</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">No. Handphone</label>
                        <input type="tel" name="phone" value="<?= htmlspecialchars($user['phone'] ?? '') ?>"
                            class="w-full rounded-xl border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 focus:ring-primary focus:border-primary shadow-sm" placeholder="08...">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Tanggal Lahir</label>
                        <input type="date" name="birth_date" value="<?= htmlspecialchars($user['birth_date'] ?? '') ?>"
                            class="w-full rounded-xl border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 focus:ring-primary focus:border-primary shadow-sm">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Jenis Kelamin</label>
                        <div class="flex gap-6">
                            <?php foreach (['male' => 'Pria', 'female' => 'Wanita', 'other' => 'Lainnya'] as $val => $lbl): ?>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" name="gender" value="<?= $val ?>" class="text-primary focus:ring-primary" <?= ($user['gender'] ?? '') === $val ? 'checked' : '' ?>>
                                <span><?= $lbl ?></span>
                            </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <div class="pt-6 border-t border-slate-200 dark:border-slate-700 flex justify-end">
                    <button type="submit" class="bg-primary hover:bg-primary/90 text-white font-bold py-3 px-8 rounded-xl shadow-lg shadow-primary/30 transition-colors flex items-center gap-2">
                        <span class="material-symbols-outlined">save</span> Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include 'components/footer.php'; ?>
