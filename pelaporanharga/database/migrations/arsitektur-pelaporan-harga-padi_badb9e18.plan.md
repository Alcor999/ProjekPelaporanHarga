---
name: arsitektur-pelaporan-harga-padi
overview: Merancang arsitektur alur sistem Flutter (petani) + Laravel (admin & publik) dengan MySQL untuk aplikasi pelaporan harga padi, fokus pada MVP dengan manajemen user & role dasar serta tampilan harga semi real-time (auto-refresh).
todos:
  - id: tentukan-db-schema
    content: Rinci skema tabel MySQL (users, wilayah, komoditas, laporan_harga, opsional rekap_harga_harian) beserta relasi dan tipe data
    status: in_progress
  - id: rancang-route-laravel
    content: Rancang struktur route Laravel (web & api), termasuk middleware role admin/petani dan endpoint publik
    status: pending
    dependencies:
      - tentukan-db-schema
  - id: kontrak-api-flutter
    content: Definisikan kontrak API (endpoint, request, response) untuk integrasi Flutter dengan Laravel
    status: pending
    dependencies:
      - rancang-route-laravel
  - id: alur-ui-web-flutter
    content: Sketsa alur UI utama untuk web (admin & publik) dan Flutter (petani) berdasarkan use case yang sudah disepakati
    status: pending
    dependencies:
      - kontrak-api-flutter
---

## Rencana Arsitektur & Alur Aplikasi Pelaporan Harga Padi

### 1. Gambaran Besar Arsitektur

- **Platform**: 
  - **Mobile (Flutter Android)**: khusus petani untuk input laporan harga.
  - **Web (Laravel)**: admin untuk kelola data & akun, publik untuk melihat harga.
- **Backend & DB**:
  - **Laravel + MySQL** sebagai API utama & panel admin.
  - Komunikasi Flutter ↔ Laravel via **REST API (JSON)**.
  - **Auto-refresh (AJAX polling)** di halaman publik/admin untuk efek "real time".

### 2. Desain Role & Hak Akses

- **Role utama**:
  - **Admin**: kelola user (petani, admin lain), verifikasi/rekap laporan, kelola master data (kecamatan, jenis padi, kualitas, dsb).
  - **Petani**: kirim laporan harga padi, lihat riwayat laporannya sendiri.
  - **Publik**: hanya melihat harga agregat & mungkin list singkat laporan terbaru, **tanpa login**.
- **Implementasi di Laravel**:
  - Tabel `users` dengan kolom `role` (`admin`, `petani`).
  - Middleware `auth` + `role:admin` dan `role:petani` untuk batasi akses.
  - Publik mengakses route yang tidak butuh auth, misalnya `/` (homepage harga) dan `/api/public/...`.

### 3. Desain Database Utama (MySQL)

- **Tabel inti** (nama bisa disesuaikan):
  - `users`: data login admin & petani (nama, email, password, role, alamat, dsb).
  - `petani_profiles`: detail tambahan petani (opsional jika ingin dipisah dari `users`).
  - `wilayah` (kecamatan/desa): untuk mapping lokasi pelaporan.
  - `komoditas`: jenis padi/grade kualitas.
  - `laporan_harga`: laporan per petani (relasi ke `users`, `wilayah`, `komoditas`, harga, satuan, tanggal panen, tanggal laporan, catatan).
  - `rekap_harga_harian` (opsional, bisa ditambah setelah dasar jalan): simpan hasil agregasi per hari/wilayah/komoditas untuk query yang lebih cepat.
- **Relasi kunci**:
  - `laporan_harga.user_id` → `users.id` (hanya petani).
  - `laporan_harga.wilayah_id` → `wilayah.id`.
  - `laporan_harga.komoditas_id` → `komoditas.id`.

### 4. Alur Bisnis Utama

#### 4.1. Alur Laporan Harga oleh Petani (Flutter → Laravel)

- **Langkah**:
  - Petani login di aplikasi Flutter (email/password yang dibuat admin).
  - Petani mengisi form laporan: pilih **wilayah**, **komoditas**, **jenis kualitas**, harga, satuan (per kg/ton), tanggal panen, dan catatan.
  - Flutter kirim data ke endpoint Laravel: `POST /api/petani/laporan`.
  - Laravel simpan ke `laporan_harga` dengan status misalnya `pending` atau `approved` default.
  - (Opsional) Admin bisa punya fitur "verifikasi" laporan.
- **Validasi penting**:
  - Pastikan user yang kirim adalah `role=petani`.
  - Cek range harga wajar (opsional, untuk menghindari typo ekstrim).

#### 4.2. Alur Rekap oleh Admin

- **Di panel admin (Laravel Blade atau SPA)**:
  - Admin melihat list `laporan_harga` per hari/wilayah.
  - Bisa **filter**: tanggal, wilayah, komoditas, petani.
  - Admin dapat:
    - Verifikasi/mengubah status laporan.
    - Mengubah nilai jika ditemukan salah input (dengan log perubahan).
  - Untuk laporan agregat:
    - Hitung **rata-rata harga**, **harga minimum**, **maksimum**, atau **median** per hari/wilayah/komoditas.
    - Simpan ke `rekap_harga_harian` via job/command (opsional) atau hitung on-the-fly dengan query agregat.

