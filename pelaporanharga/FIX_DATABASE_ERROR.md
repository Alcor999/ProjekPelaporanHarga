# Perbaikan Database Error: "no such table: main.wilayahs"

## Masalah yang Ditemukan

Error SQL:
```
SQLSTATE[HY000]: General error: 1 no such table: main.wilayahs
```

**Penyebab:**
- Di migration `create_laporan_harga_table.php`, foreign key `wilayah_id` menggunakan `constrained()` tanpa parameter
- Laravel secara default mencari tabel dengan nama plural dari model (`wilayahs`)
- Padahal tabel yang dibuat adalah `wilayah` (singular)

## Perbaikan yang Telah Dilakukan

### 1. Perbaiki Foreign Key Constraint

**Sebelum:**
```php
$table->foreignId('wilayah_id')->constrained()->onDelete('cascade');
$table->foreignId('komoditas_id')->constrained()->onDelete('cascade');
```

**Sesudah:**
```php
$table->foreignId('wilayah_id')->constrained('wilayah')->onDelete('cascade');
$table->foreignId('komoditas_id')->constrained('komoditas')->onDelete('cascade');
```

Dengan explicitly menyebutkan nama tabel, Laravel akan menggunakan tabel yang benar.

### 2. Re-run Migration

Sudah dijalankan:
```bash
php artisan migrate:fresh --seed
```

Ini akan:
- Drop semua tabel
- Re-create semua tabel dengan foreign key yang benar
- Seed data awal (admin user, wilayah, komoditas)

## Status Setelah Perbaikan

✅ Foreign key constraint sudah diperbaiki  
✅ Database sudah di-recreate dengan struktur yang benar  
✅ Data seed sudah dijalankan (admin user, wilayah, komoditas)  
✅ Tabel `wilayah` dan `komoditas` sudah ada dengan data  

## Catatan Penting

⚠️ **Perhatian:** `migrate:fresh` akan menghapus semua data yang ada!

Jika ada data penting yang perlu dipertahankan:
1. Backup database terlebih dahulu
2. Atau gunakan `migrate:refresh` jika hanya ingin re-run migrations tanpa drop database

## Verifikasi

### 1. Cek Tabel yang Ada

```bash
php artisan tinker
>>> DB::select("SELECT name FROM sqlite_master WHERE type='table'");
```

Harus menampilkan:
- `users`
- `wilayah`
- `komoditas`
- `laporan_harga`
- `personal_access_tokens`
- dll

### 2. Cek Foreign Key

```bash
php artisan tinker
>>> Schema::getConnection()->getDoctrineSchemaManager()->listTableForeignKeys('laporan_harga');
```

Harus menampilkan foreign key ke:
- `users.id`
- `wilayah.id` (bukan `wilayahs.id`)
- `komoditas.id`

### 3. Test Insert Data

Coba buat laporan baru dari aplikasi Flutter. Error "no such table: main.wilayahs" seharusnya sudah hilang.

## Troubleshooting

### Masalah: Masih ada error "no such table"

**Kemungkinan penyebab:**
1. Migration belum dijalankan ulang
2. Cache belum di-clear

**Solusi:**
```bash
# Clear cache
php artisan config:clear
php artisan cache:clear

# Re-run migration
php artisan migrate:fresh --seed
```

### Masalah: Data hilang setelah migrate:fresh

**Ini normal!** `migrate:fresh` akan menghapus semua data.

**Solusi:**
- Jika perlu data, gunakan seeder atau import data dari backup
- Atau gunakan `migrate:refresh` jika hanya ingin re-run migrations

## Langkah Selanjutnya

1. ✅ Migration sudah diperbaiki
2. ✅ Database sudah di-recreate
3. ✅ Data seed sudah dijalankan
4. ✅ Coba buat laporan baru dari aplikasi Flutter

Error "no such table: main.wilayahs" seharusnya sudah teratasi!
