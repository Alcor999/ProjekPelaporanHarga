# Pelaporan Harga Padi - Aplikasi Flutter Petani 🌾

Aplikasi mobile **Flutter** yang dirancang khusus untuk petani melaporkan harga padi secara real-time dan terintegrasi dengan backend Laravel.

## 📱 Fitur Aplikasi

### 🔐 Authentikasi
- ✅ Login dengan email & password
- ✅ Registrasi akun petani baru
- ✅ Token-based authentication (Laravel Sanctum)
- ✅ Session management & logout aman
- ✅ Password validation

### 📊 Pelaporan Harga
- ✅ Create laporan harga padi baru
- ✅ Edit laporan (jika status masih pending)
- ✅ Delete laporan (jika status masih pending)
- ✅ View riwayat semua laporan Anda
- ✅ Real-time status tracking (pending → approved/rejected)
- ✅ Pull-to-refresh functionality

### 🗂️ Data Management
- ✅ Pilih wilayah (desa, kecamatan, kabupaten)
- ✅ Pilih komoditas padi & grade kualitas
- ✅ Date picker untuk tanggal panen
- ✅ Format currency otomatis untuk harga
- ✅ Catatan/notes untuk setiap laporan

### 💻 User Experience
- ✅ Design modern & intuitif
- ✅ Dark/Light color scheme yang eye-friendly
- ✅ Loading states informatif
- ✅ Error handling user-friendly
- ✅ Responsive design untuk berbagai ukuran screen
- ✅ Form validation lengkap

## 📂 Struktur Project

```
pelaporan_harga_petani/
├── lib/
│   ├── core/
│   │   ├── constants/
│   │   │   ├── api_constants.dart       # API endpoints config
│   │   │   └── index.dart
│   │   ├── exceptions/
│   │   │   └── api_exception.dart       # Custom exceptions
│   │   └── theme/
│   │       └── app_theme.dart           # Colors, typography, spacing
│   │
│   ├── models/
│   │   ├── user_model.dart              # User data model
│   │   ├── wilayah_model.dart           # Wilayah/location model
│   │   ├── komoditas_model.dart         # Commodity model
│   │   └── laporan_harga_model.dart     # Price report model
│   │
│   ├── services/
│   │   └── api_service.dart             # API client & HTTP communication
│   │
│   ├── providers/
│   │   ├── auth_provider.dart           # Auth state management
│   │   └── laporan_provider.dart        # Laporan state management
│   │
│   ├── screens/
│   │   ├── splash_screen.dart           # Splash/loading screen
│   │   ├── login_screen.dart            # Login page
│   │   ├── register_screen.dart         # Register page
│   │   ├── dashboard_screen.dart        # Home/list laporan
│   │   ├── create_laporan_screen.dart   # Form buat/edit laporan
│   │   └── laporan_detail_screen.dart   # Detail view laporan
│   │
│   ├── widgets/
│   │   ├── buttons.dart                 # Reusable buttons
│   │   ├── custom_text_field.dart       # Custom text input
│   │   └── common_widgets.dart          # Shared widgets
│   │
│   └── main.dart                         # Entry point aplikasi
│
├── android/                              # Android-specific config
├── ios/                                  # iOS-specific config
├── test/                                 # Unit tests
├── pubspec.yaml                          # Dependencies
├── SETUP_GUIDE.md                        # Setup instructions
└── README.md                             # This file
```

## 🚀 Quick Start

