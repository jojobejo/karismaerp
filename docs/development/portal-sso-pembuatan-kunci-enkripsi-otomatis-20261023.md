# Pembuatan Kunci Enkripsi SSO Otomatis

## Tujuan

Halaman **Admin → Integrasi SSO Aplikasi Klien** (`admin/portal-sso-clients`) menyediakan tombol **Buat kunci otomatis** untuk mengatasi pesan bahwa `PORTAL_SSO_ENCRYPTION_KEY` belum tersedia. Tombol hanya dapat digunakan oleh administrator yang sudah login.

## Cara penggunaan

1. Buka halaman Integrasi SSO Aplikasi Klien.
2. Jika status menunjukkan kunci belum tersedia, klik **Buat kunci otomatis** dan konfirmasi.
3. Setelah proses berhasil, status berubah menjadi kunci aktif. Kunci acak 256-bit disimpan pada `application/config/portal_sso_local.php` dan langsung digunakan oleh proses enkripsi/dekripsi secret.
4. Lanjutkan pendaftaran atau rotasi klien. Simpan secret klien pada secret manager server klien.

File `portal_sso_local.php` masuk daftar pengecualian Git dan tidak ditampilkan di halaman. Pastikan file konfigurasi itu tetap ada setelah deployment dan termasuk backup rahasia aplikasi. Semua node aplikasi yang membaca ciphertext yang sama harus menggunakan nilai kunci yang sama. Kehilangan atau penggantian kunci menyebabkan secret yang sudah dienkripsi tidak dapat dibuka; buat secret baru melalui rotasi bila kunci lama hilang.

Jika server memakai `PORTAL_SSO_ENCRYPTION_KEY` atau `encryption_key` CI yang valid, kunci konfigurasi tersebut tetap dipakai dan tombol pembuatan dinonaktifkan. Fitur tidak mengganti kunci aktif. Jika folder `application/config` tidak dapat ditulis oleh proses PHP, tombol mengembalikan pesan agar izin folder diperiksa; atur izin minimum yang diperlukan oleh administrator server.

## Batas perubahan

Perubahan hanya menyentuh aplikasi dan dokumentasi penggunaan. Tidak ada perubahan skema atau data database.
