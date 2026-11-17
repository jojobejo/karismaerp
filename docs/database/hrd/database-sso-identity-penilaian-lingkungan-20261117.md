# Dokumentasi Database Pemetaan Identitas SSO — Penilaian Lingkungan

- **Tanggal**: 17 November 2026
- **Aplikasi Klien**: Penilaian Lingkungan (`penilaian_lingkungan`)
- **Berkas Migrasi**: `database/sql/hrd/portal_sso_identity_penilaian_lingkungan.sql`
- **Berkas Rollback**: `database/sql/hrd/portal_sso_identity_penilaian_lingkungan_rollback.sql`

## 1. Tujuan Skema

Menyimpan tautan antara identitas stabil pengguna di Portal KARISMA ERP (`external_sub`, misalnya `tb_users:123` atau `tb_karyawan:45`) dan akun kerja lokal pada aplikasi Penilaian Lingkungan (`tb_users` atau `tb_karyawan`), sesuai prasyarat database klien pada dokumentasi API Portal.

## 2. Struktur Tabel `tb_sso_identity`

| Kolom | Tipe | Keterangan |
|---|---|---|
| `id` | `int(11) AUTO_INCREMENT` | Primary key. |
| `provider` | `varchar(190)` | URL issuer Portal (default `https://portal.karismaerp.com`). |
| `auth_source` | `varchar(32)` | Sumber tabel identitas di Portal (`tb_users` atau `tb_karyawan`). |
| `external_sub` | `varchar(64)` | Klaim `data.user.sub` dari Portal (`{auth_source}:{user_id}`). |
| `local_auth_table` | `varchar(32)` | Tabel sumber akun lokal (`tb_users` atau `tb_karyawan`). |
| `local_user_id` | `int(11)` | ID akun lokal pada tabel `local_auth_table`. |
| `local_username` | `varchar(190)` | Snapshot `client_account_username` yang ditetapkan admin Portal. |
| `created_at` | `datetime` | Waktu pembuatan pemetaan pertama kali. |
| `updated_at` | `datetime` | Waktu pembaruan pemetaan terakhir. |

### Constraint & Index
- `uk_tb_sso_identity_provider_sub` (`UNIQUE (provider, external_sub)`): memastikan satu subject Portal hanya terhubung ke satu akun lokal.
- `uk_tb_sso_identity_provider_local` (`UNIQUE (provider, local_auth_table, local_user_id)`): memastikan satu akun lokal hanya terhubung ke satu identitas Portal pada waktu yang sama.

## 3. Prosedur Penerapan

1. Lakukan backup database aplikasi Penilaian Lingkungan terlebih dahulu.
2. Jalankan berkas migrasi:
   ```sql
   SOURCE database/sql/hrd/portal_sso_identity_penilaian_lingkungan.sql;
   ```
3. Tidak ada penyimpanan `client_secret`, `code`, atau `code_verifier` di dalam tabel database klien.
