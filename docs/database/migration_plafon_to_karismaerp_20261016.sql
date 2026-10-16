-- =====================================================================
-- Migrasi Database: Plafon App ke kiucoid_karismaerp_local
-- Tanggal: 16 Oktober 2026
-- Keterangan:
--   1. Memindahkan 4 tabel bisnis utama dari `plafon-db` ke `kiucoid_karismaerp_local`
--      dengan penambahan prefix `plafon_`:
--      - customers   -> plafon_customers
--      - users       -> plafon_users
--      - submissions -> plafon_submissions
--      - approvals   -> plafon_approvals
--   2. Mengecualikan tabel-tabel bawaan Laravel:
--      (cache, cache_locks, jobs, failed_jobs, job_batches, sessions, migrations)
-- =====================================================================

USE `kiucoid_karismaerp_local`;

-- 1. TABEL: plafon_users
DROP TABLE IF EXISTS `plafon_users`;
CREATE TABLE `plafon_users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` enum('sales','sales_executive','approver1','approver2','approver3','approver4','approver5','approver6','viewer','piutang_manager','direktur') COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `plafon_users_email_unique` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `plafon_users` SELECT * FROM `plafon-db`.`users`;

-- 2. TABEL: plafon_customers
DROP TABLE IF EXISTS `plafon_customers`;
CREATE TABLE `plafon_customers` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `sales_id` bigint unsigned NOT NULL,
  `nama_sales` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `kode_customer` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nama` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nama_kios` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `alamat` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `plafon_aktif` decimal(15,2) NOT NULL DEFAULT '0.00',
  `piutang` decimal(15,2) NOT NULL DEFAULT '0.00',
  `status` enum('active','inactive') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `plafon_customers_kode_customer_unique` (`kode_customer`),
  KEY `plafon_customers_sales_id_index` (`sales_id`),
  KEY `plafon_customers_nama_sales_index` (`nama_sales`),
  KEY `plafon_customers_nama_index` (`nama`),
  KEY `plafon_customers_nama_kios_index` (`nama_kios`),
  KEY `plafon_customers_status_index` (`status`),
  KEY `plafon_customers_sales_id_status_index` (`sales_id`,`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `plafon_customers` SELECT * FROM `plafon-db`.`customers`;

-- 3. TABEL: plafon_submissions
DROP TABLE IF EXISTS `plafon_submissions`;
CREATE TABLE `plafon_submissions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `kode` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nama` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nama_kios` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `alamat` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `plafon` decimal(15,2) NOT NULL,
  `plafon_sebelumnya` decimal(15,2) DEFAULT NULL,
  `plafon_type` enum('open','rubah') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'open',
  `plafon_direction` enum('naik','turun') COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'naik/turun',
  `previous_submission_id` bigint unsigned DEFAULT NULL COMMENT 'ID submission sebelumnya (untuk rubah plafon)',
  `jumlah_buka_faktur` bigint DEFAULT NULL,
  `komitmen_pembayaran` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `komitmen_pembayaran_history` json DEFAULT NULL,
  `lampiran_path` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `payment_type` enum('od','over') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payment_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `sales_id` bigint unsigned NOT NULL,
  `customer_id` bigint unsigned DEFAULT NULL,
  `status` enum('pending','approved_1','approved_2','approved_3','approved_4','approved_5','approved_6','pending_viewer','rejected','revision','done') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `current_level` int DEFAULT NULL,
  `rejection_note` text COLLATE utf8mb4_unicode_ci,
  `target_status` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `plafon_submissions_kode_unique` (`kode`),
  KEY `plafon_submissions_previous_submission_id_index` (`previous_submission_id`),
  KEY `plafon_submissions_sales_id_index` (`sales_id`),
  KEY `plafon_submissions_status_index` (`status`),
  KEY `plafon_submissions_plafon_type_index` (`plafon_type`),
  KEY `plafon_submissions_created_at_index` (`created_at`),
  KEY `plafon_submissions_customer_id_index` (`customer_id`),
  CONSTRAINT `plafon_submissions_chk_1` CHECK (json_valid(`lampiran_path`)),
  CONSTRAINT `plafon_submissions_chk_2` CHECK (json_valid(`payment_data`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `plafon_submissions` SELECT * FROM `plafon-db`.`submissions`;

-- 4. TABEL: plafon_approvals
DROP TABLE IF EXISTS `plafon_approvals`;
CREATE TABLE `plafon_approvals` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `submission_id` bigint unsigned NOT NULL,
  `approver_id` bigint unsigned NOT NULL,
  `level` int NOT NULL,
  `status` enum('approved','rejected','revision') COLLATE utf8mb4_unicode_ci NOT NULL,
  `note` text COLLATE utf8mb4_unicode_ci,
  `target_status` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `piutang` decimal(15,2) DEFAULT NULL,
  `jml_over` decimal(15,2) DEFAULT NULL,
  `jml_od_30` decimal(15,2) DEFAULT NULL,
  `jml_od_60` decimal(15,2) DEFAULT NULL,
  `jml_od_90` decimal(15,2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `plafon_approvals_submission_id_index` (`submission_id`),
  KEY `plafon_approvals_approver_id_index` (`approver_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `plafon_approvals` SELECT * FROM `plafon-db`.`approvals`;
