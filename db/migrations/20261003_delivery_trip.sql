CREATE TABLE IF NOT EXISTS `tb_delivery_trip` (
  `id_trip` INT NOT NULL AUTO_INCREMENT,
  `kode_trip` VARCHAR(40) NOT NULL,
  `kd_rute` VARCHAR(50) NOT NULL,
  `tgl_pengiriman` DATE NOT NULL,
  `nolambung` VARCHAR(100) DEFAULT NULL,
  `driver` VARCHAR(100) DEFAULT NULL,
  `kapasitas_tonase` DECIMAL(15,3) NOT NULL DEFAULT 7.000,
  `kapasitas_kubikasi` DECIMAL(15,5) NOT NULL DEFAULT 9.00000,
  `status` ENUM('DRAFT','VERIFIKASI','SIAP_LOADING','PROSES_LOADING','MENUNGGU_TAMBAHAN','PROSES_TAMBAHAN','DITUTUP','BERANGKAT','SELESAI') NOT NULL DEFAULT 'DRAFT',
  `additional_load_deadline` DATETIME DEFAULT NULL,
  `opened_additional_by` VARCHAR(100) DEFAULT NULL,
  `opened_additional_at` DATETIME DEFAULT NULL,
  `closed_by` VARCHAR(100) DEFAULT NULL,
  `closed_at` DATETIME DEFAULT NULL,
  `created_by` VARCHAR(100) NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_trip`),
  UNIQUE KEY `uk_delivery_trip_code` (`kode_trip`),
  KEY `idx_delivery_trip_route_status` (`kd_rute`,`status`),
  KEY `idx_delivery_trip_date` (`tgl_pengiriman`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE `tbso_sales_order` ADD COLUMN `id_trip` INT NULL AFTER `kd_rute`;
ALTER TABLE `tbso_sales_order` ADD COLUMN `is_additional_load` TINYINT(1) NOT NULL DEFAULT 0 AFTER `id_trip`;
ALTER TABLE `tbso_faktur_penjualan` ADD COLUMN `id_trip` INT NULL AFTER `id_so`;
ALTER TABLE `tbso_faktur_penjualan` ADD COLUMN `is_additional_load` TINYINT(1) NOT NULL DEFAULT 0 AFTER `id_trip`;
ALTER TABLE `tb_do` ADD COLUMN `id_trip` INT NULL AFTER `kd_do`;
ALTER TABLE `tb_do` ADD COLUMN `is_additional_do` TINYINT(1) NOT NULL DEFAULT 0 AFTER `id_trip`;
ALTER TABLE `tb_loading_lk` ADD COLUMN `id_trip` INT NULL AFTER `id`;
ALTER TABLE `tb_loading_kk` ADD COLUMN `id_trip` INT NULL AFTER `id`;

CREATE INDEX `idx_so_trip` ON `tbso_sales_order` (`id_trip`);
CREATE INDEX `idx_faktur_trip` ON `tbso_faktur_penjualan` (`id_trip`);
CREATE INDEX `idx_do_trip` ON `tb_do` (`id_trip`);
CREATE INDEX `idx_loading_lk_trip` ON `tb_loading_lk` (`id_trip`);
CREATE INDEX `idx_loading_kk_trip` ON `tb_loading_kk` (`id_trip`);

-- Backfill aktivitas aktif agar proses yang sedang berjalan tetap memperoleh identitas trip.
INSERT IGNORE INTO `tb_delivery_trip`
(`kode_trip`,`kd_rute`,`tgl_pengiriman`,`status`,`created_by`)
SELECT CONCAT('TRIP/', x.kd_rute, '/', DATE_FORMAT(x.tgl, '%d%m%y'), '/LEGACY'),
       x.kd_rute, x.tgl,
       IF(MAX(x.status = 'PROSES_LOADING') = 1, 'PROSES_LOADING', 'SIAP_LOADING'),
       'migration'
FROM (
    SELECT UPPER(`keterangan`) kd_rute, `tgl`, `status` FROM `tb_loading_lk` WHERE `is_archived` = 0 AND `status` <> 'DONE'
    UNION ALL
    SELECT UPPER(`keterangan`) kd_rute, `tgl`, `status` FROM `tb_loading_kk` WHERE `is_archived` = 0 AND `status` <> 'DONE'
) x
GROUP BY x.kd_rute, x.tgl;

UPDATE `tb_loading_lk` l JOIN `tb_delivery_trip` t
  ON t.kd_rute = UPPER(l.keterangan) AND t.tgl_pengiriman = l.tgl
SET l.id_trip = t.id_trip WHERE l.id_trip IS NULL AND l.is_archived = 0;
UPDATE `tb_loading_kk` l JOIN `tb_delivery_trip` t
  ON t.kd_rute = UPPER(l.keterangan) AND t.tgl_pengiriman = l.tgl
SET l.id_trip = t.id_trip WHERE l.id_trip IS NULL AND l.is_archived = 0;
UPDATE `tbso_sales_order` so JOIN `tb_delivery_trip` t ON t.kd_rute = UPPER(so.kd_rute)
SET so.id_trip = t.id_trip
WHERE so.id_trip IS NULL AND so.status IN ('sedang_verifikasi','siap_faktur','partial','completed')
  AND NOT EXISTS (SELECT 1 FROM `tbso_faktur_penjualan` f JOIN `tb_detail_do` d ON d.kd_faktur = f.no_faktur WHERE f.id_so = so.id_so);
UPDATE `tbso_faktur_penjualan` f JOIN `tbso_sales_order` so ON so.id_so = f.id_so
SET f.id_trip = so.id_trip, f.is_additional_load = so.is_additional_load
WHERE f.id_trip IS NULL AND so.id_trip IS NOT NULL
  AND NOT EXISTS (SELECT 1 FROM `tb_detail_do` d WHERE d.kd_faktur = f.no_faktur);
