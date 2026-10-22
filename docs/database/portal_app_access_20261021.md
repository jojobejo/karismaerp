# Database: Hak Akses Aplikasi Portal

## Perubahan

Tambahkan tabel `tb_portal_app_access` untuk menyimpan pengecualian hak akses aplikasi per akun. Identitas akun terdiri dari `auth_source` (`tb_karyawan` atau `tb_users`) dan `user_id`, karena kedua tabel sumber dapat memiliki ID yang sama.

## Penerapan

Jalankan `portal_app_access_20261021.sql` pada database KARISMA ERP. Operasi `CREATE TABLE IF NOT EXISTS` aman dijalankan ulang. Tidak ada perubahan pada tabel akun yang sudah ada.

## Nilai bawaan

Tidak adanya baris untuk pasangan akun dan aplikasi berarti akses diizinkan. Baris dengan `is_allowed = 0` berarti akses dicabut. Desain ini mempertahankan daftar aplikasi yang sudah terlihat sebelum fitur diberlakukan. Data akses menggunakan kode aplikasi yang dikelola di `application/models/M_PortalAccess.php`.

## Pemulihan

Untuk memulihkan akses sebuah akun, simpan kembali seluruh aplikasi sebagai diizinkan melalui halaman admin. Penghapusan seluruh tabel akan mengembalikan semua akun ke akses bawaan diizinkan, sehingga sebaiknya lakukan backup sebelum pemulihan massal.
