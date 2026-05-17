# Fitur SALESCK - Aktivitas Warehouse

### Urutan Status Lengkap

```
MENUNGGU (baru dibuat)
    ↓ (SALESCK klik Siap Loading)
SIAP_LOADING (waktu_siap_loading dicatat)
    ↓ (ADMLOG pilih CETAK_DO)
CETAK_DO
    ↓ (ADMLOG pilih DO_SELESAI)
DO_SELESAI (waktu_do_selesai dicatat)
    ↓ (CHECKER mulai siapkan)
PENYIAPAN_BARANG
    ↓ (CHECKER selesai siapkan)
BARANG_SIAP (waktu_selesai_siapkan dicatat)
    ↓ (CHECKER start loading)
PROSES_LOADING
    ↓ (CHECKER done)
DONE (waktu_selesai dicatat)
```

---



## Testing

### Test Case 1: SALESCK Membuat Loading KK
1. Login sebagai SALESCK
2. Klik "Tambah Loading KK"
3. Isi keterangan, klik Simpan
4. **Expected**: Data muncul dengan status `MENUNGGU` dan tombol **[Siap Loading]**

### Test Case 2: SALESCK Klik Siap Loading
1. Klik tombol **[Siap Loading]** pada data dengan status MENUNGGU
2. **Expected**: 
   - Status berubah menjadi `SIAP LOADING`
   - Tombol berubah menjadi **[Detail]**
   - ADMLOG bisa lihat dropdown status

### Test Case 3: ADMLOG Update Status
1. Login sebagai ADMLOG
2. Pilih status `CETAK_DO` atau `DO_SELESAI` dari dropdown
3. Klik **[Simpan]**
4. **Expected**: Status berubah sesuai pilihan

### Test Case 4: CHECKER Siapkan Barang
1. Login sebagai CHECKER
2. Klik **[Siapkan Barang]** pada data dengan status `DO_SELESAI`
3. Update progres dan klik **[Selesai Siapkan]**
4. **Expected**: Status berubah menjadi `BARANG_SIAP`

### Test Case 5: CHECKER Start Loading & Done
1. Klik **[Start Loading]** pada data dengan status `BARANG_SIAP`
2. Update progres dan klik **[Done]**
3. **Expected**: Status berubah menjadi `DONE`

### Test Case 6: ADMLOG Tidak Bisa Edit/Hapus Status MENUNGGU
1. Login sebagai ADMLOG
2. Lihat data dengan status `MENUNGGU`
3. **Expected**: Tidak ada tombol Edit/Hapus (kolom aksi kosong atau hanya tombol Detail)

### Test Case 7: ADMLOG Bisa Edit/Hapus Status SIAP_LOADING
1. Login sebagai ADMLOG
2. Lihat data dengan status `SIAP_LOADING`, `CETAK_DO`, atau `DO_SELESAI`
3. **Expected**: Tombol Edit dan Hapus muncul

---

## Catatan Penting

1. **Status MENUNGGU**: Status awal saat buat Loading KK/LK (hanya SALESCK yang bisa aksi, ADMLOG tidak bisa edit/hapus)
2. **Status SIAP_LOADING**: Setelah SALESCK konfirmasi siap loading (ADMLOG bisa edit/hapus mulai dari status ini)
3. **Status BARANG_SIAP**: Setelah CHECKER selesai siapkan barang (dulu pakai SIAP_LOADING)
4. **Durasi Tunggu**: Dihitung dari `waktu_siap_loading` sampai `waktu_do_selesai` (untuk analisis performa)
5. **Akses Edit/Hapus**: ADMLOG bisa edit/hapus data dengan status SIAP_LOADING, CETAK_DO, DO_SELESAI (tidak termasuk MENUNGGU)

---

## Kontak

Jika ada pertanyaan atau bug, hubungi tim development.
