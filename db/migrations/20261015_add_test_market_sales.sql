-- Migrasi: Penanda Barang Test Market pada Sales Order dan Faktur Penjualan
-- File: db/migrations/20261015_add_test_market_sales.sql

SET @add_so_test_market = IF(
  (SELECT COUNT(*) FROM information_schema.COLUMNS
   WHERE TABLE_SCHEMA = DATABASE()
     AND TABLE_NAME = 'tbso_sales_order'
     AND COLUMN_NAME = 'is_test_market') = 0,
  'ALTER TABLE `tbso_sales_order` ADD COLUMN `is_test_market` TINYINT(1) NOT NULL DEFAULT 0 AFTER `is_faktur_z`',
  'SELECT 1'
);
PREPARE statement_add_so_test_market FROM @add_so_test_market;
EXECUTE statement_add_so_test_market;
DEALLOCATE PREPARE statement_add_so_test_market;

SET @add_faktur_test_market = IF(
  (SELECT COUNT(*) FROM information_schema.COLUMNS
   WHERE TABLE_SCHEMA = DATABASE()
     AND TABLE_NAME = 'tbso_faktur_penjualan'
     AND COLUMN_NAME = 'is_test_market') = 0,
  'ALTER TABLE `tbso_faktur_penjualan` ADD COLUMN `is_test_market` TINYINT(1) NOT NULL DEFAULT 0 AFTER `cara_pembayaran`',
  'SELECT 1'
);
PREPARE statement_add_faktur_test_market FROM @add_faktur_test_market;
EXECUTE statement_add_faktur_test_market;
DEALLOCATE PREPARE statement_add_faktur_test_market;
