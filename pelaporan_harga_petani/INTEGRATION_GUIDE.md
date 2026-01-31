# Integrasi Flutter ↔ Laravel - Dokumentasi Teknis

## Overview

Aplikasi Flutter petani terintegrasi penuh dengan backend Laravel melalui REST API dengan authentication token (Sanctum).

## Arsitektur Integrasi

```
┌─────────────────────┐
│   Flutter App       │
│   (Petani Mobile)   │
└──────────┬──────────┘
           │
           │ HTTP (Dio)
           │ JSON Request/Response
           │ Bearer Token Auth
           │
           ▼
┌─────────────────────────────────────┐
│      Laravel Backend API            │
│  ┌─────────────────────────────────┐│
│  │ API Routes (/api/*)             ││
│  ├─────────────────────────────────┤│
│  │ • Authentication (Sanctum)      ││
│  │ • Petani Laporan CRUD           ││
│  │ • Reference Data (Wilayah, dll) ││
│  └─────────────────────────────────┘│
│  ┌─────────────────────────────────┐│
│  │ Middleware                      ││
│  ├─────────────────────────────────┤│
│  │ • auth:sanctum (verify token)   ││
│  │ • role:petani (verify role)     ││
│  └─────────────────────────────────┘│
└──────────┬──────────────────────────┘
           │
           ▼
┌─────────────────────────────────────┐
│      MySQL Database                 │
│  ┌─────────────────────────────────┐│
│  │ Tables:                         ││
│  │ • users (petani login)          ││
│  │ • laporan_harga                 ││
│  │ • wilayah (kecamatan/desa)      ││
│  │ • komoditas (padi/grade)        ││
│  │ • personal_access_tokens        ││
│  └─────────────────────────────────┘│
└─────────────────────────────────────┘
```

## Authentication Flow

### 1. Login Process

```
User Input Email/Password
         │
         ▼
┌─────────────────────────────────────┐
│ POST /api/login                     │
│ Body: { email, password }           │
└─────────────────────────────────────┘
         │
         ▼
┌─────────────────────────────────────┐
│ Laravel AuthController              │
│ • Find user by email                │
│ • Hash verify password              │
│ • Check user status (active)        │
│ • Create Sanctum token              │
└─────────────────────────────────────┘
         │
         ▼
┌─────────────────────────────────────┐
│ Response 200 (Success)              │
│ {                                   │
│   "success": true,                  │
│   "data": {                         │
│     "user": { ...user data... },    │
│     "token": "token_string",        │
│     "token_type": "Bearer"          │
│   }                                 │
│ }                                   │
└─────────────────────────────────────┘
         │
         ▼
┌─────────────────────────────────────┐
│ Flutter App                         │
│ • Save token ke SharedPreferences   │
│ • Set AuthProvider.isAuthenticated  │
│ • Add Authorization header untuk   │
│   request selanjutnya                │
│ • Navigate ke Dashboard             │
└─────────────────────────────────────┘
```

### 2. Authenticated Request Example

```http
GET /api/petani/laporan?page=1
Authorization: Bearer <token_from_login>
Content-Type: application/json

Response:
{
  "success": true,
  "data": {
    "data": [
      {
        "id": 1,
        "user_id": 5,
        "wilayah_id": 2,
        "komoditas_id": 1,
        "harga": 5500,
        "satuan": "kg",
        "tanggal_panen": "2026-01-07",
        "status": "pending",
        "wilayah": { "desa": "Desa X", "kecamatan": "...", ... },
        "komoditas": { "name": "Padi", "grade": "Grade A", ... }
      },
      ...
    ],
    "current_page": 1,
    "last_page": 5
  }
}
```

## Data Flow Examples

### Create Laporan Flow

