ALTER TABLE `stockopname_master_item`
  ADD COLUMN IF NOT EXISTS `qrcode` varchar(255) DEFAULT NULL AFTER `created_at`,
  ADD COLUMN IF NOT EXISTS `barcode` varchar(255) DEFAULT NULL AFTER `qrcode`;

CREATE INDEX IF NOT EXISTS `idx_stockopname_master_item_qrcode` ON `stockopname_master_item` (`qrcode`);
CREATE INDEX IF NOT EXISTS `idx_stockopname_master_item_barcode` ON `stockopname_master_item` (`barcode`);
