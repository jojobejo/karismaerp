<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * M_Pricelist Model
 * 
 * Mengelola kalkulasi HPP Average Beli (Weighted Average) dari LPB,
 * pembuatan Pricelist Barang consumable, serta audit trail histori harga.
 */
class M_Pricelist extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->ensure_schema();
    }

    /**
     * Memastikan tabel pendukung Modul Pricelist sudah tersedia di database
     */
    public function ensure_schema()
    {
        // 1. Tabel Summary HPP Average per Barang
        $this->db->query("CREATE TABLE IF NOT EXISTS `tb_barang_hpp_average` (
            `id_hpp` INT(11) NOT NULL AUTO_INCREMENT,
            `kd_barang` VARCHAR(100) NOT NULL,
            `total_qty_lpb` DECIMAL(18,2) NOT NULL DEFAULT 0.00,
            `total_nilai_lpb` DECIMAL(18,4) NOT NULL DEFAULT 0.0000,
            `hpp_avg_dpp` DECIMAL(18,4) NOT NULL DEFAULT 0.0000,
            `hpp_avg_inc_ppn` DECIMAL(18,4) NOT NULL DEFAULT 0.0000,
            `last_lpb_id` INT(11) DEFAULT NULL,
            `last_lpb_date` DATE DEFAULT NULL,
            `last_updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id_hpp`),
            UNIQUE KEY `uk_kd_barang` (`kd_barang`),
            KEY `idx_last_updated` (`last_updated_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");

        // 2. Tabel Pricelist Barang Consumable
        $this->db->query("CREATE TABLE IF NOT EXISTS `tb_pricelist_barang` (
            `id_pricelist` INT(11) NOT NULL AUTO_INCREMENT,
            `kd_barang` VARCHAR(100) NOT NULL,
            `nama_barang` VARCHAR(255) NOT NULL,
            `satuan` VARCHAR(50) NOT NULL,
            `rasio_konversi` DECIMAL(15,2) NOT NULL DEFAULT 1.00,
            `hpp_avg_base` DECIMAL(18,4) NOT NULL DEFAULT 0.0000,
            `margin_persen` DECIMAL(7,2) NOT NULL DEFAULT 0.00,
            `margin_nominal` DECIMAL(18,4) NOT NULL DEFAULT 0.0000,
            `harga_jual_dpp` DECIMAL(18,4) NOT NULL DEFAULT 0.0000,
            `ppn_persen` DECIMAL(5,2) NOT NULL DEFAULT 11.00,
            `ppn_nominal` DECIMAL(18,4) NOT NULL DEFAULT 0.0000,
            `harga_jual_inc_ppn` DECIMAL(18,4) NOT NULL DEFAULT 0.0000,
            `harga_minimum_jual` DECIMAL(18,4) NOT NULL DEFAULT 0.0000,
            `tier_customer` VARCHAR(50) NOT NULL DEFAULT 'REGULAR',
            `status` ENUM('ACTIVE', 'INACTIVE', 'PENDING_APPROVAL') NOT NULL DEFAULT 'ACTIVE',
            `effective_date` DATE NOT NULL,
            `updated_by` VARCHAR(100) DEFAULT NULL,
            `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id_pricelist`),
            UNIQUE KEY `uk_barang_satuan_tier` (`kd_barang`, `satuan`, `tier_customer`),
            KEY `idx_status_active` (`status`, `effective_date`),
            KEY `idx_kd_barang` (`kd_barang`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");

        // 3. Tabel Histori Perubahan Pricelist
        $this->db->query("CREATE TABLE IF NOT EXISTS `tb_pricelist_barang_history` (
            `id_history` INT(11) NOT NULL AUTO_INCREMENT,
            `id_pricelist` INT(11) NOT NULL,
            `kd_barang` VARCHAR(100) NOT NULL,
            `hpp_avg_lama` DECIMAL(18,4) NOT NULL DEFAULT 0.0000,
            `hpp_avg_baru` DECIMAL(18,4) NOT NULL DEFAULT 0.0000,
            `harga_jual_lama` DECIMAL(18,4) NOT NULL DEFAULT 0.0000,
            `harga_jual_baru` DECIMAL(18,4) NOT NULL DEFAULT 0.0000,
            `alasan_perubahan` TEXT DEFAULT NULL,
            `ref_lpb_id` INT(11) DEFAULT NULL,
            `changed_by` VARCHAR(100) DEFAULT NULL,
            `changed_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id_history`),
            KEY `idx_kd_barang` (`kd_barang`),
            KEY `idx_changed_at` (`changed_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");

        // 4. Tambah kolom min_margin_percent pada tbpo_barang jika belum ada
        if ($this->db->table_exists('tbpo_barang') && !$this->db->field_exists('min_margin_percent', 'tbpo_barang')) {
            $this->db->query("ALTER TABLE `tbpo_barang` ADD COLUMN `min_margin_percent` DECIMAL(5,2) DEFAULT 10.00 AFTER `hpp_lifo`");
        }
    }

    /**
     * Menghitung ulang Moving Weighted Average Beli untuk suatu barang dari LPB
     * 
     * @param string $kdBarang
     * @param string|null $user
     * @return array
     */
    public function recalculate_hpp_average($kdBarang, $user = 'SYSTEM')
    {
        $this->ensure_schema();
        $kdBarang = trim((string)$kdBarang);
        if ($kdBarang === '') {
            return ['success' => false, 'message' => 'Kode barang kosong.'];
        }

        // Ambil akumulasi penerimaan barang dari LPB yang valid (status_lpb != 0 / bukan VOID)
        $sqlLPB = "SELECT 
                    SUM(COALESCE(d.qty_diterima, 0)) AS total_qty,
                    SUM(
                        COALESCE(
                            NULLIF(d.dpp, 0),
                            d.qty_diterima * COALESCE(NULLIF(d.harga_satuan, 0), 0),
                            0
                        )
                    ) AS total_nilai_dpp,
                    MAX(l.id_lpb) AS last_lpb_id,
                    MAX(COALESCE(l.tanggal_invoice, l.tgl_sj, l.input_at)) AS last_lpb_date
                FROM tb_lpb_detail d
                INNER JOIN tb_lpb l ON l.id_lpb = d.id_lpb
                WHERE d.kd_barang = ?
                  AND COALESCE(l.status_lpb, 1) != 0";

        $rowLPB = $this->db->query($sqlLPB, [$kdBarang])->row_array();

        // Ambil penyesuaian harga jika ada di tb_lpb_price_adjustment_detail
        $rowAdj = [ 'total_adj_dpp' => 0 ];
        if ($this->db->table_exists('tb_lpb_price_adjustment_detail')) {
            $rowAdj = $this->db->query("SELECT SUM(total_benar - total_salah) AS total_adj_dpp FROM tb_lpb_price_adjustment_detail WHERE kd_barang = ?", [$kdBarang])->row_array();
        }

        $totalQty = (float)($rowLPB['total_qty'] ?? 0);
        $totalNilaiDpp = (float)($rowLPB['total_nilai_dpp'] ?? 0) + (float)($rowAdj['total_adj_dpp'] ?? 0);

        if ($totalQty > 0 && $totalNilaiDpp > 0) {
            $hppAvgDpp = $totalNilaiDpp / $totalQty;
        } else {
            // Fallback: Cek jika barang memiliki PO detail harga atau master default
            $poRow = $this->db->query("SELECT harga_satuan_exclude, harga_satuan_kecil_exclude FROM tbpo_detail_po WHERE kd_barang = ? ORDER BY id_detail_po DESC LIMIT 1", [$kdBarang])->row_array();
            $hppAvgDpp = (float)($poRow['harga_satuan_kecil_exclude'] ?? $poRow['harga_satuan_exclude'] ?? 0);
        }

        $hppAvgIncPpn = $hppAvgDpp * 1.11; // Standard 11% PPN

        // Upsert ke tb_barang_hpp_average
        $existing = $this->db->where('kd_barang', $kdBarang)->get('tb_barang_hpp_average')->row_array();
        $payloadHpp = [
            'kd_barang' => $kdBarang,
            'total_qty_lpb' => $totalQty,
            'total_nilai_lpb' => $totalNilaiDpp,
            'hpp_avg_dpp' => number_format($hppAvgDpp, 4, '.', ''),
            'hpp_avg_inc_ppn' => number_format($hppAvgIncPpn, 4, '.', ''),
            'last_lpb_id' => (int)($rowLPB['last_lpb_id'] ?? 0) ?: null,
            'last_lpb_date' => $rowLPB['last_lpb_date'] ? date('Y-m-d', strtotime($rowLPB['last_lpb_date'])) : null,
            'last_updated_at' => date('Y-m-d H:i:s')
        ];

        if ($existing) {
            $this->db->where('kd_barang', $kdBarang)->update('tb_barang_hpp_average', $payloadHpp);
        } else {
            $this->db->insert('tb_barang_hpp_average', $payloadHpp);
        }

        // Trigger rekalkulasi pricelist barang
        $this->recalculate_pricelist_for_item($kdBarang, $user);

        return [
            'success' => true,
            'kd_barang' => $kdBarang,
            'hpp_avg_dpp' => $hppAvgDpp,
            'hpp_avg_inc_ppn' => $hppAvgIncPpn,
            'total_qty' => $totalQty
        ];
    }

    /**
     * Memperbarui / merekalkulasi snapshot Pricelist untuk suatu barang
     * 
     * @param string $kdBarang
     * @param string $user
     * @return array
     */
    public function recalculate_pricelist_for_item($kdBarang, $user = 'SYSTEM')
    {
        $this->ensure_schema();
        $kdBarang = trim((string)$kdBarang);

        // Ambil HPP Average
        $hppRow = $this->db->where('kd_barang', $kdBarang)->get('tb_barang_hpp_average')->row_array();
        $hppAvgBase = $hppRow ? (float)$hppRow['hpp_avg_dpp'] : 0.0;

        // Ambil Data Master Barang
        $barang = $this->db->where('kode_barang', $kdBarang)->get('tbpo_barang')->row_array();
        if (!$barang) {
            return ['success' => false, 'message' => 'Barang tidak ditemukan di master.'];
        }

        $namaBarang = $barang['nama_barang'] ?? $kdBarang;
        $satuan = $barang['satuan'] ?? 'Pcs';
        $minMarginPcnt = (float)($barang['min_margin_percent'] ?? 10.00);

        // Tiers yang akan disiapkan
        $tiers = ['REGULAR', 'GROSIR', 'DISTRIBUTOR'];
        
        foreach ($tiers as $tier) {
            // Ambil record pricelist eksisting jika ada
            $existing = $this->db->where('kd_barang', $kdBarang)
                ->where('satuan', $satuan)
                ->where('tier_customer', $tier)
                ->get('tb_pricelist_barang')
                ->row_array();

            $marginPersen = $existing ? (float)$existing['margin_persen'] : ($tier === 'DISTRIBUTOR' ? 10.0 : ($tier === 'GROSIR' ? 15.0 : 20.0));
            $marginNominal = $existing ? (float)$existing['margin_nominal'] : 0.0;
            $ppnPersen = $existing ? (float)$existing['ppn_persen'] : 11.00;

            // Perhitungan Harga
            $hargaJualDpp = $hppAvgBase * (1 + ($marginPersen / 100)) + $marginNominal;
            $ppnNominal = $hargaJualDpp * ($ppnPersen / 100);
            $hargaJualIncPpn = $hargaJualDpp + $ppnNominal;
            $hargaMinJual = $hppAvgBase * (1 + ($minMarginPcnt / 100));

            $payloadPL = [
                'kd_barang' => $kdBarang,
                'nama_barang' => $namaBarang,
                'satuan' => $satuan,
                'rasio_konversi' => 1.00,
                'hpp_avg_base' => number_format($hppAvgBase, 4, '.', ''),
                'margin_persen' => number_format($marginPersen, 2, '.', ''),
                'margin_nominal' => number_format($marginNominal, 4, '.', ''),
                'harga_jual_dpp' => number_format($hargaJualDpp, 4, '.', ''),
                'ppn_persen' => number_format($ppnPersen, 2, '.', ''),
                'ppn_nominal' => number_format($ppnNominal, 4, '.', ''),
                'harga_jual_inc_ppn' => number_format($hargaJualIncPpn, 4, '.', ''),
                'harga_minimum_jual' => number_format($hargaMinJual, 4, '.', ''),
                'tier_customer' => $tier,
                'status' => 'ACTIVE',
                'effective_date' => date('Y-m-d'),
                'updated_by' => $user,
                'updated_at' => date('Y-m-d H:i:s')
            ];

            if ($existing) {
                // Catat log histori jika ada perubahan harga jual
                if (abs((float)$existing['harga_jual_inc_ppn'] - $hargaJualIncPpn) > 0.01 || abs((float)$existing['hpp_avg_base'] - $hppAvgBase) > 0.01) {
                    $this->db->insert('tb_pricelist_barang_history', [
                        'id_pricelist' => (int)$existing['id_pricelist'],
                        'kd_barang' => $kdBarang,
                        'hpp_avg_lama' => $existing['hpp_avg_base'],
                        'hpp_avg_baru' => number_format($hppAvgBase, 4, '.', ''),
                        'harga_jual_lama' => $existing['harga_jual_inc_ppn'],
                        'harga_jual_baru' => number_format($hargaJualIncPpn, 4, '.', ''),
                        'alasan_perubahan' => 'Auto Recalculate HPP Average LPB',
                        'ref_lpb_id' => $hppRow['last_lpb_id'] ?? null,
                        'changed_by' => $user,
                        'changed_at' => date('Y-m-d H:i:s')
                    ]);
                }
                $this->db->where('id_pricelist', $existing['id_pricelist'])->update('tb_pricelist_barang', $payloadPL);
            } else {
                $this->db->insert('tb_pricelist_barang', $payloadPL);
                $newId = $this->db->insert_id();
                $this->db->insert('tb_pricelist_barang_history', [
                    'id_pricelist' => (int)$newId,
                    'kd_barang' => $kdBarang,
                    'hpp_avg_lama' => 0,
                    'hpp_avg_baru' => number_format($hppAvgBase, 4, '.', ''),
                    'harga_jual_lama' => 0,
                    'harga_jual_baru' => number_format($hargaJualIncPpn, 4, '.', ''),
                    'alasan_perubahan' => 'Initial Pricelist Creation',
                    'ref_lpb_id' => $hppRow['last_lpb_id'] ?? null,
                    'changed_by' => $user,
                    'changed_at' => date('Y-m-d H:i:s')
                ]);
            }
        }

        return ['success' => true];
    }

    /**
     * Merekalkulasi seluruh barang yang ada di LPB / Master Barang
     * 
     * @param string $user
     * @return array
     */
    public function recalculate_all_items($user = 'SYSTEM')
    {
        $this->ensure_schema();
        $items = $this->db->query("SELECT DISTINCT kd_barang FROM tb_lpb_detail WHERE COALESCE(kd_barang, '') != ''")->result_array();
        $processed = 0;
        foreach ($items as $row) {
            $this->recalculate_hpp_average($row['kd_barang'], $user);
            $processed++;
        }
        return ['success' => true, 'processed_count' => $processed];
    }

    /**
     * Mengambil data Pricelist consumable untuk UI DataTables / API
     * 
     * @param string $search
     * @param string $kelompokDagang
     * @param string $tier
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function get_pricelist_rows($search = '', $kelompokDagang = '', $tier = 'REGULAR', $limit = 50, $offset = 0)
    {
        $this->ensure_schema();
        $search = trim((string)$search);
        $where = "WHERE p.tier_customer = " . $this->db->escape($tier);

        if ($search !== '') {
            $like = '%' . $this->db->escape_like_str($search) . '%';
            $where .= " AND (p.kd_barang LIKE '{$like}' OR p.nama_barang LIKE '{$like}')";
        }
        if ($kelompokDagang !== '') {
            $where .= " AND b.kelompok_dagang = " . $this->db->escape($kelompokDagang);
        }

        $sql = "SELECT p.*, b.kelompok_dagang, b.kelompok_barang, b.kategori_barang,
                       h.total_qty_lpb, h.total_nilai_lpb, h.last_lpb_date
                FROM tb_pricelist_barang p
                LEFT JOIN tbpo_barang b ON b.kode_barang = p.kd_barang
                LEFT JOIN tb_barang_hpp_average h ON h.kd_barang = p.kd_barang
                {$where}
                ORDER BY p.nama_barang ASC
                LIMIT ? OFFSET ?";

        $rows = $this->db->query($sql, [(int)$limit, (int)$offset])->result_array();

        $sqlCount = "SELECT COUNT(*) as total
                     FROM tb_pricelist_barang p
                     LEFT JOIN tbpo_barang b ON b.kode_barang = p.kd_barang
                     {$where}";
        $totalRow = $this->db->query($sqlCount)->row_array();

        return [
            'rows' => $rows,
            'total' => (int)($totalRow['total'] ?? 0)
        ];
    }

    /**
     * Update manual margin/harga jual oleh manajemen/keuangan
     * 
     * @param int $idPricelist
     * @param float $marginPersen
     * @param float $marginNominal
     * @param string $user
     * @return array
     */
    public function update_margin($idPricelist, $marginPersen, $marginNominal = 0.0, $user = 'ADMIN')
    {
        $this->ensure_schema();
        $row = $this->db->where('id_pricelist', (int)$idPricelist)->get('tb_pricelist_barang')->row_array();
        if (!$row) {
            return ['success' => false, 'message' => 'Data pricelist tidak ditemukan.'];
        }

        $hppAvgBase = (float)$row['hpp_avg_base'];
        $ppnPersen = (float)$row['ppn_persen'];

        $hargaJualDpp = $hppAvgBase * (1 + ($marginPersen / 100)) + $marginNominal;
        $ppnNominal = $hargaJualDpp * ($ppnPersen / 100);
        $hargaJualIncPpn = $hargaJualDpp + $ppnNominal;

        $updateData = [
            'margin_persen' => number_format($marginPersen, 2, '.', ''),
            'margin_nominal' => number_format($marginNominal, 4, '.', ''),
            'harga_jual_dpp' => number_format($hargaJualDpp, 4, '.', ''),
            'ppn_nominal' => number_format($ppnNominal, 4, '.', ''),
            'harga_jual_inc_ppn' => number_format($hargaJualIncPpn, 4, '.', ''),
            'updated_by' => $user,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $this->db->where('id_pricelist', (int)$idPricelist)->update('tb_pricelist_barang', $updateData);

        // Audit Log
        $this->db->insert('tb_pricelist_barang_history', [
            'id_pricelist' => (int)$idPricelist,
            'kd_barang' => $row['kd_barang'],
            'hpp_avg_lama' => $row['hpp_avg_base'],
            'hpp_avg_baru' => $row['hpp_avg_base'],
            'harga_jual_lama' => $row['harga_jual_inc_ppn'],
            'harga_jual_baru' => number_format($hargaJualIncPpn, 4, '.', ''),
            'alasan_perubahan' => 'Manual Margin Update by ' . $user,
            'changed_by' => $user,
            'changed_at' => date('Y-m-d H:i:s')
        ]);

        return ['success' => true, 'message' => 'Margin & Harga Jual berhasil diperbarui.'];
    }

    /**
     * Fast Consumable Lookup untuk Sales Order / POS Modul
     * 
     * @param string $kdBarang
     * @param string $tier
     * @return array|null
     */
    public function get_item_price($kdBarang, $tier = 'REGULAR')
    {
        $this->ensure_schema();
        return $this->db->where('kd_barang', trim((string)$kdBarang))
            ->where('tier_customer', $tier)
            ->where('status', 'ACTIVE')
            ->get('tb_pricelist_barang')
            ->row_array();
    }
}
