INSERT INTO tb_karyawan
    (nik, nm_karyawan, departemen, jobdesk, username, password, tim, wilayah, akses_lv)
SELECT
    'KIUKEU',
    'KIU KEU',
    'KEUANGAN',
    'KIUKEU',
    'kiukeu',
    '$2y$10$y7zTOcowjfl.Nbb2SxOIBuc9S67sgn7YCh7dleUfrIaqfBk5GgIhe',
    0,
    0,
    1
WHERE NOT EXISTS (
    SELECT 1 FROM tb_karyawan WHERE username = 'kiukeu'
);

UPDATE tb_karyawan
SET
    nik = 'KIUKEU',
    nm_karyawan = 'KIU KEU',
    departemen = 'KEUANGAN',
    jobdesk = 'KIUKEU',
    password = '$2y$10$y7zTOcowjfl.Nbb2SxOIBuc9S67sgn7YCh7dleUfrIaqfBk5GgIhe',
    tim = 0,
    wilayah = 0,
    akses_lv = 1
WHERE username = 'kiukeu';

DELETE FROM tb_karyawan
WHERE username = 'kiukeu'
AND id NOT IN (
    SELECT keep_id
    FROM (
        SELECT MIN(id) AS keep_id
        FROM tb_karyawan
        WHERE username = 'kiukeu'
    ) keep_row
);
