<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Model M_Bundling
 * Mengelola alur Paket Bundling KarismaERP:
 * 1. Master Formula Komposisi
 * 2. Request Bundling Purchasing (kalkulasi otomatis kebutuhan)
 * 3. Monitoring Logistik & Mutasi Bahan (Gudang Induk -> Gudang Bundling)
 * 4. Realisasi Pembuatan Paket / Assembly (Partial & Full Fulfillment)
 * 5. Pembongkaran Paket / Disassembly (Unbundling untuk Penjualan Eceran)
 * 6. Integrasi Single Source of Truth: tberp_stock_batch & tberp_stock_ledger
 */
class M_Bundling extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->model('M_PenyesuaianBarang');
    }

    /**
     * Pastikan tabel-tabel bundling dan gudang bundling tersedia
     */
    public function ensure_bundling_schema()
    {
        if (!$this->db->table_exists('tberp_bundling_formula')) {
            $this->db->query("CREATE TABLE IF NOT EXISTS `tberp_bundling_formula` (
              `id_formula` INT(11) NOT NULL AUTO_INCREMENT,
              `kode_paket` VARCHAR(25) NOT NULL,
              `nama_paket` VARCHAR(255) NOT NULL,
              `satuan_paket` VARCHAR(50) DEFAULT 'Box',
              `keterangan` TEXT DEFAULT NULL,
              `is_active` TINYINT(1) DEFAULT 1,
              `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
              `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
              PRIMARY KEY (`id_formula`),
              UNIQUE KEY `idx_kode_paket` (`kode_paket`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;");
        }

        if (!$this->db->table_exists('tberp_bundling_formula_detail')) {
            $this->db->query("CREATE TABLE IF NOT EXISTS `tberp_bundling_formula_detail` (
              `id_detail` INT(11) NOT NULL AUTO_INCREMENT,
              `id_formula` INT(11) NOT NULL,
              `kode_barang_komponen` VARCHAR(25) NOT NULL,
              `nama_barang_komponen` VARCHAR(255) DEFAULT NULL,
              `qty_komponen` DECIMAL(15,3) NOT NULL DEFAULT 1.000,
              `satuan` VARCHAR(50) DEFAULT 'Pcs',
              PRIMARY KEY (`id_detail`),
              KEY `idx_formula_id` (`id_formula`),
              KEY `idx_komponen` (`kode_barang_komponen`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;");
        }

        if (!$this->db->table_exists('tberp_bundling_request')) {
            $this->db->query("CREATE TABLE IF NOT EXISTS `tberp_bundling_request` (
              `id_request` INT(11) NOT NULL AUTO_INCREMENT,
              `no_request` VARCHAR(50) NOT NULL,
              `tanggal_request` DATE NOT NULL,
              `kode_paket` VARCHAR(25) NOT NULL,
              `nama_paket` VARCHAR(255) NOT NULL,
              `id_gudang_tujuan` INT(11) NOT NULL,
              `id_gudang_asal` INT(11) NOT NULL DEFAULT 2,
              `qty_request` DECIMAL(15,3) NOT NULL,
              `qty_realisasi` DECIMAL(15,3) NOT NULL DEFAULT 0.000,
              `satuan` VARCHAR(50) DEFAULT 'Box',
              `status` ENUM('MENUNGGU_PROSES','PROSES_SEBAGIAN','SELESAI','BATAL') NOT NULL DEFAULT 'MENUNGGU_PROSES',
              `user_request` VARCHAR(50) NOT NULL,
              `keterangan` TEXT DEFAULT NULL,
              `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
              `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
              PRIMARY KEY (`id_request`),
              UNIQUE KEY `idx_no_request` (`no_request`),
              KEY `idx_kode_paket_req` (`kode_paket`),
              KEY `idx_status_req` (`status`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;");
        }

        if (!$this->db->table_exists('tberp_bundling_request_detail')) {
            $this->db->query("CREATE TABLE IF NOT EXISTS `tberp_bundling_request_detail` (
              `id_req_detail` INT(11) NOT NULL AUTO_INCREMENT,
              `id_request` INT(11) NOT NULL,
              `kode_barang_komponen` VARCHAR(25) NOT NULL,
              `nama_barang_komponen` VARCHAR(255) DEFAULT NULL,
              `qty_per_paket` DECIMAL(15,3) NOT NULL DEFAULT 1.000,
              `qty_total_kebutuhan` DECIMAL(15,3) NOT NULL,
              `qty_terpenuhi` DECIMAL(15,3) NOT NULL DEFAULT 0.000,
              `satuan` VARCHAR(50) DEFAULT 'Pcs',
              PRIMARY KEY (`id_req_detail`),
              KEY `idx_request_id` (`id_request`),
              KEY `idx_req_komponen` (`kode_barang_komponen`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;");
        }

        if (!$this->db->table_exists('tberp_bundling_assembly')) {
            $this->db->query("CREATE TABLE IF NOT EXISTS `tberp_bundling_assembly` (
              `id_assembly` INT(11) NOT NULL AUTO_INCREMENT,
              `no_assembly` VARCHAR(50) NOT NULL,
              `id_request` INT(11) DEFAULT NULL,
              `no_request` VARCHAR(50) DEFAULT NULL,
              `tanggal` DATE NOT NULL,
              `kode_paket` VARCHAR(25) NOT NULL,
              `nama_paket` VARCHAR(255) NOT NULL,
              `id_gudang` INT(11) NOT NULL,
              `qty_assembly` DECIMAL(15,3) NOT NULL,
              `satuan` VARCHAR(50) DEFAULT 'Box',
              `no_lot_paket` VARCHAR(100) DEFAULT '-',
              `expired_date_paket` DATE DEFAULT NULL,
              `total_nilai_hpp` DECIMAL(18,2) DEFAULT 0.00,
              `hpp_per_paket` DECIMAL(18,2) DEFAULT 0.00,
              `user_input` VARCHAR(50) NOT NULL,
              `keterangan` TEXT DEFAULT NULL,
              `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
              PRIMARY KEY (`id_assembly`),
              UNIQUE KEY `idx_no_assembly` (`no_assembly`),
              KEY `idx_assembly_request` (`id_request`),
              KEY `idx_assembly_paket` (`kode_paket`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;");
        }

        if (!$this->db->table_exists('tberp_bundling_assembly_detail')) {
            $this->db->query("CREATE TABLE IF NOT EXISTS `tberp_bundling_assembly_detail` (
              `id_asm_detail` INT(11) NOT NULL AUTO_INCREMENT,
              `id_assembly` INT(11) NOT NULL,
              `kode_barang_komponen` VARCHAR(25) NOT NULL,
              `nama_barang_komponen` VARCHAR(255) DEFAULT NULL,
              `no_lot` VARCHAR(100) DEFAULT '-',
              `expired_date` DATE DEFAULT NULL,
              `qty_digunakan` DECIMAL(15,3) NOT NULL,
              `satuan` VARCHAR(50) DEFAULT 'Pcs',
              `hpp_satuan` DECIMAL(18,2) DEFAULT 0.00,
              `total_hpp` DECIMAL(18,2) DEFAULT 0.00,
              PRIMARY KEY (`id_asm_detail`),
              KEY `idx_asm_parent` (`id_assembly`),
              KEY `idx_asm_komponen` (`kode_barang_komponen`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;");
        }

        if (!$this->db->table_exists('tberp_bundling_disassembly')) {
            $this->db->query("CREATE TABLE IF NOT EXISTS `tberp_bundling_disassembly` (
              `id_disassembly` INT(11) NOT NULL AUTO_INCREMENT,
              `no_disassembly` VARCHAR(50) NOT NULL,
              `tanggal` DATE NOT NULL,
              `kode_paket` VARCHAR(25) NOT NULL,
              `nama_paket` VARCHAR(255) NOT NULL,
              `id_gudang` INT(11) NOT NULL,
              `qty_disassembly` DECIMAL(15,3) NOT NULL,
              `satuan` VARCHAR(50) DEFAULT 'Box',
              `no_lot_paket` VARCHAR(100) DEFAULT '-',
              `expired_date_paket` DATE DEFAULT NULL,
              `total_nilai_hpp` DECIMAL(18,2) DEFAULT 0.00,
              `alasan` TEXT NOT NULL,
              `user_input` VARCHAR(50) NOT NULL,
              `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
              PRIMARY KEY (`id_disassembly`),
              UNIQUE KEY `idx_no_disassembly` (`no_disassembly`),
              KEY `idx_dsb_paket` (`kode_paket`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;");
        }

        if (!$this->db->table_exists('tberp_bundling_disassembly_detail')) {
            $this->db->query("CREATE TABLE IF NOT EXISTS `tberp_bundling_disassembly_detail` (
              `id_dsb_detail` INT(11) NOT NULL AUTO_INCREMENT,
              `id_disassembly` INT(11) NOT NULL,
              `kode_barang_komponen` VARCHAR(25) NOT NULL,
              `nama_barang_komponen` VARCHAR(255) DEFAULT NULL,
              `no_lot` VARCHAR(100) DEFAULT '-',
              `expired_date` DATE DEFAULT NULL,
              `qty_kembali` DECIMAL(15,3) NOT NULL,
              `satuan` VARCHAR(50) DEFAULT 'Pcs',
              `hpp_satuan` DECIMAL(18,2) DEFAULT 0.00,
              `total_hpp` DECIMAL(18,2) DEFAULT 0.00,
              PRIMARY KEY (`id_dsb_detail`),
              KEY `idx_dsb_parent` (`id_disassembly`),
              KEY `idx_dsb_komponen` (`kode_barang_komponen`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;");
        }

        // Pastikan Gudang Bundling ada di tb_gudang
        $cekGudang = $this->db->where('id_gudang', 12)->or_like('nama_gudang', 'Bundling')->get('tb_gudang')->row();
        if (!$cekGudang) {
            $this->db->insert('tb_gudang', [
                'id_gudang'   => 12,
                'nama_gudang' => 'Gdg. Bundling',
                'tipe'        => 'INDUK',
                'is_active'   => 1,
                'created_at'  => date('Y-m-d H:i:s')
            ]);
        }

        // Pastikan kolom estimasi HPP & modal ada di tberp_bundling_request
        if ($this->db->table_exists('tberp_bundling_request')) {
            if (!$this->db->field_exists('estimasi_hpp_per_paket', 'tberp_bundling_request')) {
                $this->db->query("ALTER TABLE `tberp_bundling_request` ADD COLUMN `estimasi_hpp_per_paket` DECIMAL(18,2) DEFAULT 0.00 AFTER `satuan`, ADD COLUMN `estimasi_total_modal` DECIMAL(18,2) DEFAULT 0.00 AFTER `estimasi_hpp_per_paket`");
            }
        }

        // Pastikan kolom HPP satuan ada di tberp_bundling_request_detail
        if ($this->db->table_exists('tberp_bundling_request_detail')) {
            if (!$this->db->field_exists('hpp_satuan', 'tberp_bundling_request_detail')) {
                $this->db->query("ALTER TABLE `tberp_bundling_request_detail` ADD COLUMN `hpp_satuan` DECIMAL(18,2) DEFAULT 0.00 AFTER `qty_terpenuhi`, ADD COLUMN `subtotal_hpp` DECIMAL(18,2) DEFAULT 0.00 AFTER `hpp_satuan`");
            }
        }

        // Pastikan kolom kemasan innerbox ada di tberp_bundling_formula_detail
        if ($this->db->table_exists('tberp_bundling_formula_detail')) {
            if (!$this->db->field_exists('is_innerbox', 'tberp_bundling_formula_detail')) {
                $this->db->query("ALTER TABLE `tberp_bundling_formula_detail` 
                    ADD COLUMN `is_innerbox` TINYINT(1) NOT NULL DEFAULT 0 AFTER `satuan`,
                    ADD COLUMN `qty_innerbox` DECIMAL(15,3) NOT NULL DEFAULT 0.000 AFTER `is_innerbox`,
                    ADD COLUMN `isi_per_innerbox` DECIMAL(15,3) NOT NULL DEFAULT 0.000 AFTER `qty_innerbox`,
                    ADD COLUMN `satuan_innerbox` VARCHAR(30) DEFAULT 'Innerbox' AFTER `isi_per_innerbox`");
            }
        }

        // Pastikan kolom kemasan innerbox ada di tberp_bundling_request_detail
        if ($this->db->table_exists('tberp_bundling_request_detail')) {
            if (!$this->db->field_exists('is_innerbox', 'tberp_bundling_request_detail')) {
                $this->db->query("ALTER TABLE `tberp_bundling_request_detail` 
                    ADD COLUMN `is_innerbox` TINYINT(1) NOT NULL DEFAULT 0 AFTER `satuan`,
                    ADD COLUMN `qty_innerbox` DECIMAL(15,3) NOT NULL DEFAULT 0.000 AFTER `is_innerbox`,
                    ADD COLUMN `isi_per_innerbox` DECIMAL(15,3) NOT NULL DEFAULT 0.000 AFTER `qty_innerbox`,
                    ADD COLUMN `satuan_innerbox` VARCHAR(30) DEFAULT 'Innerbox' AFTER `isi_per_innerbox`,
                    ADD COLUMN `total_innerbox_kebutuhan` DECIMAL(15,3) NOT NULL DEFAULT 0.000 AFTER `satuan_innerbox`");
            }
        }

        // Pastikan kolom kemasan innerbox ada di tberp_bundling_assembly_detail
        if ($this->db->table_exists('tberp_bundling_assembly_detail')) {
            if (!$this->db->field_exists('is_innerbox', 'tberp_bundling_assembly_detail')) {
                $this->db->query("ALTER TABLE `tberp_bundling_assembly_detail` 
                    ADD COLUMN `is_innerbox` TINYINT(1) NOT NULL DEFAULT 0 AFTER `satuan`,
                    ADD COLUMN `qty_innerbox` DECIMAL(15,3) NOT NULL DEFAULT 0.000 AFTER `is_innerbox`,
                    ADD COLUMN `isi_per_innerbox` DECIMAL(15,3) NOT NULL DEFAULT 0.000 AFTER `qty_innerbox`");
            }
        }

        // Pastikan kolom referensi penyesuaian barang ada di tberp_bundling_assembly
        if ($this->db->table_exists('tberp_bundling_assembly')) {
            if (!$this->db->field_exists('id_penyesuaian', 'tberp_bundling_assembly')) {
                $this->db->query("ALTER TABLE `tberp_bundling_assembly` ADD COLUMN `id_penyesuaian` BIGINT(20) DEFAULT NULL AFTER `id_gudang`");
            }
            if (!$this->db->field_exists('no_penyesuaian', 'tberp_bundling_assembly')) {
                $this->db->query("ALTER TABLE `tberp_bundling_assembly` ADD COLUMN `no_penyesuaian` VARCHAR(50) DEFAULT NULL AFTER `id_penyesuaian`");
            }
            if (!$this->db->field_exists('id_gudang_asal', 'tberp_bundling_assembly')) {
                $this->db->query("ALTER TABLE `tberp_bundling_assembly` ADD COLUMN `id_gudang_asal` INT(11) DEFAULT 2 AFTER `id_request`");
            }
        }

        // Pastikan kolom konfigurasi kemasan innerbox & biaya kemasan ada di level header tberp_bundling_formula
        if ($this->db->table_exists('tberp_bundling_formula')) {
            if (!$this->db->field_exists('is_innerbox', 'tberp_bundling_formula')) {
                $this->db->query("ALTER TABLE `tberp_bundling_formula` 
                    ADD COLUMN `is_innerbox` TINYINT(1) NOT NULL DEFAULT 0 AFTER `satuan_paket`,
                    ADD COLUMN `jumlah_innerbox` DECIMAL(15,3) NOT NULL DEFAULT 0.000 AFTER `is_innerbox`,
                    ADD COLUMN `satuan_innerbox` VARCHAR(50) DEFAULT 'Innerbox' AFTER `jumlah_innerbox`");
            }
            if (!$this->db->field_exists('biaya_innerbox', 'tberp_bundling_formula')) {
                $this->db->query("ALTER TABLE `tberp_bundling_formula` 
                    ADD COLUMN `biaya_innerbox` DECIMAL(18,2) NOT NULL DEFAULT 0.00 AFTER `satuan_innerbox`,
                    ADD COLUMN `biaya_outerbox` DECIMAL(18,2) NOT NULL DEFAULT 0.00 AFTER `biaya_innerbox`,
                    ADD COLUMN `biaya_kemasan_lain` DECIMAL(18,2) NOT NULL DEFAULT 0.00 AFTER `biaya_outerbox`,
                    ADD COLUMN `keterangan_biaya_kemasan` VARCHAR(255) DEFAULT NULL AFTER `biaya_kemasan_lain`");
            }
            if (!$this->db->field_exists('rincian_biaya_kemasan', 'tberp_bundling_formula')) {
                $this->db->query("ALTER TABLE `tberp_bundling_formula` ADD COLUMN `rincian_biaya_kemasan` TEXT DEFAULT NULL AFTER `keterangan_biaya_kemasan`");
            }
        }

        // Pastikan kolom konfigurasi kemasan innerbox & biaya kemasan ada di level header tberp_bundling_request
        if ($this->db->table_exists('tberp_bundling_request')) {
            if (!$this->db->field_exists('is_innerbox', 'tberp_bundling_request')) {
                $this->db->query("ALTER TABLE `tberp_bundling_request` 
                    ADD COLUMN `is_innerbox` TINYINT(1) NOT NULL DEFAULT 0 AFTER `satuan`,
                    ADD COLUMN `jumlah_innerbox` DECIMAL(15,3) NOT NULL DEFAULT 0.000 AFTER `is_innerbox`,
                    ADD COLUMN `total_innerbox` DECIMAL(15,3) NOT NULL DEFAULT 0.000 AFTER `jumlah_innerbox`,
                    ADD COLUMN `satuan_innerbox` VARCHAR(50) DEFAULT 'Innerbox' AFTER `total_innerbox`");
            }
            if (!$this->db->field_exists('biaya_innerbox', 'tberp_bundling_request')) {
                $this->db->query("ALTER TABLE `tberp_bundling_request` 
                    ADD COLUMN `biaya_innerbox` DECIMAL(18,2) NOT NULL DEFAULT 0.00 AFTER `satuan_innerbox`,
                    ADD COLUMN `biaya_outerbox` DECIMAL(18,2) NOT NULL DEFAULT 0.00 AFTER `biaya_innerbox`,
                    ADD COLUMN `biaya_kemasan_lain` DECIMAL(18,2) NOT NULL DEFAULT 0.00 AFTER `biaya_outerbox`,
                    ADD COLUMN `keterangan_biaya_kemasan` VARCHAR(255) DEFAULT NULL AFTER `biaya_kemasan_lain`,
                    ADD COLUMN `total_biaya_kemasan_per_paket` DECIMAL(18,2) NOT NULL DEFAULT 0.00 AFTER `keterangan_biaya_kemasan`,
                    ADD COLUMN `total_biaya_kemasan_keseluruhan` DECIMAL(18,2) NOT NULL DEFAULT 0.00 AFTER `total_biaya_kemasan_per_paket`,
                    ADD COLUMN `estimasi_hpp_bahan_per_paket` DECIMAL(18,2) NOT NULL DEFAULT 0.00 AFTER `total_biaya_kemasan_keseluruhan`");
            }
            if (!$this->db->field_exists('rincian_biaya_kemasan', 'tberp_bundling_request')) {
                $this->db->query("ALTER TABLE `tberp_bundling_request` ADD COLUMN `rincian_biaya_kemasan` TEXT DEFAULT NULL AFTER `keterangan_biaya_kemasan`");
            }
        }
    }

    // =========================================================================
    // 1. MASTER FORMULA KOMPOSISI BUNDLING
    // =========================================================================

    public function get_all_formulas($search = '')
    {
        $this->ensure_bundling_schema();
        $this->db->select('f.*, COUNT(d.id_detail) as total_komponen');
        $this->db->from('tberp_bundling_formula f');
        $this->db->join('tberp_bundling_formula_detail d', 'd.id_formula = f.id_formula', 'left');
        if (!empty($search)) {
            $this->db->group_start();
            $this->db->like('f.kode_paket', $search);
            $this->db->or_like('f.nama_paket', $search);
            $this->db->group_end();
        }
        $this->db->group_by('f.id_formula');
        $this->db->order_by('f.nama_paket', 'ASC');
        return $this->db->get()->result_array();
    }

    /**
     * Mem-parsing daftar item biaya kemasan dinamis dari JSON atau fallback ke nilai kolom statis
     */
    public function parse_packaging_items($row)
    {
        $items = [];
        if (!empty($row['rincian_biaya_kemasan'])) {
            $decoded = json_decode($row['rincian_biaya_kemasan'], true);
            if (is_array($decoded) && !empty($decoded)) {
                foreach ($decoded as $it) {
                    $nama = trim($it['nama'] ?? '');
                    $nominal = (float)str_replace(',', '', $it['nominal'] ?? 0);
                    if ($nama !== '' || $nominal > 0) {
                        $items[] = [
                            'nama'    => $nama !== '' ? $nama : 'Biaya Kemasan / Printilan',
                            'nominal' => $nominal
                        ];
                    }
                }
            }
        }

        // Fallback jika JSON kosong tetapi ada data statis lama
        if (empty($items)) {
            $bInner = (float)($row['biaya_innerbox'] ?? 0);
            $bOuter = (float)($row['biaya_outerbox'] ?? 0);
            $bLain  = (float)($row['biaya_kemasan_lain'] ?? 0);
            $isInbox = !empty($row['is_innerbox']);
            $jmlInbox = (float)($row['jumlah_innerbox'] ?? 0);
            $subInner = ($isInbox && $jmlInbox > 0) ? ($jmlInbox * $bInner) : 0.0;

            if ($subInner > 0) {
                $items[] = [
                    'nama'    => 'Kardus Innerbox (' . (int)$jmlInbox . ' ' . ($row['satuan_innerbox'] ?? 'Innerbox') . ')',
                    'nominal' => $subInner
                ];
            }
            if ($bOuter > 0) {
                $items[] = [
                    'nama'    => 'Kardus Outer (Master Box)',
                    'nominal' => $bOuter
                ];
            }
            if ($bLain > 0) {
                $items[] = [
                    'nama'    => !empty($row['keterangan_biaya_kemasan']) ? $row['keterangan_biaya_kemasan'] : 'Stiker Hologram & Printilan',
                    'nominal' => $bLain
                ];
            }
        }

        // Jika benar-benar kosong, sediakan 1 baris inputan default
        if (empty($items)) {
            $items[] = [
                'nama'    => '',
                'nominal' => 0.0
            ];
        }

        return $items;
    }

    public function get_formula_by_id($id_formula)
    {
        $this->ensure_bundling_schema();
        $formula = $this->db->where('id_formula', $id_formula)->get('tberp_bundling_formula')->row_array();
        if ($formula) {
            $formula['details'] = $this->db->where('id_formula', $id_formula)->get('tberp_bundling_formula_detail')->result_array();
            $formula['kemasan_items'] = $this->parse_packaging_items($formula);
        }
        return $formula;
    }

    public function get_formula_by_kode_paket($kode_paket)
    {
        $this->ensure_bundling_schema();
        $formula = $this->db->where('kode_paket', $kode_paket)->get('tberp_bundling_formula')->row_array();
        if ($formula) {
            $formula['details'] = $this->db->where('id_formula', $formula['id_formula'])->get('tberp_bundling_formula_detail')->result_array();
            $formula['kemasan_items'] = $this->parse_packaging_items($formula);
        }
        return $formula;
    }

    public function save_formula($data, $details)
    {
        $this->ensure_bundling_schema();
        $this->db->trans_begin();

        $kodePaket = !empty($data['kode_paket']) ? trim($data['kode_paket']) : '';
        if ($kodePaket === '') {
            $kodePaket = 'FML-' . date('ymd') . '-' . substr(str_shuffle('0123456789ABCDEF'), 0, 4);
        }

        $id = !empty($data['id_formula']) ? (int)$data['id_formula'] : 0;
        if ($id <= 0) {
            $existing = $this->db->where('kode_paket', $kodePaket)->limit(1)->get('tberp_bundling_formula')->row();
            if ($existing) {
                $id = (int)$existing->id_formula;
            }
        }

        $isInnerboxFormula = !empty($data['is_innerbox']) ? 1 : 0;
        $jmlInnerboxFormula = $isInnerboxFormula ? (float)($data['jumlah_innerbox'] ?? 1) : 0.000;
        $satInnerboxFormula = $isInnerboxFormula ? (!empty($data['satuan_innerbox']) ? trim($data['satuan_innerbox']) : 'Innerbox') : 'Innerbox';

        // Proses rincian item biaya kemasan dinamis
        $rawKemasan = $data['kemasan_items'] ?? $data['rincian_biaya_kemasan'] ?? [];
        if (is_string($rawKemasan)) {
            $rawKemasan = json_decode($rawKemasan, true) ?: [];
        }

        $validKemasan = [];
        $totalKemasanFml = 0.0;
        $summaryKemasan = [];

        if (is_array($rawKemasan)) {
            foreach ($rawKemasan as $k) {
                $nm = trim($k['nama'] ?? '');
                $nom = (float)str_replace(',', '', $k['nominal'] ?? 0);
                if ($nm !== '' || $nom > 0) {
                    if ($nm === '') $nm = 'Biaya Kemasan';
                    $validKemasan[] = ['nama' => $nm, 'nominal' => $nom];
                    $totalKemasanFml += $nom;
                    $summaryKemasan[] = $nm . ' (Rp ' . number_format($nom, 0, ',', '.') . ')';
                }
            }
        }

        // Fallback jika tidak ada kemasan_items tetapi ada field lama
        $biayaInnerbox = (float)($data['biaya_innerbox'] ?? 0);
        $biayaOuterbox = (float)($data['biaya_outerbox'] ?? 0);
        $biayaKemasanLain = (float)($data['biaya_kemasan_lain'] ?? 0);
        if (empty($validKemasan) && ($biayaInnerbox > 0 || $biayaOuterbox > 0 || $biayaKemasanLain > 0)) {
            $subInner = $isInnerboxFormula ? ($jmlInnerboxFormula * $biayaInnerbox) : 0.0;
            if ($subInner > 0) $validKemasan[] = ['nama' => 'Kardus Innerbox', 'nominal' => $subInner];
            if ($biayaOuterbox > 0) $validKemasan[] = ['nama' => 'Kardus Outer (Master Box)', 'nominal' => $biayaOuterbox];
            if ($biayaKemasanLain > 0) $validKemasan[] = ['nama' => !empty($data['keterangan_biaya_kemasan']) ? $data['keterangan_biaya_kemasan'] : 'Printilan / Stiker Hologram', 'nominal' => $biayaKemasanLain];
            $totalKemasanFml = $subInner + $biayaOuterbox + $biayaKemasanLain;
        }

        $rincianKemasanJson = !empty($validKemasan) ? json_encode($validKemasan, JSON_UNESCAPED_UNICODE) : null;
        $ketBiayaKemasan = !empty($summaryKemasan) ? implode(', ', $summaryKemasan) : (!empty($data['keterangan_biaya_kemasan']) ? trim($data['keterangan_biaya_kemasan']) : null);

        $formulaHeader = [
            'kode_paket'               => $kodePaket,
            'nama_paket'               => $data['nama_paket'],
            'satuan_paket'             => $data['satuan_paket'] ?? 'Box',
            'is_innerbox'              => $isInnerboxFormula,
            'jumlah_innerbox'          => $jmlInnerboxFormula,
            'satuan_innerbox'          => $satInnerboxFormula,
            'biaya_innerbox'           => $biayaInnerbox,
            'biaya_outerbox'           => $biayaOuterbox,
            'biaya_kemasan_lain'       => $biayaKemasanLain,
            'keterangan_biaya_kemasan' => $ketBiayaKemasan,
            'rincian_biaya_kemasan'    => $rincianKemasanJson,
            'keterangan'               => $data['keterangan'] ?? null,
            'is_active'                => isset($data['is_active']) ? (int)$data['is_active'] : 1
        ];

        if ($id > 0) {
            $this->db->where('id_formula', $id)->update('tberp_bundling_formula', $formulaHeader);
            $this->db->where('id_formula', $id)->delete('tberp_bundling_formula_detail');
        } else {
            $formulaHeader['created_at'] = date('Y-m-d H:i:s');
            $this->db->insert('tberp_bundling_formula', $formulaHeader);
            $id = $this->db->insert_id();
        }

        if (!empty($details)) {
            $insertDetails = [];
            foreach ($details as $d) {
                if (empty($d['kode_barang_komponen'])) continue;

                if ($isInnerboxFormula) {
                    $isiPerInnerbox = (float)($d['isi_per_innerbox'] ?? 0);
                    if ($isiPerInnerbox <= 0 && !empty($d['qty_komponen'])) {
                        // Fallback jika dikirim qty_komponen langsung
                        $isiPerInnerbox = $jmlInnerboxFormula > 0 ? ((float)$d['qty_komponen'] / $jmlInnerboxFormula) : (float)$d['qty_komponen'];
                    }
                    $qtyKomponen = $jmlInnerboxFormula * $isiPerInnerbox;
                    $qtyInnerbox = $jmlInnerboxFormula;
                    $satInnerbox = $satInnerboxFormula;
                } else {
                    $isiPerInnerbox = 0.000;
                    $qtyInnerbox = 0.000;
                    $qtyKomponen = (float)($d['qty_komponen'] ?? 1);
                    $satInnerbox = null;
                }

                $insertDetails[] = [
                    'id_formula'           => $id,
                    'kode_barang_komponen' => $d['kode_barang_komponen'],
                    'nama_barang_komponen' => $d['nama_barang_komponen'] ?? '',
                    'qty_komponen'         => $qtyKomponen,
                    'satuan'               => $d['satuan'] ?? 'Pcs',
                    'is_innerbox'          => $isInnerboxFormula,
                    'qty_innerbox'         => $qtyInnerbox,
                    'isi_per_innerbox'     => $isiPerInnerbox,
                    'satuan_innerbox'      => $satInnerbox
                ];
            }
            if (!empty($insertDetails)) {
                $this->db->insert_batch('tberp_bundling_formula_detail', $insertDetails);
            }
        }

        // Otomatis daftarkan / perbarui produk paket di Master Barang (tbpo_barang & tbpo_barang_akun)
        $this->ensure_product_in_master_barang($kodePaket, $data['nama_paket'], $data['satuan_paket'] ?? 'Box');

        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            return ['status' => false, 'msg' => 'Gagal menyimpan formula bundling'];
        }

        $this->db->trans_commit();
        return ['status' => true, 'id_formula' => $id, 'msg' => 'Formula bundling berhasil disimpan dan didaftarkan ke Master Barang'];
    }

    // =========================================================================
    // 2. REQUEST PAKET BUNDLING (PURCHASING)
    // =========================================================================

    public function generate_request_number()
    {
        $this->ensure_bundling_schema();
        $dateStr = date('Ymd');
        $prefix = 'RPB-' . $dateStr . '-';

        $last = $this->db->select('no_request')
            ->like('no_request', $prefix, 'after')
            ->order_by('id_request', 'DESC')
            ->limit(1)
            ->get('tberp_bundling_request')
            ->row();

        $num = 1;
        if ($last) {
            $parts = explode('-', $last->no_request);
            if (isset($parts[2]) && is_numeric($parts[2])) {
                $num = (int)$parts[2] + 1;
            }
        }
        
        $newNo = $prefix . sprintf('%04d', $num);
        // Pastikan nomor benar-benar belum terpakai di database
        while ($this->db->where('no_request', $newNo)->count_all_results('tberp_bundling_request') > 0) {
            $num++;
            $newNo = $prefix . sprintf('%04d', $num);
        }

        return $newNo;
    }

    public function get_requests($filters = [], $limit = 200)
    {
        $this->ensure_bundling_schema();
        $this->db->select('r.*, gt.nama_gudang as nama_gudang_tujuan, ga.nama_gudang as nama_gudang_asal');
        $this->db->from('tberp_bundling_request r');
        $this->db->join('tb_gudang gt', 'gt.id_gudang = r.id_gudang_tujuan', 'left');
        $this->db->join('tb_gudang ga', 'ga.id_gudang = r.id_gudang_asal', 'left');

        if (!empty($filters['status']) && $filters['status'] !== 'SEMUA') {
            $this->db->where('r.status', $filters['status']);
        }
        if (!empty($filters['search'])) {
            $this->db->group_start();
            $this->db->like('r.no_request', $filters['search']);
            $this->db->or_like('r.nama_paket', $filters['search']);
            $this->db->or_like('r.kode_paket', $filters['search']);
            $this->db->group_end();
        }
        if (!empty($filters['date_from'])) {
            $this->db->where('r.tanggal_request >=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $this->db->where('r.tanggal_request <=', $filters['date_to']);
        }

        $this->db->order_by('r.id_request', 'DESC');
        $this->db->limit($limit);
        return $this->db->get()->result_array();
    }

    public function get_request_by_id($id_request)
    {
        $this->ensure_bundling_schema();
        $this->db->select('r.*, gt.nama_gudang as nama_gudang_tujuan, ga.nama_gudang as nama_gudang_asal');
        $this->db->from('tberp_bundling_request r');
        $this->db->join('tb_gudang gt', 'gt.id_gudang = r.id_gudang_tujuan', 'left');
        $this->db->join('tb_gudang ga', 'ga.id_gudang = r.id_gudang_asal', 'left');
        $this->db->where('r.id_request', $id_request);
        $header = $this->db->get()->row_array();

        if ($header) {
            $header['details'] = $this->db->where('id_request', $id_request)->get('tberp_bundling_request_detail')->result_array();
            // Ambil juga histori assembly yang telah dibuat berdasarkan request ini
            $header['assemblies'] = $this->db->where('id_request', $id_request)->order_by('id_assembly', 'DESC')->get('tberp_bundling_assembly')->result_array();

            // Hitung estimasi HPP per paket dan total modal dari rincian komponen menggunakan metode LIFO (Last In, First Out)
            $estHppPerPaket = 0.0;
            $estTotalModal = 0.0;
            $qtyReq = (float)$header['qty_request'];

            foreach ($header['details'] as $idxDet => $det) {
                $kd = $det['kode_barang_komponen'];
                $totalButuh = (float)$det['qty_total_kebutuhan'];

                $lifo = $this->calculate_item_lifo_cost($kd, $totalButuh, $header['id_gudang_asal'], $det['nama_barang_komponen']);
                $hpp = $lifo['hpp_satuan'];
                $modalTotalItem = $lifo['total_modal'];
                $subtotalHpp = ($qtyReq > 0) ? ($modalTotalItem / $qtyReq) : ((float)$det['qty_per_paket'] * $hpp);

                $header['details'][$idxDet]['hpp_satuan'] = $hpp;
                $header['details'][$idxDet]['subtotal_hpp'] = $subtotalHpp;
                $header['details'][$idxDet]['total_modal_kebutuhan'] = $modalTotalItem;
                $header['details'][$idxDet]['lifo_breakdown'] = $lifo['breakdown'];

                $estHppPerPaket += $subtotalHpp;
                $estTotalModal += $modalTotalItem;
            }

            $header['estimasi_hpp_bahan_per_paket'] = $estHppPerPaket;

            // Parsing dan hitung biaya kemasan serta printilan dinamis
            $header['kemasan_items'] = $this->parse_packaging_items($header);
            $totalBiayaKemasanPerPaket = 0.0;
            foreach ($header['kemasan_items'] as $kItem) {
                $totalBiayaKemasanPerPaket += (float)($kItem['nominal'] ?? 0);
            }
            $totalBiayaKemasanKeseluruhan = $totalBiayaKemasanPerPaket * $qtyReq;

            $header['total_biaya_kemasan_per_paket'] = $totalBiayaKemasanPerPaket;
            $header['total_biaya_kemasan_keseluruhan'] = $totalBiayaKemasanKeseluruhan;

            // Estimasi HPP Lengkap per 1 Paket = HPP Bahan Baku LIFO + Total Biaya Kemasan & Printilan
            $header['estimasi_hpp_per_paket'] = $estHppPerPaket + $totalBiayaKemasanPerPaket;
            $header['estimasi_total_modal'] = $estTotalModal + $totalBiayaKemasanKeseluruhan;
        }
        return $header;
    }

    /**
     * Membuat Request Paket Bundling baru oleh Purchasing
     * Otomatis menghitung: Total Kebutuhan = Qty Request x Qty Komponen serta Estimasi Modal HPP
     */
    public function create_request($data, $details, $user)
    {
        $this->ensure_bundling_schema();
        $this->db->trans_begin();

        $noRequest = !empty($data['no_request']) ? trim($data['no_request']) : '';
        // Jika no_request kosong atau sudah pernah ada di database, buat nomor baru yang dijamin unik
        if ($noRequest === '' || $this->db->where('no_request', $noRequest)->count_all_results('tberp_bundling_request') > 0) {
            $noRequest = $this->generate_request_number();
        }

        $kodePaket = !empty($data['kode_paket']) ? trim($data['kode_paket']) : '';
        if ($kodePaket === '') {
            $kodePaket = 'PKT-' . date('ymd') . '-' . substr(str_shuffle('0123456789ABCDEF'), 0, 4);
        }

        $qtyRequest = (float)$data['qty_request'];

        if ($qtyRequest <= 0) {
            return ['status' => false, 'msg' => 'Jumlah request paket harus lebih dari 0'];
        }
        if (empty($details)) {
            return ['status' => false, 'msg' => 'Komposisi isi paket belum ditentukan'];
        }

        $idGudangAsal = !empty($data['id_gudang_asal']) ? (int)$data['id_gudang_asal'] : 2; // Gudang Induk default
        $idGudangTujuan = !empty($data['id_gudang_tujuan']) ? (int)$data['id_gudang_tujuan'] : 12; // Gudang Bundling default

        $isInnerboxReq = !empty($data['is_innerbox']) ? 1 : 0;
        $jmlInnerboxReq = $isInnerboxReq ? (float)($data['jumlah_innerbox'] ?? 1) : 0.000;
        $totalInnerboxReq = $isInnerboxReq ? ($qtyRequest * $jmlInnerboxReq) : 0.000;
        $satInnerboxReq = $isInnerboxReq ? (!empty($data['satuan_innerbox']) ? trim($data['satuan_innerbox']) : 'Innerbox') : 'Innerbox';

        $biayaInnerbox = (float)str_replace(',', '', $data['biaya_innerbox'] ?? 0);
        $biayaOuterbox = (float)str_replace(',', '', $data['biaya_outerbox'] ?? 0);
        $biayaKemasanLain = (float)str_replace(',', '', $data['biaya_kemasan_lain'] ?? 0);
        $ketBiayaKemasan = !empty($data['keterangan_biaya_kemasan']) ? trim($data['keterangan_biaya_kemasan']) : null;

        // Proses rincian item biaya kemasan dinamis
        $rawKemasan = $data['kemasan_items'] ?? $data['rincian_biaya_kemasan'] ?? [];
        if (is_string($rawKemasan)) {
            $rawKemasan = json_decode($rawKemasan, true) ?: [];
        }

        $validKemasan = [];
        $totalBiayaKemasanPerPaket = 0.0;
        $summaryKemasan = [];

        if (is_array($rawKemasan)) {
            foreach ($rawKemasan as $k) {
                $nm = trim($k['nama'] ?? '');
                $nom = (float)str_replace(',', '', $k['nominal'] ?? 0);
                if ($nm !== '' || $nom > 0) {
                    if ($nm === '') $nm = 'Biaya Kemasan';
                    $validKemasan[] = ['nama' => $nm, 'nominal' => $nom];
                    $totalBiayaKemasanPerPaket += $nom;
                    $summaryKemasan[] = $nm . ' (Rp ' . number_format($nom, 0, ',', '.') . ')';
                }
            }
        }

        // Fallback jika tidak ada kemasan_items tetapi ada input kolom kemasan lama
        if (empty($validKemasan) && ($biayaInnerbox > 0 || $biayaOuterbox > 0 || $biayaKemasanLain > 0)) {
            $biayaInnerboxPerPaket = $isInnerboxReq ? ($jmlInnerboxReq * $biayaInnerbox) : 0.0;
            if ($biayaInnerboxPerPaket > 0) $validKemasan[] = ['nama' => 'Kardus Innerbox', 'nominal' => $biayaInnerboxPerPaket];
            if ($biayaOuterbox > 0) $validKemasan[] = ['nama' => 'Kardus Outer (Master Box)', 'nominal' => $biayaOuterbox];
            if ($biayaKemasanLain > 0) $validKemasan[] = ['nama' => !empty($ketBiayaKemasan) ? $ketBiayaKemasan : 'Printilan / Stiker Hologram', 'nominal' => $biayaKemasanLain];
            $totalBiayaKemasanPerPaket = $biayaInnerboxPerPaket + $biayaOuterbox + $biayaKemasanLain;
        }

        $rincianKemasanJson = !empty($validKemasan) ? json_encode($validKemasan, JSON_UNESCAPED_UNICODE) : null;
        if (!empty($summaryKemasan)) {
            $ketBiayaKemasan = implode(', ', $summaryKemasan);
        }

        $totalBiayaKemasanKeseluruhan = $totalBiayaKemasanPerPaket * $qtyRequest;

        $header = [
            'no_request'                      => $noRequest,
            'tanggal_request'                 => !empty($data['tanggal_request']) ? $data['tanggal_request'] : date('Y-m-d'),
            'kode_paket'                      => $kodePaket,
            'nama_paket'                      => trim($data['nama_paket']),
            'id_gudang_tujuan'                => $idGudangTujuan,
            'id_gudang_asal'                  => $idGudangAsal,
            'qty_request'                     => $qtyRequest,
            'qty_realisasi'                   => 0,
            'satuan'                          => !empty($data['satuan']) ? $data['satuan'] : 'Box',
            'is_innerbox'                     => $isInnerboxReq,
            'jumlah_innerbox'                 => $jmlInnerboxReq,
            'total_innerbox'                  => $totalInnerboxReq,
            'satuan_innerbox'                 => $satInnerboxReq,
            'biaya_innerbox'                  => $biayaInnerbox,
            'biaya_outerbox'                  => $biayaOuterbox,
            'biaya_kemasan_lain'              => $biayaKemasanLain,
            'keterangan_biaya_kemasan'        => $ketBiayaKemasan,
            'rincian_biaya_kemasan'           => $rincianKemasanJson,
            'total_biaya_kemasan_per_paket'   => $totalBiayaKemasanPerPaket,
            'total_biaya_kemasan_keseluruhan' => $totalBiayaKemasanKeseluruhan,
            'estimasi_hpp_bahan_per_paket'    => 0.00,
            'estimasi_hpp_per_paket'          => 0.00,
            'estimasi_total_modal'            => 0.00,
            'status'                          => 'MENUNGGU_PROSES',
            'user_request'                    => $user,
            'keterangan'                      => $data['keterangan'] ?? null,
            'created_at'                      => date('Y-m-d H:i:s')
        ];
        $this->db->insert('tberp_bundling_request', $header);
        $requestId = $this->db->insert_id();

        $detailRows = [];
        $totalEstHppPaket = 0.0;
        foreach ($details as $d) {
            $kdBrg = trim($d['kode_barang_komponen'] ?? '');
            if ($kdBrg === '') continue;

            if ($isInnerboxReq) {
                $qtyInnerbox = $jmlInnerboxReq;
                $isiPerInnerbox = (float)($d['isi_per_innerbox'] ?? 0);
                if ($isiPerInnerbox <= 0 && !empty($d['qty_per_paket'])) {
                    $isiPerInnerbox = $qtyInnerbox > 0 ? ((float)$d['qty_per_paket'] / $qtyInnerbox) : (float)$d['qty_per_paket'];
                }
                $qtyPerPaket = $qtyInnerbox * $isiPerInnerbox;
                $totalInnerboxKebutuhan = $qtyRequest * $qtyInnerbox;
                $satuanInnerbox = $satInnerboxReq;
            } else {
                $qtyInnerbox = 0.000;
                $isiPerInnerbox = 0.000;
                $qtyPerPaket = (float)($d['qty_per_paket'] ?? 1);
                $totalInnerboxKebutuhan = 0.000;
                $satuanInnerbox = null;
            }

            $totalKebutuhan = $qtyRequest * $qtyPerPaket; // Kalkulasi otomatis total fisik

            // Hitung HPP dan estimasi modal menggunakan metode LIFO (Last In First Out)
            $lifo = $this->calculate_item_lifo_cost($kdBrg, $totalKebutuhan, $idGudangAsal, $d['nama_barang_komponen'] ?? '');
            $hppSatuan = $lifo['hpp_satuan'];
            $modalTotalBarang = $lifo['total_modal'];
            $subtotalHpp = ($qtyRequest > 0) ? ($modalTotalBarang / $qtyRequest) : ($qtyPerPaket * $hppSatuan);

            $totalEstHppPaket += $subtotalHpp;

            $detailRows[] = [
                'id_request'               => $requestId,
                'kode_barang_komponen'     => $kdBrg,
                'nama_barang_komponen'     => $d['nama_barang_komponen'] ?? '',
                'qty_per_paket'            => $qtyPerPaket,
                'qty_total_kebutuhan'      => $totalKebutuhan,
                'qty_terpenuhi'            => 0,
                'satuan'                   => $d['satuan'] ?? 'Pcs',
                'is_innerbox'              => $isInnerboxReq,
                'qty_innerbox'             => $qtyInnerbox,
                'isi_per_innerbox'         => $isiPerInnerbox,
                'satuan_innerbox'          => $satuanInnerbox,
                'total_innerbox_kebutuhan' => $totalInnerboxKebutuhan,
                'hpp_satuan'               => $hppSatuan,
                'subtotal_hpp'             => $subtotalHpp
            ];
        }

        if (empty($detailRows)) {
            $this->db->trans_rollback();
            return ['status' => false, 'msg' => 'Detail komponen tidak valid'];
        }

        $this->db->insert_batch('tberp_bundling_request_detail', $detailRows);

        // Perbarui estimasi HPP lengkap per paket (Bahan Baku LIFO + Kemasan & Printilan)
        $estimasiHppPerPaketLengkap = $totalEstHppPaket + $totalBiayaKemasanPerPaket;
        $estimasiTotalModalLengkap = $estimasiHppPerPaketLengkap * $qtyRequest;

        $this->db->where('id_request', $requestId)->update('tberp_bundling_request', [
            'estimasi_hpp_bahan_per_paket' => $totalEstHppPaket,
            'estimasi_hpp_per_paket'       => $estimasiHppPerPaketLengkap,
            'estimasi_total_modal'         => $estimasiTotalModalLengkap
        ]);

        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            return ['status' => false, 'msg' => 'Gagal menyimpan request bundling'];
        }

        $this->db->trans_commit();
        return [
            'status'     => true,
            'id_request' => $requestId,
            'no_request' => $noRequest,
            'msg'        => 'Request Paket Bundling ' . $noRequest . ' berhasil dibuat'
        ];
    }

    /**
     * Memperbarui biaya kemasan (innerbox, outerbox, hologram/printilan) pada request bundling
     */
    public function update_request_packaging_cost($id_request, $data)
    {
        $this->ensure_bundling_schema();
        $req = $this->db->where('id_request', $id_request)->get('tberp_bundling_request')->row_array();
        if (!$req) {
            return ['status' => false, 'msg' => 'Data request bundling tidak ditemukan'];
        }

        $qtyReq = (float)$req['qty_request'];
        $isInnerbox = !empty($req['is_innerbox']) ? 1 : 0;
        $jmlInnerbox = (float)($req['jumlah_innerbox'] ?? 0);

        // Proses rincian item biaya kemasan dinamis
        $rawKemasan = $data['kemasan_items'] ?? $data['items'] ?? $data['rincian_biaya_kemasan'] ?? [];
        if (is_string($rawKemasan)) {
            $rawKemasan = json_decode($rawKemasan, true) ?: [];
        }

        $validKemasan = [];
        $totalBiayaKemasanPerPaket = 0.0;
        $summaryKemasan = [];

        if (is_array($rawKemasan)) {
            foreach ($rawKemasan as $k) {
                $nm = trim($k['nama'] ?? '');
                $nom = (float)str_replace(',', '', $k['nominal'] ?? 0);
                if ($nm !== '' || $nom > 0) {
                    if ($nm === '') $nm = 'Biaya Kemasan';
                    $validKemasan[] = ['nama' => $nm, 'nominal' => $nom];
                    $totalBiayaKemasanPerPaket += $nom;
                    $summaryKemasan[] = $nm . ' (Rp ' . number_format($nom, 0, ',', '.') . ')';
                }
            }
        }

        // Fallback jika tidak ada kemasan_items tetapi ada input kolom kemasan lama
        $biayaInnerbox = (float)str_replace(',', '', $data['biaya_innerbox'] ?? 0);
        $biayaOuterbox = (float)str_replace(',', '', $data['biaya_outerbox'] ?? 0);
        $biayaKemasanLain = (float)str_replace(',', '', $data['biaya_kemasan_lain'] ?? 0);
        $ketBiayaKemasan = isset($data['keterangan_biaya_kemasan']) ? trim($data['keterangan_biaya_kemasan']) : '';

        if (empty($validKemasan) && ($biayaInnerbox > 0 || $biayaOuterbox > 0 || $biayaKemasanLain > 0)) {
            $biayaInnerboxPerPaket = $isInnerbox ? ($jmlInnerbox * $biayaInnerbox) : 0.0;
            if ($biayaInnerboxPerPaket > 0) $validKemasan[] = ['nama' => 'Kardus Innerbox', 'nominal' => $biayaInnerboxPerPaket];
            if ($biayaOuterbox > 0) $validKemasan[] = ['nama' => 'Kardus Outer (Master Box)', 'nominal' => $biayaOuterbox];
            if ($biayaKemasanLain > 0) $validKemasan[] = ['nama' => !empty($ketBiayaKemasan) ? $ketBiayaKemasan : 'Printilan / Stiker Hologram', 'nominal' => $biayaKemasanLain];
            $totalBiayaKemasanPerPaket = $biayaInnerboxPerPaket + $biayaOuterbox + $biayaKemasanLain;
        }

        $rincianKemasanJson = !empty($validKemasan) ? json_encode($validKemasan, JSON_UNESCAPED_UNICODE) : null;
        if (!empty($summaryKemasan)) {
            $ketBiayaKemasan = implode(', ', $summaryKemasan);
        }

        $totalBiayaKemasanKeseluruhan = $totalBiayaKemasanPerPaket * $qtyReq;

        // Ambil kalkulasi HPP bahan baku terkini (LIFO)
        $reqFull = $this->get_request_by_id($id_request);
        $estHppBahan = (float)($reqFull['estimasi_hpp_bahan_per_paket'] ?? 0);

        $totalHppPerPaketLengkap = $estHppBahan + $totalBiayaKemasanPerPaket;
        $totalModalKeseluruhan = $totalHppPerPaketLengkap * $qtyReq;

        $updateData = [
            'biaya_innerbox'                  => $biayaInnerbox,
            'biaya_outerbox'                  => $biayaOuterbox,
            'biaya_kemasan_lain'              => $biayaKemasanLain,
            'keterangan_biaya_kemasan'        => $ketBiayaKemasan,
            'rincian_biaya_kemasan'           => $rincianKemasanJson,
            'total_biaya_kemasan_per_paket'   => $totalBiayaKemasanPerPaket,
            'total_biaya_kemasan_keseluruhan' => $totalBiayaKemasanKeseluruhan,
            'estimasi_hpp_bahan_per_paket'    => $estHppBahan,
            'estimasi_hpp_per_paket'          => $totalHppPerPaketLengkap,
            'estimasi_total_modal'            => $totalModalKeseluruhan
        ];

        $this->db->where('id_request', $id_request)->update('tberp_bundling_request', $updateData);

        return [
            'status' => true,
            'msg'    => 'Biaya kemasan dan modal printilan berhasil diperbarui',
            'data'   => array_merge($updateData, [
                'kemasan_items'                     => !empty($validKemasan) ? $validKemasan : [['nama' => '', 'nominal' => 0]],
                'estimasi_hpp_bahan_per_paket_fmt'  => number_format($estHppBahan, 2, ',', '.'),
                'total_biaya_kemasan_per_paket_fmt' => number_format($totalBiayaKemasanPerPaket, 2, ',', '.'),
                'estimasi_hpp_per_paket_fmt'        => number_format($totalHppPerPaketLengkap, 2, ',', '.'),
                'estimasi_total_modal_fmt'          => number_format($totalModalKeseluruhan, 2, ',', '.')
            ])
        ];
    }

    public function cancel_request($id_request, $user)
    {
        $this->ensure_bundling_schema();
        $req = $this->get_request_by_id($id_request);
        if (!$req) {
            return ['status' => false, 'msg' => 'Data request tidak ditemukan'];
        }
        if ($req['qty_realisasi'] > 0) {
            return ['status' => false, 'msg' => 'Request sudah diproses sebagian atau selesai, tidak dapat dibatalkan'];
        }

        $this->db->where('id_request', $id_request)->update('tberp_bundling_request', [
            'status'     => 'BATAL',
            'keterangan' => trim($req['keterangan'] . ' [Dibatalkan oleh ' . $user . ' pada ' . date('Y-m-d H:i') . ']')
        ]);
        return ['status' => true, 'msg' => 'Request ' . $req['no_request'] . ' berhasil dibatalkan'];
    }

    // =========================================================================
    // 3. LOGISTIK: CEK KETERSEDIAAN STOK & MUTASI BAHAN GUDANG INDUK -> BUNDLING
    // =========================================================================

    /**
     * Memeriksa stok tersedia komponen di Gudang Induk vs Gudang Bundling
     */
    public function get_component_stock_status($id_request)
    {
        $this->ensure_bundling_schema();
        $req = $this->get_request_by_id($id_request);
        if (!$req) return [];

        $gudangAsalId = (int)$req['id_gudang_asal'];     // misal Gudang Induk (2)
        $gudangTujuanId = (int)$req['id_gudang_tujuan']; // misal Gudang Bundling (12)

        $result = [];
        foreach ($req['details'] as $item) {
            $kd = $item['kode_barang_komponen'];

            // Stok di Gudang Induk (Tersedia untuk dimutasi)
            $stokInduk = $this->get_stock_available_by_gudang($kd, $gudangAsalId);

            // Stok di Gudang Bundling (Siap dirakit)
            $stokBundling = $this->get_stock_available_by_gudang($kd, $gudangTujuanId);

            $sisaKebutuhan = max(0, (float)$item['qty_total_kebutuhan'] - (float)$item['qty_terpenuhi']);
            $kekuranganDiBundling = max(0, $sisaKebutuhan - $stokBundling);

            // Hitung modal berdasarkan metode LIFO (Last In First Out)
            $lifo = $this->calculate_item_lifo_cost($kd, (float)$item['qty_total_kebutuhan'], $gudangAsalId, $item['nama_barang_komponen']);
            $hppSatuan = $lifo['hpp_satuan'];
            $totalModalKebutuhan = $lifo['total_modal'];
            $qtyReq = (float)$req['qty_request'];
            $subtotalHppPerPaket = ($qtyReq > 0) ? ($totalModalKebutuhan / $qtyReq) : ((float)$item['qty_per_paket'] * $hppSatuan);

            $result[] = [
                'kode_barang'              => $kd,
                'nama_barang'              => $item['nama_barang_komponen'],
                'qty_per_paket'            => (float)$item['qty_per_paket'],
                'qty_total_kebutuhan'      => (float)$item['qty_total_kebutuhan'],
                'qty_terpenuhi'            => (float)$item['qty_terpenuhi'],
                'sisa_kebutuhan'           => $sisaKebutuhan,
                'stok_gudang_induk'        => $stokInduk,
                'stok_gudang_bundling'     => $stokBundling,
                'kekurangan_di_bundling'   => $kekuranganDiBundling,
                'satuan'                   => $item['satuan'],
                'is_innerbox'              => (int)($item['is_innerbox'] ?? 0),
                'qty_innerbox'             => (float)($item['qty_innerbox'] ?? 0),
                'isi_per_innerbox'         => (float)($item['isi_per_innerbox'] ?? 0),
                'satuan_innerbox'          => $item['satuan_innerbox'] ?? 'Innerbox',
                'total_innerbox_kebutuhan' => (float)($item['total_innerbox_kebutuhan'] ?? 0),
                'hpp_satuan'               => $hppSatuan,
                'subtotal_hpp_per_paket'   => $subtotalHppPerPaket,
                'total_modal_kebutuhan'    => $totalModalKebutuhan
            ];
        }
        return $result;
    }

    /**
     * Eksekusi Mutasi Cepat dari Gudang Induk ke Gudang Bundling untuk bahan paket
     */
    public function execute_mutasi_bahan_bundling($id_request, $items, $user)
    {
        $this->ensure_bundling_schema();
        $req = $this->get_request_by_id($id_request);
        if (!$req) {
            return ['status' => false, 'msg' => 'Request tidak ditemukan'];
        }

        $gudangAsal = (int)$req['id_gudang_asal'];
        $gudangTujuan = (int)$req['id_gudang_tujuan'];

        if ($gudangAsal === $gudangTujuan) {
            return ['status' => false, 'msg' => 'Gudang asal dan tujuan tidak boleh sama'];
        }

        // Generate nomor mutasi
        $this->load->model('M_Ics');
        $noRefMutasi = $this->M_Ics->generate_noreff();

        $this->db->trans_begin();

        $mutasiHeader = [
            'noreff'        => $noRefMutasi,
            'tgl_transaksi' => date('Y-m-d'),
            'gudang_asal'   => $gudangAsal,
            'gudang_mutasi' => $gudangTujuan,
            'keterangan'    => 'Mutasi Bahan Paket Bundling ' . $req['nama_paket'] . ' (Ref: ' . $req['no_request'] . ')',
            'inputer'       => $user,
            'status'        => 'POSTED',
            'input_at'      => date('Y-m-d H:i:s'),
            'last_action'   => 'CREATE'
        ];
        $this->db->insert('tb_mutasi', $mutasiHeader);

        $now = date('Y-m-d H:i:s');

        foreach ($items as $item) {
            $kd = $item['kode_barang'];
            $qtyMutasi = (float)($item['qty_mutasi'] ?? 0);
            if ($qtyMutasi <= 0) continue;

            $noLot = !empty($item['no_lot']) ? trim($item['no_lot']) : '-';
            $expDate = !empty($item['expired_date']) && $item['expired_date'] !== '0000-00-00' ? $item['expired_date'] : null;

            // Validasi stok di gudang asal
            $batchAsal = $this->get_stock_batch_row($kd, $gudangAsal, $noLot, $expDate);
            $available = $batchAsal ? ((float)$batchAsal['qty_on_hand'] - (float)$batchAsal['qty_reserved']) : 0;

            if ($available + 0.0001 < $qtyMutasi) {
                $this->db->trans_rollback();
                return ['status' => false, 'msg' => 'Stok ' . $kd . ' (Lot: ' . $noLot . ') di gudang asal tidak mencukupi. Tersedia: ' . $available];
            }

            // 1. Catat detail mutasi
            $this->db->insert('tb_detail_mutasi', [
                'noreff'            => $noRefMutasi,
                'tgl_transaksi'     => date('Y-m-d'),
                'gdg_asal'          => $gudangAsal,
                'gdg_mutasi'        => $gudangTujuan,
                'kode_barang'       => $kd,
                'kode_barang_zahir' => $kd,
                'nama_barang'       => $item['nama_barang'] ?? $kd,
                'no_lot'            => $noLot,
                'exp_date'          => $expDate ?: '',
                'qty'               => $qtyMutasi,
                'satuan'            => 1,
                'input_by'          => $user,
                'create_at'         => $now,
                'last_action'       => 'CREATE'
            ]);

            // 2. Potong stok Gudang Asal
            $this->apply_batch_delta($kd, $gudangAsal, $noLot, $expDate, -$qtyMutasi);
            $this->db->insert('tberp_stock_ledger', [
                'kd_barang'    => $kd,
                'gudang_id'    => $gudangAsal,
                'no_lot'       => $noLot,
                'expired_date' => $expDate,
                'qty'          => $qtyMutasi,
                'tipe'         => 'OUT',
                'ref_no'       => $noRefMutasi,
                'ref_type'     => 'MUTASI_BUNDLING',
                'created_at'   => $now
            ]);

            // 3. Tambah stok Gudang Tujuan
            $this->apply_batch_delta($kd, $gudangTujuan, $noLot, $expDate, $qtyMutasi);
            $this->db->insert('tberp_stock_ledger', [
                'kd_barang'    => $kd,
                'gudang_id'    => $gudangTujuan,
                'no_lot'       => $noLot,
                'expired_date' => $expDate,
                'qty'          => $qtyMutasi,
                'tipe'         => 'IN',
                'ref_no'       => $noRefMutasi,
                'ref_type'     => 'MUTASI_BUNDLING',
                'created_at'   => $now
            ]);
        }

        $this->db->insert('tb_log_mutasi', [
            'noreff'     => $noRefMutasi,
            'aksi'       => 'POSTED',
            'keterangan' => 'MUTASI BAHAN BUNDLING DARI GUDANG ' . $gudangAsal . ' KE ' . $gudangTujuan,
            'user'       => $user,
            'created_at' => $now
        ]);

        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            return ['status' => false, 'msg' => 'Gagal merekam mutasi bahan bundling'];
        }

        $this->db->trans_commit();
        return [
            'status' => true,
            'noreff' => $noRefMutasi,
            'msg'    => 'Mutasi bahan berhasil direkam dengan nomor ' . $noRefMutasi
        ];
    }

    // =========================================================================
    // 4. REALISASI PEMBUATAN PAKET / ASSEMBLY (LOGISTIK)
    // =========================================================================

    public function generate_assembly_number()
    {
        $this->ensure_bundling_schema();
        $dateStr = date('Ymd');
        $prefix = 'ASM-' . $dateStr . '-';

        $last = $this->db->select('no_assembly')
            ->like('no_assembly', $prefix, 'after')
            ->order_by('id_assembly', 'DESC')
            ->limit(1)
            ->get('tberp_bundling_assembly')
            ->row();

        $num = 1;
        if ($last) {
            $parts = explode('-', $last->no_assembly);
            if (isset($parts[2]) && is_numeric($parts[2])) {
                $num = (int)$parts[2] + 1;
            }
        }
        return $prefix . sprintf('%04d', $num);
    }

    /**
     * Proses Pembuatan Paket Bundling (Assembly):
     * - Mengurangi komponen di Gudang Bundling (OUT)
     * - Menambah produk paket di Gudang Bundling (IN)
     * - Menghitung HPP Paket dari akumulasi HPP komponen
     * - Mendukung Partial Fulfillment (memperbarui status request & sisa kuantitas)
     */
    public function process_assembly($data, $componentLots, $user)
    {
        $this->ensure_bundling_schema();
        $this->load->model('M_PenyesuaianBarang');

        $qtyAssembly = (float)$data['qty_assembly'];
        if ($qtyAssembly <= 0) {
            return ['status' => false, 'msg' => 'Qty paket yang dibuat harus lebih dari 0'];
        }

        $idRequest = !empty($data['id_request']) ? (int)$data['id_request'] : null;
        $req = $idRequest ? $this->get_request_by_id($idRequest) : null;

        $kodePaket = trim($data['kode_paket']);
        $namaPaket = trim($data['nama_paket']);
        $gudangAsalId = !empty($data['id_gudang_asal']) ? (int)$data['id_gudang_asal'] : ($req ? (int)$req['id_gudang_asal'] : 2); // Gudang Induk (Sumber Bahan)
        $gudangId = !empty($data['id_gudang']) ? (int)$data['id_gudang'] : ($req ? (int)$req['id_gudang_tujuan'] : 12); // Gudang Penerima Paket
        $noAssembly = $this->generate_assembly_number();
        $tanggal = !empty($data['tanggal']) ? $data['tanggal'] : date('Y-m-d');
        $noLotPaket = !empty($data['no_lot_paket']) ? trim($data['no_lot_paket']) : 'LOT-' . date('ymd') . '-' . substr(uniqid(), -4);
        $expDatePaket = !empty($data['expired_date_paket']) && $data['expired_date_paket'] !== '0000-00-00' ? $data['expired_date_paket'] : null;

        // Jika ada request, validasi agar realisasi tidak melebihi sisa request
        if ($req) {
            $sisaRequest = (float)$req['qty_request'] - (float)$req['qty_realisasi'];
            if ($qtyAssembly > ($sisaRequest + 0.0001)) {
                return ['status' => false, 'msg' => 'Qty realisasi (' . $qtyAssembly . ') melebihi sisa request (' . $sisaRequest . ')'];
            }
        }

        // Cek apakah produk paket sudah ada di master barang tbpo_barang
        $this->ensure_product_in_master_barang($kodePaket, $namaPaket, $data['satuan'] ?? 'Box');

        $this->db->trans_begin();
        $now = date('Y-m-d H:i:s');

        $totalHppAssembly = 0.0;
        $assemblyDetailRecords = [];
        $earliestExpDate = null;

        // 1. Proses Pengurangan Stok Komponen di Gudang Asal (Gudang Induk)
        foreach ($componentLots as $comp) {
            $kdBrg = trim($comp['kode_barang']);
            $qtyPakai = (float)$comp['qty_digunakan'];
            if ($qtyPakai <= 0) continue;

            $noLotComp = !empty($comp['no_lot']) ? trim($comp['no_lot']) : '-';
            $expDateComp = !empty($comp['expired_date']) && $comp['expired_date'] !== '0000-00-00' ? $comp['expired_date'] : null;

            if ($expDateComp) {
                if ($earliestExpDate === null || strtotime($expDateComp) < strtotime($earliestExpDate)) {
                    $earliestExpDate = $expDateComp;
                }
            }

            // Validasi stok fisik komponen di Gudang Asal (Gudang Induk)
            $batchComp = $this->get_stock_batch_row($kdBrg, $gudangAsalId, $noLotComp, $expDateComp);
            $availableComp = $batchComp ? ((float)$batchComp['qty_on_hand'] - (float)$batchComp['qty_reserved']) : 0;

            if ($availableComp + 0.0001 < $qtyPakai) {
                $this->db->trans_rollback();
                return [
                    'status' => false,
                    'msg'    => 'Stok komponen ' . ($comp['nama_barang'] ?? $kdBrg) . ' (Lot: ' . $noLotComp . ') di Gudang Induk tidak mencukupi. Tersedia: ' . $availableComp . ', Dibutuhkan: ' . $qtyPakai
                ];
            }

            // Ambil Average HPP Komponen
            $hppSatuanComp = (float)$this->get_item_average_hpp($kdBrg, $gudangAsalId, $comp['nama_barang'] ?? '');
            if ($hppSatuanComp <= 0 && $req && !empty($req['details'])) {
                foreach ($req['details'] as $rDet) {
                    if ($rDet['kode_barang_komponen'] === $kdBrg && !empty($rDet['hpp_satuan']) && (float)$rDet['hpp_satuan'] > 0) {
                        $hppSatuanComp = (float)$rDet['hpp_satuan'];
                        break;
                    }
                }
            }
            $subtotalHpp = $qtyPakai * $hppSatuanComp;
            $totalHppAssembly += $subtotalHpp;

            // Potong batch komponen di Gudang Asal (Gudang Induk)
            $this->apply_batch_delta($kdBrg, $gudangAsalId, $noLotComp, $expDateComp, -$qtyPakai);

            // Catat Kartu Stok Ledger OUT Komponen
            $this->db->insert('tberp_stock_ledger', [
                'kd_barang'    => $kdBrg,
                'gudang_id'    => $gudangAsalId,
                'no_lot'       => $noLotComp,
                'expired_date' => $expDateComp,
                'qty'          => $qtyPakai,
                'tipe'         => 'OUT',
                'ref_no'       => $noAssembly,
                'ref_type'     => 'ASSEMBLY_KOMPONEN',
                'created_at'   => $now
            ]);

            $isInnerbox = 0;
            $qtyInnerbox = 0.000;
            $isiPerInnerbox = 0.000;
            if ($req && !empty($req['details'])) {
                foreach ($req['details'] as $rDet) {
                    if ($rDet['kode_barang_komponen'] === $kdBrg) {
                        $isInnerbox = (int)($rDet['is_innerbox'] ?? 0);
                        $qtyInnerbox = (float)($rDet['qty_innerbox'] ?? 0);
                        $isiPerInnerbox = (float)($rDet['isi_per_innerbox'] ?? 0);
                        break;
                    }
                }
            }

            $assemblyDetailRecords[] = [
                'kode_barang_komponen' => $kdBrg,
                'nama_barang_komponen' => $comp['nama_barang'] ?? '',
                'no_lot'               => $noLotComp,
                'expired_date'         => $expDateComp,
                'qty_digunakan'        => $qtyPakai,
                'satuan'               => $comp['satuan'] ?? 'Pcs',
                'is_innerbox'          => $isInnerbox,
                'qty_innerbox'         => $qtyInnerbox,
                'isi_per_innerbox'     => $isiPerInnerbox,
                'hpp_satuan'           => $hppSatuanComp,
                'total_hpp'            => $subtotalHpp
            ];
        }

        if (empty($assemblyDetailRecords)) {
            $this->db->trans_rollback();
            return ['status' => false, 'msg' => 'Tidak ada komponen yang diproses'];
        }

        // Expired date paket: gunakan input atau ikuti expired date komponen terdekat
        if (!$expDatePaket && $earliestExpDate) {
            $expDatePaket = $earliestExpDate;
        }

        $hppPerPaket = $qtyAssembly > 0 ? ($totalHppAssembly / $qtyAssembly) : 0;

        // 2. Catat Header Assembly
        $assemblyHeader = [
            'no_assembly'        => $noAssembly,
            'id_request'         => $idRequest,
            'id_gudang_asal'     => $gudangAsalId,
            'no_request'         => $req ? $req['no_request'] : null,
            'tanggal'            => $tanggal,
            'kode_paket'         => $kodePaket,
            'nama_paket'         => $namaPaket,
            'id_gudang'          => $gudangId,
            'qty_assembly'       => $qtyAssembly,
            'satuan'             => $data['satuan'] ?? 'Box',
            'no_lot_paket'       => $noLotPaket,
            'expired_date_paket' => $expDatePaket,
            'total_nilai_hpp'    => $totalHppAssembly,
            'hpp_per_paket'      => $hppPerPaket,
            'user_input'         => $user,
            'keterangan'         => $data['keterangan'] ?? ('Pembuatan ' . $qtyAssembly . ' Box ' . $namaPaket),
            'created_at'         => $now
        ];
        $this->db->insert('tberp_bundling_assembly', $assemblyHeader);
        $assemblyId = $this->db->insert_id();

        // 3. Catat Detail Assembly
        foreach ($assemblyDetailRecords as $idxRec => $dRec) {
            $assemblyDetailRecords[$idxRec]['id_assembly'] = $assemblyId;
        }
        $this->db->insert_batch('tberp_bundling_assembly_detail', $assemblyDetailRecords);

        // 4. Tambah Stok Produk Paket di Gudang Penerima Paket (IN)
        $this->apply_batch_delta($kodePaket, $gudangId, $noLotPaket, $expDatePaket, $qtyAssembly);

        // Catat Kartu Stok Ledger IN Paket
        $this->db->insert('tberp_stock_ledger', [
            'kd_barang'    => $kodePaket,
            'gudang_id'    => $gudangId,
            'no_lot'       => $noLotPaket,
            'expired_date' => $expDatePaket,
            'qty'          => $qtyAssembly,
            'tipe'         => 'IN',
            'ref_no'       => $noAssembly,
            'ref_type'     => 'ASSEMBLY_PAKET',
            'created_at'   => $now
        ]);

        // 5. Update Request Purchasing jika ada (Partial / Full Fulfillment)
        if ($req) {
            $newRealisasi = (float)$req['qty_realisasi'] + $qtyAssembly;
            $newStatus = ($newRealisasi >= (float)$req['qty_request'] - 0.0001) ? 'SELESAI' : 'PROSES_SEBAGIAN';

            $this->db->where('id_request', $idRequest)->update('tberp_bundling_request', [
                'qty_realisasi' => $newRealisasi,
                'status'        => $newStatus,
                'updated_at'    => $now
            ]);

            // Update qty_terpenuhi pada detail request
            foreach ($assemblyDetailRecords as $dRec) {
                $this->db->set('qty_terpenuhi', 'qty_terpenuhi + ' . (float)$dRec['qty_digunakan'], false)
                    ->where('id_request', $idRequest)
                    ->where('kode_barang_komponen', $dRec['kode_barang_komponen'])
                    ->update('tberp_bundling_request_detail');
            }
        }

        // 6. Otomatis Terbitkan Dokumen Draft Penyesuaian Barang untuk Accounting (Opsi A)
        $idPenyesuaian = null;
        $noPenyesuaian = null;
        try {
            $this->load->model('M_PenyesuaianBarang');
            $noPenyesuaian = $this->M_PenyesuaianBarang->generate_ref_no();

            $draftHeader = [
                'no_referensi'   => $noPenyesuaian,
                'tanggal'        => $tanggal,
                'id_gudang_dari' => $gudangAsalId,
                'id_gudang_ke'   => $gudangId,
                'keterangan'     => 'Perakitan Paket Bundling ' . $namaPaket . ' (' . $qtyAssembly . ' Box) Ref #' . $noAssembly,
                'total_nilai'    => $totalHppAssembly,
                'status'         => 'DRAFT',
                'created_by'     => (int)($this->session->userdata('id_user') ?: $this->session->userdata('nik') ?: 1),
                'created_at'     => $now
            ];

            $draftDetails = [];
            // Ambil Akun Penyesuaian Barang default: 14012 (Q Adjusment Persediaan)
            $adjAccount = $this->M_PenyesuaianBarang->get_default_adjustment_account();
            $idAkunPenyesuaian = !empty($adjAccount['id_akun']) ? (int)$adjAccount['id_akun'] : 225;

            // Komponen OUT (jumlah negatif)
            foreach ($assemblyDetailRecords as $dRec) {
                $kdKomponen = $dRec['kode_barang_komponen'];
                $akunKomponen = $this->M_PenyesuaianBarang->get_item_inventory_account($kdKomponen);
                $idAkunBaris = $idAkunPenyesuaian;
                // Jika komponen memiliki akun khusus persediaan (misal 14031 Barang Promosi), gunakan akun tersebut
                if (!empty($akunKomponen['kode_akun']) && $akunKomponen['kode_akun'] === '14031') {
                    $idAkunBaris = (int)$akunKomponen['id_akun'];
                }

                $draftDetails[] = [
                    'kd_barang'    => $kdKomponen,
                    'nm_barang'    => $dRec['nama_barang_komponen'],
                    'jumlah'       => -abs((float)$dRec['qty_digunakan']),
                    'satuan'       => $dRec['satuan'] ?? 'Pcs',
                    'id_akun'      => $idAkunBaris,
                    'no_lot'       => $dRec['no_lot'],
                    'expired_date' => $dRec['expired_date'],
                    'lot_data'     => null
                ];
            }

            // Paket Jadi IN (jumlah positif)
            $akunPaket = $this->M_PenyesuaianBarang->get_item_inventory_account($kodePaket);
            $idAkunPaket = !empty($akunPaket['id_akun']) ? (int)$akunPaket['id_akun'] : 102; // Default 14010 (Persediaan # 1)

            $draftDetails[] = [
                'kd_barang'    => $kodePaket,
                'nm_barang'    => $namaPaket,
                'jumlah'       => abs($qtyAssembly),
                'satuan'       => $data['satuan'] ?? 'Box',
                'id_akun'      => $idAkunPaket,
                'no_lot'       => $noLotPaket,
                'expired_date' => $expDatePaket,
                'lot_data'     => null
            ];

            $idPenyesuaian = $this->M_PenyesuaianBarang->save($draftHeader, $draftDetails);

            if ($idPenyesuaian) {
                $this->db->where('id_assembly', $assemblyId)->update('tberp_bundling_assembly', [
                    'id_penyesuaian' => $idPenyesuaian,
                    'no_penyesuaian' => $noPenyesuaian
                ]);
            }
        } catch (\Throwable $e) {
            log_message('error', 'Gagal auto-draft penyesuaian barang: ' . $e->getMessage());
        }

        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            return ['status' => false, 'msg' => 'Gagal merekam proses pembuatan paket bundling'];
        }

        $this->db->trans_commit();
        return [
            'status'         => true,
            'no_assembly'    => $noAssembly,
            'qty_assembly'   => $qtyAssembly,
            'total_hpp'      => $totalHppAssembly,
            'hpp_per_paket'  => $hppPerPaket,
            'no_penyesuaian' => $noPenyesuaian,
            'msg'            => 'Pembuatan ' . $qtyAssembly . ' Box ' . $namaPaket . ' berhasil direkam (' . $noAssembly . '). Draft Penyesuaian Barang ' . ($noPenyesuaian ? '(#'.$noPenyesuaian.')' : '') . ' telah otomatis diterbitkan untuk Accounting.'
        ];
    }

    // =========================================================================
    // 5. PEMBONGKARAN PAKET / DISASSEMBLY (UNBUNDLING UNTUK PENJUALAN ECER)
    // =========================================================================

    public function generate_disassembly_number()
    {
        $this->ensure_bundling_schema();
        $dateStr = date('Ymd');
        $prefix = 'DSB-' . $dateStr . '-';

        $last = $this->db->select('no_disassembly')
            ->like('no_disassembly', $prefix, 'after')
            ->order_by('id_disassembly', 'DESC')
            ->limit(1)
            ->get('tberp_bundling_disassembly')
            ->row();

        $num = 1;
        if ($last) {
            $parts = explode('-', $last->no_disassembly);
            if (isset($parts[2]) && is_numeric($parts[2])) {
                $num = (int)$parts[2] + 1;
            }
        }
        return $prefix . sprintf('%04d', $num);
    }

    /**
     * Proses Pembongkaran Paket Bundling (Disassembly):
     * - Mengurangi stok Paket Bundling di Gudang Bundling (OUT)
     * - Mengembalikan stok Komponen menjadi stok eceran bebas (IN)
     * - Mendistribusikan kembali nilai HPP paket ke komponen
     * - Menghilangkan double counting: fisik paket berkurang, fisik ecer bertambah
     */
    public function process_disassembly($data, $componentReturns, $user)
    {
        $this->ensure_bundling_schema();
        $this->load->model('M_PenyesuaianBarang');

        $qtyDisassembly = (float)$data['qty_disassembly'];
        if ($qtyDisassembly <= 0) {
            return ['status' => false, 'msg' => 'Qty paket yang dibongkar harus lebih dari 0'];
        }

        $kodePaket = trim($data['kode_paket']);
        $namaPaket = trim($data['nama_paket']);
        $gudangId = !empty($data['id_gudang']) ? (int)$data['id_gudang'] : 12; // Gudang Bundling
        $noLotPaket = !empty($data['no_lot_paket']) ? trim($data['no_lot_paket']) : '-';
        $expDatePaket = !empty($data['expired_date_paket']) && $data['expired_date_paket'] !== '0000-00-00' ? $data['expired_date_paket'] : null;
        $noDisassembly = $this->generate_disassembly_number();
        $tanggal = !empty($data['tanggal']) ? $data['tanggal'] : date('Y-m-d');
        $alasan = !empty($data['alasan']) ? trim($data['alasan']) : 'Kebutuhan penjualan eceran pelanggan';

        // 1. Validasi Stok Paket Bundling di Gudang Bundling
        $batchPaket = $this->get_stock_batch_row($kodePaket, $gudangId, $noLotPaket, $expDatePaket);
        $availablePaket = $batchPaket ? ((float)$batchPaket['qty_on_hand'] - (float)$batchPaket['qty_reserved']) : 0;

        if ($availablePaket + 0.0001 < $qtyDisassembly) {
            return [
                'status' => false,
                'msg'    => 'Stok paket ' . $namaPaket . ' tidak mencukupi untuk dibongkar. Tersedia: ' . $availablePaket . ', Diminta: ' . $qtyDisassembly
            ];
        }

        $this->db->trans_begin();
        $now = date('Y-m-d H:i:s');

        // Ambil HPP Paket saat ini
        $hppPaket = (float)$this->get_item_average_hpp($kodePaket, $gudangId);
        $totalNilaiPaket = $qtyDisassembly * $hppPaket;

        // 2. Potong Stok Paket Bundling (OUT)
        $this->apply_batch_delta($kodePaket, $gudangId, $noLotPaket, $expDatePaket, -$qtyDisassembly);
        $this->db->insert('tberp_stock_ledger', [
            'kd_barang'    => $kodePaket,
            'gudang_id'    => $gudangId,
            'no_lot'       => $noLotPaket,
            'expired_date' => $expDatePaket,
            'qty'          => $qtyDisassembly,
            'tipe'         => 'OUT',
            'ref_no'       => $noDisassembly,
            'ref_type'     => 'DISASSEMBLY_PAKET',
            'created_at'   => $now
        ]);

        // 3. Kembalikan Stok Komponen ke Stok Bebas (IN)
        $detailRecords = [];
        $totalHppKomponen = 0.0;

        foreach ($componentReturns as $comp) {
            $kdComp = trim($comp['kode_barang']);
            $qtyKembali = (float)$comp['qty_kembali'];
            if ($qtyKembali <= 0) continue;

            $noLotComp = !empty($comp['no_lot']) ? trim($comp['no_lot']) : '-';
            $expDateComp = !empty($comp['expired_date']) && $comp['expired_date'] !== '0000-00-00' ? $comp['expired_date'] : null;

            // HPP satuan komponen
            $hppSatuanComp = (float)$this->get_item_average_hpp($kdComp, $gudangId);
            $subtotalHppComp = $qtyKembali * $hppSatuanComp;
            $totalHppKomponen += $subtotalHppComp;

            // Tambah batch stok komponen di Gudang Bundling (atau gudang eceran)
            $this->apply_batch_delta($kdComp, $gudangId, $noLotComp, $expDateComp, $qtyKembali);

            // Catat Kartu Stok Ledger IN Komponen
            $this->db->insert('tberp_stock_ledger', [
                'kd_barang'    => $kdComp,
                'gudang_id'    => $gudangId,
                'no_lot'       => $noLotComp,
                'expired_date' => $expDateComp,
                'qty'          => $qtyKembali,
                'tipe'         => 'IN',
                'ref_no'       => $noDisassembly,
                'ref_type'     => 'DISASSEMBLY_KOMPONEN',
                'created_at'   => $now
            ]);

            $detailRecords[] = [
                'kode_barang_komponen' => $kdComp,
                'nama_barang_komponen' => $comp['nama_barang'] ?? '',
                'no_lot'               => $noLotComp,
                'expired_date'         => $expDateComp,
                'qty_kembali'          => $qtyKembali,
                'satuan'               => $comp['satuan'] ?? 'Pcs',
                'hpp_satuan'           => $hppSatuanComp,
                'total_hpp'            => $subtotalHppComp
            ];
        }

        if (empty($detailRecords)) {
            $this->db->trans_rollback();
            return ['status' => false, 'msg' => 'Tidak ada komponen yang dikembalikan'];
        }

        // 4. Catat Header Disassembly
        $dsbHeader = [
            'no_disassembly'     => $noDisassembly,
            'tanggal'            => $tanggal,
            'kode_paket'         => $kodePaket,
            'nama_paket'         => $namaPaket,
            'id_gudang'          => $gudangId,
            'qty_disassembly'    => $qtyDisassembly,
            'satuan'             => $data['satuan'] ?? 'Box',
            'no_lot_paket'       => $noLotPaket,
            'expired_date_paket' => $expDatePaket,
            'total_nilai_hpp'    => $totalNilaiPaket,
            'alasan'             => $alasan,
            'user_input'         => $user,
            'created_at'         => $now
        ];
        $this->db->insert('tberp_bundling_disassembly', $dsbHeader);
        $dsbId = $this->db->insert_id();

        // 5. Catat Detail Disassembly
        foreach ($detailRecords as &$dRec) {
            $dRec['id_disassembly'] = $dsbId;
        }
        $this->db->insert_batch('tberp_bundling_disassembly_detail', $detailRecords);

        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            return ['status' => false, 'msg' => 'Gagal merekam pembongkaran paket bundling'];
        }

        $this->db->trans_commit();
        return [
            'status'          => true,
            'no_disassembly'  => $noDisassembly,
            'qty_disassembly' => $qtyDisassembly,
            'msg'             => 'Pembongkaran ' . $qtyDisassembly . ' Box ' . $namaPaket . ' berhasil direkam (' . $noDisassembly . '). Komponen telah kembali menjadi stok eceran bebas.'
        ];
    }

    public function get_assembly_history($filters = [], $limit = 100)
    {
        $this->ensure_bundling_schema();
        $this->db->select('a.*, g.nama_gudang');
        $this->db->from('tberp_bundling_assembly a');
        $this->db->join('tb_gudang g', 'g.id_gudang = a.id_gudang', 'left');

        if (!empty($filters['search'])) {
            $this->db->group_start();
            $this->db->like('a.no_assembly', $filters['search']);
            $this->db->or_like('a.nama_paket', $filters['search']);
            $this->db->or_like('a.no_request', $filters['search']);
            $this->db->group_end();
        }
        if (!empty($filters['date_from'])) {
            $this->db->where('a.tanggal >=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $this->db->where('a.tanggal <=', $filters['date_to']);
        }

        $this->db->order_by('a.id_assembly', 'DESC');
        $this->db->limit($limit);
        return $this->db->get()->result_array();
    }

    public function get_assembly_by_id($id_assembly)
    {
        $this->ensure_bundling_schema();
        $this->db->select('a.*, g.nama_gudang');
        $this->db->from('tberp_bundling_assembly a');
        $this->db->join('tb_gudang g', 'g.id_gudang = a.id_gudang', 'left');
        $this->db->where('a.id_assembly', $id_assembly);
        $header = $this->db->get()->row_array();

        if ($header) {
            $header['details'] = $this->db->where('id_assembly', $id_assembly)->get('tberp_bundling_assembly_detail')->result_array();
        }
        return $header;
    }

    public function get_disassembly_history($filters = [], $limit = 100)
    {
        $this->ensure_bundling_schema();
        $this->db->select('d.*, g.nama_gudang');
        $this->db->from('tberp_bundling_disassembly d');
        $this->db->join('tb_gudang g', 'g.id_gudang = d.id_gudang', 'left');

        if (!empty($filters['search'])) {
            $this->db->group_start();
            $this->db->like('d.no_disassembly', $filters['search']);
            $this->db->or_like('d.nama_paket', $filters['search']);
            $this->db->group_end();
        }
        if (!empty($filters['date_from'])) {
            $this->db->where('d.tanggal >=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $this->db->where('d.tanggal <=', $filters['date_to']);
        }

        $this->db->order_by('d.id_disassembly', 'DESC');
        $this->db->limit($limit);
        return $this->db->get()->result_array();
    }

    public function get_disassembly_by_id($id_disassembly)
    {
        $this->ensure_bundling_schema();
        $this->db->select('d.*, g.nama_gudang');
        $this->db->from('tberp_bundling_disassembly d');
        $this->db->join('tb_gudang g', 'g.id_gudang = d.id_gudang', 'left');
        $this->db->where('d.id_disassembly', $id_disassembly);
        $header = $this->db->get()->row_array();

        if ($header) {
            $header['details'] = $this->db->where('id_disassembly', $id_disassembly)->get('tberp_bundling_disassembly_detail')->result_array();
        }
        return $header;
    }

    // =========================================================================
    // 6. HELPER STOK & MASTER BARANG INTEGRATION
    // =========================================================================

    public function get_stock_available_by_gudang($kdBarang, $gudangId)
    {
        if (!$this->db->table_exists('tberp_stock_batch')) return 0.0;
        $row = $this->db->select('SUM(qty_on_hand - GREATEST(qty_reserved, 0)) as available')
            ->where('kd_barang', $kdBarang)
            ->where('gudang_id', $gudangId)
            ->get('tberp_stock_batch')
            ->row();
        return $row ? max(0, (float)$row->available) : 0.0;
    }

    public function get_stock_batches_by_gudang($kdBarang, $gudangId)
    {
        if (!$this->db->table_exists('tberp_stock_batch')) return [];
        return $this->db->select('*')
            ->where('kd_barang', $kdBarang)
            ->where('gudang_id', $gudangId)
            ->where('(qty_on_hand - GREATEST(qty_reserved, 0)) > 0', null, false)
            ->order_by('expired_date', 'ASC')
            ->get('tberp_stock_batch')
            ->result_array();
    }

    private function get_stock_batch_row($kdBarang, $gudangId, $noLot, $expiredDate)
    {
        $this->db->from('tberp_stock_batch')
            ->where('kd_barang', $kdBarang)
            ->where('gudang_id', $gudangId);

        if ($noLot === '-' || empty($noLot)) {
            $this->db->group_start()
                ->where('no_lot', '-')
                ->or_where('no_lot', '')
                ->or_where('no_lot IS NULL', null, false)
                ->group_end();
        } else {
            $this->db->where('no_lot', $noLot);
        }

        if ($expiredDate === null || $expiredDate === '') {
            $this->db->group_start()
                ->where('expired_date IS NULL', null, false)
                ->or_where('expired_date', '0000-00-00')
                ->group_end();
        } else {
            $this->db->where('expired_date', $expiredDate);
        }

        return $this->db->limit(1)->get()->row_array();
    }

    private function apply_batch_delta($kdBarang, $gudangId, $noLot, $expiredDate, $delta)
    {
        $batch = $this->get_stock_batch_row($kdBarang, $gudangId, $noLot, $expiredDate);
        $now = date('Y-m-d H:i:s');

        if ($batch) {
            $this->db->where('id', $batch['id']);
            if ((float)$delta < 0) {
                $this->db->where('(qty_on_hand - qty_reserved) >= ' . abs((float)$delta), null, false);
            }
            $this->db->set('qty_on_hand', 'qty_on_hand + ' . (float)$delta, false)
                ->set('update_at', $now)
                ->update('tberp_stock_batch');
            return $this->db->affected_rows() > 0;
        }

        if ((float)$delta < 0) {
            return false;
        }

        return $this->db->insert('tberp_stock_batch', [
            'kd_barang'    => $kdBarang,
            'gudang_id'    => $gudangId,
            'no_lot'       => $noLot ?: '-',
            'expired_date' => $expiredDate ?: null,
            'qty_on_hand'  => (float)$delta,
            'qty_reserved' => 0,
            'created_at'   => $now,
            'update_at'    => $now
        ]);
    }

    /**
     * Memastikan Paket terdaftar dan tersinkronisasi di master barang tbpo_barang & tbpo_barang_akun
     */
    public function ensure_product_in_master_barang($kodePaket, $namaPaket, $satuan = 'Box')
    {
        $kodePaket = trim((string)$kodePaket);
        $namaPaket = trim((string)$namaPaket);
        if ($kodePaket === '') return false;

        $satuan = !empty($satuan) ? trim($satuan) : 'Box';

        $exists = $this->db->where('kode_barang', $kodePaket)->limit(1)->get('tbpo_barang')->row();
        if (!$exists) {
            $this->db->insert('tbpo_barang', [
                'kode_barang'              => $kodePaket,
                'kd_suplier'               => 'BUNDLING',
                'nama_barang'              => $namaPaket,
                'satuan'                   => $satuan,
                'isi'                      => 1,
                'kemasan'                  => 1,
                'is_active'                => 'T',
                'is_lot'                   => 'T',
                'is_inventori'             => 'T',
                'is_beli'                  => 'F',
                'is_jual'                  => 'T',
                'hpp_average'              => 'T',
                'kode_akun_persediaan'     => '14010', // Persediaan # 1
                'kode_akun_harga_pokok'    => '51010', // Harga Pokok Penjualan # 1
                'kode_akun_penjualan'      => '41011',
                'kode_akun_pengiriman_beli'=> '51013',
                'kode_akun_pengiriman_jual'=> '64010',
                'kode_akun_retur_penjualan'=> '41014'
            ]);
        } else {
            $updateData = [
                'nama_barang'              => $namaPaket,
                'satuan'                   => $satuan,
                'is_active'                => 'T',
                'is_inventori'             => 'T',
                'is_jual'                  => 'T',
                'kode_akun_persediaan'     => '14010',
                'kode_akun_harga_pokok'    => '51010',
                'kode_akun_penjualan'      => '41011',
                'kode_akun_pengiriman_beli'=> '51013',
                'kode_akun_pengiriman_jual'=> '64010',
                'kode_akun_retur_penjualan'=> '41014'
            ];
            $this->db->where('kode_barang', $kodePaket)->update('tbpo_barang', $updateData);
        }

        // Sinkronisasi juga ke tbpo_barang_akun
        if ($this->db->table_exists('tbpo_barang_akun')) {
            $existsAkun = $this->db->where('kode_barang', $kodePaket)->limit(1)->get('tbpo_barang_akun')->row();
            if (!$existsAkun) {
                $this->db->insert('tbpo_barang_akun', [
                    'kode_barang'              => $kodePaket,
                    'kode_akun_penjualan'      => '41011',
                    'kode_akun_persediaan'     => '14010',
                    'kode_akun_harga_pokok'    => '51010',
                    'kode_akun_retur_penjualan'=> '41014',
                    'kode_akun_pengiriman_beli'=> '51013',
                    'kode_akun_pengiriman_jual'=> '64010'
                ]);
            } else {
                $this->db->where('kode_barang', $kodePaket)->update('tbpo_barang_akun', [
                    'kode_akun_persediaan'  => '14010',
                    'kode_akun_harga_pokok' => '51010',
                    'kode_akun_penjualan'   => '41011'
                ]);
            }
        }

        return true;
    }

    /**
     * Menghitung Biaya Pokok (HPP) Komponen berdasarkan metode LIFO (Last In, First Out)
     * Mengambil alokasi kuantitas dari pembelian barang terakhir yang masuk (LPB terbaru / PO terbaru).
     * 
     * Contoh Kasus:
     * Kebutuhan: 450 unit.
     * Riwayat beli: Masuk tgl 01/08/2026 @ 5.000 (200 unit) & Masuk terakhir @ 6.000 (300 unit).
     * LIFO mengambil:
     * - 300 unit @ 6.000 = 1.800.000
     * - 150 unit @ 5.000 =   750.000
     * Total Modal: 2.550.000 (HPP LIFO per unit = 5.666,67).
     */
    public function calculate_item_lifo_cost($kd_barang, $qty_kebutuhan, $gudang_id = null, $nama_barang = '')
    {
        $kd_barang = trim((string)$kd_barang);
        $qty_kebutuhan = max(0, (float)$qty_kebutuhan);
        if ($kd_barang === '') {
            return ['total_modal' => 0.0, 'hpp_satuan' => 0.0, 'breakdown' => []];
        }

        // 1. Ambil riwayat LPB (Laporan Penerimaan Barang) terurut dari yang TERAKHIR dibeli (LIFO)
        $purchaseBatches = [];
        if ($this->db->table_exists('tb_lpb_detail')) {
            $this->db->select('ld.id_detail_lpb, ld.qty_diterima, ld.harga_satuan, ld.no_lot, l.nomor_lpb, l.tgl_sj, ld.input_at');
            $this->db->from('tb_lpb_detail ld');
            $this->db->join('tb_lpb l', 'l.id_lpb = ld.id_lpb', 'left');
            $this->db->where('ld.kd_barang', $kd_barang);
            $this->db->where('ld.qty_diterima >', 0);
            $this->db->where('ld.harga_satuan >', 0);
            $this->db->order_by('ld.id_detail_lpb', 'DESC');
            $lpbs = $this->db->get()->result_array();

            foreach ($lpbs as $lp) {
                $purchaseBatches[] = [
                    'dokumen' => $lp['nomor_lpb'] ?: 'LPB-' . $lp['id_detail_lpb'],
                    'tanggal' => !empty($lp['tgl_sj']) ? $lp['tgl_sj'] : date('Y-m-d', strtotime($lp['input_at'])),
                    'qty'     => (float)$lp['qty_diterima'],
                    'harga'   => (float)$lp['harga_satuan'],
                    'no_lot'  => $lp['no_lot']
                ];
            }
        }

        // 2. Jika riwayat LPB belum ada atau kurang, ambil dari tbpo_detail_po
        if ($this->db->table_exists('tbpo_detail_po')) {
            $this->db->select('id_det_po, no_po, kd_po, tgl_transaksi, qty, qty_kecil, hrg_satuan, harga_satuan_kecil_setelah_diskon, harga_satuan_exclude');
            $this->db->from('tbpo_detail_po');
            $this->db->where('kd_barang', $kd_barang);
            $this->db->group_start()
                ->where('hrg_satuan >', 0)
                ->or_where('harga_satuan_kecil_setelah_diskon >', 0)
            ->group_end();
            $this->db->order_by('id_det_po', 'DESC');
            $pos = $this->db->get()->result_array();

            foreach ($pos as $po) {
                $h = !empty($po['harga_satuan_kecil_setelah_diskon']) && (float)$po['harga_satuan_kecil_setelah_diskon'] > 0
                    ? (float)$po['harga_satuan_kecil_setelah_diskon']
                    : (!empty($po['harga_satuan_exclude']) && (float)$po['harga_satuan_exclude'] > 0
                        ? (float)$po['harga_satuan_exclude']
                        : (float)$po['hrg_satuan']);
                $q = !empty($po['qty_kecil']) && (float)$po['qty_kecil'] > 0 ? (float)$po['qty_kecil'] : (float)$po['qty'];
                if ($h > 0 && $q > 0) {
                    $purchaseBatches[] = [
                        'dokumen' => $po['no_po'] ?: $po['kd_po'] ?: 'PO-' . $po['id_det_po'],
                        'tanggal' => $po['tgl_transaksi'],
                        'qty'     => $q,
                        'harga'   => $h,
                        'no_lot'  => '-'
                    ];
                }
            }
        }

        // 3. Fallback jika nama barang dicari (misal kode barang berbeda)
        if (empty($purchaseBatches) && !empty($nama_barang)) {
            $firstWord = explode(' ', trim($nama_barang))[0];
            if ($this->db->table_exists('tb_lpb_detail') && $this->db->table_exists('tbpo_barang')) {
                $matchLpb = $this->db->select('ld.id_detail_lpb, ld.qty_diterima, ld.harga_satuan, ld.no_lot, l.nomor_lpb, l.tgl_sj, ld.input_at')
                    ->from('tb_lpb_detail ld')
                    ->join('tb_lpb l', 'l.id_lpb = ld.id_lpb', 'left')
                    ->join('tbpo_barang b', 'ld.kd_barang = b.kode_barang')
                    ->like('b.nama_barang', $firstWord)
                    ->where('ld.qty_diterima >', 0)
                    ->where('ld.harga_satuan >', 0)
                    ->order_by('ld.id_detail_lpb', 'DESC')
                    ->get()
                    ->result_array();
                foreach ($matchLpb as $lp) {
                    $purchaseBatches[] = [
                        'dokumen' => $lp['nomor_lpb'] ?: 'LPB-' . $lp['id_detail_lpb'],
                        'tanggal' => !empty($lp['tgl_sj']) ? $lp['tgl_sj'] : date('Y-m-d', strtotime($lp['input_at'])),
                        'qty'     => (float)$lp['qty_diterima'],
                        'harga'   => (float)$lp['harga_satuan'],
                        'no_lot'  => $lp['no_lot']
                    ];
                }
            }
        }

        // 4. Hitung alokasi LIFO
        $sisaKebutuhan = $qty_kebutuhan > 0 ? $qty_kebutuhan : 1.0;
        $totalModal = 0.0;
        $lastPriceFound = 0.0;
        $breakdown = [];

        foreach ($purchaseBatches as $b) {
            if ($sisaKebutuhan <= 0) break;
            $bQty = (float)$b['qty'];
            $bPrice = (float)$b['harga'];
            if ($bQty <= 0 || $bPrice <= 0) continue;

            $ambil = min($sisaKebutuhan, $bQty);
            $sub = $ambil * $bPrice;

            $totalModal += $sub;
            $sisaKebutuhan -= $ambil;
            $lastPriceFound = $bPrice;

            $breakdown[] = [
                'dokumen'  => $b['dokumen'],
                'tanggal'  => $b['tanggal'],
                'qty'      => $ambil,
                'harga'    => $bPrice,
                'subtotal' => $sub
            ];
        }

        // Jika kebutuhan melebihi stok riwayat pembelian yang tercatat, sisa kebutuhan dihargai dengan harga terakhir
        if ($sisaKebutuhan > 0) {
            $fallbackPrice = ($lastPriceFound > 0) 
                ? $lastPriceFound 
                : (float)$this->get_item_average_hpp($kd_barang, $gudang_id, $nama_barang);

            if ($fallbackPrice <= 0 && stripos($nama_barang, 'kaos') !== false) {
                $fallbackPrice = 15000.00;
            }

            $sub = $sisaKebutuhan * $fallbackPrice;
            $totalModal += $sub;
            $lastPriceFound = $fallbackPrice;
            $breakdown[] = [
                'dokumen'  => 'HARGA_TERAKHIR',
                'tanggal'  => date('Y-m-d'),
                'qty'      => $sisaKebutuhan,
                'harga'    => $fallbackPrice,
                'subtotal' => $sub
            ];
        }

        $hppSatuan = ($qty_kebutuhan > 0) ? ($totalModal / $qty_kebutuhan) : $lastPriceFound;

        if ($qty_kebutuhan <= 0) {
            $totalModal = 0.0;
        }

        return [
            'total_modal' => $totalModal,
            'hpp_satuan'  => $hppSatuan,
            'breakdown'   => $breakdown
        ];
    }

    /**
     * Helper mendapatkan HPP Average dari M_PenyesuaianBarang, LPB, PO, atau referensi pasar
     */
    public function get_item_average_hpp($kd_barang, $gudang_id = null, $nama_barang = '')
    {
        $kd_barang = trim((string)$kd_barang);
        if ($kd_barang === '') return 0.0;

        // 1. Cek melalui model M_PenyesuaianBarang (Moving Average pergerakan stok)
        if (isset($this->M_PenyesuaianBarang) && method_exists($this->M_PenyesuaianBarang, 'get_item_hpp')) {
            $hpp = (float)$this->M_PenyesuaianBarang->get_item_hpp($kd_barang, $gudang_id);
            if ($hpp > 0) return $hpp;
        }

        // 2. Cek tb_lpb_detail (harga satuan pembelian LPB terbaru berdasarkan kode barang)
        if ($this->db->table_exists('tb_lpb_detail')) {
            $lastLpb = $this->db->select('harga_satuan')
                ->where('kd_barang', $kd_barang)
                ->where('harga_satuan >', 0)
                ->order_by('id_detail_lpb', 'DESC')
                ->limit(1)
                ->get('tb_lpb_detail')
                ->row();
            if ($lastLpb && (float)$lastLpb->harga_satuan > 0) {
                return (float)$lastLpb->harga_satuan;
            }
        }

        // 3. Cek tbpo_detail_po (harga pembelian PO resmi berdasarkan kode barang)
        if ($this->db->table_exists('tbpo_detail_po')) {
            $lastPo = $this->db->select('COALESCE(NULLIF(harga_satuan_kecil_setelah_diskon, 0), NULLIF(harga_satuan_exclude, 0), hrg_satuan) AS hpp')
                ->where('kd_barang', $kd_barang)
                ->group_start()
                    ->where('hrg_satuan >', 0)
                    ->or_where('harga_satuan_kecil_setelah_diskon >', 0)
                ->group_end()
                ->order_by('id_det_po', 'DESC')
                ->limit(1)
                ->get('tbpo_detail_po')
                ->row();
            if ($lastPo && (float)$lastPo->hpp > 0) {
                return (float)$lastPo->hpp;
            }
        }

        // 4. Jika belum ketemu dengan kode barang (misal kode alias / dummy / barcode lama), cari via nama_barang
        if (!empty($nama_barang)) {
            $firstWord = explode(' ', trim($nama_barang))[0];
            
            // Cek di tb_lpb_detail join tbpo_barang by nama barang
            if ($this->db->table_exists('tb_lpb_detail') && $this->db->table_exists('tbpo_barang')) {
                $matchLpb = $this->db->select('ld.harga_satuan')
                    ->from('tb_lpb_detail ld')
                    ->join('tbpo_barang b', 'ld.kd_barang = b.kode_barang')
                    ->like('b.nama_barang', $firstWord)
                    ->where('ld.harga_satuan >', 0)
                    ->order_by('ld.id_detail_lpb', 'DESC')
                    ->limit(1)
                    ->get()
                    ->row();
                if ($matchLpb && (float)$matchLpb->harga_satuan > 0) {
                    return (float)$matchLpb->harga_satuan;
                }
            }

            // Cek di tbpo_detail_po by nama barang
            if ($this->db->table_exists('tbpo_detail_po')) {
                $matchPo = $this->db->select('COALESCE(NULLIF(harga_satuan_kecil_setelah_diskon, 0), NULLIF(harga_satuan_exclude, 0), hrg_satuan) AS hpp')
                    ->like('nama_barang', $firstWord)
                    ->group_start()
                        ->where('hrg_satuan >', 0)
                        ->or_where('harga_satuan_kecil_setelah_diskon >', 0)
                    ->group_end()
                    ->order_by('id_det_po', 'DESC')
                    ->limit(1)
                    ->get('tbpo_detail_po')
                    ->row();
                if ($matchPo && (float)$matchPo->hpp > 0) {
                    return (float)$matchPo->hpp;
                }
            }
        }

        // 5. Khusus barang merchandise / kaos / bonus promosi yang belum ada data transaksi beli
        if (!empty($nama_barang) && stripos($nama_barang, 'kaos') !== false) {
            return 25000.00; // Standar estimasi biaya produksi kaos merchandise Karisma
        }

        return 0.0;
    }
}

