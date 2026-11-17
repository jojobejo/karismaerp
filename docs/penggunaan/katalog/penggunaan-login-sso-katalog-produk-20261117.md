# Panduan Penggunaan & UAT Login SSO — Katalog Produk

## 1. Pengaturan di Portal KARISMA ERP (Administrator)

1. Buka `https://portal.karismaerp.com/admin/portal-sso-clients`:
   - Pastikan klien dengan `app_code = katalog_produk` berstatus **Aktif**.
   - **URL Peluncuran (`launch_uri`)**: `https://kiukatalog.karismaerp.com/sso/login`
   - **Callback Terdaftar (`redirect_uri`)**: `https://kiukatalog.karismaerp.com/sso/callback`
2. Impor / pastikan daftar akun klien untuk `katalog_produk` tersedia pada menu **Daftar Akun Klien** (misalnya `admin`, `kiusales`, `kiukeuangan`, `kiuonline`, `kiuse`, `direktur`).
3. Buka **Akses Aplikasi** (`https://portal.karismaerp.com/admin/portal-app-access`) untuk pengguna yang dituju:
   - Aktifkan izin aplikasi **Katalog Produk** (`katalog_produk`) lalu klik **Simpan akses**.
   - Klik tombol **Akun** pada kartu Katalog Produk dan tetapkan tepat **1 akun kerja lokal** yang aktif pada tabel `tb_user` di aplikasi `kiukatalog.karismaerp.com`.

## 2. Tata Cara Login Pengguna

1. Pengguna masuk ke `https://portal.karismaerp.com` menggunakan akun Portal masing-masing.
2. Pada dashboard Portal, klik **Mulai Bekerja** pada kartu **Katalog Produk** (atau akses `https://kiukatalog.karismaerp.com/sso/login`).
3. Aplikasi Katalog Produk otomatis memverifikasi otorisasi SSO dengan Portal, mencocokkan akun lokal yang ditetapkan, mencatat histori login, lalu mengarahkan pengguna langsung ke halaman sesuai `hak_akses` lokal:
   - **Hak Akses 1 (Admin)**: diarahkan ke `dashboard` (`Katalog`).
   - **Hak Akses 2 (Sales / SE)**: diarahkan ke `sales` (`Sales`).
   - **Hak Akses 3 (Keuangan)**: diarahkan ke `keuangan` (`Keuangan`).
   - **Hak Akses 4 (Sales Online)**: diarahkan ke `sales` / `salesonline`.
   - **Hak Akses 5 (Direktur / Viewer)**: diarahkan ke `viewer` (`Viewer`).
4. **Pergantian Akun**:
   - Apabila ingin berganti pengguna pada browser yang sama, lakukan logout dari Portal terlebih dahulu, login ke Portal sebagai pengguna tujuan, lalu klik kembali kartu **Katalog Produk**.
   - Logout dari aplikasi Katalog Produk (`/logout` atau `/auth/logout`) hanya mengakhiri session lokal aplikasi Katalog Produk dan tidak mengakhiri session Portal.
