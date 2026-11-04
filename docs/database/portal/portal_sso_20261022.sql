-- Skema registrasi klien SSO dan kode otorisasi sekali pakai.
-- Tidak mengubah tabel akun portal maupun dump database acuan.
CREATE TABLE IF NOT EXISTS `tb_portal_sso_client` (
  `app_code` VARCHAR(50) NOT NULL,
  `redirect_uri` VARCHAR(500) NOT NULL,
  `client_secret_hash` CHAR(64) NOT NULL,
  `is_enabled` TINYINT(1) NOT NULL DEFAULT 1,
  `created_by` BIGINT UNSIGNED DEFAULT NULL,
  `updated_by` BIGINT UNSIGNED DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`app_code`),
  KEY `idx_portal_sso_client_enabled` (`is_enabled`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `tb_portal_sso_code` (
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
