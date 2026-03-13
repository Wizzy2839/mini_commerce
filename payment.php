<?php
require_once __DIR__ . '/config/functions.php';
require_once __DIR__ . '/config/midtrans.php';
requireLogin('/auth/login.php');

$pdo  = getDB();
$txId = (int)($_GET['id'] ?? 0);

if ($txId <= 0) { header('Location: /index.php'); exit; }

// Fetch transaction (verify ownership)
$stmt = $pdo->prepare('SELECT * FROM transactions WHERE id = ? AND user_id = ?');
$stmt->execute([$txId, $_SESSION['user_id']]);
$tx = $stmt->fetch();

if (!$tx) { header('Location: /index.php'); exit; }

// If already paid/processing, go to success page
if (in_array($tx['status'], ['processing', 'delivered', 'completed'])) {
    header('Location: /order-success.php?id=' . $txId); exit;
}

// If no snap_token, Midtrans wasn't reached - show error
$snapToken  = $tx['snap_token'] ?? '';
$clientKey  = MIDTRANS_CLIENT_KEY;
$snapJsUrl  = MIDTRANS_SNAP_JS;
$orderTotal = $tx['grand_total'];
?>
<?php include 'components/header.php'; ?>

<div class="max-w-2xl mx-auto py-8">
    <!-- Header -->
    <div class="text-center mb-8">
        <div class="relative inline-flex items-center justify-center size-24 bg-primary/10 rounded-full mb-4">
            <span class="material-symbols-outlined text-primary text-[56px]">account_balance_wallet</span>
            <span class="absolute inset-0 rounded-full border-4 border-primary/20 animate-ping opacity-30"></span>
        </div>
        <h2 class="text-3xl font-bold text-slate-900 dark:text-white">Selesaikan Pembayaran</h2>
        <p class="text-slate-500 mt-2">Klik tombol di bawah untuk membuka halaman pembayaran yang aman.</p>
    </div>

    <!-- Order Info Card -->
    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-6 shadow-sm mb-6">
        <div class="grid grid-cols-2 gap-4 text-sm">
            <div>
                <p class="text-slate-500 font-medium">ID Pesanan</p>
                <p class="font-bold font-mono mt-1">ORD-<?= str_pad($tx['id'], 8, '0', STR_PAD_LEFT) ?></p>
            </div>
            <div>
                <p class="text-slate-500 font-medium">Status</p>
                <span class="inline-flex items-center gap-1.5 mt-1 px-2.5 py-1 rounded-full text-xs font-bold bg-yellow-100 text-yellow-700 border border-yellow-200">
                    <span class="size-1.5 rounded-full bg-yellow-500 animate-pulse"></span>
                    Menunggu Pembayaran
                </span>
            </div>
            <div>
                <p class="text-slate-500 font-medium">Tanggal</p>
                <p class="font-bold mt-1"><?= date('d M Y, H:i', strtotime($tx['created_at'])) ?> WIB</p>
            </div>
            <div>
                <p class="text-slate-500 font-medium">Total Pembayaran</p>
                <p class="font-bold text-primary text-lg mt-0.5"><?php
                    require_once __DIR__ . '/config/functions.php';
                    echo formatRupiah((float)$orderTotal);
                ?></p>
            </div>
        </div>
    </div>

    <?php if (empty($snapToken)): ?>
    <!-- No snap token - show error -->
    <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-2xl p-6 text-center mb-6">
        <span class="material-symbols-outlined text-red-500 text-[40px] mb-2">error</span>
        <h3 class="font-bold text-red-700 dark:text-red-400">Token Pembayaran Tidak Tersedia</h3>
        <p class="text-sm text-red-600 dark:text-red-400 mt-1">
            Terjadi kendala saat menghubungi sistem pembayaran. Silakan hubungi admin atau coba buat pesanan baru.
        </p>
    </div>
    <a href="/cart.php" class="block w-full text-center py-4 font-bold border-2 border-primary text-primary rounded-xl hover:bg-primary hover:text-white transition-colors">
        Kembali ke Keranjang
    </a>
    <?php else: ?>

    <!-- Pay Button -->
    <button id="payBtn"
        onclick="triggerSnapPay()"
        class="w-full bg-primary hover:bg-primary/90 disabled:opacity-70 disabled:cursor-not-allowed text-white font-bold py-4 rounded-xl flex items-center justify-center gap-2 transition-colors shadow-lg shadow-primary/30 mb-4">
        <span class="material-symbols-outlined" id="payIcon">payments</span>
        <span id="payText">Bayar Sekarang</span>
    </button>

    <a href="/history.php" class="block w-full text-center py-3 font-semibold text-slate-500 hover:text-slate-700 dark:hover:text-slate-300 transition-colors text-sm">
        Bayar Nanti (cek riwayat pesanan)
    </a>

    <!-- Secure badge -->
    <div class="flex items-center justify-center gap-2 mt-6 text-xs text-slate-400">
        <span class="material-symbols-outlined text-[16px] text-green-500">verified_user</span>
        Pembayaran diproses secara aman oleh Midtrans
    </div>

    <!-- Midtrans Snap.js -->
    <script src="<?= htmlspecialchars($snapJsUrl) ?>" data-client-key="<?= htmlspecialchars($clientKey) ?>"></script>
    <script>
    const SNAP_TOKEN  = <?= json_encode($snapToken) ?>;
    const SUCCESS_URL = '/order-success.php?id=<?= $txId ?>';

    function triggerSnapPay() {
        const btn  = document.getElementById('payBtn');
        const icon = document.getElementById('payIcon');
        const text = document.getElementById('payText');

        btn.disabled = true;
        icon.innerText = 'refresh';
        icon.classList.add('animate-spin');
        text.innerText = 'Membuka halaman pembayaran...';

        snap.pay(SNAP_TOKEN, {
            onSuccess: function(result) {
                // Payment confirmed - redirect to success page
                window.location.href = SUCCESS_URL;
            },
            onPending: function(result) {
                // Payment instruction sent (e.g. VA number shown)
                btn.disabled = false;
                icon.innerText = 'account_balance';
                icon.classList.remove('animate-spin');
                text.innerText = 'Cek Status / Lihat Instruksi';
                showAlert('info', 'Instruksi pembayaran sudah dikirim! Selesaikan pembayaran sesuai instruksi yang diberikan.');
            },
            onError: function(result) {
                btn.disabled = false;
                icon.innerText = 'payments';
                icon.classList.remove('animate-spin');
                text.innerText = 'Coba Lagi';
                showAlert('error', 'Terjadi kesalahan saat memproses pembayaran. Silakan coba lagi.');
            },
            onClose: function() {
                // User closed popup without paying
                btn.disabled = false;
                icon.innerText = 'payments';
                icon.classList.remove('animate-spin');
                text.innerText = 'Bayar Sekarang';
            }
        });
    }

    function showAlert(type, message) {
        const existing = document.getElementById('payAlert');
        if (existing) existing.remove();

        const colors = {
            info: 'bg-blue-50 border-blue-200 text-blue-700 dark:bg-blue-900/20 dark:border-blue-800 dark:text-blue-400',
            error: 'bg-red-50 border-red-200 text-red-700 dark:bg-red-900/20 dark:border-red-800 dark:text-red-400',
        };
        const icons = { info: 'info', error: 'error' };

        const div = document.createElement('div');
        div.id = 'payAlert';
        div.className = `flex items-start gap-3 p-4 rounded-xl border mt-4 ${colors[type]}`;
        div.innerHTML = `<span class="material-symbols-outlined text-[20px] mt-0.5">${icons[type]}</span>
                         <p class="text-sm">${message}</p>`;
        document.getElementById('payBtn').insertAdjacentElement('afterend', div);
    }

    // Auto-trigger on page load for smoother UX
    window.addEventListener('load', function() {
        setTimeout(triggerSnapPay, 500);
    });
    </script>
    <?php endif; ?>
</div>

<?php include 'components/footer.php'; ?>
