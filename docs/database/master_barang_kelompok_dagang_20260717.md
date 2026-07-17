# Database Master Barang - Kelompok Dagang

Tanggal: 2026-07-17

## Status Perubahan Database

Perubahan database disiapkan sebagai SQL migration manual pada:

`docs/database/master_barang_kelompok_dagang_20260717.sql`

Migration menambahkan kolom baru:

- tabel: `tbpo_barang`
- kolom: `kelompok_dagang`
- tipe: `text`
- default: `NULL`
- posisi: setelah `merk_barang`
- mode: idempotent, aman dijalankan ulang karena dicek lewat `INFORMATION_SCHEMA.COLUMNS`

## SQL Utama

```sql
SET @column_exists := (
  SELECT COUNT(*)
  FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'tbpo_barang'
    AND COLUMN_NAME = 'kelompok_dagang'
);

SET @migration_sql := IF(
  @column_exists = 0,
  'ALTER TABLE `tbpo_barang` ADD COLUMN `kelompok_dagang` text DEFAULT NULL AFTER `merk_barang`',
  'SELECT ''Kolom kelompok_dagang sudah tersedia'' AS info'
);

PREPARE stmt FROM @migration_sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
```

## Dampak Data

Tidak ada perubahan data lama. Kolom baru akan kosong untuk barang yang sudah ada sampai user memilih field `Kelompok Dagang` dari route `master_barang`.

Setelah dropdown `Kelompok Dagang` aktif, nilai yang disimpan ke `tbpo_barang.kelompok_dagang` adalah `tbkeu_kelompok_dagang.NOINDEX`, bukan teks deskripsi. Label tampilannya dibaca dari `tbkeu_kelompok_dagang.DESKRIPSI`.

## Rollback

```sql
ALTER TABLE `tbpo_barang` DROP COLUMN `kelompok_dagang`;
```
