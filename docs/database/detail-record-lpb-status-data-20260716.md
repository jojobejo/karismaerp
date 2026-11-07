# Database Detail Record LPB - Status Data LPB

Tanggal: 2026-07-16
Route: `ics/detail_record_lpb`

## Tabel Terdampak

Tabel: `tb_lpb`

## Kolom Baru

- `status_lpb` tinyint default `1`

## Nilai Status

- `1`: `S1 Draft`
- `2`: `S2 Nomor Ada`
- `3`: `S3 Invoice Ada`
- `4`: `S4 Siap Jurnal`

## Migration

File migration idempotent:

- `docs/database/ics_lpb_status_lpb_20260716.sql`

Migration juga mengisi ulang status data lama berdasarkan kondisi `nomor_lpb`, `no_invoice`, dan verifikasi harga detail LPB.

Setelah pemisahan edit harga dan Accept harga, status siap jurnal memakai `tb_lpb_detail.harga_verified_at`.
