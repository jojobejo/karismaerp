# Panduan Akun Purchasing: Module, Cara Penggunaan, dan Alur Bisnis

Tanggal: 2026-07-30

## Akun

- Username: `purchasing`
- Password awal yang diberikan: `1234`
- Nama user: `PURCHASING`
- Jobdesk: `ADMIN PO`
- Departemen: `purchasing`
- Landing setelah login: `dashboard`

## Ringkasan Module

| Module | Route | Fungsi bisnis utama |
| --- | --- | --- |
| Dashboard | `dashboard` | Pintu masuk module Purchasing |
| Data PO / LPB Purchasing | `ics/icspo` | Monitoring LPB dari PO, invoice, pajak, harga, dan status barang |
| Detail LPB | `ics/detail_record_lpb` | Update invoice, pecah invoice, update faktur, verifikasi harga, post/unpost LPB |
| Input LPB Manual | `ics/lpb_manual` | Membuat LPB tanpa PO untuk kasus khusus |
| Laporan LPB | `ics/lpb_report` | Monitoring LPB manual dan LPB dari PO |
| Retur Dashboard | `ics/retur` | Pintu masuk retur pembelian dan retur penjualan |
| Retur Pembelian | `ics/retur/pembelian` | Membuat draft retur dari LPB final dan verifikasi Purchasing |
| Adjustment Harga LPB | `ics/retur/pembelian/adjustment` | Koreksi harga invoice LPB yang sudah berjalan |
| Pending PO | `pendingpo` | Monitoring PO yang belum selesai diterima |
| Master Barang | `master_barang` | Referensi dan pengelolaan master barang |

## Cara Masuk

1. Buka aplikasi KarismaERP.
2. Login dengan username `purchasing`.
3. Masukkan password.
4. Setelah berhasil, aplikasi masuk ke `dashboard`.
5. Pada dashboard, buka section `PURCHASING`.

Catatan: pada kondisi database lokal saat pengecekan, sidebar akun ini bisa hanya menampilkan `Log Out` karena `tb_menu` kosong dan belum ada sidebar statis khusus `ADMIN PO`. Gunakan dashboard atau buka route module langsung.

## 1. Data PO / LPB Purchasing

Route: `ics/icspo`

Gunakan module ini untuk memantau LPB yang sudah dibuat dari PO oleh Logistik dan menindaklanjuti data administrasi Purchasing.

Langkah penggunaan:

1. Buka `dashboard`.
2. Klik `Data PO` pada section `PURCHASING`.
3. Sistem menampilkan panel `Data LPB Purchasing`.
4. Gunakan filter:
   - `Semua`
   - `Belum Invoice`
   - `Belum Pajak`
   - `Belum Afirmasi Harga`
5. Pilih LPB yang perlu dicek.
6. Buka detail LPB untuk update invoice, faktur, harga, dan status.

Yang dilakukan Purchasing:

- Memastikan nomor LPB, PO, supplier, SJ, invoice, faktur pajak, dan grand total sudah benar.
- Memastikan harga detail sudah sesuai invoice.
- Memverifikasi harga sebelum LPB dianggap siap secara administrasi.
- Mengubah LPB dari `UNPOST` ke `POST` saat data sudah final.

## 2. Detail LPB

Route: `ics/detail_record_lpb`

Fitur utama:

- `Update Invoice`
- `Pecah Invoice`
- `Update Faktur`
- Update jenis LPB
- Update harga dan qty detail saat status `UNPOST`
- `Accept` harga detail
- Bulk accept harga detail
- `POST` / Rekam LPB
- `UNPOST` LPB dengan alasan
- Print LPB bila dibutuhkan

Aturan penggunaan:

1. Update harga hanya dilakukan saat LPB masih `UNPOST`.
2. Setelah LPB `POST`, fokusnya adalah verifikasi dan kontrol, bukan edit bebas.
3. Jika harus `UNPOST`, isi alasan dengan jelas.
4. Jangan mengubah stok fisik dari sisi Purchasing; stok fisik adalah domain penerimaan/warehouse.

## 3. Input LPB Manual

Route: `ics/lpb_manual`

Gunakan hanya saat penerimaan perlu dicatat tanpa PO.

Langkah penggunaan:

1. Buka `Input LPB Manual`.
2. Isi tanggal LPB.
3. Pilih jenis LPB.
4. Pilih gudang.
5. Isi nomor SJ dan nomor invoice bila ada.
6. Isi keterangan yang menjelaskan alasan input manual.
7. Klik tambah barang.
8. Pilih barang dari master.
9. Isi qty, satuan, no lot, expired date, dan harga satuan.
10. Cek ulang semua detail.
11. Simpan LPB Manual.
12. Cek hasil di `ics/lpb_report`.

Kontrol bisnis:

- Jangan gunakan LPB Manual untuk melewati PO normal.
- Keterangan harus cukup jelas untuk audit.
- Lot dan expired wajib mengikuti dokumen/fisik barang.

## 4. Laporan LPB

Route: `ics/lpb_report`

Gunakan untuk audit harian LPB.

Langkah penggunaan:

1. Buka `Laporan LPB`.
2. Pilih sumber:
   - `Semua Sumber`
   - `LPB Manual Purchasing`
   - `LPB Logistik dari PO`
3. Isi rentang tanggal bila perlu.
4. Klik filter.
5. Cocokkan nilai, qty, no LPB, no PO, gudang, dan keterangan.

Output yang dicek:

- Tanggal LPB
- Sumber LPB
- No LPB
- Referensi manual
- No PO
- Gudang
- Total item
- Total qty
- Nilai
- Keterangan

## 5. Retur Pembelian

Route: `ics/retur/pembelian`

