# Penggunaan Retur Pembelian Potong Hutang

Tanggal: 20 Agustus 2026

## Posting Retur Pembelian

1. Buka `ics/retur/pembelian`.
2. Buat retur dari LPB final.
3. Pilih jenis penyelesaian `POTONG_HUTANG`.
4. Lanjutkan verifikasi Purchasing dan Accounting.
5. Posting retur.

Hasil posting:

- BKPS mencatat rule `RBELI-PH-BKPS`: debit `13013` dan kredit `14011`.
- BKP mencatat rule `RBELI-PH-BKP`: debit `13013`, kredit `14010`, dan kredit `13017`.

Saldo `13013` berarti retur sudah menjadi hak potong, tetapi belum memotong dokumen hutang tertentu.

## Potong Hutang Supplier Dengan Retur

1. Buka `keuangan/pembayaran-supplier`.
2. Pilih supplier.
3. Pada detail supplier, cek card `Dokumen Retur Siap Potong`.
4. Klik `Potong Hutang Retur`.
5. Pada sisi kiri, isi nominal dokumen hutang terbuka yang akan dipotong.
6. Pada sisi kanan, isi nominal retur yang dipakai.
7. Pastikan total hutang sama dengan total retur.
8. Klik `Posting Potong Hutang`.

Hasil posting:

- Debit akun hutang pada dokumen hutang terbuka.
- Kredit `13013` pada dokumen retur pembelian.
- Transaksi tercatat pada histori pembayaran supplier.

## Melihat Daftar Jurnal

1. Buka `keuangan/pembelian`.
2. Gunakan card:
   - `Daftar Jurnal Pembelian`
   - `Daftar Jurnal Retur`
   - `Daftar Jurnal Pelunasan Utang Perusahaan`
3. Klik baris jurnal untuk melihat detail debit/kredit.

## Validasi Operasional

- Jika card `Dokumen Retur Siap Potong` tidak muncul, berarti supplier belum memiliki saldo retur `13013` yang masih terbuka.
- Jika posting ditolak, cek total alokasi hutang dan total retur. Keduanya harus sama.
- Jika akun `13013`, `13017`, `14010`, atau `14011` tidak aktif, posting retur akan ditolak oleh validasi COA.
