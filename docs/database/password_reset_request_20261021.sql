CREATE TABLE IF NOT EXISTS `tb_password_reset_request` (
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
