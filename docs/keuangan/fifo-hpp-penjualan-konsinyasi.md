# FIFO HPP Penjualan Konsinyasi

## Aturan

HPP realisasi penjualan konsinyasi dihitung menggunakan FIFO per kode barang berdasarkan urutan penerimaan LPB. Aturan ini hanya berlaku untuk faktur yang nomor fakturnya berawalan `K`. Awalan `T` merupakan format lama dan tidak lagi diperlakukan sebagai faktur konsinyasi.

Harga HPP tidak mengikuti lot yang dipilih pada formulir pembayaran kios.

Pilihan barang pada formulir pembayaran menentukan settlement fisik, kuantitas yang dibeli kios, dan lot yang harus dipulihkan ketika pembayaran di-unpost. Sumber biaya HPP dicatat secara terpisah sebagai alokasi batch LPB.

## Contoh

Jika batch pertama berisi 10 pcs dengan harga Rp10.000 dan batch kedua berisi 10 pcs dengan harga Rp15.000:

- Penjualan 10 pcs menghasilkan HPP Rp100.000 dari batch pertama.
- Penjualan 12 pcs menghasilkan HPP Rp130.000: 10 pcs dari batch pertama dan 2 pcs dari batch kedua.
- Jika batch pertama tersisa 2 pcs, penjualan 10 pcs menghasilkan HPP Rp140.000: 2 pcs dari batch pertama dan 8 pcs dari batch kedua.

## Struktur Data

- `tb_konsinyasi_settlement`: identitas barang dan lot fisik yang berada atau laku di kios.
- `tb_konsinyasi_lpb_batch_stock`: saldo kuantitas dan harga pokok setiap batch LPB.
- `tb_konsinyasi_cogs_allocation`: jejak batch FIFO yang membentuk HPP setiap pembayaran.
- Jurnal dengan event `GOODS_ISSUE`: debit HPP dan kredit persediaan sebesar total hasil alokasi FIFO.

## Unpost

Saat pembayaran di-unpost, kuantitas settlement dikembalikan ke lot fisik yang semula dipilih. Secara terpisah, setiap alokasi HPP dikembalikan ke batch LPB asalnya. Dengan pemisahan ini, pemulihan lot dan pembalikan FIFO sama-sama akurat.
