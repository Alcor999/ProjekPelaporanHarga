# Pelaporan Harga Padi - Flutter Petani

Aplikasi mobile Flutter untuk petani melaporkan harga padi secara real-time ke sistem backend Laravel.

## Fitur Utama

✅ **Authentication**
- Login dengan email dan password
- Registrasi akun petani baru
- Token-based authentication dengan Sanctum Laravel
- Logout aman

✅ **Laporan Harga**
- Buat laporan harga padi baru
- Edit laporan yang masih pending
- Hapus laporan yang masih pending
- Lihat riwayat semua laporan Anda
- Status tracking (pending, approved, rejected)

✅ **Data Referensi**
- Pilih wilayah (desa, kecamatan, kabupaten)
- Pilih komoditas dan grade
- Validasi data otomatis

✅ **User Experience**
- Design yang menarik dan modern
- Loading states yang informatif
- Error handling yang user-friendly
- Pull-to-refresh pada list laporan
- Responsive design

## Struktur Project

```
lib/
├── core/
│   ├── constants/
│   │   └── api_constants.dart       # Konfigurasi API endpoints
│   ├── exceptions/
│   │   └── api_exception.dart       # Custom exceptions
│   └── theme/
│       └── app_theme.dart           # Theme, colors, typography
├── models/
│   ├── user_model.dart              # Model user
│   ├── wilayah_model.dart           # Model wilayah
│   ├── komoditas_model.dart         # Model komoditas
│   └── laporan_harga_model.dart     # Model laporan harga
├── services/
│   └── api_service.dart             # API client
├── providers/
│   ├── auth_provider.dart           # Auth state management
│   └── laporan_provider.dart        # Laporan state management
├── screens/
│   ├── splash_screen.dart           # Splash screen
│   ├── login_screen.dart            # Login screen
│   ├── register_screen.dart         # Register screen
│   ├── dashboard_screen.dart        # Home/dashboard
│   ├── create_laporan_screen.dart   # Form buat/edit laporan
│   └── laporan_detail_screen.dart   # Detail laporan
├── widgets/
│   ├── buttons.dart                 # Reusable buttons
│   ├── custom_text_field.dart       # Custom text input
│   └── common_widgets.dart          # Common widgets
└── main.dart                         # Entry point
```

## Setup & Installation

### Prerequisites
- Flutter SDK (^3.10.4)
- Android SDK atau Xcode untuk iOS
- Backend Laravel sudah berjalan

### Installation Steps

1. **Clone/Setup Project**
```bash
cd /Users/mymac/Projects/pelaporan_harga_petani
flutter pub get
```

2. **Configure Backend URL**
Edit file `lib/core/constants/api_constants.dart`:
```dart
static const String baseUrl = 'http://your-backend-url.com/api';
```

Untuk development lokal:
- **Android**: gunakan `10.0.2.2` untuk localhost
- **iOS**: gunakan `localhost` atau IP machine
```dart
static const String baseUrl = 'http://10.0.2.2:8000/api'; // Android emulator
static const String baseUrl = 'http://localhost:8000/api';  // iOS simulator
```

3. **Run Application**
```bash
flutter run
```

## Dependencies

```yaml
dependencies:
  flutter:
    sdk: flutter
  cupertino_icons: ^1.0.8
  dio: ^5.4.0                    # HTTP client
  shared_preferences: ^2.2.2     # Local storage
  intl: ^0.19.0                  # Internationalization
  provider: ^6.1.0               # State management
  google_fonts: ^6.1.0           # Custom fonts
  cached_network_image: ^3.3.1   # Image caching
  flutter_svg: ^2.0.7            # SVG support
  shimmer: ^3.0.0                # Loading shimmer
  dropdown_button2: ^2.3.9       # Better dropdown
```

## API Integration

### Authentication Flow

1. **Login**
```
POST /api/login
Body: { email, password }
Response: { success, data: { user, token, token_type } }
```

2. **Register**
```
POST /api/register
Body: { name, email, password, password_confirmation, phone, address }
Response: { success, message }
```

3. **Logout**
```
POST /api/logout
Headers: Authorization: Bearer {token}
Response: { success, message }
```

4. **Get Profile**
```
GET /api/profile
Headers: Authorization: Bearer {token}
Response: { success, data: user }
```

