# Development Menu Portal Kios Transfer dan Sapa Karisma

Tanggal: 2026-10-12

## Ringkasan

Menambahkan dua kartu aplikasi pada Portal KarismaERP menggunakan format menu yang sudah tersedia: nama, tautan aplikasi, ikon Font Awesome, deskripsi singkat, dan warna aksen.

## Detail Menu

- **Kios Transfer** — `https://kiostransfer.karismaerp.com`; untuk pencatatan metode pembayaran kios. Menggunakan ikon kasir.
- **Sapa Karisma** — `https://sapakarisma.karismaerp.com`; aplikasi HRIS untuk manajemen SDM yang dikelola HRD. Menggunakan ikon pengelolaan pengguna.

## Cara Penggunaan

1. Buka Portal KarismaERP.
2. Pilih kartu **Kios Transfer** untuk membuka aplikasi pencatatan metode pembayaran kios.
3. Pilih kartu **Sapa Karisma** untuk membuka aplikasi HRIS yang dikelola HRD.

## File

- `application/controllers/Portal.php` — menambahkan konfigurasi kedua kartu pada daftar aplikasi portal.

## Verifikasi

DNS kedua hostname berhasil di-resolve saat perubahan dibuat. Pemeriksaan ini hanya memastikan hostname memiliki rekaman DNS dan tidak memverifikasi halaman aplikasi atau proses login.
