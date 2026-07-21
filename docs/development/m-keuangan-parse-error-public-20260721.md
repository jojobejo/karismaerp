# Development - Perbaikan Parse Error M_Keuangan

Tanggal: 2026-07-21

## Tujuan

Memperbaiki error PHP:

`ParseError: syntax error, unexpected 'public' (T_PUBLIC)`

pada file `application/models/M_Keuangan.php` line 1187.

## Akar Masalah

Method `accounting_sales_journal_detail()` berhenti setelah pengecekan schema dan belum memiliki body serta penutup method. Akibatnya deklarasi method berikutnya, yaitu `accounting_purchase_journal_detail()`, terbaca berada di dalam method sebelumnya sehingga PHP menolak token `public`.

## Scope Aplikasi

File yang diubah:

- `application/models/M_Keuangan.php`

## Perubahan

Body `accounting_sales_journal_detail()` dikembalikan dengan pola query detail jurnal penjualan yang sudah dipakai pada `application/models/M_Journal.php`.

Data yang diambil meliputi:

- header jurnal dari `tbkeu_jurnal`;
- informasi faktur dari `tbso_faktur_penjualan`;
- nama pembuat dari `tb_karyawan`, `tb_user`, atau data pembayaran faktur;
- detail akun dari `tbkeu_jurnal_detail`, `tbkeu_akun`, dan `tbkeu_akun_karismaerp_ref`.

## Cara Validasi

Jalankan syntax check:

```powershell
& C:\xampp\php\php.exe -l application\models\M_Keuangan.php
```

Hasil yang diharapkan:

`No syntax errors detected in application\models\M_Keuangan.php`

## Catatan Penggunaan

Tidak ada perubahan cara penggunaan module. Perbaikan ini hanya mengembalikan model agar dapat diload oleh CodeIgniter tanpa parse error.
