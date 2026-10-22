CREATE TABLE IF NOT EXISTS `tb_portal_app_access` (
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
