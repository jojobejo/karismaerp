# Pengelolaan Application Directory

Tanggal: 2026-10-29

## Tujuan

Administrator dapat mengubah informasi kartu aplikasi Portal tanpa mengedit source code.

## Cara Penggunaan

1. Login dengan username `admin`.
2. Pilih tombol **Application Directory** di baris navigasi bagian atas Portal.
3. Pilih **Edit** pada aplikasi yang akan diubah.
4. Ubah judul, URL HTTPS, deskripsi, ikon Font Awesome, warna aksen, atau status tampil.
5. Pilih **Simpan Perubahan**. Portal akan menampilkan perubahan setelah halaman dimuat ulang.

Aplikasi bertanda **Tersembunyi** tetap tersedia di katalog administrasi dan pengaturan akses, tetapi tidak ditampilkan sebagai kartu untuk pengguna. Izin aplikasi per akun tetap berlaku.

## Implementasi

- `application/controllers/admin/C_PortalAppDirectory.php`: halaman administrasi, validasi HTTPS dan field tampilan, serta endpoint penyimpanan dengan token formulir.
- `application/models/M_PortalAppDirectory.php`: menyimpan override katalog aplikasi.
- `application/models/M_PortalAccess.php`: membaca override dari database; menggunakan nilai bawaan source jika tabel belum tersedia atau belum memiliki baris aplikasi.
- `application/views/content/admin/portal_app_directory.php`: tabel katalog dan form edit.
- `application/views/portal/index.php`: tombol navigasi direktori yang hanya ditampilkan untuk user `admin`.
- `application/views/partial/main/sidebar.php`: tautan tambahan pada menu admin.
- `application/config/routes.php`: route halaman dan penyimpanan.
- `docs/database/portal_app_directory_20261029.sql`: pembuatan tabel dan seed awal.

## Validasi

Halaman dan tombol navigasinya dibatasi untuk username `admin`; controller juga menolak akses user lain dengan HTTP 403. Di Portal, pengguna selain `admin` hanya melihat tombol Logout pada bar navigasi atas. URL wajib HTTPS, judul wajib diisi, deskripsi dibatasi 2.000 karakter, ikon harus berupa kelas `fa-*`, dan warna harus berupa hex enam digit. Belum dilakukan UAT browser.
