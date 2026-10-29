# Akun Admin Super Administrator

Tanggal: 2026-10-29

## Penggunaan

Login melalui route `/auth` menggunakan username `admin` dan password yang ditetapkan pada saat permintaan ini. Akun menggunakan level 1 (**Super Administrator**) agar mendapat akses aplikasi sesuai otorisasi yang tersedia.

Akun `admin` sebelumnya sudah ada, sehingga akun tersebut diperbarui dan tidak dibuat duplikat. Status akun aktif. Sidebar administrator memuat menu manajemen; menu dinamis lainnya mengikuti daftar yang sudah dikonfigurasi di `tb_menu`.

## Berkas Aplikasi

Tidak ada perubahan source aplikasi. Autentikasi tetap memakai alur `/auth` yang mengambil kredensial dari `tb_users`.

## Validasi

Pembaruan akun diverifikasi langsung ke database lokal: username `admin`, status aktif, level 1, dan password berhasil diverifikasi terhadap hash tersimpan. Pengujian login melalui browser belum dilakukan.
