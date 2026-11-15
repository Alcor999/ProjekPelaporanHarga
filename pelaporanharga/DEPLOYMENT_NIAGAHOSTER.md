# Panduan Hosting Backend Laravel di Subdomain Niagahoster

Backend Laravel ini dapat dijalankan di **subdomain shared hosting Niagahoster** (hPanel atau cPanel) selama versi PHP hosting memenuhi kebutuhan paket Composer (`>= 8.2`, disarankan `8.2` atau `8.3` dengan ekstensi `pdo_mysql`, `mbstring`, `openssl`, `fileinfo`, `gd`, `tokenizer`, `xml`).

Prinsip keamanan utama pada shared hosting adalah **memisahkan folder aplikasi inti Laravel (`pelaporanharga`) ke luar `public_html`**, sehingga file `.env` dan `storage/` tidak dapat diakses langsung lewat URL publik.

## 1. Persiapan Subdomain & Database di Niagahoster

1. Buka panel hosting Niagahoster (hPanel atau cPanel):
   - Masuk ke menu **Domains -> Subdomains**.
   - Buat subdomain, misalnya `api.domainanda.com` (atau `harga.domainanda.com`).
   - Pastikan document root mengarah ke `public_html/api` (sesuaikan nama subfolder dengan subdomain Anda).
   - Aktifkan **SSL Gratis (AutoSSL / Let's Encrypt)** dan aktifkan opsi Force HTTPS setelah DNS aktif.
2. Buat database MySQL:
   - Masuk ke **Databases -> MySQL Databases**.
   - Catat `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`, dan host MySQL (umumnya `localhost` atau `127.0.0.1`).
3. Atur versi PHP subdomain:
   - Masuk ke **PHP Configuration / MultiPHP Manager**, pilih **PHP 8.2** atau **8.3**.

## 2. Upload & Pemisahan Folder (`split public_html`)

Misalkan home directory akun hosting Anda adalah `/home/u123456789`:

1. Upload seluruh folder backend `pelaporanharga/` ke `/home/u123456789/pelaporanharga` (sejajar dengan `public_html`, BUKAN di dalam `public_html`).
   - Pastikan file `.env` lokal tidak ikut terupload.
   - Jika tidak menjalankan `composer install --no-dev --optimize-autoloader` di server, pastikan `vendor/` sudah di-build lokal pada versi PHP yang kompatibel (`composer install --no-dev --optimize-autoloader`).
2. Pindahkan atau salin **seluruh isi** `/home/u123456789/pelaporanharga/public/` ke document root subdomain `/home/u123456789/public_html/api/`:
   - Termasuk file tersembunyi `.htaccess`, `index.php`, `robots.txt`, dan `favicon.ico`.
3. Ubah `/home/u123456789/public_html/api/index.php` agar menunjuk ke folder inti `/home/u123456789/pelaporanharga`:

```php
<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

$corePath = '/home/u123456789/pelaporanharga';

if (file_exists($maintenance = $corePath.'/storage/framework/maintenance.php')) {
    require $maintenance;
}

require $corePath.'/vendor/autoload.php';

/** @var Application $app */
$app = require_once $corePath.'/bootstrap/app.php';

$app->handleRequest(Request::capture());
```

> Catatan: ganti `/home/u123456789` dengan path absolut home hosting Anda sendiri.

## 3. Konfigurasi `.env` & Permission

1. Salin `/home/u123456789/pelaporanharga/.env.production.example` menjadi `/home/u123456789/pelaporanharga/.env`.
2. Ubah permission file `.env` menjadi `0600` dan folder `storage/` + `bootstrap/cache/` menjadi `0755` (atau `0775` jika diminta PHP-FPM).
3. Isi variabel berikut di `.env`:
   - `APP_ENV=production`
   - `APP_DEBUG=false`
   - `APP_TIMEZONE=Asia/Jakarta`
   - `APP_URL=https://api.domainanda.com`
   - `PUBLIC_PATH=/home/u123456789/public_html/api`
   - `DB_CONNECTION=mysql`, `DB_HOST`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`
   - `SANCTUM_TOKEN_EXPIRATION=43200` (token mobile berlaku 30 hari)
   - `CORS_ALLOWED_ORIGINS=https://api.domainanda.com`
   - `ADMIN_INITIAL_PASSWORD=<sandi_admin_kuat_minimal_12_karakter>`

4. Generate `APP_KEY`:
   - Lewat SSH:
     ```bash
     cd ~/pelaporanharga
     php artisan key:generate --force
     ```
   - Jika tanpa SSH: generate key di mesin lokal (`php artisan key:generate --show`) lalu tempel nilainya ke `APP_KEY=` di `.env`.

## 4. Symlink `storage` untuk Foto Bukti

Foto bukti laporan petani disimpan di `storage/app/public/` dan diakses lewat URL `https://api.domainanda.com/storage/...`.

- **Jika ada akses SSH** (disarankan):
  ```bash
  ln -s /home/u123456789/pelaporanharga/storage/app/public /home/u123456789/public_html/api/storage
  ```
- **Jika paket hosting tidak punya SSH** (via File Manager sementara):
  1. Buat file sementara `/home/u123456789/public_html/api/buat_symlink.php`:
     ```php
     <?php
     $target = '/home/u123456789/pelaporanharga/storage/app/public';
     $link = '/home/u123456789/public_html/api/storage';
     echo symlink($target, $link) ? 'Symlink berhasil dibuat' : 'Gagal membuat symlink';
     ```
  2. Buka `https://api.domainanda.com/buat_symlink.php` sekali di browser.
  3. **Segera hapus** file `buat_symlink.php` setelah muncul pesan berhasil.

## 5. Migrasi Database & Seeding Awal

Lewat SSH:

```bash
cd ~/pelaporanharga
php artisan migrate --force
php artisan db:seed --class=AdminUserSeeder --force
php artisan db:seed --class=WilayahSeeder --force
php artisan db:seed --class=KomoditasSeeder --force
```

Catatan penting:
- **Jangan** menjalankan `SupplierSeeder` dummy atau membuat akun demo petani di server production kecuali memang dibutuhkan.
- `AdminUserSeeder` di production **mewajibkan** `ADMIN_INITIAL_PASSWORD` terisi di `.env` saat akun `admin@laporanharga.com` pertama kali dibuat.

## 6. Optimasi Cache Production

Setelah migrasi dan `.env` beres, jalankan:

```bash
cd ~/pelaporanharga
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Setiap kali Anda mengubah file `.env`, selalu jalankan ulang:

```bash
php artisan config:clear
php artisan config:cache
```

## 7. Verifikasi Pasca-Deploy

1. Cek halaman publik: buka `https://api.domainanda.com/` dan pastikan daftar harga komoditas tampil dengan skema `https://` serta tanpa error 500.
2. Cek panel admin: buka `https://api.domainanda.com/panel/login` dan masuk dengan `admin@laporanharga.com` serta password dari `ADMIN_INITIAL_PASSWORD`.
3. Cek endpoint health & API publik:
   - `GET https://api.domainanda.com/up` -> status `200`
   - `GET https://api.domainanda.com/api/public/harga-terkini` -> JSON `{"success":true,...}`
4. Uji aplikasi Flutter yang di-build dengan:
   `--dart-define=API_BASE_URL=https://api.domainanda.com/api`
   Pastikan login, upload foto laporan, dan preview foto di detail laporan berjalan normal.

## 8. Rollback & Backup Rutin

- Simpan backup database MySQL (`.sql`) via phpMyAdmin/cPanel sebelum menjalankan `php artisan migrate --force` pada pembaruan versi berikutnya.
- Aktifkan backup berkala Niagahoster dan simpan arsip folder `pelaporanharga/storage/app/public` agar foto laporan tetap aman.
