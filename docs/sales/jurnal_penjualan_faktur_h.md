# Dokumentasi Modul: Jurnal Penjualan Faktur H (Pecahan Faktur Z)

## 1. Latar Belakang & Konsep Akuntansi
Dalam proses bisnis pemecahan faktur (Faktur Z dipecah menjadi beberapa Faktur H):
- Faktur Z merupakan faktur induk/keseluruhan order penjualan.
- Faktur H adalah faktur pecahan riil yang didistribusikan kepada masing-masing customer/kios.
- **Kebijakan Pelaporan Keuangan**:
  - Untuk menjaga jejak audit (*audit trail*), Faktur Z tetap memiliki nomor referensi dan riwayat awal.
  - Namun, di **Laporan Keuangan (Neraca dan Laba Rugi)**, nilai yang diakui **hanya Jurnal Penjualan dari Faktur H**. Jurnal Faktur Z tidak boleh dihitung ganda (*double accounting*).
  - Mekanisme teknis:
    - Ketika Faktur Z dipecah menjadi Faktur H, jurnal Faktur Z induk diubah statusnya menjadi **`VOID`** dengan keterangan `Digantikan oleh Faktur Pecahan H (Faktur Z telah dipecah)`.
    - Setiap Faktur H yang terbentuk diterbitkan jurnal penjualan resmi bertipe `FAKTUR_PECAH_H` dengan status **`POSTED`**.
    - Karena modul Buku Besar, Neraca, dan Laba Rugi (`Accounting_service::reports()`) menyaring data dengan klausul `WHERE j.status = 'POSTED'`, maka secara otomatis Jurnal Faktur H yang masuk dalam perhitungan laporan keuangan, sedangkan jurnal Faktur Z yang berstatus `VOID` dikecualikan.
    - Jika seluruh Faktur H pada suatu Faktur Z di-unpost atau di-reset, status jurnal Faktur Z induk otomatis dipulihkan (*restored*) kembali menjadi `POSTED`.

---

## 2. Struktur Akun Jurnal Penjualan Faktur H

| Posisi | Akun | Nama Akun Default | Deskripsi |
| :--- | :--- | :--- | :--- |
| **Debit** | `13099` | Piutang Usaha | Piutang dagang atas nama customer acak Faktur H |
| **Kredit** | `41011` / Akun Barang | Pendapatan Penjualan | Nilai penjualan bersih (DPP) barang |
| **Kredit** | `21024` | PPN Keluaran | Pajak Pertambahan Nilai (apabila faktur kena PPN) |

*Catatan: Jurnal HPP (Harga Pokok Penjualan) & Persediaan juga disiapkan jika master barang memiliki akun persediaan dan HPP terkait.*

---

## 3. Perubahan Skema Database
File migrasi: `docs/database/20261107_add_jurnal_penjualan_tbso_faktur_z_pecah.sql`

```sql
ALTER TABLE `tbso_faktur_z_pecah`
ADD COLUMN `id_jurnal_penjualan` BIGINT UNSIGNED DEFAULT NULL AFTER `nomor_jurnal_a`,
ADD COLUMN `nomor_jurnal_penjualan` VARCHAR(50) DEFAULT NULL AFTER `id_jurnal_penjualan`,
ADD COLUMN `id_jurnal_hpp` BIGINT UNSIGNED DEFAULT NULL AFTER `nomor_jurnal_penjualan`,
ADD COLUMN `nomor_jurnal_hpp` VARCHAR(50) DEFAULT NULL AFTER `id_jurnal_hpp`;
```

---

## 4. Rincian Teknis Implementasi (MVC)

