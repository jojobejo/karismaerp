# Panduan Import Saldo Awal HPP ke tb_lpb & tb_lpb_detail — KARISMA ERP

## 1. Latar Belakang Masalah
Pada sistem **KARISMA ERP**, tabel fisik batch (`tberp_stock_batch` dan `tberp_stock_ledger`) bertugas mencatat kuantitas stok fisik dan lot. Namun, untuk perhitungan **Harga Pokok Penjualan (HPP)** dan **Laporan Kartu Stok Gudang**, sistem merujuk pada histori penerimaan barang di tabel:
- `tb_lpb` (Header Penerimaan Barang)
- `tb_lpb_detail` (Detail Barang & Kolom `harga_satuan`)

Query sistem pada Kartu Stok Gudang (`application/controllers/keuangan/C_Laporan.php`) dan Sales Order (`application/models/M_SalesOrder.php`):
```sql
SELECT ld.kd_barang, l.gudang_id, l.tgl_sj, ld.qty_diterima, ld.harga_satuan
FROM tb_lpb l
JOIN tb_lpb_detail ld ON l.id_lpb = ld.id_lpb
WHERE l.status_lpb = 1;
```
Agar nilai HPP saldo awal diakui di kartu stok dan modul penjualan, data saldo awal harus dimasukkan ke dalam `tb_lpb` dan `tb_lpb_detail`.

---

## 2. Struktur Kolom yang Wajib Terisi

### Tabel `tb_lpb` (Header LPB)
| Kolom | Tipe | Nilai yang Harus Diisi |
|---|---|---|
| `id_lpb` | int (PK, AI) | Auto Increment |
| `nosj` | text | `'SALDO-AWAL-20261031'` |
| `tgl_sj` | date | Tanggal Cut-off (misal: `'2026-10-31'`) |
| `nomor_lpb` | varchar | Nomor referensi (misal: `'LPB/SALDO-AWAL/GDG-02'`) |
| `gudang_id` | int | ID gudang tujuan (harus cocok dengan gudang stok) |
| `nama_suplier` | varchar | `'SALDO AWAL PERSEDIAAN'` |
| `keterangan` | text | `'Saldo Awal Persediaan & HPP Cut-off'` |
| `status_lpb` | tinyint(1) | **1** (Wajib 1 agar aktif dihitung kartu stok) |
| `jenis_lpb` | varchar | `'LPB Saldo Awal'` |
| `source_type` | varchar | `'SALDO_AWAL'` |

### Tabel `tb_lpb_detail` (Detail Item & HPP)
| Kolom | Tipe | Nilai yang Harus Diisi |
|---|---|---|
| `id_detail_lpb` | int (PK, AI) | Auto Increment |
| `id_lpb` | int | Foreign key merujuk ke `tb_lpb.id_lpb` |
| `kd_barang` | varchar | Kode barang |
| `qty_diterima` | decimal(18,2) | Kuantitas saldo awal barang |
| `no_lot` | varchar | Nomor batch/lot (dari `tberp_stock_batch`) |
| `expired_date` | date | Tanggal kedaluwarsa lot |
| `harga_satuan` | decimal(18,4) | **Nilai HPP satuan per pcs/unit** |
| `total_harga` | decimal(18,4) | `qty_diterima * harga_satuan` |
| `dpp` | decimal(15,2) | `qty_diterima * harga_satuan` |

---

## 3. Prosedur Import HPP (Rekomendasi Terbaik)

Karena data kuantitas, lot, dan gudang **sudah ada di tabel `tberp_stock_batch`**, Anda hanya perlu menyiapkan daftar harga HPP per kode barang.

### Langkah 1: Buat Tabel Bantuan Sementara (`tmp_import_hpp`)
Jalankan query ini di phpMyAdmin / SQL console:
```sql
CREATE TABLE IF NOT EXISTS `tmp_import_hpp` (
    `kd_barang` VARCHAR(100) NOT NULL,
    `harga_satuan` DECIMAL(18,4) NOT NULL DEFAULT '0.0000',
    PRIMARY KEY (`kd_barang`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### Langkah 2: Masukkan Data HPP ke `tmp_import_hpp`
Contoh insert manual atau import CSV (kolom: `kd_barang`, `harga_satuan`):
```sql
INSERT INTO `tmp_import_hpp` (`kd_barang`, `harga_satuan`) VALUES
('AACCU01', 125000.0000),
('AALUF01', 45000.0000),
('AAMIC01', 32000.0000),
('AAMOL01', 50000.0000),
('AAREN02', 75000.0000);
```

### Langkah 3: Buat Header `tb_lpb` Saldo Awal per Gudang
Agar kartu stok setiap gudang valid, buat 1 header LPB untuk setiap gudang yang memiliki stok:
```sql
INSERT INTO `tb_lpb` (
    `nosj`, `tgl_sj`, `nomor_lpb`, `gudang_id`, 
    `nama_suplier`, `keterangan`, `status_lpb`, 
    `jenis_lpb`, `source_type`, `input_at`
)
SELECT 
    'SALDO-AWAL-20261031' AS `nosj`,
    '2026-10-31' AS `tgl_sj`,
    CONCAT('LPB/SALDO-AWAL/GDG-', b.`gudang_id`) AS `nomor_lpb`,
    CAST(b.`gudang_id` AS UNSIGNED) AS `gudang_id`,
    'SALDO AWAL PERSEDIAAN' AS `nama_suplier`,
    'Saldo Awal Persediaan dan HPP Cut-off' AS `keterangan`,
    1 AS `status_lpb`,
    'LPB Saldo Awal' AS `jenis_lpb`,
    'SALDO_AWAL' AS `source_type`,
    NOW() AS `input_at`
FROM `tberp_stock_batch` b
WHERE COALESCE(b.`qty_on_hand`, 0) > 0
GROUP BY b.`gudang_id`;
```

### Langkah 4: Insert Data ke `tb_lpb_detail` Otomatis
Gabungkan data stok dari `tberp_stock_batch` dengan HPP dari `tmp_import_hpp`:
```sql
INSERT INTO `tb_lpb_detail` (
    `id_lpb`,
    `kd_barang`,
    `qty_diterima`,
    `no_lot`,
    `expired_date`,
    `harga_satuan`,
    `total_harga`,
    `dpp`,
    `input_at`
)
SELECT 
    l.`id_lpb`,
    b.`kd_barang`,
    b.`qty_on_hand` AS `qty_diterima`,
    COALESCE(b.`no_lot`, '-') AS `no_lot`,
    b.`expired_date`,
    COALESCE(h.`harga_satuan`, 0) AS `harga_satuan`,
    (b.`qty_on_hand` * COALESCE(h.`harga_satuan`, 0)) AS `total_harga`,
    (b.`qty_on_hand` * COALESCE(h.`harga_satuan`, 0)) AS `dpp`,
    NOW() AS `input_at`
FROM `tberp_stock_batch` b
JOIN `tb_lpb` l 
    ON l.`gudang_id` = CAST(b.`gudang_id` AS UNSIGNED) 
   AND l.`source_type` = 'SALDO_AWAL'
LEFT JOIN `tmp_import_hpp` h 
    ON b.`kd_barang` = h.`kd_barang`
WHERE COALESCE(b.`qty_on_hand`, 0) > 0;
```

### Langkah 5: Bersihkan Tabel Bantuan
Setelah proses verifikasi di kartu stok selesai:
```sql
DROP TABLE IF EXISTS `tmp_import_hpp`;
```

