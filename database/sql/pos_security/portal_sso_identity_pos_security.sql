-- =============================================================================
-- Migrasi Tabel Pemetaan Identitas SSO & Audit Log Login Portal KARISMA ERP
-- Aplikasi Klien : POS Security & HRD (app_code: pos_security)
-- Tanggal        : 2026-11-17
-- =============================================================================

CREATE TABLE IF NOT EXISTS `tb_sso_identity` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `provider` varchar(190) NOT NULL DEFAULT 'https://portal.karismaerp.com',
  `auth_source` varchar(32) NOT NULL COMMENT 'Sumber identitas Portal: tb_users atau tb_karyawan',
  `external_sub` varchar(64) NOT NULL COMMENT 'Subject stabil Portal, contoh: tb_users:123 atau tb_karyawan:123',
  `local_auth_table` varchar(32) NOT NULL DEFAULT 'tb_user' COMMENT 'Tabel akun lokal aplikasi POS Security: tb_user',
  `local_user_id` varchar(190) NOT NULL COMMENT 'Primary key akun lokal (tb_user.id)',
  `local_username` varchar(190) NOT NULL COMMENT 'Snapshot username akun lokal yang ditetapkan',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_tb_sso_identity_provider_sub` (`provider`, `external_sub`),
  UNIQUE KEY `uk_tb_sso_identity_provider_local` (`provider`, `local_auth_table`, `local_user_id`),
  KEY `idx_tb_sso_identity_local_user` (`local_user_id`),
  KEY `idx_tb_sso_identity_local_username` (`local_username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `tb_login_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` varchar(190) DEFAULT NULL COMMENT 'Primary key id pada tb_user',
  `kode_user` varchar(64) DEFAULT NULL COMMENT 'Kode user pada tb_user.kode_user',
  `username` varchar(190) DEFAULT NULL,
  `nama_user` varchar(255) DEFAULT NULL,
  `departemen` varchar(100) DEFAULT NULL,
  `akses_lv` int(11) DEFAULT NULL,
  `status` varchar(32) NOT NULL DEFAULT 'success' COMMENT 'success, failed, atau blocked',
  `message` varchar(255) DEFAULT NULL COMMENT 'Catatan hasil autentikasi tanpa data rahasia',
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `login_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_tb_login_log_username` (`username`),
  KEY `idx_tb_login_log_status` (`status`),
  KEY `idx_tb_login_log_login_at` (`login_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
