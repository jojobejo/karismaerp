# Panduan Akun Purchasing:

## 1. Data PO / LPB Purchasing
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
Gunakan untuk audit harian LPB dan monitoring status faktur pajak/invoice secara real-time.

Langkah penggunaan:
1. Buka `ics/lpb_report` (Menu `Logistik` / `Purchasing` -> `Laporan LPB`).
2. Gunakan **Filter Data Sajian (Nested Filter)**:
   - **Level 1 (Filter Utama)**: Isikan rentang tanggal LPB Dari & Sampai, serta pilih Sumber Transaksi (*Semua*, *LPB Manual*, *LPB Logistik*).
   - **Level 2 (Nested Sub-Filter Data)**: Pilih kriteria spesifik Aging Faktur Pajak, Aging Invoice, dan Jenis LPB.
   - **Filter Cepat**: Gunakan tombol badge instan (*Semua Data*, *FP Belum Diterima*, *Aging FP > 60 Hari*, *Khusus LPB Manual*, *Khusus LPB Logistik*).
3. Data pada tabel dan ringkasan statistik akan diperbarui secara otomatis (**Auto Live Reload**) tanpa perlu me-reload halaman browser.
4. Klik tombol `Reset Filter` jika ingin mengembalikan seluruh kriteria pencarian ke kondisi default.

Output yang dicek:
- Tanggal LPB & No LPB
- Sumber & Jenis LPB
- No PO, No SJ, No Invoice, dan Faktur Pajak
- Kode & Nama Supplier, Data Barang & Batch
- Nilai Finansial, Diskon, DPP, PPN 11%/12%, DPP Nilai Lain
- Aging Faktur Pajak & Lead Time Penerimaan

## 5. Retur Pembelian
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


## 8. Master Barang
Gunakan sebagai referensi barang dan data pembelian.

Fitur yang tersedia:
- Melihat data barang.
- Mencari barang.
- Melihat detail barang.
- Mengelola field master barang sesuai akses aplikasi.

Catatan penting:
- Secara bisnis, perubahan master barang harus dilakukan hati-hati karena berdampak ke pembelian, stok, dan jurnal.
- Jika hanya butuh referensi, hindari update data master.