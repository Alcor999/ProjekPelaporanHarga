# Insight & Rekomendasi: Fitur Penyetoran Hasil Panen ke Supplier

## Ringkasan Fitur
Petani dapat **menyetorkan hasil panen** ke beberapa supplier dengan harga berbeda per supplier. Contoh: Supplier A beli Rp 10.000/kg, Supplier B beli Rp 11.000/kg — petani bebas memilih mau menjual ke siapa.

---

## Dua Pendekatan Supplier

### Opsi 1: Admin Menambahkan Data Supplier (Master Data)
**Alur:**
- Admin menambah data supplier (nama, alamat, kontak)
- Admin memasukkan **harga beli per komoditas** yang ditawarkan masing-masing supplier
- Petani hanya **melihat** daftar supplier + harga, lalu memilih dan menyetorkan

**Kelebihan:**
- Implementasi lebih sederhana
- Kontrol penuh di tangan admin (validasi harga)
- Cocok jika supplier belum terbiasa pakai sistem digital

**Kekurangan:**
- Admin harus update harga manual (bisa ketinggalan)
- Supplier tidak punya akses langsung ke sistem

---

### Opsi 2: User Role "Supplier" dengan Login Sendiri (Direkomendasikan)
**Alur:**
- Supplier punya akun sendiri (register/approve admin)
- Supplier **login** ke web/app, lalu **input sendiri** harga beli per komoditas
- Petani melihat harga dari semua supplier (real-time) dan memilih
- Petani menyetorkan (pilih supplier + jumlah) — sistem mencatat transaksi

**Kelebihan:**
- Harga lebih **up-to-date** (supplier update sendiri)
- Supplier punya kontrol atas harga yang ditawarkan
- Transparansi: petani bisa bandingkan harga sebelum menjual
- Sistem lebih scalable untuk banyak supplier

**Kekurangan:**
- Perlu role baru, middleware, UI untuk supplier
- Supplier harus diajak pakai sistem

---

## Rekomendasi: **Hibrid (Fase Bertahap)**

| Fase | Langkah |
|------|---------|
| **Fase 1** | Admin tambah master supplier + harga (seperti master wilayah/komoditas). Petani bisa pilih supplier dan menyetor. |
| **Fase 2** | Tambah role Supplier — supplier bisa login dan update harga sendiri. Admin tetap bisa verifikasi/approve supplier baru. |

---

## Saran Arsitektur (Opsi 2 / Hibrid)

### 1. Database
- **`suppliers`** — id, name, phone, address, user_id (nullable, untuk supplier yang punya akun), is_active
- **`supplier_harga`** — supplier_id, komoditas_id, harga_per_satuan, satuan, valid_until (optional), updated_at
- **`penyetoran`** (transaksi penyetoran) — id, user_id (petani), supplier_id, komoditas_id, jumlah, satuan, harga_per_satuan, total_nilai, tanggal, status, catatan

### 2. User Role
- Tambah `supplier` ke enum/kolom role
- Supplier: akses web untuk kelola harga, lihat riwayat penyetoran dari petani

### 3. Flow Petani
1. Petani buka "Setor Hasil Panen" di app
2. Pilih komoditas + wilayah (opsional)
3. Sistem tampilkan daftar supplier + harga per kg/satuan
4. Petani pilih supplier, input jumlah yang disetor
5. Submit → tercatat di `penyetoran`

### 4. Flow Supplier (jika pakai role)
1. Supplier login ke web
2. Kelola harga: tambah/edit harga per komoditas
3. Lihat riwayat penyetoran dari petani

### 5. Admin
- Master supplier (CRUD)
- Jika pakai role supplier: approve akun supplier
- Lihat laporan penyetoran (per supplier, per komoditas, per periode)

---

## Fitur Tambahan yang Bisa Dipertimbangkan
- **Notifikasi** ke supplier saat ada penyetoran baru
- **Rating/ulasan** petani terhadap supplier
- **Jadwal operasional** supplier (jam buka/tutup)
- **Lokasi/koordinat** supplier untuk petani yang kirim sendiri

---

## Kesimpulan
**Saran utama:** Mulai dengan **Opsi 2 (Role Supplier)** jika memungkinkan — lebih berkelanjutan dan memberi nilai ke semua pihak. Jika ingin MVP cepat, gunakan **Fase 1 (Admin-only)** dulu, lalu upgrade ke Fase 2.
