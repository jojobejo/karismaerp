# HPP Jurnal Penjualan Moving Average

## Ketentuan

Produk dengan konfigurasi `hpp_average = T` menggunakan moving average per barang dan per gudang yang sama dengan perhitungan Kartu Stok Gudang. Harga LPB terakhir tidak digunakan sebagai HPP jurnal untuk produk tersebut. Produk yang tidak mengaktifkan metode average tetap memakai HPP snapshot sesuai metode produk yang tersimpan pada detail transaksi.

Rumus dasar transaksi keluar:

`nilai HPP = qty faktur × HPP moving average gudang`

HPP moving average dihitung dari saldo kuantitas dan saldo nilai transaksi masuk. Transaksi keluar mengurangi saldo nilai menggunakan average yang sedang berjalan.

## Alur Data

1. Daftar stok yang digunakan saat membuat SO mengisi HPP dari `M_PenyesuaianBarang::get_item_hpp()` berdasarkan barang dan gudang.
2. Detail SO dan faktur menyimpan HPP tersebut sebagai snapshot dokumen.
3. Saat posting jurnal, `Accounting_source_service::post_sales_invoice()` menghitung ulang HPP moving average berdasarkan gudang faktur.
4. Hasil perhitungan didebit ke akun Harga Pokok Penjualan dan dikredit ke akun Persediaan sesuai konfigurasi produk.

Penghitungan ulang pada saat posting mencegah jurnal menggunakan HPP LIFO lama yang mungkin sudah tersimpan pada SO atau faktur.

## Data Lama

Jurnal yang sudah berstatus `POSTED` tidak diubah otomatis. Koreksi harus dilakukan melalui proses unpost dan repost agar audit trail serta pasangan jurnal penjualan tetap terjaga.
