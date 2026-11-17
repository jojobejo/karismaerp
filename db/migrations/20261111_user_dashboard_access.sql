-- Migrasi tabel pengaturan hak akses menu Dashboard per user
-- Tanggal: 2026-11-11

CREATE TABLE IF NOT EXISTS `tb_user_dashboard_access` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED NOT NULL,
  `menu_key` VARCHAR(120) NOT NULL,
  `is_allowed` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1 = Diizinkan (Terbuka), 0 = Tidak Diizinkan (Terkunci / Gembok)',
  `updated_by` VARCHAR(100) NULL DEFAULT NULL,
  `created_at` DATETIME NULL DEFAULT NULL,
  `updated_at` DATETIME NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_user_menu` (`user_id`, `menu_key`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_menu_key` (`menu_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

