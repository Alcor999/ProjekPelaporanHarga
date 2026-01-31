# Perbaikan CSRF Token Mismatch untuk Mobile App

## Masalah yang Ditemukan

Aplikasi Flutter mendapatkan error **"CSRF token mismatch"** ketika mencoba mengakses API endpoints.

## Penyebab

Middleware `EnsureFrontendRequestsAreStateful` dari Sanctum diaktifkan untuk API routes. Middleware ini:
- ✅ **Cocok untuk**: SPA (Single Page Applications) dengan cookie-based authentication
- ❌ **Tidak cocok untuk**: Mobile apps dengan token-based authentication (Bearer token)

Mobile apps menggunakan Bearer token authentication yang **TIDAK MEMERLUKAN CSRF token**.

## Perbaikan yang Telah Dilakukan

### 1. Menghapus Middleware EnsureFrontendRequestsAreStateful

Di `bootstrap/app.php`, middleware `EnsureFrontendRequestsAreStateful` sudah dihapus dari API middleware stack.

**Sebelum:**
```php
$middleware->api(\Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class);
```

**Sesudah:**
```php
// Note: EnsureFrontendRequestsAreStateful is NOT needed for mobile apps
// It's only for SPA with cookie-based authentication which requires CSRF
// Mobile apps use token-based authentication (Bearer token) which doesn't need CSRF
// $middleware->api(\Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class);
```

### 2. Clear Cache

Sudah dijalankan:
- `php artisan config:clear`
- `php artisan route:clear`
- `php artisan cache:clear`

## Langkah yang Harus Dilakukan

### 1. **RESTART SERVER LARAVEL**

**PENTING:** Server Laravel HARUS di-restart setelah perubahan ini!

```bash
# Stop server yang sedang berjalan (Ctrl+C)
# Kemudian jalankan lagi:
cd /Users/mymac/Projects/pelaporanharga
php artisan serve --host=0.0.0.0 --port=8000
```

### 2. **HOT RESTART APLIKASI FLUTTER**

Setelah server Laravel restart, lakukan Hot Restart pada aplikasi Flutter:

**Dari Terminal:**
- Tekan `R` (huruf R kapital) di terminal tempat Flutter app berjalan

**Dari VS Code:**
- `Cmd + Shift + P` → `Flutter: Hot Restart`
- Atau shortcut: `Cmd + Shift + F5`

### 3. **Test Login**

Coba login lagi di aplikasi Flutter. Error CSRF token mismatch seharusnya sudah hilang.

## Bagaimana Cara Kerja Authentication untuk Mobile App?

### Mobile App (Flutter) - Token-Based Authentication:
1. User login dengan email/password → POST `/api/login`
2. Server memvalidasi credentials
3. Server mengembalikan **Bearer token** (bukan cookie)
4. Flutter app menyimpan token di SharedPreferences
5. Setiap request berikutnya menyertakan token di header:
   ```
   Authorization: Bearer <token>
   ```
6. Server memvalidasi token (tidak perlu CSRF token)

### SPA (Web) - Cookie-Based Authentication (untuk admin panel):
1. User login → POST `/admin/login`
2. Server mengembalikan cookie (session)
3. Browser otomatis mengirim cookie di setiap request
4. Server memvalidasi cookie + CSRF token

**Perbedaan utama:** Mobile app menggunakan **token** (tidak perlu CSRF), web app menggunakan **cookie** (perlu CSRF).

## Verifikasi

### 1. Pastikan Routes Tidak Memerlukan CSRF

Check routes:
```bash
php artisan route:list | grep api/login
```

Harus menampilkan:
```
POST   api/login  ......................... Api\AuthController@login
```

### 2. Pastikan API Routes Menggunakan Middleware 'api' (Bukan 'web')

Di `routes/api.php`, routes harus di dalam file `api.php` (bukan `web.php`).

Routes di `api.php` secara default menggunakan middleware `api` yang **TIDAK MEMERLUKAN CSRF token**.

### 3. Test dari Browser (OPTIONAL)

Test endpoint login dari browser (akan error 405 - normal karena endpoint hanya menerima POST):
```
http://192.168.100.221:8000/api/login
```

- ✅ Jika muncul "Method Not Allowed (405)" = Server bisa diakses, routes benar
- ❌ Jika muncul "CSRF token mismatch" = Server belum di-restart atau cache belum clear

## Troubleshooting

### Masalah: Masih ada CSRF token mismatch

**Kemungkinan penyebab:**
1. Server Laravel belum di-restart
2. Cache belum di-clear

**Solusi:**
```bash
# Clear semua cache
php artisan config:clear
php artisan route:clear
php artisan cache:clear

# Restart server
php artisan serve --host=0.0.0.0 --port=8000
```

### Masalah: Routes masih menggunakan middleware web

**Kemungkinan penyebab:**
- Routes terdaftar di `routes/web.php` (bukan `routes/api.php`)

**Solusi:**
- Pastikan semua API routes ada di `routes/api.php`
- Routes di `api.php` otomatis menggunakan middleware `api` (tanpa CSRF)

## Catatan Penting

⚠️ **Untuk Mobile App:**
- ✅ Gunakan token-based authentication (Bearer token)
- ✅ Tidak perlu CSRF token
- ✅ Middleware `EnsureFrontendRequestsAreStateful` TIDAK diperlukan

⚠️ **Untuk Web App (Admin Panel):**
- ✅ Gunakan cookie-based authentication
- ✅ Perlu CSRF token
- ✅ Routes di `routes/web.php` menggunakan middleware `web` dengan CSRF protection

## Status Setelah Perbaikan

✅ Middleware `EnsureFrontendRequestsAreStateful` dihapus dari API routes  
✅ Cache sudah di-clear  
✅ API routes tidak memerlukan CSRF token  
✅ Mobile app menggunakan Bearer token authentication  
✅ Flutter app siap untuk melakukan request tanpa CSRF token

---

**Langkah selanjutnya:** Restart server Laravel dan Hot Restart aplikasi Flutter, kemudian coba login lagi.
