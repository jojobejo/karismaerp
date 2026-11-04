-- Migration: Kolom update harga detail LPB
-- Tanggal: 2026-07-16
-- Route terkait: ics/detail_record_lpb
-- Tabel: tb_lpb_detail

SET @col_harga_satuan_sebelumnya := (
  SELECT COUNT(*)
  FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'tb_lpb_detail'
    AND COLUMN_NAME = 'harga_satuan_sebelumnya'
);

SET @sql_harga_satuan_sebelumnya := IF(
  @col_harga_satuan_sebelumnya = 0,
  'ALTER TABLE `tb_lpb_detail` ADD COLUMN `harga_satuan_sebelumnya` decimal(18,4) NOT NULL DEFAULT 0.0000 AFTER `input_at`',
  'SELECT ''Kolom harga_satuan_sebelumnya sudah tersedia'' AS info'
);

PREPARE stmt FROM @sql_harga_satuan_sebelumnya;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @col_total_harga_sebelumnya := (
  SELECT COUNT(*)
  FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'tb_lpb_detail'
    AND COLUMN_NAME = 'total_harga_sebelumnya'
);

SET @sql_total_harga_sebelumnya := IF(
  @col_total_harga_sebelumnya = 0,
  'ALTER TABLE `tb_lpb_detail` ADD COLUMN `total_harga_sebelumnya` decimal(18,4) NOT NULL DEFAULT 0.0000 AFTER `harga_satuan_sebelumnya`',
  'SELECT ''Kolom total_harga_sebelumnya sudah tersedia'' AS info'
);

PREPARE stmt FROM @sql_total_harga_sebelumnya;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @col_harga_satuan := (
  SELECT COUNT(*)
  FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'tb_lpb_detail'
    AND COLUMN_NAME = 'harga_satuan'
);

SET @sql_harga_satuan := IF(
  @col_harga_satuan = 0,
  'ALTER TABLE `tb_lpb_detail` ADD COLUMN `harga_satuan` decimal(18,4) NOT NULL DEFAULT 0.0000 AFTER `total_harga_sebelumnya`',
  'SELECT ''Kolom harga_satuan sudah tersedia'' AS info'
);

PREPARE stmt FROM @sql_harga_satuan;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @col_total_harga := (
  SELECT COUNT(*)
  FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'tb_lpb_detail'
    AND COLUMN_NAME = 'total_harga'
);

SET @sql_total_harga := IF(
  @col_total_harga = 0,
  'ALTER TABLE `tb_lpb_detail` ADD COLUMN `total_harga` decimal(18,4) NOT NULL DEFAULT 0.0000 AFTER `harga_satuan`',
  'SELECT ''Kolom total_harga sudah tersedia'' AS info'
);

PREPARE stmt FROM @sql_total_harga;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @col_harga_update_by := (
  SELECT COUNT(*)
  FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'tb_lpb_detail'
    AND COLUMN_NAME = 'harga_update_by'
);

SET @sql_harga_update_by := IF(
  @col_harga_update_by = 0,
  'ALTER TABLE `tb_lpb_detail` ADD COLUMN `harga_update_by` varchar(100) NULL AFTER `total_harga`',
  'SELECT ''Kolom harga_update_by sudah tersedia'' AS info'
);

PREPARE stmt FROM @sql_harga_update_by;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @col_harga_update_at := (
  SELECT COUNT(*)
  FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'tb_lpb_detail'
    AND COLUMN_NAME = 'harga_update_at'
);

SET @sql_harga_update_at := IF(
  @col_harga_update_at = 0,
  'ALTER TABLE `tb_lpb_detail` ADD COLUMN `harga_update_at` datetime NULL AFTER `harga_update_by`',
  'SELECT ''Kolom harga_update_at sudah tersedia'' AS info'
);

PREPARE stmt FROM @sql_harga_update_at;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
