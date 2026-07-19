# Development Detail Record LPB - UNPOST, Update Data, dan Log

Tanggal: 2026-07-19
Route: `ics/detail_record_lpb`

## Ringkasan

Alur LPB diubah menjadi status operasional sederhana:

- `1`: `POST`
- `0`: `UNPOST`

Saat user Logistik merekam Draft Temporary Penerimaan, LPB otomatis dibuat dengan status `POST` dan aktivitasnya dicatat pada `tb_lpb_log`.

## Perubahan UI

- Tombol `UNPOST` tampil di bawah tabel detail LPB saat status LPB `POST`.
- Saat LPB berstatus `UNPOST`, tombol bawah tabel berubah menjadi:
  - `Rekam`
  - `Update Nomor & Jenis LPB`
  - `Update SJ`
- Header tabel detail LPB menambahkan kolom `#`.
- Kolom `#` berisi tombol pensil kuning untuk edit `No Lot`, `Expired Date`, dan `Qty Diterima`.
- Panel `Log Aktivitas LPB` ditampilkan berdasarkan LPB yang dipilih.

## Endpoint Baru

- `ics/ajax_unpost_lpb`
- `ics/ajax_post_lpb`
- `ics/ajax_update_lpb_identity`
- `ics/ajax_update_lpb_sj`
- `ics/ajax_update_lpb_detail_receipt`

## File Aplikasi

- `application/controllers/logistik/C_Ics.php`
- `application/models/M_Logistik.php`
- `application/views/content/logistik/ics/detail_record_lpb.php`
- `application/config/routes.php`

## Database

Migration:

- `docs/database/ics-detail-record-lpb-unpost-update-log-20260719.sql`

