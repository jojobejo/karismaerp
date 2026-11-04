# Database - ICS Detail Record LPB Update Harga dan Qty Balance

Tanggal: 2026-07-21

## Ringkasan

Tidak ada perubahan struktur database untuk update ini.

## Tabel yang Dipakai

- `tb_lpb`
- `tb_lpb_detail`
- `tbpo_detail_po`
- `tb_lpb_batch`
- `tb_lpb_log`

## Kolom yang Dipakai

`tb_lpb_detail`:

- `id_detail_lpb`
- `id_lpb`
- `kd_barang`
- `qty_diterima`
- `harga_satuan`
- `total_harga`
- `harga_satuan_sebelumnya`
- `total_harga_sebelumnya`
- `harga_update_by`
- `harga_update_at`
- `harga_verified_by`
- `harga_verified_at`

`tb_lpb`:

- `id_lpb`
- `no_po`
- `kd_po`
- `no_invoice`
- `status_lpb`

`tbpo_detail_po`:

- `no_po`
- `kd_po`
- `kd_barang`
- `qty`
- `qty_kecil`

## Validasi Data

Qty LPB baru dibandingkan dengan total LPB/qty diterima pada detail LPB untuk kode barang yang sama.

Formula validasi:

`total_setelah_update = total_qty_lpb_lain_kode_barang + qty_lpb_baru`

`total_lpb_kode_barang = SUM(tb_lpb_detail.qty_diterima)` berdasarkan `id_lpb + kd_barang`.

Jika `qty_lpb_baru` atau `total_setelah_update` lebih besar dari `total_lpb_kode_barang`, update ditolak.

Jika `total_setelah_update` berbeda dari `total_lpb_kode_barang`, update tetap disimpan tetapi user menerima notifikasi bahwa Qty LPB belum balance.

## SQL Migration

Tidak ada SQL migration baru.
