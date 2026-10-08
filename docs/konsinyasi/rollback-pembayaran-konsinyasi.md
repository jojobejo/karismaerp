# Rollback Pembayaran Konsinyasi

## Tujuan

Menjaga kuantitas titipan di kios tetap konsisten ketika pembayaran customer untuk barang konsinyasi di-unpost dan dihapus.

## Pembayaran Parsial

Saat kios membayar sebagian kuantitas, settlement asal dipecah menjadi:

1. Baris kuantitas yang dibayar dengan status `LAKU` atau `BILLED`.
2. Baris sisa kuantitas dengan status `DI_KIOS`.

Ketika pembayaran di-unpost, sistem menjumlahkan kembali kuantitas yang dibayar ke baris sisa, memperbarui subtotal, lalu menghapus baris pecahan pembayaran. Dengan demikian modal rincian kios kembali menampilkan satu baris dengan kuantitas semula.

## Pembayaran Penuh

Pembayaran penuh tidak memiliki baris sisa. Saat di-unpost, settlement yang sama dikembalikan ke status `DI_KIOS`, hubungan pembayaran dilepas, dan informasi penyelesaian dibersihkan.

## Penghapusan Pembayaran

Penghapusan dilakukan setelah pembayaran berstatus draft. Karena pemulihan settlement telah diselesaikan pada proses unpost, penghapusan pembayaran tidak membuat pecahan settlement baru maupun mengubah kuantitas titipan kembali.
