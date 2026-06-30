-- Karisma ERP SSO schema
-- Identity source remains tb_karyawan.

CREATE TABLE IF NOT EXISTS `tb_sso_clients` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `client_code` varchar(80) NOT NULL,
  `client_name` varchar(150) NOT NULL,
  `redirect_uris` text NOT NULL,
  `client_secret_hash` varchar(255) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_sso_clients_code` (`client_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `tb_sso_auth_codes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code_hash` char(64) NOT NULL,
  `id_karyawan` int(11) NOT NULL,
  `nik` varchar(25) DEFAULT NULL,
  `client_id` int(11) NOT NULL,
  `client_code` varchar(80) NOT NULL,
  `redirect_uri` text NOT NULL,
  `state` varchar(255) DEFAULT NULL,
  `portal_session_id` varchar(128) DEFAULT NULL,
  `issued_at` datetime NOT NULL,
  `expires_at` datetime NOT NULL,
  `used_at` datetime DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'active',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_sso_auth_codes_hash` (`code_hash`),
  KEY `idx_sso_auth_codes_user` (`id_karyawan`),
  KEY `idx_sso_auth_codes_client` (`client_id`),
  KEY `idx_sso_auth_codes_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `tb_sso_sessions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `session_hash` char(64) NOT NULL,
  `id_karyawan` int(11) NOT NULL,
  `nik` varchar(25) DEFAULT NULL,
  `client_id` int(11) NOT NULL,
  `client_code` varchar(80) NOT NULL,
  `portal_session_id` varchar(128) DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `last_seen_at` datetime NOT NULL,
  `expires_at` datetime NOT NULL,
  `revoked_at` datetime DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'active',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_sso_sessions_hash` (`session_hash`),
  KEY `idx_sso_sessions_user` (`id_karyawan`),
  KEY `idx_sso_sessions_client` (`client_id`),
  KEY `idx_sso_sessions_status` (`status`),
  KEY `idx_sso_sessions_portal` (`portal_session_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
