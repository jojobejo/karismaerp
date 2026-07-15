# Struktur Database Accounting Produksi

Tanggal update: 2026-07-14

## File SQL

- `docs/database/accounting_general_ledger_journal_20260713.sql`
- `docs/database/accounting_runtime_full_20260713.sql`

## Tabel Baru / Diperluas

### `tbkeu_periode_fiskal_log`

Mencatat approval workflow periode fiskal.

Kolom utama:

- `id_log`
- `id_periode`
- `action`: `OPEN`, `CLOSE`, `REOPEN`
- `reason`
- `approval_by`
- `approval_at`
- `created_at`

### `tbkeu_posting_exception`

Tambahan kolom:

- `retry_count`
- `last_retry_at`

Kolom ini dipakai dashboard exception untuk retry audit trail.

### `tbkeu_pembayaran`

Header pembayaran AR/AP.

Kolom utama:

- `payment_type`: `CUSTOMER_PAYMENT` atau `SUPPLIER_PAYMENT`
- `nomor_pembayaran`
- `tanggal_pembayaran`
- `source_module`, `source_type`, `source_id`, `source_no`
- `id_customer`, `id_supplier`
- `amount`
- `allocated_amount`
- `unapplied_amount`
- `status`: `DRAFT`, `POSTED`, `VOID`
- `id_jurnal`

### `tbkeu_pembayaran_alokasi`

Detail alokasi pembayaran ke invoice.

Kolom utama:

- `id_pembayaran`
- `nomor_baris`
- `invoice_source_module`
- `invoice_source_type`
- `invoice_source_id`
- `invoice_no`
- `amount_allocated`
- `keterangan`

### `tbkeu_saldo_awal_akun`

Tambahan kolom:

- `is_migrated`
- `id_jurnal`
- `created_by`
- `updated_by`
- `updated_at`
- `migrated_by`
- `migrated_at`

Kolom ini mengunci saldo awal setelah migrasi jurnal opening balance.

## Tabel yang Tidak Disentuh

Accounting tetap tidak membaca, menulis, mengubah, atau membuat dependency ke:

- `tbpo_transaksi`
- `tbpo_transaksi_tmp`
- `tbpo_transaksi_trashbin`
- `tbpo_akun_tr`

## Catatan Integrasi

- DO confirm sales memakai source `LOGISTIK / DO_CONFIRM`.
- LPB final memakai source `LOGISTIK / LPB_FINAL`.
- Payment baru memakai tabel `tbkeu_pembayaran` sebagai source accounting yang benar untuk alokasi piutang/hutang.

