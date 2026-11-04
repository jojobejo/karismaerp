# Development - ICS Detail Record LPB Logistik List View

Tanggal: 2026-07-18

## Scope

Route `ics/detail_record_lpb` sekarang membedakan tampilan berdasarkan departemen:

- Departemen Logistik melihat kembali panel `Daftar LPB` di sisi kiri dan detail LPB di sisi kanan.
- Departemen Purchasing tetap memakai tampilan detail LPB full-width seperti perubahan terakhir.

## File yang Berubah

- `application/controllers/logistik/C_Ics.php`
- `application/views/content/logistik/ics/detail_record_lpb.php`

## Detail Implementasi

Controller `C_Ics::detail_record_lpb()` mengirim `lpb_record_view_mode` dari resolver departemen yang sama dengan route `ics/icspo`.

View `detail_record_lpb.php` memakai mode tersebut untuk menentukan layout:

- Mode `logistik`: menampilkan tombol kembali ke `ics/detail_po`, hero `Record Semua Data LPB`, panel `Daftar LPB`, pencarian LPB, refresh list, dan detail LPB di kolom kanan.
- Mode `purchasing`: mempertahankan tombol kembali ke `ics/icspo`, hero `Nomor PO`, dan detail LPB full-width tanpa panel daftar kiri.

Fungsi JavaScript existing tetap dipakai. Panel daftar Logistik memakai endpoint existing `ics/ajax_get_lpb_records_by_kd_po` dan detail memakai `ics/ajax_get_lpb_record_detail`.

## Tata Cara Penggunaan

1. Login sebagai user departemen Logistik.
2. Buka `ics/detail_record_lpb?kd_po=...&no_po=...&kd_suplier=...`.
3. Panel `Daftar LPB` tampil di sisi kiri.
4. Klik salah satu LPB untuk melihat detail LPB yang sudah diinput.
5. Login sebagai user departemen Purchasing pada route yang sama untuk memastikan tampilan Purchasing tetap full-width.

## Catatan QA

- Pastikan user Logistik bisa mencari LPB dari panel kiri.
- Pastikan tombol `Refresh` memuat ulang daftar LPB.
- Pastikan user Purchasing tidak melihat panel daftar kiri.
- Pastikan detail LPB, print selected, dan print semua tetap memakai endpoint existing.
