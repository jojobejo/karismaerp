# Database Adjustment Harga LPB

Tanggal: 2026-07-22

## Tabel Baru

### `tb_lpb_price_adjustment`

Header kontrol adjustment harga LPB.

Field penting:

- `no_adjustment`
- `id_lpb_salah`
- `id_lpb_adjustment`
- `id_retur_pembelian`
- `nomor_lpb_salah`
- `nomor_lpb_adjustment`
- `tanggal_adjustment`
- `kd_supplier`
- `gudang_id`
- `status`
- `total_lpb_salah`
- `total_lpb_benar`
- `selisih_dpp`
- `selisih_ppn`
- `selisih_total`
- `id_jurnal_lpb_adjustment`
- `id_jurnal_prpp`

### `tb_lpb_price_adjustment_detail`

Audit detail per barang.

Field penting:

- `id_detail_lpb_salah`
- `id_detail_lpb_adjustment`
- `kd_barang`
- `qty`
- `no_lot_adjustment`
- `expired_date_adjustment`
- `harga_salah`
- `harga_benar`
- `dpp_salah`
- `dpp_benar`
- `ppn_salah`
- `ppn_benar`
- `total_salah`
- `total_benar`
- `kelompok_dagang`

## Tabel Existing Yang Dipakai

- `tb_lpb`
- `tb_lpb_detail`
- `tb_lpb_batch`
- `tb_lpb_log`
- `tb_retur_pembelian`
- `tb_retur_pembelian_detail`
- `tb_retur_pembelian_log`
- `tberp_stock_batch`
- `tberp_stock_ledger`
- `tbkeu_jurnal`
- `tbkeu_jurnal_detail`

## Catatan Deployment

Model `M_LpbPriceAdjustment::ensure_schema()` membuat tabel baru otomatis bila belum tersedia.

Untuk deployment produksi, jalankan SQL pada `docs/database/adjustment-harga-lpb-20260722.sql` agar perubahan database tercatat eksplisit.
