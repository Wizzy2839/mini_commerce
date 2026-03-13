# 🛒 ShopEase — Mini E-Commerce

![PHP](https://img.shields.io/badge/PHP-8.x-777BB4?style=flat-square&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-8.x-4479A1?style=flat-square&logo=mysql&logoColor=white)
![Tailwind CSS](https://img.shields.io/badge/Tailwind_CSS-3.x-06B6D4?style=flat-square&logo=tailwindcss&logoColor=white)
![Midtrans](https://img.shields.io/badge/Midtrans-Snap_API-003580?style=flat-square)
![License](https://img.shields.io/badge/License-MIT-green?style=flat-square)

ShopEase adalah aplikasi e-commerce mini berbasis **PHP 8 + MySQL** yang dilengkapi dengan integrasi **Midtrans Snap** sebagai payment gateway. Dibangun dengan tampilan modern menggunakan Tailwind CSS, ShopEase mencakup alur belanja lengkap dari ujung ke ujung — mulai dari registrasi akun, browsing produk, checkout, hingga notifikasi pembayaran otomatis via webhook.

## ✨ Fitur Utama

### Sisi Pelanggan
- **Autentikasi lengkap** — Registrasi, login, dan logout dengan password hashing bcrypt. Routing otomatis berdasarkan peran (admin/user).
- **Katalog produk** — Grid produk dengan pencarian kata kunci, filter kategori, dan paginasi 12 item per halaman.
- **Detail produk** — Gambar, harga, rating, stok real-time, dan deskripsi. Badge otomatis muncul saat stok menipis (≤3) atau habis.
- **Keranjang belanja** — Tambah, ubah kuantitas, hapus item, atau kosongkan keranjang. Stok divalidasi di setiap operasi.
- **Checkout terproteksi** — Kalkulasi otomatis subtotal + ongkir (Rp 25.000) + PPN 11% + biaya layanan (Rp 2.500). Dilindungi token anti-double-order.
- **Pembayaran via Midtrans Snap** — Popup pembayaran in-app yang mendukung VA semua bank, GoPay, QRIS, kartu kredit/debit, dan lainnya.
- **Riwayat pesanan** — Timeline visual 4 langkah (Dibayar → Diproses → Dikirim → Selesai) dengan badge status berwarna.
- **Pembatalan pesanan** — Batalkan pesanan pending; stok dikembalikan secara atomik menggunakan database transaction.
- **Profil pengguna** — Edit nama, telepon, tanggal lahir, dan jenis kelamin.

### Sisi Admin
- **Dashboard statistik** — Total penjualan, total pesanan, total produk, dan pelanggan baru (30 hari terakhir) secara real-time.
- **Manajemen produk** — CRUD lengkap dengan modal dialog (tanpa reload halaman). Filter pencarian dan filter kategori.
- **Manajemen kategori** — Tambah/edit kategori, toggle aktif/nonaktif, dan proteksi hapus (tidak bisa dihapus jika masih ada produk).
- **Laporan transaksi** — Filter 3 dimensi (status + tanggal mulai + tanggal akhir). Ubah status pesanan langsung dari dropdown di tabel.

### Keamanan & Teknis
- Validasi berlapis: client-side (HTML5 + JS) dan server-side (PHP regex)
- Webhook Midtrans dengan verifikasi signature SHA-512
- Database transactions untuk operasi kritis (checkout & pembatalan)
- Singleton PDO connection pattern
- Post/Redirect/Get (PRG) pattern di semua aksi form
- E2E testing script berbasis PHP CLI (`tests/e2e_test.php`)

---

## 🛠️ Teknologi yang Digunakan

| Teknologi | Versi | Kegunaan |
|-----------|-------|----------|
| PHP | 8.x | Backend utama |
| MySQL | 8.x | Database relasional |
| PDO | — | Abstraksi database dengan prepared statements |
| Midtrans Snap API | — | Payment gateway |
| Tailwind CSS | 3.x (CDN) | Styling antarmuka |
| Google Material Symbols | — | Library ikon |
| JavaScript (Vanilla) | — | Interaksi UI & integrasi Snap.js |
| Laragon | 6.x | Lingkungan pengembangan lokal |

---

## 📁 Struktur Proyek

```
mini_commerce/
│
├── auth/                        # Autentikasi
│   ├── login.php
│   ├── register.php
│   └── logout.php
│
├── admin/                       # Panel Admin (requireAdmin)
│   ├── index.php                # Dashboard statistik
│   ├── products.php             # Kelola produk
│   ├── product_action.php       # CRUD produk
│   ├── categories.php           # Kelola kategori
│   ├── category_action.php      # CRUD kategori
│   ├── reports.php              # Laporan transaksi
│   ├── report_action.php        # Update status pesanan
│   └── components/
│       ├── admin_header.php
│       ├── admin_sidebar.php
│       └── admin_footer.php
│
├── components/                  # Komponen shared (customer)
│   ├── header.php               # Navbar + cart counter
│   └── footer.php
│
├── config/                      # Konfigurasi inti
│   ├── database.php             # Singleton PDO connection
│   ├── functions.php            # Helper functions
│   └── midtrans.php             # Midtrans config & API helper
│
├── tests/
│   └── e2e_test.php             # End-to-end test script (PHP CLI)
│
├── index.php                    # Beranda / katalog produk
├── product.php                  # Detail produk
├── cart.php                     # Halaman keranjang
├── cart_action.php              # Handler aksi keranjang
├── checkout.php                 # Halaman checkout
├── process_checkout.php         # Proses checkout + Midtrans token
├── payment.php                  # Halaman pembayaran Snap
├── payment_notification.php     # Webhook Midtrans
├── order-success.php            # Halaman sukses / detail pesanan
├── history.php                  # Riwayat pesanan
├── cancel_order.php             # Batalkan pesanan
├── profile.php                  # Profil pengguna
├── shopease.sql                 # Skema + seed data database
└── test_midtrans_channels.php   # Debug Midtrans API
```

---

## 🗄️ Skema Database

Database `shopease` terdiri dari 6 tabel dengan relasi sebagai berikut:

```
users ──────────────────────────────────────────┐
  id, name, email, password, role, ...           │
                                                 │
categories ──────────────┐                       │
  id, name, icon,        │                       │
  is_active              │                       │
                         ▼                       ▼
products ────────── cart_items ──────── transactions
  id, category_id,   id, user_id,       id, user_id,
  name, price,       product_id,        grand_total,
  stock, rating      quantity           status,
                                        snap_token,
                                        midtrans_order_id
                                             │
                                             ▼
                                   transaction_details
                                     id, transaction_id,
                                     product_id,
                                     quantity,
                                     price_at_purchase
```

---

## 🚀 Instalasi & Setup

### Prasyarat
Pastikan kamu sudah menginstall **Laragon** (versi 6 direkomendasikan) yang sudah menyertakan PHP 8, MySQL 8, dan Apache.

### Langkah 1 — Clone repositori

```bash
git clone https://github.com/username/shopease.git
cd shopease
```

### Langkah 2 — Import database

Buka **phpMyAdmin** di Laragon (`http://localhost/phpmyadmin`), buat database baru bernama `shopease`, lalu import file:

```
shopease.sql
```

File ini sudah berisi skema tabel lengkap dan seed data awal (kategori, produk contoh, serta akun admin & demo user).

### Langkah 3 — Konfigurasi database

Buka `config/database.php` dan sesuaikan kredensial jika diperlukan:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'shopease');
define('DB_USER', 'root');
define('DB_PASS', '');       // Sesuaikan jika Laragon kamu pakai password
```

### Langkah 4 — Konfigurasi Midtrans

Buka `config/midtrans.php` dan isi dengan Server Key & Client Key dari dashboard Midtrans Sandbox kamu:

```php
define('MIDTRANS_SERVER_KEY', 'Mid-server-xxxxxxxxxxxxxxxx');
define('MIDTRANS_CLIENT_KEY', 'Mid-client-xxxxxxxxxxxxxxxx');
define('MIDTRANS_IS_PRODUCTION', false); // Tetap false untuk testing
```

> **Cara mendapatkan API Key:** Daftar di [dashboard.sandbox.midtrans.com](https://dashboard.sandbox.midtrans.com) → Settings → Access Keys.

### Langkah 5 — Konfigurasi Webhook (opsional untuk testing lokal)

Agar notifikasi pembayaran Midtrans bisa diterima di lokal, gunakan **ngrok** untuk membuat tunnel:

```bash
ngrok http 8080
```

Lalu daftarkan URL berikut di Midtrans Dashboard → Settings → Payment Notification URL:

```
https://xxxx.ngrok.io/payment_notification.php
```

### Langkah 6 — Jalankan aplikasi

Akses aplikasi melalui browser:

```
http://localhost/mini_commerce
```

---

## 👤 Akun Demo

Setelah import database, dua akun demo sudah tersedia:

| Peran | Email | Password |
|-------|-------|----------|
| **Customer** | user@shopease.com | user123 |
| **Admin** | admin@shopease.com | admin123 |

---

## 🧪 Menjalankan E2E Test

ShopEase dilengkapi skrip pengujian end-to-end yang mensimulasikan alur lengkap pengguna (login → tambah ke keranjang → checkout → verifikasi pesanan) menggunakan PHP CLI + cURL.

Pastikan server Laragon sudah berjalan, lalu jalankan dari terminal:

```bash
php tests/e2e_test.php
```

Output akan menampilkan hasil setiap langkah pengujian dengan label `[PASS]` (hijau), `[FAIL]` (merah), atau `[SKIP]` (kuning).

---

## 💳 Alur Pembayaran Midtrans

Berikut alur lengkap dari checkout hingga konfirmasi pembayaran:

```
Pengguna klik "Pesan Sekarang"
        │
        ▼
process_checkout.php
  ├── Validasi form & stok
  ├── INSERT ke tabel transactions (status: pending)
  ├── INSERT ke transaction_details + kurangi stok
  ├── POST ke Midtrans Snap API → dapat snap_token
  ├── UPDATE transactions (simpan snap_token)
  └── Redirect ke payment.php
        │
        ▼
payment.php
  └── Snap.js membuka popup pembayaran
        │
        ├── onSuccess → redirect ke order-success.php
        ├── onPending → tampilkan instruksi
        ├── onError   → tampilkan pesan error
        └── onClose   → tunggu / bayar nanti
        │
        ▼ (di background, dari server Midtrans)
payment_notification.php (Webhook)
  ├── Verifikasi signature SHA-512
  ├── settlement/capture → UPDATE status: processing
  └── deny/cancel/expire → UPDATE status: cancelled
```

---

## 🔐 Ringkasan Keamanan

Beberapa praktik keamanan yang diimplementasikan dalam ShopEase:

- **bcrypt password hashing** — `password_hash()` dengan `PASSWORD_BCRYPT` untuk menyimpan password.
- **Prepared statements PDO** — Semua query menggunakan `prepare()` + `execute()` untuk mencegah SQL Injection.
- **Token anti-double-order** — Token 64-karakter unik per sesi checkout mencegah duplikasi pesanan.
- **Webhook signature verification** — `hash('sha512', ...)` memverifikasi bahwa notifikasi benar-benar dari Midtrans.
- **Role-based access control** — `requireLogin()` dan `requireAdmin()` dipanggil di setiap halaman yang dilindungi.
- **Database transactions** — `BEGIN/COMMIT/ROLLBACK` pada operasi checkout dan pembatalan untuk menjaga konsistensi data.
- **FOR UPDATE row locking** — Mencegah race condition saat pembatalan pesanan bersamaan.
- **Whitelist validation** — Status pesanan yang dikirim via POST divalidasi terhadap daftar status yang diizinkan.

---

## 📝 Lisensi

Proyek ini dibuat untuk keperluan pembelajaran dan didistribusikan di bawah lisensi [MIT](LICENSE).

---

## 🙋 Kontribusi

Pull request sangat diterima! Untuk perubahan besar, harap buka issue terlebih dahulu untuk mendiskusikan apa yang ingin diubah.

---

<p align="center">Dibuat dengan ❤️ menggunakan PHP, MySQL, dan Midtrans</p>
