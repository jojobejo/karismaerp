-- Conditional migration untuk modul Stockopname Barang Pending.
-- MySQL/MariaDB lama tidak selalu mendukung ADD COLUMN IF NOT EXISTS.

CREATE TABLE IF NOT EXISTS `stockopname_pending` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `kode_barang` varchar(50) NOT NULL,
  `nama_barang` text NOT NULL,
  `expired_date` date NOT NULL,
  `no_lot` varchar(100) NOT NULL DEFAULT '',
  `qty` int(12) NOT NULL DEFAULT 0,
  `qty_pcs` int(12) NOT NULL DEFAULT 0,
  `qty_box` int(12) NOT NULL DEFAULT 0,
  `created_by` varchar(100) DEFAULT NULL,
  `updated_by` varchar(100) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_pending_barang_expired` (`nama_barang`(191), `expired_date`),
  KEY `idx_pending_kode_barang` (`kode_barang`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

SET @has_pending_kode_barang := (
  SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'stockopname_pending' AND COLUMN_NAME = 'kode_barang'
);
SET @sql_pending_kode_barang := IF(@has_pending_kode_barang = 0,
  'ALTER TABLE `stockopname_pending` ADD `kode_barang` varchar(50) NOT NULL DEFAULT '''' AFTER `id`',
  'SELECT ''Column stockopname_pending.kode_barang already exists'' AS info'
);
PREPARE stmt_pending_kode_barang FROM @sql_pending_kode_barang;
EXECUTE stmt_pending_kode_barang;
DEALLOCATE PREPARE stmt_pending_kode_barang;

SET @has_pending_expired_date := (
  SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'stockopname_pending' AND COLUMN_NAME = 'expired_date'
);
SET @sql_pending_expired_date := IF(@has_pending_expired_date = 0,
  'ALTER TABLE `stockopname_pending` ADD `expired_date` date NULL DEFAULT NULL AFTER `nama_barang`',
  'SELECT ''Column stockopname_pending.expired_date already exists'' AS info'
);
PREPARE stmt_pending_expired_date FROM @sql_pending_expired_date;
EXECUTE stmt_pending_expired_date;
DEALLOCATE PREPARE stmt_pending_expired_date;

SET @has_pending_exp_date_legacy := (
  SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'stockopname_pending' AND COLUMN_NAME = 'exp_date'
);
SET @sql_pending_copy_expired_date := IF(@has_pending_exp_date_legacy = 1,
  'UPDATE `stockopname_pending` SET `expired_date` = COALESCE(`expired_date`, STR_TO_DATE(`exp_date`, ''%d/%m/%Y''), STR_TO_DATE(`exp_date`, ''%Y-%m-%d'')) WHERE `expired_date` IS NULL',
  'SELECT ''Legacy column stockopname_pending.exp_date not found'' AS info'
);
PREPARE stmt_pending_copy_expired_date FROM @sql_pending_copy_expired_date;
EXECUTE stmt_pending_copy_expired_date;
DEALLOCATE PREPARE stmt_pending_copy_expired_date;

SET @has_pending_no_lot := (
  SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'stockopname_pending' AND COLUMN_NAME = 'no_lot'
);
SET @sql_pending_no_lot := IF(@has_pending_no_lot = 0,
  'ALTER TABLE `stockopname_pending` ADD `no_lot` varchar(100) NOT NULL DEFAULT '''' AFTER `expired_date`',
  'SELECT ''Column stockopname_pending.no_lot already exists'' AS info'
);
PREPARE stmt_pending_no_lot FROM @sql_pending_no_lot;
EXECUTE stmt_pending_no_lot;
DEALLOCATE PREPARE stmt_pending_no_lot;

SET @has_pending_qty_pcs := (
  SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'stockopname_pending' AND COLUMN_NAME = 'qty_pcs'
);
SET @sql_pending_qty_pcs := IF(@has_pending_qty_pcs = 0,
  'ALTER TABLE `stockopname_pending` ADD `qty_pcs` int(12) NOT NULL DEFAULT 0 AFTER `qty`',
  'SELECT ''Column stockopname_pending.qty_pcs already exists'' AS info'
);
PREPARE stmt_pending_qty_pcs FROM @sql_pending_qty_pcs;
EXECUTE stmt_pending_qty_pcs;
DEALLOCATE PREPARE stmt_pending_qty_pcs;

SET @has_pending_qty_box := (
  SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'stockopname_pending' AND COLUMN_NAME = 'qty_box'
);
SET @sql_pending_qty_box := IF(@has_pending_qty_box = 0,
  'ALTER TABLE `stockopname_pending` ADD `qty_box` int(12) NOT NULL DEFAULT 0 AFTER `qty_pcs`',
  'SELECT ''Column stockopname_pending.qty_box already exists'' AS info'
);
PREPARE stmt_pending_qty_box FROM @sql_pending_qty_box;
EXECUTE stmt_pending_qty_box;
DEALLOCATE PREPARE stmt_pending_qty_box;

SET @has_pending_created_by := (
  SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'stockopname_pending' AND COLUMN_NAME = 'created_by'
);
SET @sql_pending_created_by := IF(@has_pending_created_by = 0,
  'ALTER TABLE `stockopname_pending` ADD `created_by` varchar(100) NULL DEFAULT NULL AFTER `qty_box`',
  'SELECT ''Column stockopname_pending.created_by already exists'' AS info'
);
PREPARE stmt_pending_created_by FROM @sql_pending_created_by;
EXECUTE stmt_pending_created_by;
DEALLOCATE PREPARE stmt_pending_created_by;

SET @has_pending_updated_by := (
  SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'stockopname_pending' AND COLUMN_NAME = 'updated_by'
);
SET @sql_pending_updated_by := IF(@has_pending_updated_by = 0,
  'ALTER TABLE `stockopname_pending` ADD `updated_by` varchar(100) NULL DEFAULT NULL AFTER `created_by`',
  'SELECT ''Column stockopname_pending.updated_by already exists'' AS info'
);
PREPARE stmt_pending_updated_by FROM @sql_pending_updated_by;
EXECUTE stmt_pending_updated_by;
DEALLOCATE PREPARE stmt_pending_updated_by;

SET @has_pending_created_at := (
  SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'stockopname_pending' AND COLUMN_NAME = 'created_at'
);
SET @sql_pending_created_at := IF(@has_pending_created_at = 0,
  'ALTER TABLE `stockopname_pending` ADD `created_at` datetime NOT NULL DEFAULT current_timestamp() AFTER `updated_by`',
  'SELECT ''Column stockopname_pending.created_at already exists'' AS info'
);
PREPARE stmt_pending_created_at FROM @sql_pending_created_at;
EXECUTE stmt_pending_created_at;
DEALLOCATE PREPARE stmt_pending_created_at;

SET @has_pending_updated_at := (
  SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'stockopname_pending' AND COLUMN_NAME = 'updated_at'
);
SET @sql_pending_updated_at := IF(@has_pending_updated_at = 0,
  'ALTER TABLE `stockopname_pending` ADD `updated_at` datetime NULL DEFAULT NULL ON UPDATE current_timestamp() AFTER `created_at`',
  'SELECT ''Column stockopname_pending.updated_at already exists'' AS info'
);
PREPARE stmt_pending_updated_at FROM @sql_pending_updated_at;
EXECUTE stmt_pending_updated_at;
DEALLOCATE PREPARE stmt_pending_updated_at;

SET @has_master_pending_qty := (
  SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'stockopname_master_item' AND COLUMN_NAME = 'pending_qty'
);
SET @sql_master_pending_qty := IF(@has_master_pending_qty = 0,
  'ALTER TABLE `stockopname_master_item` ADD `pending_qty` int(12) NOT NULL DEFAULT 0 AFTER `qty_pcs`',
  'SELECT ''Column stockopname_master_item.pending_qty already exists'' AS info'
);
PREPARE stmt_master_pending_qty FROM @sql_master_pending_qty;
EXECUTE stmt_master_pending_qty;
DEALLOCATE PREPARE stmt_master_pending_qty;

SET @has_master_pending_qty_pcs := (
  SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'stockopname_master_item' AND COLUMN_NAME = 'pending_qty_pcs'
);
SET @sql_master_pending_qty_pcs := IF(@has_master_pending_qty_pcs = 0,
  'ALTER TABLE `stockopname_master_item` ADD `pending_qty_pcs` int(12) NOT NULL DEFAULT 0 AFTER `pending_qty`',
  'SELECT ''Column stockopname_master_item.pending_qty_pcs already exists'' AS info'
);
PREPARE stmt_master_pending_qty_pcs FROM @sql_master_pending_qty_pcs;
EXECUTE stmt_master_pending_qty_pcs;
DEALLOCATE PREPARE stmt_master_pending_qty_pcs;

SET @has_master_pending_qty_box := (
  SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'stockopname_master_item' AND COLUMN_NAME = 'pending_qty_box'
);
SET @sql_master_pending_qty_box := IF(@has_master_pending_qty_box = 0,
  'ALTER TABLE `stockopname_master_item` ADD `pending_qty_box` int(12) NOT NULL DEFAULT 0 AFTER `pending_qty_pcs`',
  'SELECT ''Column stockopname_master_item.pending_qty_box already exists'' AS info'
);
PREPARE stmt_master_pending_qty_box FROM @sql_master_pending_qty_box;
EXECUTE stmt_master_pending_qty_box;
DEALLOCATE PREPARE stmt_master_pending_qty_box;
