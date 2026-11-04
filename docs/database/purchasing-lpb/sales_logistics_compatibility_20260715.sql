-- KARISMA ERP - Compatibility schema Sales Order / Logistik
-- Menyamakan schema lokal dengan field yang sudah dipakai controller/model.

SET NAMES utf8mb4;

ALTER TABLE `tbso_sales_order`
  ADD COLUMN IF NOT EXISTS `no_faktur` VARCHAR(30) DEFAULT NULL AFTER `no_so`,
  ADD COLUMN IF NOT EXISTS `approve_by` VARCHAR(100) DEFAULT NULL AFTER `catatan`;

UPDATE `tbso_sales_order` so
JOIN (
  SELECT id_so, MIN(no_faktur) no_faktur
  FROM tbso_faktur_penjualan
  WHERE status <> 'cancelled'
  GROUP BY id_so
) f ON f.id_so=so.id_so
SET so.no_faktur=f.no_faktur
WHERE so.no_faktur IS NULL;

-- SO lama yang belum mempunyai faktur final tetap membutuhkan business key
-- untuk workflow edit/approval/reservasi. Prefix MIG menandai bukan faktur final.
UPDATE `tbso_sales_order`
SET `no_faktur`=CONCAT('MIG-SO-',LPAD(`id_so`,10,'0'))
WHERE `no_faktur` IS NULL;

ALTER TABLE `tbso_sales_order`
  MODIFY COLUMN `status` ENUM(
    'draft','waiting_approval','approved','open','sedang_verifikasi','siap_faktur',
    'partial','partial_delivered','in_delivery','completed','cancelled'
  ) NOT NULL DEFAULT 'draft',
  ADD UNIQUE INDEX IF NOT EXISTS `uk_tbso_sales_order_no_faktur` (`no_faktur`);

ALTER TABLE `tbso_sales_order_detail`
  ADD COLUMN IF NOT EXISTS `no_faktur` VARCHAR(30) DEFAULT NULL AFTER `no_so`,
  ADD COLUMN IF NOT EXISTS `ref_no` VARCHAR(100) DEFAULT NULL AFTER `no_lot`,
  ADD COLUMN IF NOT EXISTS `approve_by` VARCHAR(100) DEFAULT NULL AFTER `kode_akun`,
  ADD COLUMN IF NOT EXISTS `is_nego` TINYINT(1) NOT NULL DEFAULT 0 AFTER `approve_by`,
  ADD COLUMN IF NOT EXISTS `qty_delivered` DECIMAL(15,3) NOT NULL DEFAULT 0.000 AFTER `qty_outstanding`,
  ADD INDEX IF NOT EXISTS `idx_sod_no_faktur` (`no_faktur`);

UPDATE `tbso_sales_order_detail` d
LEFT JOIN (
  SELECT id_so_detail, MIN(no_faktur) no_faktur
  FROM tbso_faktur_detail
  GROUP BY id_so_detail
) fd ON fd.id_so_detail=d.id
LEFT JOIN tbso_sales_order so ON so.id_so=d.id_so
SET d.no_faktur=COALESCE(fd.no_faktur,so.no_faktur)
WHERE d.no_faktur IS NULL;

CREATE TABLE IF NOT EXISTS `tbso_stock_reservation` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `no_faktur` VARCHAR(30) NOT NULL,
  `no_so` VARCHAR(30) DEFAULT NULL,
  `id_so_detail` INT DEFAULT NULL,
  `kd_barang` VARCHAR(50) NOT NULL,
  `exp_date` VARCHAR(10) DEFAULT NULL,
  `no_lot` VARCHAR(50) DEFAULT NULL,
  `gudang_id` VARCHAR(30) NOT NULL,
  `qty_reserved` DECIMAL(15,3) NOT NULL DEFAULT 0.000,
  `status` ENUM('active','released') NOT NULL DEFAULT 'active',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_so_reservation_source` (`no_faktur`,`status`),
  KEY `idx_so_reservation_stock` (`kd_barang`,`gudang_id`,`no_lot`,`exp_date`,`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `tbso_so_approval` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `no_faktur` VARCHAR(30) NOT NULL,
  `no_so` VARCHAR(30) DEFAULT NULL,
  `tipe` VARCHAR(30) NOT NULL DEFAULT 'harga',
  `keterangan` VARCHAR(500) DEFAULT NULL,
  `req_by` VARCHAR(100) DEFAULT NULL,
  `approve_by` VARCHAR(100) DEFAULT NULL,
  `status` ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `req_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `note` VARCHAR(500) DEFAULT NULL,
  `act_by` VARCHAR(100) DEFAULT NULL,
  `act_at` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_so_approval_queue` (`approve_by`,`status`,`req_at`),
  KEY `idx_so_approval_faktur` (`no_faktur`,`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

SELECT COUNT(*) AS missing_sales_order_faktur
FROM tbso_sales_order WHERE no_faktur IS NULL;
SELECT COUNT(*) AS missing_sales_order_detail_faktur
FROM tbso_sales_order_detail WHERE no_faktur IS NULL;
