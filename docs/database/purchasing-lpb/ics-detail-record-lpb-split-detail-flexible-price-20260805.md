# Database - Split Detail LPB Flexible Harga

Tanggal: 2026-08-05

## Perubahan Schema

Tidak ada perubahan struktur database baru.

Fitur memakai tabel dan kolom runtime yang sudah ada:

- `tb_lpb`
- `tb_lpb_detail`
- `tb_lpb_batch`
- `tb_lpb_log`

## Tabel Terdampak Saat Runtime

### `tb_lpb_detail`

Baris asal tetap di-update menjadi baris pertama komposisi split:

- `qty_diterima`
- `harga_satuan_sebelumnya`
- `total_harga_sebelumnya`
- `harga_satuan`
- `total_harga`
- `harga_update_by`
- `harga_update_at`
- `harga_verified_by`
- `harga_verified_at`

Baris kedua dan seterusnya tetap di-insert sebagai detail baru dengan `id_lpb` yang sama.

### `tb_lpb_batch`

Batch baris asal dan baris split baru disesuaikan mengikuti qty masing-masing baris hasil split.

### `tb_lpb_log`

Log `SPLIT_LPB_DETAIL` menjadi sumber audit dan penanda data split.

`data_before` menyimpan snapshot data awal. `data_after` menyimpan daftar baris hasil split, `total_qty`, `total_harga_awal`, `total_harga`, `total_harga_selisih`, dan `harga_rule = FLEKSIBEL_TIDAK_WAJIB_BALANCE`.

## Rules Validasi

- `SUM(qty split) = qty awal`
- `harga_satuan >= 0`
- `SUM(total harga split)` boleh berbeda dari total awal
- Selisih harga disimpan di `tb_lpb_log`, bukan ditolak oleh validasi

## Penanda Split

Tidak ada kolom marker baru pada `tb_lpb_detail`. Penanda `Split` pada UI dihitung dari keberadaan log:

- `tb_lpb_log.action_type = SPLIT_LPB_DETAIL`
- `tb_lpb_log.data_after` berisi `id_detail_lpb` atau `source_id_detail_lpb` dari baris detail terkait

Pendekatan ini menjaga schema tetap stabil dan memakai audit trail sebagai sumber tinjauan.