#### 4.3. Alur Tampilan Harga untuk Publik (Homepage Web)

- **Tanpa login**:
  - Route misalnya `/` menampilkan:
    - Tabel ringkas: wilayah vs harga rata-rata hari ini/terakhir.
    - Filter sederhana: pilih tanggal, pilih komoditas.
    - (Opsional) Grafik tren sederhana (line chart) per komoditas.
- **Real-time dengan AJAX polling**:
  - Frontend (Blade/JS) melakukan request `GET /api/public/harga-terkini?wilayah=...&komoditas=...` tiap, misalnya, **10–15 detik**.
  - Response berupa JSON dengan data agregat terbaru.
  - JS meng-update DOM (tabel/grafik) tanpa reload halaman.

### 5. Desain API Antara Flutter & Laravel

- **Autentikasi**:
  - Gunakan **Laravel Sanctum** untuk token-based auth mobile.
  - Endpoint:
    - `POST /api/login` → return token + role.
    - `POST /api/logout`.
- **Endpoint petani utama** (prefiks `api/petani`):
  - `GET /api/petani/profile` → info petani.
  - `GET /api/petani/laporan` → list laporan milik petani.
  - `POST /api/petani/laporan` → buat laporan baru.
  - `PUT /api/petani/laporan/{id}` (opsional, jika boleh edit sebelum diverifikasi).
- **Endpoint referensi (digunakan Flutter & Web)**:
  - `GET /api/ref/wilayah`.
  - `GET /api/ref/komoditas`.

### 6. Desain Frontend Web (Laravel)

- **Area publik**:
  - Halaman `homepage` yang simpel:
    - Tabel harga rata-rata terkini.
    - Dropdown filter (wilayah, komoditas, tanggal).
    - Auto-refresh via JS `setInterval` + fetch ke API publik.
- **Area admin**:
  - Login admin dengan guard web standar Laravel.
  - Menu utama:
    - Kelola user (tambah petani, tambah admin lain).
    - Kelola master data (`wilayah`, `komoditas`).
    - List & verifikasi `laporan_harga`.
    - Laporan agregat (tabel & grafik sederhana).

### 7. Contoh Diagram Alur Data (Mermaid)

```mermaid
flowchart LR
  subgraph mobileApp [Flutter Petani]
    petaniLogin[Petani Login]
    petaniForm[Lapor Harga]
  end

  subgraph backend [Laravel API + MySQL]
    apiAuth[API Auth (Sanctum)]
    apiLaporan[Endpoint Laporan Harga]
    dbMySQL[(MySQL)]
  end

  subgraph webApp [Web Laravel]
    adminPanel[Admin Panel]
    publicPage[Public Homepage]
    ajaxPolling[JS AJAX Polling]
  end

  petaniLogin --> apiAuth
  apiAuth --> dbMySQL

  petaniForm --> apiLaporan
  apiLaporan --> dbMySQL

  adminPanel --> dbMySQL
  ajaxPolling --> publicPage
  ajaxPolling --> apiLaporan
  apiLaporan --> dbMySQL
  publicPage -->|Tampilkan harga agregat| userPublik[User Publik]
```

### 8. Tahapan Implementasi (Prioritas)

- **Tahap 1 – Backend & DB dasar**
  - Setup proyek Laravel + koneksi MySQL.
  - Buat migrasi: `users` (dengan role), `wilayah`, `komoditas`, `laporan_harga`.
  - Implementasi auth Laravel untuk admin + Sanctum untuk API petani.
  - Buat endpoint dasar untuk login petani dan CRUD laporan.
- **Tahap 2 – Web admin & publik sederhana**
  - Halaman login admin & dashboard sederhana.
  - Halaman list laporan & form verifikasi.
  - Halaman publik (homepage) yang menampilkan harga rata-rata terkini (tanpa grafik dulu).
  - Tambahkan AJAX polling di homepage.
- **Tahap 3 – Aplikasi Flutter petani (MVP)**
  - Setup proyek Flutter, konfigurasi base URL API.
  - Halaman login, halaman list laporan, dan form buat laporan.
  - Integrasi dengan endpoint Laravel, handling error & validasi UI.
- **Tahap 4 – Penyempurnaan**
  - Tambah grafik tren (web publik/admin).
  - Tambah tabel `rekap_harga_harian` jika query mulai berat.
  - Optimasi keamanan (rate limiting API, validasi lebih ketat, logging audit).

### 9. Langkah Berikutnya

- Setelah Anda menyetujui arsitektur & alur ini, kita bisa lanjut:
  - Menentukan **struktur tabel lebih detail** (nama kolom, tipe data).
  - Menyiapkan **struktur folder dan route** di Laravel.
  - Menyusun **kontrak API** (request/response JSON) untuk diimplementasikan di Flutter.