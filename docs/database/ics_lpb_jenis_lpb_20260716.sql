-- Migration: Tambah jenis LPB untuk tb_lpb
-- Tanggal: 2026-07-16
-- Tujuan: Menyimpan jenis LPB agar dapat ditampilkan di route ics/detail_record_lpb.

SET @column_exists := (
  SELECT COUNT(*)
  FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'tb_lpb'
    AND COLUMN_NAME = 'jenis_lpb'
);

SET @migration_sql := IF(
  @column_exists = 0,
  'ALTER TABLE `tb_lpb` ADD COLUMN `jenis_lpb` varchar(80) DEFAULT NULL AFTER `no_invoice`',
  'SELECT ''Kolom jenis_lpb sudah tersedia'' AS info'
);

PREPARE stmt FROM @migration_sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

UPDATE `tb_lpb`
SET `jenis_lpb` = NULL
WHERE `jenis_lpb` = 'LPB PO';
