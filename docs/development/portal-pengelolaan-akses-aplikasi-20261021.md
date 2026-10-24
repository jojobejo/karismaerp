# Pengelolaan Akses Aplikasi Portal

## Tujuan

Admin dapat menentukan aplikasi mana yang tampil bagi setiap akun pada halaman `portal`. Halaman pengelolaan berada di route `admin/portal-app-access` dan dilindungi izin `master/user-management`.

## Perilaku

- Daftar aplikasi yang sebelumnya tersedia dipindahkan ke katalog terpusat `M_PortalAccess`.
- Portal membaca identitas akun dari sesi login, termasuk sumber akun (`tb_karyawan` atau `tb_users`), lalu menyembunyikan aplikasi yang aksesnya dicabut.
- Admin memilih akun, mengubah sakelar “Akses di Portal”, lalu menekan “Simpan akses”.
- Akun yang belum memiliki pengaturan tersimpan tetap memperoleh semua aplikasi, untuk mempertahankan akses yang berlaku sebelum fitur ini dipasang.
- Visibilitas katalog tersebut tidak otomatis memberi izin masuk SSO. Untuk SSO, admin harus menyimpan penetapan akses akun secara eksplisit; user tanpa penetapan ditolak.
- Pengelola membuka **User Management**, lalu menggunakan tombol ikon roda gigi pada kolom **Akses** untuk membuka hak akses aplikasi milik user tersebut.
- Setiap kartu aplikasi menyediakan tombol **Detail Module** untuk membuka katalog module aplikasi tersebut. Admin mengelola katalog melalui UI; klien terdaftar juga dapat mengelolanya melalui API dengan kredensial aplikasinya.

## Penerapan

1. Terapkan skrip database `docs/database/portal_app_access_20261021.sql`.
2. Login dengan akun yang memiliki izin `master/user-management` dan buka halaman User Management.
3. Pada baris user yang akan dikelola, klik ikon roda gigi di kolom **Akses**. Halaman Akses Aplikasi langsung memuat user tersebut.
4. Atur aplikasi yang diizinkan, lalu tekan **Simpan akses**. Kembali ke User Management melalui tombol di halaman akses.
5. Minta pengguna memuat ulang portal untuk melihat daftar terbaru.

## Batas cakupan

Kontrol ini mengatur visibilitas tautan aplikasi pada portal KARISMA ERP. Setelah integrasi SSO klien diaktifkan, endpoint SSO juga memeriksa penetapan akses ini untuk login baru. Perubahan ini belum mencabut sesi yang sudah aktif, memblokir login lokal langsung, atau mengatur role/modul/data dalam aplikasi tujuan; otorisasi internal aplikasi klien tetap harus dikelola di sana. Lihat `docs/development/portal-sso-dan-hak-akses-klien-20261022.md`.

portal_sso_secret_reveal_20261022
