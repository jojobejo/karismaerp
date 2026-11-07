-- Migrasi LPB Manual Purchasing
-- Tanggal: 2026-07-23

SET @db_name := DATABASE();

SET @sql := (
    SELECT IF(
        COUNT(*) = 0,
        'ALTER TABLE `tb_lpb` ADD COLUMN `source_type` varchar(20) NOT NULL DEFAULT ''PO'' AFTER `input_at`',
        'SELECT ''Kolom tb_lpb.source_type sudah tersedia'' AS info'
    )
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = @db_name
      AND TABLE_NAME = 'tb_lpb'
      AND COLUMN_NAME = 'source_type'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql := (
    SELECT IF(
        COUNT(*) = 0,
        'ALTER TABLE `tb_lpb` ADD COLUMN `manual_ref_no` varchar(50) NULL AFTER `source_type`',
        'SELECT ''Kolom tb_lpb.manual_ref_no sudah tersedia'' AS info'
    )
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = @db_name
      AND TABLE_NAME = 'tb_lpb'
      AND COLUMN_NAME = 'manual_ref_no'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

CREATE TABLE IF NOT EXISTS `tb_lpb_manual_log` (
    `id_log` int(11) NOT NULL AUTO_INCREMENT,
    `id_lpb` int(11) DEFAULT NULL,
    `manual_ref_no` varchar(50) DEFAULT NULL,
    `action_type` varchar(50) NOT NULL,
    `status` varchar(20) NOT NULL,
    `message` text DEFAULT NULL,
    `payload` longtext DEFAULT NULL,
    `created_by` varchar(100) DEFAULT NULL,
    `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `ip_address` varchar(45) DEFAULT NULL,
    `user_agent` varchar(255) DEFAULT NULL,
    PRIMARY KEY (`id_log`),
    KEY `idx_lpb_manual_log_ref` (`manual_ref_no`),
    KEY `idx_lpb_manual_log_lpb` (`id_lpb`),
    KEY `idx_lpb_manual_log_status` (`status`),
    KEY `idx_lpb_manual_log_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

UPDATE `tb_lpb`
SET `source_type` = 'PO'
WHERE `source_type` IS NULL OR TRIM(`source_type`) = '';
