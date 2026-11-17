# Panduan Penggunaan & UAT Login SSO — Penilaian Lingkungan

## 1. Pengaturan di Portal KARISMA ERP (Administrator)

1. Buka `https://portal.karismaerp.com/admin/portal-sso-clients`:
   - Pastikan klien dengan `app_code = penilaian_lingkungan` berstatus **Aktif**.
   - **URL Peluncuran (`launch_uri`)**: `https://lingkungan.karismaerp.com/sso/login`
   - **Callback Terdaftar (`redirect_uri`)**: `https://lingkungan.karismaerp.com/sso/callback`
2. Impor / pastikan daftar akun klien untuk `penilaian_lingkungan` tersedia pada menu **Daftar Akun Klien**.
3. Buka **Akses Aplikasi** (`https://portal.karismaerp.com/admin/portal-app-access`) untuk pengguna yang dituju:
   - Aktifkan izin aplikasi **Penilaian Lingkungan** (`penilaian_lingkungan`) lalu klik **Simpan akses**.
   - Klik tombol **Akun** pada kartu Penilaian Lingkungan dan tetapkan tepat **1 akun kerja lokal** yang aktif di database aplikasi `lingkungan.karismaerp.com`.

## 2. Tata Cara Login Pengguna

1. Pengguna masuk ke `https://portal.karismaerp.com` menggunakan akun Portal masing-masing.
2. Pada dashboard Portal, klik **Mulai Bekerja** pada kartu **Penilaian Lingkungan Kerja** (atau akses `https://lingkungan.karismaerp.com/sso/login`).
3. Aplikasi Penilaian Lingkungan otomatis memverifikasi otorisasi SSO dengan Portal, mencocokkan akun lokal yang ditetapkan, mencatat histori login, lalu mengarahkan pengguna langsung ke halaman sesuai role lokal:
   - **Superadmin** (`superadmin`): diarahkan ke `hrd/penilaian_lingkungan/admin`.
   - **Admin / Direksi Readonly** (`admin`, `direktur1`–`direktur3`, `direksi_readonly`): diarahkan ke `dashboard_penilaian`.
   - **Inputer Laporan / Karyawan** (`inputer_laporan`): diarahkan ke `penilaian_lingkungan`.
4. **Pergantian Akun**:
   - Apabila ingin berganti pengguna pada browser yang sama, lakukan logout dari Portal terlebih dahulu, login ke Portal sebagai pengguna tujuan, lalu klik kembali kartu **Penilaian Lingkungan Kerja**.
   - Logout dari aplikasi Penilaian Lingkungan (`/logout` atau `/auth/logout`) hanya mengakhiri session lokal aplikasi Penilaian Lingkungan dan tidak mengakhiri session Portal.
