# Database Detail Record LPB - Update Harga Detail

Tanggal: 2026-07-16
Route: `ics/detail_record_lpb`

## Tabel Terdampak

Tabel: `tb_lpb_detail`

## Kolom Baru

- `harga_satuan_sebelumnya`
  - Menyimpan harga satuan aktif sebelum update.
- `total_harga_sebelumnya`
  - Menyimpan total harga aktif sebelum update.
- `harga_satuan`
  - Menyimpan harga satuan baru sebagai harga aktif detail LPB.
- `total_harga`
  - Menyimpan total harga baru sebagai harga aktif detail LPB.
- `harga_update_by`
  - Menyimpan user yang melakukan update harga.
- `harga_update_at`
  - Menyimpan waktu update harga.

## Migration

File migration idempotent:

- `docs/database/ics_lpb_detail_price_update_20260716.sql`

Migration sudah dijalankan pada database lokal `kiucoid_karismaerp_local`.

## Catatan Data

Alur rekam dan finalisasi draft temporary penerimaan tidak diubah.

LPB yang belum memiliki harga pada `tb_lpb_detail` tetap dapat di-update karena sistem membaca fallback harga exclude dari `tbpo_detail_po` sebagai harga sebelumnya.
