# Database ICS Detail Record LPB - Purchasing POST/UNPOST

Tanggal: 2026-07-20
Route: `ics/detail_record_lpb`

## Status Database

Tidak ada migrasi database baru pada perubahan ini.

Perubahan memakai struktur yang sudah tersedia:

- `tb_lpb.status_lpb`
  - `1`: `POST`
  - `0`: `UNPOST`
- `tb_lpb_log`
  - menyimpan aktivitas `UNPOST_LPB`, update harga, update invoice, dan update faktur pajak.
- `tb_lpb_detail.harga_satuan_sebelumnya`
- `tb_lpb_detail.total_harga_sebelumnya`

## Validasi Lokal

Skema lokal `kiucoid_karismaerp_local` sudah memiliki kolom:

- `tb_lpb.status_lpb`
- `tb_lpb_log.id_lpb`
- `tb_lpb_log.status_before`
- `tb_lpb_log.status_after`
- `tb_lpb_log.data_before`
- `tb_lpb_log.data_after`

Karena kolom sudah ada, tidak dibuat file SQL migrasi baru.

## Update 2026-07-20

Penyesuaian tampilan kolom `#`, tombol `Rekam`, dan guard verifikasi harga tidak menambah atau mengubah struktur tabel. Tidak ada SQL tambahan.

Tombol merah `UNPOST` memakai kolom existing `tb_lpb.status_lpb` dan log existing `tb_lpb_log`. Tidak ada perubahan struktur database.

## Update Rekam Bulk Verifikasi

Endpoint bulk verifikasi Purchasing sekarang mengubah `tb_lpb.status_lpb` dari `0` (`UNPOST`) menjadi `1` (`POST`) setelah semua `tb_lpb_detail` yang dikirim berhasil diverifikasi.

Perubahan ini hanya memakai kolom existing:

- `tb_lpb.status_lpb`
- `tb_lpb_log`

Tidak ada migration SQL baru.
