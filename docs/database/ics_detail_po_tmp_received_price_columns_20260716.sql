-- Migration: Kolom harga exclude draft temporary penerimaan LPB
-- Tanggal: 2026-07-16
-- Route terkait: ics/detail_po
-- Tabel: tb_tmp_po_received

SET @col_harga_satuan := (
  SELECT COUNT(*)
  FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'tb_tmp_po_received'
    AND COLUMN_NAME = 'harga_satuan'
);

SET @sql_harga_satuan := IF(
  @col_harga_satuan = 0,
  'ALTER TABLE `tb_tmp_po_received` ADD COLUMN `harga_satuan` decimal(18,4) NOT NULL DEFAULT 0.0000 AFTER `expired_date`',
  'SELECT ''Kolom harga_satuan sudah tersedia'' AS info'
);

PREPARE stmt FROM @sql_harga_satuan;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @col_harga_satuan_kecil := (
  SELECT COUNT(*)
  FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'tb_tmp_po_received'
    AND COLUMN_NAME = 'harga_satuan_kecil'
);

SET @sql_harga_satuan_kecil := IF(
  @col_harga_satuan_kecil = 0,
  'ALTER TABLE `tb_tmp_po_received` ADD COLUMN `harga_satuan_kecil` decimal(18,4) NOT NULL DEFAULT 0.0000 AFTER `harga_satuan`',
  'SELECT ''Kolom harga_satuan_kecil sudah tersedia'' AS info'
);

PREPARE stmt FROM @sql_harga_satuan_kecil;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @col_total_harga := (
  SELECT COUNT(*)
  FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'tb_tmp_po_received'
    AND COLUMN_NAME = 'total_harga'
);

SET @sql_total_harga := IF(
  @col_total_harga = 0,
  'ALTER TABLE `tb_tmp_po_received` ADD COLUMN `total_harga` decimal(18,4) NOT NULL DEFAULT 0.0000 AFTER `harga_satuan_kecil`',
  'SELECT ''Kolom total_harga sudah tersedia'' AS info'
);

PREPARE stmt FROM @sql_total_harga;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
