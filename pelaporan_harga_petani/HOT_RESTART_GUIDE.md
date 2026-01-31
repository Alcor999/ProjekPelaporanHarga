# Cara Hot Restart Flutter App

## Metode 1: Dari Terminal (Jika aplikasi berjalan dari terminal)

### Hot Restart (Full Restart)
Tekan tombol berikut di terminal tempat aplikasi Flutter berjalan:

**`R`** (huruf R kapital) - Hot Restart
- Ini akan merestart aplikasi sepenuhnya
- Semua state akan direset
- Cocok untuk perubahan konfigurasi seperti API URL

### Hot Reload (Reload cepat)
Tekan tombol berikut:

**`r`** (huruf r kecil) - Hot Reload
- Hanya me-reload perubahan code
- State aplikasi tetap terjaga
- Tidak cocok untuk perubahan konfigurasi seperti API URL

### Stop aplikasi
Tekan:

**`q`** - Quit (stop aplikasi)

---

## Metode 2: Dari VS Code

### Hot Restart:
1. Buka Command Palette: `Cmd + Shift + P` (Mac) atau `Ctrl + Shift + P` (Windows/Linux)
2. Ketik: `Flutter: Hot Restart`
3. Atau gunakan shortcut: `Cmd + Shift + F5` (Mac) atau `Ctrl + Shift + F5` (Windows/Linux)

### Hot Reload:
1. Klik tombol **"Hot Reload"** (ikon 🔥) di toolbar
2. Atau tekan: `Cmd + \` (Mac) atau `Ctrl + \` (Windows/Linux)
3. Atau gunakan Command Palette: `Flutter: Hot Reload`

---

## Metode 3: Dari Android Studio / IntelliJ IDEA

### Hot Restart:
1. Klik tombol **"Hot Restart"** (ikon 🔄) di toolbar
2. Atau gunakan shortcut: `Ctrl + Shift + F10` (Windows/Linux) atau `Cmd + Shift + R` (Mac)

### Hot Reload:
1. Klik tombol **"Hot Reload"** (ikon ⚡) di toolbar
2. Atau tekan: `Ctrl + \` (Windows/Linux) atau `Cmd + \` (Mac)

---

## Metode 4: Menggunakan Flutter Commands

### Jika aplikasi tidak berjalan:
Jalankan aplikasi dari terminal:

```bash
cd /Users/mymac/Projects/pelaporan_harga_petani
flutter run
```

### Jika aplikasi sudah berjalan di device lain:
Restart aplikasi dengan:

```bash
# Stop semua Flutter processes
flutter clean

# Jalankan lagi
flutter run
```

---

## Kapan Menggunakan Hot Restart vs Hot Reload?

### Gunakan **Hot Restart** untuk:
- ✅ Perubahan konfigurasi (seperti API URL)
- ✅ Perubahan di `main.dart`
- ✅ Perubahan dependencies
- ✅ Perubahan constants/configuration
- ✅ Setelah mengubah kode di providers/services yang diinisialisasi saat startup

### Gunakan **Hot Reload** untuk:
- ✅ Perubahan UI/widget
- ✅ Perubahan styling
- ✅ Perubahan logika sederhana
- ✅ Debugging cepat

---

## Setelah Mengubah API URL

Karena Anda sudah mengubah API URL di `api_constants.dart`, **WAJIB** melakukan **Hot Restart** (bukan Hot Reload) agar perubahan berlaku:

1. **Dari Terminal:** Tekan `R` (huruf R kapital)
2. **Dari VS Code:** `Cmd + Shift + P` → `Flutter: Hot Restart`
3. **Dari Android Studio:** Klik tombol "Hot Restart" (ikon 🔄)

---

## Troubleshooting

### Jika Hot Restart tidak bekerja:

1. **Stop aplikasi dan jalankan ulang:**
   ```bash
   # Di terminal, tekan 'q' untuk quit
   # Kemudian jalankan lagi:
   flutter run
   ```

2. **Clear cache dan rebuild:**
   ```bash
   flutter clean
   flutter pub get
   flutter run
   ```

3. **Restart device/emulator:**
   - Matikan dan nyalakan kembali Android device
   - Atau restart emulator

4. **Uninstall dan install ulang:**
   ```bash
   flutter uninstall
   flutter run
   ```

---

## Catatan Penting

⚠️ **Setelah mengubah API URL, selalu gunakan Hot Restart, bukan Hot Reload!**

Hot Reload tidak akan memuat ulang konstanta yang sudah di-compile, jadi perubahan API URL tidak akan terlihat jika hanya melakukan Hot Reload.
