# Pelacakan Asal LPB dan PO Konsinyasi

## Tujuan

Kolom Asal LPB & PO pada rincian titipan di kios menampilkan dokumen penerimaan awal barang konsinyasi, termasuk ketika barang diterima di gudang reguler sebelum dipindahkan ke gudang konsinyasi.

## Aturan Pencarian

1. Sistem mencari LPB berdasarkan kode barang, gudang transaksi, dan nomor lot.
2. Jika tidak ditemukan karena gudang penerimaan berbeda dengan gudang konsinyasi, sistem mencari kembali berdasarkan kode barang dan nomor lot.
3. Pencarian lintas gudang hanya menerima LPB dengan jenis `LPB Konsinyasi` untuk mencegah LPB reguler terhubung sebagai asal titipan.
4. Settlement lama yang belum mempunyai `id_lpb_asal` atau `nomor_lpb_asal` dilengkapi saat sinkronisasi konsinyasi dijalankan kembali.

## Dampak

Setelah sinkronisasi, nomor LPB, nomor PO, harga PO, dan tipe pajak dapat ditampilkan melalui relasi LPB asal yang sudah ditemukan.
