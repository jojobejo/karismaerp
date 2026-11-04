-- Tabel akun aplikasi klien yang dikelola melalui Portal.
-- Kata sandi disimpan sebagai ciphertext AES-256-GCM, bukan teks biasa.
-- Tidak menggunakan foreign key ke tabel akun legacy karena sumber PIC beragam.
CREATE TABLE IF NOT EXISTS `tb_portal_sso_client_account` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `app_code` VARCHAR(50) NOT NULL,
  `username` VARCHAR(190) NOT NULL,
  `password_encrypted` MEDIUMTEXT NOT NULL,
  `pic_auth_source` VARCHAR(20) DEFAULT NULL,
  `pic_user_id` BIGINT UNSIGNED DEFAULT NULL,
  `created_by` BIGINT UNSIGNED DEFAULT NULL,
  `updated_by` BIGINT UNSIGNED DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_portal_sso_client_account` (`app_code`, `username`),
  KEY `idx_portal_sso_client_account_pic` (`pic_auth_source`, `pic_user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
