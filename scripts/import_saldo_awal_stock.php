<?php

declare(strict_types=1);

// Mengurangi peringatan kompatibilitas pustaka lama tanpa menyembunyikan kegagalan impor.
error_reporting(E_ALL & ~E_DEPRECATED & ~E_WARNING & ~E_NOTICE);

require dirname(__DIR__) . '/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

const DATABASE_NAME = 'kiucoid_karismaerp_local';
const IMPORT_REFERENCE = 'IMPORT-SALDO-AWAL-20261031';

/**
 * Mengubah nilai tanggal Excel atau teks tanggal Indonesia menjadi format MySQL.
 */
function normalizeDateCell(Cell $cell, bool $withTime = false): ?string
{
    $value = $cell->getValue();
    if ($value === null || trim((string) $value) === '') {
        return null;
    }

    if (is_numeric($value) && ExcelDate::isDateTime($cell)) {
        $date = ExcelDate::excelToDateTimeObject((float) $value);
        return $date->format($withTime ? 'Y-m-d H:i:s' : 'Y-m-d');
    }

    $text = trim((string) $cell->getFormattedValue());
    $formats = $withTime
        ? ['d/m/Y H.i', 'd/m/Y H:i', 'd-m-Y H.i', 'Y-m-d H:i:s']
        : ['d/m/Y', 'j/n/Y', 'd-m-Y', 'Y-m-d'];

    foreach ($formats as $format) {
        $date = DateTimeImmutable::createFromFormat('!' . $format, $text);
        $errors = DateTimeImmutable::getLastErrors();
        if ($date !== false && ($errors === false || ($errors['warning_count'] === 0 && $errors['error_count'] === 0))) {
            return $date->format($withTime ? 'Y-m-d H:i:s' : 'Y-m-d');
        }
    }

    throw new RuntimeException("Format tanggal tidak dikenali: {$text}");
}

/**
 * Mengambil seluruh kode master untuk validasi referensi.
 */
function fetchMasterSet(mysqli $database, string $sql): array
{
    $result = $database->query($sql);
    $set = [];
    while ($row = $result->fetch_row()) {
        $set[(string) $row[0]] = true;
    }
    return $set;
}

$options = getopt('', ['file:', 'execute']);
$filePath = isset($options['file']) ? (string) $options['file'] : '';
$execute = array_key_exists('execute', $options);

if ($filePath === '' || !is_file($filePath)) {
    fwrite(STDERR, "Gunakan --file=lokasi_file.xlsx dan pastikan file tersedia.\n");
    exit(1);
}

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$database = new mysqli('localhost', 'root', '', DATABASE_NAME);
$database->set_charset('utf8mb4');

$spreadsheet = IOFactory::load($filePath);
$worksheet = $spreadsheet->getSheet(0);
$expectedHeaders = [
    'id',
    'kd_barang',
    'gudang_id',
    'no_lot',
    'expired_date',
    'qty_on_hand',
    'qty_reserved',
    'created_at',
    'update_at',
];

$actualHeaders = [];
foreach (range('A', 'I') as $column) {
    $actualHeaders[] = strtolower(trim((string) $worksheet->getCell($column . '1')->getValue()));
}
if ($actualHeaders !== $expectedHeaders) {
    throw new RuntimeException('Susunan kolom Excel tidak sesuai struktur tberp_stock_batch.');
}

$masterItems = fetchMasterSet($database, 'SELECT DISTINCT kode_barang FROM tbpo_barang');
$masterWarehouses = fetchMasterSet($database, 'SELECT id_gudang FROM tb_gudang WHERE is_active = 1');
$rows = [];
$errors = [];
$warnings = [];
$seenIds = [];
$seenUniqueKeys = [];
$orphanItems = [];
$sourceRowCount = 0;
$mergedRowCount = 0;
$totalOnHand = 0.0;
$totalReserved = 0.0;

