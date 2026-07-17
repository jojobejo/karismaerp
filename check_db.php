<?php
$db = new mysqli('localhost', 'root', '', 'u471548307_karismaerp');
$res = $db->query('DESCRIBE tb_retur_penjualan');
while ($row = $res->fetch_assoc()) {
    echo $row['Field'] . "\n";
}
