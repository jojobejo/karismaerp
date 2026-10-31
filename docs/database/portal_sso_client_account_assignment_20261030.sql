-- Penetapan unik akun klien kepada satu user Portal untuk tiap aplikasi.
-- Pastikan backup database tersedia sebelum menerapkan migration.
CREATE TABLE IF NOT EXISTS `tb_portal_sso_client_account_assignment` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `app_code` VARCHAR(50) NOT NULL,
  `account_id` BIGINT UNSIGNED NOT NULL,
  `auth_source` VARCHAR(20) NOT NULL,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `updated_by` BIGINT UNSIGNED DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_portal_sso_assigned_account` (`account_id`),
  UNIQUE KEY `uq_portal_sso_user_app_account` (`app_code`, `auth_source`, `user_id`),
  KEY `idx_portal_sso_assignment_app` (`app_code`, `auth_source`, `user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