```
┌─────────────────────────────┐
│ User di Form                │
│ • Pilih Wilayah             │
│ • Pilih Komoditas           │
│ • Input Harga: 5500         │
│ • Pilih Tanggal Panen       │
│ • Input Catatan             │
│ • Tap "Buat Laporan"        │
└────────────┬────────────────┘
             │
             ▼
┌──────────────────────────────────────┐
│ CreateLaporanScreen                  │
│ • Validate form                      │
│ • Create LaporanHarga object         │
│ • Call laporanProvider.createLaporan │
└────────────┬─────────────────────────┘
             │
             ▼
┌──────────────────────────────────────┐
│ LaporanProvider                      │
│ • Call apiService.createLaporan()    │
│ • Handle loading state               │
│ • Update laporanList on success      │
└────────────┬─────────────────────────┘
             │
             ▼
┌──────────────────────────────────────┐
│ ApiService (Dio)                     │
│ POST /api/petani/laporan             │
│ Headers:                             │
│   Authorization: Bearer token        │
│   Content-Type: application/json     │
│ Body: {                              │
│   wilayah_id: 2,                     │
│   komoditas_id: 1,                   │
│   harga: 5500,                       │
│   satuan: "kg",                      │
│   tanggal_panen: "2026-01-07",       │
│   catatan: "..."                     │
│ }                                    │
└────────────┬─────────────────────────┘
             │
             ▼
┌──────────────────────────────────────┐
│ Laravel API Endpoint                 │
│ (LaporanController@store)            │
│ • Validate input                     │
│ • Create laporan record              │
│ • Set status = 'pending'             │
│ • Load relations                     │
│ • Return created laporan             │
└────────────┬─────────────────────────┘
             │
             ▼
┌──────────────────────────────────────┐
│ Response 201 (Created)               │
│ {                                    │
│   "success": true,                   │
│   "message": "Laporan berhasil...",  │
│   "data": {                          │
│     "id": 42,                        │
│     "user_id": 5,                    │
│     "wilayah_id": 2,                 │
│     "komoditas_id": 1,               │
│     "harga": 5500,                   │
│     "satuan": "kg",                  │
│     "tanggal_panen": "2026-01-07",   │
│     "status": "pending",             │
│     "created_at": "2026-01-08T..."   │
│   }                                  │
│ }                                    │
└────────────┬─────────────────────────┘
             │
             ▼
┌──────────────────────────────────────┐
│ Flutter App (Success Callback)       │
│ • Add laporan to list (insert at 0)  │
│ • Show success snackbar              │
│ • Navigate back to dashboard         │
│ • UI updates automatically via       │
│   Consumer widget                    │
└──────────────────────────────────────┘
```

## Error Handling

### Server Error Response Examples

#### 422 Validation Error
```http
HTTP/1.1 422 Unprocessable Entity
Content-Type: application/json

{
  "success": false,
  "message": "Validation error",
  "errors": {
    "harga": ["Harga harus berupa angka"],
    "wilayah_id": ["Wilayah tidak ditemukan"]
  }
}
```

**Flutter handling:**
```dart
catch (e) {
  if (e is ValidationException) {
    // Show validation errors per field
    e.errors.forEach((field, messages) {
      print('$field: ${messages.join(", ")}');
    });
  }
}
```

#### 401 Unauthorized
```http
HTTP/1.1 401 Unauthorized

{
  "success": false,
  "message": "Tidak terotorisasi"
}
```

**Flutter handling:**
```dart
catch (e) {
  if (e is UnauthorizedException) {
    // Clear token
    await apiService.clearToken();
    // Navigate to login
    navigator.pushReplacementNamed('/login');
  }
}
```

#### 500 Server Error
```http
HTTP/1.1 500 Internal Server Error

{
  "success": false,
  "message": "Server error"
}
```

## API Endpoints Summary

| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| POST | `/api/login` | ❌ | Login petani |
| POST | `/api/register` | ❌ | Register akun baru |
| POST | `/api/logout` | ✅ | Logout & revoke token |
| GET | `/api/profile` | ✅ | Get user profile |
| GET | `/api/ref/wilayah` | ✅ | Get list wilayah |
| GET | `/api/ref/komoditas` | ✅ | Get list komoditas |
| GET | `/api/petani/laporan` | ✅ | Get laporan list (paginated) |
| POST | `/api/petani/laporan` | ✅ | Create laporan |
| GET | `/api/petani/laporan/{id}` | ✅ | Get laporan detail |
| PUT | `/api/petani/laporan/{id}` | ✅ | Update laporan |
| DELETE | `/api/petani/laporan/{id}` | ✅ | Delete laporan |

## Database Relations

### Users Table
```sql
users
  ├── id (PK)
  ├── name
  ├── email (UNIQUE)
  ├── password
  ├── phone
  ├── address
  ├── role (enum: admin, petani)
  ├── status (enum: pending, active, blocked)
  └── timestamps
```

### Laporan Harga Table
```sql
laporan_harga
  ├── id (PK)
  ├── user_id (FK → users.id)
  ├── wilayah_id (FK → wilayah.id)
  ├── komoditas_id (FK → komoditas.id)
  ├── harga (decimal)
  ├── satuan (varchar)
  ├── tanggal_panen (date)
  ├── catatan (text)
  ├── status (enum: pending, approved, rejected)
  └── timestamps
```

