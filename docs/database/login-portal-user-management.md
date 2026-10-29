# Catatan Database: Login Portal dan Pengguna

Tanggal: 20 Oktober 2026

## Perubahan Skema

Tidak ada perubahan skema database pada pengembangan ini. Autentikasi tetap membaca sumber akun yang sudah digunakan aplikasi (`tb_karyawan` dan `tb_users` melalui `M_Auth`). Sejak 29 Oktober 2026, modul `master/user-management` mengelola data pengguna pada `tb_users`; sebelumnya modul ini mengelola `tb_karyawan`.

## Perubahan Data Lokal

Pada database lokal `kiucoid_karismaerp`, akun `admin` yang sudah ada pada `tb_karyawan` (ID 46) diaktifkan dan password-nya disetel ke nilai awal yang diminta. Password disimpan menggunakan `password_hash`; username `admin` juga ditemukan pada baris lain, sehingga pencarian login diurutkan berdasarkan ID. Tidak ada baris akun yang ditambahkan atau dihapus.

## Tabel Terkait

- `tb_users`: sumber akun yang dikelola lewat modul User Management dan telah digunakan autentikasi.
- `tb_karyawan`: sumber akun terpisah yang tetap digunakan proses autentikasi lain; tidak dimodifikasi oleh halaman `master/user-management`.
- `tb_login_log`: pencatatan login apabila tabel tersedia pada database.

Tidak ada SQL migrasi yang perlu dijalankan. Struktur acuan tetap mengikuti `db/kiucoid_karismaerp_local _acuan_2026_agustus.sql`.
