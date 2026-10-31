-- Menambahkan akun admin untuk autentikasi aplikasi melalui tabel tb_users.
-- Skrip tidak mengubah skema dan tidak menimpa akun admin yang sudah ada.
INSERT INTO `tb_users` (
    `username`,
    `password`,
    `level`,
    `jobdesk_hrd`,
    `nama_lngkp`,
    `nik`,
    `bagian`,
    `departement`,
    `jabatan`,
    `atasan`,
    `penilai`,
    `status`
)
SELECT
    'admin',
    '$2y$10$Y.0VsvZguoxCqaepY.sZ3.7So.m4qPGbJhr.kDsFwRBjgl31KpOAK',
    1,
    NULL,
    'Administrator',
    'ADMIN001',
    'ADMIN',
    'ADMIN',
    'ADMIN',
    '-',
    '-',
    1
WHERE NOT EXISTS (
    SELECT 1
    FROM `tb_users`
    WHERE `username` = 'admin'
);

-- Periksa hasil: satu baris dengan username admin dan status aktif.
SELECT `id`, `username`, `level`, `jabatan`, `status`
FROM `tb_users`
WHERE `username` = 'admin';
