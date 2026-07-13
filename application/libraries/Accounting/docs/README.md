# Accounting Module

Status: Chart of Accounts dan runtime transaksi accounting sudah tersedia untuk manual testing.

## Modul Tersedia

- Route `jurnal`
- Alias route `keuangan/jurnal`
- Route testing runtime `accounting-test`
- Controller `keuangan/C_Keuangan`
- Controller `keuangan/C_Accounting`
- Model `M_Keuangan`
- Library `Accounting_service`
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
- mapping akun melalui `tbkeu_mapping_akun`;
- auto-posting service untuk sales, purchase, LPB, payment, retur, mutasi, dan stock adjustment;
- exception dashboard;
- laporan berbasis jurnal `POSTED`.

## Tabel Luar Scope

`tbpo_transaksi`, `tbpo_transaksi_tmp`, `tbpo_transaksi_trashbin`, dan `tbpo_akun_tr` merupakan domain aplikasi Purchase Order tersendiri.

Modul accounting KARISMA ERP tidak boleh membaca, menulis, mengubah, memigrasikan, atau membuat dependency terhadap tabel tersebut.
