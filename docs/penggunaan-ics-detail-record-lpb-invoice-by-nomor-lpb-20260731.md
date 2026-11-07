# Penggunaan - ICS Detail Record LPB Invoice by Nomor LPB

Tanggal: 2026-07-31

## Route

- `ics/detail_record_lpb?kd_po=...`

## Cara Pakai Purchasing

1. Buka `ics/icspo`.
2. Masuk ke detail LPB dari PO yang dipilih.
3. Dari `ics/detail_po`, klik nomor LPB pada bagian list data LPB.
4. Sistem membuka `ics/detail_record_lpb` dengan scope nomor LPB yang diklik.
5. Pada card `List Invoice LPB per Nomor LPB`, pastikan yang tampil hanya nomor LPB tersebut.
6. Klik baris invoice untuk membuka detail LPB sesuai baris tersebut.

## Catatan

- Card hanya menampilkan LPB yang sudah memiliki `Nomor LPB`.
- Jika invoice belum diisi tetapi nomor LPB sudah ada, baris tetap tampil dengan invoice `-`.
- Invoice hasil pecah tetap muncul sebagai baris terpisah selama header LPB hasil pecah memiliki nomor LPB.
- Contoh: klik `72600003` akan menampilkan baris invoice untuk `72600003` saja.
