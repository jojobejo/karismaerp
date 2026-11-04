-- Histori konfirmasi session SSO yang berhasil dibuat aplikasi klien.
-- Tidak menyimpan password, secret klien, atau ID session aplikasi.
CREATE TABLE IF NOT EXISTS `tb_portal_sso_login_history` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `event_key_hash` CHAR(64) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  `app_code` VARCHAR(50) NOT NULL,
  `auth_source` VARCHAR(20) NOT NULL,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `account_id` BIGINT UNSIGNED NOT NULL,
  `account_username` VARCHAR(190) NOT NULL,
  `source_ip` VARCHAR(45) DEFAULT NULL,
  `user_agent` VARCHAR(255) DEFAULT NULL,
  `login_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_portal_sso_login_event_key` (`event_key_hash`),
  KEY `idx_portal_sso_login_app_time` (`app_code`, `login_at`),
  KEY `idx_portal_sso_login_account_time` (`account_id`, `login_at`),
  KEY `idx_portal_sso_login_user_time` (`auth_source`, `user_id`, `login_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
