# Penambahan Akun Admin

## Tujuan

Skrip [tambah_user_admin.sql](../../db/patches/tambah_user_admin.sql) menambahkan user `admin` aktif ke `tb_users` untuk login aplikasi. Nilai `level` diset `1` dan `jabatan` diset `ADMIN`, sesuai pemeriksaan hak admin pada proses autentikasi.

## Cara menjalankan

1. Pilih database Karisma ERP yang digunakan aplikasi di phpMyAdmin.
2. Pastikan tabel `tb_users` tersedia.
3. Buka tab **Import** atau **SQL**, lalu jalankan isi `db/patches/tambah_user_admin.sql`.
4. Periksa hasil `SELECT` pada bagian akhir skrip. Jika akun `admin` sudah ada, skrip melewati insert dan menampilkan akun yang sudah ada tanpa mengganti password atau status.

Hash password pada skrip dibuat dengan `password_hash()` PHP dan dapat diverifikasi oleh `password_verify()` yang digunakan aplikasi. Demi keamanan, ganti password setelah login pertama dan hindari membagikan kredensial melalui kanal publik.

## Dampak database

- Tabel yang digunakan: `tb_users`.
- Perubahan skema: **tidak ada**.
- Data baru: satu akun admin hanya jika username `admin` belum tersedia.
- Akun yang sudah ada tidak diperbarui ataupun diaktifkan ulang oleh skrip ini.
