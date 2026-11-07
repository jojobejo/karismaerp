# Format Qty Loading Box dan Pcs

## Masalah

Qty barang pada database disimpan dalam satuan terkecil. Jika 1 box berisi 12 pcs, kuantitas 38 pcs sebelumnya ditampilkan pada kolom Qty Dimuat sebagai hasil pembagian desimal `3,167 box`. Format tersebut sulit digunakan oleh petugas gudang dan berpotensi menimbulkan pembulatan.

## Perbaikan

Kolom Qty Dimuat untuk barang yang memiliki isi per box sekarang menggunakan dua input:

- jumlah box utuh;
- jumlah pcs tambahan.

Contoh kuantitas 38 pcs dengan isi 12 pcs per box ditampilkan sebagai `3 box + 2 pcs`. Saat dikirim ke server, kedua nilai dikonversi kembali menjadi 38 pcs. Dengan demikian, pencatatan stok tetap menggunakan satuan terkecil dan tampilan operasional tidak menggunakan pecahan box.

## Validasi

- Total `(box × isi per box) + pcs` harus lebih dari nol.
- Total tidak boleh melebihi Qty Siap.
- Sisa yang tidak termuat juga ditampilkan dalam format box dan pcs.
- Barang tanpa konfigurasi isi per box tetap memakai satu input sesuai satuan barang.