for ($rowNumber = 2; $rowNumber <= $worksheet->getHighestDataRow(); $rowNumber++) {
    $idValue = $worksheet->getCell('A' . $rowNumber)->getValue();
    $itemCode = trim((string) $worksheet->getCell('B' . $rowNumber)->getValue());
    $warehouseId = trim((string) $worksheet->getCell('C' . $rowNumber)->getValue());

    if (($idValue === null || $idValue === '') && $itemCode === '' && $warehouseId === '') {
        continue;
    }

    try {
        $sourceRowCount++;
        $id = filter_var($idValue, FILTER_VALIDATE_INT);
        $lotNumber = trim((string) $worksheet->getCell('D' . $rowNumber)->getValue());
        $expiredDate = normalizeDateCell($worksheet->getCell('E' . $rowNumber));
        $qtyOnHand = $worksheet->getCell('F' . $rowNumber)->getValue();
        $qtyReserved = $worksheet->getCell('G' . $rowNumber)->getValue();
        $createdAt = normalizeDateCell($worksheet->getCell('H' . $rowNumber), true);
        $updatedAt = normalizeDateCell($worksheet->getCell('I' . $rowNumber), true);

        if ($id === false || $id <= 0) {
            throw new RuntimeException('ID tidak valid.');
        }
        if ($itemCode === '') {
            throw new RuntimeException('Kode barang kosong.');
        }
        if (!isset($masterItems[$itemCode])) {
            $orphanItems[$itemCode] = true;
            $warnings[] = "Baris {$rowNumber}: kode barang {$itemCode} tidak ditemukan pada master dan dipertahankan sesuai sumber.";
        }
        if ($warehouseId === '' || !isset($masterWarehouses[$warehouseId])) {
            throw new RuntimeException("Gudang {$warehouseId} tidak ditemukan atau tidak aktif.");
        }
        if (!is_numeric($qtyOnHand) || (float) $qtyOnHand < 0) {
            throw new RuntimeException('qty_on_hand harus berupa angka nol atau positif.');
        }
        if (!is_numeric($qtyReserved) || (float) $qtyReserved < 0 || (float) $qtyReserved > (float) $qtyOnHand) {
            throw new RuntimeException('qty_reserved tidak valid.');
        }
        if (isset($seenIds[$id])) {
            throw new RuntimeException("ID {$id} duplikat.");
        }

        $uniqueKey = $itemCode . "\x1F" . $warehouseId . "\x1F" . $lotNumber . "\x1F" . ($expiredDate ?? '<NULL>');
        $seenIds[$id] = true;
        $totalOnHand += (float) $qtyOnHand;
        $totalReserved += (float) $qtyReserved;

        if ($expiredDate !== null && isset($seenUniqueKeys[$uniqueKey])) {
            $targetIndex = $seenUniqueKeys[$uniqueKey];
            $rows[$targetIndex][5] += (float) $qtyOnHand;
            $rows[$targetIndex][6] += (float) $qtyReserved;
            $mergedRowCount++;
            $warnings[] = "Baris {$rowNumber}: batch duplikat digabung ke ID {$rows[$targetIndex][0]} agar sesuai unique key database.";
            continue;
        }

        $seenUniqueKeys[$uniqueKey] = count($rows);
        $rows[] = [
            $id,
            $itemCode,
            $warehouseId,
            $lotNumber,
            $expiredDate,
            (float) $qtyOnHand,
            (float) $qtyReserved,
            $createdAt,
            $updatedAt,
        ];
    } catch (Throwable $exception) {
        $errors[] = "Baris {$rowNumber}: {$exception->getMessage()}";
    }
}

echo 'Mode: ' . ($execute ? 'EKSEKUSI' : 'VALIDASI') . PHP_EOL;
echo 'Baris sumber: ' . $sourceRowCount . PHP_EOL;
echo 'Baris batch hasil normalisasi: ' . count($rows) . PHP_EOL;
echo 'Baris duplikat yang digabung: ' . $mergedRowCount . PHP_EOL;
echo 'Total qty_on_hand: ' . number_format($totalOnHand, 3, '.', '') . PHP_EOL;
echo 'Total qty_reserved: ' . number_format($totalReserved, 3, '.', '') . PHP_EOL;
echo 'Kode barang di luar master: ' . count($orphanItems) . PHP_EOL;
if ($orphanItems !== []) {
    echo 'Daftar kode di luar master: ' . implode(', ', array_keys($orphanItems)) . PHP_EOL;
}
echo 'Peringatan: ' . count($warnings) . PHP_EOL;
echo 'Kesalahan: ' . count($errors) . PHP_EOL;

if ($errors !== []) {
    foreach (array_slice($errors, 0, 50) as $error) {
        echo $error . PHP_EOL;
    }
    exit(2);
}

if (!$execute) {
    exit(0);
}

$batchCount = (int) $database->query('SELECT COUNT(*) FROM tberp_stock_batch')->fetch_row()[0];
$ledgerCount = (int) $database->query('SELECT COUNT(*) FROM tberp_stock_ledger')->fetch_row()[0];
if ($batchCount !== 0 || $ledgerCount !== 0) {
    throw new RuntimeException('Impor dibatalkan karena tabel batch atau ledger stok tidak kosong.');
}

$insertBatch = $database->prepare(
    'INSERT INTO tberp_stock_batch '
    . '(id, kd_barang, gudang_id, no_lot, expired_date, qty_on_hand, qty_reserved, created_at, update_at) '
    . 'VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
);
$insertLedger = $database->prepare(
    'INSERT INTO tberp_stock_ledger '
    . '(kd_barang, gudang_id, no_lot, expired_date, qty, tipe, ref_no, ref_type, created_at) '
    . "VALUES (?, ?, ?, ?, ?, 'SALDO_AWAL', ?, 'IMPORT_EXCEL', ?)"
);

$database->begin_transaction();
try {
    foreach ($rows as $row) {
        [$id, $itemCode, $warehouseId, $lotNumber, $expiredDate, $qtyOnHand, $qtyReserved, $createdAt, $updatedAt] = $row;
        $insertBatch->bind_param(
            'issssddss',
            $id,
            $itemCode,
            $warehouseId,
            $lotNumber,
            $expiredDate,
            $qtyOnHand,
            $qtyReserved,
            $createdAt,
            $updatedAt
        );
        $insertBatch->execute();

        if ($qtyOnHand > 0) {
            $reference = IMPORT_REFERENCE;
            $insertLedger->bind_param(
                'ssssdss',
                $itemCode,
                $warehouseId,
                $lotNumber,
                $expiredDate,
                $qtyOnHand,
                $reference,
                $createdAt
            );
            $insertLedger->execute();
        }
    }
    $database->commit();
} catch (Throwable $exception) {
    $database->rollback();
    throw $exception;
}

echo 'Impor berhasil dan transaksi database sudah di-commit.' . PHP_EOL;
