# Panduan Penggunaan & UAT Login SSO — POS Security & HRD

## 1. Pengaturan di Portal KARISMA ERP (Administrator)

1. Buka `https://portal.karismaerp.com/admin/portal-sso-clients`:
   - Pastikan klien dengan `app_code = pos_security` berstatus **Aktif**.
   - **URL Peluncuran (`launch_uri`)**: `https://hrd.karismaerp.com/sso/login`
   - **Callback Terdaftar (`redirect_uri`)**: `https://hrd.karismaerp.com/sso/callback`
2. Impor / pastikan daftar akun klien untuk `pos_security` tersedia pada menu **Daftar Akun Klien** (misalnya `admin`, `HRD1`, `HRD2`, `HRD3`, `HRD4`, `logistik`, `alif`, `supri`, `marisa`, `yunika`).
3. Buka **Akses Aplikasi** (`https://portal.karismaerp.com/admin/portal-app-access`) untuk pengguna yang dituju:
   - Aktifkan izin aplikasi **POS Security** (`pos_security`) lalu klik **Simpan akses**.
   - Klik tombol **Akun** pada kartu POS Security dan tetapkan tepat **1 akun kerja lokal** yang aktif pada tabel `tb_user` di aplikasi `hrd.karismaerp.com`.

## 2. Tata Cara Login Pengguna

1. Pengguna masuk ke `https://portal.karismaerp.com` menggunakan akun Portal masing-masing.
2. Pada dashboard Portal, klik **Mulai Bekerja** pada kartu **POS Security** (atau akses `https://hrd.karismaerp.com/sso/login`).
3. Aplikasi POS Security otomatis memverifikasi otorisasi SSO dengan Portal, mencocokkan akun lokal yang ditetapkan pada `tb_user`, mencatat histori login, lalu mengarahkan pengguna langsung ke halaman sesuai departemen/role lokal:
   - **Petugas Penerimaan Paket / Keuangan / MIA / HRD & GA** (`alif`, `supri`, `marisa`, atau departemen `KEUANGAN`, `MIA`, `HRD & GA`): diarahkan ke `hrd_lap_paket_pos`.
   - **Petugas Berkas Expedisi Logistik** (`yunika` pada departemen `LOGISTIK`): diarahkan ke `hrd_lap_expedisi`.
   - **Admin IT / HRD GA (`HRD1`–`HRD4`) / Logistik**: diarahkan ke `dashboard` beserta menu modul sesuai hak akses masing-masing.
4. **Pergantian Akun**:
   - Apabila ingin berganti pengguna pada browser yang sama, lakukan logout dari Portal terlebih dahulu, login ke Portal sebagai pengguna tujuan, lalu klik kembali kartu **POS Security**.
   - Logout dari aplikasi POS Security (`/logout` atau `/auth/logout`) hanya mengakhiri session lokal aplikasi POS Security dan tidak mengakhiri session Portal.
