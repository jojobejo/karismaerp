-- Migration: Workflow UNPOST/POST dan log aktivitas LPB
-- Tanggal: 2026-07-19
-- Route terkait: ics/detail_record_lpb
-- Tabel: tb_lpb, tb_lpb_log

SET @col_status_lpb := (
  SELECT COUNT(*)
  FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'tb_lpb'
    AND COLUMN_NAME = 'status_lpb'
);

SET @sql_status_lpb := IF(
  @col_status_lpb = 0,
  'ALTER TABLE `tb_lpb` ADD COLUMN `status_lpb` tinyint(1) NOT NULL DEFAULT 1 AFTER `nomor_lpb`',
  'SELECT ''Kolom status_lpb sudah tersedia'' AS info'
);

PREPARE stmt FROM @sql_status_lpb;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

UPDATE `tb_lpb`
SET `status_lpb` = 1
WHERE `status_lpb` IS NULL OR `status_lpb` NOT IN (0, 1);

ALTER TABLE `tb_lpb_log`
  MODIFY `action_type` varchar(50) NOT NULL;

SET @col_log_id_lpb := (
  SELECT COUNT(*)
  FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'tb_lpb_log'
    AND COLUMN_NAME = 'id_lpb'
);

SET @sql_log_id_lpb := IF(
  @col_log_id_lpb = 0,
  'ALTER TABLE `tb_lpb_log` ADD COLUMN `id_lpb` int(11) NULL AFTER `id_log`',
  'SELECT ''Kolom id_lpb sudah tersedia'' AS info'
);

PREPARE stmt FROM @sql_log_id_lpb;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @col_status_before := (
  SELECT COUNT(*)
  FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'tb_lpb_log'
    AND COLUMN_NAME = 'status_before'
);

SET @sql_status_before := IF(
  @col_status_before = 0,
  'ALTER TABLE `tb_lpb_log` ADD COLUMN `status_before` varchar(20) NULL AFTER `action_type`',
  'SELECT ''Kolom status_before sudah tersedia'' AS info'
);

PREPARE stmt FROM @sql_status_before;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @col_status_after := (
  SELECT COUNT(*)
  FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'tb_lpb_log'
    AND COLUMN_NAME = 'status_after'
);

SET @sql_status_after := IF(
  @col_status_after = 0,
  'ALTER TABLE `tb_lpb_log` ADD COLUMN `status_after` varchar(20) NULL AFTER `status_before`',
  'SELECT ''Kolom status_after sudah tersedia'' AS info'
);

PREPARE stmt FROM @sql_status_after;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @col_data_before := (
  SELECT COUNT(*)
  FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'tb_lpb_log'
    AND COLUMN_NAME = 'data_before'
);

SET @sql_data_before := IF(
  @col_data_before = 0,
  'ALTER TABLE `tb_lpb_log` ADD COLUMN `data_before` text NULL AFTER `status_after`',
  'SELECT ''Kolom data_before sudah tersedia'' AS info'
);

PREPARE stmt FROM @sql_data_before;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @col_data_after := (
  SELECT COUNT(*)
  FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'tb_lpb_log'
    AND COLUMN_NAME = 'data_after'
);

SET @sql_data_after := IF(
  @col_data_after = 0,
  'ALTER TABLE `tb_lpb_log` ADD COLUMN `data_after` text NULL AFTER `data_before`',
  'SELECT ''Kolom data_after sudah tersedia'' AS info'
);

PREPARE stmt FROM @sql_data_after;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

