# Penggunaan - Notifikasi Penjualan LPB dan Log Checker

Tanggal: 2026-07-22

## Cara Membaca List Purchasing

Pada `ics/icspo`, buka panel Purchasing. Kolom `Notif` memiliki dua ikon:

- ikon kasir: menandakan apakah barang LPB sudah masuk faktur penjualan;
- ikon neraca: menandakan apakah LPB sudah memiliki jurnal pembelian POSTED.

Jika ikon kasir berwarna kuning, LPB sudah punya transaksi penjualan. Jika ikon neraca berwarna merah, jurnal pembelian LPB sudah POSTED aktif.

## Saat Update Harga LPB

Jika LPB sudah terjual atau jurnal LPB sudah POSTED, sistem menolak update harga langsung. Purchasing perlu koordinasi dengan Accounting untuk koreksi jurnal terlebih dahulu.

## Log Aktivitas

Pada detail LPB, buka tombol log aktivitas. Tabel log sekarang menampilkan:

- waktu;
- user yang melakukan aksi;
- checker LPB;
- aktivitas;
- status;
- keterangan.

