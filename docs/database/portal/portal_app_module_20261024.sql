-- Katalog module yang disediakan oleh setiap aplikasi klien Portal.
CREATE TABLE IF NOT EXISTS `tb_portal_app_module` (
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
