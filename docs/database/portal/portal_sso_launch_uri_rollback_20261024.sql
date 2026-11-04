-- Peringatan: rollback menghapus URL peluncuran yang telah didaftarkan.
ALTER TABLE `tb_portal_sso_client`
  DROP COLUMN `launch_uri`;
