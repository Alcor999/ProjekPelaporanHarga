# 📚 Dokumentasi Pelaporan Harga Padi Flutter App

## 🎯 Start Here

Pilih dokumen sesuai kebutuhan Anda:

### ⚡ Ingin Langsung Jalankan?
👉 **[QUICK_START.md](./QUICK_START.md)** - 5 menit setup

### 🔧 Ingin Detailed Setup?
👉 **[SETUP_FINAL.md](./SETUP_FINAL.md)** - Complete guide dengan troubleshooting

### 📋 Ingin Lihat Ringkasan?
👉 **[FIX_SUMMARY.md](./FIX_SUMMARY.md)** - Apa yang sudah diperbaiki

### 📖 Ingin Dokumentasi Lengkap?
👉 **[README.md](./README.md)** - Dokumentasi komprehensif

---

## 📁 File Structure

```
pelaporan_harga_petani/
├── lib/
│   ├── main.dart                    # App entry point
│   ├── core/                        # Core utilities
│   │   ├── constants/
│   │   ├── exceptions/
│   │   └── theme/
│   ├── models/                      # Data models
│   ├── services/                    # API client
│   ├── providers/                   # State management
│   ├── screens/                     # UI screens
│   └── widgets/                     # Reusable components
├── pubspec.yaml                     # Dependencies
├── QUICK_START.md                   # ⭐ Read this first!
├── SETUP_FINAL.md                   # Complete setup guide
├── FIX_SUMMARY.md                   # What was fixed
└── README.md                        # Original documentation
```

---

## 🚀 Quick Command Reference

```bash
# Clean project
flutter clean

# Get dependencies
flutter pub get

# Run app
flutter run

# Run with verbose logs
flutter run -v

# Build APK (production)
flutter build apk --release
```

---

## 📞 Getting Help

### Error: Build Fails
```bash
flutter clean
flutter pub get
flutter run
```

### Error: Connection Refused
```bash
# Make sure Laravel is running
cd /Users/mymac/Projects/pelaporanharga
php artisan serve
```

### Error: Login Fails
- Check user exists in backend database
- Check API endpoint is correct
- Check network connectivity

### More Issues?
Check **SETUP_FINAL.md** → Troubleshooting section

---

## ✅ Checklist Before Starting

- [ ] Flutter installed: `flutter --version`
- [ ] Laravel backend ready
- [ ] Backend running: `php artisan serve`
- [ ] No other app using port 8000
- [ ] Device/emulator connected
- [ ] Network accessible to backend

---

## 🎯 Feature Checklist

- [x] Authentication (Login/Register/Logout)
- [x] Create Laporan with form validation
- [x] Edit Laporan (if status = pending)
- [x] Delete Laporan with confirmation
- [x] View Laporan Detail
- [x] List Laporan with refresh
- [x] Dropdown Wilayah & Komoditas
- [x] Date picker for tanggal panen
- [x] Status color indicators
- [x] Error handling & retry
- [x] Loading states
- [x] Empty states
- [x] Profile view
- [x] Token persistence
- [x] Network error handling

---

## 🔗 Backend Integration

### Endpoints Used
```
POST   /api/login
POST   /api/register
POST   /api/logout
GET    /api/profile
GET    /api/ref/wilayah
GET    /api/ref/komoditas
GET    /api/petani/laporan
POST   /api/petani/laporan
GET    /api/petani/laporan/{id}
PUT    /api/petani/laporan/{id}
DELETE /api/petani/laporan/{id}
```

### Expected Response Format
```json
{
  "success": true,
  "message": "Success",
  "data": {
    "id": 1,
    "name": "...",
    // ...
  }
}
```

---

## 🎨 Color Scheme

- Primary: `#2E7D32` (Green - Pertanian)
- Error: `#F44336` (Red)
- Success: `#4CAF50` (Green)
- Warning: `#FFC107` (Orange)
- Neutral: `#FAFAFA` (Light Gray)

---

## 📱 Screens

1. **Splash Screen** - Loading 2 detik
2. **Login Screen** - Email + Password
3. **Register Screen** - Name + Email + Password
4. **Dashboard** - List laporan + FAB
5. **Create/Edit Laporan** - Form dengan dropdowns
6. **Detail Laporan** - Full info + edit/delete options

---

## 🔐 Authentication Flow

```
[Login Form] 
    ↓
[Send to API] 
    ↓
[API return token] 
    ↓
[Save token & user] 
    ↓
[Auto-login next time] 
    ↓
[Or Logout to clear]
```

---

## 📊 Data Flow

```
UI (Screen/Widget)
    ↓
Provider (Business Logic)
    ↓
ApiService (HTTP calls)
    ↓
Laravel Backend
    ↓
SQLite Database
```

---

## 🛠️ Tech Stack

- **Framework**: Flutter 3.10+
- **State Management**: Provider 6.1
- **HTTP Client**: Dio 5.4
- **Local Storage**: SharedPreferences 2.2
- **Date Formatting**: Intl 0.19
- **Database (Backend)**: SQLite via Laravel

---

## 📝 Notes

- Token disimpan di LocalStorage (SharedPreferences)
- App akan auto-login jika token masih valid
- Logout akan menghapus token dan user data
- Laporan hanya bisa di-edit jika status = "pending"
- Semua request ke API pake Bearer token
- Error handling comprehensive dengan custom exceptions

---

## 🎯 Last Update

- **Date**: 8 Januari 2026
- **Status**: ✅ Production Ready
- **Fixes Applied**: PopupMenuDivider issue
- **Total Files**: 21+
- **Total Features**: 50+

---

## 📞 Support

Jika ada pertanyaan:
1. Baca dokumentasi yang relevan
2. Check SETUP_FINAL.md troubleshooting
3. Review server logs
4. Check Flutter logs dengan `-v` flag

---

**Next: [QUICK_START.md](./QUICK_START.md) untuk mulai dalam 5 menit**


