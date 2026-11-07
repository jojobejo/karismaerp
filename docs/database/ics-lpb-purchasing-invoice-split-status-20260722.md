# Database - ICS LPB Purchasing Invoice Split Status

Tanggal: 2026-07-22

## Status Database

Tidak ada perubahan struktur database baru pada development ini.
Update tampilan 2026-07-22 untuk menyembunyikan status pada `List Invoice LPB` juga tidak membutuhkan migrasi database.

## Tabel Terkait

- `tb_lpb`
- `tb_lpb_detail`
- `tb_lpb_log`
- `tbpo_po`
- `tbpo_suplier`

## Kontrak Data

- `tb_lpb.status_lpb` tetap memakai kontrak existing:
  - `0` = `UNPOST`
  - `1` = `POST`
- LPB final dari route `ics/detail_po` disimpan dengan `status_lpb = 1`.
- Update invoice dan faktur pajak hanya mengubah field invoice/faktur terkait, bukan status LPB.
- Pecah invoice membuat header/detail LPB tambahan sesuai alokasi invoice, tetapi status LPB hasil pecah mengikuti status LPB asal.
- Metadata verifikasi harga pada `tb_lpb_detail` tetap dipertahankan saat pecah invoice.

## Migrasi

Tidak diperlukan SQL migration.
