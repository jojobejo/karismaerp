# Development Dashboard Penilaian Direksi Readonly

Tanggal development: 2026-07-28

## Tujuan

Menambahkan akses direksi untuk route `dashboard_penilaian` dengan mode readonly. Akun direksi dapat membaca dashboard, statistik, chart, daftar issue, dan detail analisa tanpa akses create, update, delete, atau konfigurasi master data.

## Akun Direksi

| Username | Password login | Role aplikasi | Route awal |
| --- | --- | --- | --- |
| `direktur1` | `direktur89` | `direksi_readonly` | `dashboard_penilaian` |
| `direktur2` | `direktur89` | `direksi_readonly` | `dashboard_penilaian` |
| `direktur3` | `direktur89` | `direksi_readonly` | `dashboard_penilaian` |

Password disimpan di database sebagai bcrypt, bukan plaintext.

## Perubahan Aplikasi

1. `application/controllers/Auth.php`
   - Menambahkan redirect khusus untuk session `jobdesk_hrd = direksi_readonly`.
   - Akun direksi setelah login diarahkan ke `dashboard_penilaian`.

2. `application/controllers/hrd/C_Hrd.php`
   - Menambahkan detector readonly direksi berdasarkan username `direktur1`, `direktur2`, `direktur3`, atau `jobdesk_hrd = direksi_readonly`.
   - Menambahkan block server-side untuk aksi CRUD pada endpoint penilaian lingkungan:
     - `hrd/penilaian_lingkungan/submit`
     - `hrd/penilaian_lingkungan/update`
     - `hrd/penilaian_lingkungan/locations/save`
     - `hrd/penilaian_lingkungan/locations/delete`
     - `hrd/penilaian_lingkungan/ratings/save`
     - `hrd/penilaian_lingkungan/ratings/delete`
   - Direksi yang membuka route form `penilaian_lingkungan` diarahkan kembali ke `dashboard_penilaian`.

3. `application/views/content/hrd/penilaian_lingkungan/admin_dashboard.php`
   - Menampilkan dashboard dalam mode readonly untuk akun direksi.
   - Menyembunyikan tombol `Form`, kolom `Aksi`, dan modal update issue.

4. `application/views/content/hrd/penilaian_lingkungan/monitoring.php`
   - Menyembunyikan tombol `Form`, tab `Master Data`, kolom `Aksi`, dan modal update jika akun direksi membuka monitoring secara manual.

5. `application/views/content/hrd/penilaian_lingkungan/js.php`
   - Menambahkan flag `isReadonlyPenilaian`.
   - Rendering tabel menyesuaikan jumlah kolom dan tidak membuat tombol update saat readonly.

## Aturan Hak Akses

- Direksi boleh membaca data dashboard, statistik, breakdown chart, daftar issue, dan monitoring.
- Direksi tidak boleh submit issue, update issue, tambah/edit/hapus lokasi, atau tambah/edit/hapus rating.
- Pembatasan dilakukan pada UI dan server-side. UI hanya untuk kenyamanan, server-side menjadi pengaman utama.

## Cara Uji

1. Jalankan script database. Script ini sudah mencakup tambahan struktur `tb_users` yang dibutuhkan dan insert/update akun direksi:

```sql
SOURCE database/sql/tb_users_direksi_dashboard_penilaian_readonly.sql;
```

2. Login dengan salah satu akun:

```text
username: direktur1
password: direktur89
```

3. Pastikan setelah login masuk ke route `dashboard_penilaian`.
4. Pastikan tombol `Form` dan kolom `Aksi` tidak tampil.
5. Pastikan request manual ke endpoint update/save/delete mengembalikan HTTP 403 JSON dengan pesan readonly.
