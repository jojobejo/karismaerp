# Database List Revisi Harga LPB

Tanggal: 2026-09-01

## Script

Script database:

- `database/sql/create_lpb_revision_request_20260901.sql`

Script bersifat rerunnable karena memakai `CREATE TABLE IF NOT EXISTS` dan `INSERT ... WHERE NOT EXISTS` untuk akun default.

## Tabel Baru

### `tb_lpb_revision_request`

Menyimpan header request revisi harga LPB.

Kolom penting:

- `no_request`: nomor request format `RLPB-YYYYMMDD-0001`.
- `id_lpb`, `nomor_lpb`, `kd_po`, `no_po`: referensi LPB asal.
- `kd_supplier`, `nama_supplier`, `gudang_id`: konteks supplier dan gudang.
- `status`: status workflow request.
- `alasan_revisi`: alasan dari Purchasing.
- `total_faktur`, `total_item`, `total_qty_terjual`: ringkasan dampak faktur penjualan.
- `requested_by`, `accounting_by`, `purchasing_by`, `completed_by`: audit pelaku.

### `tb_lpb_revision_request_detail`

Menyimpan detail faktur penjualan yang harus di-unpost per barang dalam 1 LPB.

Kolom penting:

- `id_request`: relasi ke header request.
- `id_detail_lpb`: detail LPB asal.
- `source_table`, `source_pk`: penanda sumber data modern atau legacy.
- `id_faktur`, `id_faktur_detail`, `no_faktur`: referensi faktur penjualan modern.
- `kd_barang`, `nama_barang`, `no_lot`, `expired_date`: identitas barang.
- `qty_lpb`, `qty_terjual`: angka pembanding LPB vs penjualan.
- `status`, `unpost_by`, `unpost_at`: status dan audit unpost accounting.

### `tb_lpb_revision_request_log`

Menyimpan audit aktivitas workflow.

Kolom penting:

- `action_type`: `CREATE_REQUEST`, `UNPOST_SALES_INVOICE`, `UNPOST_LPB`, `FINISH_REVISION`.
- `status_before`, `status_after`: perubahan status.
- `data_before`, `data_after`: payload JSON ringan bila dibutuhkan.
- `dilakukan_oleh`, `dilakukan_pada`: audit user dan waktu.

## Akun Default

Jika belum ada, script membuat:

- Username: `purchasing_lpb_revision`, password: `1234`, default redirect: `ics/lpb_revision`
- Username: `accounting_lpb_revision`, password: `1234`, default redirect: `ics/lpb_revision`

Password disimpan menggunakan bcrypt `password_hash()` PHP.

## Perubahan Tabel Existing

Tidak ada perubahan struktur pada tabel existing.

Tabel existing yang dibaca/dimutasi oleh workflow:

- `tb_lpb`
- `tb_lpb_detail`
- `tb_lpb_log`
- `tbso_faktur_penjualan`
- `tbso_faktur_detail`
- `tb_detail_do`
- `tbkeu_jurnal`
- `tbkeu_jurnal_detail`
- `tberp_stock_batch`
- `tberp_stock_ledger`

## Catatan Validasi

Mutasi faktur modern memakai `M_SalesOrder::repost_item_faktur()`, sehingga efek database mengikuti kontrak existing sales order/faktur. Jalur legacy `tb_detail_do` hanya mengubah status detail dari `4` ke `2` untuk melepas blocker LPB karena tidak memiliki kontrak faktur detail modern.
