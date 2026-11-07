# Development Detail Record LPB - Accept Verifikasi Harga

Tanggal: 2026-07-16
Route: `ics/detail_record_lpb`

## Ringkasan

View `Purchasing` sekarang memisahkan edit harga dan verifikasi harga.

## Perubahan

- Kolom `Total Harga` dan `Harga Satuan` pada view `Purchasing` menampilkan harga aktif LPB.
- Kolom `Verifikasi` menampilkan tombol `Accept` untuk harga yang belum diverifikasi.
- Kolom `#` tetap berisi tombol icon uang rupiah untuk edit harga.
- Tombol edit harga hanya menyimpan harga dan mengembalikan row ke kondisi belum verifikasi.
- Tombol `Accept` yang menandai harga verified dan menghitung ulang `status_lpb`.

## Alur

1. User klik tombol `Purchasing`.
2. User edit harga melalui tombol icon uang rupiah pada kolom `#`.
3. Sistem menyimpan harga baru, tetapi belum mengubah status LPB.
4. User klik `Accept` pada kolom `Verifikasi`.
5. Sistem menyimpan verifikasi harga dan menghitung ulang status LPB.

## File Aplikasi

- `application/config/routes.php`
- `application/controllers/logistik/C_Ics.php`
- `application/models/M_Logistik.php`
- `application/views/content/logistik/ics/detail_record_lpb.php`

## Database

Migration:

- `docs/database/ics_lpb_detail_price_accept_20260716.sql`
