# Penggunaan ICS Detail Record LPB - Purchasing POST/UNPOST

Route: `ics/detail_record_lpb`
Akses: Purchasing

## Alur Penggunaan

1. Buka detail LPB dari menu Purchasing.
2. Jika status LPB `UNPOST`, kolom `#` tampil dengan tombol pensil kuning untuk edit detail.
3. Jika status LPB `POST`, kolom `#` disembunyikan dan update detail tidak dapat dilakukan.
4. Gunakan tombol merah `UNPOST` di bawah tabel untuk membuka kembali record dari status `POST` menjadi `UNPOST`.
5. Gunakan tombol `Rekam` di bawah tabel untuk menyimpan verifikasi harga. Setelah verifikasi berhasil, status LPB langsung berubah dari `UNPOST` menjadi `POST`.
6. Gunakan tombol biru icon mata untuk melihat log aktivitas detail PO/LPB.

## Catatan

Setiap perubahan status dan update harga/invoice/faktur dicatat pada log aktivitas LPB.
