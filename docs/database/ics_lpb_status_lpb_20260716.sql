-- Migration: Status data LPB
-- Tanggal: 2026-07-16
-- Route terkait: ics/detail_record_lpb
-- Tabel: tb_lpb

SET @col_status_lpb := (
  SELECT COUNT(*)
  FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'tb_lpb'
    AND COLUMN_NAME = 'status_lpb'
);

SET @sql_status_lpb := IF(
  @col_status_lpb = 0,
  'ALTER TABLE `tb_lpb` ADD COLUMN `status_lpb` tinyint(1) NOT NULL DEFAULT 1 AFTER `nomor_lpb`',
  'SELECT ''Kolom status_lpb sudah tersedia'' AS info'
);

PREPARE stmt FROM @sql_status_lpb;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

UPDATE tb_lpb h
LEFT JOIN (
  SELECT
    id_lpb,
    COUNT(*) AS total_rows,
    SUM(
      CASE
        WHEN harga_update_at IS NOT NULL
          AND COALESCE(harga_satuan, 0) > 0
          AND COALESCE(total_harga, 0) > 0
        THEN 0
        ELSE 1
      END
    ) AS unverified_rows
  FROM tb_lpb_detail
  GROUP BY id_lpb
) px ON px.id_lpb = h.id_lpb
SET h.status_lpb = CASE
  WHEN COALESCE(NULLIF(TRIM(h.nomor_lpb), ''), '') <> ''
    AND COALESCE(NULLIF(TRIM(h.no_invoice), ''), '') <> ''
    AND TRIM(h.no_invoice) <> '-'
    AND COALESCE(px.total_rows, 0) > 0
    AND COALESCE(px.unverified_rows, 0) = 0
    THEN 4
  WHEN COALESCE(NULLIF(TRIM(h.nomor_lpb), ''), '') <> ''
    AND COALESCE(NULLIF(TRIM(h.no_invoice), ''), '') <> ''
    AND TRIM(h.no_invoice) <> '-'
    THEN 3
  WHEN COALESCE(NULLIF(TRIM(h.nomor_lpb), ''), '') <> ''
    THEN 2
  ELSE 1
END;
