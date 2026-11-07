<?php
/**
 * Import master customer acak dari Excel + nominal yang sudah terpakai (saldo awal).
 * Pemakaian (CLI): php db/import_customer_acak.php "path/file.xlsx" [--dry-run]
 * Kolom Excel: No | Kode | Nama Toko | Kontak Person | Alamat | Kota | NIK | NPWP | Nominal Terpakai
 */
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
require __DIR__ . '/../vendor/autoload.php';

$file   = $argv[1] ?? '';
$dryRun = in_array('--dry-run', $argv, true);
if (!is_file($file)) die("File tidak ditemukan\n");

$pdo = new PDO('mysql:host=localhost;dbname=kiucoid_karismaerp_admkeu;charset=utf8mb4', 'root', '', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

// Pastikan kolom saldo awal nominal terpakai tersedia
$col = $pdo->query("SHOW COLUMNS FROM tb_customer_acak LIKE 'nominal_terpakai_awal'")->fetch();
if (!$col && !$dryRun) {
    $pdo->exec("ALTER TABLE tb_customer_acak ADD COLUMN nominal_terpakai_awal DECIMAL(18,2) NOT NULL DEFAULT 0 AFTER npwp");
}

// Peta nama_toko -> kd_customer_induk dari data yang sudah ada
$indukByToko = [];
foreach ($pdo->query("SELECT nama_toko, kd_customer_induk FROM tb_customer_acak WHERE kd_customer_induk IS NOT NULL AND kd_customer_induk <> ''") as $r) {
    $indukByToko[strtolower(trim($r['nama_toko']))] = $r['kd_customer_induk'];
}
$existing = array_flip($pdo->query("SELECT kd_customer FROM tb_customer_acak")->fetchAll(PDO::FETCH_COLUMN));

$sheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file)->getSheet(0);
$rows  = $sheet->rangeToArray('A1:I' . $sheet->getHighestDataRow('B'), null, true, false);

$data = [];
foreach ($rows as $r) {
    $kd = strtoupper(trim((string)$r[1]));
    // Lewati header / baris kosong (kode valid diakhiri 'AC')
    if ($kd === '' || !preg_match('/^[A-Z0-9 ]+AC$/', $kd)) continue;
    $nominal = is_numeric($r[8]) ? round((float)$r[8], 2) : 0;
    if (isset($data[$kd])) {
        // Kode duplikat di Excel: nominal dijumlahkan
        $data[$kd]['nominal'] += $nominal;
        continue;
    }
    $data[$kd] = [
        'kd' => $kd, 'toko' => trim((string)$r[2]), 'kontak' => trim((string)$r[3]),
        'alamat' => trim((string)$r[4]), 'kota' => trim((string)$r[5]),
        'nik' => trim((string)$r[6]), 'npwp' => trim((string)$r[7]), 'nominal' => $nominal,
    ];
}

$now = date('Y-m-d H:i:s');
$ins = $upd = 0; $baru = [];
if (!$dryRun) $pdo->beginTransaction();
$stUpd = $pdo->prepare("UPDATE tb_customer_acak SET nominal_terpakai_awal = ?, updated_at = ? WHERE kd_customer = ?");
$stIns = $pdo->prepare("INSERT INTO tb_customer_acak (kd_customer, nama_toko, kd_customer_induk, kontak_person, alamat, kota, nik, npwp, nominal_terpakai_awal, created_at) VALUES (?,?,?,?,?,?,?,?,?,?)");
foreach ($data as $d) {
    if (isset($existing[$d['kd']])) {
        if (!$dryRun) $stUpd->execute([$d['nominal'], $now, $d['kd']]);
        $upd++;
    } else {
        $induk = $indukByToko[strtolower($d['toko'])] ?? null;
        if (!$induk) {
            // Fallback: cari kode induk di master tb_customer berdasarkan nama kios
            $st = $pdo->prepare("SELECT kd_customer FROM tb_customer WHERE nama_kios LIKE ? ORDER BY kd_customer LIMIT 1");
            $st->execute([$d['toko'] . '%']);
            $induk = $st->fetchColumn() ?: null;
        }
        if (!$dryRun) $stIns->execute([$d['kd'], $d['toko'], $induk, $d['kontak'], $d['alamat'], $d['kota'], $d['nik'], $d['npwp'], $d['nominal'], $now]);
        $ins++;
        $baru[] = $d['kd'] . ' | ' . $d['toko'] . ' | induk=' . ($induk ?: '-');
    }
}
if (!$dryRun) $pdo->commit();

echo ($dryRun ? "[DRY RUN] " : "") . "Total baris valid: " . count($data) . "\nUpdate nominal: $upd\nCustomer baru: $ins\n";
foreach ($baru as $b) echo "  + $b\n";
