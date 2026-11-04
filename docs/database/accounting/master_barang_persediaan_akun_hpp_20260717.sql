-- Migration: master_barang_persediaan_akun_hpp_20260717
-- Target: tbpo_barang
-- Tujuan: Menyimpan sifat barang, metode HPP, dan kode akun per barang.

SET @schema_name := DATABASE();

SET @sql := (
  SELECT IF(
    COUNT(*) = 0,
    'ALTER TABLE `tbpo_barang` ADD COLUMN `is_inventori` ENUM(''T'',''F'') NOT NULL DEFAULT ''T'' AFTER `is_lot`',
    'SELECT ''Column is_inventori already exists'' AS info'
  )
  FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_SCHEMA = @schema_name
    AND TABLE_NAME = 'tbpo_barang'
    AND COLUMN_NAME = 'is_inventori'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := (
  SELECT IF(
    COUNT(*) = 0,
    'ALTER TABLE `tbpo_barang` ADD COLUMN `is_beli` ENUM(''T'',''F'') NOT NULL DEFAULT ''T'' AFTER `is_inventori`',
    'SELECT ''Column is_beli already exists'' AS info'
  )
  FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_SCHEMA = @schema_name
    AND TABLE_NAME = 'tbpo_barang'
    AND COLUMN_NAME = 'is_beli'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := (
  SELECT IF(
    COUNT(*) = 0,
    'ALTER TABLE `tbpo_barang` ADD COLUMN `is_jual` ENUM(''T'',''F'') NOT NULL DEFAULT ''T'' AFTER `is_beli`',
    'SELECT ''Column is_jual already exists'' AS info'
  )
  FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_SCHEMA = @schema_name
    AND TABLE_NAME = 'tbpo_barang'
    AND COLUMN_NAME = 'is_jual'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := (
  SELECT IF(
    COUNT(*) = 0,
    'ALTER TABLE `tbpo_barang` ADD COLUMN `hpp_average` ENUM(''T'',''F'') NOT NULL DEFAULT ''T'' AFTER `is_jual`',
    'SELECT ''Column hpp_average already exists'' AS info'
  )
  FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_SCHEMA = @schema_name
    AND TABLE_NAME = 'tbpo_barang'
    AND COLUMN_NAME = 'hpp_average'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := (
  SELECT IF(
    COUNT(*) = 0,
    'ALTER TABLE `tbpo_barang` ADD COLUMN `hpp_fifo` ENUM(''T'',''F'') NOT NULL DEFAULT ''F'' AFTER `hpp_average`',
    'SELECT ''Column hpp_fifo already exists'' AS info'
  )
  FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_SCHEMA = @schema_name
    AND TABLE_NAME = 'tbpo_barang'
    AND COLUMN_NAME = 'hpp_fifo'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := (
  SELECT IF(
    COUNT(*) = 0,
    'ALTER TABLE `tbpo_barang` ADD COLUMN `hpp_lifo` ENUM(''T'',''F'') NOT NULL DEFAULT ''F'' AFTER `hpp_fifo`',
    'SELECT ''Column hpp_lifo already exists'' AS info'
  )
  FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_SCHEMA = @schema_name
    AND TABLE_NAME = 'tbpo_barang'
    AND COLUMN_NAME = 'hpp_lifo'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := (
  SELECT IF(
    COUNT(*) = 0,
    'ALTER TABLE `tbpo_barang` ADD COLUMN `kode_akun_harga_pokok` VARCHAR(30) DEFAULT ''51030'' AFTER `hpp_lifo`',
    'SELECT ''Column kode_akun_harga_pokok already exists'' AS info'
  )
  FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_SCHEMA = @schema_name
    AND TABLE_NAME = 'tbpo_barang'
    AND COLUMN_NAME = 'kode_akun_harga_pokok'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := (
  SELECT IF(
    COUNT(*) = 0,
    'ALTER TABLE `tbpo_barang` ADD COLUMN `kode_akun_penjualan` VARCHAR(30) DEFAULT ''41032'' AFTER `kode_akun_harga_pokok`',
    'SELECT ''Column kode_akun_penjualan already exists'' AS info'
  )
  FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_SCHEMA = @schema_name
    AND TABLE_NAME = 'tbpo_barang'
    AND COLUMN_NAME = 'kode_akun_penjualan'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := (
  SELECT IF(
    COUNT(*) = 0,
    'ALTER TABLE `tbpo_barang` ADD COLUMN `kode_akun_persediaan` VARCHAR(30) DEFAULT ''14030'' AFTER `kode_akun_penjualan`',
    'SELECT ''Column kode_akun_persediaan already exists'' AS info'
  )
  FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_SCHEMA = @schema_name
    AND TABLE_NAME = 'tbpo_barang'
    AND COLUMN_NAME = 'kode_akun_persediaan'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := (
  SELECT IF(
    COUNT(*) = 0,
    'ALTER TABLE `tbpo_barang` ADD COLUMN `kode_akun_pengiriman_beli` VARCHAR(30) DEFAULT ''51032'' AFTER `kode_akun_persediaan`',
    'SELECT ''Column kode_akun_pengiriman_beli already exists'' AS info'
  )
  FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_SCHEMA = @schema_name
    AND TABLE_NAME = 'tbpo_barang'
    AND COLUMN_NAME = 'kode_akun_pengiriman_beli'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := (
  SELECT IF(
    COUNT(*) = 0,
    'ALTER TABLE `tbpo_barang` ADD COLUMN `kode_akun_pengiriman_jual` VARCHAR(30) DEFAULT ''64030'' AFTER `kode_akun_pengiriman_beli`',
    'SELECT ''Column kode_akun_pengiriman_jual already exists'' AS info'
  )
  FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_SCHEMA = @schema_name
    AND TABLE_NAME = 'tbpo_barang'
    AND COLUMN_NAME = 'kode_akun_pengiriman_jual'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := (
  SELECT IF(
    COUNT(*) = 0,
    'ALTER TABLE `tbpo_barang` ADD COLUMN `kode_akun_retur_penjualan` VARCHAR(30) DEFAULT ''41034'' AFTER `kode_akun_pengiriman_jual`',
    'SELECT ''Column kode_akun_retur_penjualan already exists'' AS info'
  )
  FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_SCHEMA = @schema_name
    AND TABLE_NAME = 'tbpo_barang'
    AND COLUMN_NAME = 'kode_akun_retur_penjualan'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

UPDATE `tbpo_barang`
SET
  `is_inventori` = COALESCE(NULLIF(`is_inventori`, ''), 'T'),
  `is_beli` = COALESCE(NULLIF(`is_beli`, ''), 'T'),
  `is_jual` = COALESCE(NULLIF(`is_jual`, ''), 'T'),
  `hpp_average` = CASE
    WHEN `hpp_fifo` = 'T' OR `hpp_lifo` = 'T' THEN 'F'
    ELSE COALESCE(NULLIF(`hpp_average`, ''), 'T')
  END,
  `hpp_fifo` = COALESCE(NULLIF(`hpp_fifo`, ''), 'F'),
  `hpp_lifo` = COALESCE(NULLIF(`hpp_lifo`, ''), 'F'),
  `kode_akun_harga_pokok` = COALESCE(NULLIF(`kode_akun_harga_pokok`, ''), '51030'),
  `kode_akun_penjualan` = COALESCE(NULLIF(`kode_akun_penjualan`, ''), '41032'),
  `kode_akun_persediaan` = COALESCE(NULLIF(`kode_akun_persediaan`, ''), '14030'),
  `kode_akun_pengiriman_beli` = COALESCE(NULLIF(`kode_akun_pengiriman_beli`, ''), '51032'),
  `kode_akun_pengiriman_jual` = COALESCE(NULLIF(`kode_akun_pengiriman_jual`, ''), '64030'),
  `kode_akun_retur_penjualan` = COALESCE(NULLIF(`kode_akun_retur_penjualan`, ''), '41034');

SHOW COLUMNS FROM `tbpo_barang` WHERE Field IN (
  'is_inventori',
  'is_beli',
  'is_jual',
  'hpp_average',
  'hpp_fifo',
  'hpp_lifo',
  'kode_akun_harga_pokok',
  'kode_akun_penjualan',
  'kode_akun_persediaan',
  'kode_akun_pengiriman_beli',
  'kode_akun_pengiriman_jual',
  'kode_akun_retur_penjualan'
);

-- Rollback manual:
-- ALTER TABLE `tbpo_barang`
--   DROP COLUMN `kode_akun_retur_penjualan`,
--   DROP COLUMN `kode_akun_pengiriman_jual`,
--   DROP COLUMN `kode_akun_pengiriman_beli`,
--   DROP COLUMN `kode_akun_persediaan`,
--   DROP COLUMN `kode_akun_penjualan`,
--   DROP COLUMN `kode_akun_harga_pokok`,
--   DROP COLUMN `hpp_lifo`,
--   DROP COLUMN `hpp_fifo`,
--   DROP COLUMN `hpp_average`,
--   DROP COLUMN `is_jual`,
--   DROP COLUMN `is_beli`,
--   DROP COLUMN `is_inventori`;
