# Panduan Akun List Klien Portal

## Prasyarat

- Administrator sudah mengaktifkan kunci enkripsi pada halaman Integrasi SSO.
- Tabel `tb_portal_sso_client_account` sudah dibuat menggunakan migration pada `docs/database/portal_sso_client_account_20261029.sql`.
- Untuk membuka password, server harus memiliki `PORTAL_SSO_KEY_PIN` yang dikonfigurasi.

## Mengunggah akun

1. Masuk sebagai admin dan buka **Integrasi SSO** (`admin/portal-sso-clients`).
2. Pada klien tujuan, pilih **Akun List**.
3. Pilih file CSV. Gunakan baris pertama sebagai header dan pilih pemisah koma atau titik koma sesuai file.
4. Pilih header kolom username dan password pada bagian **Pemetaan header CSV**. Untuk format yang mudah dikenali, gunakan nama header CSV `username` dan `password`; bila nama header berbeda, petakan kolom yang berisi nilai akun dan kata sandi dengan benar.
5. Pilih **Impor**. Sistem menambah akun baru, melewati baris yang username/password-nya kosong, dan memperbarui password jika username sudah terdaftar pada klien tersebut. PIC lama tetap dipertahankan saat password diperbarui.

CSV dibatasi maksimal 5 MB dan 10.000 baris data. Username duplikat di dalam file membatalkan seluruh impor agar hasil tidak ambigu. File SQL tidak diterima atau dijalankan.

## Nama kolom di aplikasi klien

Aplikasi klien menyiapkan akun lokal pada database-nya dengan field `username` dan `password` (atau memetakan padanan kolom yang sudah ada). Form login lokal dapat menggunakan input `username` dan `password` untuk autentikasi non-SSO. Untuk login SSO, aplikasi mencocokkan klaim `client_account_username` dengan kolom username lokal dan membuat session; password tidak dikirim oleh Portal ke aplikasi klien. Password tetap diverifikasi oleh autentikasi lokal aplikasi klien dan tidak boleh diasumsikan tersedia di respons SSO.

Route `admin/portal-sso-clients/accounts` menampilkan ringkasan semua klien dan jumlah akun. Tombol **Akun List** dari tabel integrasi langsung membuka daftar untuk satu klien.

## Mengatur PIC

Pada baris akun, pilih PIC dari daftar akun `tb_karyawan` atau `tb_users`, lalu tekan tombol simpan. PIC dapat diganti atau dikosongkan kapan saja. Identitas akun dicatat bersama sumbernya agar ID dari kedua tabel tidak tertukar.

## Melihat password

Tekan tombol mata pada baris akun, masukkan PIN admin, lalu pilih **Tampilkan password**. Password tidak ditampilkan langsung di tabel. Tutup jendela setelah selesai; aktivitas pengungkapan dicatat di log aplikasi tanpa menyertakan nilai password.

## Menghapus akun

Tekan tombol hapus pada baris akun dan konfirmasi tindakan. Penghapusan tidak dapat dibatalkan kecuali melalui backup database.
