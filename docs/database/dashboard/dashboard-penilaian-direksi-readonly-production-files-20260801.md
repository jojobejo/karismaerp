# Database Dashboard Penilaian Direksi Readonly Production Files

Tanggal development: 2026-08-01

## Tujuan Database

Menyiapkan akun direksi readonly pada tabel `tb_users` untuk akses `dashboard_penilaian` di hosting production.

## File SQL

File migrasi yang diberikan:

`C:\Users\bram\Downloads\migrasi_direksi_readonly_dashboard_penilaian.sql`

## Akun Yang Dibuat/Diupdate

| Username | Password Login | `jobdesk_hrd` | `default_redirect` |
| --- | --- | --- | --- |
| `direktur1` | `direktur89` | `direksi_readonly` | `dashboard_penilaian` |
| `direktur2` | `direktur89` | `direksi_readonly` | `dashboard_penilaian` |
| `direktur3` | `direktur89` | `direksi_readonly` | `dashboard_penilaian` |

Password disimpan dalam bentuk bcrypt.

## Karakter Migrasi

Script aman dijalankan ulang karena:

- menambahkan kolom `tb_users` hanya jika belum ada;
- membuat index hanya jika belum ada;
- menjalankan `UPDATE` untuk akun yang sudah ada;
- menjalankan `INSERT ... WHERE NOT EXISTS` untuk akun yang belum ada;
- menampilkan query validasi akhir untuk memastikan tiga akun tersedia.
