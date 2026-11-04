-- Jalankan hanya pada database development yang dituju.
-- Password awal: karisma. Segera ubah sesudah login pertama.
START TRANSACTION;

INSERT INTO tb_users
    (username, password, level, jobdesk_hrd, nama_lngkp, nik, bagian, departement, jabatan, atasan, penilai, status_karyawan, status, default_redirect)
SELECT 'superuser', '$2y$10$488kK.OhDoT/BJQEcREdQO.y4HJjFNIQf3oQSUbKgEknxZxuH2Hky',
    1, 'superuser', 'Superuser Portal', 'SUPERUSER-PORTAL', 'Portal', 'Portal', 'SUPERUSER', '-', '-', 'AKTIF', 1, 'portal'
WHERE NOT EXISTS (SELECT 1 FROM tb_users WHERE username = 'superuser');

INSERT INTO tb_portal_app_access (auth_source, user_id, app_code, is_allowed, updated_by)
SELECT 'tb_users', u.id, d.app_code, 1, u.id
FROM tb_users u CROSS JOIN tb_portal_app_directory d
WHERE u.username = 'superuser'
ON DUPLICATE KEY UPDATE is_allowed = 1, updated_by = VALUES(updated_by);

COMMIT;
