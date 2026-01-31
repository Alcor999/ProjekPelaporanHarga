# Quick Reference - Pelaporan Harga Padi Flutter App

## 🎯 Project Overview
Aplikasi Flutter untuk **Petani** melaporkan harga padi ke backend Laravel.  
**Status**: ✅ Completed & Production Ready

---

## ⚡ Quick Start (5 Minutes)

### Step 1: Setup Backend
```bash
cd /Users/mymac/Projects/pelaporanharga
php artisan serve  # Runs on http://localhost:8000
```

### Step 2: Configure Flutter
Edit `lib/core/constants/api_constants.dart`:
```dart
static const String baseUrl = 'http://10.0.2.2:8000/api';  // Android
// or
static const String baseUrl = 'http://localhost:8000/api'; // iOS
```

### Step 3: Install & Run
```bash
cd /Users/mymac/Projects/pelaporan_harga_petani
flutter pub get
flutter run
```

### Step 4: Test
Login dengan akun yang sudah ada atau register baru

---

## 📱 Screens & Navigation

```
Splash Screen (2 seconds)
    ↓
Login Screen ←→ Register Screen
    ↓
Dashboard (List Laporan)
    ├─→ Create Laporan (FAB button)
    │   └─→ Save → Update Dashboard
    └─→ Tap Laporan → Detail Screen
        ├─→ Edit (if pending)
        └─→ Delete (if pending)
```

---

## 🔑 Key Classes

### Models
```dart
User            // User data + role/status
Wilayah         // Location (desa, kecamatan)
Komoditas       // Commodity type + grade
LaporanHarga    // Price report with relations
```

### Providers (State Management)
```dart
AuthProvider        // Authentication & user state
LaporanProvider     // Laporan CRUD + reference data
```

### Services
```dart
ApiService          // REST API client (Dio)
```

### Screens
```dart
LoginScreen
RegisterScreen
DashboardScreen
CreateLaporanScreen
LaporanDetailScreen
SplashScreen
```

---

## 🔌 API Endpoints

### Authentication
```
POST   /api/login              Login
POST   /api/register           Register
POST   /api/logout             Logout
GET    /api/profile            Get user profile
```

### Laporan
```
GET    /api/petani/laporan              List (paginated)
POST   /api/petani/laporan              Create
GET    /api/petani/laporan/{id}         Detail
PUT    /api/petani/laporan/{id}         Update
DELETE /api/petani/laporan/{id}         Delete
```

### Reference Data
```
GET    /api/ref/wilayah                 Get locations
GET    /api/ref/komoditas               Get commodities
```

---

## 🎨 Colors Used

```dart
AppColors.primary       // #2E7D32 (Green)
AppColors.primaryDark   // #1B5E20 (Dark Green)
AppColors.accent        // #FFA726 (Orange)
AppColors.success       // #4CAF50 (Green)
AppColors.error         // #F44336 (Red)
AppColors.warning       // #FFC107 (Amber)
AppColors.grey*         // Various grey shades
```

---

## 📦 Dependencies

```yaml
dio: ^5.4.0                    # HTTP Client
provider: ^6.1.0               # State Management
shared_preferences: ^2.2.2     # Local Storage
intl: ^0.19.0                  # Date/i18n formatting
google_fonts: ^6.1.0           # Custom fonts
cached_network_image: ^3.3.1   # Image caching
flutter_svg: ^2.0.7            # SVG support
shimmer: ^3.0.0                # Loading effect
dropdown_button2: ^2.3.9       # Enhanced dropdown
```

---

## 🔐 Authentication Flow

```
User Input Email/Password
    ↓
POST /api/login
    ↓
Get Token + User Data
    ↓
Save Token → SharedPreferences
    ↓
Set AuthProvider.isAuthenticated = true
    ↓
Navigate to Dashboard
```

Token otomatis di-include di setiap request dengan header:
```
Authorization: Bearer {token}
```

---

## 💾 Data Flow Example: Create Laporan

```
User fills form
    ↓
Validate form (client-side)
    ↓
context.read<LaporanProvider>().createLaporan(laporan)
    ↓
ApiService.createLaporan()
    ↓
POST /api/petani/laporan (with token)
    ↓
Laravel creates & returns laporan
    ↓
Provider updates laporanList
    ↓
UI rebuilds (Consumer widget)
    ↓
Show success message & navigate back
```

---

## 🚨 Error Handling

### Exception Types
```dart
ApiException         // Generic API error
NetworkException     // No internet
UnauthorizedException   // 401 Unauthorized
ValidationException  // 422 Validation failed
ServerException      // 500+ Server error
```

### Common Errors & Solutions

