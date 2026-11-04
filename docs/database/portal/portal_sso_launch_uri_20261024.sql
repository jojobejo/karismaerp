-- Memisahkan URL pembuka aplikasi dari callback authorization-code SSO.
-- Jalankan pada database KARISMA ERP setelah backup.
ALTER TABLE `tb_portal_sso_client`
  ADD COLUMN `launch_uri` VARCHAR(500) DEFAULT NULL AFTER `redirect_uri`;

-- Setelah migrasi, admin wajib mengisi URL peluncuran HTTPS pada menu Integrasi SSO.
-- Contoh KIU PO lokal: https://localhost/kiupobaru/
