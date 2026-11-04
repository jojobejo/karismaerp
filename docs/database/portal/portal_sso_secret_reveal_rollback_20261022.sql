-- Jalankan hanya setelah secret yang masih diperlukan dipindahkan ke server klien.
ALTER TABLE `tb_portal_sso_client`
  DROP COLUMN `client_secret_encrypted`;
