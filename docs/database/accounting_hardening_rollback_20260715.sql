-- KARISMA ERP - Accounting hardening rollback (struktur tambahan saja)
-- Backup database wajib sebelum menjalankan. Data jurnal yang sudah dibuat tidak
-- dihapus agar audit trail tetap utuh.

DELETE FROM `tbkeu_mapping_akun` WHERE `posting_event` = 'GOODS_ISSUE';

ALTER TABLE `tb_lpb_detail`
  DROP INDEX IF EXISTS `idx_lpb_detail_lpb_barang`;

ALTER TABLE `tb_do`
  DROP INDEX IF EXISTS `idx_tb_do_sales_confirm`,
  DROP COLUMN IF EXISTS `sales_confirm_note`,
  DROP COLUMN IF EXISTS `sales_confirm_at`,
  DROP COLUMN IF EXISTS `sales_confirm_by`,
  DROP COLUMN IF EXISTS `sales_confirm_status`;

ALTER TABLE `tbkeu_posting_exception`
  DROP COLUMN IF EXISTS `last_occurred_at`,
  DROP COLUMN IF EXISTS `occurrence_count`;

ALTER TABLE `tbkeu_mapping_akun`
  DROP INDEX IF EXISTS `idx_tbkeu_mapping_scope`,
  DROP INDEX IF EXISTS `uk_tbkeu_mapping_rule_scope`;
ALTER TABLE `tbkeu_mapping_akun`
  ADD UNIQUE INDEX IF NOT EXISTS `uk_tbkeu_mapping_rule`
    (`source_module`,`source_type`,`posting_event`,`account_role`,`entry_side`),
  DROP COLUMN IF EXISTS `updated_by`,
  DROP COLUMN IF EXISTS `created_by`,
  DROP COLUMN IF EXISTS `scope_key`,
  DROP COLUMN IF EXISTS `scope_type`;

ALTER TABLE `tbkeu_periode_fiskal`
  DROP COLUMN IF EXISTS `reopened_at`,
  DROP COLUMN IF EXISTS `reopened_by`,
  DROP COLUMN IF EXISTS `closed_at`,
  DROP COLUMN IF EXISTS `closed_by`;
