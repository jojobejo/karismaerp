# Rekonsiliasi Gudang LPB 2600001K

## Ringkasan

LPB Konsinyasi `2600001K` untuk barang `QROUN011`, lot `4455`, sebanyak 2.000 PCS direkonsiliasi dari Gdg. Induk (`gudang_id = 2`) ke Gdg. Konsinyasi (`gudang_id = 13`).

## Data yang Diselaraskan

- Header `tb_lpb` untuk `id_lpb = 3` dipindahkan ke gudang 13.
- Ledger penerimaan `tberp_stock_ledger` untuk transaksi IN, lot `4455`, referensi `SKPO081026PUPUK030002` dipindahkan ke gudang 13.
- `tberp_stock_batch` tidak diubah karena lot `4455` sudah berada di gudang 13.
- Aktivitas perubahan dicatat pada `tb_lpb_log` dengan tipe `REKONSILIASI_GUDANG`.

## Hasil

Kartu Stok Gdg. Konsinyasi dapat membaca penerimaan berikut:

- `PJ 2600001K`: 2.000 PCS dengan harga Rp72.072,0721 per PCS.
- `PJ 2600002K`: 6.000 PCS dengan harga Rp54.054,0541 per PCS.

Kedua penerimaan tersebut menjadi dasar perhitungan HPP rata-rata tertimbang barang konsinyasi.
