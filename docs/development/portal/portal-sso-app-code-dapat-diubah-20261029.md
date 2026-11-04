# Pengaturan app_code Klien SSO

Tanggal: 2026-10-29

## Perubahan aplikasi

Pada route `admin/portal-sso-clients`, administrator dapat menentukan `app_code` saat mendaftarkan klien dan mengubahnya melalui tombol edit. Format kode 2–50 karakter, menggunakan huruf kecil, diawali huruf atau angka, lalu hanya huruf, angka, `_`, atau `-`. Klien SSO khusus yang tidak ada pada katalog bawaan ikut muncul pada halaman `admin/portal-app-access`.

Tombol **Akses** tersedia pada setiap baris daftar integrasi dan membuka tampilan pengaturan akses Portal. Saat kode diganti, baris diperbarui setelah halaman dimuat ulang. Secret SSO tidak berubah.

## Cara penggunaan

1. Buka **Integrasi Aplikasi Klien** (`admin/portal-sso-clients`).
2. Isi `app_code`, URL peluncuran HTTPS, dan URL callback HTTPS saat mendaftarkan klien.
3. Gunakan tombol **Akses** pada baris aplikasi untuk membuka halaman hak akses pengguna.
4. Untuk mengganti kode, gunakan tombol edit, ubah `app_code`, lalu simpan. Kode tujuan harus belum dipakai.

Jika kode tujuan sudah memiliki aturan akses pengguna, perubahan ditolak agar aturan dua aplikasi tidak tercampur. Kode otorisasi yang belum ditukar dan aturan akses klien dipindahkan dalam transaksi bersama perubahan kode klien.

## Validasi

Pemeriksaan sintaks PHP dan pemeriksaan diff dijalankan. UAT browser dengan akun administrator dan pengujian pertukaran SSO belum dilakukan.
