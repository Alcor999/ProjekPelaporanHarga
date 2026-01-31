# ⚙️ Configuration & Customization Guide

## 🔧 Configuration Files

### 1. API Constants
**File**: `lib/core/constants/api_constants.dart`

```dart
class ApiConstants {
  // Update base URL sesuai dengan backend Anda
  static const String baseUrl = 'http://192.168.123.222/api';
  
  // Atau untuk development lokal
  // static const String baseUrl = 'http://localhost:8000/api';
  
  // Atau untuk IP LAN
  // static const String baseUrl = 'http://192.168.1.100:8000/api';
  
  // Timeouts (dalam seconds)
  static const Duration connectionTimeout = Duration(seconds: 30);
  static const Duration receiveTimeout = Duration(seconds: 30);
}
```

**Konfigurasi untuk berbagai environment:**

#### Development (Local)
```dart
static const String baseUrl = 'http://localhost:8000/api';
```

#### Development (LAN)
```dart
static const String baseUrl = 'http://192.168.1.100:8000/api';
```
*Ganti IP dengan IP device yang running Laravel*

#### Production
```dart
static const String baseUrl = 'https://api.yourdomain.com/api';
```

#### Testing dengan Emulator Android
```dart
// Android emulator: 10.0.2.2 = localhost dari emulator
static const String baseUrl = 'http://10.0.2.2:8000/api';
```

---

## 🎨 Theme Customization

### Color Scheme
**File**: `lib/core/theme/app_theme.dart`

```dart
class AppColors {
  // Primary colors (Change untuk theme berbeda)
  static const Color primary = Color(0xFF2E7D32);      // Green
  static const Color primaryLight = Color(0xFF66BB6A);
  static const Color primaryDark = Color(0xFF1B5E20);
  
  // Untuk theme lain, ganti primary color:
  // Blue: Color(0xFF1976D2)
  // Red: Color(0xFFD32F2F)
  // Purple: Color(0xFF7B1FA2)
  // Orange: Color(0xFFF57C00)
}
```

### Typography System
```dart
class AppTextStyles {
  // Customize font sizes dan styles di sini
  static const headlineLarge = TextStyle(
    fontSize: 32,
    fontWeight: FontWeight.bold,
    color: Color(0xFF1B5E20),
  );
  
  // Tambah atau modify text styles sesuai kebutuhan
}
```

### Spacing System
```dart
class AppSpacing {
  static const double xs = 4;    // Extra small
  static const double sm = 8;    // Small
  static const double md = 16;   // Medium
  static const double lg = 24;   // Large
  static const double xl = 32;   // Extra large
}
```

---

## 🔐 Security Configuration

### 1. Token Management
**File**: `lib/services/api_service.dart`

Current implementation:
- Token disimpan di SharedPreferences dengan key `api_token`
- Token ditambahkan ke setiap request sebagai Bearer token
- Token otomatis di-load saat app start

Untuk enkripsi token (recommended untuk production):
```dart
// Tambahkan flutter_secure_storage ke pubspec.yaml
// Ganti SharedPreferences dengan SecureStorage
```

### 2. HTTPS Configuration
Pastikan backend production menggunakan HTTPS:
```dart
// Production
static const String baseUrl = 'https://api.yourdomain.com/api';
```

### 3. Certificate Pinning (Advanced)
Untuk extra security dengan self-signed certificate:
```dart
// Di api_service.dart, customize Dio config
void _initializeDio() {
  _dio = Dio(
    BaseOptions(
      baseUrl: ApiConstants.baseUrl,
      // ... other config
    ),
  );
  
  // Add certificate pinning jika perlu
  // (Lihat Dio documentation untuk details)
}
```

---

## 🛠️ Development Configuration

### 1. Debug/Verbose Logging
```bash
# Run dengan verbose output
flutter run -v

# Melihat HTTP requests di console
# Di api_service.dart, uncomment interceptor logging
```

### 2. Mock API untuk Testing
Untuk testing tanpa backend:

