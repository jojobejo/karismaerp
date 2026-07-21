# Penggunaan - ICS Detail Record LPB Qty dan Harga Exclude

Tanggal: 2026-07-20

## Route

`ics/detail_record_lpb`

Contoh:

```text
http://localhost/karismaerp/ics/detail_record_lpb?kd_po=...&no_po=...&kd_suplier=...
```

## Membaca Detail LPB

Kolom `Qty Order` sudah tidak ditampilkan.

Urutan qty sekarang:

- `Qty In`
- `Qty Satuan`
  - `BOX`
  - `Kg/Ltr`
  - `Pcs`

Kolom `Qty In` dan `Pcs` adalah qty kecil yang benar-benar masuk ke LPB. `BOX` dihitung dari qty kecil dibagi `isi`, sedangkan `Kg/Ltr` dihitung dari qty kecil dikali `kemasan / 1000`.

## Membaca Harga

Pada panel Purchasing, harga yang tampil adalah harga exclude:

- `Harga Satuan`
- `Total Harga`

`Total Harga` diposisikan setelah `Harga Satuan` dan dihitung dari `Qty LPB x Harga Satuan` berbasis exclude.

## Update dan Rekam Harga

Jika LPB masih `UNPOST`, tombol edit harga tetap dapat digunakan dari panel Purchasing. Nilai yang diedit dan direkam mengikuti harga yang tampil pada tabel.
