# Development - ICS LPB POST Status pada View Purchasing

Tanggal: 2026-07-21

## Scope

Route terkait:

- `ics/detail_po`
- `ics/icspo`
- `ics/detail_record_lpb`

## Latar Belakang

Saat ADMLPB merekam `Draft Temporary Penerimaan` dari `ics/detail_po`, data LPB final dibuat melalui `M_Logistik::create_lpb_from_tmp()` dan `tb_lpb.status_lpb` otomatis diset menjadi `1` atau `POST`.

Pada view Purchasing, status yang tampil sebelumnya hanya mengikuti progress verifikasi harga (`harga_verified_at`), sehingga LPB yang sudah POST dari proses penerimaan bisa terlihat seperti belum selesai/draft dari sisi Purchasing.

## File Aplikasi

- `application/models/M_Logistik.php`
- `application/views/content/logistik/ics/icspo.php`

## Detail Implementasi

- `M_Logistik::get_lpb_purchasing_view()` sekarang mengirim `status_lpb` dari `tb_lpb`.
- Kolom `Status` pada tabel Purchasing menampilkan badge utama:
  - `POST` jika `status_lpb = 1`
  - `UNPOST` jika `status_lpb = 0`
- Ikon Invoice, Faktur Pajak, dan Verifikasi Harga tetap tampil sebagai indikator kelengkapan administrasi.
- Filter `Belum Uang` tetap memakai status verifikasi harga, bukan status LPB.

## Validasi

- PHP lint:
  - `C:\xampp\php\php.exe -l application/models/M_Logistik.php`
  - `C:\xampp\php\php.exe -l application/views/content/logistik/ics/icspo.php`
- Query database memastikan LPB hasil finalize memiliki `status_lpb = 1`.
- `git diff --check`