```dart
// lib/services/api_service.dart
// Uncomment mock responses untuk development

Future<Map<String, dynamic>> login(String email, String password) async {
  // Mock response untuk testing
  return {
    'success': true,
    'data': {
      'token': 'mock_token_12345',
      'user': {
        'id': 1,
        'name': 'Test User',
        'email': email,
      }
    }
  };
}
```

### 3. Flutter DevTools
```bash
# Buka Flutter DevTools
flutter pub global activate devtools
flutter pub global run devtools

# Akses di http://localhost:9100
```

---

## 📦 Dependency Configuration

### pubspec.yaml
```yaml
dependencies:
  flutter:
    sdk: flutter
  
  # HTTP Client
  dio: ^5.4.0
  
  # State Management
  provider: ^6.1.0
  
  # Local Storage
  shared_preferences: ^2.2.2
  
  # Date/Time
  intl: ^0.19.0
  
  # UI Components
  cupertino_icons: ^1.0.8
  google_fonts: ^6.1.0
  
  # Image Handling
  cached_network_image: ^3.3.1
  flutter_svg: ^2.0.7
  
  # Loading Effects
  shimmer: ^3.0.0
  
  # Dropdowns
  dropdown_button2: ^2.3.9
```

Untuk add/update dependency:
```bash
# Add dependency
flutter pub add package_name

# Upgrade specific package
flutter pub upgrade dio

# Upgrade all packages
flutter pub upgrade

# Get specific version
flutter pub add dio:^5.4.0
```

---

## 🌐 Network Configuration

### Timeout Settings
**File**: `lib/core/constants/api_constants.dart`

```dart
// Current: 30 detik
static const Duration connectionTimeout = Duration(seconds: 30);
static const Duration receiveTimeout = Duration(seconds: 30);

// Untuk slow network
// static const Duration connectionTimeout = Duration(seconds: 60);
// static const Duration receiveTimeout = Duration(seconds: 60);

// Untuk fast network
// static const Duration connectionTimeout = Duration(seconds: 10);
// static const Duration receiveTimeout = Duration(seconds: 10);
```

### Retry Configuration (Advanced)
Untuk auto-retry failed requests:
```dart
// Di api_service.dart, add retry interceptor
_dio.interceptors.add(
  RetryInterceptor(
    dio: _dio,
    logPrint: print,
    evaluateResponse: (response) {
      return response.statusCode != 200;
    },
    maxRetries: 2,
    retryDelays: [
      Duration(seconds: 1),
      Duration(seconds: 2),
    ],
  ),
);
```

---

## 📊 Database (Backend)

### Ensure Backend Has These Tables

```sql
-- Users table
CREATE TABLE users (
  id INT PRIMARY KEY,
  name VARCHAR(255),
  email VARCHAR(255) UNIQUE,
  password VARCHAR(255),
  phone VARCHAR(20),
  address TEXT,
  role VARCHAR(50),
  status VARCHAR(50),
  created_at TIMESTAMP,
  updated_at TIMESTAMP
);

-- Laporan table
CREATE TABLE laporan (
  id INT PRIMARY KEY,
  user_id INT FOREIGN KEY,
  wilayah_id INT FOREIGN KEY,
  komoditas_id INT FOREIGN KEY,
  harga DECIMAL(15, 2),
  satuan VARCHAR(50),
  tanggal_panen DATE,
  tanggal_laporan DATE,
  catatan TEXT,
  status VARCHAR(50),
  created_at TIMESTAMP,
  updated_at TIMESTAMP
);

-- Reference tables
CREATE TABLE wilayah (
  id INT PRIMARY KEY,
  desa VARCHAR(255),
  kecamatan VARCHAR(255),
  kabupaten VARCHAR(255),
  provinsi VARCHAR(255),
  is_active BOOLEAN
);

CREATE TABLE komoditas (
  id INT PRIMARY KEY,
  name VARCHAR(255),
  grade VARCHAR(50),
  description TEXT,
  is_active BOOLEAN
);
```

---

## 🚀 Build Configuration

### Android Configuration
**File**: `android/app/build.gradle`

