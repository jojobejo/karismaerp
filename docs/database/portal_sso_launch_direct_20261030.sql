-- Perbarui URL peluncuran agar kartu POERP langsung memulai SSO.
-- Prasyarat: app_code, status, dan nilai launch_uri lama harus cocok.
START TRANSACTION;

UPDATE `tb_portal_sso_client`
SET `launch_uri` = 'https://10.10.10.26/poerp/sso/login'
WHERE `app_code` = 'kiu_po'
  AND `is_enabled` = 1
  AND `launch_uri` = 'https://10.10.10.26/poerp/';

SELECT ROW_COUNT() AS `updated_rows`;
SELECT `app_code`, `launch_uri`, `redirect_uri`, `is_enabled`
FROM `tb_portal_sso_client`
WHERE `app_code` = 'kiu_po';

COMMIT;
