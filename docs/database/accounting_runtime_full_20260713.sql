-- KARISMA ERP - Accounting Runtime Full Transaction Layer
-- Tanggal: 2026-07-13
-- Scope: mapping akun, audit log, posting exception, dummy source, dan seed mapping.
-- Jalankan setelah:
-- 1. docs/database/accounting_jurnal_accounts_20260713.sql
-- 2. docs/database/accounting_jurnal_master_options_20260713.sql
-- 3. docs/database/accounting_general_ledger_journal_20260713.sql
--
-- Tabel Purchase Order di luar scope:
-- tbpo_transaksi, tbpo_transaksi_tmp, tbpo_transaksi_trashbin, tbpo_akun_tr
-- tidak dibaca, tidak ditulis, tidak diubah, dan tidak menjadi dependency.

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

ALTER TABLE `tbkeu_akun`
  ADD COLUMN IF NOT EXISTS `is_transaction_eligible` TINYINT(1) NOT NULL DEFAULT 1 AFTER `allow_manual_journal`;

UPDATE `tbkeu_akun`
SET `is_transaction_eligible` = CASE WHEN `tipe_akun` = 'POSTING' AND `is_active` = 1 THEN 1 ELSE 0 END;

CREATE TABLE IF NOT EXISTS `tbkeu_mapping_akun` (
  `id_mapping` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `source_module` VARCHAR(50) NOT NULL DEFAULT '*',
  `source_type` VARCHAR(50) NOT NULL DEFAULT '*',
  `posting_event` VARCHAR(50) NOT NULL,
  `account_role` VARCHAR(80) NOT NULL,
  `entry_side` ENUM('DEBIT','KREDIT','ANY') NOT NULL DEFAULT 'ANY',
  `id_akun` BIGINT UNSIGNED NOT NULL,
  `priority` SMALLINT UNSIGNED NOT NULL DEFAULT 100,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `keterangan` VARCHAR(255) DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_mapping`),
  UNIQUE KEY `uk_tbkeu_mapping_rule` (`source_module`, `source_type`, `posting_event`, `account_role`, `entry_side`),
  KEY `idx_tbkeu_mapping_lookup` (`posting_event`, `account_role`, `entry_side`, `is_active`),
  KEY `idx_tbkeu_mapping_akun` (`id_akun`),
  CONSTRAINT `fk_tbkeu_mapping_akun`
    FOREIGN KEY (`id_akun`) REFERENCES `tbkeu_akun` (`id_akun`)
    ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `tbkeu_jurnal_log` (
  `id_log` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_jurnal` BIGINT UNSIGNED NOT NULL,
  `action` VARCHAR(50) NOT NULL,
  `message` VARCHAR(500) DEFAULT NULL,
  `created_by` BIGINT DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_log`),
  KEY `idx_tbkeu_jurnal_log_jurnal` (`id_jurnal`),
  KEY `idx_tbkeu_jurnal_log_action` (`action`),
  CONSTRAINT `fk_tbkeu_jurnal_log_jurnal`
    FOREIGN KEY (`id_jurnal`) REFERENCES `tbkeu_jurnal` (`id_jurnal`)
    ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `tbkeu_posting_exception` (
  `id_exception` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `source_module` VARCHAR(50) DEFAULT NULL,
  `source_type` VARCHAR(50) DEFAULT NULL,
  `source_id` VARCHAR(100) DEFAULT NULL,
  `source_no` VARCHAR(100) DEFAULT NULL,
  `posting_event` VARCHAR(50) DEFAULT NULL,
  `error_code` VARCHAR(80) NOT NULL,
  `error_message` VARCHAR(1000) NOT NULL,
  `payload_json` LONGTEXT DEFAULT NULL,
  `status` ENUM('OPEN','RESOLVED','IGNORED') NOT NULL DEFAULT 'OPEN',
  `id_jurnal` BIGINT UNSIGNED DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `resolved_by` BIGINT DEFAULT NULL,
  `resolved_at` DATETIME DEFAULT NULL,
  `resolution_note` VARCHAR(500) DEFAULT NULL,
  PRIMARY KEY (`id_exception`),
  KEY `idx_tbkeu_exception_status` (`status`, `created_at`),
  KEY `idx_tbkeu_exception_source` (`source_module`, `source_type`, `source_id`, `posting_event`),
  KEY `idx_tbkeu_exception_jurnal` (`id_jurnal`),
  CONSTRAINT `fk_tbkeu_exception_jurnal`
    FOREIGN KEY (`id_jurnal`) REFERENCES `tbkeu_jurnal` (`id_jurnal`)
    ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `tbkeu_nomor_dokumen` (
  `id_nomor` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `kode_jenis_jurnal` VARCHAR(20) NOT NULL,
  `periode_yyyymm` CHAR(6) NOT NULL,
  `last_number` INT UNSIGNED NOT NULL DEFAULT 0,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_nomor`),
  UNIQUE KEY `uk_tbkeu_nomor_dokumen` (`kode_jenis_jurnal`, `periode_yyyymm`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `tbkeu_saldo_awal_akun` (
  `id_saldo_awal` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_akun` BIGINT UNSIGNED NOT NULL,
  `tanggal_saldo` DATE NOT NULL,
  `debit` DECIMAL(19,4) NOT NULL DEFAULT 0.0000,
  `kredit` DECIMAL(19,4) NOT NULL DEFAULT 0.0000,
  `keterangan` VARCHAR(255) DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_saldo_awal`),
  UNIQUE KEY `uk_tbkeu_saldo_awal` (`id_akun`, `tanggal_saldo`),
  CONSTRAINT `fk_tbkeu_saldo_awal_akun`
    FOREIGN KEY (`id_akun`) REFERENCES `tbkeu_akun` (`id_akun`)
    ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `tbkeu_dummy_source` (
  `id_dummy` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `posting_event` VARCHAR(50) NOT NULL,
  `source_no` VARCHAR(100) NOT NULL,
  `tanggal_transaksi` DATE NOT NULL,
  `partner_name` VARCHAR(150) DEFAULT NULL,
  `product_name` VARCHAR(150) DEFAULT NULL,
  `qty` DECIMAL(18,2) NOT NULL DEFAULT 0.00,
  `unit_name` VARCHAR(30) DEFAULT NULL,
  `unit_price` DECIMAL(19,4) NOT NULL DEFAULT 0.0000,
  `warehouse_name` VARCHAR(100) DEFAULT NULL,
  `amount` DECIMAL(19,4) NOT NULL DEFAULT 0.0000,
  `tax` DECIMAL(19,4) NOT NULL DEFAULT 0.0000,
  `cogs` DECIMAL(19,4) NOT NULL DEFAULT 0.0000,
  `keterangan` VARCHAR(255) DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_dummy`),
  UNIQUE KEY `uk_tbkeu_dummy_source_no` (`source_no`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

ALTER TABLE `tbkeu_dummy_source`
  ADD COLUMN IF NOT EXISTS `partner_name` VARCHAR(150) DEFAULT NULL AFTER `tanggal_transaksi`,
  ADD COLUMN IF NOT EXISTS `product_name` VARCHAR(150) DEFAULT NULL AFTER `partner_name`,
  ADD COLUMN IF NOT EXISTS `qty` DECIMAL(18,2) NOT NULL DEFAULT 0.00 AFTER `product_name`,
  ADD COLUMN IF NOT EXISTS `unit_name` VARCHAR(30) DEFAULT NULL AFTER `qty`,
  ADD COLUMN IF NOT EXISTS `unit_price` DECIMAL(19,4) NOT NULL DEFAULT 0.0000 AFTER `unit_name`,
  ADD COLUMN IF NOT EXISTS `warehouse_name` VARCHAR(100) DEFAULT NULL AFTER `unit_price`;

SET FOREIGN_KEY_CHECKS = 1;

-- =========================================================
-- SEED AKUN TAMBAHAN UNTUK RUNTIME TEST
-- Kode akun hanya berada di SQL seed/migration, bukan di source code aplikasi.
-- =========================================================

INSERT INTO `tbkeu_akun`
  (`kode_akun`, `nama_akun`, `id_klasifikasi`, `parent_id`, `level_akun`, `saldo_normal`, `tipe_akun`, `tipe_kontrol`, `allow_manual_journal`, `is_transaction_eligible`, `is_active`)
SELECT '1500', 'PPN Masukan', 1, h.id_akun, 2, 'DEBIT', 'POSTING', 'PAJAK_MASUKAN', 0, 1, 1 FROM tbkeu_akun h WHERE h.kode_akun = '1000'
ON DUPLICATE KEY UPDATE `nama_akun` = VALUES(`nama_akun`), `parent_id` = VALUES(`parent_id`), `tipe_kontrol` = VALUES(`tipe_kontrol`), `is_transaction_eligible` = 1, `is_active` = 1;

INSERT INTO `tbkeu_akun`
  (`kode_akun`, `nama_akun`, `id_klasifikasi`, `parent_id`, `level_akun`, `saldo_normal`, `tipe_akun`, `tipe_kontrol`, `allow_manual_journal`, `is_transaction_eligible`, `is_active`)
SELECT '2300', 'PPN Keluaran', 2, h.id_akun, 2, 'KREDIT', 'POSTING', 'PAJAK_KELUARAN', 0, 1, 1 FROM tbkeu_akun h WHERE h.kode_akun = '2000'
ON DUPLICATE KEY UPDATE `nama_akun` = VALUES(`nama_akun`), `parent_id` = VALUES(`parent_id`), `tipe_kontrol` = VALUES(`tipe_kontrol`), `is_transaction_eligible` = 1, `is_active` = 1;

INSERT INTO `tbkeu_akun`
  (`kode_akun`, `nama_akun`, `id_klasifikasi`, `parent_id`, `level_akun`, `saldo_normal`, `tipe_akun`, `tipe_kontrol`, `allow_manual_journal`, `is_transaction_eligible`, `is_active`)
SELECT '4200', 'Retur Penjualan', 4, h.id_akun, 2, 'DEBIT', 'POSTING', 'NONE', 0, 1, 1 FROM tbkeu_akun h WHERE h.kode_akun = '4000'
ON DUPLICATE KEY UPDATE `nama_akun` = VALUES(`nama_akun`), `parent_id` = VALUES(`parent_id`), `is_transaction_eligible` = 1, `is_active` = 1;

INSERT INTO `tbkeu_akun`
  (`kode_akun`, `nama_akun`, `id_klasifikasi`, `parent_id`, `level_akun`, `saldo_normal`, `tipe_akun`, `tipe_kontrol`, `allow_manual_journal`, `is_transaction_eligible`, `is_active`)
SELECT '6100', 'Kerugian Stock', 6, h.id_akun, 2, 'DEBIT', 'POSTING', 'NONE', 0, 1, 1 FROM tbkeu_akun h WHERE h.kode_akun = '6000'
ON DUPLICATE KEY UPDATE `nama_akun` = VALUES(`nama_akun`), `parent_id` = VALUES(`parent_id`), `is_transaction_eligible` = 1, `is_active` = 1;

INSERT INTO `tbkeu_akun`
  (`kode_akun`, `nama_akun`, `id_klasifikasi`, `parent_id`, `level_akun`, `saldo_normal`, `tipe_akun`, `tipe_kontrol`, `allow_manual_journal`, `is_transaction_eligible`, `is_active`)
SELECT '8100', 'Keuntungan Stock', 8, h.id_akun, 2, 'KREDIT', 'POSTING', 'NONE', 0, 1, 1 FROM tbkeu_akun h WHERE h.kode_akun = '8000'
ON DUPLICATE KEY UPDATE `nama_akun` = VALUES(`nama_akun`), `parent_id` = VALUES(`parent_id`), `is_transaction_eligible` = 1, `is_active` = 1;

-- Pastikan akun dasar eligible untuk auto-posting.
UPDATE `tbkeu_akun`
SET `is_transaction_eligible` = 1, `is_active` = 1
WHERE `kode_akun` IN ('1100','1200','1300','1400','2100','2200','4100','5100','1500','2300','4200','6100','8100');

-- =========================================================
-- SEED MAPPING AKUN DEFAULT
-- =========================================================

INSERT INTO `tbkeu_mapping_akun`
  (`source_module`, `source_type`, `posting_event`, `account_role`, `entry_side`, `id_akun`, `priority`, `is_active`, `keterangan`)
SELECT '*', '*', 'SALES_INVOICE', 'ACCOUNT_RECEIVABLE', 'DEBIT', a.id_akun, 100, 1, 'Default piutang sales invoice' FROM tbkeu_akun a WHERE a.kode_akun = '1300'
ON DUPLICATE KEY UPDATE `id_akun` = VALUES(`id_akun`), `is_active` = 1;

INSERT INTO `tbkeu_mapping_akun` (`source_module`, `source_type`, `posting_event`, `account_role`, `entry_side`, `id_akun`, `priority`, `is_active`, `keterangan`)
SELECT '*', '*', 'SALES_INVOICE', 'SALES_REVENUE', 'KREDIT', a.id_akun, 100, 1, 'Default pendapatan penjualan' FROM tbkeu_akun a WHERE a.kode_akun = '4100'
ON DUPLICATE KEY UPDATE `id_akun` = VALUES(`id_akun`), `is_active` = 1;

INSERT INTO `tbkeu_mapping_akun` (`source_module`, `source_type`, `posting_event`, `account_role`, `entry_side`, `id_akun`, `priority`, `is_active`, `keterangan`)
SELECT '*', '*', 'SALES_INVOICE', 'VAT_OUTPUT', 'KREDIT', a.id_akun, 100, 1, 'Default PPN keluaran' FROM tbkeu_akun a WHERE a.kode_akun = '2300'
ON DUPLICATE KEY UPDATE `id_akun` = VALUES(`id_akun`), `is_active` = 1;

INSERT INTO `tbkeu_mapping_akun` (`source_module`, `source_type`, `posting_event`, `account_role`, `entry_side`, `id_akun`, `priority`, `is_active`, `keterangan`)
SELECT '*', '*', 'SALES_INVOICE', 'COGS', 'DEBIT', a.id_akun, 100, 1, 'Default HPP' FROM tbkeu_akun a WHERE a.kode_akun = '5100'
ON DUPLICATE KEY UPDATE `id_akun` = VALUES(`id_akun`), `is_active` = 1;

INSERT INTO `tbkeu_mapping_akun` (`source_module`, `source_type`, `posting_event`, `account_role`, `entry_side`, `id_akun`, `priority`, `is_active`, `keterangan`)
SELECT '*', '*', 'SALES_INVOICE', 'INVENTORY', 'KREDIT', a.id_akun, 100, 1, 'Default persediaan keluar' FROM tbkeu_akun a WHERE a.kode_akun = '1400'
ON DUPLICATE KEY UPDATE `id_akun` = VALUES(`id_akun`), `is_active` = 1;

INSERT INTO `tbkeu_mapping_akun` (`source_module`, `source_type`, `posting_event`, `account_role`, `entry_side`, `id_akun`, `priority`, `is_active`, `keterangan`)
SELECT '*', '*', 'PURCHASE_INVOICE', 'GRNI', 'DEBIT', a.id_akun, 100, 1, 'Default GRNI invoice pembelian' FROM tbkeu_akun a WHERE a.kode_akun = '2200'
ON DUPLICATE KEY UPDATE `id_akun` = VALUES(`id_akun`), `is_active` = 1;

INSERT INTO `tbkeu_mapping_akun` (`source_module`, `source_type`, `posting_event`, `account_role`, `entry_side`, `id_akun`, `priority`, `is_active`, `keterangan`)
SELECT '*', '*', 'PURCHASE_INVOICE', 'VAT_INPUT', 'DEBIT', a.id_akun, 100, 1, 'Default PPN masukan' FROM tbkeu_akun a WHERE a.kode_akun = '1500'
ON DUPLICATE KEY UPDATE `id_akun` = VALUES(`id_akun`), `is_active` = 1;

INSERT INTO `tbkeu_mapping_akun` (`source_module`, `source_type`, `posting_event`, `account_role`, `entry_side`, `id_akun`, `priority`, `is_active`, `keterangan`)
SELECT '*', '*', 'PURCHASE_INVOICE', 'ACCOUNT_PAYABLE', 'KREDIT', a.id_akun, 100, 1, 'Default hutang invoice pembelian' FROM tbkeu_akun a WHERE a.kode_akun = '2100'
ON DUPLICATE KEY UPDATE `id_akun` = VALUES(`id_akun`), `is_active` = 1;

INSERT INTO `tbkeu_mapping_akun` (`source_module`, `source_type`, `posting_event`, `account_role`, `entry_side`, `id_akun`, `priority`, `is_active`, `keterangan`)
SELECT '*', '*', 'GOODS_RECEIPT', 'INVENTORY', 'DEBIT', a.id_akun, 100, 1, 'Default persediaan LPB' FROM tbkeu_akun a WHERE a.kode_akun = '1400'
ON DUPLICATE KEY UPDATE `id_akun` = VALUES(`id_akun`), `is_active` = 1;

INSERT INTO `tbkeu_mapping_akun` (`source_module`, `source_type`, `posting_event`, `account_role`, `entry_side`, `id_akun`, `priority`, `is_active`, `keterangan`)
SELECT '*', '*', 'GOODS_RECEIPT', 'GRNI', 'KREDIT', a.id_akun, 100, 1, 'Default GRNI LPB' FROM tbkeu_akun a WHERE a.kode_akun = '2200'
ON DUPLICATE KEY UPDATE `id_akun` = VALUES(`id_akun`), `is_active` = 1;

INSERT INTO `tbkeu_mapping_akun` (`source_module`, `source_type`, `posting_event`, `account_role`, `entry_side`, `id_akun`, `priority`, `is_active`, `keterangan`)
SELECT '*', '*', 'CUSTOMER_PAYMENT', 'CASH_BANK', 'DEBIT', a.id_akun, 100, 1, 'Default kas/bank penerimaan customer' FROM tbkeu_akun a WHERE a.kode_akun = '1200'
ON DUPLICATE KEY UPDATE `id_akun` = VALUES(`id_akun`), `is_active` = 1;

INSERT INTO `tbkeu_mapping_akun` (`source_module`, `source_type`, `posting_event`, `account_role`, `entry_side`, `id_akun`, `priority`, `is_active`, `keterangan`)
SELECT '*', '*', 'CUSTOMER_PAYMENT', 'ACCOUNT_RECEIVABLE', 'KREDIT', a.id_akun, 100, 1, 'Default piutang penerimaan customer' FROM tbkeu_akun a WHERE a.kode_akun = '1300'
ON DUPLICATE KEY UPDATE `id_akun` = VALUES(`id_akun`), `is_active` = 1;

INSERT INTO `tbkeu_mapping_akun` (`source_module`, `source_type`, `posting_event`, `account_role`, `entry_side`, `id_akun`, `priority`, `is_active`, `keterangan`)
SELECT '*', '*', 'SUPPLIER_PAYMENT', 'ACCOUNT_PAYABLE', 'DEBIT', a.id_akun, 100, 1, 'Default hutang pembayaran supplier' FROM tbkeu_akun a WHERE a.kode_akun = '2100'
ON DUPLICATE KEY UPDATE `id_akun` = VALUES(`id_akun`), `is_active` = 1;

INSERT INTO `tbkeu_mapping_akun` (`source_module`, `source_type`, `posting_event`, `account_role`, `entry_side`, `id_akun`, `priority`, `is_active`, `keterangan`)
SELECT '*', '*', 'SUPPLIER_PAYMENT', 'CASH_BANK', 'KREDIT', a.id_akun, 100, 1, 'Default kas/bank pembayaran supplier' FROM tbkeu_akun a WHERE a.kode_akun = '1200'
ON DUPLICATE KEY UPDATE `id_akun` = VALUES(`id_akun`), `is_active` = 1;

INSERT INTO `tbkeu_mapping_akun` (`source_module`, `source_type`, `posting_event`, `account_role`, `entry_side`, `id_akun`, `priority`, `is_active`, `keterangan`)
SELECT '*', '*', 'SALES_RETURN', 'SALES_RETURN', 'DEBIT', a.id_akun, 100, 1, 'Default retur penjualan' FROM tbkeu_akun a WHERE a.kode_akun = '4200'
ON DUPLICATE KEY UPDATE `id_akun` = VALUES(`id_akun`), `is_active` = 1;

INSERT INTO `tbkeu_mapping_akun` (`source_module`, `source_type`, `posting_event`, `account_role`, `entry_side`, `id_akun`, `priority`, `is_active`, `keterangan`)
SELECT '*', '*', 'SALES_RETURN', 'VAT_OUTPUT', 'DEBIT', a.id_akun, 100, 1, 'Default PPN retur penjualan' FROM tbkeu_akun a WHERE a.kode_akun = '2300'
ON DUPLICATE KEY UPDATE `id_akun` = VALUES(`id_akun`), `is_active` = 1;

INSERT INTO `tbkeu_mapping_akun` (`source_module`, `source_type`, `posting_event`, `account_role`, `entry_side`, `id_akun`, `priority`, `is_active`, `keterangan`)
SELECT '*', '*', 'SALES_RETURN', 'ACCOUNT_RECEIVABLE', 'KREDIT', a.id_akun, 100, 1, 'Default piutang retur penjualan' FROM tbkeu_akun a WHERE a.kode_akun = '1300'
ON DUPLICATE KEY UPDATE `id_akun` = VALUES(`id_akun`), `is_active` = 1;

INSERT INTO `tbkeu_mapping_akun` (`source_module`, `source_type`, `posting_event`, `account_role`, `entry_side`, `id_akun`, `priority`, `is_active`, `keterangan`)
SELECT '*', '*', 'SALES_RETURN', 'INVENTORY', 'DEBIT', a.id_akun, 100, 1, 'Default persediaan retur penjualan' FROM tbkeu_akun a WHERE a.kode_akun = '1400'
ON DUPLICATE KEY UPDATE `id_akun` = VALUES(`id_akun`), `is_active` = 1;

INSERT INTO `tbkeu_mapping_akun` (`source_module`, `source_type`, `posting_event`, `account_role`, `entry_side`, `id_akun`, `priority`, `is_active`, `keterangan`)
SELECT '*', '*', 'SALES_RETURN', 'COGS', 'KREDIT', a.id_akun, 100, 1, 'Default HPP retur penjualan' FROM tbkeu_akun a WHERE a.kode_akun = '5100'
ON DUPLICATE KEY UPDATE `id_akun` = VALUES(`id_akun`), `is_active` = 1;

INSERT INTO `tbkeu_mapping_akun` (`source_module`, `source_type`, `posting_event`, `account_role`, `entry_side`, `id_akun`, `priority`, `is_active`, `keterangan`)
SELECT '*', '*', 'PURCHASE_RETURN', 'ACCOUNT_PAYABLE', 'DEBIT', a.id_akun, 100, 1, 'Default hutang retur pembelian' FROM tbkeu_akun a WHERE a.kode_akun = '2100'
ON DUPLICATE KEY UPDATE `id_akun` = VALUES(`id_akun`), `is_active` = 1;

INSERT INTO `tbkeu_mapping_akun` (`source_module`, `source_type`, `posting_event`, `account_role`, `entry_side`, `id_akun`, `priority`, `is_active`, `keterangan`)
SELECT '*', '*', 'PURCHASE_RETURN', 'INVENTORY', 'KREDIT', a.id_akun, 100, 1, 'Default persediaan retur pembelian' FROM tbkeu_akun a WHERE a.kode_akun = '1400'
ON DUPLICATE KEY UPDATE `id_akun` = VALUES(`id_akun`), `is_active` = 1;

INSERT INTO `tbkeu_mapping_akun` (`source_module`, `source_type`, `posting_event`, `account_role`, `entry_side`, `id_akun`, `priority`, `is_active`, `keterangan`)
SELECT '*', '*', 'PURCHASE_RETURN', 'VAT_INPUT', 'KREDIT', a.id_akun, 100, 1, 'Default PPN retur pembelian' FROM tbkeu_akun a WHERE a.kode_akun = '1500'
ON DUPLICATE KEY UPDATE `id_akun` = VALUES(`id_akun`), `is_active` = 1;

INSERT INTO `tbkeu_mapping_akun` (`source_module`, `source_type`, `posting_event`, `account_role`, `entry_side`, `id_akun`, `priority`, `is_active`, `keterangan`)
SELECT '*', '*', 'STOCK_TRANSFER', 'INVENTORY', 'DEBIT', a.id_akun, 100, 1, 'Default persediaan tujuan mutasi' FROM tbkeu_akun a WHERE a.kode_akun = '1400'
ON DUPLICATE KEY UPDATE `id_akun` = VALUES(`id_akun`), `is_active` = 1;

INSERT INTO `tbkeu_mapping_akun` (`source_module`, `source_type`, `posting_event`, `account_role`, `entry_side`, `id_akun`, `priority`, `is_active`, `keterangan`)
SELECT '*', '*', 'STOCK_TRANSFER', 'INVENTORY', 'KREDIT', a.id_akun, 100, 1, 'Default persediaan asal mutasi' FROM tbkeu_akun a WHERE a.kode_akun = '1400'
ON DUPLICATE KEY UPDATE `id_akun` = VALUES(`id_akun`), `is_active` = 1;

INSERT INTO `tbkeu_mapping_akun` (`source_module`, `source_type`, `posting_event`, `account_role`, `entry_side`, `id_akun`, `priority`, `is_active`, `keterangan`)
SELECT '*', '*', 'STOCK_ADJUSTMENT_IN', 'INVENTORY', 'DEBIT', a.id_akun, 100, 1, 'Default persediaan adjustment masuk' FROM tbkeu_akun a WHERE a.kode_akun = '1400'
ON DUPLICATE KEY UPDATE `id_akun` = VALUES(`id_akun`), `is_active` = 1;

INSERT INTO `tbkeu_mapping_akun` (`source_module`, `source_type`, `posting_event`, `account_role`, `entry_side`, `id_akun`, `priority`, `is_active`, `keterangan`)
SELECT '*', '*', 'STOCK_ADJUSTMENT_IN', 'STOCK_GAIN', 'KREDIT', a.id_akun, 100, 1, 'Default keuntungan stock' FROM tbkeu_akun a WHERE a.kode_akun = '8100'
ON DUPLICATE KEY UPDATE `id_akun` = VALUES(`id_akun`), `is_active` = 1;

INSERT INTO `tbkeu_mapping_akun` (`source_module`, `source_type`, `posting_event`, `account_role`, `entry_side`, `id_akun`, `priority`, `is_active`, `keterangan`)
SELECT '*', '*', 'STOCK_ADJUSTMENT_OUT', 'STOCK_LOSS', 'DEBIT', a.id_akun, 100, 1, 'Default kerugian stock' FROM tbkeu_akun a WHERE a.kode_akun = '6100'
ON DUPLICATE KEY UPDATE `id_akun` = VALUES(`id_akun`), `is_active` = 1;

INSERT INTO `tbkeu_mapping_akun` (`source_module`, `source_type`, `posting_event`, `account_role`, `entry_side`, `id_akun`, `priority`, `is_active`, `keterangan`)
SELECT '*', '*', 'STOCK_ADJUSTMENT_OUT', 'INVENTORY', 'KREDIT', a.id_akun, 100, 1, 'Default persediaan adjustment keluar' FROM tbkeu_akun a WHERE a.kode_akun = '1400'
ON DUPLICATE KEY UPDATE `id_akun` = VALUES(`id_akun`), `is_active` = 1;

-- =========================================================
-- DATA DUMMY UNTUK MANUAL TESTING
-- Skenario disesuaikan untuk PT distributor kebutuhan, perlengkapan, dan obat agrobisnis.
-- Angka memakai variabel agar tim finance mudah mengganti asumsi bisnis.
-- =========================================================

SET @dummy_date := '2026-07-13';
SET @warehouse_main := 'Gdg. Induk';
SET @warehouse_reject := 'Gdg. Rusak';

SET @customer_retail := 'Koperasi Tani Subur Jaya';
SET @customer_estate := 'PT Perkebunan Mekar Agro';
SET @supplier_saprodi := 'PT Agro Saprodi Nusantara';
SET @supplier_seed := 'PT Benih Nusantara Prima';

SET @product_herbicide := 'Herbisida GulmaClean 1L';
SET @product_fungicide := 'Fungisida FungiStop 500gr';
SET @product_fertilizer := 'Pupuk NPK GrowMax 25kg';
SET @product_seed := 'Benih Jagung Hibrida Nusantara 5kg';
SET @product_sprayer := 'Sprayer Elektrik AgroPro 16L';

SET @sales_qty := 48.00;
SET @sales_unit_price := 185000.0000;
SET @sales_amount := @sales_qty * @sales_unit_price;
SET @sales_tax := ROUND(@sales_amount * 0.11, 4);
SET @sales_cogs := @sales_qty * 128000.0000;
SET @sales_total := @sales_amount + @sales_tax;

SET @lpb_qty := 120.00;
SET @lpb_unit_price := 245000.0000;
SET @lpb_amount := @lpb_qty * @lpb_unit_price;

SET @purchase_tax := ROUND(@lpb_amount * 0.11, 4);
SET @purchase_total := @lpb_amount + @purchase_tax;

SET @return_qty := 3.00;
SET @return_unit_price := @sales_unit_price;
SET @return_amount := @return_qty * @return_unit_price;
SET @return_tax := ROUND(@return_amount * 0.11, 4);
SET @return_cogs := @return_qty * 128000.0000;

SET @transfer_qty := 24.00;
SET @transfer_unit_cost := 128000.0000;
SET @transfer_amount := @transfer_qty * @transfer_unit_cost;

SET @adjust_out_qty := 6.00;
SET @adjust_out_unit_cost := 92500.0000;
SET @adjust_out_amount := @adjust_out_qty * @adjust_out_unit_cost;

SET @adjust_in_qty := 2.00;
SET @adjust_in_unit_cost := 128000.0000;
SET @adjust_in_amount := @adjust_in_qty * @adjust_in_unit_cost;

DELETE FROM `tbkeu_dummy_source`
WHERE `source_no` IN ('DUMMY-SALES-001', 'DUMMY-LPB-001', 'DUMMY-PAY-AR-001', 'DUMMY-ADJ-OUT-001');

INSERT INTO `tbkeu_dummy_source`
  (`posting_event`, `source_no`, `tanggal_transaksi`, `partner_name`, `product_name`, `qty`, `unit_name`, `unit_price`, `warehouse_name`, `amount`, `tax`, `cogs`, `keterangan`)
VALUES
  ('SALES_INVOICE', 'INV-AGRO-20260713-001', @dummy_date, @customer_retail, @product_herbicide, @sales_qty, 'botol', @sales_unit_price, @warehouse_main, @sales_amount, @sales_tax, @sales_cogs, CONCAT('Faktur penjualan ', @product_herbicide, ' ke ', @customer_retail)),
  ('GOODS_RECEIPT', 'LPB-AGRO-20260713-001', @dummy_date, @supplier_saprodi, @product_fertilizer, @lpb_qty, 'sak', @lpb_unit_price, @warehouse_main, @lpb_amount, 0.0000, 0.0000, CONCAT('LPB pembelian stok ', @product_fertilizer, ' dari ', @supplier_saprodi)),
  ('PURCHASE_INVOICE', 'PINV-AGRO-20260713-001', @dummy_date, @supplier_saprodi, @product_fertilizer, @lpb_qty, 'sak', @lpb_unit_price, @warehouse_main, @lpb_amount, @purchase_tax, 0.0000, CONCAT('Invoice supplier ', @product_fertilizer, ' dari ', @supplier_saprodi)),
  ('CUSTOMER_PAYMENT', 'RCPT-AGRO-20260713-001', @dummy_date, @customer_retail, @product_herbicide, @sales_qty, 'botol', @sales_unit_price, @warehouse_main, @sales_total, 0.0000, 0.0000, CONCAT('Pelunasan faktur ', @product_herbicide, ' oleh ', @customer_retail)),
  ('SUPPLIER_PAYMENT', 'PAY-SUP-AGRO-20260713-001', @dummy_date, @supplier_saprodi, @product_fertilizer, @lpb_qty, 'sak', @lpb_unit_price, @warehouse_main, @purchase_total, 0.0000, 0.0000, CONCAT('Pembayaran supplier ', @supplier_saprodi, ' untuk ', @product_fertilizer)),
  ('SALES_RETURN', 'SRET-AGRO-20260713-001', @dummy_date, @customer_retail, @product_herbicide, @return_qty, 'botol', @return_unit_price, @warehouse_reject, @return_amount, @return_tax, @return_cogs, CONCAT('Retur penjualan karena kemasan rusak ', @product_herbicide)),
  ('PURCHASE_RETURN', 'PRET-AGRO-20260713-001', @dummy_date, @supplier_seed, @product_seed, 4.00, 'dus', 875000.0000, @warehouse_reject, 3500000.0000, 385000.0000, 0.0000, CONCAT('Retur pembelian batch rusak ', @product_seed, ' ke ', @supplier_seed)),
  ('STOCK_TRANSFER', 'MUT-AGRO-20260713-001', @dummy_date, 'Internal Gudang', @product_fungicide, @transfer_qty, 'pack', @transfer_unit_cost, CONCAT(@warehouse_main, ' ke ', @warehouse_reject), @transfer_amount, 0.0000, 0.0000, CONCAT('Mutasi stok inspeksi kualitas ', @product_fungicide)),
  ('STOCK_ADJUSTMENT_OUT', 'ADJ-OUT-AGRO-20260713-001', @dummy_date, 'Internal Stock Opname', @product_sprayer, @adjust_out_qty, 'unit', @adjust_out_unit_cost, @warehouse_reject, @adjust_out_amount, 0.0000, 0.0000, CONCAT('Adjustment keluar barang rusak/expired ', @product_sprayer)),
  ('STOCK_ADJUSTMENT_IN', 'ADJ-IN-AGRO-20260713-001', @dummy_date, 'Internal Stock Opname', @product_herbicide, @adjust_in_qty, 'botol', @adjust_in_unit_cost, @warehouse_main, @adjust_in_amount, 0.0000, 0.0000, CONCAT('Adjustment masuk selisih opname ', @product_herbicide))
ON DUPLICATE KEY UPDATE
  `partner_name` = VALUES(`partner_name`),
  `product_name` = VALUES(`product_name`),
  `qty` = VALUES(`qty`),
  `unit_name` = VALUES(`unit_name`),
  `unit_price` = VALUES(`unit_price`),
  `warehouse_name` = VALUES(`warehouse_name`),
  `amount` = VALUES(`amount`),
  `tax` = VALUES(`tax`),
  `cogs` = VALUES(`cogs`),
  `keterangan` = VALUES(`keterangan`);

-- =========================================================
-- VERIFICATION
-- =========================================================

SELECT 'tbkeu_mapping_akun' AS table_name, COUNT(*) AS total_rows FROM tbkeu_mapping_akun
UNION ALL
SELECT 'tbkeu_posting_exception' AS table_name, COUNT(*) AS total_rows FROM tbkeu_posting_exception
UNION ALL
SELECT 'tbkeu_jurnal_log' AS table_name, COUNT(*) AS total_rows FROM tbkeu_jurnal_log
UNION ALL
SELECT 'tbkeu_dummy_source' AS table_name, COUNT(*) AS total_rows FROM tbkeu_dummy_source;

SELECT m.posting_event, m.account_role, m.entry_side, a.kode_akun, a.nama_akun
FROM tbkeu_mapping_akun m
JOIN tbkeu_akun a ON a.id_akun = m.id_akun
ORDER BY m.posting_event, m.account_role, m.entry_side;