```gradle
android {
    compileSdkVersion 34  // Update if needed
    
    defaultConfig {
        applicationId "com.example.pelaporan_harga"
        minSdkVersion 21  // Minimum Android 5.0
        targetSdkVersion 34
        versionCode 1
        versionName "1.0.0"
    }
}
```

### iOS Configuration
**File**: `ios/Podfile`

```ruby
platform :ios, '12.0'  # Minimum iOS 12.0
```

### Build & Release
```bash
# Build APK (Android)
flutter build apk --release

# Build AAB (Android - for Play Store)
flutter build appbundle --release

# Build iOS
flutter build ios --release

# Build Web
flutter build web --release
```

---

## 🔔 Notification Configuration (Optional)

Untuk menambah push notifications:

```bash
flutter pub add firebase_messaging
```

```dart
// Di main.dart
import 'package:firebase_messaging/firebase_messaging.dart';

void main() async {
  WidgetsFlutterBinding.ensureInitialized();
  
  // Initialize Firebase
  // await Firebase.initializeApp();
  
  // Setup push notification
  // FirebaseMessaging.instance.getInitialMessage();
}
```

---

## 📝 Environment Variables

Untuk manage multiple environments:

```bash
# Development
flutter run --dart-define=BACKEND_URL=http://localhost:8000/api

# Production
flutter run --dart-define=BACKEND_URL=https://api.yourdomain.com/api
```

Akses di code:
```dart
const String backendUrl = String.fromEnvironment('BACKEND_URL', 
  defaultValue: 'http://localhost:8000/api');
```

---

## 🧪 Testing Configuration

### Unit Testing
```bash
flutter test
```

### Integration Testing
```bash
flutter test integration_test
```

### Add test dependencies
```yaml
dev_dependencies:
  flutter_test:
    sdk: flutter
  mockito: ^5.4.0
  bloc_test: ^9.1.0
```

---

## 📋 Checklist Before Production

- [ ] Change `baseUrl` ke production URL
- [ ] Hapus debug/verbose logging
- [ ] Enable HTTPS certificate
- [ ] Setup error reporting (Sentry, Crashlytics)
- [ ] Test di real device
- [ ] Test network errors & timeouts
- [ ] Setup analytics (Firebase Analytics)
- [ ] Configure app signing
- [ ] Review security settings
- [ ] Test dengan slow network (3G)
- [ ] Test offline mode
- [ ] Check app size (`flutter build apk --release`)

---

## 🎯 Performance Optimization

### 1. Image Optimization
```dart
// Gunakan CachedNetworkImage untuk images
CachedNetworkImage(
  imageUrl: 'https://...',
  placeholder: (context, url) => LoadingShimmer(),
  errorWidget: (context, url, error) => Icon(Icons.error),
  cacheManager: CacheManager(
    Config('image_cache', maxNrOfCacheObjects: 100),
  ),
)
```

### 2. List Performance
```dart
// Gunakan ListView.builder bukan ListView
ListView.builder(
  itemCount: items.length,
  itemBuilder: (context, index) {
    return ItemWidget(items[index]);
  },
)
```

### 3. API Pagination
```dart
// Implement pagination untuk list
Future<void> loadLaporanList({int page = 1}) async {
  // Load page by page, not all at once
}
```

---

## 📞 Troubleshooting Configuration

### Issue: "Connection Refused"
**File to check**: `api_constants.dart`
```dart
// Make sure URL is correct and reachable
static const String baseUrl = 'http://192.168.123.222:8000/api';
```

### Issue: "CORS Error"
**Backend side fix** (Laravel):
```php
// config/cors.php
'allowed_origins' => ['*'],
'allowed_methods' => ['*'],
```

### Issue: "Certificate Error"
```dart
// Uncomment untuk bypass (DEVELOPMENT ONLY)
// _dio.httpClientAdapter = HttpClientAdapter()
//   ..onHttpClientCreate = (HttpClient client) {
//     client.badCertificateCallback = (cert, host, port) => true;
//     return client;
//   };
```

---

**Last Updated: 8 Januari 2026**


