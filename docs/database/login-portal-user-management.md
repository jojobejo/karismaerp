# Catatan Database: Login Portal dan Pengguna

Tanggal: 20 Oktober 2026

## Perubahan Skema

Tidak ada perubahan skema database pada pengembangan ini. Autentikasi tetap membaca sumber akun yang sudah digunakan aplikasi (`tb_karyawan` dan `tb_users` melalui `M_Auth`). Modul `master/user-management` mengelola data pengguna pada `tb_karyawan`, termasuk username, password ter-hash, jobdesk, level akses, status, dan informasi login jika kolom terkait tersedia.

## Perubahan Data Lokal

Pada database lokal `kiucoid_karismaerp`, akun `admin` yang sudah ada pada `tb_karyawan` (ID 46) diaktifkan dan password-nya disetel ke nilai awal yang diminta. Password disimpan menggunakan `password_hash`; username `admin` juga ditemukan pada baris lain, sehingga pencarian login diurutkan berdasarkan ID. Tidak ada baris akun yang ditambahkan atau dihapus.

## Tabel Terkait

- `tb_karyawan`: data akun karyawan yang dikelola lewat modul User Management.
- `tb_users`: sumber akun tambahan yang telah digunakan autentikasi, termasuk akses khusus yang tersedia pada skema.
- `tb_login_log`: pencatatan login apabila tabel tersedia pada database.

Tidak ada SQL migrasi yang perlu dijalankan. Struktur acuan tetap mengikuti `db/kiucoid_karismaerp_local _acuan_2026_agustus.sql`.
