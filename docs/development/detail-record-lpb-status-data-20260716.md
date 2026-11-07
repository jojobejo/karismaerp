# Development Detail Record LPB - Status Data LPB

Tanggal: 2026-07-16
Route: `ics/detail_record_lpb`

## Ringkasan

Data LPB sekarang memiliki status operasional `status_lpb`.

LPB yang dibuat dari draft temporary penerimaan selalu dimulai dari `status_lpb = 1`.

## Definisi Status

- `1`: `S1 Draft`
- `2`: `S2 Nomor Ada`
- `3`: `S3 Invoice Ada`
- `4`: `S4 Siap Jurnal`

Catatan: istilah nomor pada status memakai `nomor_lpb`, karena `no_po` purchase order sudah melekat saat LPB dibuat dari draft.

## Trigger Update Status

Status dihitung ulang pada proses:

- finalisasi draft temporary penerimaan, sebagai status awal `1`
- update jenis/nomor LPB
- update invoice
- Accept/verifikasi harga detail LPB dari view Purchasing

Edit harga detail LPB tidak menaikkan status. Harga baru harus di-`Accept` terlebih dahulu.

## File Aplikasi

- `application/models/M_Logistik.php`
- `application/views/content/logistik/ics/detail_record_lpb.php`

## Database

Migration:

- `docs/database/ics_lpb_status_lpb_20260716.sql`
