# Perbaikan Harga Pokok Kartu Stok Konsinyasi

## Masalah

Mutasi faktur penjualan dan `TITIP-IN` pada laporan Kartu Stok Gudang mengambil `hrg_satuan`, yaitu harga jual pada faktur. Akibatnya kolom Harga Pokok menampilkan harga penjualan.

## Perbaikan

Sumber harga kedua mutasi tersebut diubah menjadi `tbso_faktur_detail.hrg_pokok`. Nilai ini merupakan snapshot moving average harga pembelian tanpa PPN saat faktur dibuat.

Perubahan hanya memengaruhi penyajian nilai pada laporan. Kuantitas stok, transaksi faktur, dan jurnal tidak diubah.

## Validasi QROUN011

- Harga jual faktur: Rp80.000,00.
- Harga pokok moving average: Rp58.558,56.
- Harga yang ditampilkan pada mutasi `TITIP-IN`: Rp58.558,56.
