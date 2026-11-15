# Panduan Build & Rilis Android (APK / AAB)

Aplikasi Flutter `Lapor Harga Panen` (`id.petani.pelaporanhargapanen`) membaca URL server backend melalui compile-time define `--dart-define=API_BASE_URL=...` (bukan hardcoded di source code).

## 1. Membuat Keystore Rilis (Sekali Saja)

Jalankan perintah berikut dari folder `pelaporan_harga_petani/android/`:

```bash
keytool -genkey -v -keystore lapor-harga-release.jks -keyalg RSA -keysize 2048 -validity 10000 -alias laporhargakey
```

Simpan file `lapor-harga-release.jks` di tempat aman dan **jangan pernah** di-commit ke git.

## 2. Konfigurasi `android/key.properties`

1. Salin `android/key.properties.example` menjadi `android/key.properties`.
2. Isi `storePassword`, `keyPassword`, `keyAlias`, dan path `storeFile` sesuai keystore yang baru dibuat.

> Untuk build uji lokal tanpa keystore rilis, Gradle menolak release build kecuali Anda menambahkan `-PallowDebugRelease=true` secara eksplisit.

## 3. Build APK & App Bundle untuk Production

Pastikan backend Laravel sudah aktif dan memiliki sertifikat SSL HTTPS valid pada subdomain Anda, lalu jalankan dari `pelaporan_harga_petani/`:

- **Build APK (distribusi langsung / uji perangkat fisik):**
  ```bash
  flutter build apk --release --dart-define=API_BASE_URL=https://api.domainanda.com/api
  ```
  Output berada di: `build/app/outputs/flutter-apk/app-release.apk`

- **Build Android App Bundle / AAB (unggah ke Google Play Store):**
  ```bash
  flutter build appbundle --release --dart-define=API_BASE_URL=https://api.domainanda.com/api
  ```
  Output berada di: `build/app/outputs/bundle/release/app-release.aab`

## 4. Checklist Sebelum Dibagikan ke Petani

1. Pastikan `API_BASE_URL` berakhiran `/api` dan menggunakan `https://`.
2. Uji instalasi `app-release.apk` di perangkat Android fisik minimal 1 kali:
   - Registrasi akun baru dan persetujuan admin dari web panel.
   - Login petani, buat laporan baru dengan foto bukti, edit laporan pending, dan hapus laporan.
   - Buka Harga Pasar, Setor Panen, Riwayat Penyetoran, Rating Supplier, dan Notifikasi.
   - Uji mode offline (matikan data/Wi-Fi, buka ulang aplikasi -> sesi profil tersimpan dan banner offline tetap muncul tanpa mengeluarkan akun).
