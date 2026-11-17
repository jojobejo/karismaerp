# Dokumentasi Database Pemetaan Identitas SSO — POS Security & HRD

- **Tanggal**: 17 November 2026
- **Aplikasi Klien**: POS Security & HRD (`pos_security`)
- **Berkas Migrasi**: `database/sql/pos_security/portal_sso_identity_pos_security.sql`
- **Berkas Rollback**: `database/sql/pos_security/portal_sso_identity_pos_security_rollback.sql`

## 1. Tujuan Skema

1. Menyimpan tautan antara identitas stabil pengguna di Portal KARISMA ERP (`external_sub`, misalnya `tb_users:123` atau `tb_karyawan:45`) dan akun kerja lokal pada aplikasi POS Security (`tb_user.id`), sesuai prasyarat database klien pada dokumentasi API Portal.
2. Menyediakan tabel audit log autentikasi (`tb_login_log`) untuk mencatat keberhasilan maupun penolakan login tanpa menyimpan `client_secret`, `code`, atau `code_verifier`.

## 2. Struktur Tabel `tb_sso_identity`

| Kolom | Tipe | Keterangan |
|---|---|---|
| `id` | `int(11) AUTO_INCREMENT` | Primary key. |
| `provider` | `varchar(190)` | URL issuer Portal (default `https://portal.karismaerp.com`). |
| `auth_source` | `varchar(32)` | Sumber tabel identitas di Portal (`tb_users` atau `tb_karyawan`). |
| `external_sub` | `varchar(64)` | Klaim `data.user.sub` dari Portal (`{auth_source}:{user_id}`). |
| `local_auth_table` | `varchar(32)` | Tabel sumber akun lokal (`tb_user`). |
| `local_user_id` | `varchar(190)` | ID akun lokal pada `tb_user.id`. |
| `local_username` | `varchar(190)` | Snapshot `client_account_username` yang ditetapkan admin Portal. |
| `created_at` | `datetime` | Waktu pembuatan pemetaan pertama kali. |
| `updated_at` | `datetime` | Waktu pembaruan pemetaan terakhir. |

### Constraint & Index `tb_sso_identity`
- `uk_tb_sso_identity_provider_sub` (`UNIQUE (provider, external_sub)`): memastikan satu subject Portal hanya terhubung ke satu akun lokal.
- `uk_tb_sso_identity_provider_local` (`UNIQUE (provider, local_auth_table, local_user_id)`): memastikan satu akun lokal hanya terhubung ke satu identitas Portal pada waktu yang sama.

## 3. Struktur Tabel `tb_login_log`

| Kolom | Tipe | Keterangan |
|---|---|---|
| `id` | `int(11) AUTO_INCREMENT` | Primary key. |
| `user_id` | `varchar(190)` | Primary key `id` pada `tb_user`. |
| `kode_user` | `varchar(64)` | Kode `kode_user` pada `tb_user`. |
| `username` | `varchar(190)` | Username akun lokal yang diuji. |
| `nama_user` | `varchar(255)` | Nama pengguna lokal. |
| `departemen` | `varchar(100)` | Departemen akun lokal. |
| `akses_lv` | `int(11)` | Level hak akses lokal (`1` s/d `5`). |
| `status` | `varchar(32)` | Status autentikasi (`success`, `failed`, `blocked`). |
| `message` | `varchar(255)` | Ringkasan audit non-sensitif. |
| `ip_address` | `varchar(45)` | Alamat IP klien. |
| `user_agent` | `varchar(255)` | User-Agent browser klien. |
| `login_at` | `datetime` | Waktu kejadian autentikasi. |

## 4. Prosedur Penerapan

1. Lakukan backup database aplikasi POS Security (`hrd.karismaerp.com`) terlebih dahulu.
2. Jalankan berkas migrasi:
   ```sql
   SOURCE database/sql/pos_security/portal_sso_identity_pos_security.sql;
   ```
3. Tidak ada penyimpanan `client_secret`, `code`, atau `code_verifier` di dalam tabel database klien.