Gunakan ketika barang dari LPB final harus dikembalikan ke supplier.

Langkah membuat draft:

1. Buka `Retur`, lalu pilih `Retur Pembelian`.
2. Cari LPB berdasarkan supplier, nomor PO, atau nomor LPB.
3. Pilih LPB final.
4. Isi tanggal retur.
5. Pilih jenis penyelesaian.
6. Isi alasan retur.
7. Pada detail barang, isi qty retur dan alasan item bila perlu.
8. Klik `Buat Draft Retur`.
9. Cek draft pada daftar retur.

Langkah verifikasi Purchasing:

1. Pastikan status retur sudah `DRAFT` atau `SUBMITTED`.
2. Klik tombol `Purchasing`.
3. Isi catatan jika diminta.
4. Pastikan status berubah menjadi tahap verifikasi Purchasing.

Batasan bisnis:

- Qty retur tidak boleh melebihi qty LPB dan stok batch.
- Purchasing memvalidasi supplier, harga, alasan, item, dan dokumen pendukung.
- Accounting dan posting final harus mengikuti otorisasi perusahaan.

## 6. Adjustment Harga LPB

Route: `ics/retur/pembelian/adjustment`

Gunakan ketika harga invoice benar berbeda dari harga LPB yang sudah berjalan.

Langkah penggunaan:

1. Buka `Adjustment Harga LPB`.
2. Pilih LPB yang salah.
3. Isi tanggal adjustment.
4. Sistem menampilkan detail barang LPB.
5. Isi harga invoice benar pada detail yang perlu dikoreksi.
6. Isi alasan adjustment.
7. Klik `Posting Adjustment`.
8. Catat hasil adjustment untuk Accounting.

Batasan bisnis:

- Dipakai untuk koreksi harga, bukan koreksi qty fisik.
- Semua koreksi harus punya alasan.
- Jangan edit database langsung untuk membetulkan harga LPB yang sudah bergerak.

## 7. Pending PO

Route: `pendingpo`

Gunakan untuk memonitor PO yang belum selesai diterima.

Langkah penggunaan:

1. Buka `Pending PO`.
2. Cek supplier dan nomor PO.
3. Bandingkan `QTY PO`, `QTY Datang`, dan `QTY Sisa`.
4. Tindak lanjuti ke supplier atau Logistik bila barang belum datang atau penerimaan belum lengkap.

## 8. Master Barang

Route: `master_barang`

Gunakan sebagai referensi barang dan data pembelian.

Fitur yang tersedia:

- Melihat data barang.
- Mencari barang.
- Melihat detail barang.
- Mengelola field master barang sesuai akses aplikasi.

Catatan penting:

- Karena akun ini memiliki `akses_lv=1`, aplikasi memberi akses edit yang luas di Master Barang.
- Secara bisnis, perubahan master barang harus dilakukan hati-hati karena berdampak ke pembelian, stok, dan jurnal.
- Jika hanya butuh referensi, hindari update data master.

## Alur Bisnis Purchasing Harian

1. Login sebagai `purchasing`.
2. Buka dashboard section `PURCHASING`.
3. Cek `Pending PO` untuk PO yang belum selesai.
4. Buka `Data PO` / `ics/icspo`.
5. Filter LPB yang belum invoice, belum faktur pajak, atau belum afirmasi harga.
6. Buka detail LPB.
7. Update invoice dan faktur pajak.
8. Cek harga detail terhadap invoice supplier.
9. Jika harga benar, lakukan `Accept` harga.
10. Jika semua data sudah benar, lakukan `POST` / Rekam LPB.
11. Cek `Laporan LPB` untuk memastikan transaksi tercatat.
12. Jika ada barang harus dikembalikan, buat `Retur Pembelian`.
13. Jika ada koreksi harga setelah LPB berjalan, gunakan `Adjustment Harga LPB`.
14. Koordinasikan hasil retur/adjustment dengan Accounting.

## Alur Bisnis Saat Ada Selisih

### Selisih PO vs barang datang

1. Cek `Pending PO`.
2. Cek LPB terkait di `Data PO`.
3. Pastikan qty datang sudah dicatat Logistik.
4. Jika belum lengkap, follow up supplier atau Logistik.

### Invoice belum masuk

1. Filter `Belum Invoice`.
2. Buka detail LPB.
3. Update nomor dan tanggal invoice.
4. Jika satu LPB memiliki beberapa invoice, gunakan `Pecah Invoice`.

### Faktur pajak belum masuk

1. Filter `Belum Pajak`.
2. Buka detail LPB.
3. Update nomor dan tanggal faktur pajak.

### Harga invoice berbeda

1. Jika LPB masih `UNPOST`, update harga detail dari detail LPB.
2. Jika LPB sudah berjalan/posted dan perlu koreksi harga, gunakan `Adjustment Harga LPB`.
3. Jangan koreksi langsung dari database.

### Barang harus dikembalikan

1. Buka `Retur Pembelian`.
2. Pilih LPB final.
3. Buat draft retur.
4. Submit dan lakukan verifikasi Purchasing.
5. Lanjutkan ke Accounting/posting sesuai otorisasi.

## Rekomendasi Kontrol

1. Buat level akses khusus Purchasing agar tidak bergantung pada `Super Administrator`.
2. Isi menu dinamis `tb_menu` dan `tb_akses_menu` agar sidebar dan akses module lebih rapi.
3. Tambahkan sidebar khusus `ADMIN PO` jika user purchasing sering bekerja dari sidebar.
4. Pisahkan dengan jelas tugas Purchasing, Logistik, dan Accounting pada SOP operasional.
5. Gunakan audit trail aplikasi untuk semua koreksi harga, retur, post, unpost, dan adjustment.
