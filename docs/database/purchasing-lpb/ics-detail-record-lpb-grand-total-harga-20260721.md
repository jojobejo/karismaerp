# Database - ICS Detail Record LPB DPP dan Grand Total Harga

Tanggal: 2026-07-21

## Scope Database

Perubahan ini tidak menambah tabel, tidak menambah kolom, dan tidak membutuhkan migration SQL.

## Tabel yang Dipakai

- `tb_lpb`
- `tb_lpb_detail`
- `tbpo_detail_po`
- `tbpo_po`

## Kontrak Data

- DPP per baris dihitung dari `tb_lpb_detail.qty_diterima x harga satuan kecil exclude`.
- Harga satuan kecil exclude diprioritaskan dari:
  1. `tbpo_detail_po.harga_satuan_kecil_exclude`
  2. `tbpo_detail_po.harga_satuan_exclude`
  3. `tb_lpb_detail.harga_satuan`
- Total harga tampilan per baris dihitung dari:
  - `tbpo_detail_po.harga_satuan_kecil` untuk mode `include`
  - `DPP + (tbpo_po.tax / 100 x DPP)` untuk mode `exclude` yang memiliki tax
  - fallback ke `DPP`
- Total DPP adalah penjumlahan seluruh nilai `dpp` pada LPB yang sedang tampil.
- Grand Total Harga adalah penjumlahan seluruh nilai `total_harga_display` pada LPB yang sedang tampil.
- Tidak ada perubahan persistensi status LPB, harga satuan, total harga, atau metadata verifikasi harga.

## Migration

Tidak ada migration SQL baru.
