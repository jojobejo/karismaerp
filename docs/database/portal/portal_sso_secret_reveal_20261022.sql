-- Menambahkan secret terenkripsi agar admin dapat melihat kembali secret dengan PIN.
-- Backup tb_portal_sso_client sebelum menjalankan migration.
ALTER TABLE `tb_portal_sso_client`
  ADD COLUMN `client_secret_encrypted` TEXT NULL AFTER `client_secret_hash`;
