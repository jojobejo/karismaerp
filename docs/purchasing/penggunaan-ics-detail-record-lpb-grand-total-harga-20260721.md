# Penggunaan - ICS Detail Record LPB DPP dan Grand Total Harga

Tanggal: 2026-07-21

## Route

`ics/detail_record_lpb`

Contoh:

```text
http://localhost/karismaerp/ics/detail_record_lpb?kd_po=...&no_po=...&kd_suplier=...
```

## Cara Membaca Kolom Harga

Pada panel Purchasing, kolom harga tampil berurutan:

`Harga Satuan | DPP | Total Harga`

- `Harga Satuan`: harga satuan kecil exclude.
- `DPP`: qty LPB dikali harga satuan kecil exclude.
- `Total Harga`: nilai total setelah mengikuti mode PPN PO.

## Cara Membaca Ringkasan

Setelah tabel detail LPB akan tampil:

`Total DPP | Grand Total Harga`

`Total DPP` adalah penjumlahan seluruh kolom `DPP` pada LPB yang sedang dipilih.

## Catatan Role

- Purchasing dapat melihat kolom harga dan ringkasan grand total.
- Logistik tetap tidak menampilkan harga maupun ringkasan grand total harga.