### Laporan Harga API

1. **Get Wilayah Reference**
```
GET /api/ref/wilayah
Response: { success, data: [wilayah] }
```

2. **Get Komoditas Reference**
```
GET /api/ref/komoditas
Response: { success, data: [komoditas] }
```

3. **Create Laporan**
```
POST /api/petani/laporan
Headers: Authorization: Bearer {token}
Body: { wilayah_id, komoditas_id, harga, satuan, tanggal_panen, catatan }
Response: { success, message, data: laporan }
```

4. **Get Laporan List**
```
GET /api/petani/laporan?page=1
Headers: Authorization: Bearer {token}
Response: { success, data: [laporan] }
```

5. **Update Laporan** (hanya jika status=pending)
```
PUT /api/petani/laporan/{id}
Headers: Authorization: Bearer {token}
Body: { wilayah_id, komoditas_id, harga, satuan, tanggal_panen, catatan }
Response: { success, message, data: laporan }
```

6. **Delete Laporan** (hanya jika status=pending)
```
DELETE /api/petani/laporan/{id}
Headers: Authorization: Bearer {token}
Response: { success, message }
```

## State Management

### AuthProvider
Mengelola:
- User authentication state
- Login/register/logout
- User profile
- Token management

```dart
// Usage
final authProvider = context.read<AuthProvider>();
await authProvider.login(email, password);
authProvider.user; // Get current user
```

### LaporanProvider
Mengelola:
- List laporan harga
- Data referensi (wilayah, komoditas)
- Create/update/delete laporan
- Error handling

```dart
// Usage
final laporanProvider = context.read<LaporanProvider>();
await laporanProvider.loadLaporanList();
await laporanProvider.createLaporan(laporan);
```

## UI/UX Design

### Color Palette
- **Primary Green**: #2E7D32 (Pertanian)
- **Accent Orange**: #FFA726 (Aksentuasi)
- **Success**: #4CAF50
- **Error**: #F44336
- **Warning**: #FFC107

### Typography
- **Headline**: Bold, 24-32px
- **Title**: Semi-bold, 16-20px
- **Body**: Regular, 14-16px
- **Caption**: Regular, 12px

## Testing

### Manual Testing Checklist

```
□ Login
  ✓ Login dengan email/password valid
  ✓ Login dengan email invalid
  ✓ Login dengan password salah
  
□ Register
  ✓ Register dengan data lengkap
  ✓ Email sudah terdaftar
  ✓ Password tidak cocok
  ✓ Validasi field mandatory

□ Dashboard
  ✓ Load list laporan
  ✓ Pull to refresh
  ✓ Display status dengan warna benar

□ Create Laporan
  ✓ Form validation
  ✓ Dropdown wilayah & komoditas
  ✓ Date picker
  ✓ Submit laporan sukses
  ✓ Error handling jika server error

□ Detail Laporan
  ✓ Display semua informasi
  ✓ Edit button hanya jika pending
  ✓ Delete button hanya jika pending
  ✓ Konfirmasi delete

□ Logout
  ✓ Logout dan kembali ke login screen
  ✓ Token dihapus dari storage
```

## Troubleshooting

### Connection Error
- Pastikan backend Laravel sedang berjalan
- Check backend URL di `api_constants.dart`
- Pastikan firewall tidak memblokir koneksi

### Token Expired
- Token otomatis di-refresh saat re-login
- Jika diperlukan, implement token refresh logic

### Data tidak load
- Check network connectivity
- Pastikan user sudah login
- Check response format dari backend

## Fitur Future (Upcoming)

- [ ] Offline mode dengan SQLite
- [ ] Notifikasi push (FCM)
- [ ] Grafik tren harga
- [ ] QR code untuk quick reporting
- [ ] Dukungan multiple image upload
- [ ] Sync background

## Kontribusi

Untuk development lebih lanjut:

1. Follow struktur project yang sudah ada
2. Gunakan provider untuk state management
3. Handle error dengan proper exception handling
4. Buat unit test untuk business logic
5. Follow naming convention yang sudah ditetapkan

## License

Proprietary - Pelaporan Harga Padi System

## Support

Untuk bantuan teknis, hubungi developer backend (Laravel).

