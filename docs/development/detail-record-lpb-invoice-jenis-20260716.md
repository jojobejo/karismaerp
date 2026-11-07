# Development Detail Record LPB - Invoice dan Jenis LPB

Tanggal: 2026-07-16
Route: `ics/detail_record_lpb`

## Ringkasan

Halaman detail record LPB dirapikan agar fokus pada informasi audit LPB yang dibutuhkan:

- Card `Detail LPB` tidak lagi menampilkan `ID LPB`, `Total Item`, `Total Baris`, `Total Qty`, dan kolom `Qty Diterima`.
- Card `Daftar LPB` menampilkan badge status invoice:
  - `Ada invoice` bila `no_invoice` terisi dan bukan `-`.
  - `Tidak ada invoice` bila invoice kosong atau bernilai `-`.
- Card `Daftar LPB` menampilkan `Jenis LPB`.
- Tombol `Input Nomor Invoice` dipusatkan di header card `Detail LPB`, bukan di setiap card `Daftar LPB`.
- Modal invoice sekarang ikut menyimpan `Jenis LPB`.

## File Aplikasi

- `application/views/content/logistik/ics/detail_record_lpb.php`
  - Menambah badge invoice, tombol input invoice, dan tampilan jenis LPB pada daftar LPB.
  - Mengurangi field yang tampil pada card detail LPB.
  - Menampilkan tombol input invoice pada daftar LPB.
- `application/controllers/logistik/C_Ics.php`
  - Update invoice dibuka dari halaman detail record LPB.
  - Membiarkan jenis LPB kosong saat finalisasi LPB baru sampai ditentukan lewat tombol `Setting Jenis PO`.
- `application/models/M_Logistik.php`
  - Query daftar/detail LPB membawa `jenis_lpb`.
  - Fallback aman tetap membuat halaman berjalan bila database belum memiliki kolom `jenis_lpb`.

## Cara Penggunaan

1. Buka menu Data PO lalu masuk ke route `ics/detail_record_lpb`.
2. Pada card `Daftar LPB`, lihat badge invoice untuk mengetahui LPB yang belum memiliki invoice.
3. Pilih LPB pada daftar, lalu klik tombol `Update Invoice` di card `Detail LPB`.
4. Isi `No Invoice`, `Jenis LPB`, `No Surat Jalan`, `Tanggal Surat Jalan`, dan `Keterangan`.
5. Klik `Simpan Invoice`.

## Catatan

Perubahan database ada di `docs/database/ics_lpb_jenis_lpb_20260716.sql`.
