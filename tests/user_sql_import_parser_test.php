<?php
define('BASEPATH', __DIR__);
require_once __DIR__ . '/../application/libraries/Tb_users_sql_parser.php';

$parser = new Tb_users_sql_parser();
$fields = ['id', 'username', 'password', 'nama_lngkp', 'nik', 'bagian', 'departement', 'jabatan', 'level'];
$valid = "-- Data user\nINSERT INTO `tb_users` (`id`,`username`,`password`,`nama_lngkp`,`nik`,`bagian`,`departement`,`jabatan`,`level`) VALUES (7,'uji','rahasia12','O''Neil, Uji','N-7','IT','IT','STAFF',2);";
$rows = $parser->parse($valid, $fields);
if (count($rows) !== 1 || $rows[0]['nama_lngkp'] !== "O'Neil, Uji" || $rows[0]['id'] !== '7') {
    fwrite(STDERR, "Parser gagal membaca INSERT yang valid.\n");
    exit(1);
}

$dump = <<<'SQL'
-- phpMyAdmin SQL Dump
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";
/*!40101 SET NAMES utf8mb4 */;
CREATE TABLE `tb_users` (`id` int NOT NULL, `username` varchar(255) NOT NULL);
INSERT INTO `tb_users` (`id`,`username`,`password`,`nama_lngkp`,`nik`,`bagian`,`departement`,`jabatan`,`level`)
VALUES (8,'dump_uji','rahasia12','Nama Uji','N-8','IT','','STAFF',2);
ALTER TABLE `tb_users` ADD PRIMARY KEY (`id`);
ALTER TABLE `tb_users` MODIFY `id` int NOT NULL AUTO_INCREMENT;
COMMIT;
SQL;
$dumpRows = $parser->parse($dump, $fields);
if (count($dumpRows) !== 1 || $dumpRows[0]['departement'] !== '') {
    fwrite(STDERR, "Parser gagal membaca pembungkus phpMyAdmin.\n");
    exit(1);
}

$invalid = [
    "DROP TABLE tb_users;",
    "INSERT INTO tb_karyawan (username) VALUES ('uji');",
    "INSERT INTO tb_users (username) VALUES ('uji'); DELETE FROM tb_users;",
    "INSERT INTO tb_users (username) VALUES ('uji') ON DUPLICATE KEY UPDATE password='baru';",
    "INSERT INTO tb_users (username) VALUES ('uji'),;",
    "CREATE TABLE tb_karyawan (id int); INSERT INTO tb_users (username) VALUES ('uji');",
];
foreach ($invalid as $sql) {
    try {
        $parser->parse($sql, $fields);
        fwrite(STDERR, "Parser menerima SQL yang tidak diizinkan.\n");
        exit(1);
    } catch (RuntimeException $exception) {
        // Penolakan SQL di luar format adalah hasil yang diharapkan.
    }
}
echo "Parser impor user: INSERT dan dump phpMyAdmin valid, 6 contoh ditolak.\n";
