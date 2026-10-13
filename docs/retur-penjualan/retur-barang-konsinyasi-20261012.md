# Retur Barang Konsinyasi

## Tujuan

Menambahkan tipe **Retur Barang Konsinyasi** pada alur SPR. Barang yang masih berstatus titipan di kios dikembalikan ke Gudang Konsinyasi.

## Alur

1. SC membuat SPR dan memilih tipe **Retur Barang Konsinyasi**.
2. SPR mengikuti alur persetujuan yang sudah berlaku.
3. ADMLPB membuat retur dari SPR yang telah disetujui.
4. Gudang tujuan otomatis dikunci ke **Gdg. Konsiyasi** dan tidak dapat diganti.
5. Saat retur dibuat, sistem mengurangi `qty_net` titipan pada `tb_konsinyasi_settlement` berdasarkan nomor faktur, barang, dan lot.
6. Sistem menambah `qty_on_hand` batch terkait pada Gudang Konsinyasi.
7. Ledger mencatat `ADJOUT` dari Gudang Titipan di Kios dan `RJUAL` masuk ke Gudang Konsinyasi.

## Aturan Validasi

- Nomor faktur, kode barang, lot, dan qty wajib sesuai dengan saldo titipan di kios.
- Qty retur tidak boleh melebihi saldo `DI_KIOS` atau `PENDING`.
- Seluruh penyimpanan header, detail, saldo titipan, batch stok, dan ledger berlangsung dalam satu transaksi database.
- Retur konsinyasi tidak membentuk jurnal penjualan atau saldo refund karena faktur konsinyasi juga tidak terjurnal otomatis.

## Nomor Dokumen

Nomor retur konsinyasi menggunakan awalan `RPK/ddmmyy/NNNN`.

## Hak Akses

- Pembuatan dokumen retur dari SPR dilakukan oleh akun `admlpb`.
- Jobdesk yang diterima backend adalah `ADMLPB` atau `ADMINLOGLPB`.
- Akun lama `admlpb2` dihapus dari sumber autentikasi, sedangkan log historisnya tetap dipertahankan.
