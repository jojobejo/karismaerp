# Development - ICS Detail Record LPB DPP dan Grand Total Harga

Tanggal: 2026-07-21

## Scope

Route: `ics/detail_record_lpb`

Perubahan berfokus pada sajian detail LPB dan ringkasan setelah tabel `lpbDetailTable`.

## File Aplikasi

- `application/models/M_Logistik.php`
- `application/views/content/logistik/ics/detail_record_lpb.php`

## Detail Implementasi

- Menambahkan kolom `DPP` setelah `Harga Satuan` dan sebelum `Total Harga`.
- Menambahkan container ringkasan `Total DPP` dan `Grand Total Harga` tepat setelah tabel `#lpbDetailTable`.
- `M_Logistik::get_lpb_record_detail_rows()` dan `M_Logistik::get_purchasing_lpb_detail_rows()` sekarang mengirim:
  - `dpp`
  - `total_harga_display`
- `DPP` dihitung dari qty LPB dikali harga satuan kecil exclude.
- `Total Harga` tampilan dihitung sebagai:
  - mode `include`: qty LPB dikali `tbpo_detail_po.harga_satuan_kecil`
  - mode `exclude` dengan tax PO: `DPP + tax`
  - fallback: `DPP`
- Field lama `total_harga` dan `total_harga_exclude` tetap bermakna nilai exclude agar workflow edit/verifikasi harga tidak berubah.
- Ringkasan hanya ditampilkan pada mode yang menampilkan kolom harga, yaitu panel Purchasing.
- Pada mode Logistik, ringkasan disembunyikan agar data harga tetap tidak tampil di panel Logistik.
- Saat detail dikosongkan atau data LPB kosong, nilai grand total di-reset ke `Rp 0` dan container disembunyikan.

## Dampak UI

Urutan tampilan pada panel Purchasing:

Kolom harga pada panel Purchasing:

`Harga Satuan | DPP | Total Harga`

Urutan ringkasan:

`lpbDetailTable` -> `Total DPP` -> `Grand Total Harga` -> aksi Rekam/UNPOST Purchasing

## Validasi

- PHP lint:
  - `C:\xampp\php\php.exe -l application/models/M_Logistik.php`
  - `C:\xampp\php\php.exe -l application/views/content/logistik/ics/detail_record_lpb.php`
- `git diff --check`
