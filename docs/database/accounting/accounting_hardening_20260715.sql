-- KARISMA ERP - Accounting hardening (UP)
-- Jalankan setelah tiga migration accounting tanggal 2026-07-13.
-- Tidak membaca/menulis empat tabel PO legacy yang dilarang oleh MASTER_SPECS.

SET NAMES utf8mb4;

ALTER TABLE `tbkeu_periode_fiskal`
  ADD COLUMN IF NOT EXISTS `closed_by` BIGINT DEFAULT NULL AFTER `updated_at`,
  ADD COLUMN IF NOT EXISTS `closed_at` DATETIME DEFAULT NULL AFTER `closed_by`,
  ADD COLUMN IF NOT EXISTS `reopened_by` BIGINT DEFAULT NULL AFTER `closed_at`,
  ADD COLUMN IF NOT EXISTS `reopened_at` DATETIME DEFAULT NULL AFTER `reopened_by`;

ALTER TABLE `tbkeu_mapping_akun`
  ADD COLUMN IF NOT EXISTS `scope_type` VARCHAR(30) NOT NULL DEFAULT 'GLOBAL' AFTER `source_type`,
  ADD COLUMN IF NOT EXISTS `scope_key` VARCHAR(100) NOT NULL DEFAULT '*' AFTER `scope_type`,
  ADD COLUMN IF NOT EXISTS `created_by` BIGINT DEFAULT NULL AFTER `keterangan`,
  ADD COLUMN IF NOT EXISTS `updated_by` BIGINT DEFAULT NULL AFTER `created_by`;

ALTER TABLE `tbkeu_mapping_akun`
  DROP INDEX IF EXISTS `uk_tbkeu_mapping_rule`;
ALTER TABLE `tbkeu_mapping_akun`
  ADD UNIQUE INDEX IF NOT EXISTS `uk_tbkeu_mapping_rule_scope`
    (`source_module`,`source_type`,`scope_type`,`scope_key`,`posting_event`,`account_role`,`entry_side`),
  ADD INDEX IF NOT EXISTS `idx_tbkeu_mapping_scope`
    (`scope_type`,`scope_key`,`posting_event`,`is_active`);

ALTER TABLE `tbkeu_posting_exception`
  ADD COLUMN IF NOT EXISTS `occurrence_count` INT UNSIGNED NOT NULL DEFAULT 1 AFTER `retry_count`,
  ADD COLUMN IF NOT EXISTS `last_occurred_at` DATETIME DEFAULT NULL AFTER `occurrence_count`;

-- Kolom ini sudah dipakai workflow DO/Sales di aplikasi, tetapi belum ada pada
-- master schema lokal. Penambahan bersifat kompatibel dengan data lama.
ALTER TABLE `tb_do`
  ADD COLUMN IF NOT EXISTS `sales_confirm_status` ENUM('pending','siap','belum_siap') DEFAULT NULL AFTER `status`,
  ADD COLUMN IF NOT EXISTS `sales_confirm_by` VARCHAR(100) DEFAULT NULL AFTER `sales_confirm_status`,
  ADD COLUMN IF NOT EXISTS `sales_confirm_at` DATETIME DEFAULT NULL AFTER `sales_confirm_by`,
  ADD COLUMN IF NOT EXISTS `sales_confirm_note` TEXT DEFAULT NULL AFTER `sales_confirm_at`,
  ADD INDEX IF NOT EXISTS `idx_tb_do_sales_confirm` (`sales_confirm_status`,`status`);

ALTER TABLE `tb_lpb_detail`
  ADD INDEX IF NOT EXISTS `idx_lpb_detail_lpb_barang` (`id_lpb`,`kd_barang`);

-- Status REVERSED versi lama membuat jurnal asli hilang dari laporan. Jurnal
-- asli dikembalikan ke POSTED; reversed_at tetap menjadi penanda audit/UI.
UPDATE `tbkeu_jurnal`
SET `status` = 'POSTED'
WHERE `status` = 'REVERSED'
  AND `reversed_at` IS NOT NULL
  AND EXISTS (
    SELECT 1 FROM (
      SELECT `reversal_of_journal_id` FROM `tbkeu_jurnal`
      WHERE `reversal_of_journal_id` IS NOT NULL
    ) r WHERE r.`reversal_of_journal_id` = `tbkeu_jurnal`.`id_jurnal`
  );

-- Pengakuan pendapatan dan pengeluaran persediaan adalah dua event terpisah.
INSERT INTO `tbkeu_mapping_akun`
  (`source_module`,`source_type`,`scope_type`,`scope_key`,`posting_event`,`account_role`,`entry_side`,`id_akun`,`priority`,`is_active`,`keterangan`)
SELECT '*','*','GLOBAL','*','GOODS_ISSUE',m.`account_role`,m.`entry_side`,m.`id_akun`,m.`priority`,m.`is_active`,
       'Mapping pengeluaran persediaan dari faktur final'
FROM `tbkeu_mapping_akun` m
WHERE m.`posting_event` = 'SALES_INVOICE'
  AND m.`account_role` IN ('COGS','INVENTORY')
ON DUPLICATE KEY UPDATE
  `id_akun` = VALUES(`id_akun`), `priority` = VALUES(`priority`), `is_active` = VALUES(`is_active`),
  `keterangan` = VALUES(`keterangan`);

-- Checklist cepat pasca migration; semua hasil idealnya 0 kecuali mapping_count=2.
SELECT COUNT(*) AS reversed_status_remaining
FROM `tbkeu_jurnal` WHERE `status` = 'REVERSED' AND `reversed_at` IS NOT NULL;
SELECT COUNT(*) AS overlapping_period_pairs
FROM `tbkeu_periode_fiskal` a
JOIN `tbkeu_periode_fiskal` b ON a.id_periode < b.id_periode
 AND a.tanggal_mulai <= b.tanggal_selesai
 AND a.tanggal_selesai >= b.tanggal_mulai;
SELECT COUNT(*) AS goods_issue_mapping_count
FROM `tbkeu_mapping_akun` WHERE `posting_event` = 'GOODS_ISSUE' AND `is_active` = 1;

