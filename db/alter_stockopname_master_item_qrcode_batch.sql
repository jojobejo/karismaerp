ALTER TABLE `stockopname_master_item`
  ADD COLUMN IF NOT EXISTS `qrcode_value` VARCHAR(255) NULL DEFAULT NULL AFTER `barcode`,
  ADD COLUMN IF NOT EXISTS `qrcode_file` VARCHAR(255) NULL DEFAULT NULL AFTER `qrcode_value`,
  ADD COLUMN IF NOT EXISTS `qrcode_status` ENUM('PENDING','PROCESS','DONE','FAILED') NOT NULL DEFAULT 'PENDING' AFTER `qrcode_file`,
  ADD COLUMN IF NOT EXISTS `qrcode_retry_flag` TINYINT(1) NOT NULL DEFAULT 0 AFTER `qrcode_status`,
  ADD COLUMN IF NOT EXISTS `qrcode_attempt_count` INT NOT NULL DEFAULT 0 AFTER `qrcode_retry_flag`,
  ADD COLUMN IF NOT EXISTS `qrcode_error_message` TEXT NULL AFTER `qrcode_attempt_count`,
  ADD COLUMN IF NOT EXISTS `qrcode_generated_at` DATETIME NULL DEFAULT NULL AFTER `qrcode_error_message`,
  ADD COLUMN IF NOT EXISTS `qrcode_updated_at` DATETIME NULL DEFAULT NULL AFTER `qrcode_generated_at`;

CREATE INDEX IF NOT EXISTS `idx_stockopname_qrcode_status`
  ON `stockopname_master_item` (`qrcode_status`);

CREATE INDEX IF NOT EXISTS `idx_stockopname_qrcode_retry`
  ON `stockopname_master_item` (`qrcode_status`, `qrcode_retry_flag`);
