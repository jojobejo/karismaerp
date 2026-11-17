-- =============================================================================
-- Migrasi Tabel Pemetaan Identitas SSO Portal KARISMA ERP
-- Aplikasi Klien : Penilaian Lingkungan (app_code: penilaian_lingkungan)
-- Tanggal        : 2026-11-17
-- =============================================================================

CREATE TABLE IF NOT EXISTS `tb_sso_identity` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `provider` varchar(190) NOT NULL DEFAULT 'https://portal.karismaerp.com',
  `auth_source` varchar(32) NOT NULL COMMENT 'Sumber identitas Portal: tb_users atau tb_karyawan',
  `external_sub` varchar(64) NOT NULL COMMENT 'Subject stabil Portal, contoh: tb_users:123',
  `local_auth_table` varchar(32) NOT NULL DEFAULT 'tb_users' COMMENT 'Tabel akun lokal: tb_users atau tb_karyawan',
  `local_user_id` int(11) NOT NULL COMMENT 'Primary key akun lokal',
  `local_username` varchar(190) NOT NULL COMMENT 'Snapshot username akun lokal yang ditetapkan',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_tb_sso_identity_provider_sub` (`provider`, `external_sub`),
  UNIQUE KEY `uk_tb_sso_identity_provider_local` (`provider`, `local_auth_table`, `local_user_id`),
  KEY `idx_tb_sso_identity_local_user` (`local_user_id`),
  KEY `idx_tb_sso_identity_local_username` (`local_username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
