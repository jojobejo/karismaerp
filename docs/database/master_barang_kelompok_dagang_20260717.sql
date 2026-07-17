-- Migration: Tambah kolom Kelompok Dagang pada master barang komersil
-- Tanggal: 2026-07-17
-- Target: tbpo_barang
-- Tujuan: Menyimpan field Kelompok Dagang dari route master_barang.

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

-- Verification
SHOW COLUMNS FROM `tbpo_barang` LIKE 'kelompok_dagang';

-- Rollback manual jika diperlukan:
-- ALTER TABLE `tbpo_barang` DROP COLUMN `kelompok_dagang`;
