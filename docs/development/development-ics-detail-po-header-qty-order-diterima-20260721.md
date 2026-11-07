# Development Aplikasi - ICS Detail PO Header Qty Order dan Qty Diterima

Tanggal: 2026-07-21

## Scope

Route: `ics/detail_po`

File aplikasi:

- `application/views/content/logistik/ics/detail_po.php`

## Perubahan

Tabel `Detail Barang PO` pada route `ics/detail_po` disederhanakan pada bagian header dan kolom quantity.

Kolom grup `Qty Order` sekarang menampilkan:

- `Box`
- `Kg/Ltr`

Kolom `Pcs` dan `Ltr` pada grup `Qty Order` tidak lagi ditampilkan.

Kolom grup `Qty Diterima` sekarang menampilkan:

- `Box`
- `Kg/Ltr`

Kolom `Qty` dan `Ltr` pada grup `Qty Diterima` tidak lagi ditampilkan.

Header `Kg` diganti menjadi `Kg/Ltr` agar satu tampilan dapat mewakili barang berbasis kilogram maupun liter.

## Catatan Teknis

Perubahan dilakukan pada layer view saja.

Data `qty_order_pcs`, `qty_order_ltr`, `qty_diterima_pcs`, dan `qty_diterima_ltr` tetap dapat dikirim oleh backend untuk kebutuhan flow lain, tetapi tidak dirender pada tabel `Detail Barang PO`.

Konfigurasi DataTables disesuaikan karena jumlah kolom tampilan berubah dari 17 kolom menjadi 13 kolom.
