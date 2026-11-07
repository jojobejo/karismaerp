# Database - Split Detail LPB

Tanggal: 2026-07-31

Update 2026-08-05: rules total harga diperbarui. Total qty tetap wajib sama dengan qty awal, tetapi total harga hasil split boleh berbeda dan selisihnya dicatat di `tb_lpb_log`. Detail update ada di `docs/database/ics-detail-record-lpb-split-detail-flexible-price-20260805.md`.

## Perubahan Schema

Tidak ada perubahan struktur database baru.

Fitur memakai tabel dan kolom yang sudah ada:

- `tb_lpb`
- `tb_lpb_detail`
- `tb_lpb_batch`
- `tb_lpb_log`

## Tabel Terdampak Saat Runtime

### `tb_lpb_detail`

Baris asal di-update menjadi baris pertama komposisi split:

- `qty_diterima`
- `harga_satuan_sebelumnya`
- `total_harga_sebelumnya`
- `harga_satuan`
- `total_harga`
- `harga_update_by`
- `harga_update_at`
- `harga_verified_by`
- `harga_verified_at`

Baris kedua dan seterusnya di-insert sebagai data hasil split dengan `id_lpb` yang sama.

### `tb_lpb_batch`

Jika tabel tersedia:

- batch baris asal di-update mengikuti qty baris pertama.
- batch baris baru dibuat mengikuti qty masing-masing baris split.

### `tb_lpb_log`

Log baru dibuat dengan:

- `action_type`: `SPLIT_LPB_DETAIL`
- `data_before`: detail awal
- `data_after`: daftar baris hasil split
- `keterangan`: ringkasan qty, harga, total awal, jumlah baris split, total qty input, total harga input, selisih total harga, dan catatan user jika diisi

## Rumus

Misal:

- `qty_awal = 1000`
- `harga_awal = 106560`
- `total_awal = 106560000`
User membuat 2 baris:

- Baris `Data Sekarang`: `100 x 106560`
- Baris `Split 1`: `900 x 510`

Maka:

- `total_baris_1 = 100 * 106560 = 10656000`
- `total_baris_2 = 900 * 510 = 459000`
- `total_qty_input = 100 + 900 = 1000`
- `total_harga_input = 10656000 + 459000 = 11115000`

Rules balance:

- `total_qty_input` harus sama dengan `qty_awal`
- `total_harga_input` boleh berbeda dari `total_awal`
- `total_harga_selisih` dicatat di `tb_lpb_log.data_after`
