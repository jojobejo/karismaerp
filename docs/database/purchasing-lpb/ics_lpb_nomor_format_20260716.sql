-- Migration: Nomor LPB berbasis jenis
-- Tanggal: 2026-07-16
-- Route terkait: ics/detail_record_lpb
-- Format:
-- LPB CP                         : 72600001
-- LPB Benih                      : 72600001B
-- LPB Konsinyasi                 : 72600002K
-- LPB Barang Non Pajak (A)       : A72600001
-- LPB Promosi                    : X72600001
-- LPB Barang Pengganti Retur (RA): RA72600001

SET @jenis_exists := (
  SELECT COUNT(*)
  FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'tb_lpb'
    AND COLUMN_NAME = 'jenis_lpb'
);

SET @jenis_sql := IF(
  @jenis_exists = 0,
  'ALTER TABLE `tb_lpb` ADD COLUMN `jenis_lpb` varchar(80) DEFAULT NULL AFTER `no_invoice`',
  'SELECT ''Kolom jenis_lpb sudah tersedia'' AS info'
);

PREPARE stmt FROM @jenis_sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

ALTER TABLE `tb_lpb`
  MODIFY COLUMN `jenis_lpb` varchar(80) DEFAULT NULL;

SET @nomor_exists := (
  SELECT COUNT(*)
  FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'tb_lpb'
    AND COLUMN_NAME = 'nomor_lpb'
);

SET @nomor_sql := IF(
  @nomor_exists = 0,
  'ALTER TABLE `tb_lpb` ADD COLUMN `nomor_lpb` varchar(30) DEFAULT NULL AFTER `jenis_lpb`',
  'SELECT ''Kolom nomor_lpb sudah tersedia'' AS info'
);

PREPARE stmt FROM @nomor_sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

UPDATE `tb_lpb`
SET `jenis_lpb` = NULL
WHERE `jenis_lpb` = 'LPB PO';

SET @seq := 0;
UPDATE `tb_lpb` l
JOIN (
  SELECT
    id_lpb,
    (@seq := @seq + 1) AS nomor_urut
  FROM `tb_lpb`
  WHERE `jenis_lpb` = 'LPB CP'
    AND (`nomor_lpb` IS NULL OR TRIM(`nomor_lpb`) = '')
  ORDER BY id_lpb ASC
) x ON x.id_lpb = l.id_lpb
SET l.nomor_lpb = CONCAT(MONTH(CURDATE()), DATE_FORMAT(CURDATE(), '%y'), LPAD(x.nomor_urut, 5, '0'));
