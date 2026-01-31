# 🔌 API Integration Reference

## Backend URL Configuration

File: `lib/core/constants/api_constants.dart`

```dart
static const String baseUrl = 'http://192.168.123.222/api';
```

**Update ini sesuai dengan URL backend Anda:**
- Local: `http://localhost:8000/api`
- LAN: `http://192.168.1.100:8000/api`
- Production: `https://yourdomain.com/api`

---

## 🔐 Authentication Endpoints

### 1. Login
```
POST /api/login
Content-Type: application/json

{
  "email": "user@example.com",
  "password": "password123"
}

Response (200):
{
  "success": true,
  "message": "Login successful",
  "data": {
    "token": "eyJhbGciOiJIUzI1NiIs...",
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "user@example.com",
      "phone": "08123456789",
      "address": "Jl. Main Street",
      "role": "petani",
      "status": "active",
      "created_at": "2024-01-01T00:00:00Z"
    }
  }
}

Implementation: AuthProvider.login()
```

### 2. Register
```
POST /api/register
Content-Type: application/json

{
  "name": "Budi Santoso",
  "email": "budi@example.com",
  "password": "password123",
  "password_confirmation": "password123",
  "phone": "08123456789",
  "address": "Jl. Main Street"
}

Response (201):
{
  "success": true,
  "message": "Registration successful",
  "data": {
    "id": 2,
    "name": "Budi Santoso",
    "email": "budi@example.com",
    "phone": "08123456789",
    "address": "Jl. Main Street",
    "role": "petani",
    "status": "pending"
  }
}

Implementation: AuthProvider.register()
```

### 3. Get Profile
```
GET /api/profile
Authorization: Bearer {token}

Response (200):
{
  "success": true,
  "data": {
    "id": 1,
    "name": "John Doe",
    "email": "user@example.com",
    "phone": "08123456789",
    "address": "Jl. Main Street",
    "role": "petani",
    "status": "active",
    "created_at": "2024-01-01T00:00:00Z"
  }
}

Implementation: ApiService.getProfile()
```

### 4. Logout
```
POST /api/logout
Authorization: Bearer {token}

Response (200):
{
  "success": true,
  "message": "Logout successful"
}

Implementation: ApiService.logout()
```

---

## 📊 Reference Data Endpoints

### 5. Get Wilayah (Region/Area)
```
GET /api/ref/wilayah
Authorization: Bearer {token}

Response (200):
{
  "success": true,
  "data": [
    {
      "id": 1,
      "desa": "Nusa Indah",
      "kecamatan": "Tambaksari",
      "kabupaten": "Surabaya",
      "provinsi": "Jawa Timur",
      "is_active": true
    },
    {
      "id": 2,
      "desa": "Ploso",
      "kecamatan": "Tambaksari",
      "kabupaten": "Surabaya",
      "provinsi": "Jawa Timur",
      "is_active": true
    }
  ]
}

Implementation: ApiService.getWilayah()
                 Used in: LaporanProvider.loadWilayah()
```

### 6. Get Komoditas (Commodity)
```
GET /api/ref/komoditas
Authorization: Bearer {token}

Response (200):
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Padi",
      "grade": "Grade A",
      "description": "Beras berkualitas tinggi",
      "is_active": true
    },
    {
      "id": 2,
      "name": "Padi",
      "grade": "Grade B",
      "description": "Beras kualitas standar",
      "is_active": true
    }
  ]
}

Implementation: ApiService.getKomoditas()
                Used in: LaporanProvider.loadKomoditas()
```

---

## 📋 Laporan Endpoints

### 7. Get Laporan List
```
GET /api/petani/laporan?page=1
Authorization: Bearer {token}

Query Parameters:
- page (optional): Page number for pagination

Response (200):
{
  "success": true,
  "data": [
    {
      "id": 1,
      "user_id": 1,
      "wilayah_id": 1,
      "komoditas_id": 1,
      "harga": 15000.00,
      "satuan": "kg",
      "tanggal_panen": "2024-01-15",
      "tanggal_laporan": "2024-01-16",
      "catatan": "Panen bagus",
      "status": "pending",
      "created_at": "2024-01-16T10:00:00Z",
      "wilayah": {
        "id": 1,
        "desa": "Nusa Indah",
        "kecamatan": "Tambaksari",
        "kabupaten": "Surabaya",
        "provinsi": "Jawa Timur"
      },
      "komoditas": {
        "id": 1,
        "name": "Padi",
        "grade": "Grade A"
      }
    }
  ]
}

Implementation: ApiService.getLaporanList()
                Used in: LaporanProvider.loadLaporanList()
```

### 8. Create Laporan
```
POST /api/petani/laporan
Authorization: Bearer {token}
Content-Type: application/json

{
  "user_id": 1,
  "wilayah_id": 1,
  "komoditas_id": 1,
  "harga": 15000.00,
  "satuan": "kg",
  "tanggal_panen": "2024-01-15",
  "catatan": "Panen bagus"
}

Response (201):
{
  "success": true,
  "message": "Laporan created successfully",
  "data": {
    "id": 1,
    "user_id": 1,
    "wilayah_id": 1,
    "komoditas_id": 1,
    "harga": 15000.00,
    "satuan": "kg",
    "tanggal_panen": "2024-01-15",
    "tanggal_laporan": "2024-01-16",
    "catatan": "Panen bagus",
    "status": "pending",
    "created_at": "2024-01-16T10:00:00Z"
  }
}

Implementation: ApiService.createLaporan()
                Used in: LaporanProvider.createLaporan()
```

