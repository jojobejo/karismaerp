# Database Detail Record LPB - Accept Verifikasi Harga

Tanggal: 2026-07-16
Route: `ics/detail_record_lpb`

## Tabel Terdampak

Tabel: `tb_lpb_detail`

## Kolom Baru

- `harga_verified_by`
  - User yang melakukan Accept harga.
- `harga_verified_at`
  - Waktu harga diterima/verifikasi.

## Perubahan Rule Status

Status LPB `4` hanya tercapai jika semua detail memiliki:

- `harga_verified_at` tidak kosong
- `harga_satuan` lebih dari `0`
- `total_harga` lebih dari `0`

Edit harga hanya mengisi `harga_update_by` dan `harga_update_at`, serta mengosongkan kembali verifikasi harga.

## Migration

File migration idempotent:

- `docs/database/ics_lpb_detail_price_accept_20260716.sql`

Migration sudah dijalankan pada database lokal `kiucoid_karismaerp_local`.
