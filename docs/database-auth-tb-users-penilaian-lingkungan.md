# Dokumentasi Database Auth tb_users

Tanggal development: 2026-06-30

## Tujuan Struktur Database

Tabel `tb_users` dijadikan sumber auth untuk kebutuhan HRD, khususnya user dengan `jobdesk_hrd = inputer_laporan` yang harus masuk ke modul `penilaian_lingkungan`.

## File SQL

Script database tersedia di:

`database/sql/tb_users_auth_penilaian_lingkungan.sql`

## Kolom Yang Dibutuhkan

| Kolom | Tipe | Fungsi |
| --- | --- | --- |
| `password` | `varchar(255)` | Password login. Disarankan berisi hash dari `password_hash()`. |
| `level` | `int(11)` | Level akses sederhana untuk session `lv` / `akses_lv`. |
| `jobdesk_hrd` | `varchar(100)` | Role HRD modular. Nilai `inputer_laporan` diarahkan ke `penilaian_lingkungan`. |
| `status` | `tinyint(1)` | Status aktif user. `1` aktif, selain itu diblokir. |
| `default_redirect` | `varchar(180)` | Route default opsional untuk user `tb_users`. |
| `last_login` | `datetime` | Timestamp login terakhir. |
| `created_at` | `datetime` | Timestamp data dibuat. |
| `updated_at` | `datetime` | Timestamp data terakhir diperbarui. |

## Index

| Index | Kolom | Tujuan |
| --- | --- | --- |
| `idx_tb_users_username` | `username` | Mempercepat lookup login. |
| `idx_tb_users_jobdesk_hrd` | `jobdesk_hrd` | Mempercepat filter role HRD. |
| `idx_tb_users_status` | `status` | Mempercepat filter user aktif/nonaktif. |

## Mapping Session

| Session | Sumber `tb_users` |
| --- | --- |
| `id` | `id` |
| `nik` | `nik` |
| `username` | `username` |
| `departemen` | `departement` |
| `lv`, `akses_lv`, `akses_lv_id` | `level` |
| `jobdesk` | `jabatan` |
| `jobdesk_hrd` | `jobdesk_hrd` |
| `nama`, `nama_user` | `nama_lngkp` |
| `auth_source` | literal `tb_users` |

## Contoh Update Role

```sql
UPDATE tb_users
SET jobdesk_hrd = 'inputer_laporan',
    default_redirect = 'penilaian_lingkungan',
    status = 1
WHERE username = 'username_user';
```

## Catatan Keamanan

Password baru sebaiknya tidak disimpan plaintext. Gunakan hash PHP:

```php
password_hash('password_user', PASSWORD_DEFAULT);
```

Verifikasi plaintext dipertahankan sebagai kompatibilitas untuk data lama. Jika login plaintext berhasil, aplikasi otomatis mengubah nilai `password` user tersebut menjadi hash PHP pada tabel sumber login.
