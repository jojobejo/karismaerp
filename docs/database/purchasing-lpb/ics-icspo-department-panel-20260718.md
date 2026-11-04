# Database - ICS ICSPo Department Panel

Tanggal: 2026-07-18

## Kesimpulan

Tidak ada perubahan struktur database.

## Alasan

Perubahan ini hanya mengatur pemilihan dataset dan tampilan panel pada route `ics/icspo` berdasarkan session login:

- Data Logistik tetap berasal dari query LPB/PO yang sudah ada.
- Data Purchasing tetap berasal dari `M_Logistik::get_lpb_purchasing_view()`.
- Tidak ada tabel baru.
- Tidak ada kolom baru.
- Tidak ada perubahan indeks.
- Tidak ada migrasi SQL yang perlu dijalankan.

## Dampak Data

Tidak ada transformasi data existing. Sistem hanya mencegah departemen Purchasing melihat panel Logistik dan mencegah departemen Logistik melihat panel Purchasing pada tampilan `ics/icspo`.
