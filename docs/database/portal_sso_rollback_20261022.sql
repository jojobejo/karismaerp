-- PERINGATAN: rollback menghapus seluruh registrasi integrasi dan kode SSO.
-- Ekspor/backup kedua tabel sebelum menjalankan skrip ini.
DROP TABLE IF EXISTS `tb_portal_sso_code`;
DROP TABLE IF EXISTS `tb_portal_sso_client`;
