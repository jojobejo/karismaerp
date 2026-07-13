# Accounting Module

Status: tahap awal implementasi Chart of Accounts.

## Modul Tersedia

- Route `jurnal`
- Alias route `keuangan/jurnal`
- Controller `keuangan/C_Keuangan`
- Model `M_Keuangan`
- View `content/keuangan/jurnal`

## Scope Tahap Ini

Tahap ini hanya mencakup master akun jurnal:

- klasifikasi akun;
- akun HEADER;
- akun POSTING;
- hierarki parent-child;
- status aktif/nonaktif;
- guard akun yang sudah dipakai jurnal tidak dihapus.

Posting jurnal, periode fiskal, mapping akun, laporan, reversal, auto-posting, dan exception dashboard belum diaktifkan pada tahap ini.

## Tabel Luar Scope

`tbpo_transaksi`, `tbpo_transaksi_tmp`, `tbpo_transaksi_trashbin`, dan `tbpo_akun_tr` merupakan domain aplikasi Purchase Order tersendiri.

Modul accounting KARISMA ERP tidak boleh membaca, menulis, mengubah, memigrasikan, atau membuat dependency terhadap tabel tersebut.
