# ⚡ QUICK START (5 Menit)

## Langkah 1: Pastikan Backend Running (1 menit)
```bash
# Di terminal baru
cd /Users/mymac/Projects/pelaporanharga
php artisan serve
```
Output harus: `Starting Laravel development server: http://127.0.0.1:8000`

---

## Langkah 2: Run Flutter App (2 menit)
```bash
# Di terminal lain
cd /Users/mymac/Projects/pelaporan_harga_petani
flutter clean
flutter pub get
flutter run
```

---

## Langkah 3: Test Login (2 menit)

### Option A: Registrasi Akun Baru
1. Tap "Belum punya akun?" button
2. Isi form registrasi
3. Tap Register

### Option B: Gunakan Akun Existing
1. Email: `test@example.com` (atau sesuaikan dengan backend Anda)
2. Password: `password`
3. Tap Login

---

## Langkah 4: Buat Laporan Pertama
1. Di Dashboard, tap tombol "+" (FAB)
2. Pilih Wilayah dari dropdown
3. Pilih Komoditas
4. Input Harga (contoh: 15000)
5. Input Satuan (default: kg)
6. Pilih Tanggal Panen
7. Input Catatan (optional)
8. Tap "Buat Laporan"

---

## ✅ Done!

Sekarang Anda punya:
- ✅ App Flutter running
- ✅ Backend Laravel connected
- ✅ User registered
- ✅ First laporan created

### Next:
- Edit laporan: Tap card → Tap edit icon
- Lihat detail: Tap laporan card
- Hapus: Di detail screen, tap delete
- Logout: Tap profile menu → Logout

---

## 🐛 Jika Ada Error

### Error 1: "Connection refused"
**Fix**: Pastikan Laravel running
```bash
php artisan serve
```

### Error 2: "Login failed"
**Fix**: Pastikan user ada di database, atau buat user baru via register

### Error 3: "Build error"
**Fix**: Clean dan re-get dependencies
```bash
flutter clean
flutter pub get
```

### Error 4: "Dropdown kosong"
**Fix**: Backend harus return wilayah dan komoditas. Check backend.

---

## 📱 UI Overview

```
[Splash]
   ↓
[Login/Register]
   ↓
[Dashboard] ← List of laporan
   ↓
   ├→ [FAB +] → [Create Laporan]
   └→ [Tap Card] → [Laporan Detail]
```

---

## 🎯 API URLs

Backend: `http://192.168.123.222:8000/api`
(Ganti sesuai dengan URL backend Anda di `lib/core/constants/api_constants.dart`)

---

## ✨ Features Ready

- ✅ Login/Register
- ✅ Create Laporan
- ✅ Edit Laporan
- ✅ Delete Laporan  
- ✅ View Detail
- ✅ Status colors
- ✅ Dropdown wilayah & komoditas
- ✅ Date picker
- ✅ Error handling
- ✅ Loading states

---

**Done! Happy Coding! 🚀**


