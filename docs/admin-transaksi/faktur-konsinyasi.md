# Faktur Konsinyasi pada Admin Transaksi

## Tujuan

Tab Faktur Konsinyasi pada halaman Admin Transaksi menampilkan faktur realisasi konsinyasi yang terbit ketika kios melakukan pembayaran, misalnya `TINV0810260001-1`.

## Sumber Data

- Dokumen faktur: `tb_konsinyasi_faktur`.
- Faktur induk: `tbso_faktur_penjualan` melalui `id_faktur_induk` dan `no_faktur_induk`.
- Pembayaran: `tbkeu_pembayaran_faktur` melalui `id_pembayaran`.
- Jurnal penjualan dan HPP: `tbkeu_jurnal` dengan tipe sumber `FAKTUR_PENJUALAN_KONSINYASI`.

## Informasi yang Ditampilkan

Daftar menampilkan nomor faktur konsinyasi, faktur induk/SO, tanggal, customer, nominal, status, dan jurnal penjualan utama. Tombol Detail hanya menampilkan jurnal modul SALES, yaitu jurnal penjualan/piutang serta HPP/persediaan. Jurnal kas atau bank ditampilkan pada kategori Pembayaran Customer.

Keterangan jurnal penjualan, rincian jurnal, dan referensi `source_no` menggunakan nomor faktur konsinyasi (contoh `TINV0810260001-1`), bukan nomor faktur induk (`TINV0810260001`).

Faktur induk konsinyasi hanya berfungsi sebagai dokumen penitipan dan tidak menampilkan jurnal akuntansi. Jurnal bertipe `FAKTUR_PENJUALAN_KONSINYASI` hanya ditampilkan pada faktur realisasi. Ketika pembayaran konsinyasi yang sudah di-unpost dihapus, jurnal pembayaran dan jurnal realisasi konsinyasi terkait ikut dihapus agar tidak menjadi jurnal yatim pada faktur induk.

Pada halaman Jurnal Penjualan, jurnal faktur realisasi konsinyasi tetap menampilkan nomor SO dan pelanggan milik faktur induk. Relasi dilakukan melalui `tb_konsinyasi_faktur.no_faktur_induk`, sehingga perubahan nomor dokumen menjadi faktur turunan tidak memutus informasi SO dan pelanggan.

## Aksi Transaksi

- **Edit** menyelaraskan tanggal, qty, harga jual, subtotal, pembayaran, settlement, serta nominal jurnal terkait. Qty settlement yang sudah `BILLED` tidak dapat diubah sebelum penyelesaian supplier di-unpost.
- **Unpost** menjalankan rollback pembayaran konsinyasi sehingga jurnal menjadi draft, faktur konsinyasi dibatalkan, dan qty parsial dikembalikan ke sisa titipan di kios.
- **Hapus** hanya dapat dijalankan setelah unpost. Pembayaran draft, jurnal terkait, dan faktur konsinyasi kemudian dibersihkan dalam satu transaksi database.
