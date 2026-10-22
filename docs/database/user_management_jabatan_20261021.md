# Perubahan Database Jabatan User Management

Master referensi `tb_jabatan` sudah tersedia. Pada 21 Oktober 2026, kolom `jabatan` ditambahkan ke tabel `tb_karyawan` pada database lokal `kiucoid_karismaerp` setelah membuat backup tabel berikut:

`database/backups/tb_karyawan_before_jabatan_20261021.sql`

Skrip migrasi yang diterapkan:

```sql
ALTER TABLE `tb_karyawan`
  ADD COLUMN `jabatan` varchar(100) DEFAULT NULL AFTER `jobdesk`;
```

File migrasi terpisah disediakan pada `user_management_jabatan_20261021.sql`. Perubahan bersifat aditif. Dua akun yang sudah ada saat migrasi diterapkan memiliki nilai `jabatan` bernilai `NULL`, sebab sebelum kolom ditambahkan aplikasi belum menyimpan nilai jabatan pada data karyawan. Nilai tersebut harus dipilih sekali ketika akun diedit; setelah disimpan, form edit akan memuat jabatan tersimpan sebagai pilihan awal.

Migrasi hanya diterapkan pada database lokal yang disebutkan di atas. Database lain perlu dimigrasikan terpisah setelah backup.
