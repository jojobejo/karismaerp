# Loading Sebagian oleh Checker

Tanggal: 7 Oktober 2026

## Tujuan

Checker dapat mencatat barang yang dimuat sebagian. Contoh: SO menyiapkan 10 box, tetapi kendaraan hanya dapat memuat 9 box.

## Alur

1. Checker mengisi **Qty Dimuat** pada baris barang.
2. Untuk barang dengan isi per box, input menggunakan satuan box dan sistem mengonversinya ke satuan terkecil.
3. Checker menekan tombol centang untuk mengonfirmasi kuantitas yang dimuat.
4. Faktur dan DO hanya dapat menggunakan kuantitas aktual yang lolos checker.
5. Selisih kuantitas dicatat sebagai barang tidak terkirim dan tetap menjadi outstanding untuk proses pengiriman berikutnya.
6. Tonase dan kubikasi aktual trip dihitung berdasarkan kuantitas yang benar-benar dimuat.

## Database

Kolom `tbso_sales_order_detail.qty_checker_loaded` menyimpan kuantitas aktual dalam satuan terkecil. Script migrasi tersedia di `docs/database/partial_checker_loading_20261007.sql`.
