# Development Detail Record LPB - Update Harga Detail

Tanggal: 2026-07-16
Route: `ics/detail_record_lpb`

## Ringkasan

Card `Detail LPB` sekarang memiliki kolom `#` pada tabel detail barang. Kolom ini berisi tombol hijau dengan icon uang rupiah untuk update harga detail LPB per barang.

## Alur

1. User memilih LPB dari daftar.
2. Pada tabel `Detail LPB`, user klik tombol icon uang rupiah di kolom `#`.
3. Modal menampilkan barang, qty LPB, harga satuan sebelumnya, dan total harga sebelumnya.
4. User mengisi `Harga Satuan Baru`.
5. Sistem menghitung `Total Harga Baru` dari `qty_diterima x harga_satuan_baru`.
6. Saat disimpan, harga aktif sebelum update dipindahkan ke kolom harga sebelumnya, lalu harga baru disimpan sebagai harga aktif.

## Sumber Harga Sebelumnya

- Untuk LPB yang sudah pernah di-update, harga sebelumnya diambil dari harga aktif `tb_lpb_detail`.
- Untuk LPB lama yang belum memiliki harga aktif, sistem memakai fallback harga exclude dari `tbpo_detail_po` berdasarkan `no_po`, `kd_po`, dan `kd_barang`.

Alur rekam dan finalisasi draft temporary penerimaan tidak diubah.

## File Aplikasi

- `application/config/routes.php`
- `application/controllers/logistik/C_Ics.php`
- `application/models/M_Logistik.php`
- `application/views/content/logistik/ics/detail_record_lpb.php`

## Database

Migration:

- `docs/database/ics_lpb_detail_price_update_20260716.sql`