| Error | Cause | Solution |
|-------|-------|----------|
| Connection refused | Backend not running | `php artisan serve` |
| CORS error | Wrong backend config | Check Laravel CORS |
| 401 Unauthorized | Token invalid | Clear app data & re-login |
| Validation error | Invalid input | Check form validation |

---

## 🧪 Testing Checklist

### Quick Manual Test
```
[ ] Login with email/password
[ ] Register new account
[ ] Create laporan
[ ] View laporan list
[ ] View laporan detail
[ ] Edit laporan
[ ] Delete laporan
[ ] Pull-to-refresh
[ ] View user profile
[ ] Logout
```

---

## 📂 File Organization

```
lib/
├── core/          # Constants, theme, exceptions
├── models/        # Data models (User, Laporan, etc)
├── services/      # API service (Dio)
├── providers/     # State management (Provider)
├── screens/       # App screens
├── widgets/       # Reusable UI components
└── main.dart      # Entry point
```

---

## 🔧 Common Tasks

### Change Backend URL
Edit `lib/core/constants/api_constants.dart`:
```dart
static const String baseUrl = 'http://new-url.com/api';
```

### Add New Screen
1. Create file in `lib/screens/`
2. Create StatefulWidget or StatelessWidget
3. Add route in `main.dart` if needed
4. Navigate using: `Navigator.of(context).push(...)`

### Add New API Endpoint
1. Add method in `lib/services/api_service.dart`
2. Use it in appropriate Provider
3. Update screen to call Provider method

### Change Theme Colors
Edit `lib/core/theme/app_theme.dart` - `AppColors` class

---

## 📋 Form Validation Rules

| Field | Rules |
|-------|-------|
| Email | Required, valid email format |
| Password | Required, min 6 characters |
| Name | Required, max 255 chars |
| Harga | Required, numeric, positive |
| Satuan | Required, not empty |
| Tanggal Panen | Required, before or equal today |
| Wilayah | Required, must be selected |
| Komoditas | Required, must be selected |

---

## 🔐 Security Notes

✅ Token saved securely (SharedPreferences)
✅ Password never logged
✅ Token auto-cleared on 401
✅ HTTPS ready (change baseUrl to https://)
✅ Input validation on client
✅ Server-side validation in Laravel

---

## 📊 State Management Pattern

```dart
// Using Provider
final provider = context.read<AuthProvider>();

// Consuming in UI
Consumer<AuthProvider>(
  builder: (context, authProvider, _) {
    return Text(authProvider.user?.name ?? '');
  },
)

// Listening for changes
watch<AuthProvider>().isLoading
```

---

## 🎯 API Response Format

All responses follow this format:

```json
{
  "success": true|false,
  "message": "Optional message",
  "data": { ... }  // or [...] for lists
}
```

---

## 💡 Tips & Tricks

1. **Use Consumer for optimal rebuilds**
   ```dart
   Consumer<LaporanProvider>(
     builder: (context, provider, _) { ... }
   )
   ```

2. **Validate forms before submit**
   ```dart
   if (_formKey.currentState?.validate() ?? false) {
     // Submit
   }
   ```

3. **Show loading state**
   ```dart
   Consumer<LaporanProvider>(
     builder: (context, provider, _) {
       if (provider.isLoading) return CircularProgressIndicator();
       // ...
     }
   )
   ```

4. **Handle errors gracefully**
   ```dart
   if (provider.errorMessage != null) {
     showErrorSnackBar(context, provider.errorMessage!);
   }
   ```

---

## 📱 Device Testing

### Android Emulator
```bash
flutter run -d android  # Emulator
flutter run -d <device-id>  # Physical device
```
Use `10.0.2.2` for localhost

### iOS Simulator
```bash
flutter run -d ios  # Simulator
```
Use `localhost` for localhost

---

## 🚀 Build & Deploy

### Debug Build
```bash
flutter run
flutter build apk --debug
flutter build ios --debug
```

### Release Build
```bash
flutter build apk --release
flutter build ios --release
```

---

## 📞 Need Help?

1. **Check SETUP_GUIDE.md** - Detailed setup instructions
2. **Check INTEGRATION_GUIDE.md** - API integration details
3. **Check README.md** - Full documentation
4. **Check Flutter console** - Error messages & logs

---

## ✨ Last Minute Checklist

Before going to production:
- [ ] Backend running & tested
- [ ] API_CONSTANTS.baseUrl correct
- [ ] All screens tested
- [ ] Network errors handled
- [ ] Loading states implemented
- [ ] Form validation works
- [ ] Error messages user-friendly
- [ ] No console warnings/errors
- [ ] App signing configured (Android)
- [ ] App provisioning configured (iOS)

---

**Version**: 1.0.0  
**Last Updated**: January 8, 2026  
**Status**: ✅ Production Ready

