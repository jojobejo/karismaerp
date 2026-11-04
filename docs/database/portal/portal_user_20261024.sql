-- Tabel penghubung akun ERP ke akses portal.
-- Sumber identitas dan autentikasi tetap tb_users; password tidak disalin.
-- Jalankan pada database aplikasi setelah memastikan tb_users tersedia.

CREATE TABLE IF NOT EXISTS `portal_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '1=akses portal aktif, 0=akses portal nonaktif',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_portal_user_user_id` (`user_id`),
  KEY `idx_portal_user_status` (`status`),
  CONSTRAINT `fk_portal_user_tb_users`
    FOREIGN KEY (`user_id`) REFERENCES `tb_users` (`id`)
    ON UPDATE CASCADE
    ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