### 9. Get Laporan Detail
```
GET /api/petani/laporan/{id}
Authorization: Bearer {token}

Response (200):
{
  "success": true,
  "data": {
    "id": 1,
    "user_id": 1,
    "wilayah_id": 1,
    "komoditas_id": 1,
    "harga": 15000.00,
    "satuan": "kg",
    "tanggal_panen": "2024-01-15",
    "tanggal_laporan": "2024-01-16",
    "catatan": "Panen bagus",
    "status": "pending",
    "created_at": "2024-01-16T10:00:00Z",
    "wilayah": { ... },
    "komoditas": { ... }
  }
}

Implementation: ApiService.getLaporanDetail()
```

### 10. Update Laporan
```
PUT /api/petani/laporan/{id}
Authorization: Bearer {token}
Content-Type: application/json

{
  "user_id": 1,
  "wilayah_id": 1,
  "komoditas_id": 1,
  "harga": 16000.00,
  "satuan": "kg",
  "tanggal_panen": "2024-01-15",
  "catatan": "Update info panen"
}

Response (200):
{
  "success": true,
  "message": "Laporan updated successfully",
  "data": {
    "id": 1,
    "user_id": 1,
    "wilayah_id": 1,
    "komoditas_id": 1,
    "harga": 16000.00,
    "satuan": "kg",
    "tanggal_panen": "2024-01-15",
    "tanggal_laporan": "2024-01-16",
    "catatan": "Update info panen",
    "status": "pending",
    "created_at": "2024-01-16T10:00:00Z",
    "updated_at": "2024-01-16T11:00:00Z"
  }
}

Implementation: ApiService.updateLaporan()
                Used in: LaporanProvider.updateLaporan()
```

### 11. Delete Laporan
```
DELETE /api/petani/laporan/{id}
Authorization: Bearer {token}

Response (200):
{
  "success": true,
  "message": "Laporan deleted successfully"
}

Implementation: ApiService.deleteLaporan()
                Used in: LaporanProvider.deleteLaporan()
```

---

## 🔑 Authentication Headers

Semua request (kecuali login/register) butuh:
```
Authorization: Bearer {token}
```

Token didapat dari response login dan disimpan di SharedPreferences dengan key `api_token`.

---

## ⚠️ Error Responses

### 400 Bad Request
```json
{
  "success": false,
  "message": "Bad request",
  "errors": { }
}
```

### 401 Unauthorized
```json
{
  "success": false,
  "message": "Unauthorized"
}
```
**Fix**: Login kembali, token mungkin expired

### 403 Forbidden
```json
{
  "success": false,
  "message": "Forbidden"
}
```
**Fix**: User tidak punya permission untuk action ini

### 404 Not Found
```json
{
  "success": false,
  "message": "Laporan not found"
}
```

### 422 Unprocessable Entity (Validation Error)
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "email": ["Email sudah terdaftar"],
    "harga": ["Harga harus angka"]
  }
}
```

### 500 Server Error
```json
{
  "success": false,
  "message": "Server error"
}
```
**Fix**: Check backend logs

---

## 🧪 Testing dengan Postman

### 1. Login
```
POST http://localhost:8000/api/login
Body (JSON):
{
  "email": "test@example.com",
  "password": "password"
}
```

Simpan token dari response.

### 2. Get Wilayah (Dengan Token)
```
GET http://localhost:8000/api/ref/wilayah
Headers:
Authorization: Bearer {token_dari_login}
```

### 3. Create Laporan
```
POST http://localhost:8000/api/petani/laporan
Headers:
Authorization: Bearer {token}
Content-Type: application/json

Body (JSON):
{
  "wilayah_id": 1,
  "komoditas_id": 1,
  "harga": 15000,
  "satuan": "kg",
  "tanggal_panen": "2024-01-15",
  "catatan": "Test laporan"
}
```

---

## 📱 Flutter Implementation Example

```dart
// Login
final authProvider = AuthProvider(apiService);
bool success = await authProvider.login('email@example.com', 'password');

// Get laporan list
final laporanProvider = LaporanProvider(apiService);
await laporanProvider.loadLaporanList();
List<LaporanHarga> laporan = laporanProvider.laporanList;

// Create laporan
LaporanHarga newLaporan = LaporanHarga(
  wilayahId: 1,
  komoditasId: 1,
  harga: 15000,
  satuan: 'kg',
  tanggalPanen: DateTime.now(),
  catatan: 'Test',
  status: 'pending',
  userId: 0,
);
bool created = await laporanProvider.createLaporan(newLaporan);

// Update laporan
bool updated = await laporanProvider.updateLaporan(1, updatedLaporan);

// Delete laporan
bool deleted = await laporanProvider.deleteLaporan(1);
```

---

## 🔄 Request/Response Cycle

```
Flutter UI
    ↓
Provider (call ApiService method)
    ↓
ApiService (build request + add token)
    ↓
Dio (HTTP client)
    ↓
Laravel Backend
    ↓
Database (SQLite)
    ↓
Backend returns response
    ↓
Dio (parse JSON)
    ↓
ApiService (handle errors)
    ↓
Provider (update state)
    ↓
UI (rebuild with new data)
```

---

## 📝 Notes

- Semua timestamps dalam ISO 8601 format
- Harga dalam format decimal
- Status laporan: `pending`, `approved`, `rejected`
- User role: `petani`, `admin`
- User status: `active`, `inactive`, `pending`
- Komoditas grade: `Grade A`, `Grade B`, `Grade C`

---

## 🔗 Related Files

- API Service: `lib/services/api_service.dart`
- Models: `lib/models/`
- Providers: `lib/providers/`
- Constants: `lib/core/constants/api_constants.dart`
- Exceptions: `lib/core/exceptions/api_exception.dart`

---

**Last Updated: 8 Januari 2026**


