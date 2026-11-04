# Database - ICS Detail Record LPB Qty dan Harga Exclude

Tanggal: 2026-07-20

## Scope Database

Perubahan ini tidak menambah tabel dan tidak menambah kolom baru.

Tabel yang dipakai:

- `tb_lpb`
- `tb_lpb_detail`
- `tbpo_detail_po`
- `tbpo_barang`
- `tb_master_barang_all`

## Kontrak Data

- `Qty LPB` berasal dari `tb_lpb_detail.qty_diterima` dan diperlakukan sebagai qty kecil.
- `Qty In` memakai qty kecil LPB langsung dari `tb_lpb_detail.qty_diterima`.
- `Qty Satuan` ditampilkan sebagai tiga subkolom:
  - `BOX`: `tb_lpb_detail.qty_diterima / isi`.
  - `Kg/Ltr`: `tb_lpb_detail.qty_diterima x (kemasan / 1000)`.
  - `Pcs`: qty kecil LPB dari `tb_lpb_detail.qty_diterima`.
- `Harga Satuan` tampilan diprioritaskan dari:
  1. `tbpo_detail_po.harga_satuan_kecil_exclude`
  2. `tbpo_detail_po.harga_satuan_exclude`
  3. `tb_lpb_detail.harga_satuan` sebagai fallback data lama
- `Total Harga` tampilan dihitung dari `tb_lpb_detail.qty_diterima x Harga Satuan`.

## Rumus Konversi

Rumus mengikuti helper aplikasi yang sama dengan `ics/detail_po`:

- `Box`: memakai `tbpo_barang.isi`, fallback ke nilai `isi` di `tbpo_detail_po`.
- `Kg`: memakai `tbpo_barang.kemasan`, fallback ke nilai `kemasan` di `tbpo_detail_po`.
- `Ltr`: memakai `tbpo_barang.kemasan`, fallback ke nilai `kemasan` di `tbpo_detail_po`.
- `Pcs`: memakai `tb_lpb_detail.qty_diterima`.

## Migration

Tidak ada migration SQL baru. Perubahan bersifat query dan tampilan aplikasi.
