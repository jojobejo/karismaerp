INSERT INTO tb_karyawan
    (nik, nm_karyawan, departemen, jobdesk, username, password, tim, wilayah, akses_lv)
SELECT
    'ADMINSC',
    'Admin SC',
    'SALES',
    'ADMINSC',
    'admsc',
    '$2y$10$zHvcqR3s7A8pErXOX30Pfe8WkNE8SrTk7EJXCNUFFdhEEEWjZg21y',
    '',
    '',
    1
WHERE NOT EXISTS (
    SELECT 1 FROM tb_karyawan WHERE username = 'admsc'
);
