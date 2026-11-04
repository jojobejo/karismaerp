# Development - Navigasi Retur dari ICS ICSPo

Tanggal: 2026-07-24

## Scope

Route:

- `ics/icspo`
- `ics/retur`

File aplikasi:

- `application/views/content/logistik/ics/icspo.php`
- `application/views/content/logistik/ics/dashretur.php`

## Perubahan

- Menambahkan tombol `Retur` pada route `ics/icspo` tepat setelah tombol `Input LPB Manual`.
- Tombol `Retur` mengarah ke dashboard module retur: `ics/retur`.
- Menghapus tombol retur duplikat pada baris tombol lama di `ics/icspo` agar user melihat satu pintu masuk yang jelas.
- Menambahkan toolbar pada dashboard retur berisi:
  - `Kembali Dashboard` ke route `dashboard`.
  - `Kembali Data LPB` ke route `ics/icspo`.
  - `Input Retur` ke route `ics/retur/pembelian`.

## Cara Penggunaan

1. Buka route `ics/icspo`.
2. Klik tombol `Retur` setelah `Input LPB Manual`.
3. Sistem membuka dashboard retur pada route `ics/retur`.
4. Pada dashboard retur:
   - Klik `Kembali Dashboard` untuk kembali ke dashboard utama.
   - Klik `Kembali Data LPB` untuk kembali ke route `ics/icspo`.
   - Klik `Input Retur` untuk membuka form input retur pembelian dari LPB final.

## Validasi

- Lint PHP:

`C:\xampp\php\php.exe -l application/views/content/logistik/ics/icspo.php`

`C:\xampp\php\php.exe -l application/views/content/logistik/ics/dashretur.php`

