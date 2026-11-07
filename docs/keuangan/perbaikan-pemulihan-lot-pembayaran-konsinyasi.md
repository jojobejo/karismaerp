# Perbaikan Pemulihan Lot Pembayaran Konsinyasi

## Masalah

Pada pembayaran barang konsinyasi, pengguna dapat memilih barang beserta nomor lot tertentu. Sebelumnya, alokasi HPP menjalankan FIFO berdasarkan kode barang tanpa membatasi nomor lot. Kondisi ini dapat menimpa nomor lot settlement dengan lot barang lain. Saat pembayaran di-unpost, kuantitas kemudian dipulihkan ke lot yang salah.

Contoh kasus: lot yang dibayar adalah `q1214`, tetapi FIFO global memilih lot `4455`. Setelah unpost, kuantitas kembali ke lot `4455`.

## Perbaikan

- Nomor lot fisik tetap mengikuti `id_settlement` yang dipilih pada pembayaran.
- Identitas lot settlement tidak lagi ditimpa oleh batch yang dipakai untuk perhitungan HPP.
- Alokasi HPP FIFO tetap berjalan global per kode barang berdasarkan urutan penerimaan LPB.
- Jejak batch HPP disimpan terpisah pada `tb_konsinyasi_cogs_allocation`.

Dengan perubahan ini, pembayaran lot `q1214` tetap tercatat pada settlement lot `q1214`, sehingga proses unpost memulihkan kuantitas fisik ke lot tersebut. Nilai HPP tetap dapat berasal dari batch LPB FIFO yang berbeda tanpa mengubah identitas lot fisik.

## Skenario Pengujian

1. Siapkan satu barang konsinyasi dengan minimal dua lot aktif, misalnya `4455` dan `q1214`.
2. Bayar sebagian kuantitas dari lot `q1214`.
3. Pastikan settlement berstatus `LAKU` dan alokasi HPP mencatat lot `q1214`.
4. Unpost pembayaran tersebut.
5. Pastikan kuantitas kembali ke settlement lot `q1214`, bukan ke lot `4455`.
6. Hapus draft pembayaran dan pastikan kuantitas lot tidak berubah lagi atau berpindah ke lot lain.
