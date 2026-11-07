# Database Tabel Retur Pembelian LPB Final

Tanggal: 2026-07-22

## Tabel Baru

### `tb_retur_pembelian`

Header dokumen retur pembelian.

Field penting:

- `no_retur_pembelian`
- `id_lpb`
- `kd_po`
- `no_po`
- `kd_supplier`
- `tanggal_retur`
- `gudang_id`
- `status`
- `jenis_penyelesaian`
- `total_dpp`
- `total_ppn`
- `grand_total`
- `id_jurnal`
- `id_jurnal_reversal`
- `created_by`
- `posted_by`
- `reversed_by`

### `tb_retur_pembelian_detail`

Detail item dan batch yang diretur.

Field penting:

- `id_detail_lpb`
- `kd_barang`
- `no_lot`
- `expired_date`
- `qty_retur`
- `harga_satuan`
- `dpp`
- `ppn`
- `total`
- `kelompok_dagang`
- `alasan_retur`

### `tb_retur_pembelian_log`

Audit trail action retur pembelian.

Action minimal:

- `CREATE_DRAFT`
- `UPDATE_DRAFT`
- `SUBMIT`
- `VERIFY_PURCHASING`
- `VERIFY_ACCOUNTING`
- `APPROVE`
- `POST`
- `REVERSE`
- `VOID`

Implementasi saat ini memakai `CREATE_DRAFT`, `SUBMIT`, `VERIFY_PURCHASING`, `VERIFY_ACCOUNTING`, `APPROVE`, `POST`, `REVERSE`, dan `VOID`.

## Tabel Existing Yang Dipakai

- `tb_lpb`
- `tb_lpb_detail`
- `tb_lpb_batch`
- `tbpo_po`
- `tbpo_suplier`
- `tbpo_barang`
- `tbpo_detail_po`
- `tberp_stock_batch`
- `tberp_stock_ledger`
- `tbkeu_jurnal`
- `tbkeu_jurnal_detail`
- `tbkeu_posting_exception`

## Catatan Deployment

File SQL migrasi tersedia di `docs/database/retur-pembelian-lpb-final-20260722.sql`.

Model `M_ReturPembelian::ensure_schema()` juga membuat tabel jika belum tersedia untuk menjaga environment lokal tetap bisa diuji, tetapi deployment produksi sebaiknya memakai file SQL agar perubahan database terdokumentasi dan dapat direview.
