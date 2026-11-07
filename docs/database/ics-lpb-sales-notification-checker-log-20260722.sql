-- Migration: Notifikasi penjualan LPB dan metadata checker log
-- Tanggal: 2026-07-22
-- Tabel: tb_lpb, tb_lpb_log

SET @col_lpb_checker_name := (
  SELECT COUNT(*)
  FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'tb_lpb'
    AND COLUMN_NAME = 'checker_name'
);

SET @sql_lpb_checker_name := IF(
  @col_lpb_checker_name = 0,
  'ALTER TABLE `tb_lpb` ADD COLUMN `checker_name` varchar(100) NULL AFTER `keterangan`',
  'SELECT ''Kolom tb_lpb.checker_name sudah tersedia'' AS info'
);

PREPARE stmt FROM @sql_lpb_checker_name;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @col_lpb_checker_by := (
  SELECT COUNT(*)
  FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'tb_lpb'
    AND COLUMN_NAME = 'checker_by'
);

SET @sql_lpb_checker_by := IF(
  @col_lpb_checker_by = 0,
  'ALTER TABLE `tb_lpb` ADD COLUMN `checker_by` varchar(50) NULL AFTER `checker_name`',
  'SELECT ''Kolom tb_lpb.checker_by sudah tersedia'' AS info'
);

PREPARE stmt FROM @sql_lpb_checker_by;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @col_lpb_checker_at := (
  SELECT COUNT(*)
  FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'tb_lpb'
    AND COLUMN_NAME = 'checker_at'
);

SET @sql_lpb_checker_at := IF(
  @col_lpb_checker_at = 0,
  'ALTER TABLE `tb_lpb` ADD COLUMN `checker_at` datetime NULL AFTER `checker_by`',
  'SELECT ''Kolom tb_lpb.checker_at sudah tersedia'' AS info'
);

PREPARE stmt FROM @sql_lpb_checker_at;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @col_log_checker_name := (
  SELECT COUNT(*)
  FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'tb_lpb_log'
    AND COLUMN_NAME = 'id_lpb'
);

SET @sql_log_id_lpb := IF(
  @col_log_checker_name = 0,
  'ALTER TABLE `tb_lpb_log` ADD COLUMN `id_lpb` int(11) NULL AFTER `id_log`',
  'SELECT ''Kolom tb_lpb_log.id_lpb sudah tersedia'' AS info'
);

PREPARE stmt FROM @sql_log_id_lpb;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @col_log_checker_name := (
  SELECT COUNT(*)
  FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'tb_lpb_log'
    AND COLUMN_NAME = 'checker_name'
);

SET @sql_log_checker_name := IF(
  @col_log_checker_name = 0,
  'ALTER TABLE `tb_lpb_log` ADD COLUMN `checker_name` varchar(100) NULL AFTER `dilakukan_oleh`',
  'SELECT ''Kolom tb_lpb_log.checker_name sudah tersedia'' AS info'
);

PREPARE stmt FROM @sql_log_checker_name;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @col_log_checker_by := (
  SELECT COUNT(*)
  FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'tb_lpb_log'
    AND COLUMN_NAME = 'checker_by'
);

SET @sql_log_checker_by := IF(
  @col_log_checker_by = 0,
  'ALTER TABLE `tb_lpb_log` ADD COLUMN `checker_by` varchar(50) NULL AFTER `checker_name`',
  'SELECT ''Kolom tb_lpb_log.checker_by sudah tersedia'' AS info'
);

PREPARE stmt FROM @sql_log_checker_by;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

UPDATE tb_lpb_log log
INNER JOIN tb_lpb lpb ON lpb.id_lpb = log.id_lpb
SET
  log.checker_name = COALESCE(log.checker_name, lpb.checker_name),
  log.checker_by = COALESCE(log.checker_by, lpb.checker_by)
WHERE log.id_lpb IS NOT NULL;
