-- Migration: Verifikasi harga detail LPB
-- Tanggal: 2026-07-16
-- Route terkait: ics/detail_record_lpb
-- Tabel: tb_lpb_detail

SET @col_harga_verified_by := (
  SELECT COUNT(*)
  FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'tb_lpb_detail'
    AND COLUMN_NAME = 'harga_verified_by'
);

SET @sql_harga_verified_by := IF(
  @col_harga_verified_by = 0,
  'ALTER TABLE `tb_lpb_detail` ADD COLUMN `harga_verified_by` varchar(100) NULL AFTER `harga_update_at`',
  'SELECT ''Kolom harga_verified_by sudah tersedia'' AS info'
);

PREPARE stmt FROM @sql_harga_verified_by;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @col_harga_verified_at := (
  SELECT COUNT(*)
  FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'tb_lpb_detail'
    AND COLUMN_NAME = 'harga_verified_at'
);

SET @sql_harga_verified_at := IF(
  @col_harga_verified_at = 0,
  'ALTER TABLE `tb_lpb_detail` ADD COLUMN `harga_verified_at` datetime NULL AFTER `harga_verified_by`',
  'SELECT ''Kolom harga_verified_at sudah tersedia'' AS info'
);

PREPARE stmt FROM @sql_harga_verified_at;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

UPDATE tb_lpb_detail
SET harga_verified_by = harga_update_by,
    harga_verified_at = harga_update_at
WHERE harga_verified_at IS NULL
  AND harga_update_at IS NOT NULL
  AND COALESCE(harga_satuan, 0) > 0
  AND COALESCE(total_harga, 0) > 0;

UPDATE tb_lpb h
LEFT JOIN (
  SELECT
    id_lpb,
    COUNT(*) AS total_rows,
    SUM(
      CASE
        WHEN harga_verified_at IS NOT NULL
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
END
WHERE EXISTS (
  SELECT 1
  FROM INFORMATION_SCHEMA.COLUMNS c
  WHERE c.TABLE_SCHEMA = DATABASE()
    AND c.TABLE_NAME = 'tb_lpb'
    AND c.COLUMN_NAME = 'status_lpb'
);
