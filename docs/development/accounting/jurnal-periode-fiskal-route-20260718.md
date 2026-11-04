# Development - Periode Fiskal di Route Jurnal

Tanggal: 2026-07-18

## Tujuan

Memindahkan pengelolaan Periode Fiskal dari halaman `accounting` ke route `jurnal`, supaya menu Jurnal menjadi pusat kerja Finance untuk Chart of Accounts, master pendukung, laporan, dan kontrol periode posting.

## Scope Perubahan

- Route baru:
  - `jurnal/period-store`
  - `jurnal/period-action`
  - `keuangan/jurnal/period-store`
  - `keuangan/jurnal/period-action`
- Route lama yang dihapus dari prefix `accounting`:
  - `accounting/period-store`
  - `accounting/period-action`
  - `keuangan/accounting/period-store`
  - `keuangan/accounting/period-action`
- Halaman `jurnal` menampilkan dua kolom di bawah `Daftar Jurnal Penjualan`:
  - Kolom kiri: Klasifikasi, Saldo Normal, Tipe Kontrol, Parent/Subclass, Neraca, Laba Rugi.
  - Kolom kanan: Periode Fiskal.
- Halaman `accounting` tidak lagi menampilkan panel Periode Fiskal.

## File Terdampak

- `application/config/routes.php`
- `application/controllers/keuangan/C_Keuangan.php`
- `application/controllers/keuangan/C_Accounting.php`
- `application/views/content/keuangan/jurnal.php`
- `application/views/content/keuangan/ajax/ajax_jurnal.php`
- `application/views/content/keuangan/accounting_runtime_test.php`

## Alur Baru

1. User membuka route `jurnal`.
2. Sistem memuat data periode fiskal dari `Accounting_service::fiscal_period_rows()`.
3. User membuat periode lewat form `Periode Fiskal`.
4. AJAX mengirim request ke `jurnal/period-store`.
5. User dapat melakukan `Close` atau `Reopen` melalui `jurnal/period-action`.
6. Setelah aksi berhasil, halaman reload agar status periode terbaru langsung terlihat.

## Validasi

Syntax check:

- `php -l application/config/routes.php`
- `php -l application/controllers/keuangan/C_Keuangan.php`
- `php -l application/controllers/keuangan/C_Accounting.php`
- `php -l application/views/content/keuangan/jurnal.php`
- `php -l application/views/content/keuangan/ajax/ajax_jurnal.php`
- `php -l application/views/content/keuangan/accounting_runtime_test.php`

Hasil: semua file lolos syntax check.
