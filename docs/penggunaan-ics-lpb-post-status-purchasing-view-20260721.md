# Penggunaan - ICS LPB POST Status pada View Purchasing

Tanggal: 2026-07-21

## Route

- `ics/detail_po`
- `ics/icspo`
- `ics/detail_record_lpb`

## Alur

1. ADMLPB membuka `ics/detail_po`.
2. ADMLPB mengisi dan merekam `Draft Temporary Penerimaan`.
3. Sistem membuat LPB final dan status LPB otomatis menjadi `POST`.
4. Purchasing membuka panel Purchasing.
5. Kolom `Status` menampilkan badge `POST` atau `UNPOST` berdasarkan status LPB asli.

## Catatan

Ikon uang pada kolom status tetap menunjukkan apakah harga detail sudah diverifikasi Purchasing. Ikon tersebut tidak mengubah status LPB utama.
