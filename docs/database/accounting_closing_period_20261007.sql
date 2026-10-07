-- Modul tutup buku KarismaERP.
-- Jalankan sesudah migration general ledger dan accounting hardening.

CREATE TABLE IF NOT EXISTS `tbkeu_closing_config` (
  `id_config` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `config_key` VARCHAR(80) NOT NULL,
  `config_value` VARCHAR(255) DEFAULT NULL,
  `id_akun` BIGINT UNSIGNED DEFAULT NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `updated_by` BIGINT DEFAULT NULL,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_config`),
  UNIQUE KEY `uk_closing_config_key` (`config_key`),
  KEY `idx_closing_config_akun` (`id_akun`),
  CONSTRAINT `fk_closing_config_akun` FOREIGN KEY (`id_akun`) REFERENCES `tbkeu_akun` (`id_akun`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `tbkeu_closing_period` (
  `id_closing` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_periode` BIGINT UNSIGNED NOT NULL,
  `closing_version` INT UNSIGNED NOT NULL,
  `closing_type` ENUM('MONTH_END','YEAR_END') NOT NULL DEFAULT 'MONTH_END',
  `status` ENUM('DRAFT','VALIDATING','READY_TO_APPROVE','APPROVED','EXECUTING','COMPLETED','REOPENED','SUPERSEDED','FAILED') NOT NULL DEFAULT 'DRAFT',
  `cutoff_at` DATETIME NOT NULL,
  `source_watermark` DATETIME DEFAULT NULL,
  `requested_by` BIGINT NOT NULL,
  `requested_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `approved_by` BIGINT DEFAULT NULL,
  `approved_at` DATETIME DEFAULT NULL,
  `executed_by` BIGINT DEFAULT NULL,
  `executed_at` DATETIME DEFAULT NULL,
  `id_jurnal_penutup` BIGINT UNSIGNED DEFAULT NULL,
  `net_income` DECIMAL(19,4) NOT NULL DEFAULT 0.0000,
  `snapshot_checksum` CHAR(64) DEFAULT NULL,
  `failure_message` VARCHAR(1000) DEFAULT NULL,
  `notes` TEXT DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_closing`),
  UNIQUE KEY `uk_closing_version` (`id_periode`,`closing_type`,`closing_version`),
  KEY `idx_closing_status` (`status`),
  KEY `idx_closing_jurnal` (`id_jurnal_penutup`),
  CONSTRAINT `fk_closing_periode` FOREIGN KEY (`id_periode`) REFERENCES `tbkeu_periode_fiskal` (`id_periode`) ON UPDATE CASCADE,
  CONSTRAINT `fk_closing_jurnal` FOREIGN KEY (`id_jurnal_penutup`) REFERENCES `tbkeu_jurnal` (`id_jurnal`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `tbkeu_closing_validation_run` (
  `id_validation_run` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_closing` BIGINT UNSIGNED NOT NULL,
  `run_number` INT UNSIGNED NOT NULL,
  `status` ENUM('RUNNING','PASSED','FAILED') NOT NULL DEFAULT 'RUNNING',
  `blocking_count` INT UNSIGNED NOT NULL DEFAULT 0,
  `warning_count` INT UNSIGNED NOT NULL DEFAULT 0,
  `started_by` BIGINT NOT NULL,
  `started_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `finished_at` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id_validation_run`),
  UNIQUE KEY `uk_validation_run` (`id_closing`,`run_number`),
  CONSTRAINT `fk_validation_closing` FOREIGN KEY (`id_closing`) REFERENCES `tbkeu_closing_period` (`id_closing`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `tbkeu_closing_validation_detail` (
  `id_validation_detail` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_validation_run` BIGINT UNSIGNED NOT NULL,
  `rule_code` VARCHAR(80) NOT NULL,
  `rule_version` VARCHAR(20) NOT NULL DEFAULT '1.0',
  `severity` ENUM('BLOCKING','WARNING','INFO') NOT NULL,
  `status` ENUM('PASSED','FAILED') NOT NULL,
  `expected_amount` DECIMAL(19,4) DEFAULT NULL,
  `actual_amount` DECIMAL(19,4) DEFAULT NULL,
  `difference_amount` DECIMAL(19,4) DEFAULT NULL,
  `anomaly_count` INT UNSIGNED NOT NULL DEFAULT 0,
  `message` VARCHAR(1000) NOT NULL,
  `reference_json` LONGTEXT DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_validation_detail`),
  UNIQUE KEY `uk_validation_rule` (`id_validation_run`,`rule_code`),
  CONSTRAINT `fk_validation_detail_run` FOREIGN KEY (`id_validation_run`) REFERENCES `tbkeu_closing_validation_run` (`id_validation_run`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `tbkeu_closing_balance_account` (
  `id_closing_balance` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_closing` BIGINT UNSIGNED NOT NULL,
  `id_akun` BIGINT UNSIGNED NOT NULL,
  `opening_balance` DECIMAL(19,4) NOT NULL DEFAULT 0.0000,
  `period_debit` DECIMAL(19,4) NOT NULL DEFAULT 0.0000,
  `period_credit` DECIMAL(19,4) NOT NULL DEFAULT 0.0000,
  `ending_balance` DECIMAL(19,4) NOT NULL DEFAULT 0.0000,
  `normal_balance` ENUM('DEBIT','KREDIT') NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_closing_balance`),
  UNIQUE KEY `uk_closing_account` (`id_closing`,`id_akun`),
  KEY `idx_closing_balance_akun` (`id_akun`),
  CONSTRAINT `fk_closing_balance_header` FOREIGN KEY (`id_closing`) REFERENCES `tbkeu_closing_period` (`id_closing`) ON UPDATE CASCADE,
  CONSTRAINT `fk_closing_balance_akun` FOREIGN KEY (`id_akun`) REFERENCES `tbkeu_akun` (`id_akun`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `tbkeu_reopen_request` (
  `id_reopen` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_periode` BIGINT UNSIGNED NOT NULL,
  `id_closing` BIGINT UNSIGNED NOT NULL,
  `reason` TEXT NOT NULL,
  `correction_plan` TEXT NOT NULL,
  `status` ENUM('PENDING_MANAGER','PENDING_DIRECTOR','APPROVED','REJECTED','EXPIRED','COMPLETED') NOT NULL DEFAULT 'PENDING_MANAGER',
  `requested_by` BIGINT NOT NULL,
  `requested_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `manager_approved_by` BIGINT DEFAULT NULL,
  `manager_approved_at` DATETIME DEFAULT NULL,
  `director_approved_by` BIGINT DEFAULT NULL,
  `director_approved_at` DATETIME DEFAULT NULL,
  `reopen_until` DATETIME DEFAULT NULL,
  `completed_at` DATETIME DEFAULT NULL,
  `rejection_note` VARCHAR(1000) DEFAULT NULL,
  PRIMARY KEY (`id_reopen`),
  KEY `idx_reopen_period_status` (`id_periode`,`status`),
  CONSTRAINT `fk_reopen_period` FOREIGN KEY (`id_periode`) REFERENCES `tbkeu_periode_fiskal` (`id_periode`) ON UPDATE CASCADE,
  CONSTRAINT `fk_reopen_closing` FOREIGN KEY (`id_closing`) REFERENCES `tbkeu_closing_period` (`id_closing`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `tbkeu_reopen_approval_log` (
  `id_approval_log` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_reopen` BIGINT UNSIGNED NOT NULL,
  `approval_level` ENUM('MANAGER','DIRECTOR') NOT NULL,
  `decision` ENUM('APPROVED','REJECTED') NOT NULL,
  `decision_by` BIGINT NOT NULL,
  `decision_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `note` VARCHAR(1000) NOT NULL,
  PRIMARY KEY (`id_approval_log`),
  KEY `idx_reopen_approval` (`id_reopen`,`decision_at`),
  CONSTRAINT `fk_reopen_approval_request` FOREIGN KEY (`id_reopen`) REFERENCES `tbkeu_reopen_request` (`id_reopen`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Akun wajib dipilih oleh tim accounting sebelum tutup buku tahunan.
INSERT INTO `tbkeu_closing_config` (`config_key`,`config_value`,`id_akun`,`is_active`)
VALUES ('RETAINED_EARNINGS_ACCOUNT',NULL,NULL,1)
ON DUPLICATE KEY UPDATE `config_key`=VALUES(`config_key`);