### Prerequisites
- **Flutter** SDK (≥3.10.4) - [Install Flutter](https://flutter.dev/docs/get-started/install)
- **Dart** (included with Flutter)
- **Backend Laravel** sudah running
- **Android Studio** atau **Xcode** untuk emulator

### Installation

1. **Clone atau navigate ke project folder**
   ```bash
   cd /Users/mymac/Projects/pelaporan_harga_petani
   ```

2. **Get dependencies**
   ```bash
   flutter pub get
   ```

3. **Configure API Backend URL**
   
   Edit file `lib/core/constants/api_constants.dart`:
   ```dart
   // Untuk development dengan backend lokal:
   // Android emulator
   static const String baseUrl = 'http://10.0.2.2:8000/api';
   
   // iOS simulator
   static const String baseUrl = 'http://localhost:8000/api';
   
   // Atau jika pakai IP address
   static const String baseUrl = 'http://192.168.x.x:8000/api';
   ```

4. **Run aplikasi**
   ```bash
   # Run di Android emulator
   flutter run
   
   # Run di iOS simulator
   flutter run -d ios
   
   # Run di device fisik
   flutter run
   ```

## 📦 Dependencies

| Package | Version | Purpose |
|---------|---------|---------|
| `dio` | ^5.4.0 | HTTP client untuk API calls |
| `provider` | ^6.1.0 | State management |
| `shared_preferences` | ^2.2.2 | Local storage untuk token |
| `intl` | ^0.19.0 | Internationalization & date formatting |
| `google_fonts` | ^6.1.0 | Custom fonts |
| `cached_network_image` | ^3.3.1 | Image caching |
| `flutter_svg` | ^2.0.7 | SVG asset support |
| `shimmer` | ^3.0.0 | Loading animation |
| `dropdown_button2` | ^2.3.9 | Enhanced dropdown |

## 🔌 API Integration

### Base Configuration
```dart
baseUrl: http://localhost:8000/api
Timeout: 30 seconds (connection & receive)
Header: Content-Type: application/json
```

### Authentication Endpoints

#### Login
```http
POST /login
Content-Type: application/json

{
  "email": "petani@example.com",
  "password": "password123"
}

Response (200):
{
  "success": true,
  "message": "Login berhasil",
  "data": {
    "user": { id, name, email, role, status, ... },
    "token": "token_string",
    "token_type": "Bearer"
  }
}
```

#### Register
```http
POST /register
Content-Type: application/json

{
  "name": "Nama Petani",
  "email": "petani@example.com",
  "password": "password123",
  "password_confirmation": "password123",
  "phone": "08xxx",
  "address": "Alamat..."
}

Response (201):
{
  "success": true,
  "message": "Registrasi berhasil",
  "data": { ... }
}
```

### Reference Data Endpoints

#### Get Wilayah
```http
GET /ref/wilayah
Authorization: Bearer {token}

Response (200):
{
  "success": true,
  "data": [
    {
      "id": 1,
      "desa": "Desa Maju",
      "kecamatan": "Kecamatan A",
      "kabupaten": "Ngawi",
      "provinsi": "Jawa Timur",
      "is_active": true
    },
    ...
  ]
}
```

#### Get Komoditas
```http
GET /ref/komoditas
Authorization: Bearer {token}

Response (200):
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Padi",
      "grade": "Beras Putih",
      "description": "...",
      "is_active": true
    },
    ...
  ]
}
```

### Laporan Endpoints

#### Create Laporan
```http
POST /petani/laporan
Authorization: Bearer {token}
Content-Type: application/json

{
  "wilayah_id": 1,
  "komoditas_id": 1,
  "harga": 5000,
  "satuan": "kg",
  "tanggal_panen": "2026-01-08",
  "catatan": "Panen sempurna"
}

Response (201):
{
  "success": true,
  "message": "Laporan berhasil dibuat",
  "data": { id, status: "pending", ... }
}
```

#### Get Laporan List
```http
GET /petani/laporan?page=1
Authorization: Bearer {token}

Response (200):
{
  "success": true,
  "data": {
    "data": [ laporan items ],
    "current_page": 1,
    "last_page": 5,
    "total": 100
  }
}
```

#### Update Laporan
```http
PUT /petani/laporan/{id}
Authorization: Bearer {token}

{ wilayah_id, komoditas_id, harga, satuan, tanggal_panen, catatan }

Response (200):
{ success: true, data: updated_laporan }
```

#### Delete Laporan
```http
DELETE /petani/laporan/{id}
Authorization: Bearer {token}

Response (200):
{ success: true, message: "Laporan berhasil dihapus" }
```

## 🎨 Design System

### Colors
```dart
Primary Green      : #2E7D32  (Pertanian theme)
Primary Light      : #66BB6A
Primary Dark       : #1B5E20

Accent Orange      : #FFA726
Accent Light       : #FFB74D

Success Green      : #4CAF50
Error Red          : #F44336
Warning Amber      : #FFC107
Info Blue          : #2196F3

Neutral Grey       : #999999
Light Grey         : #F5F5F5 - #E0E0E0
```

### Typography
```dart
Headline Large     : 32px, Bold
Headline Medium    : 28px, Bold
Title Large        : 20px, Semi-bold
Body Medium        : 14px, Regular
Body Small         : 12px, Regular
```

### Spacing
```dart
XS: 4px
SM: 8px
MD: 16px
LG: 24px
XL: 32px
XXL: 48px
```

## 🔄 State Management Flow

### AuthProvider
```
User Login/Register
    ↓
[ApiService.login]
    ↓
[Save Token + User]
    ↓
[AuthProvider.isAuthenticated = true]
    ↓
Navigate to Dashboard
```

### LaporanProvider
```
Load Reference Data (Wilayah, Komoditas)
    ↓
Load User's Laporan List
    ↓
Display in UI
    ↓
User Action (Create/Edit/Delete)
    ↓
API Call
    ↓
Update State
    ↓
Reflect in UI
```

## 🧪 Testing Guide

### Test Cases

#### Authentication Flow
- [ ] Login dengan kredensial valid
- [ ] Login dengan email tidak terdaftar
- [ ] Login dengan password salah
- [ ] Register dengan data valid
- [ ] Register dengan email sudah terdaftar
- [ ] Logout & kembali ke login screen

#### Laporan Management
- [ ] Lihat list laporan
- [ ] Pull-to-refresh berhasil
- [ ] Create laporan baru
- [ ] Edit laporan (pending status)
- [ ] Delete laporan
- [ ] View detail laporan
- [ ] Validasi form (harga, satuan, tanggal)

#### Error Handling
- [ ] Network error → Show error message
- [ ] API error (422) → Show validation errors
- [ ] Server error (500) → Show error message
- [ ] Unauthorized (401) → Logout & navigate to login

## ⚙️ Configuration

### Environment Setup

**Development:**
```dart
// lib/core/constants/api_constants.dart
static const String baseUrl = 'http://10.0.2.2:8000/api';  // Android
static const String baseUrl = 'http://localhost:8000/api'; // iOS
```

**Production:**
```dart
static const String baseUrl = 'https://api.yourdomain.com/api';
```

### Build Commands

```bash
# Development build
flutter build apk --debug
flutter build ios --debug

# Release build
flutter build apk --release
flutter build ios --release

# Run with specific build flavor (jika ada)
flutter run --flavor production -t lib/main_production.dart
```

## 🐛 Troubleshooting

### App tidak bisa connect ke backend
**Solution:**
- Pastikan backend Laravel running: `php artisan serve`
- Check URL di `api_constants.dart`
- Test koneksi: Buka browser → http://10.0.2.2:8000/api/login (Android)
- Check firewall/antivirus tidak blokir koneksi

### "Flutter SDK not found"
**Solution:**
```bash
flutter --version
export PATH="$PATH:`pwd`/flutter/bin"
```

### Pubspec locked version conflict
**Solution:**
```bash
flutter pub get
flutter pub upgrade
```

### Emulator timeout
**Solution:**
- Restart emulator
- Increase timeout di `api_constants.dart`:
```dart
static const Duration connectionTimeout = Duration(seconds: 60);
```

## 📝 Code Standards

### Naming Convention
```dart
// Classes & Models: PascalCase
class LaporanHarga { }
class UserModel { }

// Functions & Variables: camelCase
void handleLogin() { }
String userName = "Budi";

// Constants: UPPER_SNAKE_CASE
const String API_BASE_URL = 'http://...';

// Private members: _leadingUnderscore
int _localCounter = 0;
void _privateMethod() { }

// Files: snake_case
login_screen.dart
api_service.dart
```

### Best Practices
1. **State Management**: Selalu gunakan Provider
2. **Error Handling**: Catch specific exceptions
3. **UI Updates**: Gunakan Consumer widget
4. **Asset Organization**: Organize assets by feature
5. **Code Comments**: Comment untuk business logic yang kompleks

## 🚀 Deployment

### Android Release
```bash
# Generate keystore
keytool -genkey -v -keystore ~/key.jks -keyalg RSA -keysize 2048 -validity 10000 -alias upload

# Build APK
flutter build apk --release

# Build AAB for Play Store
flutter build appbundle --release
```

### iOS Release
```bash
flutter build ios --release

# Then use Xcode atau Apple Transporter untuk upload ke App Store
```

## 📞 Support & Contact

Untuk bantuan teknis:
- Hubungi backend developer (Laravel)
- Check SETUP_GUIDE.md untuk detailed instructions
- Review API documentation di backend project

## 📄 License

Proprietary - Pelaporan Harga Padi System
All rights reserved.

---

**Last Updated**: January 8, 2026  
**Flutter Version**: ^3.10.4  
**Dart Version**: ^3.x

