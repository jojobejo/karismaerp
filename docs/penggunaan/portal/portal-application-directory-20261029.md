# Panduan status Application Directory

## Arti warna kartu

- **Berwarna** dan berlabel **Klien terhubung**: integrasi aplikasi klien aktif di Portal dan URL peluncurannya sudah diisi.
- **Abu-abu** dan berlabel **Belum diatur klien**: integrasi belum aktif, URL peluncuran belum diisi, atau konfigurasi integrasi belum tersedia pada database.

Warna adalah indikator kesiapan konfigurasi integrasi klien. Hak pengguna untuk melihat aplikasi tetap ditentukan terpisah melalui menu **Akses Aplikasi**.

Saat integrasi klien aktif dan URL peluncuran sudah diisi, klik kartu akan membuka URL peluncuran tersebut. Jika URL peluncuran belum tersedia, Portal memakai URL aplikasi dari katalog.

## Mengatur agar aplikasi berwarna

1. Masuk sebagai admin ke Portal.
2. Buka **Integrasi SSO**.
3. Pilih kode aplikasi klien, lalu isi callback HTTPS dan URL peluncuran aplikasi.
4. Simpan dan pastikan integrasi dalam keadaan aktif.
5. Muat ulang Portal untuk melihat status terbaru.

Jika menu integrasi meminta migration URL peluncuran, jalankan SQL yang dirujuk oleh halaman atau dokumentasi database integrasi SSO. Perubahan status ini tidak mengubah hak akses tiap pengguna.
