# Development - ICS Detail Record LPB Logistik Column Access

Tanggal: 2026-07-18

## Scope

Route `ics/detail_record_lpb` membatasi tampilan tabel Detail LPB untuk user departemen Logistik.

## File yang Berubah

- `application/controllers/logistik/C_Ics.php`
- `application/views/content/logistik/ics/detail_record_lpb.php`

## Detail Implementasi

View memakai flag existing `$showLpbListPanel` sebagai penanda mode Logistik. Saat mode Logistik aktif, tabel Detail LPB hanya menampilkan kolom:

- Kode Barang
- Nama Barang
- No Lot
- Expired Date
- Qty Order
- Qty LPB

Kolom berikut disembunyikan dari mode Logistik:

- `#`
- `Harga Satuan`
- `Total Harga`

Header Detail LPB pada mode Logistik juga tidak menampilkan tombol `Update Invoice` dan `Update Faktur`. Mode Purchasing tetap mempertahankan tabel lengkap beserta tombol update invoice, update faktur, dan verifikasi harga.

Endpoint `ics/ajax_update_invoice` dan `ics/ajax_update_faktur_pajak` sekarang memakai guard `reject_non_admin_po_ajax()`, sehingga update invoice dan faktur hanya dapat dilakukan oleh user Purchasing/Admin PO.

Panel `Daftar LPB` pada mode Logistik dibuat sedikit lebih kecil pada layar desktop agar area Detail LPB lebih lega.

## Tata Cara Penggunaan

1. Login sebagai user departemen Logistik.
2. Buka `ics/detail_record_lpb?kd_po=...&no_po=...&kd_suplier=...`.
3. Pilih LPB pada panel daftar kiri.
4. Pastikan tombol `Update Invoice` dan `Update Faktur` tidak tampil.
5. Pastikan tabel Detail LPB menampilkan `Qty Order` sebelum `Qty LPB`.
6. Pastikan tabel Detail LPB tidak menampilkan kolom `#`, `Harga Satuan`, dan `Total Harga`.
7. Login sebagai user departemen Purchasing pada route yang sama untuk memastikan kolom lengkap masih tampil.

## Catatan QA

- Pastikan data Qty LPB tetap tampil benar untuk user Logistik.
- Pastikan data Qty Order tetap tampil sebelum Qty LPB untuk user Logistik.
- Pastikan tombol update invoice dan update faktur tidak tampil pada mode Logistik.
- Pastikan request manual ke endpoint update invoice atau faktur ditolak untuk user Logistik.
- Pastikan tombol verifikasi atau edit harga tidak tampil pada mode Logistik.
- Pastikan alur Purchasing tidak berubah.
