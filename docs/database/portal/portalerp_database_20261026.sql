-- Skema minimum database Portal ERP.
-- Skema tabel akun diambil dari dump acuan Agustus 2026 dan dibatasi pada
-- kolom yang dipakai Auth, Portal, hak akses, dan klaim SSO.
-- Tabel Portal memakai definisi migration terbaru hingga 24 Oktober 2026.
-- Berkas ini membuat skema saja; tidak menyalin data akun atau data bisnis.

CREATE DATABASE IF NOT EXISTS `portalerp`
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `portalerp`;

CREATE TABLE IF NOT EXISTS `portalerp_tb_karyawan` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nik` varchar(25) DEFAULT NULL,
  `nm_karyawan` text DEFAULT NULL,
  `departemen` varchar(20) DEFAULT NULL,
  `jobdesk` text DEFAULT NULL,
  `username` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `tim` int(11) NOT NULL,
  `wilayah` int(11) NOT NULL,
  `jobdesk_id` int(11) DEFAULT NULL,
  `akses_lv_id` int(11) DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `last_login` datetime DEFAULT NULL,
  `akses_lv` int(2) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_nik` (`nik`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `portalerp_tb_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `level` int(11) NOT NULL DEFAULT 1,
  `jobdesk_hrd` varchar(100) DEFAULT NULL,
  `nama_lngkp` varchar(255) NOT NULL,
  `nik` varchar(255) NOT NULL,
  `departement` varchar(255) NOT NULL,
  `jabatan` varchar(255) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `default_redirect` varchar(180) DEFAULT NULL,
  `last_login` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_tb_users_username` (`username`),
  KEY `idx_tb_users_jobdesk_hrd` (`jobdesk_hrd`),
  KEY `idx_tb_users_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `portalerp_tb_portal_app_access` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `auth_source` VARCHAR(20) NOT NULL,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `app_code` VARCHAR(50) NOT NULL,
  `is_allowed` TINYINT(1) NOT NULL DEFAULT 0,
  `updated_by` BIGINT UNSIGNED DEFAULT NULL,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_portal_user_app` (`auth_source`, `user_id`, `app_code`),
  KEY `idx_portal_app_user` (`app_code`, `auth_source`, `user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `portalerp_tb_portal_sso_client` (
  `app_code` VARCHAR(50) NOT NULL,
  `redirect_uri` VARCHAR(500) NOT NULL,
  `launch_uri` VARCHAR(500) DEFAULT NULL,
  `client_secret_hash` CHAR(64) NOT NULL,
  `client_secret_encrypted` TEXT DEFAULT NULL,
  `is_enabled` TINYINT(1) NOT NULL DEFAULT 1,
  `created_by` BIGINT UNSIGNED DEFAULT NULL,
  `updated_by` BIGINT UNSIGNED DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`app_code`),
  KEY `idx_portal_sso_client_enabled` (`is_enabled`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `portalerp_tb_portal_sso_code` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `code_hash` CHAR(64) NOT NULL,
  `app_code` VARCHAR(50) NOT NULL,
  `auth_source` VARCHAR(20) NOT NULL,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `redirect_uri` VARCHAR(500) NOT NULL,
  `state` VARCHAR(512) NOT NULL,
  `code_challenge` CHAR(43) NOT NULL,
  `expires_at` DATETIME NOT NULL,
  `used_at` DATETIME DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_portal_sso_code_hash` (`code_hash`),
  KEY `idx_portal_sso_code_expiry` (`expires_at`),
  KEY `idx_portal_sso_code_user` (`auth_source`, `user_id`, `created_at`),
  KEY `idx_portal_sso_code_app` (`app_code`, `used_at`, `expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `portalerp_tb_portal_app_module` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `app_code` VARCHAR(50) NOT NULL,
  `module_code` VARCHAR(80) NOT NULL,
  `module_name` VARCHAR(150) NOT NULL,
  `description` VARCHAR(2000) DEFAULT NULL,
  `module_url` VARCHAR(500) DEFAULT NULL,
  `sort_order` INT UNSIGNED NOT NULL DEFAULT 0,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_by` BIGINT UNSIGNED DEFAULT NULL,
  `updated_by` BIGINT UNSIGNED DEFAULT NULL,
  `created_by_client` VARCHAR(50) DEFAULT NULL,
  `updated_by_client` VARCHAR(50) DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_portal_app_module_code` (`app_code`, `module_code`),
  KEY `idx_portal_app_module_active_order` (`app_code`, `is_active`, `sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Opsional: log kompatibel dengan struktur tb_login_log dari dump Agustus.
-- Model login hanya mengisi irisan kolom; status_login pada tabel acuan tidak
-- dipetakan dari status failed/blocked/success yang dipakai Auth saat ini.
CREATE TABLE IF NOT EXISTS `portalerp_tb_login_log` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `id_karyawan` int(11) DEFAULT NULL,
  `username` varchar(255) DEFAULT NULL,
  `ip_address` varchar(100) DEFAULT NULL,
  `browser` text DEFAULT NULL,
  `platform` varchar(100) DEFAULT NULL,
  `status_login` enum('SUCCESS','FAILED') DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Opsional: dipakai fitur ajukan dan proses reset password.
CREATE TABLE IF NOT EXISTS `portalerp_tb_password_reset_request` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `auth_source` VARCHAR(32) NOT NULL,
  `user_id` INT NOT NULL,
  `username` VARCHAR(100) NOT NULL,
  `status` ENUM('PENDING','COMPLETED') NOT NULL DEFAULT 'PENDING',
  `requested_at` DATETIME NOT NULL,
  `processed_at` DATETIME DEFAULT NULL,
  `processed_by` INT DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_password_reset_status_time` (`status`, `requested_at`),
  KEY `idx_password_reset_account` (`auth_source`, `user_id`, `status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
