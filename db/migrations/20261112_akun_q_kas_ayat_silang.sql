-- Migrasi penambahan akun Kas: Q Kas Ayat Silang (110111)
-- Mengikuti struktur akun Q Kas (11010) pada tbkeu_akun

INSERT INTO `tbkeu_akun` (
    `kode_akun`,
    `nama_akun`,
    `id_klasifikasi`,
    `parent_id`,
    `level_akun`,
    `saldo_normal`,
    `tipe_akun`,
    `tipe_kontrol`,
    `allow_manual_journal`,
    `is_transaction_eligible`,
    `is_active`
)
SELECT
    '110111',
    'Q Kas Ayat Silang',
    COALESCE((SELECT `id_klasifikasi` FROM (SELECT `id_klasifikasi` FROM `tbkeu_akun` WHERE `kode_akun` = '11010' LIMIT 1) t1), 1),
    (
        SELECT p.`id_akun`
        FROM (SELECT `id_akun` FROM `tbkeu_akun`) p
        WHERE p.`id_akun` = (SELECT `parent_id` FROM (SELECT `parent_id` FROM `tbkeu_akun` WHERE `kode_akun` = '11010' LIMIT 1) t2)
        LIMIT 1
    ),
    COALESCE((SELECT `level_akun` FROM (SELECT `level_akun` FROM `tbkeu_akun` WHERE `kode_akun` = '11010' LIMIT 1) t3), 3),
    'DEBIT',
    'POSTING',
    'KAS',
    1,
    1,
    1
FROM DUAL
WHERE NOT EXISTS (
    SELECT 1 FROM `tbkeu_akun` WHERE `kode_akun` = '110111' OR LOWER(`nama_akun`) = 'q kas ayat silang'
);
