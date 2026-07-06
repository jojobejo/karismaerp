-- Auth development for tb_users and Penilaian Lingkungan.
-- Run after backup on the target Karisma ERP database.

ALTER TABLE `tb_users`
  ADD COLUMN IF NOT EXISTS `password` varchar(255) NOT NULL DEFAULT '' AFTER `username`,
  ADD COLUMN IF NOT EXISTS `level` int(11) NOT NULL DEFAULT 1 AFTER `password`,
  ADD COLUMN IF NOT EXISTS `jobdesk_hrd` varchar(100) NOT NULL DEFAULT 'inputer_laporan' AFTER `level`,
  ADD COLUMN IF NOT EXISTS `status` tinyint(1) NOT NULL DEFAULT 1 AFTER `penilai`,
  ADD COLUMN IF NOT EXISTS `default_redirect` varchar(180) DEFAULT NULL AFTER `status`,
  ADD COLUMN IF NOT EXISTS `last_login` datetime DEFAULT NULL AFTER `default_redirect`,
  ADD COLUMN IF NOT EXISTS `created_at` datetime DEFAULT CURRENT_TIMESTAMP AFTER `last_login`,
  ADD COLUMN IF NOT EXISTS `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER `created_at`;

CREATE INDEX IF NOT EXISTS `idx_tb_users_username` ON `tb_users` (`username`);
CREATE INDEX IF NOT EXISTS `idx_tb_users_jobdesk_hrd` ON `tb_users` (`jobdesk_hrd`);
CREATE INDEX IF NOT EXISTS `idx_tb_users_status` ON `tb_users` (`status`);

-- Existing rows become HRD report input users by default.
UPDATE `tb_users`
SET `jobdesk_hrd` = 'inputer_laporan'
WHERE `jobdesk_hrd` IS NULL OR `jobdesk_hrd` = '';

-- User with jobdesk_hrd = inputer_laporan will be redirected to /penilaian_lingkungan.
UPDATE `tb_users`
SET `default_redirect` = 'penilaian_lingkungan'
WHERE `jobdesk_hrd` = 'inputer_laporan'
  AND (`default_redirect` IS NULL OR `default_redirect` = '');
