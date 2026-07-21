# Penggunaan - ICS Detail Record LPB DPP Nilai Lain dan PPN

Tanggal: 2026-07-21

## Route

`ics/detail_record_lpb`

Contoh:

```text
http://localhost/karismaerp/ics/detail_record_lpb?kd_po=...&no_po=...&kd_suplier=...
```

## Cara Membaca Kolom

Pada panel Purchasing, kolom harga tampil berurutan:

```text
Harga Satuan | DPP | DPP Nilai Lain | PPN | Total Harga
```

- `DPP`: qty LPB dikali harga satuan kecil exclude.
- `DPP Nilai Lain`: `DPP x 11/12`.
- `PPN`: `DPP Nilai Lain x 12%`.

## Catatan Tampilan

- Kolom `Pcs` sudah tidak tampil pada group `Qty Satuan`.
- Group `Qty Satuan` hanya menampilkan `BOX` dan `Kg/Ltr`.
- Panel Logistik tetap tidak menampilkan kolom harga.
