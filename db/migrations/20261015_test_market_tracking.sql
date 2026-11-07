-- Migrasi: Gudang virtual dan tracking realisasi penjualan Test Market
-- File: db/migrations/20261015_test_market_tracking.sql

INSERT INTO `tb_gudang` (`nama_gudang`, `tipe`, `is_active`, `created_at`)
SELECT 'Gdg. Test Market di Kios', 'INDUK', 1, NOW()
WHERE NOT EXISTS (
  SELECT 1 FROM `tb_gudang` WHERE `nama_gudang` = 'Gdg. Test Market di Kios'
);

CREATE TABLE IF NOT EXISTS `tb_test_market_position` (
  `id_position` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_faktur` INT NOT NULL,
  `id_faktur_detail` INT NOT NULL,
  `id_so` INT DEFAULT NULL,
  `no_faktur` VARCHAR(30) NOT NULL,
  `no_so` VARCHAR(30) DEFAULT NULL,
  `kd_customer` VARCHAR(50) NOT NULL,
  `customer_name` VARCHAR(150) DEFAULT NULL,
  `kd_barang` VARCHAR(50) NOT NULL,
  `nama_barang` VARCHAR(200) DEFAULT NULL,
  `no_lot` VARCHAR(50) DEFAULT NULL,
  `expired_date` DATE DEFAULT NULL,
  `satuan` VARCHAR(20) DEFAULT NULL,
  `gudang_asal_id` VARCHAR(30) DEFAULT NULL,
  `gudang_test_market_id` INT NOT NULL,
  `qty_titip` DECIMAL(15,3) NOT NULL DEFAULT 0.000,
  `qty_terjual` DECIMAL(15,3) NOT NULL DEFAULT 0.000,
  `qty_sisa` DECIMAL(15,3) NOT NULL DEFAULT 0.000,
  `harga_jual` DECIMAL(18,2) NOT NULL DEFAULT 0.00,
  `harga_pokok` DECIMAL(18,2) NOT NULL DEFAULT 0.00,
  `status` ENUM('DI_KIOS','SEBAGIAN','HABIS','DIBATALKAN') NOT NULL DEFAULT 'DI_KIOS',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_position`),
  UNIQUE KEY `uk_test_market_faktur_detail` (`id_faktur_detail`),
  KEY `idx_test_market_customer` (`kd_customer`, `status`),
  KEY `idx_test_market_barang` (`kd_barang`, `status`),
  KEY `idx_test_market_faktur` (`id_faktur`, `no_faktur`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `tb_test_market_realisasi` (
  `id_realisasi` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_position` BIGINT UNSIGNED NOT NULL,
  `id_pembayaran` INT UNSIGNED NOT NULL,
  `id_faktur` INT NOT NULL,
  `id_faktur_detail` INT NOT NULL,
  `no_faktur` VARCHAR(30) NOT NULL,
  `kd_customer` VARCHAR(50) NOT NULL,
  `kd_barang` VARCHAR(50) NOT NULL,
  `qty` DECIMAL(15,3) NOT NULL,
  `harga_satuan` DECIMAL(18,2) NOT NULL DEFAULT 0.00,
  `subtotal` DECIMAL(18,2) NOT NULL DEFAULT 0.00,
  `tanggal_realisasi` DATE NOT NULL,
  `status` ENUM('POSTED','DIBATALKAN') NOT NULL DEFAULT 'POSTED',
  `created_by` VARCHAR(100) DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_realisasi`),
  UNIQUE KEY `uk_test_market_pembayaran` (`id_pembayaran`),
  KEY `idx_test_market_realisasi_position` (`id_position`, `status`),
  KEY `idx_test_market_realisasi_tanggal` (`tanggal_realisasi`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

SET @source_collation = (
  SELECT `TABLE_COLLATION` FROM information_schema.TABLES
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tbso_faktur_detail'
);
SET @source_charset = SUBSTRING_INDEX(@source_collation, '_', 1);
SET @align_test_market_position = CONCAT('ALTER TABLE `tb_test_market_position` CONVERT TO CHARACTER SET ', @source_charset, ' COLLATE ', @source_collation);
PREPARE statement_align_test_market_position FROM @align_test_market_position;
EXECUTE statement_align_test_market_position;
DEALLOCATE PREPARE statement_align_test_market_position;

SET @payment_collation = (
  SELECT `TABLE_COLLATION` FROM information_schema.TABLES
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tbkeu_pembayaran_faktur'
);
SET @payment_charset = SUBSTRING_INDEX(@payment_collation, '_', 1);
SET @align_test_market_realisasi = CONCAT('ALTER TABLE `tb_test_market_realisasi` CONVERT TO CHARACTER SET ', @payment_charset, ' COLLATE ', @payment_collation);
PREPARE statement_align_test_market_realisasi FROM @align_test_market_realisasi;
EXECUTE statement_align_test_market_realisasi;
DEALLOCATE PREPARE statement_align_test_market_realisasi;

SET @add_payment_detail = IF(
  (SELECT COUNT(*) FROM information_schema.COLUMNS
   WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tbkeu_pembayaran_faktur' AND COLUMN_NAME = 'id_faktur_detail') = 0,
  'ALTER TABLE `tbkeu_pembayaran_faktur` ADD COLUMN `id_faktur_detail` INT NULL AFTER `id_settlement`',
  'SELECT 1'
);
PREPARE statement_add_payment_detail FROM @add_payment_detail;
EXECUTE statement_add_payment_detail;
DEALLOCATE PREPARE statement_add_payment_detail;

SET @add_payment_qty = IF(
  (SELECT COUNT(*) FROM information_schema.COLUMNS
   WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tbkeu_pembayaran_faktur' AND COLUMN_NAME = 'qty_realisasi') = 0,
  'ALTER TABLE `tbkeu_pembayaran_faktur` ADD COLUMN `qty_realisasi` DECIMAL(15,3) NULL AFTER `id_faktur_detail`',
  'SELECT 1'
);
PREPARE statement_add_payment_qty FROM @add_payment_qty;
EXECUTE statement_add_payment_qty;
DEALLOCATE PREPARE statement_add_payment_qty;
