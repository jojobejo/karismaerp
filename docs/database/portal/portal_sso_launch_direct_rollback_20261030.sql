-- Pulihkan URL peluncuran POERP sebelumnya jika diperlukan.
START TRANSACTION;

UPDATE `tb_portal_sso_client`
SET `launch_uri` = 'https://10.10.10.26/poerp/'
WHERE `app_code` = 'kiu_po'
  AND `launch_uri` = 'https://10.10.10.26/poerp/sso/login';

SELECT ROW_COUNT() AS `restored_rows`;
SELECT `app_code`, `launch_uri`, `redirect_uri`, `is_enabled`
FROM `tb_portal_sso_client`
WHERE `app_code` = 'kiu_po';

COMMIT;
