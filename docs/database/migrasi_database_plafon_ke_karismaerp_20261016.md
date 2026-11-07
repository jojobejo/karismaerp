# Dokumentasi Migrasi Database Plafon ke KarismaERP

- **Tanggal Migrasi**: 16 Oktober 2026
- **Database Sumber**: `plafon-db` (Aplikasi Plafon - Laravel)
- **Database Tujuan**: `kiucoid_karismaerp_local` (KarismaERP)
- **Path Projek Plafon**: `D:\plafon realeese\Plafon\Plafon`
- **Script SQL**: [`migration_plafon_to_karismaerp_20261016.sql`](file:///c:/laragon/www/karismaerp/docs/database/migration_plafon_to_karismaerp_20261016.sql)

---

## 1. Ringkasan Eksekusi Migrasi Database

Tabel bisnis utama dari `plafon-db` telah berhasil disalin dan digabungkan ke dalam `kiucoid_karismaerp_local` dengan penambahan prefix `plafon_`:

| No | Tabel Sumber (`plafon-db`) | Tabel Baru (`kiucoid_karismaerp_local`) | Jumlah Baris | Status | Auto Increment |
|---|---|---|---|---|---|
| 1 | `users` | `plafon_users` | 25 | Selesai (100% Cocok) | 83 |
| 2 | `customers` | `plafon_customers` | 8.091 | Selesai (100% Cocok) | 16.007 |
| 3 | `submissions` | `plafon_submissions` | 922 | Selesai (100% Cocok) | 983 |
| 4 | `approvals` | `plafon_approvals` | 3.661 | Selesai (100% Cocok) | 3.714 |

### Tabel Laravel yang Dieliminasi (Tidak Diikutkan)
Tabel framework Laravel berikut sengaja **dieliminasi** dari `kiucoid_karismaerp_local`:
- `cache`, `cache_locks`
- `failed_jobs`, `job_batches`, `jobs`
- `migrations`
- `sessions`

---

## 2. Perubahan pada Projek Aplikasi Plafon (`D:\plafon realeese\Plafon\Plafon`)

Semua konfigurasi berikut telah diterapkan dan diuji langsung:

### A. File `.env`
Backup telah dibuat di `.env.backup_20261016`. Perubahan nilai:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=kiucoid_karismaerp_local
DB_PREFIX=plafon_
DB_USERNAME=root
DB_PASSWORD=

# Driver diubah ke file & sync agar tidak memerlukan tabel bawaan database
SESSION_DRIVER=file
QUEUE_CONNECTION=sync
CACHE_STORE=file
```

### B. File `config/database.php`
Backup telah dibuat di `config/database.php.backup_20261016`.
Prefix koneksi `mysql` dan `mariadb` telah diset:
```php
'prefix' => env('DB_PREFIX', 'plafon_'),
```

---

## 3. Hasil Uji Konektivitas Aplikasi Laravel

Pengujian query model Eloquent melalui runtime aplikasi Laravel (`test_plafon.php`):
```text
Database: kiucoid_karismaerp_local
Prefix: plafon_
Session Driver: file
Cache Store: file
Queue Connection: sync
Users count: 25 (tabel plafon_users)
Customers count: 8091 (tabel plafon_customers)
Submissions count: 922 (tabel plafon_submissions)
Approvals count: 3661 (tabel plafon_approvals)
```
Status: **SEMUA MODEL ELOQUENT BERJALAN NORMAL 100% TANPA ERROR.**
