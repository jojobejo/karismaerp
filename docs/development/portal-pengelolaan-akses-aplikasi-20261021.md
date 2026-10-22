# Pengelolaan Akses Aplikasi Portal

## Tujuan

Admin dapat menentukan aplikasi mana yang tampil bagi setiap akun pada halaman `portal`. Halaman pengelolaan berada di route `admin/portal-app-access` dan hanya dapat dibuka oleh akun yang memiliki `is_admin_dashboard`.

## Perilaku

- Daftar aplikasi yang sebelumnya tersedia dipindahkan ke katalog terpusat `M_PortalAccess`.
- Portal membaca identitas akun dari sesi login, termasuk sumber akun (`tb_karyawan` atau `tb_users`), lalu menyembunyikan aplikasi yang aksesnya dicabut.
- Admin memilih akun, mengubah sakelar “Akses di Portal”, lalu menekan “Simpan akses”.
- Akun yang belum memiliki pengaturan tersimpan tetap memperoleh semua aplikasi, untuk mempertahankan akses yang berlaku sebelum fitur ini dipasang.
- Admin membuka pengaturan melalui tombol “Akses Aplikasi” pada bagian navigasi admin di halaman portal.

## Penerapan

1. Terapkan skrip database `docs/database/portal_app_access_20261021.sql`.
2. Login sebagai admin dan buka `portal`.
3. Pilih **Akses Aplikasi**, pilih akun yang dikelola, atur sakelar aplikasi, lalu simpan.
4. Minta pengguna memuat ulang portal untuk melihat daftar terbaru.

## Batas cakupan

Kontrol ini mengatur visibilitas tautan aplikasi pada portal KARISMA ERP. Aplikasi tujuan berada pada sistem/domain tersendiri; perubahan ini tidak mencabut sesi atau memblokir login langsung pada aplikasi tujuan. Otorisasi di sisi aplikasi tujuan tetap harus dikelola di sana.