## Token Management

### How Tokens Work

1. **Initial Login**: User menerima token
2. **Storage**: Token disimpan di SharedPreferences (encrypted)
3. **Usage**: Token ditambahkan ke setiap request header
4. **Expiration**: Sanctum tokens tidak expire by default (bisa dikonfigurasi)
5. **Revocation**: Token dihapus saat logout
6. **Re-login**: User perlu login ulang jika token hilang

### Token Header Format
```
Authorization: Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...
```

## Caching & Local Storage

### SharedPreferences Usage
```dart
// Saved during login
'api_token' → Bearer token string
'user' → JSON stringified user object

// Cleared on logout
prefs.remove('api_token');
prefs.remove('user');
```

## Performance Optimization

### Network Optimization
1. **Pagination**: Laporan list dipaginate (20 items/page)
2. **Lazy Loading**: Reference data (wilayah, komoditas) di-load on demand
3. **Caching**: Token disimpan lokal untuk instant access
4. **Timeout**: 30 detik connection timeout untuk slow network

### UI Optimization
1. **Provider Rebuild**: Hanya widget yang subscribed ke provider yang rebuild
2. **Consumer**: Granular updates menggunakan Consumer widget
3. **Image Loading**: Cached network image untuk performa optimal
4. **Skeleton Loading**: Shimmer effect untuk better UX

## Security Measures

### Frontend Security
1. ✅ Token stored securely (SharedPreferences encrypted)
2. ✅ HTTPS ready (change baseUrl to https:// in production)
3. ✅ Input validation on client side
4. ✅ Token auto-cleared on 401/403 response
5. ✅ Password never logged or stored

### Backend Security (Laravel)
1. ✅ CORS configured for mobile
2. ✅ Password hashed with bcrypt
3. ✅ Sanctum token verification
4. ✅ Role-based access control
5. ✅ Input validation & sanitization
6. ✅ Rate limiting on auth endpoints

## Testing Integration

### Manual Testing Checklist

```
Network & API
☐ Test dengan WiFi
☐ Test dengan mobile data (4G/5G)
☐ Test dengan slow network (throttle)
☐ Test dengan offline mode (should show error)

Authentication
☐ Login berhasil
☐ Token tersimpan
☐ Token di-include di setiap request
☐ Logout clear token
☐ 401 response → automatic logout

Data Sync
☐ Create laporan → appear di list
☐ Update laporan → reflect in list
☐ Delete laporan → remove from list
☐ Pull-to-refresh → get latest data

Error Handling
☐ Network timeout → show error message
☐ Validation error → show per-field errors
☐ Server error (500) → show error message
☐ 401 unauthorized → logout & redirect
```

## Troubleshooting Integration

### Common Issues & Solutions

**Issue**: "Connection refused"
```
✓ Backend tidak running
✓ Backend URL salah di api_constants.dart
✓ Firewall memblokir koneksi
→ Check backend running: curl http://localhost:8000/api/login
```

**Issue**: "Token invalid"
```
✓ Token expired (not applicable untuk Sanctum default)
✓ Token dihapus dari server
✓ Token corrupt di SharedPreferences
→ Clear app data & re-login
```

**Issue**: "CORS error"
```
✓ Backend tidak configure CORS dengan benar
✓ Request header tidak match
→ Check Laravel config/cors.php
```

## Monitoring & Logging

### Enable Debug Logging
```dart
// In main.dart during development
if (kDebugMode) {
  _dio.interceptors.add(LoggingInterceptor());
}
```

### Monitor Network Requests
```dart
// Dio interceptor logs all requests
// Check Flutter console untuk detailed logs
```

## Production Checklist

- [ ] Change API baseUrl ke production URL
- [ ] Enable HTTPS
- [ ] Configure proper CORS
- [ ] Setup error logging (Firebase Crashlytics, Sentry)
- [ ] Test dengan network throttling
- [ ] Setup analytics
- [ ] Configure app signing
- [ ] Test di real device
- [ ] Setup CI/CD pipeline
- [ ] Document API changes
- [ ] Monitor app analytics post-launch

---

**Version**: 1.0.0  
**Last Updated**: January 8, 2026  
**Backend**: Laravel 10+  
**Frontend**: Flutter 3.10+

