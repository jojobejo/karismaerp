# Dokumentasi Database Dashboard Penilaian Direksi Readonly

Tanggal development: 2026-07-28

## Tujuan Database

Menambahkan tiga akun direksi pada tabel `tb_users` untuk akses readonly ke route `dashboard_penilaian`.

## File SQL

Script database:

`database/sql/tb_users_direksi_dashboard_penilaian_readonly.sql`

Script aman dijalankan ulang karena:

- menambahkan kolom struktur `tb_users` hanya jika belum tersedia;
- membuat index pendukung hanya jika belum tersedia;
- melakukan `UPDATE` lebih dulu untuk username yang sudah ada;
- melakukan `INSERT ... WHERE NOT EXISTS` jika username belum ada.

Script ini menyelesaikan error production `#1054 - Unknown column 'status' in 'SET'` dengan menambahkan kolom `status` sebelum query update data direksi dijalankan.

## Data Akun

| Username | Password login | Kolom `jobdesk_hrd` | Kolom `default_redirect` | Status |
| --- | --- | --- | --- | --- |
| `direktur1` | `direktur89` | `direksi_readonly` | `dashboard_penilaian` | `1` jika kolom tersedia |
| `direktur2` | `direktur89` | `direksi_readonly` | `dashboard_penilaian` | `1` jika kolom tersedia |
| `direktur3` | `direktur89` | `direksi_readonly` | `dashboard_penilaian` | `1` jika kolom tersedia |

## Kolom Yang Digunakan

| Kolom | Nilai |
| --- | --- |
| `password` | Hash bcrypt dari `direktur89` |
| `level` | `5` |
| `jobdesk_hrd` | `direksi_readonly` |
| `nama_lngkp` | `Direktur 1`, `Direktur 2`, `Direktur 3` |
| `nik` | `-` |
| `bagian` | `Direksi` |
| `departement` | `Direksi` |
| `jabatan` | `Direksi` |
| `atasan` | `-` |
| `penilai` | `-` |
| `status` | `1` |
| `default_redirect` | `dashboard_penilaian` |

## Perubahan Schema

Script akan menambahkan kolom berikut jika belum ada:

| Kolom | Tipe |
| --- | --- |
| `password` | `varchar(255) NOT NULL DEFAULT ''` |
| `level` | `int(11) NOT NULL DEFAULT 1` |
| `jobdesk_hrd` | `varchar(100) DEFAULT NULL` |
| `nama_lngkp` | `varchar(255) NOT NULL DEFAULT ''` |
| `nik` | `varchar(255) NOT NULL DEFAULT '-'` |
| `bagian` | `varchar(255) NOT NULL DEFAULT ''` |
| `departement` | `varchar(255) NOT NULL DEFAULT ''` |
| `jabatan` | `varchar(255) NOT NULL DEFAULT ''` |
| `atasan` | `varchar(255) NOT NULL DEFAULT ''` |
| `penilai` | `varchar(255) NOT NULL DEFAULT ''` |
| `status` | `tinyint(1) NOT NULL DEFAULT 1` |
| `default_redirect` | `varchar(180) DEFAULT NULL` |
| `last_login` | `datetime DEFAULT NULL` |
| `created_at` | `datetime DEFAULT CURRENT_TIMESTAMP` |
| `updated_at` | `datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP` |

Script juga membuat index non-unique jika belum ada:

- `idx_tb_users_username` pada `username`;
- `idx_tb_users_jobdesk_hrd` pada `jobdesk_hrd`;
- `idx_tb_users_status` pada `status`.

## Validasi Database

Query validasi:

```sql
SELECT id, username, level, jobdesk_hrd, nama_lngkp, jabatan, status, default_redirect
FROM tb_users
WHERE username IN ('direktur1', 'direktur2', 'direktur3');
```

Hasil yang diharapkan:

- Tiga row ditemukan.
- `jobdesk_hrd = direksi_readonly`.
- `default_redirect = dashboard_penilaian`.
- `status = 1`.
