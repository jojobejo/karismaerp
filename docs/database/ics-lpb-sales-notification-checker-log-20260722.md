# Database - Notifikasi Penjualan LPB dan Log Checker

Tanggal: 2026-07-22

## Tabel Terdampak

### `tb_lpb`

Kolom baru:

- `checker_name varchar(100) null`
- `checker_by varchar(50) null`
- `checker_at datetime null`

Kolom ini menyimpan identitas checker saat LPB dibuat dari draft temporary penerimaan.

### `tb_lpb_log`

Kolom baru:

- `checker_name varchar(100) null`
- `checker_by varchar(50) null`

Kolom ini menyimpan checker yang terkait dengan aktivitas LPB.

## Query Deteksi

Notifikasi penjualan tidak memakai tabel baru. Sistem membaca relasi:

- `tb_lpb_detail`;
- `tb_lpb_batch` jika tersedia;
- `tbso_faktur_detail`.

Kunci pencocokan:

- `kd_barang`;
- `no_lot`;
- `expired_date`.

Notifikasi jurnal membaca:

- `tbkeu_jurnal.source_module = LOGISTIK`;
- `tbkeu_jurnal.source_type = LPB_FINAL`;
- `tbkeu_jurnal.posting_event = GOODS_RECEIPT`;
- `tbkeu_jurnal.status = POSTED`;
- `tbkeu_jurnal.reversed_at is null` bila kolom tersedia.

## File SQL

Migration idempotent tersedia di:

- `docs/database/ics-lpb-sales-notification-checker-log-20260722.sql`

