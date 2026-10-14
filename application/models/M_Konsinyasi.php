<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Model M_Konsinyasi
 * Mengelola siklus penuh barang konsinyasi (titipan supplier):
 * 1. Penerimaan Barang Konsinyasi dari Supplier → tabel tb_konsinyasi_masuk
 * 2. Stok masuk ke gudang konsinyasi (tanpa LPB, tanpa hutang)
 * 3. Sinkronisasi barang terjual dari SO/Faktur → tb_konsinyasi_settlement
 * 4. Penyelesaian (Settlement): input harga + invoice → jurnal Hutang + HPP
 */
class M_Konsinyasi extends CI_Model
{
    public function __construct()
    {
        parent::__construct(); 
        $this->ensure_schema();
    }

    /**
     * Memastikan tabel tb_konsinyasi_settlement tersedia di database
     */
    public function ensure_schema()
    {
        if (!$this->db->table_exists('tb_konsinyasi_settlement')) {
            $this->db->query("
                CREATE TABLE IF NOT EXISTS `tb_konsinyasi_settlement` (
                  `id_settlement` int(11) NOT NULL AUTO_INCREMENT,
                  `no_settlement` varchar(50) NOT NULL,
                  `tanggal_settlement` date NOT NULL,
                  `kd_suplier` varchar(50) NOT NULL,
                  `nama_suplier` varchar(150) NOT NULL,
                  `gudang_id` int(11) NOT NULL DEFAULT 13,
                  `id_so` int(11) DEFAULT NULL,
                  `no_so` varchar(50) DEFAULT NULL,
                  `id_faktur` int(11) DEFAULT NULL,
                  `id_pembayaran` int(11) DEFAULT NULL,
                  `no_faktur` varchar(50) DEFAULT NULL,
                  `customer_name` varchar(150) DEFAULT NULL,
                  `id_lpb_asal` int(11) DEFAULT NULL,
                  `nomor_lpb_asal` varchar(50) DEFAULT NULL,
                  `kd_barang` varchar(50) NOT NULL,
                  `nama_barang` varchar(200) NOT NULL,
                  `no_lot` varchar(100) DEFAULT NULL,
                  `expired_date` date DEFAULT NULL,
                  `qty_terjual` decimal(15,3) NOT NULL DEFAULT 0.000,
                  `qty_retur` decimal(15,3) NOT NULL DEFAULT 0.000,
                  `qty_net` decimal(15,3) NOT NULL DEFAULT 0.000,
                  `satuan` varchar(30) DEFAULT NULL,
                  `hrg_jual` decimal(15,2) NOT NULL DEFAULT 0.00,
                  `tipe_pajak` enum('EXCLUDE','INCLUDE','NON_PPN') NOT NULL DEFAULT 'EXCLUDE',
                  `hrg_satuan_input` decimal(15,2) NOT NULL DEFAULT 0.00,
                  `subtotal_jual` decimal(15,2) NOT NULL DEFAULT 0.00,
                  `hrg_beli_satuan` decimal(15,2) NOT NULL DEFAULT 0.00,
                  `subtotal_beli` decimal(15,2) NOT NULL DEFAULT 0.00,
                  `ppn_persen` decimal(5,2) NOT NULL DEFAULT 0.00,
                  `nilai_ppn` decimal(15,2) NOT NULL DEFAULT 0.00,
                  `total_tagihan_beli` decimal(15,2) NOT NULL DEFAULT 0.00,
                  `no_invoice_supplier` varchar(100) DEFAULT NULL,
                  `tgl_invoice_supplier` date DEFAULT NULL,
                  `status` enum('DI_KIOS','PENDING','LAKU','BILLED','CANCELLED') NOT NULL DEFAULT 'DI_KIOS',
                  `id_jurnal_pembelian` bigint(20) unsigned DEFAULT NULL,
                  `settled_at` datetime DEFAULT NULL,
                  `settled_by` varchar(50) DEFAULT NULL,
                  `catatan` text DEFAULT NULL,
                  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                  PRIMARY KEY (`id_settlement`),
                  KEY `idx_status` (`status`),
                  KEY `idx_suplier` (`kd_suplier`),
                  KEY `idx_so` (`no_so`),
                  KEY `idx_faktur` (`no_faktur`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
            ");
        } else {
            // Pastikan enum kolom status sudah mendukung DI_KIOS dan LAKU jika tabel sudah ada sebelumnya
            $col = $this->db->query("SHOW COLUMNS FROM tb_konsinyasi_settlement LIKE 'status'")->row_array();
            if ($col && strpos((string)$col['Type'], 'DI_KIOS') === false) {
                $this->db->query("ALTER TABLE tb_konsinyasi_settlement MODIFY COLUMN status ENUM('DI_KIOS','PENDING','LAKU','BILLED','CANCELLED') NOT NULL DEFAULT 'DI_KIOS'");
            }
            if (!$this->db->field_exists('id_pembayaran', 'tb_konsinyasi_settlement')) {
                $this->db->query("ALTER TABLE tb_konsinyasi_settlement ADD COLUMN id_pembayaran INT(11) NULL DEFAULT NULL AFTER id_faktur, ADD INDEX idx_pembayaran (id_pembayaran)");
            }
        }

        if (!$this->db->table_exists('tb_konsinyasi_masuk')) {
            $this->db->query("
                CREATE TABLE IF NOT EXISTS `tb_konsinyasi_masuk` (
                  `id_masuk` int(11) NOT NULL AUTO_INCREMENT,
                  `nomor_masuk` varchar(50) NOT NULL,
                  `tanggal_masuk` date NOT NULL,
                  `kd_suplier` varchar(50) NOT NULL,
                  `nama_suplier` varchar(150) NOT NULL,
                  `gudang_id` int(11) NOT NULL DEFAULT 13,
                  `no_surat_jalan` varchar(100) DEFAULT NULL,
                  `tgl_surat_jalan` date DEFAULT NULL,
                  `keterangan` text DEFAULT NULL,
                  `status` enum('RECEIVED','CANCELLED') NOT NULL DEFAULT 'RECEIVED',
                  `created_by` varchar(50) DEFAULT NULL,
                  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                  PRIMARY KEY (`id_masuk`),
                  UNIQUE KEY `idx_nomor_masuk` (`nomor_masuk`),
                  KEY `idx_suplier` (`kd_suplier`),
                  KEY `idx_tgl` (`tanggal_masuk`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
            ");
        }

        if (!$this->db->table_exists('tb_konsinyasi_masuk_detail')) {
            $this->db->query("
                CREATE TABLE IF NOT EXISTS `tb_konsinyasi_masuk_detail` (
                  `id_detail` int(11) NOT NULL AUTO_INCREMENT,
                  `id_masuk` int(11) NOT NULL,
                  `kd_barang` varchar(50) NOT NULL,
                  `nama_barang` varchar(200) NOT NULL,
                  `satuan` varchar(30) DEFAULT NULL,
                  `qty` decimal(15,3) NOT NULL DEFAULT 0.000,
                  `no_lot` varchar(100) DEFAULT NULL,
                  `expired_date` date DEFAULT NULL,
                  `keterangan` varchar(255) DEFAULT NULL,
                  PRIMARY KEY (`id_detail`),
                  KEY `idx_id_masuk` (`id_masuk`),
                  KEY `idx_kd_barang` (`kd_barang`),
                  KEY `idx_no_lot` (`no_lot`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
            ");
        }

        if (!$this->db->table_exists('tb_konsinyasi_faktur')) {
            $this->db->query("
                CREATE TABLE IF NOT EXISTS `tb_konsinyasi_faktur` (
                  `id_faktur_konsinyasi` int(11) NOT NULL AUTO_INCREMENT,
                  `no_faktur_konsinyasi` varchar(50) NOT NULL,
                  `id_faktur_induk` int(11) NOT NULL,
                  `no_faktur_induk` varchar(50) NOT NULL,
                  `id_pembayaran` int(11) NOT NULL,
                  `id_settlement` int(11) DEFAULT NULL,
                  `id_so` int(11) DEFAULT NULL,
                  `no_so` varchar(50) DEFAULT NULL,
                  `termin_ke` int(11) NOT NULL DEFAULT 1,
                  `tanggal_faktur` date NOT NULL,
                  `kd_customer` varchar(50) DEFAULT NULL,
                  `nama_customer` varchar(150) DEFAULT NULL,
                  `kd_suplier` varchar(50) DEFAULT NULL,
                  `nama_suplier` varchar(150) DEFAULT NULL,
                  `gudang_id` int(11) DEFAULT 13,
                  `kd_barang` varchar(50) NOT NULL,
                  `nama_barang` varchar(200) NOT NULL,
                  `no_lot` varchar(100) DEFAULT NULL,
                  `expired_date` date DEFAULT NULL,
                  `qty` decimal(15,3) NOT NULL DEFAULT 0.000,
                  `satuan` varchar(30) DEFAULT 'PCS',
                  `hrg_satuan` decimal(15,2) NOT NULL DEFAULT 0.00,
                  `subtotal` decimal(15,2) NOT NULL DEFAULT 0.00,
                  `jumlah_bayar` decimal(15,2) NOT NULL DEFAULT 0.00,
                  `metode_pembayaran` varchar(50) DEFAULT NULL,
                  `status` enum('LAKU','BILLED','CANCELLED') NOT NULL DEFAULT 'LAKU',
                  `created_by` varchar(100) DEFAULT NULL,
                  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                  PRIMARY KEY (`id_faktur_konsinyasi`),
                  UNIQUE KEY `idx_no_faktur_konsinyasi` (`no_faktur_konsinyasi`),
                  KEY `idx_faktur_induk` (`id_faktur_induk`),
                  KEY `idx_pembayaran` (`id_pembayaran`),
                  KEY `idx_settlement` (`id_settlement`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
            ");
        }

        if ($this->db->table_exists('tbkeu_pembayaran_faktur') && !$this->db->field_exists('no_faktur_konsinyasi', 'tbkeu_pembayaran_faktur')) {
            $this->db->query("ALTER TABLE tbkeu_pembayaran_faktur ADD COLUMN no_faktur_konsinyasi VARCHAR(50) NULL DEFAULT NULL AFTER no_faktur, ADD INDEX idx_no_faktur_konsinyasi (no_faktur_konsinyasi)");
        }

        if (!$this->db->table_exists('tb_konsinyasi_lpb_batch_stock')) {
            $this->db->query("
                CREATE TABLE IF NOT EXISTS `tb_konsinyasi_lpb_batch_stock` (
                  `id_batch_stock` INT UNSIGNED NOT NULL AUTO_INCREMENT,
                  `id_lpb` INT NOT NULL,
                  `id_detail_lpb` INT NOT NULL,
                  `nomor_lpb` VARCHAR(50) NOT NULL,
                  `tgl_lpb` DATE NOT NULL,
                  `kd_suplier` VARCHAR(50) DEFAULT NULL,
                  `nama_suplier` VARCHAR(150) DEFAULT NULL,
                  `gudang_id` INT NOT NULL DEFAULT 13,
                  `kd_barang` VARCHAR(50) NOT NULL,
                  `nama_barang` VARCHAR(200) NOT NULL,
                  `no_lot` VARCHAR(100) DEFAULT NULL,
                  `expired_date` DATE DEFAULT NULL,
                  `qty_masuk` DECIMAL(15,3) NOT NULL DEFAULT 0.000,
                  `qty_terjual` DECIMAL(15,3) NOT NULL DEFAULT 0.000,
                  `qty_sisa` DECIMAL(15,3) NOT NULL DEFAULT 0.000,
                  `satuan` VARCHAR(30) DEFAULT 'PCS',
                  `harga_beli_satuan` DECIMAL(18,4) NOT NULL DEFAULT 0.0000,
                  `status` ENUM('AVAILABLE','PARTIAL','EXHAUSTED') NOT NULL DEFAULT 'AVAILABLE',
                  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                  PRIMARY KEY (`id_batch_stock`),
                  UNIQUE KEY `uk_lpb_detail` (`id_lpb`, `id_detail_lpb`),
                  KEY `idx_kd_barang_sisa` (`kd_barang`, `qty_sisa`, `tgl_lpb`, `id_lpb`),
                  KEY `idx_lpb_batch` (`id_lpb`, `nomor_lpb`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
            ");
        }

        if (!$this->db->table_exists('tb_konsinyasi_cogs_allocation')) {
            $this->db->query("
                CREATE TABLE IF NOT EXISTS `tb_konsinyasi_cogs_allocation` (
                  `id_cogs_alloc` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                  `id_pembayaran` INT NOT NULL,
                  `id_faktur` INT NOT NULL,
                  `no_faktur_konsinyasi` VARCHAR(50) NOT NULL,
                  `id_batch_stock` INT UNSIGNED NOT NULL,
                  `id_lpb` INT NOT NULL,
                  `nomor_lpb` VARCHAR(50) NOT NULL,
                  `kd_barang` VARCHAR(50) NOT NULL,
                  `no_lot` VARCHAR(100) DEFAULT NULL,
                  `expired_date` DATE DEFAULT NULL,
                  `qty_allocated` DECIMAL(15,3) NOT NULL,
                  `harga_pokok_satuan` DECIMAL(18,4) NOT NULL,
                  `subtotal_cogs` DECIMAL(18,4) NOT NULL,
                  `id_jurnal_gi` BIGINT UNSIGNED DEFAULT NULL,
                  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                  PRIMARY KEY (`id_cogs_alloc`),
                  KEY `idx_alloc_pembayaran` (`id_pembayaran`),
                  KEY `idx_alloc_faktur` (`id_faktur`),
                  KEY `idx_alloc_batch_stock` (`id_batch_stock`),
                  KEY `idx_alloc_jurnal_gi` (`id_jurnal_gi`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
            ");
        }

        // Pastikan Master Gudang Konsinyasi, Gudang Titipan di Kios, dan Gudang Konsinyasi Lunas tersedia di tb_gudang
        $this->ensure_consignment_warehouses();
    }

    /**
     * Memastikan 3 jenis gudang konsinyasi tersedia di database:
     * 1. Gudang Konsinyasi (Stok fisik konsinyasi masuk dari supplier)
     * 2. Gudang Titipan di Kios (Barang saat difakturkan ke kios sebagai titipan)
     * 3. Gudang Konsinyasi Lunas (Barang konsinyasi yang telah dilunasi ke supplier dan diakui perusahaan)
     */
    public function ensure_consignment_warehouses()
    {
        if (!$this->db->table_exists('tb_gudang')) {
            return;
        }

        // 1. Gudang Konsinyasi Utama (default id 13 jika belum ada)
        $whKons = $this->db->select('id_gudang')
            ->from('tb_gudang')
            ->group_start()
                ->where('id_gudang', 13)
                ->or_like('nama_gudang', 'Konsi')
            ->group_end()
            ->limit(1)->get()->row_array();
        if (!$whKons) {
            $this->db->insert('tb_gudang', [
                'nama_gudang' => 'Gdg. Konsinyasi',
                'tipe'        => 'INDUK',
                'is_active'   => 1,
                'created_at'  => date('Y-m-d H:i:s')
            ]);
        }

        // 2. Gudang Titipan di Kios
        $whKios = $this->db->select('id_gudang')
            ->from('tb_gudang')
            ->like('nama_gudang', 'Titipan di Kios')
            ->limit(1)->get()->row_array();
        if (!$whKios) {
            $this->db->insert('tb_gudang', [
                'nama_gudang' => 'Gdg. Titipan di Kios',
                'tipe'        => 'INDUK',
                'is_active'   => 1,
                'created_at'  => date('Y-m-d H:i:s')
            ]);
        }

        // 3. Gudang Konsinyasi Lunas
        $whLunas = $this->db->select('id_gudang')
            ->from('tb_gudang')
            ->like('nama_gudang', 'Konsinyasi Lunas')
            ->limit(1)->get()->row_array();
        if (!$whLunas) {
            $this->db->insert('tb_gudang', [
                'nama_gudang' => 'Gdg. Konsinyasi Lunas',
                'tipe'        => 'INDUK',
                'is_active'   => 1,
                'created_at'  => date('Y-m-d H:i:s')
            ]);
        }
    }

    /**
     * Mendapatkan ID Gudang Konsinyasi Utama
     */
    public function get_warehouse_konsinyasi_id()
    {
        $row = $this->db->select('id_gudang')
            ->from('tb_gudang')
            ->group_start()
                ->where('id_gudang', 13)
                ->or_like('nama_gudang', 'Konsi')
            ->group_end()
            ->limit(1)->get()->row_array();
        return $row ? (int)$row['id_gudang'] : 13;
    }

    /**
     * Mendapatkan ID Gudang Titipan di Kios
     */
    public function get_warehouse_titipan_kios_id()
    {
        $row = $this->db->select('id_gudang')
            ->from('tb_gudang')
            ->like('nama_gudang', 'Titipan di Kios')
            ->limit(1)->get()->row_array();
        return $row ? (int)$row['id_gudang'] : 14;
    }

    /**
     * Mendapatkan ID Gudang Konsinyasi Lunas
     */
    public function get_warehouse_konsinyasi_lunas_id()
    {
        $row = $this->db->select('id_gudang')
            ->from('tb_gudang')
            ->like('nama_gudang', 'Konsinyasi Lunas')
            ->limit(1)->get()->row_array();
        return $row ? (int)$row['id_gudang'] : 15;
    }

    /**
     * Menghasilkan nomor referensi unik settlement
     * Format: KONS-SET-YYMMDD-XXXX
     */
    public function generate_settlement_no()
    {
        $prefix = 'KONS-SET-' . date('ymd') . '-';
        $lastRow = $this->db
            ->select('no_settlement')
            ->like('no_settlement', $prefix, 'after')
            ->order_by('id_settlement', 'DESC')
            ->limit(1)
            ->get('tb_konsinyasi_settlement')
            ->row_array();

        $seq = 1;
        if (!empty($lastRow['no_settlement'])) {
            $lastNum = (int) substr($lastRow['no_settlement'], strlen($prefix));
            $seq = $lastNum + 1;
        }

        return $prefix . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Mengambil ID gudang yang berkategori konsinyasi
     */
    public function get_consignment_warehouse_ids()
    {
        $rows = $this->db
            ->select('id_gudang')
            ->group_start()
                ->like('nama_gudang', 'Konsi', 'both')
                ->or_where('tipe', 'KONSINYASI')
                ->or_where('id_gudang', 13)
            ->group_end()
            ->get('tb_gudang')
            ->result_array();

        $ids = array_map(function ($r) {
            return (int) $r['id_gudang'];
        }, $rows);

        if (empty($ids)) {
            $ids = [13];
        }

        return array_unique($ids);
    }

    /**
     * Sinkronisasi data barang konsinyasi yang terjual dari SO / Faktur
     * Otomatis membaca penjualan yang mengambil stok dari gudang konsinyasi
     */
    public function sync_pending_consignment_sales()
    {
        $warehouseIds = $this->get_consignment_warehouse_ids();
        if (empty($warehouseIds)) {
            return 0;
        }

        // Jalankan update sinkronisasi nomor faktur dan id faktur yang baru terbit untuk transaksi yang sudah tercatat
        $this->db->query("
            UPDATE tb_konsinyasi_settlement s
            JOIN tbso_faktur_penjualan fp ON fp.id_so = s.id_so
            SET s.no_faktur = fp.no_faktur, s.id_faktur = fp.id_faktur
            WHERE ((s.no_faktur IS NULL OR s.no_faktur = '') AND (s.no_faktur NOT LIKE '%-%'))
               OR (s.id_faktur IS NULL OR s.id_faktur = 0)
        ");

        // Hapus data konsinyasi_settlement yang berasal dari SO tapi belum ada faktur (karena rule baru: baru masuk kios ketika sudah jadi faktur)
        $this->db->query("
            DELETE s FROM tb_konsinyasi_settlement s
            WHERE s.id_so IS NOT NULL 
              AND (s.id_faktur IS NULL OR s.id_faktur = 0)
              AND s.status = 'DI_KIOS'
        ");

        $this->db->select("
            so.id_so,
            so.no_so,
            COALESCE(fp.no_faktur, so.no_faktur) AS no_faktur,
            fp.id_faktur,
            so.tanggal_transaksi,
            so.customer_name,
            so.gudang_id,
            sod.id AS id_so_detail,
            sod.kd_barang,
            sod.nama_barang,
            sod.qty,
            sod.satuan,
            sod.no_lot,
            sod.expired_date,
            sod.hrg_satuan,
            sod.subtotal_after_disc
        ");
        $this->db->from('tbso_sales_order so');
        $this->db->join('tbso_sales_order_detail sod', 'sod.id_so = so.id_so', 'inner');
        // Ubah join ke inner agar HANYA SO yang sudah terbit faktur yang masuk ke konsinyasi
        $this->db->join('tbso_faktur_penjualan fp', 'fp.id_so = so.id_so', 'inner');
        $this->db->where_in('so.gudang_id', $warehouseIds);
        $this->db->where_not_in('so.status', ['draft', 'cancelled']);
        $this->db->where('sod.qty >', 0);
        $salesItems = $this->db->get()->result_array();

        $insertedCount = 0;
        foreach ($salesItems as $item) {
            // Cek apakah transaksi SO detail ini sudah pernah dicatat di tabel settlement
            $exists = $this->db
                ->where('id_so', $item['id_so'])
                ->where('kd_barang', $item['kd_barang'])
                ->where('no_lot', trim((string) $item['no_lot']))
                ->get('tb_konsinyasi_settlement')
                ->row_array();

            if ($exists) {
                // Update nomor faktur jika sudah terbit belakangan
                $updates = [];
                if (empty($exists['no_faktur']) && !empty($item['no_faktur'])) {
                    $updates['no_faktur'] = $item['no_faktur'];
                }
                if (empty($exists['id_faktur']) && !empty($item['id_faktur'])) {
                    $updates['id_faktur'] = (int) $item['id_faktur'];
                }

                // Lengkapi relasi LPB lama yang gagal ditemukan ketika barang
                // berpindah dari gudang penerimaan ke gudang konsinyasi.
                if (empty($exists['id_lpb_asal']) || empty($exists['nomor_lpb_asal'])) {
                    $originLpb = $this->find_origin_consignment_lpb(
                        $item['kd_barang'],
                        $item['gudang_id'],
                        $item['no_lot']
                    );
                    if (!empty($originLpb['id_lpb'])) {
                        $updates['id_lpb_asal'] = (int) $originLpb['id_lpb'];
                        $updates['nomor_lpb_asal'] = $originLpb['nomor_lpb'];
                        if (!empty($originLpb['kd_suplier'])) {
                            $updates['kd_suplier'] = $originLpb['kd_suplier'];
                        }
                        if (!empty($originLpb['nama_suplier'])) {
                            $updates['nama_suplier'] = $originLpb['nama_suplier'];
                        }
                    }
                }
                if (!empty($updates)) {
                    $this->db->where('id_settlement', $exists['id_settlement'])->update('tb_konsinyasi_settlement', $updates);
                }
                continue;
            }

            // Cari supplier asal dan nomor LPB konsinyasi dari penerimaan fisik
            $originLpb = $this->find_origin_consignment_lpb(
                $item['kd_barang'],
                $item['gudang_id'],
                $item['no_lot']
            );

            $kdSuplier = !empty($originLpb['kd_suplier']) ? $originLpb['kd_suplier'] : 'SUPP-KONS';
            $namaSuplier = !empty($originLpb['nama_suplier']) ? $originLpb['nama_suplier'] : 'Supplier Konsinyasi';
            $idLpbAsal = !empty($originLpb['id_lpb']) ? (int) $originLpb['id_lpb'] : null;
            $nomorLpbAsal = !empty($originLpb['nomor_lpb']) ? $originLpb['nomor_lpb'] : null;

            $qtyTerjual = (float) $item['qty'];
            $hrgJual = (float) $item['hrg_satuan'];
            $subtotalJual = (float) ($item['subtotal_after_disc'] > 0 ? $item['subtotal_after_disc'] : ($qtyTerjual * $hrgJual));

            $insertPayload = [
                'no_settlement'       => $this->generate_settlement_no(),
                'tanggal_settlement'  => $item['tanggal_transaksi'] ?: date('Y-m-d'),
                'kd_suplier'          => $kdSuplier,
                'nama_suplier'        => $namaSuplier,
                'gudang_id'           => (int) $item['gudang_id'],
                'id_so'               => (int) $item['id_so'],
                'no_so'               => $item['no_so'],
                'id_faktur'           => !empty($item['id_faktur']) ? (int) $item['id_faktur'] : null,
                'no_faktur'           => !empty($item['no_faktur']) ? $item['no_faktur'] : null,
                'customer_name'       => $item['customer_name'] ?: 'Customer Umum',
                'id_lpb_asal'         => $idLpbAsal,
                'nomor_lpb_asal'      => $nomorLpbAsal,
                'kd_barang'           => $item['kd_barang'],
                'nama_barang'         => $item['nama_barang'],
                'no_lot'              => trim((string) $item['no_lot']),
                'expired_date'        => !empty($item['expired_date']) ? $item['expired_date'] : null,
                'qty_terjual'         => $qtyTerjual,
                'qty_retur'           => 0.000,
                'qty_net'             => $qtyTerjual,
                'satuan'              => $item['satuan'] ?: 'PCS',
                'hrg_jual'            => $hrgJual,
                'subtotal_jual'       => $subtotalJual,
                'hrg_beli_satuan'     => 0.00,
                'subtotal_beli'       => 0.00,
                'ppn_persen'          => 0.00,
                'nilai_ppn'           => 0.00,
                'total_tagihan_beli'  => 0.00,
                'no_invoice_supplier' => null,
                'tgl_invoice_supplier'=> null,
                'status'              => 'DI_KIOS',
                'created_at'          => date('Y-m-d H:i:s')
            ];

            $this->db->insert('tb_konsinyasi_settlement', $insertPayload);
            $insertedCount++;
        }

        // Selaraskan data settlement dengan alokasi FIFO LPB aktual
        $this->sync_settlements_with_fifo_allocation();

        return $insertedCount;
    }

    /**
     * Menyinkronkan data tb_konsinyasi_settlement dengan alokasi FIFO LPB aktual di tb_konsinyasi_cogs_allocation
     * - Memastikan id_lpb_asal, nomor_lpb_asal, no_lot, expired_date, dan hrg_beli_satuan mengikuti alokasi FIFO LPB.
     * - Jika satu pembayaran teralokasi lintas batch LPB (misal 10 pcs di LPB A dan 2 pcs di LPB B),
     *   settlement akan dipecah per porsi LPB agar proses pelunasan di Purchasing mengikuti antrean LPB terlama (FIFO).
     */
    public function sync_settlements_with_fifo_allocation()
    {
        if (!$this->db->table_exists('tb_konsinyasi_cogs_allocation') || !$this->db->table_exists('tb_konsinyasi_settlement')) {
            return 0;
        }

        // Ambil semua pembayaran yang ada di tb_konsinyasi_cogs_allocation
        $payments = $this->db
            ->select('DISTINCT id_pembayaran', false)
            ->where('id_pembayaran IS NOT NULL', null, false)
            ->where('id_pembayaran >', 0)
            ->get('tb_konsinyasi_cogs_allocation')
            ->result_array();

        if (empty($payments)) {
            return 0;
        }

        $syncCount = 0;
        foreach ($payments as $p) {
            $idPembayaran = (int) $p['id_pembayaran'];

            // Ambil rincian alokasi FIFO untuk pembayaran ini, urutkan berdasarkan LPB terlama (id_cogs_alloc ASC / id_lpb ASC)
            $allocs = $this->db
                ->where('id_pembayaran', $idPembayaran)
                ->order_by('id_cogs_alloc', 'ASC')
                ->get('tb_konsinyasi_cogs_allocation')
                ->result_array();

            if (empty($allocs)) {
                continue;
            }

            // Ambil data settlement yang terikat ke id_pembayaran ini
            $settlements = $this->db
                ->where('id_pembayaran', $idPembayaran)
                ->order_by('id_settlement', 'ASC')
                ->get('tb_konsinyasi_settlement')
                ->result_array();

            if (empty($settlements)) {
                continue;
            }

            // Jika jumlah settlement sama dengan jumlah alokasi FIFO (atau hanya 1 alokasi):
            if (count($settlements) === count($allocs)) {
                for ($i = 0; $i < count($allocs); $i++) {
                    $alloc = $allocs[$i];
                    $st = $settlements[$i];
                    $update = [];

                    if ((int)$st['id_lpb_asal'] !== (int)$alloc['id_lpb']) {
                        $update['id_lpb_asal'] = (int)$alloc['id_lpb'];
                    }
                    if ($st['nomor_lpb_asal'] !== $alloc['nomor_lpb']) {
                        $update['nomor_lpb_asal'] = $alloc['nomor_lpb'];
                    }
                    if (!empty($alloc['no_lot']) && $st['no_lot'] !== $alloc['no_lot']) {
                        $update['no_lot'] = $alloc['no_lot'];
                    }
                    if (!empty($alloc['expired_date']) && $st['expired_date'] !== $alloc['expired_date']) {
                        $update['expired_date'] = $alloc['expired_date'];
                    }
                    if (abs((float)$st['qty_net'] - (float)$alloc['qty_allocated']) > 0.001) {
                        $update['qty_terjual'] = (float)$alloc['qty_allocated'];
                        $update['qty_net']     = (float)$alloc['qty_allocated'];
                        $update['subtotal_jual'] = round((float)$alloc['qty_allocated'] * (float)$st['hrg_jual'], 2);
                    }
                    $cogsPrice = (float)$alloc['harga_pokok_satuan'];
                    if ($cogsPrice > 0 && abs((float)$st['hrg_beli_satuan'] - $cogsPrice) > 0.01) {
                        $update['hrg_beli_satuan'] = $cogsPrice;
                        if ($st['status'] !== 'BILLED') {
                            $update['hrg_satuan_input'] = $cogsPrice;
                        }
                    }
                    if (!empty($alloc['no_faktur_konsinyasi']) && empty($st['no_faktur'])) {
                        $update['no_faktur'] = $alloc['no_faktur_konsinyasi'];
                    }

                    if (!empty($update)) {
                        $this->db->where('id_settlement', (int)$st['id_settlement'])->update('tb_konsinyasi_settlement', $update);
                        $syncCount++;
                    }
                }
            } elseif (count($settlements) === 1 && count($allocs) > 1) {
                // Kasus pecahan: 1 settlement tapi ada multi batch LPB (>1 alokasi FIFO)
                // Update baris pertama dengan alokasi pertama
                $primarySt = $settlements[0];
                $firstAlloc = $allocs[0];
                $firstQty = (float)$firstAlloc['qty_allocated'];
                $cogsPrice1 = (float)$firstAlloc['harga_pokok_satuan'];

                $updatePrimary = [
                    'id_lpb_asal'     => (int)$firstAlloc['id_lpb'],
                    'nomor_lpb_asal'  => $firstAlloc['nomor_lpb'],
                    'no_lot'          => $firstAlloc['no_lot'],
                    'expired_date'    => $firstAlloc['expired_date'],
                    'qty_terjual'     => $firstQty,
                    'qty_net'         => $firstQty,
                    'subtotal_jual'   => round($firstQty * (float)$primarySt['hrg_jual'], 2),
                    'hrg_beli_satuan' => $cogsPrice1,
                    'catatan'         => 'Porsi alokasi FIFO LPB ' . $firstAlloc['nomor_lpb'] . ' (' . $firstQty . ' ' . $primarySt['satuan'] . ')'
                ];
                if ($primarySt['status'] !== 'BILLED') {
                    $updatePrimary['hrg_satuan_input'] = $cogsPrice1;
                }
                $this->db->where('id_settlement', (int)$primarySt['id_settlement'])->update('tb_konsinyasi_settlement', $updatePrimary);
                $syncCount++;

                // Pecah dan insert record settlement tambahan untuk alokasi LPB ke-2 dst
                for ($j = 1; $j < count($allocs); $j++) {
                    $extraAlloc = $allocs[$j];
                    $extraQty = (float)$extraAlloc['qty_allocated'];
                    $cogsPriceExtra = (float)$extraAlloc['harga_pokok_satuan'];

                    $extraPayload = $primarySt;
                    unset($extraPayload['id_settlement']);
                    $extraPayload['no_settlement']     = $this->generate_settlement_no();
                    $extraPayload['id_lpb_asal']       = (int)$extraAlloc['id_lpb'];
                    $extraPayload['nomor_lpb_asal']    = $extraAlloc['nomor_lpb'];
                    $extraPayload['no_lot']            = $extraAlloc['no_lot'];
                    $extraPayload['expired_date']      = $extraAlloc['expired_date'];
                    $extraPayload['qty_terjual']       = $extraQty;
                    $extraPayload['qty_net']           = $extraQty;
                    $extraPayload['subtotal_jual']     = round($extraQty * (float)$primarySt['hrg_jual'], 2);
                    $extraPayload['hrg_beli_satuan']   = $cogsPriceExtra;
                    $extraPayload['hrg_satuan_input']  = ($primarySt['status'] !== 'BILLED') ? $cogsPriceExtra : (float)$primarySt['hrg_satuan_input'];
                    $extraPayload['subtotal_beli']     = round($extraQty * $cogsPriceExtra, 2);
                    $extraPayload['total_tagihan_beli']= round($extraQty * $cogsPriceExtra, 2);
                    $extraPayload['catatan']           = 'Porsi alokasi FIFO LPB ' . $extraAlloc['nomor_lpb'] . ' (' . $extraQty . ' ' . $primarySt['satuan'] . ')';
                    $extraPayload['created_at']        = date('Y-m-d H:i:s');

                    $this->db->insert('tb_konsinyasi_settlement', $extraPayload);
                    $syncCount++;
                }
            }
        }

        return $syncCount;
    }

    /**
     * Mencari data penerimaan asal untuk mengetahui pemilik/supplier barang titipan
     * Prioritas:
     * 1. tb_lpb resmi (LPB Konsinyasi hasil PO di Logistik)
     * 2. tbpo_barang (supplier default barang)
     */
    private function find_origin_consignment_lpb($kdBarang, $gudangId, $noLot = '')
    {
        // 1. Cek dari penerimaan resmi tb_lpb (diutamakan jenis LPB Konsinyasi)
        $this->db->select('h.id_lpb, h.nomor_lpb, h.kd_suplier, h.nama_suplier');
        $this->db->from('tb_lpb h');
        $this->db->join('tb_lpb_detail d', 'd.id_lpb = h.id_lpb', 'inner');
        $this->db->where('d.kd_barang', $kdBarang);
        $this->db->where('h.gudang_id', (int) $gudangId);
        if (!empty($noLot)) {
            $this->db->where('d.no_lot', trim((string) $noLot));
        }
        if ($this->db->field_exists('jenis_lpb', 'tb_lpb')) {
            $this->db->order_by("(CASE WHEN h.jenis_lpb = 'LPB Konsinyasi' THEN 1 ELSE 2 END)", 'ASC', false);
        }
        $this->db->order_by('h.id_lpb', 'DESC');
        $this->db->limit(1);
        $row = $this->db->get()->row_array();

        // Barang dapat diterima lebih dahulu di gudang reguler lalu dipindahkan
        // ke gudang konsinyasi. Jika gudang berbeda, lot tetap menjadi identitas
        // asal yang paling spesifik untuk menelusuri LPB Konsinyasi.
        if (!$row && trim((string) $noLot) !== '' && $this->db->field_exists('jenis_lpb', 'tb_lpb')) {
            $row = $this->db
                ->select('h.id_lpb, h.nomor_lpb, h.kd_suplier, h.nama_suplier')
                ->from('tb_lpb h')
                ->join('tb_lpb_detail d', 'd.id_lpb = h.id_lpb', 'inner')
                ->where('d.kd_barang', $kdBarang)
                ->where('d.no_lot', trim((string) $noLot))
                ->where('h.jenis_lpb', 'LPB Konsinyasi')
                ->order_by('h.id_lpb', 'DESC')
                ->limit(1)
                ->get()
                ->row_array();
        }

        if ($row) {
            // LPB tetap merupakan sumber penerimaan yang sah walaupun supplier pada
            // header lama belum terisi. Supplier dapat dilengkapi dari master barang.
            if (empty($row['kd_suplier'])) {
                $supplierBarang = $this->db
                    ->select('b.kd_suplier, COALESCE(s.nama_suplier, b.kd_suplier) AS nama_suplier')
                    ->from('tbpo_barang b')
                    ->join('tbpo_suplier s', 's.kd_suplier = b.kd_suplier', 'left')
                    ->  where('b.kode_barang', $kdBarang)
                    ->limit(1)
                    ->get()
                    ->row_array();

                if ($supplierBarang && !empty($supplierBarang['kd_suplier'])) {
                    $row['kd_suplier'] = $supplierBarang['kd_suplier'];
                    $row['nama_suplier'] = $supplierBarang['nama_suplier'];
                }
            }

            return $row;
        }

        // 2. Fallback: cari di tbpo_barang supplier default dan join ke tbpo_suplier untuk mendapatkan nama_suplier
        $barang = $this->db
            ->select('b.kd_suplier, COALESCE(s.nama_suplier, b.kd_suplier) AS nama_suplier')
            ->from('tbpo_barang b')
            ->join('tbpo_suplier s', 's.kd_suplier = b.kd_suplier', 'left')
            ->where('b.kode_barang', $kdBarang)
            ->limit(1)
            ->get()
            ->row_array();

        if ($barang && !empty($barang['kd_suplier'])) {
            return [
                'id_lpb'       => null,
                'nomor_lpb'    => null,
                'kd_suplier'   => $barang['kd_suplier'],
                'nama_suplier' => !empty($barang['nama_suplier']) ? trim((string)$barang['nama_suplier']) : $barang['kd_suplier']
            ];
        }

        return null;
    }

    /**
     * Mengambil ringkasan daftar supplier yang memiliki barang laku (LAKU/PENDING)
     * Mengelompokkan transaksi settlement berdasarkan supplier beserta rincian jumlah barang, qty, dan estimasi tagihan.
     *
     * @param array $filters
     * @return array
     */
    public function get_laku_suppliers_summary(array $filters = [])
    {
        $this->db->select("
            s.kd_suplier,
            s.nama_suplier,
            COUNT(*) AS total_transaksi,
            COUNT(DISTINCT s.kd_barang) AS total_jenis_barang,
            SUM(s.qty_net) AS total_qty,
            GROUP_CONCAT(DISTINCT s.nama_barang ORDER BY s.nama_barang ASC SEPARATOR ', ') AS daftar_barang,
            MAX(s.tanggal_settlement) AS tgl_terakhir,
            MIN(s.tanggal_settlement) AS tgl_awal,
            SUM(s.qty_net * COALESCE(NULLIF(s.hrg_beli_satuan, 0), NULLIF(pod.hrg_satuan, 0), NULLIF(lpbd.harga_satuan, 0), 0)) AS total_estimasi_tagihan,
            MAX(s.satuan) AS satuan
        ", false);
        $this->db->from('tb_konsinyasi_settlement s');
        $this->db->join('tb_lpb lpb', '(lpb.id_lpb = s.id_lpb_asal OR (s.id_lpb_asal IS NULL AND lpb.nomor_lpb = s.nomor_lpb_asal))', 'left');
        $this->db->join('tbpo_po po', 'po.no_po = lpb.no_po', 'left');
        $this->db->join('tbpo_detail_po pod', 'pod.no_po = po.no_po AND pod.kd_barang = s.kd_barang', 'left');
        $this->db->join('tb_lpb_detail lpbd', 'lpbd.id_lpb = lpb.id_lpb AND lpbd.kd_barang = s.kd_barang AND (lpbd.no_lot = s.no_lot OR s.no_lot IS NULL OR s.no_lot = \'\')', 'left');

        $this->db->where_in('s.status', ['LAKU', 'PENDING']);

        if (!empty($filters['date_from'])) {
            $this->db->where('s.tanggal_settlement >=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $this->db->where('s.tanggal_settlement <=', $filters['date_to']);
        }
        if (!empty($filters['search'])) {
            $search = trim((string)$filters['search']);
            $this->db->group_start();
                $this->db->like('s.kd_suplier', $search);
                $this->db->or_like('s.nama_suplier', $search);
                $this->db->or_like('s.nama_barang', $search);
                $this->db->or_like('s.no_settlement', $search);
            $this->db->group_end();
        }

        $this->db->group_by(['s.kd_suplier', 's.nama_suplier']);
        $this->db->order_by('s.nama_suplier', 'ASC');

        return $this->db->get()->result_array();
    }

    /**
     * Mengambil daftar settlement dengan filter
     * Catatan: Khusus tab Barang Laku (status LAKU / PENDING), transaksi barang dan lot yang sama
     * diakumulasikan menjadi 1 baris per batch LPB penerimaan sesuai prinsip FIFO pelunasan supplier.
     */
    public function get_settlement_list(array $filters = [])
    {
        $statusFilter = !empty($filters['status']) ? strtoupper(trim((string) $filters['status'])) : 'LAKU';
        $isLakuGrouping = ($statusFilter === 'LAKU' || $statusFilter === 'PENDING');

        if ($isLakuGrouping) {
            // Grouping barang konsinyasi berbasis batch LPB penerimaan dan akumulasi transaksi laku.
            // Rumus sisa belum lunas: QTY LPB Masuk dikurangi QTY yang telah resmi dilunasi (status BILLED / posting jurnal).
            // Transaksi yang berstatus LAKU belum dilunasi sehingga sisa belum lunas tetap utuh sampai diproses.
            $whereParts = ["(lpb.gudang_id = 13 OR lpb.jenis_lpb = 'LPB Konsinyasi' OR lpb.nomor_lpb LIKE '%K')"];
            $params = [];

            if (!empty($filters['kd_suplier']) && $filters['kd_suplier'] !== 'SEMUA') {
                $whereParts[] = "COALESCE(st_meta.kd_suplier, sup.kd_suplier, b.kd_suplier, lpb.kd_suplier) = " . $this->db->escape(trim((string) $filters['kd_suplier']));
            }

            if (!empty($filters['date_from'])) {
                $whereParts[] = "COALESCE(laku.tanggal_settlement, lpb.tgl_sj) >= " . $this->db->escape($filters['date_from']);
            }
            if (!empty($filters['date_to'])) {
                $whereParts[] = "COALESCE(laku.tanggal_settlement, lpb.tgl_sj) <= " . $this->db->escape($filters['date_to']);
            }

            if (!empty($filters['search'])) {
                $searchEsc = $this->db->escape('%' . trim((string) $filters['search']) . '%');
                $whereParts[] = "(
                    lpb.nomor_lpb LIKE {$searchEsc}
                    OR po.no_po LIKE {$searchEsc}
                    OR b.nama_barang LIKE {$searchEsc}
                    OR lpbd.kd_barang LIKE {$searchEsc}
                    OR lpbd.no_lot LIKE {$searchEsc}
                    OR COALESCE(st_meta.nama_suplier, sup.nama_suplier) LIKE {$searchEsc}
                    OR laku.customer_name LIKE {$searchEsc}
                    OR laku.no_so LIKE {$searchEsc}
                    OR laku.no_faktur LIKE {$searchEsc}
                )";
            }

            // Tampilkan batch LPB jika masih memiliki sisa barang yang belum lunas ATAU ada barang laku yang menunggu pelunasan
            $whereParts[] = "(COALESCE(laku.qty_laku, 0) > 0 OR GREATEST(0, lpbd.qty_diterima - COALESCE(billed.qty_billed, 0)) > 0)";
            $whereSql = implode(' AND ', $whereParts);

            $sql = "
                SELECT 
                    COALESCE(laku.id_settlement, st_meta.id_settlement, 0) AS id_settlement,
                    laku.all_settlement_ids,
                    COALESCE(laku.total_gabungan_transaksi, 0) AS total_gabungan_transaksi,
                    COALESCE(laku.no_settlement, st_meta.no_settlement, CONCAT('LPB-', lpb.nomor_lpb)) AS no_settlement,
                    COALESCE(laku.tanggal_settlement, lpb.tgl_sj) AS tanggal_settlement,
                    lpb.tgl_sj AS tanggal_lpb_masuk,
                    COALESCE(st_meta.kd_suplier, sup.kd_suplier, b.kd_suplier, lpb.kd_suplier) AS kd_suplier,
                    COALESCE(st_meta.nama_suplier, sup.nama_suplier, lpb.nama_suplier) AS nama_suplier,
                    COALESCE(laku.gudang_id, lpb.gudang_id, 13) AS gudang_id,
                    g.nama_gudang,
                    laku.customer_name,
                    laku.no_so,
                    laku.no_faktur,
                    lpb.id_lpb AS id_lpb_asal,
                    lpb.nomor_lpb AS nomor_lpb_asal,
                    lpb.nomor_lpb AS lpb_no,
                    po.no_po AS po_no_po,
                    lpbd.kd_barang,
                    COALESCE(st_meta.nama_barang, b.nama_barang) AS nama_barang,
                    lpbd.no_lot,
                    lpbd.expired_date,
                    lpbd.qty_diterima AS qty_lpb_masuk,
                    COALESCE(laku.qty_terjual, 0) AS qty_terjual,
                    0.000 AS qty_retur,
                    COALESCE(laku.qty_laku, 0) AS qty_net,
                    COALESCE(billed.qty_billed, 0) AS qty_billed,
                    GREATEST(0, lpbd.qty_diterima - COALESCE(billed.qty_billed, 0)) AS qty_sisa_belum_lunas,
                    COALESCE(b.satuan, 'PCS') AS satuan,
                    COALESCE(laku.hrg_jual, 0) AS hrg_jual,
                    COALESCE(laku.subtotal_jual, 0) AS subtotal_jual,
                    COALESCE(pod.hrg_satuan, lpbd.harga_satuan, 0) AS po_hrg_satuan,
                    COALESCE(pod.harga_satuan_exclude, lpbd.harga_satuan, 0) AS po_hrg_satuan_exclude,
                    CASE 
                        WHEN LOWER(po.keterangan_harga_ppn) = 'include' THEN 'INCLUDE'
                        WHEN LOWER(po.keterangan_harga_ppn) = 'non_ppn' THEN 'NON_PPN'
                        ELSE 'EXCLUDE'
                    END AS po_tipe_pajak,
                    CASE 
                        WHEN COALESCE(laku.qty_laku, 0) > 0 THEN 'LAKU'
                        ELSE 'DI_KIOS'
                    END AS status,
                    COALESCE(laku.tipe_pajak, 'EXCLUDE') AS tipe_pajak,
                    COALESCE(laku.hrg_satuan_input, pod.hrg_satuan, lpbd.harga_satuan, 0) AS hrg_satuan_input,
                    COALESCE(laku.hrg_beli_satuan, pod.hrg_satuan, lpbd.harga_satuan, 0) AS hrg_beli_satuan,
                    COALESCE(laku.subtotal_beli, 0) AS subtotal_beli,
                    COALESCE(laku.ppn_persen, 11) AS ppn_persen,
                    COALESCE(laku.nilai_ppn, 0) AS nilai_ppn,
                    COALESCE(laku.total_tagihan_beli, 0) AS total_tagihan_beli,
                    laku.no_invoice_supplier,
                    laku.tgl_invoice_supplier,
                    NULL AS nomor_jurnal,
                    NULL AS id_jurnal_pembelian
                FROM tb_lpb lpb
                JOIN tb_lpb_detail lpbd ON lpbd.id_lpb = lpb.id_lpb
                LEFT JOIN tbpo_barang b ON b.kode_barang = lpbd.kd_barang
                LEFT JOIN tbpo_suplier sup ON sup.kd_suplier = COALESCE(lpb.kd_suplier, b.kd_suplier)
                LEFT JOIN tbpo_po po ON po.no_po = lpb.no_po
                LEFT JOIN tbpo_detail_po pod ON pod.no_po = po.no_po AND pod.kd_barang = lpbd.kd_barang
                LEFT JOIN tb_gudang g ON g.id_gudang = COALESCE(lpb.gudang_id, 13)
                LEFT JOIN (
                    SELECT 
                        id_lpb_asal, nomor_lpb_asal, kd_barang, no_lot,
                        MAX(gudang_id) AS gudang_id,
                        MIN(id_settlement) AS id_settlement,
                        MIN(no_settlement) AS no_settlement,
                        MAX(tanggal_settlement) AS tanggal_settlement,
                        GROUP_CONCAT(id_settlement ORDER BY id_settlement ASC SEPARATOR ',') AS all_settlement_ids,
                        COUNT(id_settlement) AS total_gabungan_transaksi,
                        MAX(kd_suplier) AS kd_suplier,
                        MAX(nama_suplier) AS nama_suplier,
                        MAX(nama_barang) AS nama_barang,
                        SUM(qty_terjual) AS qty_terjual,
                        SUM(qty_net) AS qty_laku,
                        MAX(hrg_jual) AS hrg_jual,
                        SUM(subtotal_jual) AS subtotal_jual,
                        MAX(tipe_pajak) AS tipe_pajak,
                        MAX(hrg_satuan_input) AS hrg_satuan_input,
                        MAX(hrg_beli_satuan) AS hrg_beli_satuan,
                        SUM(subtotal_beli) AS subtotal_beli,
                        MAX(ppn_persen) AS ppn_persen,
                        SUM(nilai_ppn) AS nilai_ppn,
                        SUM(total_tagihan_beli) AS total_tagihan_beli,
                        MAX(no_invoice_supplier) AS no_invoice_supplier,
                        MAX(tgl_invoice_supplier) AS tgl_invoice_supplier,
                        GROUP_CONCAT(DISTINCT customer_name ORDER BY customer_name ASC SEPARATOR ', ') AS customer_name,
                        GROUP_CONCAT(DISTINCT no_so ORDER BY no_so ASC SEPARATOR ', ') AS no_so,
                        GROUP_CONCAT(DISTINCT no_faktur ORDER BY no_faktur ASC SEPARATOR ', ') AS no_faktur
                    FROM tb_konsinyasi_settlement
                    WHERE status IN ('LAKU', 'PENDING')
                    GROUP BY id_lpb_asal, nomor_lpb_asal, kd_barang, no_lot
                ) laku ON (laku.id_lpb_asal = lpb.id_lpb OR (laku.id_lpb_asal IS NULL AND laku.nomor_lpb_asal = lpb.nomor_lpb))
                       AND laku.kd_barang = lpbd.kd_barang 
                       AND (laku.no_lot = lpbd.no_lot OR (laku.no_lot IS NULL AND lpbd.no_lot IS NULL))
                LEFT JOIN (
                    SELECT id_lpb_asal, nomor_lpb_asal, kd_barang, no_lot, SUM(qty_net) AS qty_billed
                    FROM tb_konsinyasi_settlement
                    WHERE status = 'BILLED'
                    GROUP BY id_lpb_asal, nomor_lpb_asal, kd_barang, no_lot
                ) billed ON (billed.id_lpb_asal = lpb.id_lpb OR (billed.id_lpb_asal IS NULL AND billed.nomor_lpb_asal = lpb.nomor_lpb))
                         AND billed.kd_barang = lpbd.kd_barang 
                         AND (billed.no_lot = lpbd.no_lot OR (billed.no_lot IS NULL AND lpbd.no_lot IS NULL))
                LEFT JOIN (
                    SELECT 
                        id_lpb_asal, nomor_lpb_asal, kd_barang, no_lot,
                        MIN(id_settlement) AS id_settlement,
                        MIN(no_settlement) AS no_settlement,
                        MAX(kd_suplier) AS kd_suplier,
                        MAX(nama_suplier) AS nama_suplier,
                        MAX(nama_barang) AS nama_barang
                    FROM tb_konsinyasi_settlement
                    GROUP BY id_lpb_asal, nomor_lpb_asal, kd_barang, no_lot
                ) st_meta ON (st_meta.id_lpb_asal = lpb.id_lpb OR (st_meta.id_lpb_asal IS NULL AND st_meta.nomor_lpb_asal = lpb.nomor_lpb))
                          AND st_meta.kd_barang = lpbd.kd_barang 
                          AND (st_meta.no_lot = lpbd.no_lot OR (st_meta.no_lot IS NULL AND lpbd.no_lot IS NULL))
                WHERE {$whereSql}
                ORDER BY COALESCE(laku.qty_laku, 0) DESC, lpb.id_lpb ASC
            ";

            return $this->db->query($sql)->result_array();
        }

        // Mode standar untuk status BILLED atau SEMUA (tanpa grouping transaksi)
        $this->db->select("
            s.*, 
            g.nama_gudang, 
            j.nomor_jurnal,
            COALESCE(NULLIF(s.nomor_lpb_asal, ''), lpb.nomor_lpb) AS lpb_no,
            COALESCE(lpb.no_po, po.no_po) AS po_no_po,
            lpb.tgl_sj AS tanggal_lpb_masuk,
            COALESCE(lpbd.qty_diterima, 0) AS qty_lpb_masuk,
            GREATEST(0, COALESCE(lpbd.qty_diterima, 0) - COALESCE((
                SELECT SUM(st_sub.qty_net) 
                FROM tb_konsinyasi_settlement st_sub 
                WHERE (st_sub.id_lpb_asal = s.id_lpb_asal OR (s.id_lpb_asal IS NULL AND st_sub.nomor_lpb_asal = s.nomor_lpb_asal))
                  AND st_sub.kd_barang = s.kd_barang
                  AND (st_sub.no_lot = s.no_lot OR (s.no_lot IS NULL AND st_sub.no_lot IS NULL))
                  AND st_sub.status = 'BILLED'
            ), 0)) AS qty_sisa_belum_lunas,
            COALESCE(NULLIF(s.hrg_beli_satuan, 0), NULLIF(pod.hrg_satuan, 0), NULLIF(lpbd.harga_satuan, 0), 0) AS po_hrg_satuan,
            COALESCE(NULLIF(s.hrg_beli_satuan, 0), NULLIF(pod.harga_satuan_exclude, 0), NULLIF(lpbd.harga_satuan, 0), 0) AS po_hrg_satuan_exclude,
            CASE 
                WHEN LOWER(po.keterangan_harga_ppn) = 'include' THEN 'INCLUDE'
                WHEN LOWER(po.keterangan_harga_ppn) = 'non_ppn' THEN 'NON_PPN'
                ELSE 'EXCLUDE'
            END AS po_tipe_pajak
        ", false);
        $this->db->from('tb_konsinyasi_settlement s');
        $this->db->join('tb_gudang g', 'g.id_gudang = s.gudang_id', 'left');
        $this->db->join('tbkeu_jurnal j', 'j.id_jurnal = s.id_jurnal_pembelian', 'left');
        $this->db->join('tb_lpb lpb', '(lpb.id_lpb = s.id_lpb_asal OR (s.id_lpb_asal IS NULL AND lpb.nomor_lpb = s.nomor_lpb_asal))', 'left');
        $this->db->join('tbpo_po po', 'po.no_po = lpb.no_po', 'left');
        $this->db->join('tbpo_detail_po pod', 'pod.no_po = po.no_po AND pod.kd_barang = s.kd_barang', 'left');
        $this->db->join('tb_lpb_detail lpbd', 'lpbd.id_lpb = lpb.id_lpb AND lpbd.kd_barang = s.kd_barang AND (lpbd.no_lot = s.no_lot OR s.no_lot IS NULL OR s.no_lot = \'\')', 'left');

        if (!empty($filters['status']) && $filters['status'] !== 'SEMUA') {
            $st = strtoupper(trim((string) $filters['status']));
            $this->db->where('s.status', $st);
        } elseif (!empty($filters['status']) && $filters['status'] === 'SEMUA') {
            $this->db->where_in('s.status', ['LAKU', 'BILLED', 'PENDING']);
        }

        if (!empty($filters['kd_suplier']) && $filters['kd_suplier'] !== 'SEMUA') {
            $this->db->where('s.kd_suplier', trim((string) $filters['kd_suplier']));
        }

        if (!empty($filters['date_from'])) {
            $this->db->where('s.tanggal_settlement >=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $this->db->where('s.tanggal_settlement <=', $filters['date_to']);
        }

        if (!empty($filters['search'])) {
            $search = trim((string) $filters['search']);
            $this->db->group_start();
                $this->db->like('s.no_settlement', $search);
                $this->db->or_like('s.nama_barang', $search);
                $this->db->or_like('s.nama_suplier', $search);
                $this->db->or_like('s.customer_name', $search);
                $this->db->or_like('s.no_so', $search);
                $this->db->or_like('s.no_faktur', $search);
                $this->db->or_like('s.no_invoice_supplier', $search);
                $this->db->or_like('lpb.nomor_lpb', $search);
                $this->db->or_like('po.no_po', $search);
            $this->db->group_end();
        }

        $this->db->order_by('s.id_settlement', 'DESC');
        return $this->db->get()->result_array();
    }

    /**
     * Mengambil 1 baris settlement berdasarkan ID
     */
    public function get_settlement_by_id($idSettlement)
    {
        // Pulihkan relasi LPB untuk data lama yang gagal tertaut karena header LPB
        // tidak memiliki supplier. Harga form tetap harus bersumber dari PO/LPB asal.
        $settlementRef = $this->db
            ->select('id_settlement, id_lpb_asal, nomor_lpb_asal, kd_barang, gudang_id, no_lot')
            ->where('id_settlement', (int) $idSettlement)
            ->get('tb_konsinyasi_settlement')
            ->row_array();

        if ($settlementRef && empty($settlementRef['id_lpb_asal']) && empty($settlementRef['nomor_lpb_asal'])) {
            $originLpb = $this->find_origin_consignment_lpb(
                $settlementRef['kd_barang'],
                $settlementRef['gudang_id'],
                $settlementRef['no_lot']
            );

            if ($originLpb && !empty($originLpb['id_lpb'])) {
                $this->db->where('id_settlement', (int) $idSettlement)->update('tb_konsinyasi_settlement', [
                    'id_lpb_asal'    => (int) $originLpb['id_lpb'],
                    'nomor_lpb_asal' => $originLpb['nomor_lpb']
                ]);
            }
        }

        return $this->db
            ->select("
                s.*, 
                g.nama_gudang, 
                j.nomor_jurnal,
                COALESCE(NULLIF(s.nomor_lpb_asal, ''), lpb.nomor_lpb) AS lpb_no,
                COALESCE(NULLIF(s.nomor_lpb_asal, ''), lpb.nomor_lpb) AS nomor_lpb_asal,
                COALESCE(lpb.no_po, po.no_po) AS po_no_po,
                lpb.tgl_sj AS tanggal_lpb_masuk,
                COALESCE(lpbd.qty_diterima, 0) AS qty_lpb_masuk,
                CASE 
                    WHEN LOWER(po.keterangan_harga_ppn) = 'include' THEN 'INCLUDE'
                    WHEN LOWER(po.keterangan_harga_ppn) = 'non_ppn' THEN 'NON_PPN'
                    ELSE 'EXCLUDE'
                END AS po_tipe_pajak,
                COALESCE(NULLIF(s.hrg_beli_satuan, 0), NULLIF(pod.hrg_satuan, 0), NULLIF(lpbd.harga_satuan, 0), 0) AS po_hrg_satuan,
                COALESCE(NULLIF(s.hrg_beli_satuan, 0), NULLIF(pod.harga_satuan_exclude, 0), NULLIF(lpbd.harga_satuan, 0), 0) AS po_hrg_satuan_exclude
            ", false)
            ->from('tb_konsinyasi_settlement s')
            ->join('tb_gudang g', 'g.id_gudang = s.gudang_id', 'left')
            ->join('tbkeu_jurnal j', 'j.id_jurnal = s.id_jurnal_pembelian', 'left')
            ->join('tb_lpb lpb', '(lpb.id_lpb = s.id_lpb_asal OR (s.id_lpb_asal IS NULL AND lpb.nomor_lpb = s.nomor_lpb_asal))', 'left')
            ->join('tbpo_po po', 'po.no_po = lpb.no_po', 'left')
            ->join('tbpo_detail_po pod', 'pod.no_po = po.no_po AND pod.kd_barang = s.kd_barang', 'left')
            ->join('tb_lpb_detail lpbd', 'lpbd.id_lpb = lpb.id_lpb AND lpbd.kd_barang = s.kd_barang AND (lpbd.no_lot = s.no_lot OR s.no_lot IS NULL OR s.no_lot = \'\')', 'left')
            ->where('s.id_settlement', (int) $idSettlement)
            ->get()
            ->row_array();
    }

    /**
     * Mengambil data detail akumulasi untuk form modal pelunasan jika baris settlement terdiri dari beberapa transaksi
     *
     * @param array $ids
     * @return array|null
     */
    public function get_grouped_settlement_by_ids(array $ids)
    {
        $ids = array_unique(array_filter(array_map('intval', $ids)));
        if (empty($ids)) {
            return null;
        }

        $firstId = reset($ids);
        $base = $this->get_settlement_by_id($firstId);
        if (!$base) {
            return null;
        }

        if (count($ids) === 1) {
            $base['all_settlement_ids'] = (string)$firstId;
            return $base;
        }

        $rows = $this->db
            ->where_in('id_settlement', $ids)
            ->get('tb_konsinyasi_settlement')
            ->result_array();

        $totalQtyNet = 0.0;
        $totalQtyTerjual = 0.0;
        $allSo = [];
        $allFaktur = [];
        $allCustomer = [];

        foreach ($rows as $r) {
            $totalQtyNet += (float)$r['qty_net'];
            $totalQtyTerjual += (float)$r['qty_terjual'];
            if (!empty($r['no_so'])) $allSo[] = $r['no_so'];
            if (!empty($r['no_faktur'])) $allFaktur[] = $r['no_faktur'];
            if (!empty($r['customer_name'])) $allCustomer[] = $r['customer_name'];
        }

        $base['all_settlement_ids'] = implode(',', $ids);
        $base['qty_net'] = $totalQtyNet;
        $base['qty_terjual'] = $totalQtyTerjual;
        $base['total_gabungan_transaksi'] = count($rows);
        if (!empty($allCustomer)) {
            $base['customer_name'] = implode(', ', array_unique($allCustomer));
        }
        if (!empty($allSo)) {
            $base['no_so'] = implode(', ', array_unique($allSo));
        }
        if (!empty($allFaktur)) {
            $base['no_faktur'] = implode(', ', array_unique($allFaktur));
        }

        return $base;
    }

    /**
     * Proses posting penyelesaian konsinyasi:
     * - Mengupdate harga beli resmi dari invoice supplier
     * - Menghitung total tagihan beli
     * - Memposting jurnal Hutang Konsinyasi & HPP
     */
    public function process_settlement($idSettlement, array $payload, $userId = null, $userName = 'SYSTEM')
    {
        $settlement = $this->get_settlement_by_id($idSettlement);
        if (!$settlement) {
            return ['status' => false, 'message' => 'Data settlement konsinyasi tidak ditemukan.'];
        }

        if ($settlement['status'] === 'BILLED') {
            return ['status' => false, 'message' => 'Transaksi ini sudah selesai diposting sebelumnya.'];
        }

        $hrgSatuanInput = (float) ($payload['hrg_satuan_input'] ?? ($payload['hrg_beli_satuan'] ?? 0));
        if ($hrgSatuanInput <= 0 && !empty($settlement['po_hrg_satuan'])) {
            $hrgSatuanInput = (float) $settlement['po_hrg_satuan'];
        }

        if ($hrgSatuanInput <= 0) {
            return ['status' => false, 'message' => 'Harga beli satuan dari supplier harus lebih besar dari 0.'];
        }

        $tipePajak = !empty($payload['tipe_pajak']) 
            ? strtoupper(trim((string) $payload['tipe_pajak'])) 
            : (!empty($settlement['po_tipe_pajak']) ? $settlement['po_tipe_pajak'] : ($settlement['tipe_pajak'] ?? 'EXCLUDE'));

        if (!in_array($tipePajak, ['EXCLUDE', 'INCLUDE', 'NON_PPN'], true)) {
            $tipePajak = 'EXCLUDE';
        }

        $noInvoiceSupplier = !empty($payload['no_invoice_supplier']) ? trim((string) $payload['no_invoice_supplier']) : null;
        $tglInvoiceSupplier = !empty($payload['tgl_invoice_supplier']) ? $payload['tgl_invoice_supplier'] : null;
        $ppnPersen = ($tipePajak === 'NON_PPN') ? 0.00 : (!empty($payload['ppn_persen']) ? (float) $payload['ppn_persen'] : 11.00);

        // Qty yang dilaporkan laku / dibeli oleh kios
        $qtyNetOriginal = (float) $settlement['qty_net'];
        $qtyLaku = !empty($payload['qty_laku']) ? (float) $payload['qty_laku'] : $qtyNetOriginal;
        if ($qtyLaku <= 0 || $qtyLaku > $qtyNetOriginal) {
            $qtyLaku = $qtyNetOriginal;
        }

        $isPartial = ($qtyLaku < $qtyNetOriginal);
        $qtySisaDiKios = round($qtyNetOriginal - $qtyLaku, 3);
        $qtyNet = $qtyLaku;

        if ($tipePajak === 'INCLUDE') {
            // Jika harga dari supplier INCLUDE PPN (misal Rp 55.500 include PPN 11%):
            // Total tagihan = Qty * Harga Satuan Input
            $totalTagihanBeli = round($qtyNet * $hrgSatuanInput, 2);
            // DPP = Total Tagihan / (1 + (PPN% / 100))
            $divider = 1 + ($ppnPersen / 100);
            $subtotalBeli = ($ppnPersen > 0) ? round($totalTagihanBeli / $divider, 2) : $totalTagihanBeli;
            $nilaiPpn = round($totalTagihanBeli - $subtotalBeli, 2);
            $hrgBeliSatuan = ($qtyNet > 0) ? round($subtotalBeli / $qtyNet, 4) : 0;
        } elseif ($tipePajak === 'NON_PPN') {
            $ppnPersen = 0.00;
            $hrgBeliSatuan = $hrgSatuanInput;
            $subtotalBeli = round($qtyNet * $hrgBeliSatuan, 2);
            $nilaiPpn = 0.00;
            $totalTagihanBeli = $subtotalBeli;
        } else {
            // EXCLUDE PPN: Harga input adalah DPP murni sebelum PPN
            $hrgBeliSatuan = $hrgSatuanInput;
            $subtotalBeli = round($qtyNet * $hrgBeliSatuan, 2);
            $nilaiPpn = round(($subtotalBeli * $ppnPersen) / 100, 2);
            $totalTagihanBeli = round($subtotalBeli + $nilaiPpn, 2);
        }

        $this->db->trans_begin();

        // 1. Jika penjualan hanya laku sebagian (misal dari 100 baru laku 50),
        // buat record titipan baru untuk sisa barang yang masih berada di kios (status PENDING)
        if ($isPartial) {
            $sisaPayload = [
                'no_settlement'       => $this->generate_settlement_no(),
                'tanggal_settlement'  => $settlement['tanggal_settlement'],
                'kd_suplier'          => $settlement['kd_suplier'],
                'nama_suplier'        => $settlement['nama_suplier'],
                'gudang_id'           => (int) $settlement['gudang_id'],
                'id_so'               => !empty($settlement['id_so']) ? (int) $settlement['id_so'] : null,
                'no_so'               => $settlement['no_so'],
                'id_faktur'           => !empty($settlement['id_faktur']) ? (int) $settlement['id_faktur'] : null,
                'no_faktur'           => $settlement['no_faktur'],
                'customer_name'       => $settlement['customer_name'],
                'id_lpb_asal'         => !empty($settlement['id_lpb_asal']) ? (int) $settlement['id_lpb_asal'] : null,
                'nomor_lpb_asal'      => $settlement['nomor_lpb_asal'],
                'kd_barang'           => $settlement['kd_barang'],
                'nama_barang'         => $settlement['nama_barang'],
                'no_lot'              => $settlement['no_lot'],
                'expired_date'        => $settlement['expired_date'],
                'qty_terjual'         => $qtySisaDiKios,
                'qty_retur'           => 0.000,
                'qty_net'             => $qtySisaDiKios,
                'satuan'              => $settlement['satuan'],
                'hrg_jual'            => (float) $settlement['hrg_jual'],
                'subtotal_jual'       => round($qtySisaDiKios * (float) $settlement['hrg_jual'], 2),
                'hrg_satuan_input'    => 0.00,
                'hrg_beli_satuan'     => 0.00,
                'subtotal_beli'       => 0.00,
                'ppn_persen'          => 0.00,
                'nilai_ppn'           => 0.00,
                'total_tagihan_beli'  => 0.00,
                'no_invoice_supplier' => null,
                'tgl_invoice_supplier'=> null,
                'status'              => 'DI_KIOS',
                'created_at'          => date('Y-m-d H:i:s'),
                'catatan'             => 'Sisa titipan di kios setelah pelunasan ' . $qtyLaku . ' ' . $settlement['satuan']
            ];
            $this->db->insert('tb_konsinyasi_settlement', $sisaPayload);
        }

        // 2. Baris settlement ini diupdate dengan qty yang laku dibeli kios & status menjadi BILLED
        $updateData = [
            'qty_terjual'         => $qtyLaku,
            'qty_net'             => $qtyLaku,
            'subtotal_jual'       => round($qtyLaku * (float) $settlement['hrg_jual'], 2),
            'tipe_pajak'          => $tipePajak,
            'hrg_satuan_input'    => $hrgSatuanInput,
            'hrg_beli_satuan'     => $hrgBeliSatuan,
            'subtotal_beli'       => $subtotalBeli,
            'ppn_persen'          => $ppnPersen,
            'nilai_ppn'           => $nilaiPpn,
            'total_tagihan_beli'  => $totalTagihanBeli,
            'no_invoice_supplier' => $noInvoiceSupplier,
            'tgl_invoice_supplier'=> $tglInvoiceSupplier,
            'status'              => 'BILLED',
            'settled_at'          => date('Y-m-d H:i:s'),
            'settled_by'          => $userName,
            'catatan'             => trim((string) ($payload['catatan'] ?? ''))
        ];

        $this->db->where('id_settlement', (int) $idSettlement)->update('tb_konsinyasi_settlement', $updateData);

        // Eksekusi Posting Jurnal Akuntansi untuk barang yang resmi dibeli kios
        $this->load->library('Accounting_source_service');
        $journalRes = $this->accounting_source_service->post_consignment_settlement($idSettlement, $userId);

        if (!$journalRes['success']) {
            $this->db->trans_rollback();
            return [
                'status'  => false,
                'message' => 'Gagal memposting jurnal akuntansi: ' . ($journalRes['message'] ?? 'Error akuntansi.'),
                'errors'  => $journalRes['errors'] ?? []
            ];
        }

        $idJurnal = null;
        $nomorJurnal = null;
        if (!empty($journalRes['data'])) {
            if (is_array($journalRes['data'])) {
                $idJurnal = (int) ($journalRes['data']['id_jurnal'] ?? 0) ?: null;
                $nomorJurnal = $journalRes['data']['nomor_jurnal'] ?? null;
            } elseif (is_object($journalRes['data'])) {
                $idJurnal = (int) ($journalRes['data']->id_jurnal ?? 0) ?: null;
                $nomorJurnal = $journalRes['data']->nomor_jurnal ?? null;
            }
        }
        if (!$idJurnal && !empty($journalRes['data']['journal'])) {
            $idJurnal = (int) ($journalRes['data']['journal']->id_jurnal ?? 0) ?: null;
            $nomorJurnal = $journalRes['data']['journal']->nomor_jurnal ?? null;
        }

        if ($idJurnal) {
            $this->db->where('id_settlement', (int) $idSettlement)->update('tb_konsinyasi_settlement', [
                'id_jurnal_pembelian' => $idJurnal
            ]);
        }

        // Catat jejak batch barang konsinyasi lunas ke Gudang Konsinyasi Lunas (qty_on_hand 0 karena langsung keluar/diserahkan ke Kios)
        $whLunasId = $this->get_warehouse_konsinyasi_lunas_id();
        if ($this->db->table_exists('tberp_stock_batch') && $whLunasId) {
            $existBatch = $this->db
                ->where('kd_barang', $settlement['kd_barang'])
                ->where('gudang_id', (string)$whLunasId)
                ->where('no_lot', trim((string)$settlement['no_lot']))
                ->get('tberp_stock_batch')
                ->row_array();

            if ($existBatch) {
                $this->db->where('id', $existBatch['id'])->update('tberp_stock_batch', [
                    'qty_on_hand' => 0.000,
                    'update_at'   => date('Y-m-d H:i:s')
                ]);
            } else {
                $this->db->insert('tberp_stock_batch', [
                    'kd_barang'    => $settlement['kd_barang'],
                    'gudang_id'    => (string)$whLunasId,
                    'no_lot'       => trim((string)$settlement['no_lot']),
                    'expired_date' => !empty($settlement['expired_date']) ? $settlement['expired_date'] : null,
                    'qty_on_hand'  => 0.000,
                    'qty_reserved' => 0.000,
                    'created_at'   => date('Y-m-d H:i:s'),
                    'update_at'    => date('Y-m-d H:i:s')
                ]);
            }
        }

        $this->db->trans_commit();

        $pesanSukses = 'Penyelesaian konsinyasi berhasil diposting. Qty laku: ' . $qtyLaku . ' ' . $settlement['satuan'] . ' telah dilunasi dan resmi masuk ke Gudang Konsinyasi Lunas.';
        if ($isPartial) {
            $pesanSukses .= ' Sisa ' . $qtySisaDiKios . ' ' . $settlement['satuan'] . ' tetap tercatat sebagai titipan di kios.';
        }

        return [
            'status'        => true,
            'message'       => $pesanSukses,
            'id_jurnal'     => $idJurnal,
            'nomor_jurnal'  => $nomorJurnal,
            'total_tagihan' => $totalTagihanBeli,
            'qty_laku'      => $qtyLaku,
            'qty_sisa_kios' => $qtySisaDiKios
        ];
    }

    /**
     * Memproses pelunasan barang konsinyasi secara batch untuk transaksi gabungan barang yang sama
     *
     * @param array $ids
     * @param array $payload
     * @param int|null $userId
     * @param string $userName
     * @return array
     */
    public function process_settlement_batch(array $ids, array $payload, $userId = null, $userName = 'SYSTEM')
    {
        $ids = array_unique(array_filter(array_map('intval', $ids)));
        if (empty($ids)) {
            return ['status' => false, 'message' => 'Daftar ID settlement tidak valid.'];
        }

        $settlements = $this->db
            ->where_in('id_settlement', $ids)
            ->get('tb_konsinyasi_settlement')
            ->result_array();

        if (empty($settlements)) {
            return ['status' => false, 'message' => 'Data settlement tidak ditemukan.'];
        }

        $this->db->trans_begin();

        $processedCount = 0;
        $totalTagihanAll = 0.0;
        $totalQtyAll = 0.0;
        $firstJurnalId = null;
        $firstJurnalNo = null;

        foreach ($settlements as $st) {
            if ($st['status'] === 'BILLED') {
                continue;
            }

            $singlePayload = $payload;
            $singlePayload['qty_laku'] = (float)$st['qty_net'];

            $res = $this->process_settlement((int)$st['id_settlement'], $singlePayload, $userId, $userName);
            if (!$res['status']) {
                $this->db->trans_rollback();
                return $res;
            }

            $processedCount++;
            $totalTagihanAll += (float)($res['total_tagihan'] ?? 0);
            $totalQtyAll += (float)($res['qty_laku'] ?? 0);
            if (!$firstJurnalId && !empty($res['id_jurnal'])) {
                $firstJurnalId = $res['id_jurnal'];
                $firstJurnalNo = $res['nomor_jurnal'] ?? null;
            }
        }

        if ($processedCount === 0) {
            $this->db->trans_rollback();
            return ['status' => false, 'message' => 'Semua transaksi settlement dalam grup ini sudah berstatus lunas sebelumnya.'];
        }

        $this->db->trans_commit();

        return [
            'status'        => true,
            'message'       => "Berhasil memproses pelunasan barang konsinyasi ({$processedCount} transaksi, total {$totalQtyAll} {$settlements[0]['satuan']}) ke supplier.",
            'id_jurnal'     => $firstJurnalId,
            'nomor_jurnal'  => $firstJurnalNo,
            'total_tagihan' => $totalTagihanAll,
            'qty_laku'      => $totalQtyAll
        ];
    }

    /**
     * Memperbarui nomor faktur dan tanggal invoice supplier pada transaksi konsinyasi yang telah lunas
     */
    public function update_invoice_supplier($idSettlement, $noInvoice, $tglInvoice, $userId = 1, $userName = 'PURCHASING')
    {
        $settlement = $this->get_settlement_by_id((int) $idSettlement);
        if (!$settlement) {
            return ['status' => false, 'message' => 'Data settlement tidak ditemukan.'];
        }

        $this->db->trans_begin();

        $updatePayload = [
            'no_invoice_supplier'  => $noInvoice,
            'tgl_invoice_supplier' => $tglInvoice ?: date('Y-m-d')
        ];

        $this->db->where('id_settlement', (int) $idSettlement)->update('tb_konsinyasi_settlement', $updatePayload);

        // Jika terdapat jurnal pembelian terkait, sinkronkan catatan keterangan pada jurnal
        if (!empty($settlement['id_jurnal_pembelian'])) {
            $keterangan = 'Pembelian Konsinyasi: ' . $settlement['nama_barang'] . ' (' . (float)$settlement['qty_net'] . ' ' . $settlement['satuan'] . '), Supplier: ' . $settlement['nama_suplier'] . ' [Inv: ' . $noInvoice . ']';
            $this->db->where('id_jurnal', (int) $settlement['id_jurnal_pembelian'])->update('tbkeu_jurnal', [
                'keterangan' => $keterangan
            ]);
        }

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            return ['status' => false, 'message' => 'Gagal memperbarui invoice supplier ke database.'];
        }

        $this->db->trans_commit();

        return [
            'status'  => true,
            'message' => 'Faktur / Invoice supplier berhasil diperbarui (No: ' . $noInvoice . ').'
        ];
    }

    /**
     * Ringkasan global tracking posisi barang konsinyasi:
     * 1. Total di Gudang Konsinyasi (Stok fisik konsinyasi sebelum dikirim ke kios)
     * 2. Total di Kios (Barang yang sudah difakturkan ke kios tapi belum laku/dibeli)
     * 3. Total Laku (Barang yang telah resmi dibeli oleh kios & menunggu pelunasan)
     * 4. Total Konsinyasi Lunas (Barang konsinyasi yang sudah lunas dibeli & resmi jadi milik kita)
     * 5. Total Keseluruhan Barang Konsinyasi
     */
    public function get_tracking_summary()
    {
        $warehouseIds = $this->get_consignment_warehouse_ids();
        $whList = !empty($warehouseIds) ? implode(',', array_map('intval', $warehouseIds)) : '13';

        // 1. Total Stok Fisik di Gudang Konsinyasi Kita
        $qGudang = $this->db
            ->select('COALESCE(SUM(qty_on_hand), 0) AS total_gudang, COUNT(DISTINCT kd_barang) AS item_gudang')
            ->where_in('gudang_id', $warehouseIds)
            ->where('qty_on_hand >', 0)
            ->get('tberp_stock_batch')
            ->row_array();

        // 2. Total Barang di Kios (Status DI_KIOS / PENDING = Titipan di Kios, belum dibeli kios)
        $qKios = $this->db
            ->select('COALESCE(SUM(qty_net), 0) AS total_kios, COUNT(DISTINCT kd_barang) AS item_kios, COUNT(DISTINCT customer_name) AS total_kios_count')
            ->where_in('status', ['DI_KIOS', 'PENDING'])
            ->get('tb_konsinyasi_settlement')
            ->row_array();

        // 3. Total Barang Laku (Status LAKU = Sudah dibeli kios & menunggu tagihan/pelunasan supplier)
        $qLaku = $this->db
            ->select('COALESCE(SUM(qty_net), 0) AS total_laku, COUNT(DISTINCT kd_barang) AS item_laku, COALESCE(SUM(subtotal_jual), 0) AS total_nominal_laku')
            ->where('status', 'LAKU')
            ->get('tb_konsinyasi_settlement')
            ->row_array();

        // 4. Total Barang Konsinyasi Lunas (Status BILLED = Sudah dilunasi ke supplier & stok masuk Gudang Konsinyasi Lunas)
        $qLunas = $this->db
            ->select('COALESCE(SUM(qty_net), 0) AS total_lunas, COUNT(DISTINCT kd_barang) AS item_lunas, COALESCE(SUM(total_tagihan_beli), 0) AS total_nominal_lunas')
            ->where('status', 'BILLED')
            ->get('tb_konsinyasi_settlement')
            ->row_array();

        $totalGudang = (float) ($qGudang['total_gudang'] ?? 0);
        $totalKios   = (float) ($qKios['total_kios'] ?? 0);
        $totalLaku   = (float) ($qLaku['total_laku'] ?? 0);
        $totalLunas  = (float) ($qLunas['total_lunas'] ?? 0);

        return [
            'total_di_gudang'      => $totalGudang,
            'item_di_gudang'       => (int) ($qGudang['item_gudang'] ?? 0),
            'total_di_kios'        => $totalKios,
            'item_di_kios'         => (int) ($qKios['item_kios'] ?? 0),
            'total_kios_count'     => (int) ($qKios['total_kios_count'] ?? 0),
            'total_laku'           => $totalLaku,
            'item_laku'            => (int) ($qLaku['item_laku'] ?? 0),
            'total_nominal_laku'   => (float) ($qLaku['total_nominal_laku'] ?? 0),
            'total_lunas'          => $totalLunas,
            'item_lunas'           => (int) ($qLunas['item_lunas'] ?? 0),
            'total_nominal_lunas'  => (float) ($qLunas['total_nominal_lunas'] ?? 0),
            'total_semua'          => $totalGudang + $totalKios + $totalLaku + $totalLunas
        ];
    }

    /**
     * Mengambil daftar inventaris barang konsinyasi dengan posisi stok lengkap:
     * - Di Gudang Konsinyasi
     * - Di Kios (Titipan di Kios)
     * - Barang Laku (Siap Dilunasi)
     * - Konsinyasi Lunas (Gudang Konsinyasi Lunas)
     * - Total Stok
     */
    public function get_tracking_barang_list(array $filters = [])
    {
        $warehouseIds = $this->get_consignment_warehouse_ids();
        $whList = !empty($warehouseIds) ? implode(',', array_map('intval', $warehouseIds)) : '13';

        $this->db->select("
            b.kode_barang,
            b.nama_barang,
            b.satuan,
            COALESCE(lpb_info.nomor_lpb, '') AS nomor_lpb,
            COALESCE(sup.kd_suplier, sub_sup.kd_suplier, '') AS kd_suplier,
            COALESCE(sup.nama_suplier, sub_sup.nama_suplier, 'Supplier Konsinyasi') AS nama_suplier,
            COALESCE(gudang.stok_gudang, 0) AS stok_gudang,
            COALESCE(kios.stok_kios, 0) AS stok_kios,
            COALESCE(kios.jml_kios, 0) AS jml_kios,
            COALESCE(laku.stok_laku, 0) AS stok_laku,
            COALESCE(lunas.stok_lunas, 0) AS stok_lunas,
            (COALESCE(gudang.stok_gudang, 0) + COALESCE(kios.stok_kios, 0) + COALESCE(laku.stok_laku, 0)) AS total_stok
        ", false);

        $this->db->from('tbpo_barang b');

        // Subquery stok gudang konsinyasi
        $this->db->join("
            (SELECT kd_barang, SUM(qty_on_hand) AS stok_gudang 
             FROM tberp_stock_batch 
             WHERE gudang_id IN ($whList) 
             GROUP BY kd_barang) gudang
        ", "gudang.kd_barang = b.kode_barang", "left", false);

        // Subquery stok titipan di kios (DI_KIOS / PENDING)
        $this->db->join("
            (SELECT kd_barang, SUM(qty_net) AS stok_kios, COUNT(DISTINCT customer_name) AS jml_kios
             FROM tb_konsinyasi_settlement 
             WHERE status IN ('DI_KIOS', 'PENDING') 
             GROUP BY kd_barang) kios
        ", "kios.kd_barang = b.kode_barang", "left", false);

        // Subquery stok laku (Hanya yang status LAKU / belum dilunasi ke supplier)
        $this->db->join("
            (SELECT kd_barang, SUM(qty_net) AS stok_laku 
             FROM tb_konsinyasi_settlement 
             WHERE status = 'LAKU' 
             GROUP BY kd_barang) laku
        ", "laku.kd_barang = b.kode_barang", "left", false);

        // Subquery stok konsinyasi lunas (Status BILLED / sudah dilunasi ke supplier)
        $this->db->join("
            (SELECT kd_barang, SUM(qty_net) AS stok_lunas 
             FROM tb_konsinyasi_settlement 
             WHERE status = 'BILLED' 
             GROUP BY kd_barang) lunas
        ", "lunas.kd_barang = b.kode_barang", "left", false);

        // Subquery nomor LPB konsinyasi
        $this->db->join("
            (SELECT kd_barang, GROUP_CONCAT(DISTINCT nomor_lpb ORDER BY nomor_lpb ASC SEPARATOR ', ') AS nomor_lpb
             FROM (
                 SELECT kd_barang, nomor_lpb_asal AS nomor_lpb 
                 FROM tb_konsinyasi_settlement 
                 WHERE nomor_lpb_asal IS NOT NULL AND nomor_lpb_asal != ''
                 UNION
                 SELECT d.kd_barang, h.nomor_lpb 
                 FROM tb_lpb h 
                 JOIN tb_lpb_detail d ON d.id_lpb = h.id_lpb 
                 WHERE (h.gudang_id IN ($whList) OR h.jenis_lpb LIKE '%konsin%') 
                   AND h.nomor_lpb IS NOT NULL AND h.nomor_lpb != ''
             ) lpb_all GROUP BY kd_barang) lpb_info
        ", "lpb_info.kd_barang = b.kode_barang", "left", false);

        // Subquery supplier
        $this->db->join("
            (SELECT kd_barang, MAX(kd_suplier) AS kd_suplier, MAX(nama_suplier) AS nama_suplier
             FROM (
                 SELECT kd_barang, kd_suplier, nama_suplier FROM tb_konsinyasi_settlement
                 UNION
                 SELECT d.kd_barang, h.kd_suplier, h.nama_suplier FROM tb_lpb h JOIN tb_lpb_detail d ON d.id_lpb = h.id_lpb WHERE h.gudang_id IN ($whList)
             ) s_all GROUP BY kd_barang) sub_sup
        ", "sub_sup.kd_barang = b.kode_barang", "left", false);

        $this->db->join('tbpo_suplier sup', 'sup.kd_suplier = b.kd_suplier', 'left');

        // Filter hanya barang yang memiliki stok aktif di gudang, di kios, atau laku
        $this->db->where("(COALESCE(gudang.stok_gudang, 0) > 0 OR COALESCE(kios.stok_kios, 0) > 0 OR COALESCE(laku.stok_laku, 0) > 0)", null, false);

        if (!empty($filters['kd_suplier']) && $filters['kd_suplier'] !== 'SEMUA') {
            $kdSup = $this->db->escape(trim((string)$filters['kd_suplier']));
            $this->db->where("(COALESCE(sup.kd_suplier, sub_sup.kd_suplier) = $kdSup)", null, false);
        }

        if (!empty($filters['search'])) {
            $search = trim((string) $filters['search']);
            $this->db->group_start();
                $this->db->like('b.kode_barang', $search);
                $this->db->or_like('b.nama_barang', $search);
                $this->db->or_like('sup.nama_suplier', $search);
                $this->db->or_like('sub_sup.nama_suplier', $search);
                $this->db->or_like('lpb_info.nomor_lpb', $search);
            $this->db->group_end();
        }

        $this->db->order_by('b.nama_barang', 'ASC');
        return $this->db->get()->result_array();
    }

    /**
     * Mengambil detail daftar kios yang memegang titipan barang konsinyasi tertentu
     */
    public function get_tracking_kios_by_barang($kdBarang)
    {
        $raw = $this->db
            ->select("
                s.id_settlement,
                s.no_settlement,
                s.tanggal_settlement,
                s.customer_name,
                s.id_so,
                s.no_so,
                s.id_faktur,
                COALESCE(fp.no_faktur, s.no_faktur, '-') AS no_faktur,
                s.id_lpb_asal,
                COALESCE(NULLIF(s.nomor_lpb_asal, ''), lpb.nomor_lpb, '-') AS nomor_lpb,
                COALESCE(lpb.no_po, po.no_po, '-') AS no_po,
                COALESCE(NULLIF(pod.hrg_satuan, 0), NULLIF(lpbd.harga_satuan, 0), NULLIF(s.hrg_beli_satuan, 0), 0) AS po_hrg_satuan,
                COALESCE(NULLIF(pod.harga_satuan_exclude, 0), NULLIF(lpbd.harga_satuan, 0), NULLIF(s.hrg_beli_satuan, 0), 0) AS po_hrg_satuan_exclude,
                CASE 
                    WHEN LOWER(po.keterangan_harga_ppn) = 'include' THEN 'INCLUDE'
                    WHEN LOWER(po.keterangan_harga_ppn) = 'non_ppn' THEN 'NON_PPN'
                    ELSE 'EXCLUDE'
                END AS po_tipe_pajak,
                s.no_lot,
                s.expired_date,
                s.qty_terjual,
                s.qty_net,
                s.satuan,
                s.status,
                s.no_invoice_supplier,
                s.total_tagihan_beli
            ", false)
            ->from('tb_konsinyasi_settlement s')
            ->join('tbso_faktur_penjualan fp', 'fp.id_so = s.id_so', 'left')
            ->join('tb_lpb lpb', '(lpb.id_lpb = s.id_lpb_asal OR (s.id_lpb_asal IS NULL AND lpb.nomor_lpb = s.nomor_lpb_asal))', 'left')
            ->join('tbpo_po po', 'po.no_po = lpb.no_po', 'left')
            ->join('tbpo_detail_po pod', 'pod.no_po = po.no_po AND pod.kd_barang = s.kd_barang', 'left')
            ->join('tb_lpb_detail lpbd', 'lpbd.id_lpb = lpb.id_lpb AND lpbd.kd_barang = s.kd_barang AND (lpbd.no_lot = s.no_lot OR s.no_lot IS NULL OR s.no_lot = \'\')', 'left')
            ->where('s.kd_barang', trim((string) $kdBarang))
            ->order_by('s.id_settlement', 'ASC')
            ->get()
            ->result_array();

        // Kelompokkan per transaksi pengiriman faktur/SO + no_lot agar tidak memecah baris
        $grouped = [];
        foreach ($raw as $r) {
            $cleanNoFaktur = preg_replace('/-\d+$/', '', (string)$r['no_faktur']);
            $groupKey = ($r['id_so'] ?: 'SO') . '_' . ($r['id_faktur'] ?: $cleanNoFaktur) . '_' . trim((string)$r['no_lot']);

            if (!isset($grouped[$groupKey])) {
                $grouped[$groupKey] = [
                    'id_settlement'        => $r['id_settlement'],
                    'no_settlement'        => $r['no_settlement'],
                    'tanggal_settlement'   => $r['tanggal_settlement'],
                    'customer_name'        => $r['customer_name'],
                    'id_so'                => $r['id_so'],
                    'no_so'                => $r['no_so'],
                    'id_faktur'            => $r['id_faktur'],
                    'no_faktur'            => $cleanNoFaktur,
                    'nomor_lpb'            => $r['nomor_lpb'],
                    'no_po'                => $r['no_po'],
                    'po_hrg_satuan'        => (float)$r['po_hrg_satuan'],
                    'po_hrg_satuan_exclude'=> (float)$r['po_hrg_satuan_exclude'],
                    'po_tipe_pajak'        => $r['po_tipe_pajak'],
                    'no_lot'               => $r['no_lot'],
                    'expired_date'         => $r['expired_date'],
                    'satuan'               => $r['satuan'],
                    'qty_kios'             => 0.0,
                    'qty_laku'             => 0.0,
                    'qty_lunas'            => 0.0,
                    'qty_total'            => 0.0,
                    'status'               => $r['status']
                ];
            }

            $qty = (float)$r['qty_net'];
            $st = strtoupper((string)$r['status']);

            if ($st === 'DI_KIOS' || $st === 'PENDING') {
                $grouped[$groupKey]['qty_kios'] += $qty;
            } else if ($st === 'LAKU') {
                // Barang laku dibeli kios & siap dilunasi ke supplier
                $grouped[$groupKey]['qty_laku'] += $qty;
            } else if ($st === 'BILLED') {
                // Barang sudah dilunasi ke pihak supplier
                $grouped[$groupKey]['qty_lunas'] += $qty;
            }
            $grouped[$groupKey]['qty_total'] += $qty;
        }

        $results = [];
        foreach ($grouped as $item) {
            if ($item['qty_kios'] > 0 && $item['qty_laku'] > 0) {
                $item['status'] = 'PARTIAL_LAKU';
            } else if ($item['qty_kios'] > 0 && $item['qty_lunas'] > 0 && $item['qty_laku'] <= 0) {
                $item['status'] = 'PARTIAL_LUNAS';
            } else if ($item['qty_kios'] > 0 && $item['qty_laku'] <= 0 && $item['qty_lunas'] <= 0) {
                $item['status'] = 'DI_KIOS';
            } else if ($item['qty_kios'] <= 0 && $item['qty_laku'] > 0) {
                $item['status'] = 'LAKU';
            } else if ($item['qty_kios'] <= 0 && $item['qty_laku'] <= 0 && $item['qty_lunas'] > 0) {
                $item['status'] = 'LUNAS';
            } else {
                $item['status'] = !empty($item['status']) ? $item['status'] : 'DI_KIOS';
            }
            $results[] = $item;
        }

        return $results;
    }

    /**
     * Mengambil ringkasan statistik konsinyasi
     */
    public function get_summary_stats(array $filters = [])
    {
        $this->db->select('COUNT(*) as total_item, COALESCE(SUM(qty_net), 0) as total_qty, COALESCE(SUM(subtotal_jual), 0) as total_omzet');
        $this->db->where_in('status', ['LAKU', 'PENDING']);
        if (!empty($filters['kd_suplier']) && $filters['kd_suplier'] !== 'SEMUA') {
            $this->db->where('kd_suplier', trim((string) $filters['kd_suplier']));
        }
        if (!empty($filters['date_from'])) {
            $this->db->where('tanggal_settlement >=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $this->db->where('tanggal_settlement <=', $filters['date_to']);
        }
        $pending = $this->db->get('tb_konsinyasi_settlement')->row_array();

        $this->db->select('COUNT(*) as total_item, COALESCE(SUM(qty_net), 0) as total_qty, COALESCE(SUM(total_tagihan_beli), 0) as total_hutang');
        $this->db->where('status', 'BILLED');
        if (!empty($filters['kd_suplier']) && $filters['kd_suplier'] !== 'SEMUA') {
            $this->db->where('kd_suplier', trim((string) $filters['kd_suplier']));
        }
        if (!empty($filters['date_from'])) {
            $this->db->where('tanggal_settlement >=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $this->db->where('tanggal_settlement <=', $filters['date_to']);
        }
        $billed = $this->db->get('tb_konsinyasi_settlement')->row_array();

        return [
            'pending_count'  => (int) ($pending['total_item'] ?? 0),
            'pending_qty'    => (float) ($pending['total_qty'] ?? 0),
            'pending_omzet'  => (float) ($pending['total_omzet'] ?? 0),
            'billed_count'   => (int) ($billed['total_item'] ?? 0),
            'billed_qty'     => (float) ($billed['total_qty'] ?? 0),
            'billed_hutang'  => (float) ($billed['total_hutang'] ?? 0)
        ];
    }

    /**
     * Mengambil daftar supplier konsinyasi untuk filter
     */
    public function get_suppliers_list()
    {
        return $this->db
            ->select('kd_suplier, nama_suplier, COUNT(*) as total_transaksi')
            ->group_by(['kd_suplier', 'nama_suplier'])
            ->order_by('nama_suplier', 'ASC')
            ->get('tb_konsinyasi_settlement')
            ->result_array();
    }

    /**
     * Menerbitkan entitas faktur konsinyasi baru saat pelunasan kios
     * Sesuai termin dan kuantitas barang yang dibeli kios.
     * TIDAK disimpan di tbso_faktur_penjualan (disimpan mandiri di tb_konsinyasi_faktur)
     */
    public function terbitkan_faktur_konsinyasi($id_faktur, $id_pembayaran, array $extra = [])
    {
        $this->ensure_schema();

        $id_pembayaran = (int) $id_pembayaran;
        $id_faktur = (int) $id_faktur;

        $pembayaran = $this->db->get_where('tbkeu_pembayaran_faktur', ['id_pembayaran' => $id_pembayaran])->row_array();
        if (!$pembayaran) {
            return null;
        }

        // Ambil data faktur induk
        $fakturInduk = $this->db->get_where('tbso_faktur_penjualan', ['id_faktur' => $id_faktur])->row_array();
        if (!$fakturInduk) {
            return null;
        }

        $customer = $this->db->get_where('tb_customer', ['kd_customer' => $fakturInduk['kd_customer']])->row_array();
        $namaCustomer = $customer['nama_customer'] ?? $fakturInduk['nama_customer'] ?? $fakturInduk['kd_customer'];

        // Hitung termin ke berapa untuk pembayaran konsinyasi ini
        $allPayments = $this->db
            ->select('id_pembayaran')
            ->where('id_faktur', $id_faktur)
            ->where('status !=', 'CANCELLED')
            ->order_by('id_pembayaran', 'ASC')
            ->get('tbkeu_pembayaran_faktur')
            ->result_array();

        $terminKe = 1;
        foreach ($allPayments as $idx => $p) {
            if ((int) $p['id_pembayaran'] === $id_pembayaran) {
                $terminKe = $idx + 1;
                break;
            }
        }

        $noFakturKonsinyasi = $fakturInduk['no_faktur'] . '-' . $terminKe;

        // Update nomor faktur konsinyasi di tabel pembayaran
        $this->db->where('id_pembayaran', $id_pembayaran)->update('tbkeu_pembayaran_faktur', [
            'no_faktur_konsinyasi' => $noFakturKonsinyasi
        ]);

        // Cari settlement terkait (berdasarkan id_pembayaran atau settlement aktif)
        $settlement = null;
        if (!empty($extra['id_settlement'])) {
            $settlement = $this->db->get_where('tb_konsinyasi_settlement', ['id_settlement' => (int) $extra['id_settlement']])->row_array();
        }
        if (!$settlement) {
            $settlement = $this->db->get_where('tb_konsinyasi_settlement', ['id_pembayaran' => $id_pembayaran])->row_array();
        }
        if (!$settlement) {
            $settlement = $this->db
                ->where('id_faktur', $id_faktur)
                ->where_in('status', ['LAKU', 'BILLED', 'DI_KIOS', 'PENDING'])
                ->order_by('id_settlement', 'DESC')
                ->get('tb_konsinyasi_settlement')
                ->row_array();
        }

        $fakturDetail = $this->db->get_where('tbso_faktur_detail', ['id_faktur' => $id_faktur])->row_array();

        $kdBarang   = $settlement['kd_barang'] ?? $fakturDetail['kd_barang'] ?? 'KONS';
        $namaBarang = $settlement['nama_barang'] ?? $fakturDetail['nama_barang'] ?? 'Barang Konsinyasi';
        $satuan     = $settlement['satuan'] ?? $fakturDetail['satuan'] ?? 'PCS';
        $hrgSatuan  = (float) ($settlement['hrg_jual'] ?? $fakturDetail['hrg_satuan'] ?? 0);
        $qty        = (float) (!empty($pembayaran['qty_konsinyasi']) && (float)$pembayaran['qty_konsinyasi'] > 0
                        ? $pembayaran['qty_konsinyasi']
                        : ($settlement['qty_net'] ?? 0));

        if ($qty <= 0 && $hrgSatuan > 0) {
            $qty = round((float) $pembayaran['jumlah_pembayaran'] / $hrgSatuan, 3);
        }
        $subtotal = round($qty * $hrgSatuan, 2);
        if ($subtotal <= 0) {
            $subtotal = (float) $pembayaran['jumlah_pembayaran'];
        }

        $kdSuplier   = $settlement['kd_suplier'] ?? null;
        $namaSuplier = $settlement['nama_suplier'] ?? 'Supplier Konsinyasi';
        $noLot       = $settlement['no_lot'] ?? ($fakturDetail['no_lot'] ?? null);
        $expDate     = $settlement['expired_date'] ?? null;
        $idSettlement = !empty($settlement['id_settlement']) ? (int) $settlement['id_settlement'] : null;

        $existing = $this->db->get_where('tb_konsinyasi_faktur', ['no_faktur_konsinyasi' => $noFakturKonsinyasi])->row_array();
        $payloadFaktur = [
            'no_faktur_konsinyasi' => $noFakturKonsinyasi,
            'id_faktur_induk'      => $id_faktur,
            'no_faktur_induk'      => $fakturInduk['no_faktur'],
            'id_pembayaran'        => $id_pembayaran,
            'id_settlement'        => $idSettlement,
            'id_so'                => !empty($fakturInduk['id_so']) ? (int) $fakturInduk['id_so'] : null,
            'no_so'                => $fakturInduk['no_so'],
            'termin_ke'            => $terminKe,
            'tanggal_faktur'       => $pembayaran['tanggal_pembayaran'],
            'kd_customer'          => $fakturInduk['kd_customer'],
            'nama_customer'        => $namaCustomer,
            'kd_suplier'           => $kdSuplier,
            'nama_suplier'         => $namaSuplier,
            'gudang_id'            => (int) ($fakturInduk['gudang_id'] ?: 13),
            'kd_barang'            => $kdBarang,
            'nama_barang'          => $namaBarang,
            'no_lot'               => $noLot,
            'expired_date'         => $expDate,
            'qty'                  => $qty,
            'satuan'               => $satuan,
            'hrg_satuan'           => $hrgSatuan,
            'subtotal'             => $subtotal,
            'jumlah_bayar'         => (float) $pembayaran['jumlah_pembayaran'],
            'metode_pembayaran'    => $pembayaran['metode_pembayaran'],
            'status'               => ($settlement['status'] ?? 'LAKU') === 'BILLED' ? 'BILLED' : 'LAKU',
            'created_by'           => $pembayaran['create_by'] ?? 'system',
            'created_at'           => $pembayaran['create_at'] ?? date('Y-m-d H:i:s')
        ];

        if ($existing) {
            $this->db->where('id_faktur_konsinyasi', $existing['id_faktur_konsinyasi'])->update('tb_konsinyasi_faktur', $payloadFaktur);
            $idFakturKonsinyasi = $existing['id_faktur_konsinyasi'];
        } else {
            $this->db->insert('tb_konsinyasi_faktur', $payloadFaktur);
            $idFakturKonsinyasi = $this->db->insert_id();
        }

        // Update juga settlement agar no_faktur dan id_pembayaran sinkron
        if ($idSettlement) {
            $this->db->where('id_settlement', $idSettlement)->update('tb_konsinyasi_settlement', [
                'no_faktur'     => $noFakturKonsinyasi,
                'id_pembayaran' => $id_pembayaran
            ]);
        }

        $payloadFaktur['id_faktur_konsinyasi'] = $idFakturKonsinyasi;
        return $payloadFaktur;
    }

    /**
     * Mengambil entitas faktur konsinyasi berdasarkan id_pembayaran
     */
    public function get_faktur_konsinyasi_by_payment($id_pembayaran)
    {
        $this->ensure_schema();
        $row = $this->db->get_where('tb_konsinyasi_faktur', ['id_pembayaran' => (int) $id_pembayaran])->row_array();
        if ($row) {
            return $row;
        }

        // Jika belum ada di tabel tapi ada pembayaran, otomatis terbitkan
        $pembayaran = $this->db->get_where('tbkeu_pembayaran_faktur', ['id_pembayaran' => (int) $id_pembayaran])->row_array();
        if ($pembayaran) {
            return $this->terbitkan_faktur_konsinyasi($pembayaran['id_faktur'], $id_pembayaran);
        }

        return null;
    }

    /**
     * Mengambil entitas faktur konsinyasi berdasarkan nomor faktur konsinyasi
     */
    public function get_faktur_konsinyasi_by_no($noFakturKonsinyasi)
    {
        $this->ensure_schema();
        return $this->db->get_where('tb_konsinyasi_faktur', ['no_faktur_konsinyasi' => trim((string)$noFakturKonsinyasi)])->row_array();
    }

    /**
     * Sinkronisasi data LPB Konsinyasi ke tabel tracking batch stok FIFO tb_konsinyasi_lpb_batch_stock.
     * Merekam setiap penerimaan LPB konsinyasi beserta harga beli DPP aktual.
     *
     * @return int Jumlah batch baru yang disinkronkan
     */
    public function sync_lpb_consignment_batches()
    {
        $this->ensure_schema();

        if (!$this->db->table_exists('tb_lpb') || !$this->db->table_exists('tb_lpb_detail')) {
            return 0;
        }

        $query = "
            SELECT
                h.id_lpb,
                h.nomor_lpb,
                CASE 
                    WHEN h.tgl_sj IS NOT NULL AND h.tgl_sj > '1970-01-01' THEN h.tgl_sj
                    WHEN h.input_at IS NOT NULL THEN DATE(h.input_at)
                    ELSE CURDATE()
                END AS tgl_lpb,
                h.kd_suplier,
                h.nama_suplier,
                COALESCE(h.gudang_id, 13) AS gudang_id,
                d.id_detail_lpb,
                d.kd_barang,
                COALESCE(b.nama_barang, d.kd_barang) AS nama_barang,
                d.no_lot,
                d.expired_date,
                d.qty_diterima,
                d.harga_satuan,
                COALESCE(b.satuan, 'PCS') AS satuan,
                0 AS master_hrg_pokok
            FROM tb_lpb h
            JOIN tb_lpb_detail d ON h.id_lpb = d.id_lpb
            LEFT JOIN tbpo_barang b ON d.kd_barang = b.kode_barang
            WHERE (h.gudang_id = 13 OR h.jenis_lpb = 'LPB Konsinyasi' OR h.nomor_lpb LIKE '%K')
              AND h.status_lpb = 1
              AND d.qty_diterima > 0
            ORDER BY 
                CASE 
                    WHEN h.tgl_sj IS NOT NULL AND h.tgl_sj > '1970-01-01' THEN h.tgl_sj
                    WHEN h.input_at IS NOT NULL THEN DATE(h.input_at)
                    ELSE CURDATE()
                END ASC, 
                h.id_lpb ASC, 
                d.id_detail_lpb ASC
        ";

        $rows = $this->db->query($query)->result_array();
        $inserted = 0;

        foreach ($rows as $r) {
            $idLpb = (int)$r['id_lpb'];
            $idDetailLpb = (int)$r['id_detail_lpb'];

            // Cek apakah batch LPB ini sudah ada di tabel batch stock
            $exists = $this->db
                ->where('id_lpb', $idLpb)
                ->where('id_detail_lpb', $idDetailLpb)
                ->get('tb_konsinyasi_lpb_batch_stock')
                ->row_array();

            if ($exists) {
                // Perbarui harga beli satuan jika sebelumnya 0 dan sekarang sudah ada harga di LPB
                if ((float)$exists['harga_beli_satuan'] <= 0 && (float)$r['harga_satuan'] > 0) {
                    $this->db->where('id_batch_stock', (int)$exists['id_batch_stock'])->update('tb_konsinyasi_lpb_batch_stock', [
                        'harga_beli_satuan' => (float)$r['harga_satuan']
                    ]);
                }
                continue;
            }

            // Hitung harga beli satuan (DPP per pcs tanpa PPN)
            $hrgBeli = (float)$r['harga_satuan'];
            if ($hrgBeli <= 0) {
                $hrgBeli = (float)$r['master_hrg_pokok'];
            }

            $qtyMasuk = (float)$r['qty_diterima'];

            // Cek apakah sudah pernah ada alokasi untuk detail LPB ini sebelumnya
            $allocatedQty = 0.0;
            $allocRow = $this->db
                ->select('COALESCE(SUM(qty_allocated), 0) AS total_alloc')
                ->where('id_lpb', $idLpb)
                ->where('kd_barang', $r['kd_barang'])
                ->get('tb_konsinyasi_cogs_allocation')
                ->row();
            if ($allocRow && (float)$allocRow->total_alloc > 0) {
                $allocatedQty = (float)$allocRow->total_alloc;
            }

            $qtySisa = max(0.0, round($qtyMasuk - $allocatedQty, 3));
            $status = ($qtySisa <= 0) ? 'EXHAUSTED' : (($qtySisa < $qtyMasuk) ? 'PARTIAL' : 'AVAILABLE');

            $insertPayload = [
                'id_lpb'            => $idLpb,
                'id_detail_lpb'     => $idDetailLpb,
                'nomor_lpb'         => trim((string)$r['nomor_lpb']),
                'tgl_lpb'           => $r['tgl_lpb'],
                'kd_suplier'        => $r['kd_suplier'],
                'nama_suplier'      => $r['nama_suplier'],
                'gudang_id'         => (int)$r['gudang_id'],
                'kd_barang'         => $r['kd_barang'],
                'nama_barang'       => $r['nama_barang'],
                'no_lot'            => trim((string)$r['no_lot']),
                'expired_date'      => !empty($r['expired_date']) ? $r['expired_date'] : null,
                'qty_masuk'         => $qtyMasuk,
                'qty_terjual'       => $allocatedQty,
                'qty_sisa'          => $qtySisa,
                'satuan'            => $r['satuan'] ?: 'PCS',
                'harga_beli_satuan' => $hrgBeli,
                'status'            => $status,
                'created_at'        => date('Y-m-d H:i:s'),
                'updated_at'        => date('Y-m-d H:i:s')
            ];

            $this->db->insert('tb_konsinyasi_lpb_batch_stock', $insertPayload);
            $inserted++;
        }

        return $inserted;
    }

    /**
     * Alokasi HPP Penjualan Barang Konsinyasi Menggunakan Metode FIFO (First In, First Out).
     * Mengambil stok dari penerimaan LPB paling awal dan mendukung penjualan lintas batch LPB.
     *
     * @param string $kdBarang Kode barang konsinyasi
     * @param float $qtyToAllocate Kuantitas barang yang dibeli/laku
     * @param int $idPembayaran ID Pembayaran faktur di tabel tbkeu_pembayaran_faktur
     * @param int $idFaktur ID Faktur penjualan di tabel tbso_faktur_penjualan
     * @param string $noFakturKonsinyasi Nomor faktur konsinyasi realisasi kios
     * @return array [total_cogs, allocated_qty, allocations, is_existing]
     */
    public function allocate_fifo_consignment_cogs($kdBarang, $qtyToAllocate, $idPembayaran, $idFaktur, $noFakturKonsinyasi)
    {
        $this->ensure_schema();
        $this->sync_lpb_consignment_batches();

        $idPembayaran = (int)$idPembayaran;
        $idFaktur = (int)$idFaktur;
        $qtyToAllocate = (float)$qtyToAllocate;
        $kdBarang = trim((string)$kdBarang);
        $noFakturKonsinyasi = trim((string)$noFakturKonsinyasi);

        // 1. Cek idempotency: jika alokasi untuk id_pembayaran ini sudah pernah dibuat sebelumnya
        $existing = $this->db
            ->where('id_pembayaran', $idPembayaran)
            ->get('tb_konsinyasi_cogs_allocation')
            ->result_array();

        if (!empty($existing)) {
            $totalCogs = 0.0;
            $totalQty = 0.0;
            foreach ($existing as $ex) {
                $totalCogs += (float)$ex['subtotal_cogs'];
                $totalQty += (float)$ex['qty_allocated'];
            }
            return [
                'total_cogs'    => round($totalCogs, 2),
                'allocated_qty' => $totalQty,
                'allocations'   => $existing,
                'is_existing'   => true
            ];
        }

        if ($qtyToAllocate <= 0) {
            return [
                'total_cogs'    => 0.0,
                'allocated_qty' => 0.0,
                'allocations'   => [],
                'is_existing'   => false
            ];
        }

        // 2. Ambil batch LPB konsinyasi aktif yang masih memiliki sisa stok, diurutkan FIFO (paling awal masuk duluan)
        $availableBatches = $this->db
            ->where('kd_barang', $kdBarang)
            ->where('qty_sisa >', 0)
            ->order_by('tgl_lpb', 'ASC')
            ->order_by('id_lpb', 'ASC')
            ->order_by('id_batch_stock', 'ASC')
            ->get('tb_konsinyasi_lpb_batch_stock')
            ->result_array();

        $needed = $qtyToAllocate;
        $totalCogs = 0.0;
        $allocations = [];

        foreach ($availableBatches as $batch) {
            if ($needed <= 0) {
                break;
            }

            $avail = (float)$batch['qty_sisa'];
            $take = min($avail, $needed);
            $hrgBeli = (float)$batch['harga_beli_satuan'];
            $subtotal = round($take * $hrgBeli, 2);

            $newSisa = round($avail - $take, 3);
            $newTerjual = round((float)$batch['qty_terjual'] + $take, 3);
            $newStatus = ($newSisa <= 0) ? 'EXHAUSTED' : 'PARTIAL';

            // Update saldo sisa stok pada batch LPB terkait
            $this->db->where('id_batch_stock', (int)$batch['id_batch_stock'])->update('tb_konsinyasi_lpb_batch_stock', [
                'qty_terjual' => $newTerjual,
                'qty_sisa'    => $newSisa,
                'status'      => $newStatus,
                'updated_at'  => date('Y-m-d H:i:s')
            ]);

            // Catat jejak audit alokasi FIFO
            $allocPayload = [
                'id_pembayaran'        => $idPembayaran,
                'id_faktur'            => $idFaktur,
                'no_faktur_konsinyasi' => $noFakturKonsinyasi,
                'id_batch_stock'       => (int)$batch['id_batch_stock'],
                'id_lpb'               => (int)$batch['id_lpb'],
                'nomor_lpb'            => $batch['nomor_lpb'],
                'kd_barang'            => $kdBarang,
                'no_lot'               => $batch['no_lot'],
                'expired_date'         => $batch['expired_date'],
                'qty_allocated'        => $take,
                'harga_pokok_satuan'   => $hrgBeli,
                'subtotal_cogs'        => $subtotal,
                'created_at'           => date('Y-m-d H:i:s')
            ];
            $this->db->insert('tb_konsinyasi_cogs_allocation', $allocPayload);
            $allocPayload['id_cogs_alloc'] = $this->db->insert_id();

            $allocations[] = $allocPayload;
            $totalCogs += $subtotal;
            $needed = round($needed - $take, 3);
        }

        // 3. Fallback jika kuantitas penjualan melebihi seluruh sisa stok batch LPB yang tersedia
        if ($needed > 0) {
            $lastBatch = !empty($availableBatches) ? end($availableBatches) : null;
            $fallbackPrice = $lastBatch ? (float)$lastBatch['harga_beli_satuan'] : 0.0;
            if ($fallbackPrice <= 0) {
                $masterItem = $this->db->select('hrg_pokok')->where('kode_barang', $kdBarang)->get('tbpo_barang')->row_array();
                $fallbackPrice = (float)($masterItem['hrg_pokok'] ?? 0);
            }
            $subtotalFallback = round($needed * $fallbackPrice, 2);
            $totalCogs += $subtotalFallback;
        }

        return [
            'total_cogs'    => round($totalCogs, 2),
            'allocated_qty' => $qtyToAllocate,
            'allocations'   => $allocations,
            'is_existing'   => false
        ];
    }

    /**
     * Mengembalikan kuantitas batch LPB saat pembayaran konsinyasi di-unpost atau dibatalkan.
     * Menjaga saldo stok batch LPB konsinyasi tetap sinkron dan presisi.
     *
     * @param int $idPembayaran ID Pembayaran faktur
     */
    public function revert_fifo_consignment_cogs($idPembayaran)
    {
        $idPembayaran = (int)$idPembayaran;
        if ($idPembayaran <= 0 || !$this->db->table_exists('tb_konsinyasi_cogs_allocation')) {
            return;
        }

        $allocations = $this->db
            ->where('id_pembayaran', $idPembayaran)
            ->get('tb_konsinyasi_cogs_allocation')
            ->result_array();

        if (empty($allocations)) {
            return;
        }

        foreach ($allocations as $alloc) {
            $batchId = (int)$alloc['id_batch_stock'];
            $qtyAlloc = (float)$alloc['qty_allocated'];

            $batch = $this->db->where('id_batch_stock', $batchId)->get('tb_konsinyasi_lpb_batch_stock')->row_array();
            if ($batch) {
                $restoredSisa = round((float)$batch['qty_sisa'] + $qtyAlloc, 3);
                $restoredTerjual = max(0.0, round((float)$batch['qty_terjual'] - $qtyAlloc, 3));
                $restoredStatus = ($restoredTerjual <= 0) ? 'AVAILABLE' : 'PARTIAL';

                $this->db->where('id_batch_stock', $batchId)->update('tb_konsinyasi_lpb_batch_stock', [
                    'qty_terjual' => $restoredTerjual,
                    'qty_sisa'    => $restoredSisa,
                    'status'      => $restoredStatus,
                    'updated_at'  => date('Y-m-d H:i:s')
                ]);
            }
        }

        // Hapus jejak alokasi untuk pembayaran yang dibatalkan
        $this->db->where('id_pembayaran', $idPembayaran)->delete('tb_konsinyasi_cogs_allocation');
    }

    /**
     * Mengaitkan ID Jurnal GOODS_ISSUE (HPP & Persediaan) ke jejak alokasi FIFO
     *
     * @param int $idPembayaran
     * @param int $idJurnalGi
     */
    public function update_cogs_journal_id($idPembayaran, $idJurnalGi)
    {
        $idPembayaran = (int)$idPembayaran;
        $idJurnalGi = (int)$idJurnalGi;
        if ($idPembayaran <= 0 || $idJurnalGi <= 0 || !$this->db->table_exists('tb_konsinyasi_cogs_allocation')) {
            return;
        }

        $this->db->where('id_pembayaran', $idPembayaran)->update('tb_konsinyasi_cogs_allocation', [
            'id_jurnal_gi' => $idJurnalGi
        ]);
    }
}

