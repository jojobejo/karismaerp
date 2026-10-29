# Direktori Aplikasi Menggunakan Tautan Resmi

Tanggal: 2026-10-29

## Perubahan

Daftar Application Directory di Portal kini memuat tautan HTTPS yang diberikan untuk KIU PO, Warehouse, KMT CORN, KPI, POS Security, Katalog Produk, Karisma Online, Plafon Sales, Stockopname, Penilaian Lingkungan Kerja, Overdue, Kios Transfer, dan Sapa Karisma.

Kartu portal membuka tautan resmi aplikasi di tab baru. Status `hidden` untuk Karisma Online dipertahankan: aplikasinya tetap terdaftar untuk pengaturan modul/akses, tetapi kartunya tidak ditampilkan di direktori pengguna. Hak akses per akun tetap diterapkan sebelum kartu terlihat.

Alamat yang dikirim dengan tanda kutip berlebih untuk Penilaian Lingkungan Kerja dinormalisasi menjadi `https://lingkungan.karismaerp.com/`. URL Overdue memakai `https://tagpd.karismaerp.com/`. Pemisah array sebelum Sapa Karisma juga sudah sesuai sintaks PHP.

## Berkas Aplikasi

- `application/models/M_PortalAccess.php`: melengkapi katalog aplikasi dengan URL resmi dan menyembunyikan aplikasi bertanda `hidden` pada daftar pengguna. Pengelolaan nilai katalog kemudian ditambahkan pada [halaman Application Directory](application-directory-management-20261029.md).
- View portal yang sudah ada tetap merender kartu dan membuka `redirect_url` pada tab baru.

URL peluncuran (`launch_uri`) yang disimpan pada konfigurasi klien SSO tidak lagi menentukan tujuan kartu direktori; kartu memakai URL resmi di katalog aplikasi, yang sekarang dapat diedit administrator. Konfigurasi callback SSO tetap tersedia untuk alur autentikasi klien.

## Cara Penggunaan

1. Login ke KARISMA ERP dan buka Portal.
2. Pilih kartu aplikasi untuk membuka situs resminya di tab baru.
3. Jika sebuah kartu tidak tampil, periksa pengaturan akses aplikasi akun tersebut.

## Validasi

Belum dilakukan pengujian browser atau pemeriksaan ketersediaan seluruh situs tujuan. Tautan di katalog mengikuti URL yang diberikan.
