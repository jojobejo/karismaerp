# Database - ICS Detail Record LPB Multiple Invoice

Tanggal: 2026-07-21

## Status Migrasi

Tidak ada perubahan schema database baru untuk fitur pecah LPB multiple invoice.

Fitur memakai table yang sudah tersedia:

- `tb_lpb`
- `tb_lpb_detail`
- `tb_lpb_batch`
- `tb_lpb_log`

## Table Yang Dipakai

### `tb_lpb`

Dipakai untuk menyimpan header LPB per invoice.

Kolom penting:

- `id_lpb`
- `kd_po`
- `no_po`
- `nomor_lpb`
- `jenis_lpb`
- `no_invoice`
- `tanggal_invoice`
- `status_lpb`
- `nosj`
- `tgl_sj`
- `gudang_id`
- `keterangan`

Hasil split membuat beberapa row `tb_lpb` dengan `nomor_lpb` yang sama, tetapi `no_invoice` berbeda.

### `tb_lpb_detail`

Dipakai untuk menyimpan detail qty per invoice hasil split.

Kolom penting:

- `id_detail_lpb`
- `id_lpb`
- `kd_barang`
- `qty_diterima`
- `no_lot`
- `expired_date`
- `harga_satuan`
- `total_harga`
- `harga_verified_by`
- `harga_verified_at`

Total `qty_diterima` dari seluruh hasil split per detail barang harus sama dengan qty sebelum split.

### `tb_lpb_batch`

Disesuaikan mengikuti detail hasil split. Batch lama untuk detail asal diganti dengan batch baru yang qty-nya mengikuti qty invoice pertama. Detail hasil split baru mendapat batch baru.

### `tb_lpb_log`

Dipakai untuk audit aktivitas:

- `SPLIT_LPB_MULTIPLE_INVOICE`
- `CREATE_LPB_SPLIT_INVOICE`

## SQL Migrasi

Tidak ada SQL migrasi yang perlu dijalankan.

## Dampak Stok

Tidak ada perubahan total stok on hand. Fitur ini hanya memecah representasi LPB dan invoice, bukan melakukan penerimaan barang baru.

