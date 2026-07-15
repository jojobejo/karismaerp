-- Jalankan hanya sebelum ada SO baru yang memakai kolom/tabel compatibility.
DROP TABLE IF EXISTS `tbso_so_approval`;
DROP TABLE IF EXISTS `tbso_stock_reservation`;
ALTER TABLE `tbso_sales_order_detail`
  DROP INDEX IF EXISTS `idx_sod_no_faktur`,
  DROP COLUMN IF EXISTS `qty_delivered`,
  DROP COLUMN IF EXISTS `is_nego`,
  DROP COLUMN IF EXISTS `approve_by`,
  DROP COLUMN IF EXISTS `ref_no`,
  DROP COLUMN IF EXISTS `no_faktur`;
ALTER TABLE `tbso_sales_order`
  DROP INDEX IF EXISTS `uk_tbso_sales_order_no_faktur`,
  DROP COLUMN IF EXISTS `approve_by`,
  DROP COLUMN IF EXISTS `no_faktur`;
-- Status enum tidak dipersempit otomatis karena dapat merusak data baru.