### A. Model `application/models/M_Journal.php`
- `post_jurnal_penjualan_faktur_h($id_pecah, $userId)`: Menghasilkan nomor jurnal berawalan `SJ-`, membuat header di `tbkeu_jurnal`, mengisi baris debit/kredit di `tbkeu_jurnal_detail`, dan memperbarui field `id_jurnal_penjualan` serta `nomor_jurnal_penjualan` pada `tbso_faktur_z_pecah`.
- `delete_jurnal_penjualan_faktur_h($id_pecah)`: Menghapus ayat jurnal penjualan dan HPP Faktur H jika dilakukan pembatalan/reset.
- `void_jurnal_faktur_z_induk($no_faktur_z, $userId)`: Mengubah status jurnal penjualan induk Faktur Z menjadi `VOID` saat berhasil dipecah.
- `restore_jurnal_faktur_z_induk($no_faktur_z)`: Memulihkan status jurnal penjualan Faktur Z kembali menjadi `POSTED` saat pemecahan dibatalkan.

### B. Model `application/models/M_SalesOrder.php`
- `proses_split_faktur_batch()`: Memanggil otomatis pembuatan jurnal penjualan Faktur H setelah baris detail tersimpan dan memanggil `void_jurnal_faktur_z_induk()`.
- `auto_split_faktur_kios()`: Menjalankan logika pembuatan jurnal H otomatis saat auto-split dieksekusi.
- `ensure_faktur_z_pecah_tables()`: Memastikan tabel `tbso_faktur_z_pecah` memiliki kolom `id_jurnal_penjualan`, `nomor_jurnal_penjualan`, `id_jurnal_hpp`, dan `nomor_jurnal_hpp`.

### C. Controller `application/controllers/sales/C_SalesOrder.php`
- Endpoint JSON: `jurnal_penjualan_faktur_z_pecah_json($id_pecah)` untuk memuat data popup modal detail jurnal penjualan Faktur H (header, baris debit/kredit, total).
- Endpoint Sinkronisasi: `sinkronkan_jurnal_penjualan_faktur_h()` untuk melakukan posting massal pada Faktur H yang belum memiliki jurnal penjualan.
- Sinkronisasi pada aksi `hapus_faktur_pecah`, `unpost_faktur_pecah_batch`, dan `reset_pecah_faktur_induk`.

### D. View & User Interface `application/views/content/sales/pecah_faktur_list.php`
- **Tab Faktur Pecahan Kode H**:
  - Kolom **Jurnal Penjualan** menampilkan nomor jurnal (`SJ-xxxxx`) dengan warna badge biru yang interaktif (dapat diklik).
  - Tombol **Sinkron Jurnal Penjualan H** di baris aksi atas untuk sinkronisasi massal sewaktu-waktu.
  - Modal interaktif **Detail Jurnal Penjualan Faktur H** (`#modalDetailJurnalPenjualanH`) untuk melihat rincian Debit/Kredit, akun, dan balance jurnal secara langsung tanpa harus berpindah ke menu akuntansi.

---

## 5. Cara Penggunaan / User Guide

1. **Membuat Faktur Pecahan**:
   - Buka menu **Sales Order > Pecah Faktur Z**.
   - Masuk ke tab **Faktur Pecahan Kode H** (`https://karismaerp.test/sales_order/pecah_faktur#content-faktur-h`).
   - Lakukan pemecahan faktur seperti biasa (baik manual maupun batch/auto).
   - Setelah proses selesai, setiap Faktur H yang berstatus `posted` otomatis mendapatkan nomor jurnal penjualan (misal: `SJ-07112600001`).
   
2. **Melihat Rincian Jurnal**:
   - Pada tabel Faktur Pecahan H, klik pada badge nomor jurnal (misal `SJ-07112600001`).
   - Modal popup akan menampilkan rincian jurnal:
     - Nomor referensi faktur H.
     - Tanggal transaksi.
     - Baris Debit: Piutang Usaha.
     - Baris Kredit: Penjualan Produk & PPN.
     - Status: POSTED (Seimbang / Balance).

3. **Verifikasi Laporan Keuangan**:
   - Buka menu **Akuntansi > Laporan Laba Rugi** atau **Neraca**.
   - Nilai penjualan dan piutang yang muncul adalah agregat dari Faktur H (`POSTED`).
   - Faktur Z induk yang sudah dipecah tidak akan menduplikasi angka pendapatan karena status jurnal induknya otomatis `VOID`.

