# Database: Hak Akses Aplikasi Portal

## Perubahan

Tambahkan tabel `tb_portal_app_access` untuk menyimpan pengecualian hak akses aplikasi per akun. Identitas akun terdiri dari `auth_source` (`tb_karyawan` atau `tb_users`) dan `user_id`, karena kedua tabel sumber dapat memiliki ID yang sama.

## Penerapan

Jalankan `portal_app_access_20261021.sql` pada database KARISMA ERP. Operasi `CREATE TABLE IF NOT EXISTS` aman dijalankan ulang. Tidak ada perubahan pada tabel akun yang sudah ada.

## Nilai bawaan

Untuk tampilan katalog lama, tidak adanya baris tetap mempertahankan akses bawaan diizinkan. Untuk otorisasi SSO, akun harus memiliki setidaknya satu penetapan akses eksplisit; tanpa penetapan, login SSO ditolak. Jika akun sudah memiliki konfigurasi lama berbasis baris penolakan saja, aplikasi yang tidak memiliki baris penolakan tetap dianggap diizinkan. Penyimpanan baru menulis satu baris per aplikasi dengan `is_allowed` 1 atau 0. Data akses menggunakan kode aplikasi yang dikelola di `application/models/M_PortalAccess.php`.

## Pemulihan

Untuk memulihkan akses sebuah akun, simpan kembali seluruh aplikasi sebagai diizinkan melalui halaman admin. Penghapusan seluruh tabel akan mengembalikan semua akun ke akses bawaan diizinkan, sehingga sebaiknya lakukan backup sebelum pemulihan massal.
