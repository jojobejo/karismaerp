# Development Detail PO - Harga Exclude Draft Temporary Penerimaan

Tanggal: 2026-07-16
Route: `ics/detail_po`

## Ringkasan

Saat user menyimpan draft temporary penerimaan, sistem sekarang ikut menyimpan metadata harga exclude dari data PO.

Data harga diambil berdasarkan `no_po` dan `kd_barang` dari `tbpo_detail_po`, dengan `kd_suplier` dan `kd_po` sebagai pengaman tambahan.

## Field yang Disimpan

- `tb_tmp_po_received.harga_satuan`
  - Diambil dari `tbpo_detail_po.harga_satuan_exclude`.
- `tb_tmp_po_received.harga_satuan_kecil`
  - Diambil dari `tbpo_detail_po.harga_satuan_kecil_exclude`.
- `tb_tmp_po_received.total_harga`
  - Dihitung dari `qty_diterima x harga_satuan_kecil`.

Semua harga menggunakan nilai exclude. Harga include seperti `hrg_satuan` tidak dipakai.

## Tampilan

Card draft temporary penerimaan di route `ics/detail_po` menampilkan kolom:

- `Harga Satuan`
- `Total Harga`

## File Aplikasi

- `application/controllers/logistik/C_Ics.php`
  - Menambahkan pengambilan harga exclude sebelum menyimpan draft temporary.
- `application/models/M_Logistik.php`
  - Menambahkan helper `get_po_exclude_price_by_item()`.
  - Summary draft ikut membawa `harga_satuan`, `harga_satuan_kecil`, dan `total_harga`.
- `application/views/content/logistik/ics/detail_po.php`
  - Menampilkan `Harga Satuan` dan `Total Harga` pada tabel draft.

## Database

Migration: `docs/database/ics_detail_po_tmp_received_price_columns_20260716.sql`

Kolom baru:

- `tb_tmp_po_received.harga_satuan`
- `tb_tmp_po_received.harga_satuan_kecil`
- `tb_tmp_po_received.total_harga`
