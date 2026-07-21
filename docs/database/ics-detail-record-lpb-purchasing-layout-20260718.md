# Database Detail Record LPB Purchasing Layout

Tanggal: 2026-07-18

## Route

`ics/detail_record_lpb`

## Perubahan Struktur

Tidak ada perubahan struktur database.

## Dampak Data

- Tidak ada tabel baru.
- Tidak ada kolom baru.
- Tidak ada migrasi SQL.
- Perubahan hanya berada pada aturan konteks akses Purchasing di controller dan tampilan halaman detail record LPB.

## Tabel Existing Yang Tetap Dipakai

- `tb_lpb`
- `tb_lpb_detail`
- `tbpo_po`
- `tbpo_detail_po`
- `tbpo_suplier`

Data LPB tetap dibaca melalui endpoint dan model existing yang sudah dipakai route `ics/detail_record_lpb`.
