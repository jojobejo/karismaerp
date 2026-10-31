-- PERINGATAN: Menghapus semua pemetaan akun klien ke user Portal.
-- Backup tabel sebelum menjalankan rollback ini.
DROP TABLE IF EXISTS `tb_portal_sso_client_account_assignment`;
