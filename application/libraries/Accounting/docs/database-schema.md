# Accounting Database Schema

Tanggal: 2026-07-13

## Migration

SQL migration tahap awal berada di:

`docs/database/accounting_jurnal_accounts_20260713.sql`

## Tabel

### `tbkeu_klasifikasi_akun`

Master klasifikasi akun untuk laporan Neraca dan Laba Rugi. Tabel ini menentukan saldo normal default.

### `tbkeu_akun`

Chart of Accounts. Mendukung akun `HEADER` sebagai parent dan akun `POSTING` sebagai akun transaksi.

### `tbkeu_saldo_normal`

Master saldo normal yang menjadi sumber pilihan `Saldo Normal` pada klasifikasi dan akun.

### `tbkeu_tipe_kontrol`

Master tipe kontrol yang menjadi sumber pilihan `Tipe Kontrol` pada akun.

## Foreign Key

- `tbkeu_akun.id_klasifikasi` ke `tbkeu_klasifikasi_akun.id_klasifikasi`
- `tbkeu_akun.parent_id` ke `tbkeu_akun.id_akun`

MariaDB 10.4 tidak memakai `CHECK parent_id <> id_akun` karena `id_akun` adalah `AUTO_INCREMENT`. Aturan self-parent dijaga oleh controller dan SQL audit.

## Index Utama

- unique `tbkeu_akun.kode_akun`
- primary key `tbkeu_saldo_normal.kode_saldo`
- primary key `tbkeu_tipe_kontrol.kode_tipe_kontrol`
- index `tbkeu_akun.id_klasifikasi`
- index `tbkeu_akun.parent_id`
- index `tbkeu_akun.tipe_akun, is_active`

## Rollback

Rollback tahap awal hanya drop `tbkeu_akun` lalu `tbkeu_klasifikasi_akun`. Jangan rollback jika sudah ada tabel accounting lain yang memiliki foreign key ke `tbkeu_akun`.
