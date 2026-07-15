# Accounting Module

Status: Chart of Accounts dan runtime transaksi accounting sudah tersedia untuk penggunaan produksi bertahap.

## Modul Tersedia

- Route `jurnal`
- Alias route `keuangan/jurnal`
- Route produksi runtime `accounting`
- Alias route `keuangan/accounting`
- Route UAT terpisah `accounting-test` (simulator tidak tersedia di route produksi)
- Controller `keuangan/C_Keuangan`
- Controller `keuangan/C_Accounting`
- Model `M_Keuangan`
- Library `Accounting_service`
- Library `Accounting_source_service`
- View `content/keuangan/jurnal`
- View `content/keuangan/accounting_runtime_test`

## Dokumen Analisis dan UAT

- `application/libraries/Accounting/docs/database-analysis.md`
- `docs/accounting/ALUR_PENGGUNAAN_ACCOUNTING.md`
- `docs/accounting/UAT_ACCOUNTING_MODULE.md`
- `docs/accounting/RUNTIME_TRANSAKSI_PENUH_20260713.md`

## Scope Tahap Ini

Master akun jurnal:

- klasifikasi akun;
- akun HEADER;
- akun POSTING;
- hierarki parent-child;
- status aktif/nonaktif;
- guard akun yang sudah dipakai jurnal tidak dihapus.

Runtime transaksi yang tersedia:

- input jurnal manual sebagai `DRAFT`;
- validasi dan posting jurnal;
- reversal jurnal `POSTED`;
- workflow periode fiskal open, close, reopen dengan approval reason;
- pembayaran customer/supplier dan alokasi piutang/hutang;
- saldo awal akun dan migrasi opening balance;
- mapping akun melalui `tbkeu_mapping_akun`;
- auto-posting service untuk sales, purchase, LPB, payment, retur, mutasi, dan stock adjustment;
- exception dashboard dengan retry, resolve, dan ignore;
- laporan berbasis jurnal `POSTED`.

## Dokumen Penggunaan Produksi

- `docs/accounting/PANDUAN_PENGGUNAAN_ACCOUNTING_PRODUKSI_20260714.md`
- `docs/accounting/STRUKTUR_DATABASE_ACCOUNTING_PRODUKSI_20260714.md`
- `docs/accounting/UAT_ACCOUNTING_PRODUKSI_20260714.md`
- `docs/accounting/PANDUAN_OPERASIONAL_JURNAL_20260715.md`
- `docs/accounting/UAT_FLOW_MODULE_DAN_DATABASE_20260715.md`
- `docs/database/accounting_hardening_20260715.sql`
- `docs/database/sales_logistics_compatibility_20260715.sql`
- `docs/database/accounting_uat_database_20260715.sql`

## Tabel Luar Scope

`tbpo_transaksi`, `tbpo_transaksi_tmp`, `tbpo_transaksi_trashbin`, dan `tbpo_akun_tr` merupakan domain aplikasi Purchase Order tersendiri.

Modul accounting KARISMA ERP tidak boleh membaca, menulis, mengubah, memigrasikan, atau membuat dependency terhadap tabel tersebut.
