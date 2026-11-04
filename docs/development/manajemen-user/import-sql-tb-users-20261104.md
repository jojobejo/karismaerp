# Import SQL user pada User Management

Tanggal: 4 November 2026. Route `master/user-management/import-sql` menerima unggahan SQL melalui dua langkah: pemeriksaan isi dan penerapan berkas yang sama. Tombol tersedia pada `master/user-management` untuk akun Portal bernama `admin`; endpoint juga memeriksa sesi admin dan token formulir.

Parser `Tb_users_sql_parser` membaca data tanpa mengeksekusi SQL unggahan. File `INSERT INTO tb_users ... VALUES` dan dump phpMyAdmin `tb_users` diterima. Pembungkus `SET SQL_MODE`, `SET time_zone`, transaksi, `CREATE TABLE tb_users`, dan `ALTER TABLE tb_users` pada dump hanya dikenali sebagai metadata lalu diabaikan. Perintah untuk tabel lain, `DROP`, `DELETE`, `UPDATE`, ekspresi SQL, dan sintaks tambahan ditolak. Berkas dibatasi 2 MB, UTF-8, dan 1.000 baris. Pemeriksaan menyimpan SHA-256 berkas pada sesi selama 10 menit; penerapan mengharuskan unggahan ulang berkas yang sama.

Model memvalidasi field wajib, level, status, serta password. Password teks biasa minimal enam karakter di-hash menggunakan `PASSWORD_DEFAULT`; hash PHP yang dikenali dipertahankan. ID, waktu login, dan timestamp dari dump dipertahankan untuk akun baru; benturan ID dengan akun berbeda ditolak. Username yang sudah ada dilewati tanpa menimpa akun. NIK ganda dan departemen kosong dari data lama diterima tetapi dihitung dalam pratinjau. Penyimpanan baris baru memakai satu transaksi; kegagalan membatalkan seluruh penyimpanan.

Uji sintaks PHP lulus. `tests/user_sql_import_parser_test.php` meloloskan contoh `INSERT` dan dump phpMyAdmin sintetis, serta menolak enam contoh SQL di luar format. Uji HTTP pada database dev berhasil memeriksa, mengimpor satu user sementara, lalu menghapus user uji itu. Perintah `DROP TABLE` dan penerapan tanpa pratinjau ditolak; akun `admin` yang sudah ada dikenali sebagai duplikat dan dilewati. Jumlah user kembali satu.

File pengguna `tb_users (1).sql` dari Downloads diuji **hanya pada pratinjau**: 167 baris terbaca, 166 calon user baru, satu akun yang sudah ada dilewati, tujuh NIK ganda, dan empat departemen kosong. Dari 167 password, 161 berupa hash PHP yang dikenali dan enam berupa teks biasa yang memenuhi batas panjang. SHA-256 file sumber saat diuji: `E8F34075CA0DD9A9EBBE580AC31A6E92B06DE8FAA9150C545D5C5F231F8578EA`. File pengguna tidak diimpor; uji ini belum mencakup semua variasi dump SQL atau unggahan sangat besar.

Uji HTTP tambahan memakai dump phpMyAdmin sintetis dengan dua user sementara. Pratinjau dan penerapan berhasil, ID sumber dipertahankan, satu NIK ganda serta satu departemen kosong terhitung, lalu kedua user uji dihapus. Dump pengguna tetap hanya dipratinjau.

Tidak ada perubahan skema database. Catatan dampak data ada di [dokumentasi database](../../database/manajemen-user/import-sql-tb-users-20261104.md), dan langkah pengguna ada di [panduan penggunaan](../../penggunaan/manajemen-user/import-sql-tb-users-20261104.md).
