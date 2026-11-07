# UNPOST Pembayaran Terakhir

Tanggal: 7 Oktober 2026

## Perubahan

- Halaman histori pembayaran menampilkan status pembayaran, nomor jurnal, dan tombol `UNPOST`.
- Tombol hanya tersedia untuk pembayaran `POSTED` paling terakhir.
- Validasi pembayaran terakhir juga dilakukan di server agar pembayaran lama tidak dapat di-UNPOST melalui request langsung.
- UNPOST mengubah pembayaran dan jurnal penerimaan terkait menjadi `DRAFT`, mencatat pengguna, waktu, serta alasan UNPOST.
- Untuk transaksi konsinyasi, jurnal realisasi penjualan yang diterbitkan oleh pembayaran tersebut ikut dibatalkan.
- Saldo pembayaran, sisa piutang, dan plafon customer dihitung/dikembalikan sesuai transaksi yang dibatalkan.
- Halaman detail customer tetap menampilkan faktur lunas yang mempunyai histori pembayaran sehingga jurnal pembayaran masih dapat diperiksa.

## Aturan Penggunaan

Jika terdapat beberapa pembayaran, lakukan UNPOST berurutan mulai dari pembayaran `POSTED` paling terakhir.
