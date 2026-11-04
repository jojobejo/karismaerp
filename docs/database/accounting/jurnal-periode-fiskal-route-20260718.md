# Database - Periode Fiskal di Route Jurnal

Tanggal: 2026-07-18

## Status Database

Tidak ada perubahan struktur database.

Perubahan ini hanya memindahkan lokasi penggunaan dan endpoint Periode Fiskal dari route `accounting` ke route `jurnal`.

## Tabel Yang Dipakai

- `tbkeu_periode_fiskal`
- `tbkeu_periode_fiskal_log`

## Operasi Data

Operasi tetap memakai service existing:

- `Accounting_service::save_fiscal_period()`
- `Accounting_service::change_fiscal_period_status()`
- `Accounting_service::fiscal_period_rows()`

## Catatan Implementasi

- Periode fiskal tetap wajib `OPEN` agar jurnal otomatis dapat diposting pada tanggal transaksi terkait.
- Periode `CLOSED` harus dibuka kembali melalui proses `REOPEN` dengan alasan approval.
- Tidak ada migration SQL baru yang perlu dijalankan untuk perubahan route dan tampilan ini.
