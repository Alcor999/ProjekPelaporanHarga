# Perbaikan Koneksi API - Flutter ke Laravel

## Masalah yang Ditemukan

1. **API routes tidak terdaftar**: Di `bootstrap/app.php` tidak ada konfigurasi untuk API routes
2. **Double prefix**: Di `routes/api.php` ada double prefix `api` yang tidak diperlukan

## Perbaikan yang Telah Dilakukan

1. ✅ Menambahkan `api: __DIR__.'/../routes/api.php'` di `bootstrap/app.php`
2. ✅ Menghapus double prefix `api` di `routes/api.php`
3. ✅ Merestrukturisasi routes agar lebih jelas
4. ✅ Route cache sudah di-clear dan di-rebuild

## Langkah yang Harus Dilakukan

### 1. **RESTART SERVER LARAVEL**

**PENTING:** Server Laravel HARUS di-restart setelah perubahan ini!

```bash
# Stop server yang sedang berjalan (Ctrl+C)
# Kemudian jalankan lagi dengan:
cd /Users/mymac/Projects/pelaporanharga
php artisan serve --host=0.0.0.0 --port=8000
```

**CATATAN PENTING:**
- Gunakan `--host=0.0.0.0` agar server bisa diakses dari network (bukan hanya localhost)
- Tanpa `--host=0.0.0.0`, perangkat Android tidak bisa mengakses server

### 2. **Pastikan Perangkat Android dan Komputer di Jaringan yang Sama**

- Android device dan komputer harus terhubung ke Wi-Fi yang sama
- IP komputer: `192.168.100.221` (sudah dikonfigurasi di Flutter app)

### 3. **Verifikasi Routes**

Setelah server restart, verifikasi routes dengan:

```bash
php artisan route:list | grep api/login
```

Harus menampilkan:
```
POST   api/login  ......................... Api\AuthController@login
```

### 4. **Test Koneksi dari Browser**

Buka browser dan coba akses:
- **GET request** (akan error 405 - normal, karena endpoint hanya menerima POST):
  ```
  http://192.168.100.221:8000/api/login
  ```

- Jika muncul error "Method Not Allowed" = Server sudah bisa diakses ✅
- Jika muncul "Connection refused" = Server belum berjalan dengan `--host=0.0.0.0`

### 5. **Restart Aplikasi Flutter**

Setelah server Laravel restart:
- Hot restart aplikasi Flutter
- Coba login lagi

## Troubleshooting

### Masalah: Connection Timeout

**Kemungkinan penyebab:**
1. Server tidak berjalan dengan `--host=0.0.0.0`
2. Firewall memblokir port 8000
3. IP komputer berubah (periksa dengan `ipconfig getifaddr en0`)

**Solusi:**
```bash
# Cek IP komputer saat ini
ipconfig getifaddr en0

# Update IP di Flutter app jika berubah:
# File: pelaporan_harga_petani/lib/core/constants/api_constants.dart
# Line 9: Ganti IP dengan IP baru
```

### Masalah: Method Not Allowed (405)

Ini **NORMAL** jika mengakses dari browser dengan GET request.
Endpoint `/api/login` hanya menerima POST request dari Flutter app.

### Masalah: CORS Error

CORS sudah dikonfigurasi dengan `allowed_origins => ['*']` yang mengizinkan semua origin.

Jika masih ada masalah CORS:
1. Pastikan `config/cors.php` sudah ada (sudah di-publish)
2. Clear config cache: `php artisan config:clear`
3. Restart server

## Verifikasi Akhir

1. ✅ Server Laravel berjalan dengan `--host=0.0.0.0 --port=8000`
2. ✅ Routes terdaftar: `php artisan route:list | grep api/login`
3. ✅ CORS dikonfigurasi: `config/cors.php` exists
4. ✅ Flutter app menggunakan IP yang benar: `192.168.100.221:8000/api`
5. ✅ Android device dan komputer di jaringan Wi-Fi yang sama
6. ✅ Aplikasi Flutter sudah di-restart

## API Endpoints yang Tersedia

- `POST /api/login` - Login
- `POST /api/register` - Register petani baru
- `POST /api/logout` - Logout (require auth)
- `GET /api/profile` - Get profile (require auth)
- `GET /api/ref/wilayah` - Get list wilayah
- `GET /api/ref/komoditas` - Get list komoditas
- `POST /api/petani/laporan` - Create laporan (require auth + role:petani)
- `GET /api/petani/laporan` - Get list laporan (require auth + role:petani)
